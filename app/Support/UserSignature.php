<?php

namespace App\Support;

use App\Models\User;
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
        return (string) config('filesystems.default', 'local');
    }

    public function routeUrl(?User $user): ?string
    {
        return $this->path($user) ? route('account.signature') : null;
    }

    public function storeUploaded(UploadedFile $file): string
    {
        $extension = $file->extension() ?: ($file->getMimeType() === 'image/png' ? 'png' : 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png'], true) ? $extension : 'jpg';

        return $file->storeAs('signatures', Str::uuid().'.'.$extension, $this->disk());
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

        $path = 'signatures/signature-'.$user->getKey().'-'.Str::uuid().'.png';
        Storage::disk($this->disk())->put($path, $signature);

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! is_string($path) || trim($path) === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        $stored = $this->storedFile($path);

        if ($stored) {
            Storage::disk($stored['disk'])->delete($stored['path']);
        }
    }

    public function content(?string $path): ?string
    {
        $stored = $this->storedFile($path);

        if (! $stored) {
            return null;
        }

        return Storage::disk($stored['disk'])->get($stored['path']);
    }

    public function mimeType(?string $path): ?string
    {
        $stored = $this->storedFile($path);

        if (! $stored) {
            return null;
        }

        return Storage::disk($stored['disk'])->mimeType($stored['path']) ?: 'image/png';
    }

    public function exists(?string $path): bool
    {
        return $this->storedFile($path) !== null;
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

        foreach (array_unique([$this->disk(), 'public', 'local']) as $disk) {
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
