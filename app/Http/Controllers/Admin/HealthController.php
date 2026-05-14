<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackblazeB2Service;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(CloudinaryService $cloudinary, BackblazeB2Service $b2): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cloudinary' => $this->checkCloudinary($cloudinary),
            'backblaze' => $this->checkBackblaze($b2),
            'mail' => $this->checkMail(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];

        return response()->json([
            'status' => collect($checks)->contains(fn (array $check): bool => $check['status'] === 'fail')
                ? 'degraded'
                : 'ok',
            'checks' => $checks,
        ]);
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');

            return ['status' => 'ok', 'message' => 'Database connection is available.'];
        } catch (Throwable $e) {
            return $this->failedCheck('database', $e);
        }
    }

    private function checkCloudinary(CloudinaryService $cloudinary): array
    {
        try {
            $cloudinary->client()->adminApi()->ping();

            return ['status' => 'ok', 'message' => 'Cloudinary connection is available.'];
        } catch (Throwable $e) {
            return $this->failedCheck('cloudinary', $e);
        }
    }

    private function checkBackblaze(BackblazeB2Service $b2): array
    {
        try {
            $b2->authorizeAccount();

            return ['status' => 'ok', 'message' => 'Backblaze B2 authorization is available.'];
        } catch (Throwable $e) {
            return $this->failedCheck('backblaze', $e);
        }
    }

    private function checkMail(): array
    {
        $host = trim((string) config('mail.mailers.smtp.host'));
        $username = trim((string) config('mail.mailers.smtp.username'));

        if ($host === '' || $username === '') {
            return ['status' => 'fail', 'message' => 'SMTP host or username is not configured.'];
        }

        return ['status' => 'ok', 'message' => 'SMTP host and username are configured.'];
    }

    private function checkQueue(): array
    {
        try {
            if (! Schema::hasTable(config('queue.connections.database.table', 'jobs'))) {
                return ['status' => 'fail', 'message' => 'Queue jobs table is missing.'];
            }

            DB::table(config('queue.connections.database.table', 'jobs'))->limit(1)->get();

            return ['status' => 'ok', 'message' => 'Queue table is accessible.'];
        } catch (Throwable $e) {
            return $this->failedCheck('queue', $e);
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path('app/temp');

        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return is_writable($path)
            ? ['status' => 'ok', 'message' => 'Temporary PDF directory is writable.']
            : ['status' => 'fail', 'message' => 'Temporary PDF directory is not writable.'];
    }

    private function failedCheck(string $check, Throwable $e): array
    {
        // Production hardening: health failures are logged without exposing traces to the client.
        Log::error("Health check failed: {$check}", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return ['status' => 'fail', 'message' => "{$check} check failed."];
    }
}
