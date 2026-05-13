<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Services\StorageService;
use App\Support\NotificationLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function index(): View
    {
        $applications = JobApplication::query()
            ->with('careerJob')
            ->orderByDesc('applied_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('careers.applications.index', [
            'applications' => $applications,
            'statusOptions' => JobApplication::statusOptions(),
            'summary' => [
                'total' => $applications->count(),
                'new' => $applications->where('status', JobApplication::STATUS_NEW)->count(),
                'reviewing' => $applications->where('status', JobApplication::STATUS_REVIEWING)->count(),
                'shortlisted' => $applications->where('status', JobApplication::STATUS_SHORTLISTED)->count(),
                'hired' => $applications->where('status', JobApplication::STATUS_HIRED)->count(),
            ],
        ]);
    }

    public function show(JobApplication $application): View
    {
        $application->load('careerJob');
        app(NotificationLogger::class)->markReadFor($application);

        return view('careers.applications.show', [
            'application' => $application,
            'statusOptions' => JobApplication::statusOptions(),
        ]);
    }

    public function status(Request $request, JobApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(JobApplication::statusOptions()))],
            'notes' => ['nullable', 'string'],
        ]);

        $application->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $application->notes,
        ]);

        return back()->with('toast-success', 'Application updated successfully.');
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        if ($application->pdf_storage_file_id && $application->pdf_storage_key) {
            app(StorageService::class)->deletePDF($application->pdf_storage_file_id, $application->pdf_storage_key);
        }

        $application->delete();

        return redirect()
            ->route('careers.applications.index')
            ->with('toast-success', 'Application deleted successfully.');
    }
}
