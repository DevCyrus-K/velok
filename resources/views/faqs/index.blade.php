@extends('layouts.vertical', ['title' => 'FAQs'])

@section('css')
<style>
    .content-toolbar > * { flex: 0 1 auto; }
    .content-search-bar { min-width: 220px; }
    .faq-answer {
        white-space: pre-line;
    }
    .faq-question {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    @media (max-width: 767.98px) {
        .content-toolbar > *,
        .content-toolbar .dropdown,
        .content-toolbar .dropdown > .btn,
        .content-toolbar .content-search-bar,
        .content-toolbar .content-search-bar input,
        .content-toolbar .btn-success { width: 100%; }
        .content-toolbar .content-search-bar { margin-left: 0 !important; }
    }
</style>
@endsection

@section('content')
<div class="row">
    @foreach([
        ['label' => 'Total FAQs', 'value' => $summary['total'] ?? 0, 'icon' => 'circle-help', 'class' => 'primary'],
        ['label' => 'Published', 'value' => $summary['published'] ?? 0, 'icon' => 'badge-check', 'class' => 'success'],
        ['label' => 'Drafts', 'value' => $summary['draft'] ?? 0, 'icon' => 'file-clock', 'class' => 'warning'],
        ['label' => 'Archived', 'value' => $summary['archived'] ?? 0, 'icon' => 'archive', 'class' => 'secondary'],
        ['label' => 'Categories', 'value' => $summary['categories'] ?? 0, 'icon' => 'tags', 'class' => 'info'],
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

<div class="card">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 content-toolbar">
            <div>
                <h4 class="mb-1">FAQs</h4>
                <p class="text-muted mb-0">Group common customer questions so answers are easy to scan.</p>
            </div>
            <div class="dropdown">
                <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                    Status: <span id="faq-filter-label">All</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item faq-filter-option" data-filter="all" href="#!">All</a>
                    @foreach($statusOptions as $status => $label)
                        <a class="dropdown-item faq-filter-option" data-filter="{{ $status }}" href="#!">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div class="dropdown">
                <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                    Category: <span id="faq-category-filter-label">All</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item faq-category-filter-option" data-category="all" href="#!">All categories</a>
                    @foreach($faqs->pluck('category')->filter()->unique()->sort()->values() as $category)
                        <a class="dropdown-item faq-category-filter-option" data-category="{{ Str::slug($category) }}" href="#!">{{ Str::headline($category) }}</a>
                    @endforeach
                </div>
            </div>
            <div class="search-bar ms-auto content-search-bar">
                <span style="top: 2px;"><i data-lucide="search"></i></span>
                <input class="form-control form-control-sm" id="faq-search" placeholder="Search FAQs..." type="search" />
            </div>
            <div class="dropdown">
                <a aria-expanded="false" class="dropdown-toggle btn btn-sm btn-outline-light rounded" data-bs-toggle="dropdown" href="#">
                    Sort: <span id="faq-sort-label">Order</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item faq-sort-option" data-sort="order" href="#!">Display Order</a>
                    <a class="dropdown-item faq-sort-option" data-sort="newest" href="#!">Newest First</a>
                    <a class="dropdown-item faq-sort-option" data-sort="question" href="#!">Question</a>
                </div>
            </div>
            <a class="btn btn-sm btn-success" href="{{ route('faqs.create') }}">
                <i class="icon-sm me-1" data-lucide="plus"></i>Add FAQ
            </a>
        </div>

        <div class="row g-xl-4 g-4" id="faq-accordion-grid">
            @forelse($faqs->groupBy(fn ($faq) => $faq->category ?: 'general') as $category => $categoryFaqs)
                @php
                    $sectionSlug = Str::slug($category ?: 'general') ?: 'general';
                    $sectionId = 'faq-accordion-' . $sectionSlug . '-' . $loop->index;
                @endphp
                <div class="col-xl-6"
                     data-faq-category-section
                     data-section-category="{{ $sectionSlug }}">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <h4 class="mb-0 fw-semibold fs-16">{{ Str::headline($category ?: 'general') }}</h4>
                        <span class="badge badge-soft-primary" data-faq-section-count>{{ $categoryFaqs->count() }}</span>
                    </div>
                    <div class="accordion" data-faq-section-list id="{{ $sectionId }}">
                        @foreach($categoryFaqs as $faq)
                            @php
                                $headingId = 'faq-heading-' . $faq->id;
                                $collapseId = 'faq-collapse-' . $faq->id;
                            @endphp
                            <div class="accordion-item"
                                 data-category="{{ Str::slug($faq->category ?: 'general') }}"
                                 data-created="{{ $faq->updated_at?->format('c') ?? '' }}"
                                 data-faq-card
                                 data-order="{{ $faq->display_order }}"
                                 data-question="{{ strtolower($faq->question) }}"
                                 data-search="{{ strtolower(implode(' ', [$faq->reference(), $faq->question, $faq->answer, $faq->category, $faq->statusLabel()])) }}"
                                 data-status="{{ $faq->status }}">
                                <h2 class="accordion-header" id="{{ $headingId }}">
                                    <button aria-controls="{{ $collapseId }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                            class="accordion-button fw-medium {{ $loop->first ? '' : 'collapsed' }}"
                                            data-bs-target="#{{ $collapseId }}"
                                            data-bs-toggle="collapse"
                                            type="button">
                                        <span class="faq-question pe-3">{{ $faq->question }}</span>
                                    </button>
                                </h2>
                                <div aria-labelledby="{{ $headingId }}"
                                     class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                     data-bs-parent="#{{ $sectionId }}"
                                     id="{{ $collapseId }}">
                                    <div class="accordion-body">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                            <span class="badge badge-soft-{{ $faq->statusBadgeClass() }}">{{ $faq->statusLabel() }}</span>
                                            <span class="badge badge-soft-secondary">{{ $faq->reference() }}</span>
                                            <span class="badge badge-soft-info">Order {{ $faq->display_order }}</span>
                                            <small class="text-muted ms-auto">{{ $faq->updated_at?->format('d M, Y') ?? 'N/A' }}</small>
                                        </div>
                                        <p class="text-muted faq-answer mb-3">{{ $faq->answer }}</p>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('faqs.show', $faq) }}" title="View"><i class="align-middle" data-lucide="eye"></i></a>
                                            <a class="btn btn-icon btn-sm btn-soft-secondary" href="{{ route('faqs.edit', $faq) }}" title="Edit"><i class="align-middle" data-lucide="square-pen"></i></a>
                                            @if($faq->status !== \App\Models\Faq::STATUS_PUBLISHED)
                                                <form action="{{ route('faqs.publish', $faq) }}" class="d-inline-flex" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-icon btn-sm btn-soft-success" title="Publish" type="submit"><i class="align-middle" data-lucide="check"></i></button>
                                                </form>
                                            @endif
                                            @if($faq->status !== \App\Models\Faq::STATUS_ARCHIVED)
                                                <form action="{{ route('faqs.archive', $faq) }}" class="d-inline-flex" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-icon btn-sm btn-soft-warning" title="Archive" type="submit"><i class="align-middle" data-lucide="archive"></i></button>
                                                </form>
                                            @endif
                                            <form action="{{ route('faqs.destroy', $faq) }}" class="d-inline-flex ms-auto" data-delete-confirm data-delete-message="Do you want to delete this FAQ?" data-delete-title="Delete FAQ?" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-icon btn-sm btn-soft-danger" title="Delete" type="submit"><i class="align-middle" data-lucide="trash-2"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="col-12" id="faq-empty-state">
                    <div class="text-center py-5">
                        <h5 class="mb-2" id="faq-empty-message">No FAQs yet.</h5>
                        <p class="text-muted mb-0">Add the first FAQ so visitors can get answers faster.</p>
                    </div>
                </div>
            @endforelse

            @if($faqs->isNotEmpty())
                <div class="col-12 d-none" id="faq-empty-state">
                    <div class="text-center py-5">
                        <h5 class="mb-2" id="faq-empty-message">No FAQs match your search.</h5>
                        <p class="text-muted mb-0">Try another search term, status, or category.</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-3">
            <small class="text-muted" id="faq-count">{{ $faqs->count() }} FAQs</small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('faq-search');
        const cards = Array.from(document.querySelectorAll('[data-faq-card]'));
        const sections = Array.from(document.querySelectorAll('[data-faq-category-section]'));
        const emptyState = document.getElementById('faq-empty-state');
        const emptyMessage = document.getElementById('faq-empty-message');
        const countLabel = document.getElementById('faq-count');
        const filterOptions = document.querySelectorAll('.faq-filter-option');
        const categoryOptions = document.querySelectorAll('.faq-category-filter-option');
        const sortOptions = document.querySelectorAll('.faq-sort-option');
        const filterLabel = document.getElementById('faq-filter-label');
        const categoryLabel = document.getElementById('faq-category-filter-label');
        const sortLabel = document.getElementById('faq-sort-label');

        if (!searchInput || !emptyState || !emptyMessage || !countLabel) return;

        const total = cards.length;
        let debounceTimer = null;
        let currentFilter = 'all';
        let currentCategory = 'all';
        let currentSort = 'order';
        const filterLabels = { all: 'All', draft: 'Draft', published: 'Published', archived: 'Archived' };
        const sortLabels = { order: 'Order', newest: 'Newest', question: 'Question' };
        const categoryLabels = { all: 'All' };

        categoryOptions.forEach((option) => { categoryLabels[option.dataset.category] = option.textContent.trim(); });

        const getDate = (card) => new Date(card.dataset.created || 0);
        const sortCards = (cardsToSort) => {
            const sorted = [...cardsToSort];
            if (currentSort === 'newest') sorted.sort((a, b) => getDate(b) - getDate(a));
            if (currentSort === 'question') sorted.sort((a, b) => (a.dataset.question || '').localeCompare(b.dataset.question || ''));
            if (currentSort === 'order') sorted.sort((a, b) => Number(a.dataset.order || 0) - Number(b.dataset.order || 0));
            return sorted;
        };
        const applyFilters = () => {
            const query = searchInput.value.trim().toLowerCase();
            let matchCount = 0;

            cards.forEach((card) => {
                const matches = (query === '' || (card.dataset.search || '').toLowerCase().includes(query))
                    && (currentFilter === 'all' || card.dataset.status === currentFilter)
                    && (currentCategory === 'all' || card.dataset.category === currentCategory);

                card.classList.toggle('d-none', !matches);
                if (matches) matchCount += 1;
            });

            sections.forEach((section) => {
                const sectionList = section.querySelector('[data-faq-section-list]');
                const visibleCards = Array.from(section.querySelectorAll('[data-faq-card]')).filter((card) => !card.classList.contains('d-none'));
                const countBadge = section.querySelector('[data-faq-section-count]');

                sortCards(visibleCards).forEach((card) => sectionList.appendChild(card));
                section.classList.toggle('d-none', visibleCards.length === 0);

                if (countBadge) {
                    countBadge.textContent = visibleCards.length;
                }
            });

            emptyState.classList.toggle('d-none', matchCount > 0);
            emptyMessage.textContent = total === 0 ? 'No FAQs yet.' : 'No FAQs match your search.';
            countLabel.textContent = total === 0 ? '0 FAQs' : `${matchCount} of ${total} FAQs`;
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
            sortLabel.textContent = sortLabels[currentSort] || 'Order';
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
