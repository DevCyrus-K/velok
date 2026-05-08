<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\GalleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentApiController extends Controller
{
    public function faqs(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->integer('limit', 12), 1), 50);
        $category = trim((string) $request->query('category', ''));

        $faqs = Faq::query()
            ->where('status', Faq::STATUS_PUBLISHED)
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('display_order')
            ->orderBy('category')
            ->orderBy('id')
            ->take($limit)
            ->get()
            ->map(fn (Faq $faq) => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'category' => $faq->category,
                'display_order' => $faq->display_order,
                'created_at' => $faq->created_at?->toISOString(),
            ]);

        return response()->json([
            'ok' => true,
            'success' => true,
            'data' => $faqs,
        ]);
    }

    public function gallery(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->integer('limit', 12), 1), 50);
        $category = trim((string) $request->query('category', ''));
        $featured = $request->query('featured');

        $items = GalleryItem::query()
            ->where('status', GalleryItem::STATUS_PUBLISHED)
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($featured !== null, fn ($query) => $query->where('featured', filter_var($featured, FILTER_VALIDATE_BOOLEAN)))
            ->orderByDesc('featured')
            ->orderBy('created_at')
            ->orderBy('id')
            ->take($limit)
            ->get()
            ->map(fn (GalleryItem $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'image_path' => $item->imagePath(),
                'image_url' => str_starts_with($item->imagePath(), 'storage/')
                    ? '/' . $item->imagePath()
                    : $item->imagePath(),
                'category' => $item->category,
                'alt_text' => $item->altText(),
                'featured' => $item->featured,
                'created_at' => $item->created_at?->toISOString(),
            ]);

        return response()->json([
            'ok' => true,
            'success' => true,
            'data' => $items,
        ]);
    }
}
