@extends('layouts.vertical', ['title' => $report['title']])

@php
    $table = $report['table'] ?? null;
    $hasActions = $table ? collect($table['rows'] ?? [])->contains(fn ($row) => !empty($row['actions'] ?? [])) : false;
@endphp

@section('css')
<style>
    .report-hero-copy {
        max-width: 760px;
    }

    .report-top-card .card-body {
        min-height: 170px;
    }

    .report-card-sparkline {
        min-height: 50px;
    }

    .report-chart {
        min-height: 320px;
    }

    .report-chart-short {
        min-height: 280px;
    }

    .report-table-toolbar .form-control,
    .report-table-toolbar .form-select {
        min-height: 44px;
    }

    .report-empty-row td {
        padding-block: 3rem;
    }

    .report-cell-stack small {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-0 bg-light-subtle">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="report-hero-copy">
                        <span class="badge badge-soft-primary mb-3">{{ $report['badge'] ?? 'Report' }}</span>
                        <h3 class="mb-2">{{ $report['title'] }}</h3>
                        <p class="text-muted mb-0">{{ $report['subtitle'] ?? '' }}</p>
                    </div>
                    <a href="{{ route('second', ['reports', 'overview']) }}" class="btn btn-outline-secondary btn-sm">
                        All Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($report['cards']))
<div class="row">
    @foreach($report['cards'] as $index => $card)
    <div class="col-xl-3 col-md-6">
        <div class="card report-top-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-3 card-title">{{ $card['title'] }}</p>
                        <h4 class="fw-bold d-flex align-items-center gap-2 mb-0 {{ $card['value_class'] ?? '' }}">{{ $card['value'] }}</h4>
                    </div>
                    <div>
                        <i data-lucide="{{ $card['icon'] }}" class="fs-32 text-primary"></i>
                    </div>
                </div>
                @if(!empty($card['sparkline']))
                <div class="row align-items-center mt-4">
                    <div class="col-12">
                        <div id="report-card-sparkline-{{ $index }}" class="apex-charts report-card-sparkline"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if(!empty($report['charts']))
<div class="row">
    @foreach($report['charts'] as $chart)
    <div class="{{ $chart['col_class'] ?? 'col-12' }}">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-1">{{ $chart['title'] }}</h4>
                <p class="text-muted mb-0">{{ $chart['description'] }}</p>
            </div>
            <div class="card-body">
                <div id="{{ $chart['key'] }}" class="apex-charts {{ in_array($chart['type'], ['bar-vertical'], true) ? 'report-chart-short' : 'report-chart' }}"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if(!empty($report['insights']))
