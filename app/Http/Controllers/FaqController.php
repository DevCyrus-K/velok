<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::query()
            ->orderBy('display_order')
            ->orderBy('category')
            ->orderByDesc('updated_at')
            ->get();

        return view('faqs.index', [
            'faqs' => $faqs,
            'statusOptions' => Faq::statusOptions(),
            'summary' => [
                'total' => $faqs->count(),
                'draft' => $faqs->where('status', Faq::STATUS_DRAFT)->count(),
                'published' => $faqs->where('status', Faq::STATUS_PUBLISHED)->count(),
                'archived' => $faqs->where('status', Faq::STATUS_ARCHIVED)->count(),
                'categories' => $faqs->pluck('category')->filter()->unique()->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('faqs.form', $this->formData(new Faq([
            'status' => Faq::STATUS_DRAFT,
            'category' => 'general',
            'display_order' => (int) Faq::query()->max('display_order') + 1,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $faq = Faq::query()->create($this->validatedData($request));

        return redirect()
            ->route('faqs.show', $faq)
            ->with('toast-success', 'FAQ saved successfully.');
    }

    public function show(Faq $faq): View
    {
        return view('faqs.show', [
            'faq' => $faq,
        ]);
    }

    public function edit(Faq $faq): View
    {
        return view('faqs.form', $this->formData($faq));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validatedData($request));

        return redirect()
            ->route('faqs.show', $faq)
            ->with('toast-success', 'FAQ updated successfully.');
    }

    public function publish(Faq $faq): RedirectResponse
    {
        $faq->update(['status' => Faq::STATUS_PUBLISHED]);

        return back()->with('toast-success', 'FAQ published successfully.');
    }

    public function archive(Faq $faq): RedirectResponse
    {
        $faq->update(['status' => Faq::STATUS_ARCHIVED]);

        return back()->with('toast-success', 'FAQ archived successfully.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()
            ->route('faqs.index')
            ->with('toast-success', 'FAQ deleted successfully.');
    }

    private function formData(Faq $faq): array
    {
        return [
            'faq' => $faq,
            'isEditing' => $faq->exists,
            'statusOptions' => Faq::statusOptions(),
            'categories' => Faq::query()
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->filter()
                ->values(),
        ];
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:80'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(array_keys(Faq::statusOptions()))],
        ]);

        return [
            'question' => $this->squish($validated['question']),
            'answer' => trim($validated['answer']),
            'category' => $this->nullableSquish($validated['category'] ?? null) ?: 'general',
            'display_order' => (int) ($validated['display_order'] ?? 0),
            'status' => $validated['status'],
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
}
