@extends('layouts.vertical', ['title' => 'Email Delivery Report'])

@section('css')
<style>
    .email-report-top-card .card-body {
        min-height: 170px;
    }

    .email-report-top-chart {
        min-height: 50px;
    }

    .email-report-chart {
        min-height: 320px;
    }

    .email-report-toolbar .form-control,
    .email-report-toolbar .form-select {
        min-height: 44px;
    }

    .email-report-table .action-cell {
        min-width: 130px;
    }

    .email-report-table {
        min-width: 940px;
    }

    .email-report-table-wrap {
        max-height: clamp(440px, 64vh, 760px);
        overflow: auto;
    }

    .email-report-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--bs-body-bg);
        box-shadow: inset 0 -1px 0 var(--bs-border-color);
    }

    .email-report-empty-row td {
        padding-block: 3rem;
    }

    .email-log-detail-value,
    .email-log-response {
        white-space: pre-wrap;
        word-break: break-word;
    }

    @media (max-width: 767.98px) {
        .email-report-toolbar .form-control,
        .email-report-toolbar .form-select,
        .email-report-toolbar button {
            width: 100%;
        }

        .email-report-table-wrap {
            max-height: 62vh;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card email-report-top-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-3 card-title">Total Logs</p>
                        <h4 class="fw-bold text-primary d-flex align-items-center gap-2 mb-0">{{ $emailDeliverySummary['total'] }}</h4>
                    </div>
                    <div>
                        <i data-lucide="mail" class="fs-32 text-primary"></i>
                    </div>
                </div>
                <div class="row align-items-center mt-4">
                    <div class="col-12">
                        <div id="email-total-sparkline" class="apex-charts email-report-top-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card email-report-top-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-3 card-title">Sent</p>
                        <h4 class="fw-bold text-success d-flex align-items-center gap-2 mb-0">{{ $emailDeliverySummary['sent'] }}</h4>
                    </div>
                    <div>
                        <i data-lucide="mail-check" class="fs-32 text-primary"></i>
                    </div>
                </div>
                <div class="row align-items-center mt-4">
                    <div class="col-12">
                        <div id="email-sent-sparkline" class="apex-charts email-report-top-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card email-report-top-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-3 card-title">Failed</p>
                        <h4 class="fw-bold text-danger d-flex align-items-center gap-2 mb-0">{{ $emailDeliverySummary['failed'] }}</h4>
                    </div>
                    <div>
                        <i data-lucide="shield-alert" class="fs-32 text-primary"></i>
                    </div>
                </div>
                <div class="row align-items-center mt-4">
                    <div class="col-12">
                        <div id="email-failed-sparkline" class="apex-charts email-report-top-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card email-report-top-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-3 card-title">Pending</p>
                        <h4 class="fw-bold text-warning d-flex align-items-center gap-2 mb-0">{{ $emailDeliverySummary['pending'] }}</h4>
                    </div>
                    <div>
                        <i data-lucide="clock-3" class="fs-32 text-primary"></i>
                    </div>
                </div>
                <div class="row align-items-center mt-4">
                    <div class="col-12">
                        <div id="email-pending-sparkline" class="apex-charts email-report-top-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xxl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-1">Delivery Trend</h4>
                    <p class="text-muted mb-0">Track the last 7 days of live email movement without leaving the page.</p>
                </div>
                <span class="badge badge-soft-primary">Source: email_delivery_logs</span>
            </div>
            <div class="card-body">
                <div id="email-delivery-trend-chart" class="apex-charts email-report-chart"></div>
            </div>
        </div>
    </div>

    <div class="col-xxl-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-1">Status Breakdown</h4>
                <p class="text-muted mb-0">Quick status split of all tracked email logs.</p>
            </div>
            <div class="card-body">
                <div id="email-delivery-status-chart" class="apex-charts email-report-chart"></div>
                <div class="row text-center mt-3">
                    <div class="col-4">
                        <p class="text-muted mb-1">Sent</p>
                        <h5 class="mb-0 text-success">{{ $emailDeliverySummary['sent'] }}</h5>
                    </div>
                    <div class="col-4">
                        <p class="text-muted mb-1">Failed</p>
                        <h5 class="mb-0 text-danger">{{ $emailDeliverySummary['failed'] }}</h5>
                    </div>
                    <div class="col-4">
                        <p class="text-muted mb-1">Pending</p>
                        <h5 class="mb-0 text-warning">{{ $emailDeliverySummary['pending'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xxl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-1">Purpose Performance</h4>
                    <p class="text-muted mb-0">Find which email journeys are generating the most activity.</p>
                </div>
                <span class="badge badge-soft-secondary">{{ $emailDeliveryInsights['top_purpose'] }}</span>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <p class="text-muted mb-0">See the busiest email flows at a glance and spot where follow-up is needed.</p>
                </div>
                <div id="email-delivery-purpose-chart" class="apex-charts email-report-chart"></div>
            </div>
        </div>
    </div>

    <div class="col-xxl-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-1">Direction Overview</h4>
                <p class="text-muted mb-0">Compare where delivery activity is happening right now.</p>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="border rounded-3 p-3 h-100">
                            <span class="text-muted d-block mb-1">Latest Activity</span>
                            <span class="fw-semibold">{{ $emailDeliveryInsights['latest_activity'] }}</span>
                            <small class="text-muted d-block mt-1">{{ $emailDeliveryInsights['latest_activity_at'] }}</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded-3 p-3 h-100">
                            <span class="text-muted d-block mb-1">Success Rate</span>
                            <span class="fw-semibold">{{ $emailDeliveryInsights['success_rate'] }}%</span>
                            <small class="text-muted d-block mt-1">Failure rate {{ $emailDeliveryInsights['failure_rate'] }}%</small>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-muted mb-0">Admin and client direction counts are plotted using the same bar style from the dashboard inquiries card.</p>
                </div>
                <div id="email-delivery-direction-chart" class="apex-charts email-report-chart"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="card-title mb-1">Email Delivery Logs</h4>
                    <p class="text-muted mb-0">Search, filter, sort, paginate, and inspect full email log details from one place.</p>
                </div>
                <span class="badge badge-soft-primary">Source: email_delivery_logs</span>
            </div>

            <div class="card-body border-bottom">
                <div class="row g-3 email-report-toolbar">
                    <div class="col-xl-4 col-lg-6">
                        <label for="emailDeliverySearch" class="form-label">Search Logs</label>
                        <input type="search" id="emailDeliverySearch" class="form-control" placeholder="Search email, purpose, status, transport, error details">
                    </div>

                    <div class="col-xl-2 col-lg-6">
                        <label for="emailDeliveryStatusFilter" class="form-label">Status</label>
                        <select id="emailDeliveryStatusFilter" class="form-select">
                            <option value="all">All statuses</option>
                            @foreach($emailDeliveryFilterOptions['statuses'] as $status)
                            <option value="{{ \Illuminate\Support\Str::lower($status) }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-6">
                        <label for="emailDeliveryPurposeFilter" class="form-label">Purpose</label>
                        <select id="emailDeliveryPurposeFilter" class="form-select">
                            <option value="all">All purposes</option>
                            @foreach($emailDeliveryFilterOptions['purposes'] as $purpose)
                            <option value="{{ \Illuminate\Support\Str::lower($purpose) }}">{{ $purpose }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-6">
                        <label for="emailDeliveryDirectionFilter" class="form-label">Direction</label>
                        <select id="emailDeliveryDirectionFilter" class="form-select">
                            <option value="all">All directions</option>
                            @foreach($emailDeliveryFilterOptions['directions'] as $direction)
                            <option value="{{ \Illuminate\Support\Str::lower($direction) }}">{{ $direction }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-6">
                        <label for="emailDeliverySort" class="form-label">Sort</label>
                        <select id="emailDeliverySort" class="form-select">
                            <option value="newest">Newest first</option>
                            <option value="oldest">Oldest first</option>
                            <option value="email_asc">Email A-Z</option>
                            <option value="status_asc">Status A-Z</option>
                            <option value="purpose_asc">Purpose A-Z</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span id="emailDeliveryCount" class="badge badge-soft-dark">Showing {{ $emailDeliveryReportData['totalRows'] }} of {{ $emailDeliveryReportData['totalRows'] }} logs</span>
                        <span class="text-muted small">Search stays fast with debounced filtering.</span>
                    </div>
                    <button type="button" id="emailDeliveryClearFilters" class="btn btn-light btn-sm">Clear Filters</button>
                </div>
            </div>

            <div>
                <div class="table-responsive table-centered email-report-table-wrap">
                    <table class="table table-striped text-nowrap mb-0 email-report-table">
                        <thead class="text-uppercase fs-12">
                            <tr>
                                <th class="border-0 py-2 text-dark">Date</th>
                                <th class="border-0 py-2 text-dark">Email</th>
                                <th class="border-0 py-2 text-dark">Purpose</th>
                                <th class="border-0 py-2 text-dark">Status</th>
                                <th class="border-0 py-2 text-dark">Direction</th>
                                <th class="border-0 py-2 text-dark">Transport</th>
                                <th class="border-0 py-2 text-dark text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="emailDeliveryTableBody">
                            @forelse($emailDeliveryReportData['tableRows'] as $row)
                            <tr
                                data-log-row
                                data-date-sort="{{ $row['date_sort'] }}"
                                data-status="{{ \Illuminate\Support\Str::lower($row['status']) }}"
                                data-purpose="{{ \Illuminate\Support\Str::lower($row['purpose']) }}"
                                data-direction="{{ \Illuminate\Support\Str::lower($row['direction']) }}"
                                data-email="{{ \Illuminate\Support\Str::lower($row['email']) }}"
                                data-search="{{ \Illuminate\Support\Str::lower(implode(' ', array_filter([$row['email'], $row['purpose'], $row['status'], $row['direction'], $row['transport'], $row['subject'], $row['response_message']]))) }}"
                            >
                                <td>
                                    {{ $row['date_label'] }}
                                    <small class="text-muted d-block">{{ $row['time_label'] }}</small>
                                </td>
                                <td>
                                    @if($row['email'] !== '')
                                    <a class="text-decoration-none" href="mailto:{{ $row['email'] }}">{{ $row['email'] }}</a>
                                    @else
                                    <span class="text-muted">No email recorded</span>
                                    @endif
                                </td>
                                <td>{{ $row['purpose'] }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $row['status_badge_class'] }}">{{ $row['status'] }}</span>
                                </td>
                                <td>{{ $row['direction'] }}</td>
                                <td>{{ $row['transport'] }}</td>
                                <td class="text-end action-cell">
                                    <div class="d-flex flex-wrap justify-content-end gap-1">
                                        <button type="button" class="btn btn-icon btn-sm btn-soft-primary" data-email-log-view data-log-id="{{ $row['id'] }}" title="View">
                                            <i class="align-middle" data-lucide="eye"></i>
                                        </button>
                                        <form action="{{ route('reports.email-delivery.destroy', $row['id']) }}" method="POST" data-delete-confirm data-delete-message="Do you want to delete this email delivery log?" data-delete-title="Delete email log?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-soft-danger" title="Delete">
                                                <i class="align-middle" data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr class="email-report-empty-row">
                                <td colspan="7" class="text-center text-muted">No email delivery logs are available yet.</td>
                            </tr>
                            @endforelse
                            @if($emailDeliveryReportData['totalRows'] > 0)
                            <tr id="emailDeliveryEmptyRow" class="email-report-empty-row d-none">
                                <td colspan="7" class="text-center text-muted">No email logs match your current search or filters.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="align-items-center justify-content-between row g-0 text-center text-sm-start p-3 border-top">
                    <div class="col-sm">
                        <div class="text-muted" id="emailDeliveryPaginationCount">Showing {{ $emailDeliveryReportData['totalRows'] }} of {{ $emailDeliveryReportData['totalRows'] }} logs</div>
                    </div>
                    <div class="col-sm-auto mt-3 mt-sm-0">
                        <ul class="pagination justify-content-end mb-0" id="emailDeliveryPagination"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailDeliveryDetailsModal" tabindex="-1" aria-hidden="true" aria-labelledby="emailDeliveryDetailsModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="emailDeliveryDetailsModalLabel">Email Log Details</h5>
                    <small class="text-muted" id="emailDeliveryModalReference">Log reference</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <span class="text-muted d-block mb-1">Date</span>
                            <span class="fw-semibold" id="emailDeliveryModalDate">N/A</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <span class="text-muted d-block mb-1">Status</span>
                            <span id="emailDeliveryModalStatus" class="badge badge-soft-secondary">N/A</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <span class="text-muted d-block mb-1">Recipient Email</span>
                            <span class="fw-semibold email-log-detail-value" id="emailDeliveryModalEmail">N/A</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <span class="text-muted d-block mb-1">Purpose</span>
                            <span class="fw-semibold" id="emailDeliveryModalPurpose">N/A</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <span class="text-muted d-block mb-1">Direction</span>
                            <span class="fw-semibold" id="emailDeliveryModalDirection">N/A</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <span class="text-muted d-block mb-1">Transport</span>
                            <span class="fw-semibold" id="emailDeliveryModalTransport">N/A</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="mb-2">Subject</h6>
                    <div class="border rounded-3 p-3 email-log-detail-value" id="emailDeliveryModalSubject">No subject recorded.</div>
                </div>

                <div>
                    <h6 class="mb-2">Full Error / Response Details</h6>
                    <div class="border rounded-3 p-3 email-log-response" id="emailDeliveryModalResponse">No response or error details recorded.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const reportData = @json($emailDeliveryReportData);
        const chartColors = {
            primary: '#4d5761',
            success: '#22B956',
            danger: '#f95c5c',
            warning: '#f59e0b',
            accent: '#3b82f6',
            border: '#f1f3fa'
        };

        const normalize = (value) => (value || '').toString().trim().toLowerCase();

        const buildSparklineChart = (elementId, data, type, color) => {
            const chartElement = document.getElementById(elementId);

            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            const options = {
                chart: {
                    type,
                    height: 50,
                    sparkline: {
                        enabled: true
                    }
                },
                series: [{
                    data: Array.isArray(data) && data.length > 0 ? data.map((value) => Number(value) || 0) : [0]
                }],
                stroke: {
                    width: 0,
                    curve: 'smooth'
                },
                markers: {
                    size: 0
                },
                colors: [color],
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

            if (type === 'area') {
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

            if (type === 'bar') {
                options.plotOptions = {
                    bar: {
                        borderRadius: 3,
                        columnWidth: '30%'
                    }
                };
            }

            new ApexCharts(chartElement, options).render();
        };

        const buildStatusChart = () => {
            const chartElement = document.getElementById('email-delivery-status-chart');

            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            const rawSeries = reportData.charts.status.series.map((value) => Number(value) || 0);
            const hasData = rawSeries.some((value) => value > 0);
            const series = hasData ? rawSeries : [1];
            const labels = hasData ? reportData.charts.status.labels : ['No activity'];
            const statusPalette = [
                chartColors.success,
                chartColors.danger,
                chartColors.warning,
                chartColors.accent,
                chartColors.primary
            ];
            const colors = hasData
                ? labels.map((label, index) => statusPalette[index % statusPalette.length])
                : [chartColors.primary];

            new ApexCharts(chartElement, {
                chart: {
                    type: 'donut',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                series,
                labels,
                colors,
                stroke: {
                    width: 0
                },
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true
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
                            return value + ' logs';
                        }
                    }
                }
            }).render();
        };

        const buildTrendChart = () => {
            const chartElement = document.getElementById('email-delivery-trend-chart');

            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            new ApexCharts(chartElement, {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Logs',
                    data: reportData.charts.trend.series.map((value) => Number(value) || 0)
                }],
                colors: [chartColors.accent],
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
                    strokeWidth: 0,
                    hover: {
                        sizeOffset: 2
                    }
                },
                xaxis: {
                    categories: reportData.charts.trend.labels,
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
                            return Math.round(value);
                        }
                    }
                },
                grid: {
                    borderColor: chartColors.border,
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return value + ' logs';
                        }
                    }
                }
            }).render();
        };

        const buildDirectionChart = () => {
            const chartElement = document.getElementById('email-delivery-direction-chart');

            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            const rawSeries = reportData.charts.direction.series.map((value) => Number(value) || 0);
            const hasData = rawSeries.some((value) => value > 0);
            const categories = hasData ? reportData.charts.direction.labels : ['No activity'];
            const series = hasData ? rawSeries : [0];

            new ApexCharts(chartElement, {
                chart: {
                    height: 280,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 2,
                        columnWidth: '30%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (value) {
                        return value + ' logs';
                    },
                    offsetY: -25,
                    style: {
                        fontSize: '12px',
                        colors: ['#304758']
                    }
                },
                colors: [chartColors.primary],
                legend: {
                    show: true,
                    horizontalAlign: 'center',
                    offsetX: 0,
                    offsetY: -5
                },
                series: [{
                    name: 'Direction Logs',
                    data: series
                }],
                xaxis: {
                    categories,
                    position: 'bottom',
                    labels: {
                        offsetY: 0
                    },
                    axisBorder: {
                        show: true
                    },
                    axisTicks: {
                        show: true
                    },
                    tooltip: {
                        enabled: true,
                        offsetY: -10
                    }
                },
                yaxis: {
                    axisBorder: {
                        show: true
                    },
                    axisTicks: {
                        show: true
                    },
                    labels: {
                        show: true,
                        formatter: function (value) {
                            return Math.round(value);
                        }
                    }
                },
                grid: {
                    row: {
                        colors: ['transparent', 'transparent'],
                        opacity: 0.2
                    },
                    borderColor: chartColors.border
                }
            }).render();
        };

        const buildPurposeChart = () => {
            const chartElement = document.getElementById('email-delivery-purpose-chart');

            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            const rawSeries = reportData.charts.purpose.series.map((value) => Number(value) || 0);
            const hasData = rawSeries.some((value) => value > 0);
            const categories = hasData ? reportData.charts.purpose.labels : ['No activity'];
            const series = hasData ? rawSeries : [0];

            new ApexCharts(chartElement, {
                chart: {
                    height: 320,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Logs',
                    data: series
                }],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        barHeight: '48%'
                    }
                },
                colors: [chartColors.accent],
                dataLabels: {
                    enabled: true
                },
                xaxis: {
                    categories
                },
                grid: {
                    borderColor: chartColors.border,
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return value + ' logs';
                        }
                    }
                }
            }).render();
        };

        buildSparklineChart('email-total-sparkline', reportData.charts.sparklines.total, 'area', chartColors.primary);
        buildSparklineChart('email-sent-sparkline', reportData.charts.sparklines.sent, 'bar', chartColors.success);
        buildSparklineChart('email-failed-sparkline', reportData.charts.sparklines.failed, 'area', chartColors.danger);
        buildSparklineChart('email-pending-sparkline', reportData.charts.sparklines.pending, 'bar', chartColors.warning);
        buildStatusChart();
        buildTrendChart();
        buildDirectionChart();
        buildPurposeChart();

        const tableBody = document.getElementById('emailDeliveryTableBody');

        if (!tableBody) {
            return;
        }

        const tableRows = Array.from(tableBody.querySelectorAll('tr[data-log-row]'));
        const emptyRow = document.getElementById('emailDeliveryEmptyRow');
        const searchInput = document.getElementById('emailDeliverySearch');
        const statusFilter = document.getElementById('emailDeliveryStatusFilter');
        const purposeFilter = document.getElementById('emailDeliveryPurposeFilter');
        const directionFilter = document.getElementById('emailDeliveryDirectionFilter');
        const sortSelect = document.getElementById('emailDeliverySort');
        const clearButton = document.getElementById('emailDeliveryClearFilters');
        const countLabel = document.getElementById('emailDeliveryCount');
        const paginationCountLabel = document.getElementById('emailDeliveryPaginationCount');
        const pagination = document.getElementById('emailDeliveryPagination');
        const modalElement = document.getElementById('emailDeliveryDetailsModal');
        const detailsModal = modalElement && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalElement) : null;
        const logRowsById = new Map(reportData.tableRows.map((row) => [String(row.id), row]));

        const state = {
            search: '',
            status: 'all',
            purpose: 'all',
            direction: 'all',
            sort: 'newest',
            page: 1,
            perPage: 8
        };

        const compareStrings = (first, second) => first.localeCompare(second, undefined, { sensitivity: 'base' });

        const sortRows = (rows, sortBy) => {
            const sortedRows = [...rows];

            sortedRows.sort(function (firstRow, secondRow) {
                const firstDate = Number(firstRow.dataset.dateSort || 0);
                const secondDate = Number(secondRow.dataset.dateSort || 0);
                const firstEmail = normalize(firstRow.dataset.email);
                const secondEmail = normalize(secondRow.dataset.email);
                const firstStatus = normalize(firstRow.dataset.status);
                const secondStatus = normalize(secondRow.dataset.status);
                const firstPurpose = normalize(firstRow.dataset.purpose);
                const secondPurpose = normalize(secondRow.dataset.purpose);

                if (sortBy === 'oldest') {
                    return firstDate - secondDate;
                }

                if (sortBy === 'email_asc') {
                    return compareStrings(firstEmail, secondEmail) || (secondDate - firstDate);
                }

                if (sortBy === 'status_asc') {
                    return compareStrings(firstStatus, secondStatus) || (secondDate - firstDate);
                }

                if (sortBy === 'purpose_asc') {
                    return compareStrings(firstPurpose, secondPurpose) || (secondDate - firstDate);
                }

                return secondDate - firstDate;
            });

            return sortedRows;
        };

        const renderPagination = (totalPages) => {
            if (!pagination) {
                return;
            }

            pagination.innerHTML = '';

            const createPageItem = ({ label, page, disabled = false, active = false, icon = null }) => {
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

                if (icon) {
                    link.innerHTML = '<iconify-icon class="fs-18" icon="lucide:' + icon + '"></iconify-icon>';
                } else {
                    link.textContent = String(label);
                }

                if (!disabled && !active) {
                    link.addEventListener('click', function () {
                        state.page = page;
                        applyFilters();
                    });
                }

                item.appendChild(link);
                pagination.appendChild(item);
            };

            const currentPage = Math.min(state.page, totalPages);
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, startPage + 4);
            const pages = [];

            for (let page = startPage; page <= endPage; page += 1) {
                pages.push(page);
            }

            createPageItem({
                page: currentPage - 1,
                disabled: currentPage <= 1,
                icon: 'chevron-left'
            });

            if (startPage > 1) {
                createPageItem({ label: 1, page: 1, active: currentPage === 1 });
            }

            if (startPage > 2) {
                createPageItem({ label: '...', page: currentPage, disabled: true });
            }

            pages.forEach(function (page) {
                createPageItem({
                    label: page,
                    page,
                    active: page === currentPage
                });
            });

            if (endPage < totalPages - 1) {
                createPageItem({ label: '...', page: currentPage, disabled: true });
            }

            if (endPage < totalPages) {
                createPageItem({
                    label: totalPages,
                    page: totalPages,
                    active: currentPage === totalPages
                });
            }

            createPageItem({
                page: currentPage + 1,
                disabled: currentPage >= totalPages,
                icon: 'chevron-right'
            });
        };

        const applyFilters = () => {
            const searchTerm = normalize(state.search);
            const filteredRows = sortRows(tableRows.filter(function (row) {
                const matchesSearch = searchTerm === '' || row.dataset.search.includes(searchTerm);
                const matchesStatus = state.status === 'all' || normalize(row.dataset.status) === state.status;
                const matchesPurpose = state.purpose === 'all' || normalize(row.dataset.purpose) === state.purpose;
                const matchesDirection = state.direction === 'all' || normalize(row.dataset.direction) === state.direction;

                return matchesSearch && matchesStatus && matchesPurpose && matchesDirection;
            }), state.sort);

            const filteredSet = new Set(filteredRows);
            const nonFilteredRows = tableRows.filter((row) => !filteredSet.has(row));
            const totalFiltered = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(totalFiltered / state.perPage));

            if (state.page > totalPages) {
                state.page = totalPages;
            }

            const startIndex = totalFiltered === 0 ? 0 : (state.page - 1) * state.perPage;
            const pageRows = filteredRows.slice(startIndex, startIndex + state.perPage);
            const pageSet = new Set(pageRows);

            tableBody.append(...filteredRows, ...nonFilteredRows);

            tableRows.forEach(function (row) {
                row.classList.toggle('d-none', !pageSet.has(row));
            });

            if (emptyRow) {
                tableBody.append(emptyRow);
                emptyRow.classList.toggle('d-none', totalFiltered > 0);
            }

            const firstVisible = totalFiltered === 0 ? 0 : startIndex + 1;
            const lastVisible = totalFiltered === 0 ? 0 : startIndex + pageRows.length;
            const countText = totalFiltered === 0
                ? 'Showing 0 of ' + reportData.totalRows + ' logs'
                : 'Showing ' + firstVisible + '-' + lastVisible + ' of ' + totalFiltered + ' logs';

            if (countLabel) {
                countLabel.textContent = countText;
            }

            if (paginationCountLabel) {
                paginationCountLabel.textContent = countText;
            }

            renderPagination(totalPages);
        };

        const debounce = (callback, wait = 220) => {
            let timeoutId;

            return function (...args) {
                window.clearTimeout(timeoutId);
                timeoutId = window.setTimeout(function () {
                    callback.apply(null, args);
                }, wait);
            };
        };

        const applySearchDebounced = debounce(function () {
            state.page = 1;
            applyFilters();
        });

        if (searchInput) {
            searchInput.addEventListener('input', function (event) {
                state.search = event.target.value;
                applySearchDebounced();
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', function (event) {
                state.status = event.target.value;
                state.page = 1;
                applyFilters();
            });
        }

        if (purposeFilter) {
            purposeFilter.addEventListener('change', function (event) {
                state.purpose = event.target.value;
                state.page = 1;
                applyFilters();
            });
        }

        if (directionFilter) {
            directionFilter.addEventListener('change', function (event) {
                state.direction = event.target.value;
                state.page = 1;
                applyFilters();
            });
        }

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
                state.status = 'all';
                state.purpose = 'all';
                state.direction = 'all';
                state.sort = 'newest';
                state.page = 1;

                if (searchInput) {
                    searchInput.value = '';
                }

                if (statusFilter) {
                    statusFilter.value = 'all';
                }

                if (purposeFilter) {
                    purposeFilter.value = 'all';
                }

                if (directionFilter) {
                    directionFilter.value = 'all';
                }

                if (sortSelect) {
                    sortSelect.value = 'newest';
                }

                applyFilters();
            });
        }

        const setModalText = (id, value, fallback = 'N/A') => {
            const element = document.getElementById(id);

            if (!element) {
                return;
            }

            element.textContent = value && value !== '' ? value : fallback;
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-email-log-view]');

            if (!trigger || !detailsModal) {
                return;
            }

            event.preventDefault();

            const log = logRowsById.get(String(trigger.dataset.logId || ''));

            if (!log) {
                return;
            }

            setModalText('emailDeliveryModalReference', 'Log #' + log.id);
            setModalText('emailDeliveryModalDate', ((log.date_label || 'N/A') + (log.time_label ? ' ' + log.time_label : '')).trim(), 'N/A');
            setModalText('emailDeliveryModalEmail', log.email, 'No email recorded');
            setModalText('emailDeliveryModalPurpose', log.purpose, 'N/A');
            setModalText('emailDeliveryModalDirection', log.direction, 'N/A');
            setModalText('emailDeliveryModalTransport', log.transport, 'N/A');
            setModalText('emailDeliveryModalSubject', log.subject, 'No subject recorded.');
            setModalText('emailDeliveryModalResponse', log.response_message, 'No response or error details recorded.');

            const statusBadge = document.getElementById('emailDeliveryModalStatus');

            if (statusBadge) {
                statusBadge.className = 'badge badge-soft-' + (log.status_badge_class || 'secondary');
                statusBadge.textContent = log.status || 'N/A';
            }

            detailsModal.show();
        });

        applyFilters();
    });
</script>
@endsection
