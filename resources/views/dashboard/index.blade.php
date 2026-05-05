@extends('layouts.vertical', ['title' => 'Analytics'])

@section('css')
<style>
     .dashboard-overview-card,
     .dashboard-chart-card,
     .dashboard-list-card {
          width: 100%;
          height: 100%;
     }

     .dashboard-overview-card .card-body {
          min-height: 240px;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          padding: 1.35rem 1.35rem 1rem;
     }

     .dashboard-overview-copy {
          min-width: 0;
     }

     .dashboard-overview-copy h4 {
          font-size: clamp(1.25rem, 1.1rem + .35vw, 1.6rem);
          line-height: 1.25;
     }

     .dashboard-overview-icon {
          width: 54px;
          height: 54px;
          flex: 0 0 54px;
          border-radius: 12px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          background: var(--bs-light);
     }

     .dashboard-overview-row > [class*="col-"] {
          display: flex;
     }

     .dashboard-overview-chart {
          min-height: 112px;
          margin-top: 1.15rem;
     }

     .dashboard-overview-chart .apex-charts {
          min-height: 106px;
     }

     .dashboard-sparkline-caption {
          display: inline-flex;
          align-items: center;
          width: fit-content;
          margin-top: .75rem;
          padding: .25rem .55rem;
          border-radius: 999px;
          background: var(--bs-light);
          color: var(--bs-secondary-color);
          font-size: .72rem;
          font-weight: 600;
     }

     .dashboard-chart-row {
          margin-bottom: 1.5rem;
     }

     .dashboard-chart-card .card-body {
          padding-bottom: 1.5rem;
     }

     .dashboard-chart-panel {
          min-height: 280px;
     }

     .dashboard-period-dropdown .btn {
          min-width: 116px;
          justify-content: space-between;
     }

     .dashboard-list-card {
          width: 100%;
          height: 470px;
          min-height: 0;
     }

     .dashboard-list-row > [class*="col-"] {
          display: flex;
     }

     .dashboard-list-card .card-header {
          min-height: 64px;
          flex-shrink: 0;
     }

     .dashboard-list-card .card-footer {
          flex-shrink: 0;
     }

     .dashboard-list-body {
          min-height: 0;
     }

     .dashboard-scroll-panel {
          height: 100%;
          min-height: 0;
     }

     .dashboard-table-wrap {
          height: 100%;
          max-height: 100%;
          overflow: auto;
     }

     .dashboard-table-wrap .table {
          min-width: 640px;
     }

     .dashboard-table-wrap thead th {
          position: sticky;
          top: 0;
          z-index: 2;
          background: var(--bs-body-bg);
          box-shadow: inset 0 -1px 0 var(--bs-border-color);
     }

     .dashboard-scroll-panel .simplebar-content-wrapper {
          overflow: auto;
     }

     @media (max-width: 767.98px) {
          .dashboard-chart-card .card-header,
          .dashboard-list-card .card-header {
               flex-wrap: wrap;
               gap: .75rem;
          }

          .dashboard-period-dropdown,
          .dashboard-period-dropdown .btn {
               width: 100%;
          }

          .dashboard-list-card {
               height: 470px;
          }

          .dashboard-overview-card .card-body {
               min-height: 220px;
          }
     }
</style>
@endsection

@section('content')
@php
     $dashboardPeriodOptions = $dashboardCharts['periodOptions'] ?? ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'];
     $emailDeliveryPeriodOptions = ['today' => 'Today', 'weekly' => 'Weekly', 'monthly' => 'Monthly'];
@endphp

<div class="row g-3 dashboard-overview-row align-items-stretch">
     @foreach($dashboardOverview['cards'] as $card)
     <div class="col-xl-3 col-md-6">
          <div class="card dashboard-overview-card">
               <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                         <div class="dashboard-overview-copy">
                              <p class="mb-2 card-title">{{ $card['title'] }}</p>
                              <h4 class="fw-bold {{ $card['value_class'] }} d-flex align-items-center gap-2 mb-2">{{ $card['value'] }}</h4>
                              <p class="text-muted mb-0 small">{{ $card['note'] }}</p>
                              <span class="dashboard-sparkline-caption">{{ $card['sparkline_label'] ?? '7-day comparison' }}</span>
                         </div>
                         <div class="dashboard-overview-icon">
                              <i data-lucide="{{ $card['icon'] }}" class="fs-32 {{ $card['icon_class'] }}"></i>
                         </div>
                    </div>
                    <div class="row align-items-center dashboard-overview-chart">
                         <div class="col-12">
                              <div id="{{ $card['sparkline_id'] }}" class="apex-charts"></div>
                         </div>
                    </div>
               </div>
          </div>
     </div>
     @endforeach
