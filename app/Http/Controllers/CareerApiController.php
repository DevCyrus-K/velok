<?php

namespace App\Http\Controllers;

use App\Models\CareerJob;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CareerApiController extends Controller
{
    public function jobs(): JsonResponse
    {
        $jobs = CareerJob::query()
            ->where('status', CareerJob::STATUS_OPEN)
            ->where(function ($query) {
                $query->whereNull('closes_at')->orWhere('closes_at', '>=', now());
            })
            ->orderByDesc('posted_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (CareerJob $job) => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'department' => $job->department,
                'location' => $job->location,
                'employment_type' => $job->employment_type,
                'salary_range' => $job->salary_range,
                'summary' => $job->summary,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'posted_at' => $job->posted_at?->toISOString(),
                'closes_at' => $job->closes_at?->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function storeApplication(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'job_id' => ['nullable', 'integer'],
            'job_slug' => ['nullable', 'string', 'max:190'],
            'job_title' => ['nullable', 'string', 'max:190'],
            'applicant_name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
            'current_location' => ['nullable', 'string', 'max:160'],
            'resume_url' => ['nullable', 'string', 'max:255'],
            'cover_letter' => ['nullable', 'string'],
            'source_page' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please check the application details and try again.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $job = $this->findJob($validated);

        if (!$job && empty($validated['job_title'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please choose the job you want to apply for.',
            ], 422);
        }

        if ($job && ($job->status !== CareerJob::STATUS_OPEN || ($job->closes_at && $job->closes_at->lt(now())))) {
            return response()->json([
                'success' => false,
                'message' => 'This role is not currently accepting applications.',
            ], 422);
        }

        $application = JobApplication::query()->create([
            'career_job_id' => $job?->id,
            'job_title' => $job?->title ?? trim((string) $validated['job_title']),
            'applicant_name' => trim((string) $validated['applicant_name']),
            'email' => strtolower(trim((string) $validated['email'])),
            'phone' => trim((string) $validated['phone']),
            'current_location' => $this->nullableTrim($validated['current_location'] ?? null),
            'resume_url' => $this->nullableTrim($validated['resume_url'] ?? null),
            'cover_letter' => $this->nullableTrim($validated['cover_letter'] ?? null),
            'status' => JobApplication::STATUS_NEW,
            'applied_at' => now(),
            'source_page' => $this->nullableTrim($validated['source_page'] ?? null) ?: '/careers',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your application has been received.',
            'data' => [
                'id' => $application->id,
                'reference' => $application->reference(),
            ],
        ], 201);
    }

    private function findJob(array $validated): ?CareerJob
    {
        if (!empty($validated['job_id'])) {
            return CareerJob::query()->find($validated['job_id']);
        }

        if (!empty($validated['job_slug'])) {
            return CareerJob::query()->where('slug', $validated['job_slug'])->first();
        }

        return null;
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
