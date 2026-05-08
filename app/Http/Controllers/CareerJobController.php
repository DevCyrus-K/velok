<?php

namespace App\Http\Controllers;

use App\Models\CareerJob;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CareerJobController extends Controller
{
    public function index(): View
    {
        $jobs = CareerJob::query()
            ->withCount('applications')
            ->withCount([
                'applications as open_applications_count' => fn ($query) => $query->whereIn('status', [
                    JobApplication::STATUS_NEW,
                    JobApplication::STATUS_REVIEWING,
                    JobApplication::STATUS_SHORTLISTED,
                ]),
            ])
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('careers.jobs.index', [
            'jobs' => $jobs,
            'statusOptions' => CareerJob::statusOptions(),
            'summary' => [
                'total' => $jobs->count(),
                'open' => $jobs->where('status', CareerJob::STATUS_OPEN)->count(),
                'draft' => $jobs->where('status', CareerJob::STATUS_DRAFT)->count(),
                'closed' => $jobs->where('status', CareerJob::STATUS_CLOSED)->count(),
                'applications' => $jobs->sum('applications_count'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('careers.jobs.form', $this->formData(new CareerJob([
            'status' => CareerJob::STATUS_OPEN,
            'posted_at' => now(),
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $job = CareerJob::query()->create($this->validatedData($request));

        return redirect()
            ->route('careers.jobs.show', $job)
            ->with('toast-success', 'Job listed successfully.');
    }

    public function show(CareerJob $job): View
    {
        $job->load([
            'applications' => fn ($query) => $query->orderByDesc('applied_at')->orderByDesc('id'),
        ]);

        return view('careers.jobs.show', [
            'job' => $job,
            'applications' => $job->applications,
        ]);
    }

    public function edit(CareerJob $job): View
    {
        return view('careers.jobs.form', $this->formData($job));
    }

    public function update(Request $request, CareerJob $job): RedirectResponse
    {
        $job->update($this->validatedData($request));

        return redirect()
            ->route('careers.jobs.show', $job)
            ->with('toast-success', 'Job updated successfully.');
    }

    public function destroy(CareerJob $job): RedirectResponse
    {
        $job->delete();

        return redirect()
            ->route('careers.jobs.index')
            ->with('toast-success', 'Job deleted successfully. Existing applications were kept for review.');
    }

    private function formData(CareerJob $job): array
    {
        return [
            'job' => $job,
            'isEditing' => $job->exists,
            'statusOptions' => CareerJob::statusOptions(),
            'employmentTypeOptions' => CareerJob::employmentTypeOptions(),
        ];
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:160'],
            'employment_type' => ['nullable', 'string', 'max:80'],
            'salary_range' => ['nullable', 'string', 'max:120'],
            'summary' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(CareerJob::statusOptions()))],
            'posted_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
        ]);

        return [
            'title' => $this->squish($validated['title']),
            'department' => $this->nullableSquish($validated['department'] ?? null),
            'location' => $this->nullableSquish($validated['location'] ?? null),
            'employment_type' => $this->nullableSquish($validated['employment_type'] ?? null),
            'salary_range' => $this->nullableSquish($validated['salary_range'] ?? null),
            'summary' => $this->nullableSquish($validated['summary'] ?? null),
            'description' => $this->nullableTrim($validated['description'] ?? null),
            'requirements' => $this->nullableTrim($validated['requirements'] ?? null),
            'status' => $validated['status'],
            'posted_at' => $validated['posted_at'] ?? null,
            'closes_at' => $validated['closes_at'] ?? null,
        ];
    }

    private function squish(string $value): string
    {
        return (string) Str::of($value)->squish();
    }

    private function nullableSquish(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return $this->squish($value);
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
