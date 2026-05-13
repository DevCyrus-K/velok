<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $items = GalleryItem::query()
            ->orderByDesc('featured')
            ->orderBy('category')
            ->orderByDesc('created_at')
            ->get();

        return view('gallery.index', [
            'items' => $items,
            'statusOptions' => GalleryItem::statusOptions(),
            'summary' => [
                'total' => $items->count(),
                'published' => $items->where('status', GalleryItem::STATUS_PUBLISHED)->count(),
                'draft' => $items->where('status', GalleryItem::STATUS_DRAFT)->count(),
                'archived' => $items->where('status', GalleryItem::STATUS_ARCHIVED)->count(),
                'featured' => $items->where('featured', true)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('gallery.form', $this->formData(new GalleryItem([
            'status' => GalleryItem::STATUS_DRAFT,
            'category' => 'General',
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedData($request);
        unset($payload['_uploaded_new_image']);

        $item = GalleryItem::query()->create($payload);

        return redirect()
            ->route('gallery.show', $item)
            ->with('toast-success', 'Gallery image saved successfully.');
    }

    public function show(GalleryItem $gallery): View
    {
        return view('gallery.show', [
            'item' => $gallery,
        ]);
    }

    public function edit(GalleryItem $gallery): View
    {
        return view('gallery.form', $this->formData($gallery));
    }

    public function update(Request $request, GalleryItem $gallery): RedirectResponse
    {
        $payload = $this->validatedData($request, $gallery);

        if (($payload['_uploaded_new_image'] ?? false) === true) {
            $this->deleteStoredImage($gallery);
        }

        unset($payload['_uploaded_new_image']);
        $gallery->update($payload);

        return redirect()
            ->route('gallery.show', $gallery)
            ->with('toast-success', 'Gallery image updated successfully.');
    }

    public function publish(GalleryItem $gallery): RedirectResponse
    {
        $gallery->update(['status' => GalleryItem::STATUS_PUBLISHED]);

        return back()->with('toast-success', 'Gallery image published successfully.');
    }

    public function archive(GalleryItem $gallery): RedirectResponse
    {
        $gallery->update(['status' => GalleryItem::STATUS_ARCHIVED]);

        return back()->with('toast-success', 'Gallery image archived successfully.');
    }

    public function destroy(GalleryItem $gallery): RedirectResponse
    {
        $this->deleteStoredImage($gallery);
        $gallery->delete();

        return redirect()
            ->route('gallery.index')
            ->with('toast-success', 'Gallery image deleted successfully.');
    }

    private function formData(GalleryItem $item): array
    {
        return [
            'item' => $item,
            'isEditing' => $item->exists,
            'statusOptions' => GalleryItem::statusOptions(),
            'categories' => GalleryItem::query()
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->filter()
                ->values(),
        ];
    }

    private function validatedData(Request $request, ?GalleryItem $item = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'image_path' => ['nullable', 'string', 'max:1000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'category' => ['nullable', 'string', 'max:100'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_keys(GalleryItem::statusOptions()))],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $imagePath = $this->nullableTrim($validated['image_path'] ?? null);
        $imageFile = $request->file('image_file');
        $uploaded = null;

        if ($imageFile instanceof UploadedFile) {
            $uploaded = app(StorageService::class)->storeUploadedFile($imageFile, 'images/gallery');
            $imagePath = $uploaded['key'];
        }

        if (! $imagePath && (! $item || ! $item->exists)) {
            $request->validate([
                'image_path' => ['required_without:image_file'],
            ], [
                'image_path.required_without' => 'Add an existing image path or upload an image.',
            ]);
        }

        $payload = [
            'title' => $this->squish($validated['title']),
            'image_path' => $imagePath ?: $item?->imagePath(),
            'category' => $this->nullableSquish($validated['category'] ?? null) ?: 'General',
            'alt_text' => $this->nullableSquish($validated['alt_text'] ?? null) ?: $this->squish($validated['title']),
            'description' => $this->nullableTrim($validated['description'] ?? null),
            'featured' => (bool) ($validated['featured'] ?? false),
            'status' => $validated['status'],
            'order' => (int) ($validated['order'] ?? 0),
        ];

        if (Schema::hasColumn('gallery', 'image_url')) {
            $payload['image_url'] = $uploaded['url'] ?? app(StorageService::class)->url($payload['image_path']) ?? $payload['image_path'];
        }

        if ($uploaded && Schema::hasColumn('gallery', 'image_public_id')) {
            $payload['image_public_id'] = $uploaded['public_id'] ?? $uploaded['key'];
        }

        if ($uploaded && Schema::hasColumn('gallery', 'storage_key')) {
            $payload['storage_key'] = $uploaded['key'];
        }

        if ($uploaded && Schema::hasColumn('gallery', 'storage_url')) {
            $payload['storage_url'] = $uploaded['url'];
        }

        if ($uploaded) {
            $payload['_uploaded_new_image'] = true;
        }

        return $payload;
    }

    private function deleteStoredImage(GalleryItem $item): void
    {
        $publicId = $item->getAttribute('image_public_id') ?: $item->getAttribute('storage_key') ?: $item->imagePath();

        if (! is_string($publicId) || trim($publicId) === '' || Str::startsWith($publicId, ['http://', 'https://', '/'])) {
            return;
        }

        app(StorageService::class)->deleteImage($publicId);
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
