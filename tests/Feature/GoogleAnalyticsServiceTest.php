<?php

use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('builds visitor reports from google analytics data api responses', function () {
    Cache::flush();
    Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

    $key = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);
    openssl_pkey_export($key, $privateKey);

    config()->set('services.google_analytics.property_id', '123456789');
    config()->set('services.google_analytics.credentials_path', null);
    config()->set('services.google_analytics.credentials_json', json_encode([
        'type' => 'service_account',
        'client_email' => 'analytics-reader@example.iam.gserviceaccount.com',
        'private_key_id' => 'test-key-id',
        'private_key' => $privateKey,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ]));
    config()->set('services.google_analytics.cache_ttl', 300);
    config()->set('services.google_analytics.timeout', 5);

    $runReportRequests = [];

    Http::fake(function (Request $request) use (&$runReportRequests) {
        if (str_contains($request->url(), 'oauth2.googleapis.com/token')) {
            expect($request->data()['grant_type'])->toBe('urn:ietf:params:oauth:grant-type:jwt-bearer')
                ->and($request->data()['assertion'])->toContain('.');

            return Http::response([
                'access_token' => 'ya29.test-token',
                'expires_in' => 3600,
            ]);
        }

        $authorizationHeader = (array) $request->header('Authorization');

        expect($request->url())->toBe('https://analyticsdata.googleapis.com/v1beta/properties/123456789:runReport')
            ->and($authorizationHeader[0] ?? '')->toBe('Bearer ya29.test-token');

        $payload = $request->data();
        $runReportRequests[] = $payload;
        $dimensions = collect($payload['dimensions'] ?? [])->pluck('name')->values()->all();

        if ($dimensions === []) {
            return Http::response([
                'rows' => [[
                    'metricValues' => [
                        ['value' => '128'],
                        ['value' => '41'],
                        ['value' => '205'],
                        ['value' => '634'],
                        ['value' => '0.64'],
                        ['value' => '131'],
                    ],
                ]],
            ]);
        }

        if ($dimensions === ['date']) {
            return Http::response([
                'rows' => [
                    [
                        'dimensionValues' => [['value' => '20260505']],
                        'metricValues' => [
                            ['value' => '58'],
                            ['value' => '18'],
                            ['value' => '80'],
                            ['value' => '240'],
                        ],
                    ],
                    [
                        'dimensionValues' => [['value' => '20260506']],
                        'metricValues' => [
                            ['value' => '70'],
                            ['value' => '23'],
                            ['value' => '125'],
                            ['value' => '394'],
                        ],
                    ],
                ],
            ]);
        }

        if ($dimensions === ['pagePath']) {
            return Http::response([
                'rows' => [[
                    'dimensionValues' => [['value' => '/properties/for-sale']],
                    'metricValues' => [
                        ['value' => '91'],
                        ['value' => '143'],
                        ['value' => '320'],
                        ['value' => '0.72'],
                    ],
                ]],
            ]);
        }

        if ($dimensions === ['deviceCategory']) {
            return Http::response([
                'rows' => [
                    [
                        'dimensionValues' => [['value' => 'desktop']],
                        'metricValues' => [['value' => '80']],
                    ],
                    [
                        'dimensionValues' => [['value' => 'mobile']],
                        'metricValues' => [['value' => '40']],
                    ],
                    [
                        'dimensionValues' => [['value' => 'tablet']],
                        'metricValues' => [['value' => '8']],
                    ],
                ],
            ]);
        }

        if ($dimensions === ['defaultChannelGroup']) {
            return Http::response([
                'rows' => [[
                    'dimensionValues' => [['value' => 'Organic Search']],
                    'metricValues' => [
                        ['value' => '88'],
                        ['value' => '121'],
                    ],
                ]],
            ]);
        }

        return Http::response(['error' => ['message' => 'Unexpected report request']], 400);
    });

    $report = app(GoogleAnalyticsService::class)->visitorReport(
        Carbon::parse('2026-05-05'),
        Carbon::parse('2026-05-06')
    );

    expect($report['configured'])->toBeTrue()
        ->and($report['summary']['active_users'])->toBe(128)
        ->and($report['summary']['engagement_rate'])->toBe(0.64)
        ->and($report['daily'])->toHaveCount(2)
        ->and($report['today']['new_users'])->toBe(23)
        ->and($report['pages'][0]['page'])->toBe('/properties/for-sale')
        ->and($report['devices']['labels'])->toBe(['Desktop', 'Mobile', 'Tablet'])
        ->and($report['devices']['series'])->toBe([80, 40, 8])
        ->and($report['channels'][0]['channel'])->toBe('Organic Search');

    expect($runReportRequests)->toHaveCount(5);

    Carbon::setTestNow();
});
