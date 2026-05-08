<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleAnalyticsService
{
    private const DATA_API_BASE = 'https://analyticsdata.googleapis.com/v1beta';
    private const DEFAULT_TOKEN_URI = 'https://oauth2.googleapis.com/token';
    private const OAUTH_GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';
    private const READONLY_SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';
    private const DEVICE_LABELS = ['Desktop', 'Mobile', 'Tablet'];

    public function visitorReport(?CarbonInterface $start = null, ?CarbonInterface $end = null): array
    {
        $endDate = $end ? Carbon::instance($end)->startOfDay() : now()->startOfDay();
        $startDate = $start ? Carbon::instance($start)->startOfDay() : $endDate->copy()->subDays(29);

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if (!$this->isConfigured()) {
            return $this->emptyReport(
                'Google Analytics is not configured yet. Add the GA4 property ID and service account credentials to load visitor data.',
                false,
                $startDate,
                $endDate
            );
        }

        try {
            return Cache::remember(
                $this->reportCacheKey($startDate, $endDate),
                now()->addSeconds($this->cacheTtl()),
                fn () => $this->fetchVisitorReport($startDate, $endDate)
            );
        } catch (Throwable $exception) {
            Log::warning('Google Analytics visitor report failed.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->emptyReport(
                'Google Analytics data is unavailable right now. Check the property ID, API access, and service account permissions.',
                true,
                $startDate,
                $endDate
            );
        }
    }

    private function fetchVisitorReport(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $summary = $this->summaryReport($startDate, $endDate);
        $daily = $this->dailyReport($startDate, $endDate);
        $pages = $this->pagesReport($startDate, $endDate);
        $devices = $this->devicesReport($startDate, $endDate);
        $channels = $this->channelsReport($startDate, $endDate);

        return [
            'configured' => true,
            'error' => null,
            'source' => 'Google Analytics Data API',
            'property' => $this->propertyName(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'date_range_label' => $this->dateRangeLabel($startDate, $endDate),
            'summary' => $summary,
            'today' => $this->todayFromDaily($daily, $endDate),
            'daily' => $daily,
            'pages' => $pages,
            'devices' => $devices,
            'channels' => $channels,
        ];
    }

    private function summaryReport(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $response = $this->runReport([
            'dateRanges' => [$this->dateRange($startDate, $endDate)],
            'metrics' => $this->metrics([
                'activeUsers',
                'newUsers',
                'sessions',
                'screenPageViews',
                'engagementRate',
                'engagedSessions',
            ]),
        ]);

        $row = $response['rows'][0] ?? [];

        return [
            'active_users' => $this->intMetric($row, 0),
            'new_users' => $this->intMetric($row, 1),
            'sessions' => $this->intMetric($row, 2),
            'screen_page_views' => $this->intMetric($row, 3),
            'engagement_rate' => $this->floatMetric($row, 4),
            'engaged_sessions' => $this->intMetric($row, 5),
        ];
    }

    private function dailyReport(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $response = $this->runReport([
            'dateRanges' => [$this->dateRange($startDate, $endDate)],
            'dimensions' => $this->dimensions(['date']),
            'metrics' => $this->metrics(['activeUsers', 'newUsers', 'sessions', 'screenPageViews']),
            'orderBys' => [[
                'dimension' => [
                    'dimensionName' => 'date',
                ],
            ]],
            'keepEmptyRows' => true,
        ]);

        $rowsByDate = collect($response['rows'] ?? [])
            ->mapWithKeys(function (array $row) {
                $date = $this->dateFromGaValue($this->dimensionValue($row, 0));

                if (!$date) {
                    return [];
                }

                return [$date->toDateString() => [
                    'date' => $date->toDateString(),
                    'label' => $date->format('d M'),
                    'active_users' => $this->intMetric($row, 0),
                    'new_users' => $this->intMetric($row, 1),
                    'sessions' => $this->intMetric($row, 2),
                    'screen_page_views' => $this->intMetric($row, 3),
                ]];
            });

        return collect($startDate->daysUntil($endDate))
            ->map(function (Carbon $date) use ($rowsByDate) {
                return $rowsByDate[$date->toDateString()] ?? [
                    'date' => $date->toDateString(),
                    'label' => $date->format('d M'),
                    'active_users' => 0,
                    'new_users' => 0,
                    'sessions' => 0,
                    'screen_page_views' => 0,
                ];
            })
            ->values()
            ->all();
    }

    private function pagesReport(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $response = $this->runReport([
            'dateRanges' => [$this->dateRange($startDate, $endDate)],
            'dimensions' => $this->dimensions(['pagePath']),
            'metrics' => $this->metrics(['activeUsers', 'sessions', 'screenPageViews', 'engagementRate']),
            'orderBys' => [[
                'metric' => [
                    'metricName' => 'screenPageViews',
                ],
                'desc' => true,
            ]],
            'limit' => '50',
        ]);

        return collect($response['rows'] ?? [])
            ->map(fn (array $row) => [
                'page' => $this->cleanDimension($this->dimensionValue($row, 0), 'Unknown Page'),
                'active_users' => $this->intMetric($row, 0),
                'sessions' => $this->intMetric($row, 1),
                'screen_page_views' => $this->intMetric($row, 2),
                'engagement_rate' => $this->floatMetric($row, 3),
            ])
            ->filter(fn (array $row) => $row['screen_page_views'] > 0 || $row['active_users'] > 0)
            ->values()
            ->all();
    }

    private function devicesReport(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $response = $this->runReport([
            'dateRanges' => [$this->dateRange($startDate, $endDate)],
            'dimensions' => $this->dimensions(['deviceCategory']),
            'metrics' => $this->metrics(['activeUsers']),
            'orderBys' => [[
                'metric' => [
                    'metricName' => 'activeUsers',
                ],
                'desc' => true,
            ]],
        ]);

        $counts = array_fill_keys(self::DEVICE_LABELS, 0);

        foreach ($response['rows'] ?? [] as $row) {
            $label = $this->deviceLabel($this->dimensionValue($row, 0));

            if (array_key_exists($label, $counts)) {
                $counts[$label] += $this->intMetric($row, 0);
            }
        }

        return [
            'labels' => self::DEVICE_LABELS,
            'series' => array_values($counts),
        ];
    }

    private function channelsReport(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $response = $this->runReport([
            'dateRanges' => [$this->dateRange($startDate, $endDate)],
            'dimensions' => $this->dimensions(['defaultChannelGroup']),
            'metrics' => $this->metrics(['activeUsers', 'sessions']),
            'orderBys' => [[
                'metric' => [
                    'metricName' => 'activeUsers',
                ],
                'desc' => true,
            ]],
            'limit' => '8',
        ]);

        return collect($response['rows'] ?? [])
            ->map(fn (array $row) => [
                'channel' => $this->cleanDimension($this->dimensionValue($row, 0), 'Unassigned'),
                'active_users' => $this->intMetric($row, 0),
                'sessions' => $this->intMetric($row, 1),
            ])
            ->filter(fn (array $row) => $row['active_users'] > 0 || $row['sessions'] > 0)
            ->values()
            ->all();
    }

    private function runReport(array $payload): array
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout($this->timeout())
            ->post(self::DATA_API_BASE . '/' . $this->propertyName() . ':runReport', $payload);

        if ($response->failed()) {
            throw new RuntimeException($this->googleErrorMessage($response->json()) ?: 'Google Analytics request failed.');
        }

        return $response->json() ?: [];
    }

    private function accessToken(): string
    {
        $credentials = $this->credentials();
        $cacheKey = 'google-analytics-token:' . sha1(($credentials['client_email'] ?? '') . '|' . ($credentials['private_key_id'] ?? ''));
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $tokenUri = $credentials['token_uri'] ?? self::DEFAULT_TOKEN_URI;
        $response = Http::asForm()
            ->acceptJson()
            ->timeout($this->timeout())
            ->post($tokenUri, [
                'grant_type' => self::OAUTH_GRANT_TYPE,
                'assertion' => $this->signedJwt($credentials, $tokenUri),
            ]);

        if ($response->failed()) {
            throw new RuntimeException($this->googleErrorMessage($response->json()) ?: 'Google Analytics authentication failed.');
        }

        $token = (string) ($response->json('access_token') ?? '');

        if ($token === '') {
            throw new RuntimeException('Google Analytics authentication did not return an access token.');
        }

        $expiresIn = max(60, (int) ($response->json('expires_in') ?? 3600) - 60);
        Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));

        return $token;
    }

    private function signedJwt(array $credentials, string $tokenUri): string
    {
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        if (!empty($credentials['private_key_id'])) {
            $header['kid'] = $credentials['private_key_id'];
        }

        $claims = [
            'iss' => $credentials['client_email'],
            'scope' => self::READONLY_SCOPE,
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $unsigned = $this->base64UrlJson($header) . '.' . $this->base64UrlJson($claims);

        if (!openssl_sign($unsigned, $signature, $this->normalizePrivateKey($credentials['private_key']), OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign the Google Analytics service account token.');
        }

        return $unsigned . '.' . $this->base64UrlEncode($signature);
    }

    private function credentials(): array
    {
        $credentials = $this->credentialsFromJsonConfig()
            ?? $this->credentialsFromPath()
            ?? $this->credentialsFromDiscreteEnv();

        if (!$this->hasCredentialFields($credentials)) {
            throw new RuntimeException('Google Analytics service account credentials are missing.');
        }

        $credentials['private_key'] = $this->normalizePrivateKey($credentials['private_key']);
        $credentials['token_uri'] = $credentials['token_uri'] ?? self::DEFAULT_TOKEN_URI;

        return $credentials;
    }

    private function credentialsFromJsonConfig(): ?array
    {
        $rawJson = trim((string) config('services.google_analytics.credentials_json', ''));

        if ($rawJson === '') {
            return null;
        }

        $decoded = json_decode($rawJson, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $base64Decoded = base64_decode($rawJson, true);

        if (!is_string($base64Decoded)) {
            return null;
        }

        $decoded = json_decode($base64Decoded, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function credentialsFromPath(): ?array
    {
        $path = trim((string) config('services.google_analytics.credentials_path', ''));

        if ($path === '' || !is_readable($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function credentialsFromDiscreteEnv(): ?array
    {
        $clientEmail = trim((string) config('services.google_analytics.client_email', ''));
        $privateKey = trim((string) config('services.google_analytics.private_key', ''));

        if ($clientEmail === '' || $privateKey === '') {
            return null;
        }

        return [
            'client_email' => $clientEmail,
            'private_key' => $privateKey,
            'private_key_id' => config('services.google_analytics.private_key_id'),
            'token_uri' => self::DEFAULT_TOKEN_URI,
        ];
    }

    private function isConfigured(): bool
    {
        return $this->propertyName() !== null && $this->hasCredentialFields(
            $this->credentialsFromJsonConfig()
            ?? $this->credentialsFromPath()
            ?? $this->credentialsFromDiscreteEnv()
        );
    }

    private function hasCredentialFields(?array $credentials): bool
    {
        return is_array($credentials)
            && trim((string) ($credentials['client_email'] ?? '')) !== ''
            && trim((string) ($credentials['private_key'] ?? '')) !== '';
    }

    private function propertyName(): ?string
    {
        $propertyId = trim((string) config('services.google_analytics.property_id', ''));

        if ($propertyId === '') {
            return null;
        }

        return str_starts_with($propertyId, 'properties/') ? $propertyId : 'properties/' . $propertyId;
    }

    private function emptyReport(string $message, bool $configured, CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        return [
            'configured' => $configured,
            'error' => $message,
            'source' => 'Google Analytics Data API',
            'property' => $this->propertyName(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'date_range_label' => $this->dateRangeLabel($startDate, $endDate),
            'summary' => [
                'active_users' => 0,
                'new_users' => 0,
                'sessions' => 0,
                'screen_page_views' => 0,
                'engagement_rate' => 0.0,
                'engaged_sessions' => 0,
            ],
            'today' => [
                'active_users' => 0,
                'new_users' => 0,
                'sessions' => 0,
                'screen_page_views' => 0,
            ],
            'daily' => $this->emptyDailySeries($startDate, $endDate),
            'pages' => [],
            'devices' => [
                'labels' => self::DEVICE_LABELS,
                'series' => array_fill(0, count(self::DEVICE_LABELS), 0),
            ],
            'channels' => [],
        ];
    }

    private function emptyDailySeries(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        return collect($startDate->daysUntil($endDate))
            ->map(fn (Carbon $date) => [
                'date' => $date->toDateString(),
                'label' => $date->format('d M'),
                'active_users' => 0,
                'new_users' => 0,
                'sessions' => 0,
                'screen_page_views' => 0,
            ])
            ->values()
            ->all();
    }

    private function todayFromDaily(array $daily, CarbonInterface $endDate): array
    {
        return collect($daily)->firstWhere('date', $endDate->toDateString()) ?? [
            'active_users' => 0,
            'new_users' => 0,
            'sessions' => 0,
            'screen_page_views' => 0,
        ];
    }

    private function reportCacheKey(CarbonInterface $startDate, CarbonInterface $endDate): string
    {
        return 'google-analytics-visitors:' . sha1(implode('|', [
            $this->propertyName(),
            $startDate->toDateString(),
            $endDate->toDateString(),
        ]));
    }

    private function cacheTtl(): int
    {
        return max(60, (int) config('services.google_analytics.cache_ttl', 900));
    }

    private function timeout(): int
    {
        return max(3, (int) config('services.google_analytics.timeout', 10));
    }

    private function dateRange(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        return [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
        ];
    }

    private function dimensions(array $names): array
    {
        return collect($names)->map(fn (string $name) => ['name' => $name])->all();
    }

    private function metrics(array $names): array
    {
        return collect($names)->map(fn (string $name) => ['name' => $name])->all();
    }

    private function intMetric(array $row, int $index): int
    {
        return (int) round($this->floatMetric($row, $index));
    }

    private function floatMetric(array $row, int $index): float
    {
        return (float) ($row['metricValues'][$index]['value'] ?? 0);
    }

    private function dimensionValue(array $row, int $index): string
    {
        return (string) ($row['dimensionValues'][$index]['value'] ?? '');
    }

    private function cleanDimension(string $value, string $fallback): string
    {
        $value = trim($value);

        if ($value === '' || $value === '(not set)') {
            return $fallback;
        }

        return $value;
    }

    private function deviceLabel(string $value): string
    {
        return match (strtolower(trim($value))) {
            'mobile' => 'Mobile',
            'tablet' => 'Tablet',
            default => 'Desktop',
        };
    }

    private function dateFromGaValue(string $value): ?Carbon
    {
        if (!preg_match('/^\d{8}$/', $value)) {
            return null;
        }

        return Carbon::createFromFormat('Ymd', $value)->startOfDay();
    }

    private function dateRangeLabel(CarbonInterface $startDate, CarbonInterface $endDate): string
    {
        return Carbon::instance($startDate)->format('d M Y') . ' - ' . Carbon::instance($endDate)->format('d M Y');
    }

    private function normalizePrivateKey(string $privateKey): string
    {
        return str_replace('\\n', "\n", trim($privateKey));
    }

    private function base64UrlJson(array $value): string
    {
        return $this->base64UrlEncode((string) json_encode($value, JSON_UNESCAPED_SLASHES));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function googleErrorMessage(?array $payload): ?string
    {
        $message = $payload['error']['message'] ?? null;

        return is_string($message) && trim($message) !== '' ? $message : null;
    }
}
