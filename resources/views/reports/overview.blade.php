@extends('layouts.vertical', ['title' => 'Reports Overview'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 bg-light-subtle">
            <div class="card-body p-4">
                <span class="badge badge-soft-primary mb-3">Reports Hub</span>
                <h3 class="mb-2">Reporting Center</h3>
                <p class="text-muted mb-0">These four report pages are built from the current live codebase and data model so the team can move from insight to action faster.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @foreach($reportSummaryCards as $card)
    <div class="col-xl col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <p class="text-dark fw-semibold fs-26 mb-1">{{ $card['value'] }}</p>
                        <p class="card-title mb-1">{{ $card['title'] }}</p>
                        <p class="text-muted mb-0">{{ $card['description'] }}</p>
                    </div>
                    <div class="ms-auto">
                        <span class="btn btn-primary avatar-md rounded-circle d-flex align-items-center justify-content-center">
                            <i data-lucide="{{ $card['icon'] }}" class="fs-5 text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@foreach($reportSections as $section)
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="mb-1">{{ $section['title'] }}</h4>
                <p class="text-muted mb-0">Open a focused report page for this part of the business.</p>
            </div>
        </div>
    </div>

    @foreach($section['reports'] as $report)
    <div class="col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h5 class="mb-1">{{ $report['title'] }}</h5>
                        <p class="text-muted mb-0">{{ $report['description'] }}</p>
                    </div>
                    <span class="btn btn-outline-primary avatar-md rounded-circle d-flex align-items-center justify-content-center">
                        <i data-lucide="{{ $report['icon'] }}" class="fs-5"></i>
                    </span>
                </div>

                <div class="mt-auto">
                    <a href="{{ route('second', ['reports', $report['slug']]) }}" class="btn btn-sm btn-primary">
                        Open Report
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endforeach
@endsection