</div>

<div class="row g-3 dashboard-chart-row align-items-stretch">
     <div class="col-xl-4 col-lg-6">
          <div class="card dashboard-chart-card">
	               <div class="card-header d-flex align-items-center justify-content-between">
	                    <div>
	                         <h4 class="card-title mb-0">Visitor Insights</h4>
	                    </div>
	                    <div>
	                         <a href="{{ route('second', ['reports', 'visitor-reports']) }}" class="text-dark btn btn-sm btn-link text-uppercase fw-semibold px-0">Visitor Insights <i data-lucide="arrow-right"></i></a>
                    </div>
               </div>
	               <div class="card-body">
	                    <div class="text-center">
	                         <p class="text-muted mb-3">Yeah! You have received <span class="fw-semibold text-success">+33</span> new visitors today.</p>
	                    </div>
	                    <div id="simple-donut" class="apex-charts dashboard-chart-panel"></div>
               </div>
          </div>
     </div>

     <div class="col-xl-4 col-lg-6">
          <div class="card dashboard-chart-card">
	               <div class="card-header d-flex align-items-center justify-content-between">
	                    <div>
	                         <h4 class="card-title mb-0">Inquiries</h4>
	                    </div>
	                    <div class="dropdown dashboard-period-dropdown">
	                         <button class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
	                              <span data-dashboard-period-label="inquiries">Weekly</span>
	                              <i data-lucide="chevron-down" class="icon-xs"></i>
	                         </button>
	                         <div class="dropdown-menu dropdown-menu-end">
	                              @foreach($dashboardPeriodOptions as $periodKey => $periodLabel)
	                              <button class="dropdown-item dashboard-period-option {{ $periodKey === 'weekly' ? 'active' : '' }}" type="button" data-dashboard-chart-period="inquiries" data-period="{{ $periodKey }}">{{ $periodLabel }}</button>
	                              @endforeach
	                         </div>
	                    </div>
	               </div>
               <div class="card-body">
                    <div class="text-center">
                         <p class="text-muted mb-3">You have received <span class="fw-semibold text-success">+3</span> new inquiries today.</p>
                    </div>
                    <div id="datalabels-column2" class="apex-charts dashboard-chart-panel"></div>
               </div>
          </div>
     </div>

     <div class="col-xl-4 col-lg-12">
          <div class="card dashboard-chart-card">
               <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                         <h4 class="card-title mb-0">Service Category</h4>
                    </div>
                    <div class="dropdown dashboard-period-dropdown">
                         <button class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              <span data-dashboard-period-label="serviceHeatmap">Weekly</span>
                              <i data-lucide="chevron-down" class="icon-xs"></i>
                         </button>
                         <div class="dropdown-menu dropdown-menu-end">
                              @foreach($dashboardPeriodOptions as $periodKey => $periodLabel)
                              <button class="dropdown-item dashboard-period-option {{ $periodKey === 'weekly' ? 'active' : '' }}" type="button" data-dashboard-chart-period="serviceHeatmap" data-period="{{ $periodKey }}">{{ $periodLabel }}</button>
                              @endforeach
                         </div>
                    </div>
               </div>
               <div class="card-body">
                    <div class="text-center">
                         <p class="text-muted mb-3">Yeah! You have completed <span class="fw-semibold text-success">5</span> moves this week.</p>
                    </div>
                    <div id="basic-heatmap" class="apex-charts dashboard-chart-panel"></div>
               </div>
          </div>
     </div>
</div>

