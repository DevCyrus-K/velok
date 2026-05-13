<?php

namespace App\Support;

use App\Models\User;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserSignature
{
    public function path(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $path = $user->getAttribute('signature')
            ?: $user->getAttribute('signature_path');

        return is_string($path) && trim($path) !== '' ? $path : null;
    }

    public function disk(): string
    {
        return 'public';
    }

    public function routeUrl(?User $user): ?string
    {
        return $this->path($user) ? route('account.signature') : null;
    }

    public function storeUploaded(UploadedFile $file): string
    {
        return app(StorageService::class)->storeUploadedFile($file, 'images/signatures')['key'];
    }

    public function storeDrawn(string $signatureData, User $user): string
    {
        if (! preg_match('/^data:image\/png;base64,(.+)$/', $signatureData, $matches)) {
            throw ValidationException::withMessages([
                'signature_data' => 'Please draw or upload a valid signature image.',
            ]);
        }

        $signature = base64_decode($matches[1], true);

        if ($signature === false) {
            throw ValidationException::withMessages([
                'signature_data' => 'Please draw or upload a valid signature image.',
            ]);
        }

        $filename = 'signature-'.$user->getKey().'-'.Str::uuid().'.png';

        return app(StorageService::class)->uploadFile($signature, $filename, 'image/png', 'images/signatures')['key'];
    }

    public function delete(?string $path): void
    {
        if (! is_string($path) || trim($path) === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        if ($stored = $this->storedFile($path)) {
            Storage::disk($stored['disk'])->delete($stored['path']);

            return;
        }

        $storage = app(StorageService::class);
        $key = $storage->normalizeKey($path);

        if ($key && ! Str::startsWith($key, ['http://', 'https://', '/'])) {
            $storage->deleteImage($key);
        }
    }

    public function content(?string $path): ?string
    {
        $stored = $this->storedFile($path);

        if ($stored) {
            return Storage::disk($stored['disk'])->get($stored['path']);
        }

        $storage = app(StorageService::class);
        $key = $storage->normalizeKey($path);

        if ($key) {
            $content = $storage->contents($key);

            if ($content !== null) {
                return $content;
            }
        }

        return null;
    }

    public function mimeType(?string $path): ?string
    {
        $stored = $this->storedFile($path);

        if ($stored) {
            return Storage::disk($stored['disk'])->mimeType($stored['path']) ?: 'image/png';
        }

        $storage = app(StorageService::class);
        $key = $storage->normalizeKey($path);

        if ($key) {
            $mime = $storage->mimeType($key);

            if ($mime) {
                return $mime;
            }
        }

        return null;
    }

    public function exists(?string $path): bool
    {
        if ($this->storedFile($path) !== null) {
            return true;
        }

        $storage = app(StorageService::class);
        $key = $storage->normalizeKey($path);

        return $key !== null && $storage->exists($key);
    }

    public function dataUri(?string $path): ?string
    {
        $content = $this->content($path);

        if ($content === null) {
            return null;
        }

        return 'data:'.($this->mimeType($path) ?: 'image/png').';base64,'.base64_encode($content);
    }

    /**
     * @return array{disk: string, path: string}|null
     */
    private function storedFile(?string $path): ?array
    {
        if (! is_string($path) || trim($path) === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return null;
        }

        $relativePath = Str::startsWith($path, '/storage/')
            ? Str::after($path, '/storage/')
            : ltrim($path, '/');

        foreach (array_unique([$this->disk(), 'local']) as $disk) {
            if (Storage::disk($disk)->exists($relativePath)) {
                return [
                    'disk' => $disk,
                    'path' => $relativePath,
                ];
            }
        }

        return null;
    }
}
