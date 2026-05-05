@extends('layouts.vertical', ['title' => 'Gallery'])

@section('css')
     @vite(['node_modules/glightbox/dist/css/glightbox.min.css'])
@endsection

@section('content')
<div class="row">
     <div class="col-12">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
               <div>
                    <h4 class="mb-1">Gallery Library</h4>
                    <p class="text-muted mb-0">
                         Live images from the shared <code>kwikshift.gallery</code> table.
                    </p>
               </div>
               <div class="text-muted fw-medium">
                    {{ $galleryItems->count() }} {{ \Illuminate\Support\Str::plural('image', $galleryItems->count()) }}
               </div>
          </div>
     </div>
 </div>

@if ($galleryCategories->isNotEmpty())
<div class="row">
     <div class="col-12">
          <div class="d-flex flex-wrap gap-2 filter-options mb-4">
               <button class="btn btn-sm btn-primary active" data-group="all" type="button">All</button>
               @foreach ($galleryCategories as $category)
                    <button class="btn btn-sm btn-outline-primary" data-group="{{ \Illuminate\Support\Str::slug($category) }}" type="button">
                         {{ $category }}
                    </button>
               @endforeach
          </div>
     </div>
</div>
@endif

<div class="row" data-gallery-grid>
     @forelse ($galleryItems as $item)
          <div class="col-xl-4 col-md-6 picture-item mb-4" data-groups='@json([$item["filter_group"]])'>
               <div class="card h-100 border-0 shadow-sm overflow-hidden">
                    <a class="image-popup d-block position-relative" href="{{ $item['image_url'] }}">
                         <img
                              alt="{{ $item['alt_text'] }}"
                              class="card-img-top"
                              loading="lazy"
                              src="{{ $item['image_url'] }}"
                              style="aspect-ratio: 4 / 3; object-fit: cover;"
                         />
                         <div class="position-absolute top-0 end-0 m-2 d-flex gap-2">
                              <span class="badge bg-dark bg-opacity-75">{{ $item['category'] }}</span>
                              @if ($item['featured'])
                                   <span class="badge text-bg-danger">Featured</span>
                              @endif
                         </div>
                    </a>
                    <div class="card-body">
                         <h5 class="card-title mb-2">{{ $item['title'] }}</h5>
                         <p class="text-muted mb-0">{{ $item['alt_text'] }}</p>
                    </div>
               </div>
          </div>
     @empty
          <div class="col-12">
               <div class="card">
                    <div class="card-body text-center py-5">
                         <h5 class="mb-2">No gallery images found</h5>
                         <p class="text-muted mb-0">
                              Add published records to the shared <code>gallery</code> table and they will appear here automatically.
                         </p>
                    </div>
               </div>
          </div>
     @endforelse
</div>
@endsection

@section('scripts')
     @vite(['resources/js/pages/gallery.js'])
@endsection
