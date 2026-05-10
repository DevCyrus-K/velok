@extends('layouts.vertical', ['title' => 'Reports Overview'])

@section('css')
<style>
    .reports-hero-panel {
        min-height: 220px;
    }

    .reports-hero-icon,
    .reports-summary-icon,
    .reports-directory-icon {
        width: 2.75rem;
        height: 2.75rem;
        flex: 0 0 2.75rem;
    }

    .reports-overview-chart {
        min-height: 300px;
    }

    .reports-chart-card .card-body {
        min-height: 350px;
    }

    .reports-category-meter {
        height: .5rem;
    }

    .reports-directory-link {
        color: inherit;
        text-decoration: none;
    }

    .reports-directory-link:hover {
        border-color: var(--bs-primary) !important;
    }
</style>
@endsection

@section('content')
@php
    $allReports = collect($reportSections)->flatMap(fn ($section) => $section['reports'])->values();
    $priorityReports = $allReports->take(3);
@endphp

<div class="row g-3 align-items-stretch">
    <div class="col-xl-8">
        <div class="card border-0 bg-light-subtle h-100 reports-hero-panel">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <span class="badge badge-soft-primary mb-3">Reports Hub</span>
                    <h3 class="mb-2">Reporting Center</h3>
                    <p class="text-muted mb-0">{{ $reportCount }} live report {{ $reportCount === 1 ? 'page is' : 'pages are' }} ready from the current database and connected services.</p>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-primary btn-sm" href="{{ route('reports.download', 'overview') }}">
                        <i class="icon-sm me-1" data-lucide="download"></i>Download PDF
                    </a>
                    @foreach($priorityReports as $report)
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('second', ['reports', $report['slug']]) }}">
                            <i class="icon-sm me-1" data-lucide="{{ $report['icon'] }}"></i>{{ $report['title'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="reports-hero-icon rounded bg-primary-subtle d-flex align-items-center justify-content-center text-primary">
                        <i data-lucide="chart-column"></i>
                    </span>
                    <div>
                        <p class="card-title mb-1">Live Coverage</p>
                        <h3 class="fw-semibold mb-1">{{ $reportCount }}</h3>
                        <p class="text-muted mb-0">Focused report pages across leads, finance, operations, visitors, and email delivery.</p>
                    </div>
                </div>
                <div class="border-top pt-3">
                    @foreach($reportSections as $section)
                        @php
                            $sectionCount = count($section['reports']);
                            $sectionPercent = $reportCount > 0 ? round(($sectionCount / $reportCount) * 100) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                <span class="fw-semibold">{{ $section['title'] }}</span>
                                <span class="text-muted small">{{ $sectionCount }}</span>
                            </div>
                            <div class="progress reports-category-meter" role="progressbar" aria-label="{{ $section['title'] }} report share" aria-valuenow="{{ $sectionPercent }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: {{ $sectionPercent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    @foreach($reportSummaryCards as $card)
    <div class="col-xxl-2 col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="min-w-0">
                        <p class="card-title mb-1">{{ $card['title'] }}</p>
                        <h4 class="fw-semibold text-dark mb-1">{{ $card['value'] }}</h4>
                        <p class="text-muted small mb-0">{{ $card['description'] }}</p>
                    </div>
                    <span class="reports-summary-icon rounded bg-light d-flex align-items-center justify-content-center text-primary">
                        <i data-lucide="{{ $card['icon'] }}"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card h-100 reports-chart-card">
            <div class="card-header d-flex align-items-start justify-content-between gap-3">
                <div>
                    <h4 class="card-title mb-1">Data Activity</h4>
                    <p class="text-muted mb-0">Records currently powering the reporting center.</p>
                </div>
                <span class="badge badge-soft-primary">Database</span>
            </div>
            <div class="card-body">
                <div id="reports-activity-chart" class="apex-charts reports-overview-chart"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100 reports-chart-card">
            <div class="card-header">
                <h4 class="card-title mb-1">Report Mix</h4>
                <p class="text-muted mb-0">Coverage by business area.</p>
            </div>
            <div class="card-body">
                <div id="reports-category-chart" class="apex-charts reports-overview-chart"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-1">
            <div>
                <h4 class="mb-1">Report Directory</h4>
                <p class="text-muted mb-0">Open the exact view needed for follow-up, revenue, traffic, or delivery checks.</p>
            </div>
        </div>
    </div>

    @foreach($reportSections as $section)
    <div class="col-xl-4 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1">{{ $section['title'] }}</h5>
                <p class="text-muted mb-0">{{ count($section['reports']) }} report{{ count($section['reports']) === 1 ? '' : 's' }}</p>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @foreach($section['reports'] as $report)
                        <a class="reports-directory-link border rounded p-3" href="{{ route('second', ['reports', $report['slug']]) }}">
                            <span class="d-flex align-items-start gap-3">
                                <span class="reports-directory-icon rounded bg-light d-flex align-items-center justify-content-center text-primary">
                                    <i data-lucide="{{ $report['icon'] }}"></i>
                                </span>
                                <span class="d-block min-w-0">
                                    <span class="fw-semibold text-dark d-block mb-1">{{ $report['title'] }}</span>
                                    <span class="text-muted small d-block">{{ $report['description'] }}</span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const charts = @json($reportOverviewCharts ?? []);

        if (typeof ApexCharts === 'undefined') {
            return;
        }

        const colors = ['#3b82f6', '#22b956', '#f59e0b', '#f95c5c', '#64748b'];
        const activityElement = document.getElementById('reports-activity-chart');
        const categoryElement = document.getElementById('reports-category-chart');
        const activity = charts.activity || { labels: [], series: [] };
        const categories = charts.categories || { labels: [], series: [] };
        const categorySeries = Array.isArray(categories.series) ? categories.series.map((value) => Number(value) || 0) : [];
        const hasCategoryData = categorySeries.some((value) => value > 0);

        if (activityElement) {
            new ApexCharts(activityElement, {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Records',
                    data: Array.isArray(activity.series) ? activity.series.map((value) => Number(value) || 0) : []
                }],
                colors: ['#3b82f6'],
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '38%'
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -18,
                    style: {
                        colors: ['#304758'],
                        fontSize: '12px'
                    }
                },
                xaxis: {
                    categories: activity.labels || [],
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true
                },
                grid: {
                    borderColor: '#f1f3fa',
                    strokeDashArray: 4
                }
            }).render();
        }

        if (categoryElement) {
            new ApexCharts(categoryElement, {
                chart: {
                    type: 'donut',
                    height: 285,
                    toolbar: {
                        show: false
                    }
                },
                series: hasCategoryData ? categorySeries : [1],
                labels: hasCategoryData ? (categories.labels || []) : ['No reports'],
                colors: hasCategoryData ? colors : ['#94a3b8'],
                stroke: {
                    width: 0
                },
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%'
                        }
                    }
                }
            }).render();
        }
    });
</script>
@endsection
