@extends('layouts.vertical', ['title' => 'Gallery'])

@section('css')
    @vite(['node_modules/glightbox/dist/css/glightbox.min.css'])
    <style>
        .gallery-toolbar > * { flex: 0 1 auto; }
        .gallery-search-bar { min-width: 220px; }
        .gallery-image {
            aspect-ratio: 4 / 3;
            object-fit: cover;
        }
        .gallery-card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            min-height: 2.7em;
            overflow: hidden;
        }
        .gallery-card-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            min-height: 2.5em;
            overflow: hidden;
        }
        @media (max-width: 767.98px) {
            .gallery-toolbar > *,
            .gallery-toolbar .dropdown,
            .gallery-toolbar .dropdown > .btn,
            .gallery-toolbar .gallery-search-bar,
            .gallery-toolbar .gallery-search-bar input,
            .gallery-toolbar .btn-success { width: 100%; }
            .gallery-toolbar .gallery-search-bar { margin-left: 0 !important; }
        }
    </style>
@endsection

@section('content')
<div class="row">
    @foreach([
        ['label' => 'Total Images', 'value' => $summary['total'] ?? 0, 'icon' => 'images', 'class' => 'primary'],
        ['label' => 'Published', 'value' => $summary['published'] ?? 0, 'icon' => 'badge-check', 'class' => 'success'],
        ['label' => 'Drafts', 'value' => $summary['draft'] ?? 0, 'icon' => 'file-clock', 'class' => 'warning'],
        ['label' => 'Archived', 'value' => $summary['archived'] ?? 0, 'icon' => 'archive', 'class' => 'secondary'],
        ['label' => 'Featured', 'value' => $summary['featured'] ?? 0, 'icon' => 'sparkles', 'class' => 'info'],
    ] as $card)
        <div class="col-md-6 col-xl">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <p class="text-dark fw-semibold fs-26 mb-1">{{ number_format($card['value']) }}</p>
                            <p class="card-title mb-0">{{ $card['label'] }}</p>
                        </div>
                        <div class="ms-auto">
                            <span class="btn btn-{{ $card['class'] }} avatar-md rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fs-5 text-white" data-lucide="{{ $card['icon'] }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 gallery-toolbar">
    <div>
        <h4 class="mb-1">Gallery Library</h4>
        <p class="text-muted mb-0">Manage the images visitors see across the site.</p>
    </div>
    <div class="dropdown">
        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
            Status: <span id="gallery-filter-label">All</span>
        </a>
        <div class="dropdown-menu">
            <a class="dropdown-item gallery-filter-option" data-filter="all" href="#!">All</a>
            @foreach($statusOptions as $status => $label)
                <a class="dropdown-item gallery-filter-option" data-filter="{{ $status }}" href="#!">{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div class="dropdown">
        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
            Category: <span id="gallery-category-filter-label">All</span>
        </a>
        <div class="dropdown-menu">
            <a class="dropdown-item gallery-category-filter-option" data-category="all" href="#!">All categories</a>
            @foreach($items->pluck('category')->filter()->unique()->sort()->values() as $category)
                <a class="dropdown-item gallery-category-filter-option" data-category="{{ Str::slug($category) }}" href="#!">{{ $category }}</a>
            @endforeach
        </div>
    </div>
    <div class="search-bar ms-auto gallery-search-bar">
        <span style="top: 2px;"><i data-lucide="search"></i></span>
        <input class="form-control form-control-sm" id="gallery-search" placeholder="Search gallery..." type="search" />
    </div>
    <div class="dropdown">
        <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
            Sort: <span id="gallery-sort-label">Newest</span>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item gallery-sort-option" data-sort="newest" href="#!">Newest First</a>
            <a class="dropdown-item gallery-sort-option" data-sort="title" href="#!">Title</a>
            <a class="dropdown-item gallery-sort-option" data-sort="featured" href="#!">Featured First</a>
        </div>
    </div>
    <a class="btn btn-sm btn-success" href="{{ route('gallery.create') }}">
        <i class="icon-sm me-1" data-lucide="plus"></i>Add Image
    </a>
</div>

<div class="row g-4" id="gallery-card-grid">
    @foreach($items as $item)
        <div class="col-xl-4 col-md-6"
             data-category="{{ Str::slug($item->category ?: 'General') }}"
             data-created="{{ $item->created_at?->format('c') ?? '' }}"
             data-featured="{{ $item->featured ? 1 : 0 }}"
             data-gallery-card
             data-search="{{ strtolower(implode(' ', [$item->reference(), $item->title, $item->category, $item->altText(), $item->imagePath(), $item->statusLabel()])) }}"
             data-status="{{ $item->status }}"
             data-title="{{ strtolower($item->title) }}">
            <div class="card h-100 border-0 shadow-sm overflow-hidden">
                <a class="image-popup d-block position-relative" href="{{ $item->publicUrl() }}">
                    <img alt="{{ $item->altText() }}" class="card-img-top gallery-image" loading="lazy" src="{{ $item->publicUrl() }}">
                    <div class="position-absolute top-0 end-0 m-2 d-flex flex-wrap justify-content-end gap-2">
                        <span class="badge bg-dark bg-opacity-75">{{ $item->category ?: 'General' }}</span>
                        @if($item->featured)
                            <span class="badge text-bg-danger">Featured</span>
                        @endif
                        <span class="badge badge-soft-{{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span>
                    </div>
                </a>
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        <a class="h5 card-title gallery-card-title link-dark mb-1" href="{{ route('gallery.show', $item) }}">{{ $item->title }}</a>
                        <small class="text-muted d-block">{{ $item->reference() }}</small>
                    </div>
                    <p class="text-muted gallery-card-text mb-3">{{ $item->altText() }}</p>
                    <div class="d-flex flex-wrap gap-1 mt-auto">
                        <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('gallery.show', $item) }}" title="View"><i class="align-middle" data-lucide="eye"></i></a>
                        <a class="btn btn-icon btn-sm btn-soft-secondary" href="{{ route('gallery.edit', $item) }}" title="Edit"><i class="align-middle" data-lucide="square-pen"></i></a>
                        @if($item->status !== \App\Models\GalleryItem::STATUS_PUBLISHED)
                            <form action="{{ route('gallery.publish', $item) }}" class="d-inline-flex" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-icon btn-sm btn-soft-success" title="Publish" type="submit"><i class="align-middle" data-lucide="check"></i></button>
                            </form>
                        @endif
                        @if($item->status !== \App\Models\GalleryItem::STATUS_ARCHIVED)
                            <form action="{{ route('gallery.archive', $item) }}" class="d-inline-flex" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-icon btn-sm btn-soft-warning" title="Archive" type="submit"><i class="align-middle" data-lucide="archive"></i></button>
                            </form>
                        @endif
                        <form action="{{ route('gallery.destroy', $item) }}" class="d-inline-flex ms-auto" data-delete-confirm data-delete-message="Do you want to delete this gallery image?" data-delete-title="Delete gallery image?" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-icon btn-sm btn-soft-danger" title="Delete" type="submit"><i class="align-middle" data-lucide="trash-2"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-12 {{ $items->isNotEmpty() ? 'd-none' : '' }}" id="gallery-empty-state">
        <div class="card">
            <div class="card-body text-center py-5">
                <h5 class="mb-2" id="gallery-empty-message">{{ $items->isEmpty() ? 'No gallery images found' : 'No gallery images match your search' }}</h5>
                <p class="text-muted mb-0">Add an image and publish it when it is ready for the site.</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <small class="text-muted" id="gallery-count">{{ $items->count() }} images</small>