<div class="row">
    @foreach($report['insights'] as $insight)
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <span class="text-muted d-block mb-2">{{ $insight['label'] }}</span>
                <h5 class="mb-2">{{ $insight['value'] }}</h5>
                <p class="text-muted mb-0">{{ $insight['note'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($table)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="card-title mb-1">{{ $table['title'] }}</h4>
                    <p class="text-muted mb-0">{{ $table['description'] }}</p>
                </div>
                <span class="badge badge-soft-primary">{{ $report['badge'] ?? 'Report' }}</span>
            </div>

            <div class="card-body border-bottom">
                <div class="row g-3 report-table-toolbar">
                    <div class="col-xl-4 col-lg-6">
                        <label for="reportSearch" class="form-label">Search</label>
                        <input type="search" id="reportSearch" class="form-control" placeholder="{{ $table['search_placeholder'] ?? 'Search report' }}">
                    </div>

                    @foreach($table['filters'] as $filter)
                    <div class="col-xl-2 col-lg-6">
                        <label for="reportFilter{{ ucfirst($filter['id']) }}" class="form-label">{{ $filter['label'] }}</label>
                        <select
                            id="reportFilter{{ ucfirst($filter['id']) }}"
                            class="form-select"
                            data-report-filter
                            data-filter-dataset="{{ $filter['dataset'] }}"
                        >
                            <option value="all">{{ $filter['all_label'] }}</option>
                            @foreach($filter['options'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach

                    <div class="col-xl-2 col-lg-6">
                        <label for="reportSort" class="form-label">Sort</label>
                        <select id="reportSort" class="form-select">
                            @foreach($table['sorts'] as $sort)
                            <option value="{{ $sort['value'] }}">{{ $sort['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span id="reportCountBadge" class="badge badge-soft-dark">Showing {{ count($table['rows']) }} of {{ count($table['rows']) }} rows</span>
                        <span class="text-muted small">Filters, sorting, and pagination run instantly on the page.</span>
                    </div>
                    <button type="button" id="reportClearFilters" class="btn btn-light btn-sm">Clear Filters</button>
                </div>
            </div>

            <div>
                <div class="table-responsive table-centered">
                    <table class="table table-striped text-nowrap mb-0">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                @foreach($table['columns'] as $column)
                                <th class="border-0 py-2 text-dark">{{ $column['label'] }}</th>
                                @endforeach
                                @if($hasActions)
                                <th class="border-0 py-2 text-dark text-end">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            @forelse($table['rows'] as $row)
                            <tr
                                data-report-row
                                @foreach(($row['datasets'] ?? []) as $datasetKey => $datasetValue)
                                data-{{ \Illuminate\Support\Str::kebab($datasetKey) }}="{{ $datasetValue }}"
                                @endforeach
                            >
                                @foreach($table['columns'] as $column)
                                @php
                                    $cell = $row['cells'][$column['key']] ?? ['type' => 'text', 'text' => ''];
                                @endphp
                                <td>
                                    @if(($cell['type'] ?? 'text') === 'stack')
                                    <div class="report-cell-stack">
                                        <div>{{ $cell['primary'] }}</div>
                                        @if(!empty($cell['secondary']))
                                        <small class="text-muted">{{ $cell['secondary'] }}</small>
                                        @endif
                                    </div>
                                    @elseif(($cell['type'] ?? 'text') === 'badge')
                                    <span class="badge badge-soft-{{ $cell['class'] }}">{{ $cell['label'] }}</span>
                                    @else
                                    {{ $cell['text'] ?? '' }}
                                    @endif
                                </td>
                                @endforeach
                                @if($hasActions)
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                        @foreach(($row['actions'] ?? []) as $action)
                                        <a class="btn btn-icon btn-sm {{ $action['class'] }}" href="{{ $action['url'] }}" title="{{ $action['label'] }}">
                                            <i data-lucide="{{ $action['icon'] }}" class="align-middle"></i>
                                        </a>
                                        @endforeach
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr class="report-empty-row">
                                <td colspan="{{ count($table['columns']) + ($hasActions ? 1 : 0) }}" class="text-center text-muted">No report rows are available yet.</td>
                            </tr>
                            @endforelse
                            @if(!empty($table['rows']))
                            <tr id="reportEmptyRow" class="report-empty-row d-none">
                                <td colspan="{{ count($table['columns']) + ($hasActions ? 1 : 0) }}" class="text-center text-muted">No rows match your current search or filters.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="align-items-center justify-content-between row g-0 text-center text-sm-start p-3 border-top">
                    <div class="col-sm">
                        <div class="text-muted" id="reportPaginationCount">Showing {{ count($table['rows']) }} of {{ count($table['rows']) }} rows</div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        <ul class="pagination justify-content-end mb-0" id="reportPagination"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const report = @json($report);
        const colors = {
            primary: '#4d5761',
            accent: '#3b82f6',
            success: '#22b956',
            warning: '#f59e0b',
            danger: '#f95c5c',
            border: '#f1f3fa',
            muted: '#64748b'
        };

        const normalize = (value) => (value || '').toString().trim().toLowerCase();
        const formatValue = (value, prefix = '', suffix = '') => prefix + value + suffix;

        const renderSparkline = (element, card) => {
            if (!element || typeof ApexCharts === 'undefined' || !Array.isArray(card.sparkline) || card.sparkline.length === 0) {
                return;
            }

            const options = {
                chart: {
                    type: card.sparkline_type || 'area',
                    height: 50,
                    sparkline: {
                        enabled: true
                    }
                },
                series: [{
                    data: card.sparkline.map((value) => Number(value) || 0)
                }],
                stroke: {
                    width: 0,
                    curve: 'smooth'
                },
                markers: {
                    size: 0
                },
                colors: [card.sparkline_color || colors.primary],
                tooltip: {
                    fixed: {
                        enabled: false
                    },
                    x: {
                        show: false
                    },
                    y: {
                        title: {
                            formatter: function () {
                                return '';
                            }
                        }
                    },
                    marker: {
                        show: false
                    }
                }
            };

            if ((card.sparkline_type || 'area') === 'area') {
                options.fill = {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        opacityFrom: 0.9,
                        opacityTo: 0.3,
                        stops: [0, 100]
                    }
                };
            }

            if (card.sparkline_type === 'bar') {
                options.plotOptions = {
                    bar: {
                        borderRadius: 3,
                        columnWidth: '30%'
                    }
                };
            }

            new ApexCharts(element, options).render();
        };

        const renderChart = (chart) => {
            const element = document.getElementById(chart.key);

            if (!element || typeof ApexCharts === 'undefined') {
                return;
            }

            if (chart.type === 'donut') {
                const series = Array.isArray(chart.series) ? chart.series.map((value) => Number(value) || 0) : [0];
                const hasData = series.some((value) => value > 0);

                new ApexCharts(element, {
                    chart: {
                        type: 'donut',
                        height: 320,
                        toolbar: {
                            show: false
                        }
                    },
                    series: hasData ? series : [1],
                    labels: hasData ? chart.labels : ['No activity'],
                    colors: hasData ? (chart.colors || [colors.primary]) : [colors.muted],
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
                    },
                    tooltip: {
                        y: {
                            formatter: function (value) {
                                return formatValue(value, chart.value_prefix || '', chart.value_suffix || '');
                            }
                        }
                    }
                }).render();

                return;
            }

            if (chart.type === 'area') {
                new ApexCharts(element, {
                    chart: {
                        type: 'area',
                        height: 320,
                        toolbar: {
                            show: false
                        }
                    },
                    series: chart.series,
                    colors: chart.colors || [colors.accent],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.36,
                            opacityTo: 0.05,
                            stops: [0, 95, 100]
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 4,
                        strokeWidth: 0
                    },
                    xaxis: {
                        categories: chart.categories || [],
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        min: 0,
                        forceNiceScale: true,
                        labels: {
                            formatter: function (value) {
                                return formatValue(Math.round(value), chart.value_prefix || '', chart.value_suffix || '');
                            }
                        }
                    },
                    grid: {
                        borderColor: colors.border,
                        strokeDashArray: 4
                    },
                    tooltip: {
                        y: {
                            formatter: function (value) {
                                return formatValue(value, chart.value_prefix || '', chart.value_suffix || '');
                            }
                        }
                    }
                }).render();

                return;
            }

            const horizontal = chart.type === 'bar-horizontal';

            new ApexCharts(element, {
                chart: {
                    type: 'bar',
                    height: horizontal ? 320 : 280,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal,
                        borderRadius: 6,
                        barHeight: horizontal ? '48%' : undefined,
                        columnWidth: horizontal ? undefined : '30%',
                        dataLabels: {
                            position: horizontal ? 'center' : 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (value) {
                        return formatValue(value, chart.value_prefix || '', chart.value_suffix || '');
                    },
                    offsetY: horizontal ? 0 : -20,
                    style: {
                        fontSize: '12px',
                        colors: ['#304758']
                    }
                },
                series: chart.series,
                colors: chart.colors || [colors.primary],
                xaxis: {
                    categories: chart.categories || []
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true
                },
                grid: {
                    borderColor: colors.border,
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return formatValue(value, chart.value_prefix || '', chart.value_suffix || '');
                        }
                    }
                }
            }).render();
        };

        (report.cards || []).forEach(function (card, index) {
            renderSparkline(document.getElementById('report-card-sparkline-' + index), card);
        });

        (report.charts || []).forEach(renderChart);

        const table = report.table;
        const tableBody = document.getElementById('reportTableBody');

        if (!table || !tableBody) {
            return;
        }

        const rows = Array.from(tableBody.querySelectorAll('[data-report-row]'));
        const emptyRow = document.getElementById('reportEmptyRow');
        const searchInput = document.getElementById('reportSearch');
        const filterSelects = Array.from(document.querySelectorAll('[data-report-filter]'));
        const sortSelect = document.getElementById('reportSort');
        const clearButton = document.getElementById('reportClearFilters');
        const countBadge = document.getElementById('reportCountBadge');
        const paginationCount = document.getElementById('reportPaginationCount');
        const pagination = document.getElementById('reportPagination');
        const sortMap = Object.fromEntries((table.sorts || []).map((sort) => [sort.value, sort]));

        const state = {
            search: '',
            sort: table.sorts?.[0]?.value || 'newest',
            page: 1,
            perPage: 8,
            filters: Object.fromEntries((table.filters || []).map((filter) => [filter.dataset, 'all']))
        };

        const compareValues = (first, second, type) => {
            if (type === 'number') {
                return (Number(first) || 0) - (Number(second) || 0);
            }

            return (first || '').toString().localeCompare((second || '').toString(), undefined, { sensitivity: 'base' });
        };

        const renderPagination = (totalPages) => {
            if (!pagination) {
                return;
            }

            pagination.innerHTML = '';

            const createItem = ({ label, page, disabled = false, active = false, icon = null }) => {
                const item = document.createElement('li');
                item.className = 'page-item';

                if (disabled) {
                    item.classList.add('disabled');
                }

                if (active) {
                    item.classList.add('active');
                }

                const link = document.createElement('a');
                link.className = 'page-link';
                link.href = 'javascript:void(0);';
                link.innerHTML = icon
                    ? '<iconify-icon class="fs-18" icon="lucide:' + icon + '"></iconify-icon>'
                    : String(label);

                if (!disabled && !active) {
                    link.addEventListener('click', function () {
                        state.page = page;
                        applyFilters();
                    });
                }

                item.appendChild(link);
                pagination.appendChild(item);
            };

            const current = Math.min(state.page, totalPages);
            const start = Math.max(1, current - 2);
            const end = Math.min(totalPages, start + 4);

            createItem({ page: current - 1, disabled: current <= 1, icon: 'chevron-left' });

            for (let page = start; page <= end; page += 1) {
                createItem({ label: page, page, active: page === current });
            }

            createItem({ page: current + 1, disabled: current >= totalPages, icon: 'chevron-right' });
        };

        const applyFilters = () => {
            const filteredRows = rows.filter(function (row) {
                const matchesSearch = state.search === '' || normalize(row.dataset.search).includes(state.search);
                const matchesFilters = Object.entries(state.filters).every(function ([dataset, value]) {
                    return value === 'all' || normalize(row.dataset[dataset]) === value;
                });

                return matchesSearch && matchesFilters;
            });

            const sortConfig = sortMap[state.sort] || table.sorts?.[0];
            const sortedRows = [...filteredRows].sort(function (firstRow, secondRow) {
                if (!sortConfig) {
                    return 0;
                }

                const comparison = compareValues(firstRow.dataset[sortConfig.dataset], secondRow.dataset[sortConfig.dataset], sortConfig.type);
                return sortConfig.direction === 'desc' ? (comparison * -1) : comparison;
            });

            const filteredSet = new Set(sortedRows);
            const hiddenRows = rows.filter((row) => !filteredSet.has(row));
            const totalFiltered = sortedRows.length;
            const totalPages = Math.max(1, Math.ceil(totalFiltered / state.perPage));

            if (state.page > totalPages) {
                state.page = totalPages;
            }

            const startIndex = totalFiltered === 0 ? 0 : (state.page - 1) * state.perPage;
            const pageRows = sortedRows.slice(startIndex, startIndex + state.perPage);
            const pageSet = new Set(pageRows);

            tableBody.append(...sortedRows, ...hiddenRows);

            rows.forEach(function (row) {
                row.classList.toggle('d-none', !pageSet.has(row));
            });

            if (emptyRow) {
                tableBody.append(emptyRow);
                emptyRow.classList.toggle('d-none', totalFiltered > 0);
            }

            const firstVisible = totalFiltered === 0 ? 0 : startIndex + 1;
            const lastVisible = totalFiltered === 0 ? 0 : startIndex + pageRows.length;
            const label = totalFiltered === 0
                ? 'Showing 0 of ' + rows.length + ' rows'
                : 'Showing ' + firstVisible + '-' + lastVisible + ' of ' + totalFiltered + ' rows';

            if (countBadge) {
                countBadge.textContent = label;
            }

            if (paginationCount) {
                paginationCount.textContent = label;
            }

            renderPagination(totalPages);
        };

        const debounce = (callback, delay = 220) => {
            let timeoutId;

            return function (...args) {
                window.clearTimeout(timeoutId);
                timeoutId = window.setTimeout(function () {
                    callback.apply(null, args);
                }, delay);
            };
        };

        const debouncedSearch = debounce(function () {
            state.page = 1;
            applyFilters();
        });

        if (searchInput) {
            searchInput.addEventListener('input', function (event) {
                state.search = normalize(event.target.value);
                debouncedSearch();
            });
        }

        filterSelects.forEach(function (select) {
            select.addEventListener('change', function (event) {
                const dataset = event.target.dataset.filterDataset;
                state.filters[dataset] = event.target.value;
                state.page = 1;
                applyFilters();
            });
        });

        if (sortSelect) {
            sortSelect.addEventListener('change', function (event) {
                state.sort = event.target.value;
                state.page = 1;
                applyFilters();
            });
        }

        if (clearButton) {
            clearButton.addEventListener('click', function () {
                state.search = '';
                state.sort = table.sorts?.[0]?.value || 'newest';
                state.page = 1;

                Object.keys(state.filters).forEach(function (key) {
                    state.filters[key] = 'all';
                });

                if (searchInput) {
                    searchInput.value = '';
                }

                filterSelects.forEach(function (select) {
                    select.value = 'all';
                });

                if (sortSelect) {
                    sortSelect.value = state.sort;
                }

                applyFilters();
            });
        }

        applyFilters();
    });
</script>
@endsection
