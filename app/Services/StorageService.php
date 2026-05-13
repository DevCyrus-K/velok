<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class StorageService
{
    public function __construct(
        private readonly CloudinaryService $cloudinary,
        private readonly BackblazeB2Service $b2,
    ) {}

    public function validateStorageConfiguration(): void
    {
        $missing = [
            ...$this->cloudinary->missingConfiguration(),
            ...$this->b2->missingConfiguration(),
        ];

        if ($missing !== []) {
            Log::critical('Storage providers are not configured. Missing environment variables: '.implode(', ', $missing));

            throw new RuntimeException('Storage providers are not configured. Missing: '.implode(', ', $missing));
        }
    }

    public function validateB2Configuration(): void
    {
        $this->b2->validateConfiguration();
    }

    /**
     * @return list<string>
     */
    public function missingConfiguration(): array
    {
        return [
            ...$this->cloudinary->missingConfiguration(),
            ...$this->b2->missingConfiguration(),
        ];
    }

    /**
     * @return array{key: string, url: string, filename: string, bucket: string, public_id?: string, fileId?: string, provider?: string}
     */
    public function uploadFile(string $buffer, string $filename, string $mimeType, string $folder): array
    {
        if ($this->isImageMime($mimeType)) {
            return $this->cloudinary->uploadImageFromBuffer($buffer, $folder, $filename);
        }

        if ($this->isPdfMime($mimeType)) {
            return $this->b2->uploadPDF($buffer, $filename, $this->pdfFolder($folder));
        }

        throw new RuntimeException('Only image files and PDF files are allowed');
    }

    /**
     * @return array{key: string, url: string, filename: string, bucket: string, public_id?: string, fileId?: string, provider?: string}
     */
    public function storeUploadedFile(UploadedFile $file, string $folder): array
    {
        $mimeType = (string) ($file->getMimeType() ?: $file->getClientMimeType());

        if ($this->isImageMime($mimeType)) {
            return $this->cloudinary->uploadUploadedImage($file, $folder);
        }

        if ($this->isPdfMime($mimeType)) {
            $contents = @file_get_contents($file->getRealPath());

            if ($contents === false) {
                throw new RuntimeException('The uploaded PDF could not be read.');
            }

            return $this->b2->uploadPDF(
                $contents,
                $file->getClientOriginalName() ?: 'document.pdf',
                $this->pdfFolder($folder)
            );
        }

        throw new RuntimeException('Only image files and PDF files are allowed');
    }

    /**
     * @return array{key: string, fileId: string, url: string, filename: string, bucket: string}
     */
    public function uploadPDF(string $buffer, string $filename, string $folder): array
    {
        return $this->b2->uploadPDF($buffer, $filename, $this->pdfFolder($folder));
    }

    /**
     * @return array{key: string, fileId: string, url: string, filename: string, bucket: string}
     */
    public function uploadPDFFromLocalPath(string $localFilePath, string $folder): array
    {
        return $this->b2->uploadPDFFromLocalPath($localFilePath, $this->pdfFolder($folder));
    }

    /**
     * @return array{key: string, fileId: string, url: string, filename: string, bucket: string}
     */
    public function uploadFromLocalPath(string $localFilePath, string $folder): array
    {
        return $this->uploadPDFFromLocalPath($localFilePath, $folder);
    }

    /**
     * @return array{key: string, fileId: string, url: string, filename: string, bucket: string}
     */
    public function uploadGeneratedPdf(string $contents, string $filename, string $folder): array
    {
        $tmpPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .$this->sanitizeBaseName(pathinfo($filename, PATHINFO_FILENAME))
            .'-'.Str::uuid()
            .'.pdf';

        if (@file_put_contents($tmpPath, $contents) === false) {
            throw new RuntimeException("Could not write temporary PDF {$filename} before B2 upload.");
        }

        return $this->uploadPDFFromLocalPath($tmpPath, $folder);
    }

    /**
     * @return array{deleted: true, publicId: string}
     */
    public function deleteImage(?string $publicId): array
    {
        return $this->cloudinary->deleteImage($publicId);
    }

    /**
     * @return array{deleted: true, fileName: string}
     */
    public function deletePDF(?string $fileId, ?string $fileName): array
    {
        return $this->b2->deletePDF($fileId, $fileName);
    }

    /**
     * @return array{deleted: true, key: string}
     */
    public function deleteFile(?string $key, ?string $fileId = null): array
    {
        $key = $this->normalizeKey($key);

        if ($key === null) {
            return ['deleted' => true, 'key' => ''];
        }

        if ($this->looksLikePdfKey($key)) {
            $this->deletePDF($fileId, $key);

            return ['deleted' => true, 'key' => $key];
        }

        $this->deleteImage($key);

        return ['deleted' => true, 'key' => $key];
    }

    public function getPDFDownloadUrl(?string $key): string
    {
        return $this->b2->getPDFDownloadUrl($key);
    }

    public function getSignedUrl(?string $key, int $expiresInSeconds = 3600): string
    {
        $key = $this->normalizeKey($key);

        if ($key === null) {
            throw new RuntimeException('Cannot create a URL without a storage key.');
        }

        if ($this->looksLikePdfKey($key)) {
            return $this->getPDFDownloadUrl($key);
        }

        return $this->cloudinary->url($key) ?: $key;
    }

    public function url(?string $key, int $expiresInSeconds = 3600): ?string
    {
        $key = $this->normalizeKey($key);

        if ($key === null) {
            return null;
        }

        if (Str::startsWith($key, ['http://', 'https://', '/'])) {
            return $key;
        }

        if ($this->looksLikePdfKey($key)) {
            return $this->b2->publicBaseUrl()
                ? $this->b2->publicBaseUrl().'/'.ltrim($key, '/')
                : $this->b2->getPDFDownloadUrl($key);
        }

        if (is_file(public_path($key))) {
            return asset($key);
        }

        return $this->cloudinary->url($key);
    }

    public function contents(?string $key): ?string
    {
        $key = $this->normalizeKey($key);

        if ($key === null) {
            return null;
        }

        if ($this->looksLikeDataUri($key)) {
            return base64_decode((string) Str::after($key, ','), true) ?: null;
        }

        if (Str::startsWith($key, ['http://', 'https://'])) {
            try {
                $response = Http::timeout(20)->get($key);

                return $response->successful() ? $response->body() : null;
            } catch (Throwable) {
                return null;
            }
        }

        if ($this->looksLikePdfKey($key)) {
            return $this->b2->contents($key);
        }

        if (is_file(public_path($key))) {
            return (string) file_get_contents(public_path($key));
        }

        return $this->cloudinary->contents($key);
    }

    public function exists(?string $key): bool
    {
        $key = $this->normalizeKey($key);

        if ($key === null) {
            return false;
        }

        if (Str::startsWith($key, ['http://', 'https://', 'data:image/'])) {
            return true;
        }

        if ($this->looksLikePdfKey($key)) {
            return $this->b2->exists($key);
        }

        return is_file(public_path($key)) || $this->cloudinary->contents($key) !== null;
    }

    public function mimeType(?string $key): ?string
    {
        $key = $this->normalizeKey($key);

        if ($key === null) {
            return null;
        }

        if ($this->looksLikeDataUri($key) && preg_match('/^data:([^;]+)/', $key, $matches)) {
            return $matches[1];
        }

        if ($this->looksLikePdfKey($key)) {
            return 'application/pdf';
        }

        if (is_file(public_path($key))) {
            return mime_content_type(public_path($key)) ?: null;
        }

        return $this->cloudinary->mimeType($key) ?: 'image/png';
    }

    /**
     * @return array{cloudinary: array<string, mixed>, backblaze: array<string, mixed>}
     */
    public function testStorageConnections(): array
    {
        return [
            'cloudinary' => $this->cloudinary->testCloudinaryConnection(),
            'backblaze' => $this->b2->testB2Connection(),
        ];
    }

    /**
     * @return array{connected: bool, provider: string, error?: string}
     */
    public function testB2Connection(): array
    {
        return $this->b2->testB2Connection();
    }

    public function normalizeKey(?string $key): ?string
    {
        if (! is_string($key)) {
            return null;
        }

        $key = trim($key);

        if ($key === '') {
            return null;
        }

        $baseUrl = $this->b2->publicBaseUrl();

        if ($baseUrl !== null && Str::startsWith($key, $baseUrl.'/')) {
            $key = Str::after($key, $baseUrl.'/');
        }

        if (Str::startsWith($key, ['/storage/', 'storage/'])) {
            $key = Str::after(ltrim($key, '/'), 'storage/');
        }

        return ltrim($key, '/');
    }

    public function publicBaseUrl(): ?string
    {
        return $this->b2->publicBaseUrl();
    }

    private function isImageMime(string $mimeType): bool
    {
        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true);
    }

    private function isPdfMime(string $mimeType): bool
    {
        return $mimeType === 'application/pdf';
    }

    private function looksLikePdfKey(string $key): bool
    {
        return Str::endsWith(Str::lower(Str::before($key, '?')), '.pdf')
            || Str::startsWith($key, ['invoices/', 'quotes/', 'agreements/', 'reports/', 'pdfs/', 'service-agreements/']);
    }

    private function looksLikeDataUri(string $key): bool
    {
        return Str::startsWith($key, 'data:') && str_contains($key, ',');
    }

    private function pdfFolder(string $folder): string
    {
        $folder = trim($folder, '/');

        if (Str::startsWith($folder, 'pdfs/')) {
            return $folder;
        }

        return match ($folder) {
            'invoices', 'quotes', 'agreements', 'reports', 'pdfs' => $folder,
            'service-agreements' => 'agreements',
            default => Str::startsWith($folder, ['images/', 'avatars', 'jobs', 'general'])
                ? 'pdfs'
                : $folder,
        };
    }

    private function sanitizeBaseName(string $baseName): string
    {
        $baseName = Str::ascii($baseName);
        $baseName = preg_replace('/[^A-Za-z0-9_\- ]+/', '', $baseName) ?: 'document';
        $baseName = preg_replace('/\s+/', '_', trim($baseName)) ?: 'document';

        return Str::limit($baseName, 120, '');
    }
}