</div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/gallery.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('gallery-search');
            const grid = document.getElementById('gallery-card-grid');
            const cards = Array.from(document.querySelectorAll('[data-gallery-card]'));
            const emptyState = document.getElementById('gallery-empty-state');
            const emptyMessage = document.getElementById('gallery-empty-message');
            const countLabel = document.getElementById('gallery-count');
            const filterOptions = document.querySelectorAll('.gallery-filter-option');
            const categoryOptions = document.querySelectorAll('.gallery-category-filter-option');
            const sortOptions = document.querySelectorAll('.gallery-sort-option');
            const filterLabel = document.getElementById('gallery-filter-label');
            const categoryLabel = document.getElementById('gallery-category-filter-label');
            const sortLabel = document.getElementById('gallery-sort-label');

            if (!searchInput || !grid || !emptyState || !emptyMessage || !countLabel) return;

            const total = cards.length;
            let debounceTimer = null;
            let currentFilter = 'all';
            let currentCategory = 'all';
            let currentSort = 'newest';
            const filterLabels = { all: 'All', draft: 'Draft', published: 'Published', archived: 'Archived' };
            const sortLabels = { newest: 'Newest', title: 'Title', featured: 'Featured' };
            const categoryLabels = { all: 'All' };

            categoryOptions.forEach((option) => { categoryLabels[option.dataset.category] = option.textContent.trim(); });

            const getDate = (card) => new Date(card.dataset.created || 0);
            const sortCards = (cardsToSort) => {
                const sorted = [...cardsToSort];
                if (currentSort === 'newest') sorted.sort((a, b) => getDate(b) - getDate(a));
                if (currentSort === 'title') sorted.sort((a, b) => (a.dataset.title || '').localeCompare(b.dataset.title || ''));
                if (currentSort === 'featured') sorted.sort((a, b) => Number(b.dataset.featured || 0) - Number(a.dataset.featured || 0));
                return sorted;
            };
            const applyFilters = () => {
                const query = searchInput.value.trim().toLowerCase();
                const matchingCards = [];

                cards.forEach((card) => {
                    const matches = (query === '' || (card.dataset.search || '').toLowerCase().includes(query))
                        && (currentFilter === 'all' || card.dataset.status === currentFilter)
                        && (currentCategory === 'all' || card.dataset.category === currentCategory);

                    card.classList.toggle('d-none', !matches);
                    if (matches) matchingCards.push(card);
                });

                sortCards(matchingCards).forEach((card) => grid.appendChild(card));
                grid.appendChild(emptyState);
                emptyState.classList.toggle('d-none', matchingCards.length > 0);
                emptyMessage.textContent = total === 0 ? 'No gallery images found' : 'No gallery images match your search';
                countLabel.textContent = total === 0 ? '0 images' : `${matchingCards.length} of ${total} images`;
            };

            filterOptions.forEach((option) => option.addEventListener('click', function (event) {
                event.preventDefault();
                currentFilter = this.dataset.filter;
                filterLabel.textContent = filterLabels[currentFilter] || 'All';
                applyFilters();
            }));
            categoryOptions.forEach((option) => option.addEventListener('click', function (event) {
                event.preventDefault();
                currentCategory = this.dataset.category;
                categoryLabel.textContent = categoryLabels[currentCategory] || 'All';
                applyFilters();
            }));
            sortOptions.forEach((option) => option.addEventListener('click', function (event) {
                event.preventDefault();
                currentSort = this.dataset.sort;
                sortLabel.textContent = sortLabels[currentSort] || 'Newest';
                applyFilters();
            }));
            searchInput.addEventListener('input', function () {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(applyFilters, 180);
            });
            applyFilters();
        });
    </script>
@endsection
