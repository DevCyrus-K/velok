<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CloudinaryService
{
    private const REQUIRED_ENV = [
        'CLOUDINARY_CLOUD_NAME',
        'CLOUDINARY_API_KEY',
        'CLOUDINARY_API_SECRET',
    ];

    private const IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    private ?Cloudinary $client = null;

    /**
     * @var array<string, string>
     */
    private static array $fakeImages = [];

    /**
     * @return list<string>
     */
    public function missingConfiguration(): array
    {
        $values = [
            'CLOUDINARY_CLOUD_NAME' => config('services.cloudinary.cloud_name'),
            'CLOUDINARY_API_KEY' => config('services.cloudinary.api_key'),
            'CLOUDINARY_API_SECRET' => config('services.cloudinary.api_secret'),
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
            throw new RuntimeException('Cloudinary image storage is not configured. Missing: '.implode(', ', $missing));
        }
    }

    public function client(): Cloudinary
    {
        $this->validateConfiguration();

        if ($this->client instanceof Cloudinary) {
            return $this->client;
        }

        $this->client = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        return $this->client;
    }

    /**
     * @return array{key: string, url: string, public_id: string, filename: string, bucket: string, provider: string}
     */
    public function uploadImage(UploadedFile $file, string $folder): array
    {
        // Storage hardening: expose the required Cloudinary upload contract for all image uploads.
        return $this->uploadUploadedImage($file, $folder);
    }

    /**
     * @return array{key: string, url: string, public_id: string, filename: string, bucket: string, provider: string}
     */
    public function uploadUploadedImage(UploadedFile $file, string $folder): array
    {
        $mimeType = (string) ($file->getMimeType() ?: $file->getClientMimeType());
        $this->assertAllowedImage($mimeType, $folder, $file->getSize());

        return $this->uploadPath(
            (string) $file->getRealPath(),
            $folder,
            $file->getClientOriginalName() ?: 'image'
        );
    }

    /**
     * @return array{key: string, url: string, public_id: string, filename: string, bucket: string, provider: string}
     */
    public function uploadImageFromBuffer(string $buffer, string $folder, string $filename): array
    {
        $mimeType = $this->mimeTypeFromBuffer($buffer);
        $this->assertAllowedImage($mimeType, $folder, strlen($buffer));

        $dataUri = 'data:'.$mimeType.';base64,'.base64_encode($buffer);

        return $this->uploadPath($dataUri, $folder, $filename);
    }

    /**
     * @return bool
     */
    public function deleteImage(?string $publicId): bool
    {
        $publicId = $this->normalizePublicId($publicId);

        if ($publicId === null) {
            return true;
        }

        if (app()->environment('testing')) {
            unset(self::$fakeImages[$publicId]);

            return true;
        }

        try {
            $this->client()->uploadApi()->destroy($publicId, ['resource_type' => 'image']);

            return true;
        } catch (Throwable $exception) {
            Log::error('Cloudinary image delete failed', [
                'public_id' => $publicId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw new RuntimeException("Could not delete image {$publicId} from Cloudinary.", 0, $exception);
        }
    }

    public function url(?string $publicId): ?string
    {
        $publicId = $this->normalizePublicId($publicId);

        if ($publicId === null) {
            return null;
        }

        if (Str::startsWith($publicId, ['http://', 'https://', '/'])) {
            return $publicId;
        }

        $cloudName = (string) config('services.cloudinary.cloud_name');
        $encodedPublicId = collect(explode('/', $publicId))
            ->map(fn (string $part): string => rawurlencode($part))
            ->implode('/');

        return "https://res.cloudinary.com/{$cloudName}/image/upload/f_auto,q_auto/{$encodedPublicId}";
    }

    public function contents(?string $publicId): ?string
    {
        $url = $this->url($publicId);

        $fakePublicId = $this->normalizePublicId($publicId);

        if (app()->environment('testing') && $fakePublicId !== null) {
            return self::$fakeImages[$fakePublicId] ?? null;
        }

        if (! $url || ! Str::startsWith($url, ['http://', 'https://'])) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($url);

            return $response->successful() ? $response->body() : null;
        } catch (Throwable $exception) {
            Log::warning('Cloudinary image read failed', [
                'public_id' => $this->normalizePublicId($publicId),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function mimeType(?string $publicId): ?string
    {
        $contents = $this->contents($publicId);

        return $contents !== null ? $this->mimeTypeFromBuffer($contents) : null;
    }

    /**
     * @return array{connected: bool, provider: string, error?: string}
     */
    public function testCloudinaryConnection(): array
    {
        try {
            if (app()->environment('testing')) {
                return ['connected' => true, 'provider' => 'cloudinary'];
            }

            $this->client()->adminApi()->ping();

            return ['connected' => true, 'provider' => 'cloudinary'];
        } catch (Throwable $exception) {
            return [
                'connected' => false,
                'provider' => 'cloudinary',
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{key: string, url: string, public_id: string, filename: string, bucket: string, provider: string}
     */
    private function uploadPath(string $pathOrDataUri, string $folder, string $filename): array
    {
        $profile = $this->uploadProfile($folder);
        $publicIdBase = $this->sanitizeBaseName(pathinfo($filename, PATHINFO_FILENAME));
        $publicId = $profile['folder'].'/'.$publicIdBase.'_'.now()->timestamp.'_'.Str::lower(Str::random(6));

        if (app()->environment('testing')) {
            self::$fakeImages[$publicId] = Str::startsWith($pathOrDataUri, 'data:')
                ? (base64_decode((string) Str::after($pathOrDataUri, ','), true) ?: '')
                : ((string) @file_get_contents($pathOrDataUri));

            return [
                'key' => $publicId,
                'url' => 'https://res.cloudinary.test/image/upload/'.$publicId,
                'public_id' => $publicId,
                'filename' => basename($publicId),
                'bucket' => 'test-cloud',
                'provider' => 'cloudinary',
            ];
        }

        try {
            $result = $this->client()->uploadApi()->upload($pathOrDataUri, array_filter([
                'resource_type' => 'image',
                'folder' => $profile['folder'],
                'public_id' => basename($publicId),
                'overwrite' => false,
                'allowed_formats' => $profile['formats'],
                'transformation' => $profile['transformation'],
            ], fn ($value) => $value !== null));

            $secureUrl = (string) ($result['secure_url'] ?? '');
            $publicId = (string) ($result['public_id'] ?? '');

            if ($secureUrl === '' || $publicId === '') {
                throw new RuntimeException('Cloudinary upload did not return a secure URL and public ID.');
            }

            return [
                'key' => $publicId,
                'url' => $secureUrl,
                'public_id' => $publicId,
                'filename' => basename($publicId),
                'bucket' => (string) config('services.cloudinary.cloud_name'),
                'provider' => 'cloudinary',
            ];
        } catch (Throwable $exception) {
            Log::error('Cloudinary image upload failed', [
                'filename' => $filename,
                'folder' => $profile['folder'],
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw new RuntimeException("Could not upload {$filename} to Cloudinary.", 0, $exception);
        }
    }

    /**
     * @return array{folder: string, formats: list<string>, max_size: int, transformation: array<int, array<string, mixed>>|null}
     */
    private function uploadProfile(string $folder): array
    {
        $folder = Str::lower($folder);

        if (str_contains($folder, 'avatar') || str_contains($folder, 'profile')) {
            return [
                'folder' => 'avatars',
                'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                'max_size' => 5 * 1024 * 1024,
                'transformation' => [[
                    'width' => 500,
                    'height' => 500,
                    'crop' => 'limit',
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                ]],
            ];
        }

        if (str_contains($folder, 'job') || str_contains($folder, 'inventory') || str_contains($folder, 'site')) {
            return [
                'folder' => 'jobs',
                'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                'max_size' => 10 * 1024 * 1024,
                'transformation' => [[
                    'width' => 1200,
                    'height' => 1200,
                    'crop' => 'limit',
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                ]],
            ];
        }

        return [
            'folder' => 'general',
            'formats' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'max_size' => 10 * 1024 * 1024,
            'transformation' => null,
        ];
    }

    private function assertAllowedImage(string $mimeType, string $folder, ?int $size = null): void
    {
        $profile = $this->uploadProfile($folder);
        $extension = self::IMAGE_MIMES[$mimeType] ?? null;

        if ($extension === null || ! in_array($extension, $profile['formats'], true)) {
            throw new RuntimeException('Only image files are allowed');
        }

        if ($size !== null && $size > $profile['max_size']) {
            throw new RuntimeException('Image file is too large for this upload type.');
        }
    }

    private function normalizePublicId(?string $publicId): ?string
    {
        if (! is_string($publicId)) {
            return null;
        }

        $publicId = trim($publicId);

        return $publicId !== '' ? $publicId : null;
    }

    private function mimeTypeFromBuffer(string $buffer): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_buffer($finfo, $buffer) : null;

        if ($finfo) {
            finfo_close($finfo);
        }

        return is_string($mimeType) && $mimeType !== '' ? $mimeType : 'application/octet-stream';
    }

    private function sanitizeBaseName(string $baseName): string
    {
        $baseName = Str::ascii($baseName);
        $baseName = preg_replace('/[^A-Za-z0-9_\- ]+/', '', $baseName) ?: 'image';
        $baseName = preg_replace('/\s+/', '_', trim($baseName)) ?: 'image';

        return Str::limit($baseName, 100, '');
    }
}
