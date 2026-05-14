<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BackblazeB2Service
{
    private const REQUIRED_ENV = [
        'B2_APPLICATION_KEY_ID',
        'B2_APPLICATION_KEY',
        'B2_BUCKET_ID',
        'B2_BUCKET_NAME',
    ];

    private const AUTH_CACHE_KEY = 'backblaze_b2.authorization';

    private ?array $authorization = null;

    /**
     * @var array<string, string>
     */
    private static array $fakePdfs = [];

    /**
     * @return list<string>
     */
    public function missingConfiguration(): array
    {
        $values = [
            'B2_APPLICATION_KEY_ID' => config('services.backblaze_b2.application_key_id'),
            'B2_APPLICATION_KEY' => config('services.backblaze_b2.application_key'),
            'B2_BUCKET_ID' => config('services.backblaze_b2.bucket_id'),
            'B2_BUCKET_NAME' => config('services.backblaze_b2.bucket_name'),
        ];

        return collect(self::REQUIRED_ENV)
            ->filter(fn (string $key): bool => trim((string) ($values[$key] ?? '')) === '')
            ->values()
            ->all();
    }

    public function validateConfiguration(): void
    {
        $missing = $this->missingConfiguration();

        if ($missing !== []) {
            throw new RuntimeException('Backblaze B2 PDF storage is not configured. Missing: '.implode(', ', $missing));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function authorizeAccount(): array
    {
        $this->validateConfiguration();

        if (is_array($this->authorization)) {
            return $this->authorization;
        }

        // Storage hardening: cache the B2 account token for 23 hours so requests do not re-authorize.
        return $this->authorization = Cache::remember(self::AUTH_CACHE_KEY, now()->addHours(23), function (): array {
            try {
                $response = Http::withBasicAuth(
                    (string) config('services.backblaze_b2.application_key_id'),
                    (string) config('services.backblaze_b2.application_key')
                )
                    ->timeout(20)
                    ->post('https://api.backblazeb2.com/b2api/v3/b2_authorize_account');

                if (! $response->successful()) {
                    throw new RuntimeException('Backblaze B2 authorization failed with status '.$response->status().'.');
                }

                $data = $response->json();
                $apiUrl = data_get($data, 'apiInfo.storageApi.apiUrl') ?: ($data['apiUrl'] ?? null);
                $downloadUrl = data_get($data, 'apiInfo.storageApi.downloadUrl') ?: ($data['downloadUrl'] ?? null);
                $authorizationToken = $data['authorizationToken'] ?? null;

                if (! is_string($apiUrl) || ! is_string($downloadUrl) || ! is_string($authorizationToken)) {
                    throw new RuntimeException('Backblaze B2 authorization response was missing required API URLs.');
                }

                return [
                    'apiUrl' => rtrim($apiUrl, '/'),
                    'downloadUrl' => rtrim($downloadUrl, '/'),
                    'authorizationToken' => $authorizationToken,
                ];
            } catch (Throwable $exception) {
                Log::error('Backblaze B2 authorization failed', [
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]);

                throw new RuntimeException('Backblaze B2 authorization failed.', 0, $exception);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function authorizeB2(): array
    {
        return $this->authorizeAccount();
    }

    /**
     * @return array{key: string, fileId: string, url: string, filename: string, bucket: string}
     */
    public function uploadPDF(string $filePathOrBuffer, string $folderOrFilename, ?string $folder = null): array
    {
        // Storage hardening: two-argument calls follow the required temp-file upload contract.
        if ($folder === null) {
            return $this->uploadPDFLocalPath($filePathOrBuffer, $folderOrFilename);
        }

        return $this->uploadPDFBuffer($filePathOrBuffer, $folderOrFilename, $folder);
    }

    /**
     * @return array{key: string, fileId: string, url: string, filename: string, bucket: string}
     */
    private function uploadPDFBuffer(string $buffer, string $filename, string $folder): array
    {
        $folder = $this->cleanFolder($folder);
        $sanitizedFilename = $this->sanitizePdfFilename($filename);
        $fullFileName = $folder.'/'.$sanitizedFilename;

        if (app()->environment('testing')) {
            self::$fakePdfs[$fullFileName] = $buffer;

            return [
                'key' => $fullFileName,
                'fileId' => 'fake_'.Str::lower(Str::random(12)),
                'url' => 'https://b2.test/file/test-bucket/'.$fullFileName,
                'filename' => $sanitizedFilename,
                'bucket' => (string) config('services.backblaze_b2.bucket_name', 'test-bucket'),
            ];
        }

        try {
            return $this->withRetry(function () use ($buffer, $fullFileName, $sanitizedFilename): array {
                $uploadUrlData = $this->getUploadUrl();
                $response = Http::withHeaders([
                    'Authorization' => (string) $uploadUrlData['authorizationToken'],
                    'X-Bz-File-Name' => $this->encodeFileName($fullFileName),
                    'X-Bz-Content-Sha1' => sha1($buffer),
                    'Content-Type' => 'application/pdf',
                    'Content-Length' => (string) strlen($buffer),
                ])
                    ->timeout(60)
                    ->withBody($buffer, 'application/pdf')
                    ->post((string) $uploadUrlData['uploadUrl']);

                if ($response->status() === 401) {
                    throw new BackblazeAuthorizationExpired('Backblaze B2 upload token expired.');
                }

                if (! $response->successful()) {
                    throw new RuntimeException('Backblaze B2 upload failed with status '.$response->status().'.');
                }

                $data = $response->json();
                $fileName = (string) ($data['fileName'] ?? $fullFileName);
                $fileId = (string) ($data['fileId'] ?? '');

                if ($fileId === '') {
                    throw new RuntimeException('Backblaze B2 upload did not return a fileId.');
                }

                return [
                    'key' => $fileName,
                    'fileId' => $fileId,
                    'url' => $this->publicUrlFor($fileName) ?: $this->getPDFDownloadUrl($fileName),
                    'filename' => $sanitizedFilename,
                    'bucket' => (string) config('services.backblaze_b2.bucket_name'),
                ];
            });
        } catch (Throwable $exception) {
            Log::error('Backblaze B2 PDF upload failed', [
                'filename' => $filename,
                'folder' => $folder,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw new RuntimeException("Could not upload {$filename} to Backblaze B2.", 0, $exception);
        }
    }

    /**
     * @return bool
     */
    public function deletePDF(?string $fileId, ?string $fileName): bool
    {
        $fileId = is_string($fileId) ? trim($fileId) : '';
        $fileName = $this->normalizeKey($fileName);

        if ($fileName === null) {
            return true;
        }

        if (app()->environment('testing')) {
            unset(self::$fakePdfs[$fileName]);

            return true;
        }

        if ($fileId === '') {
            throw new RuntimeException("Cannot delete {$fileName} from Backblaze B2 without its fileId.");
        }

        try {
            $this->withRetry(function () use ($fileId, $fileName): void {
                $this->postJson('b2_delete_file_version', [
                    'fileId' => $fileId,
                    'fileName' => $fileName,
                ]);
            });

            return true;
        } catch (Throwable $exception) {
            Log::error('Backblaze B2 PDF delete failed', [
                'file_id' => $fileId,
                'file_name' => $fileName,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw new RuntimeException("Could not delete {$fileName} from Backblaze B2.", 0, $exception);
        }
    }

    public function getDownloadUrl(?string $fileName): string
    {
        $fileName = $this->normalizeKey($fileName);

        if ($fileName === null) {
            throw new RuntimeException('Cannot create a download URL without a Backblaze B2 file name.');
        }

        if (app()->environment('testing')) {
            return 'https://b2.test/file/'.rawurlencode((string) config('services.backblaze_b2.bucket_name', 'test-bucket')).'/'.$this->encodeFileName($fileName).'?Authorization=fake';
        }

        return $this->withRetry(function () use ($fileName): string {
            $authData = $this->postJson('b2_get_download_authorization', [
                'bucketId' => config('services.backblaze_b2.bucket_id'),
                'fileNamePrefix' => $fileName,
                'validDurationInSeconds' => 3600,
            ]);

            $token = (string) ($authData['authorizationToken'] ?? '');
            $authorization = $this->authorizeAccount();

            if ($token === '') {
                throw new RuntimeException('Backblaze B2 download authorization did not return a token.');
            }

            return $authorization['downloadUrl']
                .'/file/'
                .rawurlencode((string) config('services.backblaze_b2.bucket_name'))
                .'/'
                .$this->encodeFileName($fileName)
                .'?Authorization='
                .rawurlencode($token);
        });
    }

    public function getPDFDownloadUrl(?string $fileName): string
    {
        return $this->getDownloadUrl($fileName);
    }

    /**
     * @return array{key: string, fileId: string, url: string, filename: string, bucket: string}
     */
    public function uploadPDFFromLocalPath(string $localFilePath, string $folder): array
    {
        return $this->uploadPDFLocalPath($localFilePath, $folder);
    }

    /**
     * @return array{key: string, fileId: string, url: string, filename: string, bucket: string}
     */
    private function uploadPDFLocalPath(string $localFilePath, string $folder): array
    {
        $filename = basename($localFilePath);

        try {
            $buffer = @file_get_contents($localFilePath);

            if ($buffer === false) {
                throw new RuntimeException("Could not read temporary PDF {$localFilePath}.");
            }

            return $this->uploadPDFBuffer($buffer, $filename, $folder);
        } catch (Throwable $exception) {
            Log::error('Backblaze B2 local PDF upload failed', [
                'path' => $localFilePath,
                'folder' => $folder,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        } finally {
            // Storage hardening: generated PDFs are temporary and removed after B2 upload attempts.
            if (is_file($localFilePath)) {
                @unlink($localFilePath);
            }
        }
    }

    public function contents(?string $fileName): ?string
    {
        $fileName = $this->normalizeKey($fileName);

        if ($fileName === null) {
            return null;
        }

        if (app()->environment('testing')) {
            return self::$fakePdfs[$fileName] ?? null;
        }

        try {
            $response = Http::timeout(60)->get($this->getPDFDownloadUrl($fileName));

            return $response->successful() ? $response->body() : null;
        } catch (Throwable $exception) {
            Log::warning('Backblaze B2 PDF read failed', [
                'file_name' => $fileName,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function exists(?string $fileName): bool
    {
        $fileName = $this->normalizeKey($fileName);

        if ($fileName === null) {
            return false;
        }

        if (app()->environment('testing')) {
            return array_key_exists($fileName, self::$fakePdfs);
        }

        try {
            return $this->withRetry(function () use ($fileName): bool {
                $data = $this->postJson('b2_list_file_names', [
                    'bucketId' => config('services.backblaze_b2.bucket_id'),
                    'prefix' => $fileName,
                    'maxFileCount' => 1,
                ]);

                $files = $data['files'] ?? [];

                return isset($files[0]['fileName']) && $files[0]['fileName'] === $fileName;
            });
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array{connected: bool, provider: string, error?: string}
     */
    public function testB2Connection(): array
    {
        try {
            if (app()->environment('testing')) {
                return ['connected' => true, 'provider' => 'backblaze-b2'];
            }

            $this->withRetry(function (): void {
                $this->postJson('b2_list_file_names', [
                    'bucketId' => config('services.backblaze_b2.bucket_id'),
                    'maxFileCount' => 1,
                ]);
            });

            return ['connected' => true, 'provider' => 'backblaze-b2'];
        } catch (Throwable $exception) {
            return [
                'connected' => false,
                'provider' => 'backblaze-b2',
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function normalizeKey(?string $fileName): ?string
    {
        if (! is_string($fileName)) {
            return null;
        }

        $fileName = trim($fileName);

        if ($fileName === '') {
            return null;
        }

        $baseUrl = $this->publicBaseUrl();

        if ($baseUrl !== null && Str::startsWith($fileName, $baseUrl.'/')) {
            $fileName = Str::after($fileName, $baseUrl.'/');
        }

        return ltrim($fileName, '/');
    }

    public function publicBaseUrl(): ?string
    {
        $baseUrl = trim((string) config('services.backblaze_b2.bucket_base_url'));

        return $baseUrl !== '' ? rtrim($baseUrl, '/') : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getUploadUrl(): array
    {
        return $this->postJson('b2_get_upload_url', [
            'bucketId' => config('services.backblaze_b2.bucket_id'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function postJson(string $endpoint, array $payload): array
    {
        $authorization = $this->authorizeAccount();
        $response = Http::withHeaders([
            'Authorization' => (string) $authorization['authorizationToken'],
        ])
            ->timeout(30)
            ->post($authorization['apiUrl'].'/b2api/v3/'.$endpoint, $payload);

        if ($response->status() === 401) {
            throw new BackblazeAuthorizationExpired('Backblaze B2 authorization expired.');
        }

        if (! $response->successful()) {
            throw new RuntimeException("Backblaze B2 {$endpoint} failed with status ".$response->status().'.');
        }

        return $response->json() ?: [];
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function withRetry(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (BackblazeAuthorizationExpired) {
            $this->authorization = null;
            Cache::forget(self::AUTH_CACHE_KEY);
            $this->authorizeAccount();

            return $callback();
        }
    }

    private function publicUrlFor(string $fileName): ?string
    {
        $baseUrl = $this->publicBaseUrl();

        return $baseUrl ? $baseUrl.'/'.ltrim($fileName, '/') : null;
    }

    private function sanitizePdfFilename(string $filename): string
    {
        $baseName = Str::ascii(pathinfo($filename, PATHINFO_FILENAME));
        $baseName = preg_replace('/[^A-Za-z0-9_\- ]+/', '', $baseName) ?: 'document';
        $baseName = preg_replace('/\s+/', '_', trim($baseName)) ?: 'document';
        $baseName = Str::limit($baseName, 120, '');

        return $baseName.'_'.now()->timestamp.'_'.Str::lower(Str::random(6)).'.pdf';
    }

    private function cleanFolder(string $folder): string
    {
        $folder = preg_replace('/[^A-Za-z0-9_\-\/]+/', '', trim($folder, '/')) ?: 'pdfs';

        return trim($folder, '/');
    }

    private function encodeFileName(string $fileName): string
    {
        return collect(explode('/', $fileName))
            ->map(fn (string $part): string => rawurlencode($part))
            ->implode('/');
    }
}

class BackblazeAuthorizationExpired extends RuntimeException
{
}