<div class="row g-3 dashboard-list-row align-items-stretch">
     <div class="col-xl-4 col-lg-6">
          <div class="card d-flex flex-column dashboard-list-card">
               <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                         <h4 class="card-title mb-0">New Users</h4>
                    </div>
                    <div>
                         <a href="{{ route('any', 'customers') }}" class="text-dark btn btn-sm btn-link text-uppercase fw-semibold px-0">View All <i data-lucide="arrow-right"></i></a>
                    </div>
               </div>

               <div class="flex-grow-1 overflow-hidden d-flex flex-column dashboard-list-body">
                    <div class="flex-grow-1 overflow-hidden dashboard-scroll-panel" data-simplebar>
                         @forelse($recentCustomers as $customer)
                         <div class="d-flex flex-wrap gap-3 border-bottom p-3">
                              <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold">
                                   {{ $customer->initials() }}
                              </div>
                              <div class="flex-grow-1">
                                   <div class="text-dark fs-15 fw-medium">{{ $customer->full_name }}</div>
                                   <p class="mb-1 text-muted">{{ $customer->email }}</p>
                                   <small class="text-muted d-block">{{ $customer->latestServiceLabel() }} • {{ $customer->latestRouteSummary() }}</small>
                              </div>
                              <div class="align-self-center ms-auto d-flex justify-content-end">
                                   <a href="{{ $customer->telLink() }}" class="btn btn-sm btn-primary">Call</a>
                              </div>
                         </div>
                         @empty
                         <div class="p-3">
                              <div class="alert alert-warning mb-0" role="alert">
                                   No customers found yet. Once quote requests are synced, the newest customers will show here.
                              </div>
                         </div>
                         @endforelse
                    </div>
               </div>

               <div class="card-footer border-top text-center p-3">
                    <a href="{{ route('any', 'customers') }}" class="link-primary text-decoration-underline fw-medium">Show More <i class="ri-arrow-right-up-line"></i></a>
               </div>
          </div>
     </div>

     <div class="col-xl-4 col-lg-6">
          <div class="card d-flex flex-column dashboard-list-card">
               <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                         <h4 class="card-title mb-0">Recent Quotes</h4>
                    </div>
               </div>
               <div class="flex-grow-1 overflow-hidden d-flex flex-column dashboard-list-body">
                    <div class="table-responsive dashboard-table-wrap">
                              <table class="table table-sm table-hover mb-0 align-middle">
                                   <thead>
                                        <tr>
                                             <th>Client Name</th>
                                             <th>Move Date</th>
                                             <th>Status</th>
                                             <th class="text-end">Actions</th>
                                        </tr>
                                   </thead>
                                   <tbody>
                                        @forelse($recentQuotes as $quote)
                                        <tr>
                                             <td>
                                                  <a class="fw-medium" href="{{ route('quotes.show', $quote) }}">{{ $quote->full_name }}</a>
                                                  <div class="text-muted small">{{ $quote->reference() }} • {{ $quote->serviceTypeLabel() }}</div>
                                             </td>
                                             <td>
                                                  {{ $quote->move_date?->format('d M, Y') ?? 'Not set' }}
                                                  <small class="text-muted d-block">{{ $quote->created_at?->format('h:i A') ?? '' }}</small>
                                             </td>
                                             <td>
                                                  @php
                                                       $displayStatus = match ($quote->status) {
                                                            'quoted' => 'Approved',
                                                            'closed', 'spam' => 'Declined',
                                                            default => 'Pending',
                                                       };
                                                  @endphp
                                                  <span class="badge badge-soft-{{ $quote->statusBadgeClass() }}">{{ $displayStatus }}</span>
                                             </td>
                                             <td class="text-end">
                                                  <div class="d-flex flex-wrap justify-content-end gap-1">
                                                       <a class="btn btn-icon btn-sm btn-soft-primary" href="{{ route('quotes.show', $quote) }}" title="View">
                                                            <i data-lucide="eye" class="align-middle"></i>
                                                       </a>
                                                       <a class="btn btn-icon btn-sm btn-soft-secondary" href="{{ route('quotes.edit', $quote) }}" title="Edit">
                                                            <i data-lucide="edit-3" class="align-middle"></i>
                                                       </a>
                                                  </div>
                                             </td>
                                        </tr>
                                        @empty
                                        <tr>
                                             <td colspan="4" class="text-center text-muted py-4">No recent quotes are available yet.</td>
                                        </tr>
                                        @endforelse
                                   </tbody>
                              </table>
                         </div>
               </div>
               <div class="card-footer border-top text-center p-3">
                    <a href="{{ route('quotes.index') }}" class="link-primary text-decoration-underline fw-medium">Show More <i class="ri-arrow-right-up-line"></i></a>
               </div>
          </div>
     </div>

     <div class="col-xl-4 col-lg-6">
          <div class="card d-flex flex-column dashboard-list-card">
               <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                         <h4 class="card-title mb-0">Email Delivery Status</h4>
                    </div>
                    <div class="dropdown dashboard-period-dropdown">
                         <button class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              <span id="dashboard-email-period-label">Today</span>
                              <i data-lucide="chevron-down" class="icon-xs"></i>
                         </button>
                         <div class="dropdown-menu dropdown-menu-end">
                              @foreach($emailDeliveryPeriodOptions as $periodKey => $periodLabel)
                              <button class="dropdown-item dashboard-email-period-option {{ $periodKey === 'today' ? 'active' : '' }}" type="button" data-period="{{ $periodKey }}">{{ $periodLabel }}</button>
                              @endforeach
                         </div>
                    </div>
               </div>

               <div class="flex-grow-1 overflow-hidden d-flex flex-column dashboard-list-body">
                    <div class="table-responsive dashboard-table-wrap">
                              <table class="table table-sm table-hover mb-0">
                                   <thead>
                                        <tr>
                                             <th>Date</th>
                                             <th>Email</th>
                                             <th>Status</th>
                                             <th>Purpose</th>
                                        </tr>
                                   </thead>

                                   <tbody>
                                        @forelse($emailDeliveryLogs as $log)
                                        <tr data-dashboard-email-row data-created="{{ $log->created_at?->timestamp ?? 0 }}">
                                             <td>
                                                  {{ $log->created_at?->format('d M, Y') ?? 'N/A' }}
                                                  <small class="text-muted d-block">{{ $log->created_at?->format('h:i A') ?? '' }}</small>
                                             </td>
                                             <td>
                                                  @if($log->recipient_email)
                                                  <a class="text-muted text-decoration-none" href="mailto:{{ $log->recipient_email }}">{{ $log->recipient_email }}</a>
                                                  @else
                                                  <span class="text-muted">No email recorded</span>
                                                  @endif
                                             </td>
                                             <td>
                                                  <span class="badge badge-soft-{{ $log->status_badge_class }}">{{ $log->status_label }}</span>
                                             </td>
                                             <td>
                                                  <span class="fw-medium">{{ $log->purpose }}</span>
                                             </td>
                                        </tr>
                                        @empty
                                        <tr>
                                             <td colspan="4" class="text-center text-muted py-4">No email delivery logs are available yet.</td>
                                        </tr>
                                        @endforelse
                                        @if($emailDeliveryLogs->isNotEmpty())
                                        <tr id="dashboard-email-empty-row" class="d-none">
                                             <td colspan="4" class="text-center text-muted py-4">No email delivery logs match this period.</td>
                                        </tr>
                                        @endif
                                   </tbody>
                              </table>
                         </div>
               </div>

               <div class="card-footer border-top text-center p-3">
                    <a href="{{ route('second', ['reports', 'email-delivery']) }}" class="link-primary text-decoration-underline fw-medium">Show More <i class="ri-arrow-right-up-line"></i></a>
               </div>
          </div>
     </div>
</div>
@endsection

@section('scripts')
@php
    $dashboardChartData = [
        'cards' => collect($dashboardOverview['cards'] ?? [])->map(fn (array $card) => [
            'sparkline_id' => $card['sparkline_id'] ?? '',
            'sparkline' => array_values($card['sparkline'] ?? []),
            'sparkline_type' => $card['sparkline_type'] ?? 'line',
            'sparkline_color' => $card['sparkline_color'] ?? '#22B956',
        ])->filter(fn (array $card) => filled($card['sparkline_id']))->values()->all(),
        'visitorDevices' => $dashboardCharts['visitorDevices'] ?? ['labels' => [], 'series' => []],
        'periodOptions' => $dashboardCharts['periodOptions'] ?? ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'],
        'inquiries' => $dashboardCharts['inquiries'] ?? ['weekly' => ['labels' => [], 'series' => []]],
        'serviceHeatmap' => $dashboardCharts['serviceHeatmap'] ?? ['weekly' => ['labels' => [], 'series' => []]],
    ];
@endphp
<script id="dashboard-chart-data" type="application/json">@json($dashboardChartData)</script>
@vite(['resources/js/pages/dashboard.js'])
@endsection
