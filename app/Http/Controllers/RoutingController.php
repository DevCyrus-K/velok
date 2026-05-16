<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GalleryItem;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Services\CustomerSyncService;
use App\Services\GoogleAnalyticsService;
use App\Services\StorageService;
use App\Support\CompanyProfile;
use App\Support\InvoiceAuthorization;
use App\Support\PaymentSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoutingController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private const DASHBOARD_PERIOD_OPTIONS = [
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ];

    private const DASHBOARD_WEEKDAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    private const DASHBOARD_SERVICE_CATEGORIES = [
        'Residential Relocation',
        'Office Relocation',
        'Long-Distance Move',
        'Packing & Storage',
    ];

    public function __construct()
    {
        // $this->
        // middleware('auth')->
        // except('index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (Auth::user()) {
            return redirect('/dashboard/index');
        } else {
            return redirect('login');
        }
    }

    /**
     * Display a view based on first route param
     *
     * @return Response
     */
    public function root(Request $request, $first)
    {
        if ($first === 'contacts') {
            return redirect()->route('any', 'customers');
        }

        if ($first === 'customers') {
            return $this->customersPage();
        }

        return $this->notFound();
    }

    /**
     * second level route
     */
    public function secondLevel(Request $request, $first, $second)
    {
        if ($first === 'dashboard' && $second === 'index') {
            return $this->dashboardPage();
        }

        if ($first === 'reports') {
            return $this->reportsPage($second);
        }

        if ($first === 'pages' && $second === 'settings') {
            return redirect()->route('settings.index');
        }

        if ($first === 'pages' && $second === 'profile') {
            return redirect()->route('account.show');
        }

        if ($first === 'pages' && $second === 'gallery') {
            $galleryItems = GalleryItem::query()
                ->published()
                ->orderByDesc('featured')
                ->orderByDesc('created_at')
                ->get()
                ->map(function (GalleryItem $item) {
                    $category = $item->category ?: 'General';

                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'category' => $category,
                        'alt_text' => $item->altText(),
                        'featured' => $item->featured,
                        'image_url' => $item->publicUrl(),
                        'filter_group' => Str::slug($category),
                    ];
                });

            $categories = $galleryItems->pluck('category')
                ->filter()
                ->unique()
                ->values();

            return view('pages.gallery', [
                'galleryItems' => $galleryItems,
                'galleryCategories' => $categories,
            ]);
        }

        if ($first === 'invoice' && $second === 'invoices') {
            return $this->invoicesPage();
        }

        if ($first === 'invoice' && $second === 'invoice-details') {
            return $this->invoiceDetailsPage();
        }

        return $this->notFound();
    }

    /**
     * third level route
     */
    public function thirdLevel(Request $request, $first, $second, $third)
    {
        if ($first === 'invoice' && $second === 'invoice-details') {
            return $this->invoiceDetailsPage($third);
        }

        return $this->notFound();
    }

    private function notFound(): Response
    {
        return response()->view('errors.404', [], 404);
    }

    private function dashboardPage(): View
    {
        $customerData = $this->customerData();
        $dashboardData = $this->dashboardLeadData($customerData['customers']);

        return view('dashboard.index', [
            'recentCustomers' => $customerData['recentCustomers'],
            'customerSummary' => $customerData['summary'],
            'recentQuotes' => $dashboardData['recentQuotes'],
            'emailDeliveryLogs' => $dashboardData['emailDeliveryLogs'],
            'dashboardOverview' => $dashboardData['overview'],
            'dashboardCharts' => $dashboardData['charts'],
        ]);
    }

    private function customersPage(): View
    {
        $customerData = $this->customerData();

        return view('customers', [
            'customers' => $customerData['customers'],
            'customerSummary' => $customerData['summary'],
        ]);
    }

    public function invoicesPage(): View
    {
        return view('invoice.invoices', [
            'invoices' => $this->invoicesCollection(),
        ]);
    }

    public function invoiceDetailsPage($invoice = null): View
    {
        $invoiceRecord = $this->invoiceRecord($invoice);
        $company = app(CompanyProfile::class)->data();
        $user = auth()->user();
        $authorization = $invoiceRecord ? app(InvoiceAuthorization::class)->data($invoiceRecord, $company, $user) : null;

        return view('invoice.invoice-details', [
            'invoice' => $invoiceRecord,
            'company' => $company,
            'paymentMethods' => $invoiceRecord ? app(PaymentSettings::class)->methodsForInvoice($invoiceRecord) : [],
            'thankYouMessage' => app(CompanyProfile::class)->thankYouMessage(),
            'authorization' => $authorization,
            'signatureDataUri' => $invoiceRecord ? app(InvoiceAuthorization::class)->signatureDataUri($invoiceRecord, $company, $user) : null,
            'user' => $user,
        ]);
    }

    public function downloadReport(string $report)
    {
        $reportData = $this->reportDataForDownload($report);
        $filename = Str::slug($reportData['title'] ?? $report).'-report-'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $reportData,
            'generatedAt' => now(),
        ])
            ->setPaper('a4', 'landscape');

        try {
            $uploaded = app(StorageService::class)->uploadGeneratedPdf($pdf->output(), $filename, 'reports');
            return redirect()->away(app(StorageService::class)->getPDFDownloadUrl($uploaded['key']));
        } catch (\Exception $e) {
            // Fallback: if B2 upload fails, serve PDF directly
            \Log::warning("B2 upload failed for report {$report}: {$e->getMessage()}");
            
            return response()
                ->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"'
                ]);
        }
    }

    private function customerData(): array
    {
        app(CustomerSyncService::class)->sync();

        if (! Schema::hasTable('customers')) {
            return [
                'customers' => collect(),
                'recentCustomers' => collect(),
                'summary' => [
                    'total' => 0,
                    'lead' => 0,
                    'active_client' => 0,
                    'completed' => 0,
                    'inactive' => 0,
                ],
            ];
        }

        $customersQuery = Customer::query()
            ->orderByDesc(DB::raw('COALESCE(last_quote_at, first_seen_at, created_at)'))
            ->orderByDesc('id');

        $customers = $customersQuery->paginate(15);

        $allCustomers = $customersQuery->get();

        return [
            'customers' => $customers,
            'recentCustomers' => $allCustomers->take(8),
            'summary' => [
                'total' => $allCustomers->count(),
                'lead' => $allCustomers->where('status', Customer::STATUS_LEAD)->count(),
                'active_client' => $allCustomers->where('status', Customer::STATUS_ACTIVE_CLIENT)->count(),
                'completed' => $allCustomers->where('status', Customer::STATUS_COMPLETED)->count(),
                'inactive' => $allCustomers->where('status', Customer::STATUS_INACTIVE)->count(),
            ],
        ];
    }

    private function dashboardLeadData($customers = null): array
    {
        $quotes = $this->quoteRequests();
        $messages = $this->messagesCollection();
        $customers = $customers ?? $this->customersCollection();
        $invoices = $this->invoicesCollection();
        $visitorAnalytics = $this->googleAnalyticsVisitors();

        return [
            'recentQuotes' => $quotes->take(8)->values(),
            'emailDeliveryLogs' => $this->emailDeliveryLogs(8),
            'overview' => $this->dashboardOverviewData($quotes, $customers, $invoices, $visitorAnalytics),
            'charts' => $this->dashboardChartsData($quotes, $messages, $customers, $visitorAnalytics),
        ];
    }

    private function dashboardOverviewData($quotes, $customers, $invoices, array $visitorAnalytics): array
    {
        $completedMoves = $customers->where('status', Customer::STATUS_COMPLETED)->count();
        $cancelledBookings = $quotes->whereIn('status', ['closed', 'spam'])->count();
        $visitorSummary = $visitorAnalytics['summary'] ?? [];

        return [
            'cards' => [
                [
                    'title' => 'Total Revenue',
                    'value' => $this->formatCurrency($invoices->sum('total_amount')),
                    'note' => $invoices->count().' live invoices contributing to the current total.',
                    'icon' => 'wallet',
                    'icon_class' => 'text-primary',
                    'value_class' => 'text-primary',
                    'sparkline_id' => 'sales_funnel',
                    'sparkline_type' => 'area',
                    'sparkline_color' => '#4d5761',
                    'sparkline' => $this->dateSeries(
                        $invoices,
                        fn (Invoice $invoice) => $invoice->invoice_date ?? $invoice->created_at,
                        11,
                        fn (Invoice $invoice) => (float) $invoice->total_amount
                    )['series'],
                ],
                [
                    'title' => 'Completed Moves',
                    'value' => number_format($completedMoves),
                    'note' => 'Customers marked complete after quote and follow-up activity.',
                    'icon' => 'briefcase-conveyor-belt',
                    'icon_class' => 'text-success',
                    'value_class' => 'text-dark',
                    'sparkline_id' => 'order_funnel',
                    'sparkline_type' => 'bar',
                    'sparkline_color' => '#22B956',
                    'sparkline' => $this->sparklineSeries(
                        $customers,
                        fn (Customer $customer) => $customer->last_quote_at ?? $customer->created_at,
                        fn (Customer $customer) => $customer->status === Customer::STATUS_COMPLETED
                    ),
                ],
                [
                    'title' => 'Cancelled Bookings',
                    'value' => number_format($cancelledBookings),
                    'note' => 'Quote requests that ended as declined or spam.',
                    'icon' => 'shield-minus',
                    'icon_class' => 'text-danger',
                    'value_class' => 'text-primary',
                    'sparkline_id' => 'cancel_funnel',
                    'sparkline_type' => 'area',
                    'sparkline_color' => '#f95c5c',
                    'sparkline' => $this->sparklineSeries(
                        $quotes,
                        fn (QuoteRequest $quote) => $quote->created_at,
                        fn (QuoteRequest $quote) => in_array($quote->status, ['closed', 'spam'], true)
                    ),
                ],
                [
                    'title' => 'Total Visitors',
                    'value' => number_format((int) ($visitorSummary['active_users'] ?? 0)),
                    'note' => ($visitorAnalytics['error'] ?? null)
                        ? 'Connect Google Analytics to show live visitor totals.'
                        : 'Google Analytics active users for '.($visitorAnalytics['date_range_label'] ?? 'the selected range').'.',
                    'icon' => 'users',
                    'icon_class' => 'text-info',
                    'value_class' => 'text-dark',
                    'sparkline_id' => 'customer_funnel',
                    'sparkline_type' => 'bar',
                    'sparkline_color' => '#3b82f6',
                    'sparkline_label' => '30-day GA trend',
                    'sparkline' => collect($visitorAnalytics['daily'] ?? [])
                        ->take(-11)
                        ->pluck('active_users')
                        ->map(fn ($value) => (int) $value)
                        ->values()
                        ->all(),
                ],
            ],
        ];
    }

    private function dashboardChartsData($quotes, $messages, $customers, array $visitorAnalytics): array
    {
        $sourceCounts = $this->combinedCountMaps(
            $this->topCounts($quotes, fn (QuoteRequest $quote) => $quote->source_page, 20, 'Unknown Source'),
            $this->topCounts($messages, fn (Message $message) => $message->origin_page, 20, 'Unknown Origin')
        )->take(4);

        return [
            'trafficSources' => [
                'labels' => $sourceCounts->keys()->values()->all(),
                'series' => $sourceCounts->values()->values()->all(),
            ],
            'visitorDevices' => $visitorAnalytics['devices'] ?? ['labels' => [], 'series' => []],
            'visitorToday' => $visitorAnalytics['today'] ?? ['active_users' => 0, 'new_users' => 0],
            'periodOptions' => self::DASHBOARD_PERIOD_OPTIONS,
            'summary' => $this->dashboardChartSummaryData($quotes, $messages, $customers),
            'inquiries' => $this->dashboardInquiryPeriodData($quotes, $messages),
            'serviceHeatmap' => $this->dashboardServiceCategoryPeriodData($quotes),
        ];
    }

    private function dashboardChartSummaryData($quotes, $messages, $customers): array
    {
        $todayStart = now()->copy()->startOfDay();
        $todayEnd = now()->copy()->endOfDay();
        [$weekStart, $weekEnd] = $this->dashboardPeriodRange('weekly');

        return [
            'inquiries_today' => $this->countItemsInPeriod($quotes, fn (QuoteRequest $quote) => $quote->created_at, $todayStart, $todayEnd)
                + $this->countItemsInPeriod($messages, fn (Message $message) => $message->created_at, $todayStart, $todayEnd),
            'completed_moves_this_week' => $this->countItemsInPeriod(
                $customers->where('status', Customer::STATUS_COMPLETED),
                fn (Customer $customer) => $customer->last_quote_at ?? $customer->updated_at ?? $customer->created_at,
                $weekStart,
                $weekEnd
            ),
        ];
    }

    private function reportsPage(string $report): View
    {
        return match ($report) {
            'overview' => $this->reportsOverviewPage(),
            'quote-funnel' => $this->genericReportPage($this->quoteFunnelReportData()),
            'lead-sources' => $this->genericReportPage($this->leadSourcesReportData()),
            'customer-lifecycle' => $this->genericReportPage($this->customerLifecycleReportData()),
            'quotation-pipeline' => $this->genericReportPage($this->quotationPipelineReportData()),
            'message-response' => $this->genericReportPage($this->messageResponseReportData()),
            'route-demand' => $this->genericReportPage($this->routeDemandReportData()),
            'at-risk-follow-up' => $this->genericReportPage($this->atRiskFollowUpReportData()),
            'visitor-reports' => $this->genericReportPage($this->visitorReportsData()),
            'financial-reports' => $this->genericReportPage($this->financialReportData()),
            'email-delivery' => $this->emailDeliveryReportPage(),
            default => abort(404),
        };
    }

    private function reportsOverviewPage(): View
    {
        $quotes = $this->quoteRequests();
        $customers = $this->customersCollection();
        $quotations = $this->quotationsCollection();
        $invoices = $this->invoicesCollection();
        $messages = $this->messagesCollection();
        $reportCatalog = collect($this->reportsCatalog());
        $visibleReports = $reportCatalog->reject(fn (array $report) => $report['slug'] === 'overview');

        $reportSections = $visibleReports
            ->groupBy('category')
            ->map(fn ($items, string $category) => [
                'title' => $category,
                'reports' => $items->values()->all(),
            ])
            ->values()
            ->all();

        return view('reports.overview', [
            'reportSections' => $reportSections,
            'reportCount' => $visibleReports->count(),
            'reportOverviewCharts' => [
                'activity' => [
                    'labels' => ['Quotes', 'Customers', 'Messages', 'Invoices', 'Quotations'],
                    'series' => [
                        $quotes->count(),
                        $customers->count(),
                        $messages->count(),
                        $invoices->count(),
                        $quotations->count(),
                    ],
                ],
                'categories' => [
                    'labels' => $visibleReports->groupBy('category')->keys()->values()->all(),
                    'series' => $visibleReports->groupBy('category')->map->count()->values()->all(),
                ],
            ],
            'reportSummaryCards' => [
                [
                    'title' => 'Live Reports',
                    'value' => $visibleReports->count(),
                    'icon' => 'chart-column',
                    'description' => 'Real report pages available from the current data model.',
                ],
                [
                    'title' => 'Tracked Quotes',
                    'value' => $quotes->count(),
                    'icon' => 'message-square-quote',
                    'description' => 'Lead and quote requests available for customer and funnel reporting.',
                ],
                [
                    'title' => 'Tracked Customers',
                    'value' => $customers->count(),
                    'icon' => 'users',
                    'description' => 'Synced customers ready for customer reporting and follow-up.',
                ],
                [
                    'title' => 'Tracked Messages',
                    'value' => $messages->count(),
                    'icon' => 'mail',
                    'description' => 'Messages and email logs support response and delivery tracking.',
                ],
                [
                    'title' => 'Tracked Invoices',
                    'value' => $invoices->count(),
                    'icon' => 'receipt',
                    'description' => 'Live invoices power revenue, paid value, and outstanding balance reporting.',
                ],
                [
                    'title' => 'Tracked Quotations',
                    'value' => $quotations->count(),
                    'icon' => 'file-text',
                    'description' => 'Quotation values remain available as pipeline context.',
                ],
            ],
        ]);
    }

    private function reportsOverviewDownloadData(): array
    {
        $quotes = $this->quoteRequests();
        $customers = $this->customersCollection();
        $quotations = $this->quotationsCollection();
        $invoices = $this->invoicesCollection();
        $messages = $this->messagesCollection();
        $visibleReports = collect($this->reportsCatalog())
            ->reject(fn (array $report) => $report['slug'] === 'overview')
            ->values();

        return [
            'slug' => 'overview',
            'title' => 'Reports Overview',
            'subtitle' => 'Downloadable directory and coverage summary for the live reporting center.',
            'badge' => 'Reports Hub',
            'cards' => [
                $this->reportCard('Live Reports', (string) $visibleReports->count(), 'chart-column', 'text-primary'),
                $this->reportCard('Tracked Quotes', (string) $quotes->count(), 'message-square-quote', 'text-primary'),
                $this->reportCard('Tracked Customers', (string) $customers->count(), 'users', 'text-primary'),
                $this->reportCard('Tracked Messages', (string) $messages->count(), 'mail', 'text-primary'),
                $this->reportCard('Tracked Invoices', (string) $invoices->count(), 'receipt', 'text-primary'),
                $this->reportCard('Tracked Quotations', (string) $quotations->count(), 'file-text', 'text-primary'),
            ],
            'insights' => $visibleReports
                ->groupBy('category')
                ->map(fn ($reports, string $category) => $this->insightCard(
                    $category,
                    (string) $reports->count(),
                    'Downloadable report pages in this category.'
                ))
                ->values()
                ->all(),
            'table' => [
                'title' => 'Report Directory',
                'description' => 'Every report currently available for viewing and download.',
                'columns' => [
                    ['key' => 'category', 'label' => 'Category'],
                    ['key' => 'report', 'label' => 'Report'],
                    ['key' => 'description', 'label' => 'Description'],
                    ['key' => 'path', 'label' => 'Path'],
                ],
                'rows' => $visibleReports->map(fn (array $report) => [
                    'cells' => [
                        'category' => $this->textCell($report['category']),
                        'report' => $this->textCell($report['title']),
                        'description' => $this->textCell($report['description']),
                        'path' => $this->textCell('/reports/'.$report['slug']),
                    ],
                    'datasets' => [],
                    'actions' => [],
                ])->all(),
            ],
        ];
    }

    private function genericReportPage(array $report): View
    {
        return view('reports.generic', [
            'report' => $report,
        ]);
    }

    private function reportDataForDownload(string $report): array
    {
        return match ($report) {
            'overview' => $this->reportsOverviewDownloadData(),
            'quote-funnel' => $this->quoteFunnelReportData(),
            'lead-sources' => $this->leadSourcesReportData(),
            'customer-lifecycle' => $this->customerLifecycleReportData(),
            'quotation-pipeline' => $this->quotationPipelineReportData(),
            'message-response' => $this->messageResponseReportData(),
            'route-demand' => $this->routeDemandReportData(),
            'at-risk-follow-up' => $this->atRiskFollowUpReportData(),
            'visitor-reports' => $this->visitorReportsData(),
            'financial-reports' => $this->financialReportData(),
            'email-delivery' => $this->emailDeliveryDownloadData(),
            default => abort(404),
        };
    }

    private function googleAnalyticsVisitors(): array
    {
        return app(GoogleAnalyticsService::class)->visitorReport();
    }

    private function reportsCatalog(): array
    {
        return [
            [
                'slug' => 'overview',
                'title' => 'Reports Overview',
                'description' => 'Jump into every live report view from one place.',
                'icon' => 'chart-column',
                'category' => 'Overview',
            ],
            [
                'slug' => 'quote-funnel',
                'title' => 'Quote Funnel Report',
                'description' => 'Measure pending, approved, declined, and email-failed quote requests from live quote data.',
                'icon' => 'message-square-quote',
                'category' => 'Lead Reports',
            ],
            [
                'slug' => 'lead-sources',
                'title' => 'Lead Sources Report',
                'description' => 'Compare captured quote source pages by volume, quality, and delivery issues.',
                'icon' => 'waypoints',
                'category' => 'Lead Reports',
            ],
            [
                'slug' => 'customer-lifecycle',
                'title' => 'Customer Report',
                'description' => 'See how synced leads become active clients, completed customers, or inactive accounts.',
                'icon' => 'users',
                'category' => 'Core Reports',
            ],
            [
                'slug' => 'financial-reports',
                'title' => 'Financial Report',
                'description' => 'Summarize invoiced revenue, paid value, outstanding balances, and quotation pipeline value.',
                'icon' => 'wallet',
                'category' => 'Core Reports',
            ],
            [
                'slug' => 'quotation-pipeline',
                'title' => 'Quotation Pipeline Report',
                'description' => 'Monitor drafts, sent quotations, validity windows, and quote value from live quotations.',
                'icon' => 'file-text',
                'category' => 'Operations Reports',
            ],
            [
                'slug' => 'message-response',
                'title' => 'Message Response Report',
                'description' => 'Track inbox backlog, reply speed, and origin pages from live message records.',
                'icon' => 'mail',
                'category' => 'Operations Reports',
            ],
            [
                'slug' => 'route-demand',
                'title' => 'Route Demand Report',
                'description' => 'Find the busiest origins, destinations, and route combinations from quote requests.',
                'icon' => 'route',
                'category' => 'Lead Reports',
            ],
            [
                'slug' => 'at-risk-follow-up',
                'title' => 'At-Risk Follow-Up Report',
                'description' => 'Surface stale quotes, unread messages, draft quotations, and inactive customers.',
                'icon' => 'triangle-alert',
                'category' => 'Operations Reports',
            ],
            [
                'slug' => 'email-delivery',
                'title' => 'Email Delivery Report',
                'description' => 'Review delivery success, failures, and full email log details.',
                'icon' => 'mail',
                'category' => 'Core Reports',
            ],
            [
                'slug' => 'visitor-reports',
                'title' => 'Visitor Insights',
                'description' => 'Review visitor, page, device, and channel traffic directly from Google Analytics.',
                'icon' => 'chart-column',
                'category' => 'Core Reports',
            ],
        ];
    }

    private function quoteFunnelReportData(): array
    {
        $quotes = $this->quoteRequests();
        $pendingStatuses = [QuoteRequest::STATUS_NEW, QuoteRequest::STATUS_PROCESSING, QuoteRequest::STATUS_EMAIL_FAILED];
        $approvedStatuses = [QuoteRequest::STATUS_QUOTED, QuoteRequest::STATUS_CREATED, QuoteRequest::STATUS_EMAILED];
        $declinedStatuses = [QuoteRequest::STATUS_CLOSED, QuoteRequest::STATUS_SPAM];

        $pendingCount = $quotes->whereIn('status', $pendingStatuses)->count();
        $approvedCount = $quotes->whereIn('status', $approvedStatuses)->count();
        $declinedCount = $quotes->whereIn('status', $declinedStatuses)->count();
        $topSources = $this->topCounts($quotes, fn (QuoteRequest $quote) => $quote->source_page, 6, 'Unknown Source');
        $topServices = $this->topCounts($quotes, fn (QuoteRequest $quote) => $quote->serviceTypeLabel(), 6, 'Unknown Service');
        $avgLeadTimeDays = $this->averageMoveLeadTimeDays($quotes);
        $quoteStatusSteps = collect(QuoteRequest::statusOptions())
            ->map(fn (string $label, string $status) => [
                'label' => $label,
                'count' => $quotes->where('status', $status)->count(),
            ])
            ->values();

        return [
            'slug' => 'quote-funnel',
            'title' => 'Quote Funnel Report',
            'subtitle' => 'Measure how quickly quote requests turn into approved work and where the funnel is leaking.',
            'badge' => 'Live Quote Data',
            'cards' => [
                $this->reportCard('Total Quotes', (string) $quotes->count(), 'message-square-quote', 'text-primary', $this->sparklineSeries($quotes, fn (QuoteRequest $quote) => $quote->created_at)),
                $this->reportCard('Pending', (string) $pendingCount, 'clock-3', 'text-warning', $this->sparklineSeries($quotes, fn (QuoteRequest $quote) => $quote->created_at, fn (QuoteRequest $quote) => in_array($quote->status, $pendingStatuses, true)), 'bar', '#f59e0b'),
                $this->reportCard('Approved', (string) $approvedCount, 'check-circle', 'text-success', $this->sparklineSeries($quotes, fn (QuoteRequest $quote) => $quote->created_at, fn (QuoteRequest $quote) => in_array($quote->status, $approvedStatuses, true)), 'bar', '#22b956'),
                $this->reportCard('Declined', (string) $declinedCount, 'x-circle', 'text-danger', $this->sparklineSeries($quotes, fn (QuoteRequest $quote) => $quote->created_at, fn (QuoteRequest $quote) => in_array($quote->status, $declinedStatuses, true)), 'area', '#f95c5c'),
            ],
            'charts' => [
                $this->areaChart(
                    'quote-funnel-trend',
                    'Quote Volume',
                    'See how many new quote requests are entering the funnel over the last 7 days.',
                    $this->dateSeries($quotes, fn (QuoteRequest $quote) => $quote->created_at, 7),
                    'Quotes',
                    ['#3b82f6'],
                    'col-xxl-8'
                ),
                $this->donutChart(
                    'quote-funnel-status',
                    'Funnel Outcome Mix',
                    'Status split from the current quote request records.',
                    [
                        'labels' => $quoteStatusSteps->pluck('label')->values()->all(),
                        'series' => $quoteStatusSteps->pluck('count')->values()->all(),
                    ],
                    ['#f59e0b', '#22b956', '#64748b', '#f95c5c'],
                    'col-xxl-4'
                ),
                $this->barChart(
                    'quote-funnel-status-steps',
                    'Status Step Breakdown',
                    'Use this to spot whether leads are stalling early or late in the funnel.',
                    [
                        'categories' => $quoteStatusSteps->pluck('label')->values()->all(),
                        'series' => [[
                            'name' => 'Quotes',
                            'data' => $quoteStatusSteps->pluck('count')->values()->all(),
                        ]],
                    ],
                    false,
                    ['#4d5761'],
                    ' quotes',
                    'col-xxl-12'
                ),
            ],
            'insights' => [
                $this->insightCard('Approval Rate', $this->formatPercent($approvedCount, $quotes->count()), 'Approved quotes divided by all tracked requests.'),
                $this->insightCard('Top Source', $topSources->keys()->first() ?? 'No source data yet', 'Most productive captured source page so far.'),
                $this->insightCard('Top Service', $topServices->keys()->first() ?? 'No service data yet', 'The service type currently driving the most quotes.'),
                $this->insightCard('Avg Move Lead Time', $avgLeadTimeDays > 0 ? number_format($avgLeadTimeDays, 1).' days' : 'N/A', 'Average time between request creation and planned move date.'),
            ],
            'table' => [
                'title' => 'Quote Funnel Details',
                'description' => 'Review the live quote requests feeding the funnel and jump into individual records quickly.',
                'search_placeholder' => 'Search customer, email, route, source, or service',
                'filters' => [
                    $this->tableFilter('status', 'Status', 'status', $quoteStatusSteps->pluck('label')->values()->all(), 'All statuses'),
                    $this->tableFilter('source', 'Source', 'source', $quotes->map(fn (QuoteRequest $quote) => $this->safeLabel($quote->source_page, 'Unknown Source'))->unique()->values()->all(), 'All sources'),
                ],
                'sorts' => [
                    $this->sortOption('newest', 'Newest first', 'dateSort', 'number', 'desc'),
                    $this->sortOption('oldest', 'Oldest first', 'dateSort', 'number', 'asc'),
                    $this->sortOption('customer', 'Customer A-Z', 'customer', 'string', 'asc'),
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'customer', 'label' => 'Customer'],
                    ['key' => 'route', 'label' => 'Route'],
                    ['key' => 'service', 'label' => 'Service'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'rows' => $quotes->map(function (QuoteRequest $quote) {
                    $displayStatus = [
                        'label' => $quote->statusLabel(),
                        'class' => $quote->statusBadgeClass(),
                    ];

                    return [
                        'cells' => [
                            'date' => $this->stackCell($quote->created_at?->format('Y-m-d') ?? 'N/A', $quote->created_at?->format('h:i A') ?? ''),
                            'customer' => $this->stackCell($quote->full_name, $quote->email),
                            'route' => $this->stackCell($this->safeLabel($quote->moving_from, 'Unknown Origin'), 'to '.$this->safeLabel($quote->moving_to, 'Unknown Destination')),
                            'service' => $this->stackCell($quote->serviceTypeLabel(), $this->safeLabel($quote->move_size, 'Size not set')),
                            'source' => $this->textCell($this->safeLabel($quote->source_page, 'Unknown Source')),
                            'status' => $this->badgeCell($displayStatus['label'], $displayStatus['class']),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', array_filter([
                                $quote->reference(),
                                $quote->full_name,
                                $quote->email,
                                $quote->moving_from,
                                $quote->moving_to,
                                $quote->serviceTypeLabel(),
                                $quote->source_page,
                                $displayStatus['label'],
                            ]))),
                            'status' => $this->normalizeFilterValue($displayStatus['label']),
                            'source' => $this->normalizeFilterValue($quote->source_page),
                            'dateSort' => $quote->created_at?->timestamp ?? 0,
                            'customer' => Str::lower($quote->full_name),
                        ],
                        'actions' => [
                            $this->actionLink('View', route('quotes.show', $quote), 'eye', 'btn-soft-primary'),
                        ],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function leadSourcesReportData(): array
    {
        $quotes = $this->quoteRequests();
        $sourceRows = $quotes
            ->groupBy(fn (QuoteRequest $quote) => $this->safeLabel($quote->source_page, 'Unknown Source'))
            ->map(function ($group, string $source) {
                $total = $group->count();
                $approved = $group->whereIn('status', [QuoteRequest::STATUS_QUOTED, QuoteRequest::STATUS_CREATED, QuoteRequest::STATUS_EMAILED])->count();
                $declined = $group->whereIn('status', [QuoteRequest::STATUS_CLOSED, QuoteRequest::STATUS_SPAM])->count();
                $emailFailed = $group->where('status', QuoteRequest::STATUS_EMAIL_FAILED)->count();

                return [
                    'source' => $source,
                    'total' => $total,
                    'approved' => $approved,
                    'declined' => $declined,
                    'email_failed' => $emailFailed,
                    'approval_rate' => $this->percentage($approved, $total),
                    'last_seen_at' => $group->max('created_at'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $bestSource = $sourceRows->sortByDesc('approval_rate')->first();
        $largestSource = $sourceRows->first();

        return [
            'slug' => 'lead-sources',
            'title' => 'Lead Sources Report',
            'subtitle' => 'Compare captured quote source pages by volume, quality, and delivery issues.',
            'badge' => 'Source Page Signals',
            'cards' => [
                $this->reportCard('Tracked Sources', (string) $sourceRows->count(), 'waypoints', 'text-primary'),
                $this->reportCard('Total Leads', (string) $quotes->count(), 'users', 'text-success', $this->sparklineSeries($quotes, fn (QuoteRequest $quote) => $quote->created_at), 'bar', '#22b956'),
                $this->reportCard('Approved Leads', (string) $quotes->whereIn('status', [QuoteRequest::STATUS_QUOTED, QuoteRequest::STATUS_CREATED, QuoteRequest::STATUS_EMAILED])->count(), 'check-circle', 'text-info', $this->sparklineSeries($quotes, fn (QuoteRequest $quote) => $quote->created_at, fn (QuoteRequest $quote) => in_array($quote->status, [QuoteRequest::STATUS_QUOTED, QuoteRequest::STATUS_CREATED, QuoteRequest::STATUS_EMAILED], true)), 'area', '#3b82f6'),
                $this->reportCard('Email Issues', (string) $quotes->where('status', QuoteRequest::STATUS_EMAIL_FAILED)->count(), 'triangle-alert', 'text-danger', $this->sparklineSeries($quotes, fn (QuoteRequest $quote) => $quote->created_at, fn (QuoteRequest $quote) => $quote->status === QuoteRequest::STATUS_EMAIL_FAILED), 'area', '#f95c5c'),
            ],
            'charts' => [
                $this->barChart(
                    'lead-sources-volume',
                    'Lead Volume by Source',
                    'Top source pages ranked by captured quote requests.',
                    [
                        'categories' => $sourceRows->take(8)->pluck('source')->values()->all(),
                        'series' => [[
                            'name' => 'Leads',
                            'data' => $sourceRows->take(8)->pluck('total')->values()->all(),
                        ]],
                    ],
                    true,
                    ['#4d5761'],
                    ' leads',
                    'col-xxl-8'
                ),
                $this->barChart(
                    'lead-sources-approval',
                    'Approval Rate by Source',
                    'See which source pages are bringing in the best-converting leads.',
                    [
                        'categories' => $sourceRows->take(6)->pluck('source')->values()->all(),
                        'series' => [[
                            'name' => 'Approval Rate',
                            'data' => $sourceRows->take(6)->pluck('approval_rate')->values()->all(),
                        ]],
                    ],
                    false,
                    ['#22b956'],
                    '%',
                    'col-xxl-4'
                ),
            ],
            'insights' => [
                $this->insightCard('Best Converting Source', $bestSource['source'] ?? 'No source data yet', ($bestSource['approval_rate'] ?? 0).'% approval rate'),
                $this->insightCard('Largest Source', $largestSource['source'] ?? 'No source data yet', ($largestSource['total'] ?? 0).' captured leads'),
                $this->insightCard('Sources with Email Issues', (string) $sourceRows->where('email_failed', '>', 0)->count(), 'Pages where follow-up delivery problems were recorded.'),
                $this->insightCard('Avg Leads per Source', $sourceRows->isNotEmpty() ? number_format($sourceRows->avg('total'), 1) : '0', 'Average lead volume per tracked source page.'),
            ],
            'table' => [
                'title' => 'Lead Source Breakdown',
                'description' => 'Review source quality and delivery performance side by side.',
                'search_placeholder' => 'Search source page or metrics',
                'filters' => [],
                'sorts' => [
                    $this->sortOption('highest_volume', 'Highest volume', 'totalSort', 'number', 'desc'),
                    $this->sortOption('best_conversion', 'Best conversion', 'approvalRateSort', 'number', 'desc'),
                    $this->sortOption('source', 'Source A-Z', 'source', 'string', 'asc'),
                ],
                'columns' => [
                    ['key' => 'source', 'label' => 'Source Page'],
                    ['key' => 'leads', 'label' => 'Leads'],
                    ['key' => 'approved', 'label' => 'Approved'],
                    ['key' => 'declined', 'label' => 'Declined'],
                    ['key' => 'email_failed', 'label' => 'Email Failed'],
                    ['key' => 'approval_rate', 'label' => 'Approval Rate'],
                ],
                'rows' => $sourceRows->map(function (array $row) {
                    return [
                        'cells' => [
                            'source' => $this->textCell($row['source']),
                            'leads' => $this->textCell((string) $row['total']),
                            'approved' => $this->textCell((string) $row['approved']),
                            'declined' => $this->textCell((string) $row['declined']),
                            'email_failed' => $this->textCell((string) $row['email_failed']),
                            'approval_rate' => $this->textCell($row['approval_rate'].'%'),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', [$row['source'], $row['total'], $row['approved'], $row['declined'], $row['email_failed'], $row['approval_rate']])),
                            'totalSort' => $row['total'],
                            'approvalRateSort' => $row['approval_rate'],
                            'source' => Str::lower($row['source']),
                        ],
                        'actions' => [],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function visitorReportsData(): array
    {
        $analytics = $this->googleAnalyticsVisitors();
        $summary = $analytics['summary'] ?? [];
        $daily = collect($analytics['daily'] ?? []);
        $pages = collect($analytics['pages'] ?? []);
        $channels = collect($analytics['channels'] ?? []);

        $topPage = $pages->first();
        $bestEngagementPage = $pages
            ->filter(fn (array $page) => (int) ($page['active_users'] ?? 0) > 0)
            ->sortByDesc('engagement_rate')
            ->first();
        $topChannel = $channels->first();
        $alerts = [];

        if (! empty($analytics['error'])) {
            $alerts[] = [
                'type' => ($analytics['configured'] ?? false) ? 'warning' : 'info',
                'title' => 'Google Analytics setup',
                'message' => $analytics['error'],
            ];
        }

        return [
            'slug' => 'visitor-reports',
            'title' => 'Visitor Insights',
            'subtitle' => 'Showing only GA4 Data API traffic for '.($analytics['date_range_label'] ?? 'the selected range').'.',
            'badge' => 'Google Analytics Data',
            'alerts' => $alerts,
            'cards' => [
                $this->reportCard('Active Users', number_format((int) ($summary['active_users'] ?? 0)), 'users', 'text-primary', $daily->pluck('active_users')->map(fn ($value) => (int) $value)->values()->all(), 'bar', '#3b82f6'),
                $this->reportCard('New Users', number_format((int) ($summary['new_users'] ?? 0)), 'user-plus', 'text-success', $daily->pluck('new_users')->map(fn ($value) => (int) $value)->values()->all(), 'bar', '#22b956'),
                $this->reportCard('Sessions', number_format((int) ($summary['sessions'] ?? 0)), 'mouse-pointer-click', 'text-info', $daily->pluck('sessions')->map(fn ($value) => (int) $value)->values()->all(), 'area', '#4d5761'),
                $this->reportCard('Page Views', number_format((int) ($summary['screen_page_views'] ?? 0)), 'eye', 'text-warning', $daily->pluck('screen_page_views')->map(fn ($value) => (int) $value)->values()->all(), 'area', '#f59e0b'),
            ],
            'charts' => [
                $this->areaChart(
                    'visitor-ga-trend',
                    'GA Visitor Trend',
                    'Daily active users from Google Analytics.',
                    [
                        'labels' => $daily->pluck('label')->values()->all(),
                        'series' => $daily->pluck('active_users')->map(fn ($value) => (int) $value)->values()->all(),
                    ],
                    'Active Users',
                    ['#3b82f6'],
                    'col-xxl-8',
                    '',
                    ' users'
                ),
                $this->donutChart(
                    'visitor-device-mix',
                    'Device Category',
                    'Active users split by GA device category.',
                    $analytics['devices'] ?? ['labels' => [], 'series' => []],
                    ['#22b956', '#3b82f6', '#f59e0b'],
                    'col-xxl-4'
                ),
                $this->barChart(
                    'visitor-ga-pages',
                    'Top Pages',
                    'Pages ranked by Google Analytics page views.',
                    [
                        'categories' => $pages->take(8)->pluck('page')->values()->all(),
                        'series' => [[
                            'name' => 'Views',
                            'data' => $pages->take(8)->pluck('screen_page_views')->map(fn ($value) => (int) $value)->values()->all(),
                        ]],
                    ],
                    true,
                    ['#4d5761'],
                    ' views',
                    'col-xxl-8'
                ),
                $this->barChart(
                    'visitor-ga-channels',
                    'Traffic Channels',
                    'Active users by Google Analytics default channel group.',
                    [
                        'categories' => $channels->pluck('channel')->values()->all(),
                        'series' => [[
                            'name' => 'Active Users',
                            'data' => $channels->pluck('active_users')->map(fn ($value) => (int) $value)->values()->all(),
                        ]],
                    ],
                    true,
                    ['#22b956'],
                    ' users',
                    'col-xxl-4'
                ),
            ],
            'insights' => [
                $this->insightCard('Top Page', $topPage['page'] ?? 'No GA page data yet', number_format((int) ($topPage['screen_page_views'] ?? 0)).' GA page views'),
                $this->insightCard('Best Engagement', $bestEngagementPage['page'] ?? 'No GA engagement data yet', $this->formatRatePercent((float) ($bestEngagementPage['engagement_rate'] ?? 0)).' engagement rate'),
                $this->insightCard('Top Channel', $topChannel['channel'] ?? 'No GA channel data yet', number_format((int) ($topChannel['active_users'] ?? 0)).' active users'),
                $this->insightCard('Data Source', 'Google Analytics', 'No quote, message, or local dashboard counters are used here.'),
            ],
            'table' => [
                'title' => 'GA Page Breakdown',
                'description' => 'Page-level traffic pulled directly from Google Analytics.',
                'search_placeholder' => 'Search GA pages',
                'filters' => [],
                'sorts' => [
                    $this->sortOption('highest_views', 'Highest views', 'viewSort', 'number', 'desc'),
                    $this->sortOption('highest_users', 'Highest users', 'userSort', 'number', 'desc'),
                    $this->sortOption('highest_sessions', 'Highest sessions', 'sessionSort', 'number', 'desc'),
                    $this->sortOption('highest_engagement', 'Highest engagement', 'engagementSort', 'number', 'desc'),
                    $this->sortOption('page', 'Page A-Z', 'page', 'string', 'asc'),
                ],
                'columns' => [
                    ['key' => 'page', 'label' => 'Page'],
                    ['key' => 'users', 'label' => 'Active Users'],
                    ['key' => 'views', 'label' => 'Views'],
                    ['key' => 'sessions', 'label' => 'Sessions'],
                    ['key' => 'engagement', 'label' => 'Engagement'],
                ],
                'rows' => $pages->map(function (array $row) {
                    $engagementRate = (float) ($row['engagement_rate'] ?? 0);

                    return [
                        'cells' => [
                            'page' => $this->textCell($row['page']),
                            'users' => $this->textCell(number_format((int) ($row['active_users'] ?? 0))),
                            'views' => $this->textCell(number_format((int) ($row['screen_page_views'] ?? 0))),
                            'sessions' => $this->textCell(number_format((int) ($row['sessions'] ?? 0))),
                            'engagement' => $this->textCell($this->formatRatePercent($engagementRate)),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', [$row['page'], $row['active_users'] ?? 0, $row['screen_page_views'] ?? 0, $row['sessions'] ?? 0, $this->formatRatePercent($engagementRate)])),
                            'viewSort' => (int) ($row['screen_page_views'] ?? 0),
                            'userSort' => (int) ($row['active_users'] ?? 0),
                            'sessionSort' => (int) ($row['sessions'] ?? 0),
                            'engagementSort' => $engagementRate,
                            'page' => Str::lower($row['page']),
                        ],
                        'actions' => [],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function customerLifecycleReportData(): array
    {
        $customers = $this->customersCollection();
        $statusCounts = collect(Customer::statusOptions())->mapWithKeys(
            fn (string $label, string $status) => [$label => $customers->where('status', $status)->count()]
        );
        $topServices = $this->topCounts($customers, fn (Customer $customer) => $customer->latestServiceLabel(), 6, 'Unknown Service');

        return [
            'slug' => 'customer-lifecycle',
            'title' => 'Customer Report',
            'subtitle' => 'Track how synced leads are progressing through the customer journey and where reactivation is needed.',
            'badge' => 'Synced Customer Data',
            'cards' => [
                $this->reportCard('Total Customers', (string) $customers->count(), 'users', 'text-primary', $this->sparklineSeries($customers, fn (Customer $customer) => $customer->first_seen_at ?? $customer->created_at)),
                $this->reportCard('Leads', (string) $customers->where('status', Customer::STATUS_LEAD)->count(), 'user-plus', 'text-warning', $this->sparklineSeries($customers, fn (Customer $customer) => $customer->first_seen_at ?? $customer->created_at, fn (Customer $customer) => $customer->status === Customer::STATUS_LEAD), 'bar', '#f59e0b'),
                $this->reportCard('Active Clients', (string) $customers->where('status', Customer::STATUS_ACTIVE_CLIENT)->count(), 'user-check', 'text-info', $this->sparklineSeries($customers, fn (Customer $customer) => $customer->last_quote_at ?? $customer->created_at, fn (Customer $customer) => $customer->status === Customer::STATUS_ACTIVE_CLIENT), 'bar', '#3b82f6'),
                $this->reportCard('Completed', (string) $customers->where('status', Customer::STATUS_COMPLETED)->count(), 'shield-check', 'text-success', $this->sparklineSeries($customers, fn (Customer $customer) => $customer->last_quote_at ?? $customer->created_at, fn (Customer $customer) => $customer->status === Customer::STATUS_COMPLETED), 'area', '#22b956'),
            ],
            'charts' => [
                $this->areaChart(
                    'customer-lifecycle-trend',
                    'New Customer Trend',
                    'See when new customer records were first captured.',
                    $this->dateSeries($customers, fn (Customer $customer) => $customer->first_seen_at ?? $customer->created_at, 7),
                    'Customers',
                    ['#3b82f6'],
                    'col-xxl-8'
                ),
                $this->donutChart(
                    'customer-lifecycle-status',
                    'Lifecycle Split',
                    'Compare lead, active, completed, and inactive customers.',
                    [
                        'labels' => $statusCounts->keys()->values()->all(),
                        'series' => $statusCounts->values()->values()->all(),
                    ],
                    ['#f59e0b', '#3b82f6', '#22b956', '#64748b'],
                    'col-xxl-4'
                ),
                $this->barChart(
                    'customer-lifecycle-service',
                    'Top Services in Lifecycle',
                    'Latest requested services across your synced customer base.',
                    [
                        'categories' => $topServices->keys()->values()->all(),
                        'series' => [[
                            'name' => 'Customers',
                            'data' => $topServices->values()->values()->all(),
                        ]],
                    ],
                    true,
                    ['#4d5761'],
                    ' customers',
                    'col-xxl-12'
                ),
            ],
            'insights' => [
                $this->insightCard('Inactive Customers', (string) $customers->where('status', Customer::STATUS_INACTIVE)->count(), 'Customers that may need a reactivation campaign.'),
                $this->insightCard('Repeat Customers', (string) $customers->where('quotes_count', '>', 1)->count(), 'Customers who have submitted more than one quote request.'),
                $this->insightCard('Avg Quotes per Customer', $customers->isNotEmpty() ? number_format($customers->avg('quotes_count'), 1) : '0', 'Useful for spotting stronger relationship depth.'),
                $this->insightCard('Top Service', $topServices->keys()->first() ?? 'No service data yet', 'Most common latest service type among synced customers.'),
            ],
            'table' => [
                'title' => 'Customer Lifecycle Details',
                'description' => 'Filter your customer base by lifecycle stage, service, and engagement depth.',
                'search_placeholder' => 'Search customer, route, service, or status',
                'filters' => [
                    $this->tableFilter('status', 'Status', 'status', array_values(Customer::statusOptions()), 'All statuses'),
                    $this->tableFilter('service', 'Service', 'service', $customers->map(fn (Customer $customer) => $customer->latestServiceLabel())->unique()->values()->all(), 'All services'),
                ],
                'sorts' => [
                    $this->sortOption('newest', 'Newest first', 'dateSort', 'number', 'desc'),
                    $this->sortOption('oldest', 'Oldest first', 'dateSort', 'number', 'asc'),
                    $this->sortOption('customer', 'Customer A-Z', 'customer', 'string', 'asc'),
                    $this->sortOption('quotes', 'Most quotes', 'quotesSort', 'number', 'desc'),
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'First Seen'],
                    ['key' => 'customer', 'label' => 'Customer'],
                    ['key' => 'route', 'label' => 'Latest Route'],
                    ['key' => 'service', 'label' => 'Latest Service'],
                    ['key' => 'volume', 'label' => 'Quote Volume'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'rows' => $customers->map(function (Customer $customer) {
                    return [
                        'cells' => [
                            'date' => $this->stackCell(
                                $customer->first_seen_at?->format('Y-m-d') ?? 'N/A',
                                $customer->last_quote_at ? 'Last quote '.$customer->last_quote_at->format('Y-m-d') : ''
                            ),
                            'customer' => $this->stackCell($customer->full_name, $customer->email),
                            'route' => $this->textCell($customer->latestRouteSummary()),
                            'service' => $this->textCell($customer->latestServiceLabel()),
                            'volume' => $this->stackCell((string) $customer->quotes_count.' total', (string) $customer->approved_quotes_count.' approved / '.(string) $customer->declined_quotes_count.' declined'),
                            'status' => $this->badgeCell($customer->statusLabel(), $customer->statusBadgeClass()),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', [
                                $customer->full_name,
                                $customer->email,
                                $customer->phone,
                                $customer->latestRouteSummary(),
                                $customer->latestServiceLabel(),
                                $customer->statusLabel(),
                            ])),
                            'status' => $this->normalizeFilterValue($customer->statusLabel()),
                            'service' => $this->normalizeFilterValue($customer->latestServiceLabel()),
                            'dateSort' => $customer->first_seen_at?->timestamp ?? ($customer->created_at?->timestamp ?? 0),
                            'customer' => Str::lower($customer->full_name),
                            'quotesSort' => $customer->quotes_count,
                        ],
                        'actions' => [
                            $this->actionLink('View', route('customers.show', $customer), 'eye', 'btn-soft-primary'),
                        ],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function quotationPipelineReportData(): array
    {
        $quotations = $this->quotationsCollection();
        $totalValue = $quotations->sum(fn (Quotation $quotation) => (float) ($quotation->quote_amount ?? 0));
        $draftCount = $quotations->where('status', 'draft')->count();
        $sentCount = $quotations->where('status', 'sent')->count();
        $expiringSoon = $quotations->filter(fn (Quotation $quotation) => $quotation->quote_valid_until && $quotation->quote_valid_until->isFuture() && $quotation->quote_valid_until->diffInDays(now()) <= 7)->count();

        return [
            'slug' => 'quotation-pipeline',
            'title' => 'Quotation Pipeline Report',
            'subtitle' => 'Monitor drafts, sent quotations, and quote validity so the team can close faster.',
            'badge' => 'Live Quotation Data',
            'cards' => [
                $this->reportCard('Total Quotations', (string) $quotations->count(), 'file-text', 'text-primary', $this->sparklineSeries($quotations, fn (Quotation $quotation) => $quotation->created_at)),
                $this->reportCard('Drafts', (string) $draftCount, 'square-pen', 'text-warning', $this->sparklineSeries($quotations, fn (Quotation $quotation) => $quotation->created_at, fn (Quotation $quotation) => $quotation->status === 'draft'), 'bar', '#f59e0b'),
                $this->reportCard('Sent', (string) $sentCount, 'send', 'text-success', $this->sparklineSeries($quotations, fn (Quotation $quotation) => $quotation->sent_at ?? $quotation->created_at, fn (Quotation $quotation) => $quotation->status === 'sent'), 'bar', '#22b956'),
                $this->reportCard('Avg Value', $this->formatCurrency($quotations->avg('quote_amount') ?? 0), 'wallet', 'text-info'),
            ],
            'charts' => [
                $this->areaChart(
                    'quotation-pipeline-trend',
                    'Quotation Creation Trend',
                    'See how many quotations are being generated over the last 7 days.',
                    $this->dateSeries($quotations, fn (Quotation $quotation) => $quotation->created_at, 7),
                    'Quotations',
                    ['#3b82f6'],
                    'col-xxl-8'
                ),
                $this->donutChart(
                    'quotation-pipeline-status',
                    'Quotation Status Mix',
                    'Draft and sent pipeline stages based on the current quotations table.',
                    [
                        'labels' => $quotations->pluck('status')->map(fn (string $status) => Str::headline($status))->unique()->values()->all(),
                        'series' => $quotations->groupBy('status')->map->count()->values()->all(),
                    ],
                    ['#f59e0b', '#22b956', '#64748b', '#3b82f6'],
                    'col-xxl-4'
                ),
            ],
            'insights' => [
                $this->insightCard('Pipeline Value', $this->formatCurrency($totalValue), 'Total live quotation value currently tracked.'),
                $this->insightCard('Projected Deposits', $this->formatCurrency($quotations->sum(fn (Quotation $quotation) => ((float) ($quotation->quote_amount ?? 0)) * (((float) ($quotation->deposit_percentage ?? 0)) / 100))), 'Estimated deposits based on current quotation terms.'),
                $this->insightCard('Expiring Soon', (string) $expiringSoon, 'Quotations valid for 7 days or less from today.'),
                $this->insightCard('Send Rate', $this->formatPercent($sentCount, $quotations->count()), 'Sent quotations divided by total quotations.'),
            ],
            'table' => [
                'title' => 'Quotation Pipeline Details',
                'description' => 'Review draft and sent quotations together with validity and value.',
                'search_placeholder' => 'Search customer, route, status, or amount',
                'filters' => [
                    $this->tableFilter('status', 'Status', 'status', $quotations->pluck('status')->map(fn (string $status) => Str::headline($status))->unique()->values()->all(), 'All statuses'),
                ],
                'sorts' => [
                    $this->sortOption('newest', 'Newest first', 'dateSort', 'number', 'desc'),
                    $this->sortOption('oldest', 'Oldest first', 'dateSort', 'number', 'asc'),
                    $this->sortOption('highest_amount', 'Highest amount', 'amountSort', 'number', 'desc'),
                    $this->sortOption('customer', 'Customer A-Z', 'customer', 'string', 'asc'),
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Quote Date'],
                    ['key' => 'customer', 'label' => 'Customer'],
                    ['key' => 'validity', 'label' => 'Valid Until'],
                    ['key' => 'amount', 'label' => 'Amount'],
                    ['key' => 'deposit', 'label' => 'Deposit'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'rows' => $quotations->map(function (Quotation $quotation) {
                    $customerName = $quotation->quoteRequest?->full_name ?? 'Unknown Customer';
                    $statusLabel = Str::headline((string) $quotation->status);
                    $statusClass = $quotation->status === 'sent' ? 'success' : ($quotation->status === 'draft' ? 'warning' : 'secondary');

                    return [
                        'cells' => [
                            'date' => $this->stackCell($quotation->quote_date?->format('Y-m-d') ?? 'N/A', $quotation->created_at?->format('h:i A') ?? ''),
                            'customer' => $this->stackCell($customerName, $quotation->quoteRequest?->reference() ?? 'No reference'),
                            'validity' => $this->stackCell($quotation->quote_valid_until?->format('Y-m-d') ?? 'N/A', $quotation->quote_valid_until ? $quotation->quote_valid_until->diffForHumans() : ''),
                            'amount' => $this->textCell($this->formatCurrency($quotation->quote_amount ?? 0)),
                            'deposit' => $this->textCell($this->formatCurrency(((float) ($quotation->quote_amount ?? 0)) * (((float) ($quotation->deposit_percentage ?? 0)) / 100))),
                            'status' => $this->badgeCell($statusLabel, $statusClass),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', [
                                $customerName,
                                $quotation->quoteRequest?->reference() ?? '',
                                $quotation->moving_from,
                                $quotation->moving_to,
                                $statusLabel,
                                $quotation->quote_amount,
                            ])),
                            'status' => $this->normalizeFilterValue($statusLabel),
                            'dateSort' => $quotation->created_at?->timestamp ?? 0,
                            'amountSort' => (float) ($quotation->quote_amount ?? 0),
                            'customer' => Str::lower($customerName),
                        ],
                        'actions' => [
                            $this->actionLink('View', route('quotations.show', $quotation), 'eye', 'btn-soft-primary'),
                        ],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function financialReportData(): array
    {
        $invoices = $this->invoicesCollection();
        $quotations = $this->quotationsCollection();
        $invoiceStatusOptions = collect(Invoice::statusOptions());
        $invoiceValueByStatus = $invoiceStatusOptions
            ->map(fn (string $label, string $status) => [
                'status' => $label,
                'value' => (float) $invoices->where('status', $status)->sum('total_amount'),
            ])
            ->filter(fn (array $row) => $row['value'] > 0)
            ->values();
        $quotedPipelineValue = $quotations->sum(fn (Quotation $quotation) => (float) ($quotation->quote_amount ?? 0));
        $paidValue = $invoices->where('status', Invoice::STATUS_PAID)->sum('total_amount');
        $outstandingStatuses = collect([Invoice::STATUS_UNPAID, Invoice::STATUS_PENDING, Invoice::STATUS_SENT])
            ->filter(fn (string $status) => $invoiceStatusOptions->has($status));
        $outstandingValue = $invoices
            ->whereIn('status', $outstandingStatuses->all())
            ->sum('total_amount');

        $trend = $this->dateSeries(
            $invoices,
            fn (Invoice $invoice) => $invoice->invoice_date ?? $invoice->created_at,
            7,
            fn (Invoice $invoice) => (float) ($invoice->total_amount ?? 0)
        );

        $quotationValueByStatus = $quotations
            ->groupBy('status')
            ->map(fn ($group, string $status) => [
                'status' => Str::headline($status),
                'value' => (float) $group->sum('quote_amount'),
            ])
            ->sortByDesc('value')
            ->values();

        return [
            'slug' => 'financial-reports',
            'title' => 'Financial Report',
            'subtitle' => 'Summarize invoice revenue, paid value, outstanding balances, and quotation pipeline context from live database records.',
            'badge' => 'Live Finance Data',
            'cards' => [
                $this->reportCard('Total Invoiced', $this->formatCurrency($invoices->sum('total_amount')), 'wallet', 'text-primary', $this->sparklineSeries($invoices, fn (Invoice $invoice) => $invoice->invoice_date ?? $invoice->created_at, null, 11), 'area', '#3b82f6'),
                $this->reportCard('Paid Revenue', $this->formatCurrency($paidValue), 'check-circle', 'text-success', $this->sparklineSeries($invoices, fn (Invoice $invoice) => $invoice->invoice_date ?? $invoice->created_at, fn (Invoice $invoice) => $invoice->status === Invoice::STATUS_PAID, 11), 'bar', '#22b956'),
                $this->reportCard('Outstanding', $this->formatCurrency($outstandingValue), 'clock-3', 'text-warning', $this->sparklineSeries($invoices, fn (Invoice $invoice) => $invoice->invoice_date ?? $invoice->created_at, fn (Invoice $invoice) => $outstandingStatuses->contains($invoice->status), 11), 'bar', '#f59e0b'),
                $this->reportCard('Quoted Pipeline', $this->formatCurrency($quotedPipelineValue), 'file-text', 'text-info', $this->sparklineSeries($quotations, fn (Quotation $quotation) => $quotation->quote_date ?? $quotation->created_at, null, 11), 'area', '#4d5761'),
            ],
            'charts' => [
                $this->areaChart(
                    'financial-trend',
                    'Invoice Revenue Trend',
                    'Daily invoice value based on invoice dates and live invoice records.',
                    $trend,
                    'Invoice Value',
                    ['#3b82f6'],
                    'col-xxl-8',
                    'KES '
                ),
                $this->barChart(
                    'financial-invoice-status-values',
                    'Invoice Value by Status',
                    'Compare financial value across the statuses stored on invoices.',
                    [
                        'categories' => $invoiceValueByStatus->pluck('status')->values()->all(),
                        'series' => [[
                            'name' => 'Value',
                            'data' => $invoiceValueByStatus->pluck('value')->values()->all(),
                        ]],
                    ],
                    true,
                    ['#22b956'],
                    '',
                    'col-xxl-4',
                    'KES '
                ),
                $this->barChart(
                    'financial-quotation-status-values',
                    'Quotation Pipeline by Status',
                    'Quotation value remains visible as pipeline context, separate from invoice revenue.',
                    [
                        'categories' => $quotationValueByStatus->pluck('status')->values()->all(),
                        'series' => [[
                            'name' => 'Quoted Value',
                            'data' => $quotationValueByStatus->pluck('value')->values()->all(),
                        ]],
                    ],
                    true,
                    ['#4d5761'],
                    '',
                    'col-xxl-12',
                    'KES '
                ),
            ],
            'insights' => [
                $this->insightCard('Average Invoice', $this->formatCurrency($invoices->avg('total_amount') ?? 0), 'Average value across all live invoices.'),
                $this->insightCard('Highest Invoice', $this->formatCurrency($invoices->max('total_amount') ?? 0), 'Largest single invoice currently stored.'),
                $this->insightCard('Payment Completion', $this->formatPercent($paidValue, max(1, $invoices->sum('total_amount'))), 'Paid invoice value as a share of total invoiced value.'),
                $this->insightCard('Projected Deposits', $this->formatCurrency($quotations->sum(fn (Quotation $quotation) => ((float) ($quotation->quote_amount ?? 0)) * (((float) ($quotation->deposit_percentage ?? 0)) / 100))), 'Estimated deposits from current quotation terms.'),
            ],
            'table' => [
                'title' => 'Financial Invoice Details',
                'description' => 'Review each invoice with customer, total, tax, due date, status, and payment method in one place.',
                'search_placeholder' => 'Search customer, invoice number, amount, status, method, or route',
                'filters' => [
                    $this->tableFilter('status', 'Status', 'status', $invoices->map(fn (Invoice $invoice) => $invoice->statusLabel())->unique()->values()->all(), 'All statuses'),
                    $this->tableFilter('payment', 'Payment', 'payment', $invoices->map(fn (Invoice $invoice) => $invoice->paymentMethodLabel())->unique()->values()->all(), 'All methods'),
                ],
                'sorts' => [
                    $this->sortOption('highest_amount', 'Highest amount', 'amountSort', 'number', 'desc'),
                    $this->sortOption('newest', 'Newest first', 'dateSort', 'number', 'desc'),
                    $this->sortOption('due_soonest', 'Due soonest', 'dueSort', 'number', 'asc'),
                    $this->sortOption('customer', 'Customer A-Z', 'customer', 'string', 'asc'),
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Invoice Date'],
                    ['key' => 'customer', 'label' => 'Customer'],
                    ['key' => 'amount', 'label' => 'Total'],
                    ['key' => 'tax', 'label' => 'Tax'],
                    ['key' => 'due', 'label' => 'Due Date'],
                    ['key' => 'payment', 'label' => 'Payment'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'rows' => $invoices->map(function (Invoice $invoice) {
                    return [
                        'cells' => [
                            'date' => $this->stackCell($invoice->invoice_date?->format('Y-m-d') ?? 'N/A', $invoice->invoice_number),
                            'customer' => $this->stackCell($invoice->customer_name, $invoice->customer_email),
                            'amount' => $this->textCell($this->formatCurrency($invoice->total_amount ?? 0)),
                            'tax' => $this->textCell($this->formatCurrency($invoice->tax ?? 0)),
                            'due' => $this->stackCell($invoice->due_date?->format('Y-m-d') ?? 'N/A', $invoice->due_date ? $invoice->due_date->diffForHumans() : ''),
                            'payment' => $this->textCell($invoice->paymentMethodLabel()),
                            'status' => $this->badgeCell($invoice->statusLabel(), $invoice->statusBadgeClass()),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', [
                                $invoice->invoice_number,
                                $invoice->customer_name,
                                $invoice->customer_email,
                                $invoice->customer_phone,
                                $invoice->move_origin,
                                $invoice->move_destination,
                                $invoice->total_amount,
                                $invoice->statusLabel(),
                                $invoice->paymentMethodLabel(),
                            ])),
                            'status' => $this->normalizeFilterValue($invoice->statusLabel()),
                            'payment' => $this->normalizeFilterValue($invoice->paymentMethodLabel()),
                            'amountSort' => (float) ($invoice->total_amount ?? 0),
                            'dateSort' => $invoice->invoice_date?->timestamp ?? ($invoice->created_at?->timestamp ?? 0),
                            'dueSort' => $invoice->due_date?->timestamp ?? PHP_INT_MAX,
                            'customer' => Str::lower($invoice->customer_name),
                        ],
                        'actions' => [
                            $this->actionLink('View', route('invoice.details', ['invoice' => $invoice->id]), 'eye', 'btn-soft-primary'),
                        ],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function messageResponseReportData(): array
    {
        $messages = $this->messagesCollection();
        $statusCounts = $messages->groupBy('status')->map->count();
        $avgResponseHours = $messages
            ->filter(fn (Message $message) => $message->responded_at && $message->created_at)
            ->map(fn (Message $message) => $message->created_at->diffInMinutes($message->responded_at) / 60)
            ->avg();
        $topOrigins = $this->topCounts($messages, fn (Message $message) => $message->origin_page, 6, 'Unknown Origin');

        return [
            'slug' => 'message-response',
            'title' => 'Message Response Report',
            'subtitle' => 'Track inbox backlog, reply speed, and which origin pages are creating the most conversation load.',
            'badge' => 'Live Message Data',
            'cards' => [
                $this->reportCard('Total Messages', (string) $messages->count(), 'mail', 'text-primary', $this->sparklineSeries($messages, fn (Message $message) => $message->created_at)),
                $this->reportCard('Unread', (string) $messages->where('status', 'unread')->count(), 'mail', 'text-danger', $this->sparklineSeries($messages, fn (Message $message) => $message->created_at, fn (Message $message) => $message->status === 'unread'), 'area', '#f95c5c'),
                $this->reportCard('Responded', (string) $messages->where('status', 'responded')->count(), 'reply', 'text-success', $this->sparklineSeries($messages, fn (Message $message) => $message->responded_at ?? $message->created_at, fn (Message $message) => $message->status === 'responded'), 'bar', '#22b956'),
                $this->reportCard('Drafts', (string) $messages->where('status', 'draft')->count(), 'square-pen', 'text-warning', $this->sparklineSeries($messages, fn (Message $message) => $message->created_at, fn (Message $message) => $message->status === 'draft'), 'bar', '#f59e0b'),
            ],
            'charts' => [
                $this->areaChart(
                    'message-response-trend',
                    'Message Intake Trend',
                    'See new inbox load over the last 7 days.',
                    $this->dateSeries($messages, fn (Message $message) => $message->created_at, 7),
                    'Messages',
                    ['#3b82f6'],
                    'col-xxl-8'
                ),
                $this->donutChart(
                    'message-response-status',
                    'Message Status Mix',
                    'Compare unread, read, responded, draft, and sent messages.',
                    [
                        'labels' => $statusCounts->keys()->map(fn (string $status) => Str::headline($status))->values()->all(),
                        'series' => $statusCounts->values()->values()->all(),
                    ],
                    ['#f95c5c', '#64748b', '#22b956', '#f59e0b', '#3b82f6'],
                    'col-xxl-4'
                ),
            ],
            'insights' => [
                $this->insightCard('Response Rate', $this->formatPercent($messages->where('status', 'responded')->count(), $messages->count()), 'Responded messages divided by total messages.'),
                $this->insightCard('Avg Response Time', $avgResponseHours ? number_format($avgResponseHours, 1).' hrs' : 'N/A', 'Based on messages that already have a recorded response.'),
                $this->insightCard('Top Origin', $topOrigins->keys()->first() ?? 'No origin data yet', 'Most common page origin for messages currently tracked.'),
                $this->insightCard('Unread > 24h', (string) $messages->filter(fn (Message $message) => $message->status === 'unread' && $message->created_at && $message->created_at->lt(now()->subDay()))->count(), 'Messages likely needing faster follow-up.'),
            ],
            'table' => [
                'title' => 'Message Response Details',
                'description' => 'Filter the inbox by status and origin to spot backlog and bottlenecks quickly.',
                'search_placeholder' => 'Search sender, subject, message, or origin',
                'filters' => [
                    $this->tableFilter('status', 'Status', 'status', $statusCounts->keys()->map(fn (string $status) => Str::headline($status))->values()->all(), 'All statuses'),
                    $this->tableFilter('origin', 'Origin', 'origin', $messages->map(fn (Message $message) => $this->safeLabel($message->origin_page, 'Unknown Origin'))->unique()->values()->all(), 'All origins'),
                ],
                'sorts' => [
                    $this->sortOption('newest', 'Newest first', 'dateSort', 'number', 'desc'),
                    $this->sortOption('oldest', 'Oldest first', 'dateSort', 'number', 'asc'),
                    $this->sortOption('sender', 'Sender A-Z', 'sender', 'string', 'asc'),
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'sender', 'label' => 'Sender'],
                    ['key' => 'subject', 'label' => 'Subject'],
                    ['key' => 'origin', 'label' => 'Origin'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'response', 'label' => 'Response'],
                ],
                'rows' => $messages->map(function (Message $message) {
                    $statusLabel = Str::headline($message->status);
                    $statusClass = match ($message->status) {
                        'unread' => 'danger',
                        'responded' => 'success',
                        'draft' => 'warning',
                        'sent' => 'info',
                        default => 'secondary',
                    };

                    return [
                        'cells' => [
                            'date' => $this->stackCell($message->created_at?->format('Y-m-d') ?? 'N/A', $message->created_at?->format('h:i A') ?? ''),
                            'sender' => $this->stackCell($message->name, $message->email),
                            'subject' => $this->textCell(Str::limit($message->subject, 54)),
                            'origin' => $this->textCell($this->safeLabel($message->origin_page, 'Unknown Origin')),
                            'status' => $this->badgeCell($statusLabel, $statusClass),
                            'response' => $this->textCell($message->responded_at ? $message->responded_at->diffForHumans($message->created_at, true).' later' : 'Awaiting response'),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', [
                                $message->name,
                                $message->email,
                                $message->subject,
                                $message->message,
                                $message->origin_page,
                                $statusLabel,
                            ])),
                            'status' => $this->normalizeFilterValue($statusLabel),
                            'origin' => $this->normalizeFilterValue($message->origin_page),
                            'dateSort' => $message->created_at?->timestamp ?? 0,
                            'sender' => Str::lower($message->name),
                        ],
                        'actions' => [
                            $this->actionLink('View', route('messages.show', $message), 'eye', 'btn-soft-primary'),
                        ],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function routeDemandReportData(): array
    {
        $quotes = $this->quoteRequests();
        $routeRows = $quotes
            ->groupBy(fn (QuoteRequest $quote) => $this->safeLabel($quote->moving_from, 'Unknown Origin').' -> '.$this->safeLabel($quote->moving_to, 'Unknown Destination'))
            ->map(function ($group, string $route) {
                $latest = $group->sortByDesc('created_at')->first();
                $topService = $this->topCounts($group, fn (QuoteRequest $quote) => $quote->serviceTypeLabel(), 1, 'Unknown Service')->keys()->first();

                return [
                    'route' => $route,
                    'total' => $group->count(),
                    'approved' => $group->whereIn('status', [
                        QuoteRequest::STATUS_QUOTED,
                        QuoteRequest::STATUS_CREATED,
                        QuoteRequest::STATUS_EMAILED,
                    ])->count(),
                    'top_service' => $topService,
                    'last_seen_at' => $latest?->created_at,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $serviceCounts = $this->topCounts($quotes, fn (QuoteRequest $quote) => $quote->serviceTypeLabel(), 6, 'Unknown Service');
        $originCounts = $this->topCounts($quotes, fn (QuoteRequest $quote) => $quote->moving_from, 6, 'Unknown Origin');
        $destinationCounts = $this->topCounts($quotes, fn (QuoteRequest $quote) => $quote->moving_to, 6, 'Unknown Destination');

        return [
            'slug' => 'route-demand',
            'title' => 'Route Demand Report',
            'subtitle' => 'Find the busiest routes, the most-requested services, and where operational demand is strongest.',
            'badge' => 'Live Route Signals',
            'cards' => [
                $this->reportCard('Unique Routes', (string) $routeRows->count(), 'route', 'text-primary'),
                $this->reportCard('Unique Origins', (string) $originCounts->count(), 'map-pinned', 'text-info'),
                $this->reportCard('Unique Destinations', (string) $destinationCounts->count(), 'route', 'text-success'),
                $this->reportCard('Scheduled Moves', (string) $quotes->filter(fn (QuoteRequest $quote) => $quote->move_date !== null)->count(), 'calendar-range', 'text-warning'),
            ],
            'charts' => [
                $this->barChart(
                    'route-demand-routes',
                    'Top Routes',
                    'The busiest origin-to-destination combinations from captured quote requests.',
                    [
                        'categories' => $routeRows->take(8)->pluck('route')->values()->all(),
                        'series' => [[
                            'name' => 'Quotes',
                            'data' => $routeRows->take(8)->pluck('total')->values()->all(),
                        ]],
                    ],
                    true,
                    ['#4d5761'],
                    ' quotes',
                    'col-xxl-8'
                ),
                $this->donutChart(
                    'route-demand-services',
                    'Service Mix',
                    'Most common services across all captured route demand.',
                    [
                        'labels' => $serviceCounts->keys()->values()->all(),
                        'series' => $serviceCounts->values()->values()->all(),
                    ],
                    ['#3b82f6', '#22b956', '#f59e0b', '#f95c5c', '#64748b', '#0ea5e9'],
                    'col-xxl-4'
                ),
            ],
            'insights' => [
                $this->insightCard('Top Route', $routeRows->pluck('route')->first() ?? 'No route data yet', ($routeRows->pluck('total')->first() ?? 0).' quote requests'),
                $this->insightCard('Top Origin', $originCounts->keys()->first() ?? 'No origin data yet', 'Most frequent starting location.'),
                $this->insightCard('Top Destination', $destinationCounts->keys()->first() ?? 'No destination data yet', 'Most frequent destination location.'),
                $this->insightCard('Top Service', $serviceCounts->keys()->first() ?? 'No service data yet', 'Most requested service on current routes.'),
            ],
            'table' => [
                'title' => 'Route Demand Details',
                'description' => 'Use this to spot where route demand and service concentration are strongest.',
                'search_placeholder' => 'Search route or service',
                'filters' => [
                    $this->tableFilter('service', 'Top Service', 'service', $routeRows->pluck('top_service')->filter()->unique()->values()->all(), 'All services'),
                ],
                'sorts' => [
                    $this->sortOption('highest_volume', 'Highest volume', 'volumeSort', 'number', 'desc'),
                    $this->sortOption('route', 'Route A-Z', 'route', 'string', 'asc'),
                    $this->sortOption('latest', 'Latest activity', 'dateSort', 'number', 'desc'),
                ],
                'columns' => [
                    ['key' => 'route', 'label' => 'Route'],
                    ['key' => 'volume', 'label' => 'Quotes'],
                    ['key' => 'approved', 'label' => 'Approved'],
                    ['key' => 'service', 'label' => 'Top Service'],
                    ['key' => 'last_seen', 'label' => 'Last Seen'],
                ],
                'rows' => $routeRows->map(function (array $row) {
                    return [
                        'cells' => [
                            'route' => $this->textCell($row['route']),
                            'volume' => $this->badgeCell((string) $row['total'], 'primary'),
                            'approved' => $this->textCell((string) $row['approved']),
                            'service' => $this->textCell($row['top_service'] ?: 'Unknown Service'),
                            'last_seen' => $this->textCell($row['last_seen_at']?->format('Y-m-d') ?? 'N/A'),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', [$row['route'], $row['top_service'], $row['total'], $row['approved']])),
                            'service' => $this->normalizeFilterValue($row['top_service']),
                            'volumeSort' => $row['total'],
                            'route' => Str::lower($row['route']),
                            'dateSort' => $row['last_seen_at']?->timestamp ?? 0,
                        ],
                        'actions' => [],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function atRiskFollowUpReportData(): array
    {
        $quotes = $this->quoteRequests();
        $messages = $this->messagesCollection();
        $quotations = $this->quotationsCollection();
        $customers = $this->customersCollection();

        $alerts = collect();

        $alerts = $alerts
            ->merge($quotes->filter(fn (QuoteRequest $quote) => in_array($quote->status, ['new', 'processing', 'emailed'], true) && $quote->created_at && $quote->created_at->lt(now()->subDays(3)))
                ->map(fn (QuoteRequest $quote) => $this->followUpAlert(
                    'Stale Quote',
                    $quote->created_at,
                    $quote->full_name,
                    'Quote has been pending follow-up for more than 3 days.',
                    'High',
                    route('quotes.show', $quote)
                )))
            ->merge($quotes->where('status', 'email_failed')
                ->map(fn (QuoteRequest $quote) => $this->followUpAlert(
                    'Email Failed',
                    $quote->created_at,
                    $quote->full_name,
                    'Email delivery failed and likely blocked lead follow-up.',
                    'High',
                    route('quotes.show', $quote)
                )))
            ->merge($messages->filter(fn (Message $message) => $message->status === 'unread' && $message->created_at && $message->created_at->lt(now()->subDay()))
                ->map(fn (Message $message) => $this->followUpAlert(
                    'Unread Message',
                    $message->created_at,
                    $message->name,
                    'Message has been unread for more than 24 hours.',
                    'High',
                    route('messages.show', $message)
                )))
            ->merge($quotations->filter(fn (Quotation $quotation) => $quotation->status === 'draft' && $quotation->created_at && $quotation->created_at->lt(now()->subDays(2)))
                ->map(fn (Quotation $quotation) => $this->followUpAlert(
                    'Draft Quotation',
                    $quotation->created_at,
                    $quotation->quoteRequest?->full_name ?? 'Unknown Customer',
                    'Quotation draft has not been sent for more than 2 days.',
                    'Medium',
                    route('quotations.show', $quotation)
                )))
            ->merge($customers->filter(fn (Customer $customer) => $customer->status === Customer::STATUS_INACTIVE && $customer->last_quote_at && $customer->last_quote_at->lt(now()->subMonths(6)))
                ->map(fn (Customer $customer) => $this->followUpAlert(
                    'Inactive Customer',
                    $customer->last_quote_at,
                    $customer->full_name,
                    'Previously active customer has gone quiet for over 6 months.',
                    'Low',
                    route('customers.show', $customer)
                )))
            ->sortByDesc(fn (array $alert) => $alert['detected_at']?->timestamp ?? 0)
            ->values();

        $typeCounts = $alerts->groupBy('type')->map->count()->sortDesc();
        $severityCounts = $alerts->groupBy('severity')->map->count()->sortDesc();

        return [
            'slug' => 'at-risk-follow-up',
            'title' => 'At-Risk Follow-Up Report',
            'subtitle' => 'Surface the records most likely to lose momentum so the team can recover them fast.',
            'badge' => 'Cross-Team Risk View',
            'cards' => [
                $this->reportCard('Total Alerts', (string) $alerts->count(), 'triangle-alert', 'text-primary'),
                $this->reportCard('High Severity', (string) $alerts->where('severity', 'High')->count(), 'shield-alert', 'text-danger'),
                $this->reportCard('Stale Quotes', (string) $alerts->where('type', 'Stale Quote')->count(), 'message-square-quote', 'text-warning'),
                $this->reportCard('Unread Messages', (string) $alerts->where('type', 'Unread Message')->count(), 'mail', 'text-info'),
            ],
            'charts' => [
                $this->barChart(
                    'at-risk-types',
                    'Alerts by Type',
                    'See which bottlenecks are creating the largest follow-up risk right now.',
                    [
                        'categories' => $typeCounts->keys()->values()->all(),
                        'series' => [[
                            'name' => 'Alerts',
                            'data' => $typeCounts->values()->values()->all(),
                        ]],
                    ],
                    true,
                    ['#4d5761'],
                    ' alerts',
                    'col-xxl-8'
                ),
                $this->donutChart(
                    'at-risk-severity',
                    'Severity Mix',
                    'Focus the team where the risk is highest first.',
                    [
                        'labels' => $severityCounts->keys()->values()->all(),
                        'series' => $severityCounts->values()->values()->all(),
                    ],
                    ['#f95c5c', '#f59e0b', '#64748b'],
                    'col-xxl-4'
                ),
            ],
            'insights' => [
                $this->insightCard('Oldest Alert', optional($alerts->sortBy(fn (array $alert) => $alert['detected_at']?->timestamp ?? PHP_INT_MAX)->first()['detected_at'] ?? null)?->diffForHumans() ?? 'No alerts', 'The oldest unresolved issue usually deserves immediate attention.'),
                $this->insightCard('Draft Quotation Risk', (string) $alerts->where('type', 'Draft Quotation')->count(), 'Draft quotations that may be slowing down close rate.'),
                $this->insightCard('Reactivation Targets', (string) $alerts->where('type', 'Inactive Customer')->count(), 'Inactive customers worth a reactivation push.'),
                $this->insightCard('Recovery Priority', $alerts->where('severity', 'High')->isNotEmpty() ? 'High severity exists' : 'Stable', 'Use the severity split to work top-down.'),
            ],
            'table' => [
                'title' => 'At-Risk Follow-Up Details',
                'description' => 'Every row below should point to a concrete recovery action.',
                'search_placeholder' => 'Search person, issue, or alert type',
                'filters' => [
                    $this->tableFilter('severity', 'Severity', 'severity', ['High', 'Medium', 'Low'], 'All severities'),
                    $this->tableFilter('type', 'Type', 'type', $typeCounts->keys()->values()->all(), 'All types'),
                ],
                'sorts' => [
                    $this->sortOption('newest', 'Newest first', 'dateSort', 'number', 'desc'),
                    $this->sortOption('oldest', 'Oldest first', 'dateSort', 'number', 'asc'),
                    $this->sortOption('severity', 'Highest severity', 'severityRank', 'number', 'desc'),
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Detected'],
                    ['key' => 'person', 'label' => 'Lead / Customer'],
                    ['key' => 'type', 'label' => 'Type'],
                    ['key' => 'issue', 'label' => 'Issue'],
                    ['key' => 'severity', 'label' => 'Severity'],
                ],
                'rows' => $alerts->map(function (array $alert) {
                    $severityClass = match ($alert['severity']) {
                        'High' => 'danger',
                        'Medium' => 'warning',
                        default => 'secondary',
                    };

                    return [
                        'cells' => [
                            'date' => $this->stackCell($alert['detected_at']?->format('Y-m-d') ?? 'N/A', $alert['detected_at']?->diffForHumans() ?? ''),
                            'person' => $this->textCell($alert['person']),
                            'type' => $this->textCell($alert['type']),
                            'issue' => $this->textCell($alert['issue']),
                            'severity' => $this->badgeCell($alert['severity'], $severityClass),
                        ],
                        'datasets' => [
                            'search' => Str::lower(implode(' ', [$alert['person'], $alert['type'], $alert['issue'], $alert['severity']])),
                            'severity' => $this->normalizeFilterValue($alert['severity']),
                            'type' => $this->normalizeFilterValue($alert['type']),
                            'dateSort' => $alert['detected_at']?->timestamp ?? 0,
                            'severityRank' => $alert['severity_rank'],
                        ],
                        'actions' => [
                            $this->actionLink('Open', $alert['url'], 'eye', 'btn-soft-primary'),
                        ],
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function quoteRequests()
    {
        if (! Schema::hasTable('quote_requests')) {
            return collect();
        }

        return QuoteRequest::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    private function customersCollection()
    {
        app(CustomerSyncService::class)->sync();

        if (! Schema::hasTable('customers')) {
            return collect();
        }

        return Customer::query()
            ->orderByDesc(DB::raw('COALESCE(last_quote_at, first_seen_at, created_at)'))
            ->orderByDesc('id')
            ->get();
    }

    private function quotationsCollection()
    {
        if (! Schema::hasTable('quotations') || ! Schema::hasTable('quote_requests')) {
            return collect();
        }

        return Quotation::query()
            ->with('quoteRequest')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    private function invoicesCollection()
    {
        if (! Schema::hasTable('invoices')) {
            return collect();
        }

        return Invoice::query()
            ->with(['items', 'quoteRequest.quotation', 'emailLogs'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(15);
    }

    private function invoiceRecord($invoice = null): ?Invoice
    {
        if (! Schema::hasTable('invoices')) {
            return null;
        }

        $query = Invoice::query()->with(['items', 'quoteRequest.quotation', 'emailLogs', 'stages']);

        if ($invoice !== null && trim((string) $invoice) !== '') {
            $invoiceKey = trim((string) $invoice);
            $normalizedInvoiceKey = ltrim($invoiceKey, '#');

            $record = $query
                ->where(function ($query) use ($invoiceKey, $normalizedInvoiceKey) {
                    if (ctype_digit($invoiceKey)) {
                        $query->whereKey((int) $invoiceKey);
                    }

                    $query->orWhere('invoice_number', $invoiceKey)
                        ->orWhere('invoice_number', $normalizedInvoiceKey);
                })
                ->first();

            abort_unless($record, 404);

            return $record;
        }

        return $query
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->first();
    }

    private function messagesCollection()
    {
        if (! Schema::hasTable('messages')) {
            return collect();
        }

        return Message::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);
    }

    private function topCounts($items, callable $resolver, int $limit = 6, string $fallback = 'Unknown')
    {
        return $items
            ->map(fn ($item) => $this->safeLabel($resolver($item), $fallback))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take($limit);
    }

    private function combinedCountMaps($first, $second)
    {
        $first = $first instanceof Collection ? $first : collect($first);
        $second = $second instanceof Collection ? $second : collect($second);

        $allKeys = $first->keys()->merge($second->keys())->unique();

        $merged = $allKeys->mapWithKeys(function ($key) use ($first, $second) {
            $a = $first->get($key, 0);
            $b = $second->get($key, 0);

            return [$key => collect([$a, $b])];
        });

        return $merged->sortByDesc(fn ($counts) => $counts->sum());
    }

    private function dashboardInquiryPeriodData($quotes, $messages): array
    {
        return collect(self::DASHBOARD_PERIOD_OPTIONS)
            ->mapWithKeys(fn (string $label, string $period) => [
                $period => $this->weekdaySeries([
                    [$quotes, fn (QuoteRequest $quote) => $quote->created_at],
                    [$messages, fn (Message $message) => $message->created_at],
                ], $period),
            ])
            ->all();
    }

    private function dashboardServiceCategoryPeriodData($quotes): array
    {
        return collect(self::DASHBOARD_PERIOD_OPTIONS)
            ->mapWithKeys(function (string $label, string $period) use ($quotes) {
                [$start, $end] = $this->dashboardPeriodRange($period);

                $series = collect(self::DASHBOARD_SERVICE_CATEGORIES)->map(function (string $category) use ($quotes, $start, $end) {
                    $counts = array_fill(0, count(self::DASHBOARD_WEEKDAY_LABELS), 0);

                    $quotes->each(function (QuoteRequest $quote) use (&$counts, $category, $start, $end) {
                        $createdAt = $this->carbonDate($quote->created_at);

                        if (
                            ! $createdAt
                            || $createdAt->lt($start)
                            || $createdAt->gt($end)
                            || $this->dashboardServiceCategoryForQuote($quote) !== $category
                        ) {
                            return;
                        }

                        $counts[(int) $createdAt->dayOfWeek]++;
                    });

                    return [
                        'name' => $category,
                        'data' => collect(self::DASHBOARD_WEEKDAY_LABELS)
                            ->map(fn (string $day, int $index) => [
                                'x' => $day,
                                'y' => $counts[$index],
                            ])
                            ->values()
                            ->all(),
                    ];
                })->values()->all();

                return [
                    $period => [
                        'labels' => self::DASHBOARD_WEEKDAY_LABELS,
                        'series' => $series,
                    ],
                ];
            })
            ->all();
    }

    private function weekdaySeries(array $sources, string $period): array
    {
        [$start, $end] = $this->dashboardPeriodRange($period);
        $counts = array_fill(0, count(self::DASHBOARD_WEEKDAY_LABELS), 0);

        collect($sources)->each(function (array $source) use (&$counts, $start, $end) {
            [$items, $resolver] = $source;

            $items->each(function ($item) use (&$counts, $resolver, $start, $end) {
                $date = $this->carbonDate($resolver($item));

                if (! $date || $date->lt($start) || $date->gt($end)) {
                    return;
                }

                $counts[(int) $date->dayOfWeek]++;
            });
        });

        return [
            'labels' => self::DASHBOARD_WEEKDAY_LABELS,
            'series' => array_values($counts),
        ];
    }

    private function dashboardPeriodRange(string $period): array
    {
        return match ($period) {
            'monthly' => [now()->copy()->startOfMonth(), now()->copy()->endOfMonth()],
            'yearly' => [now()->copy()->startOfYear(), now()->copy()->endOfYear()],
            default => [
                now()->copy()->startOfWeek(CarbonInterface::SUNDAY),
                now()->copy()->endOfWeek(CarbonInterface::SATURDAY),
            ],
        };
    }

    private function dashboardServiceCategoryForQuote(QuoteRequest $quote): string
    {
        $haystack = Str::lower(implode(' ', array_filter([
            $quote->serviceTypeLabel(),
            $quote->moving_from,
            $quote->moving_to,
            $quote->move_size,
            $quote->source_page,
        ])));

        if (Str::contains($haystack, ['storage', 'packing', 'packaging', 'carton'])) {
            return 'Packing & Storage';
        }

        if (Str::contains($haystack, ['office', 'corporate', 'commercial', 'business', 'warehouse', 'desk'])) {
            return 'Office Relocation';
        }

        if (Str::contains($haystack, ['long distance', 'intercity', 'upcountry', 'mombasa', 'kisumu', 'eldoret', 'nakuru', 'naivasha'])) {
            return 'Long-Distance Move';
        }

        return 'Residential Relocation';
    }

    private function dateSeries($items, callable $dateResolver, int $days = 7, ?callable $valueResolver = null): array
    {
        $valuesByDate = $items
            ->map(function ($item) use ($dateResolver, $valueResolver) {
                $date = $this->carbonDate($dateResolver($item));

                if (! $date) {
                    return null;
                }

                return [
                    'date' => $date->toDateString(),
                    'value' => $valueResolver ? (float) $valueResolver($item) : 1,
                ];
            })
            ->filter()
            ->groupBy('date')
            ->map(fn ($group) => $group->sum('value'));

        $window = collect(range($days - 1, 0))->map(fn (int $daysAgo) => now()->copy()->subDays($daysAgo));

        return [
            'labels' => $window->map(fn ($date) => $date->format('d M'))->values()->all(),
            'series' => $window->map(fn ($date) => (float) ($valuesByDate[$date->toDateString()] ?? 0))->values()->all(),
        ];
    }

    private function sparklineSeries($items, callable $dateResolver, ?callable $filter = null, int $days = 11): array
    {
        $filtered = $filter ? $items->filter($filter) : $items;

        return $this->dateSeries($filtered, $dateResolver, $days)['series'];
    }

    private function countItemsInPeriod($items, callable $dateResolver, CarbonInterface $start, CarbonInterface $end): int
    {
        return $items->filter(function ($item) use ($dateResolver, $start, $end) {
            $date = $this->carbonDate($dateResolver($item));

            return $date && $date->gte($start) && $date->lte($end);
        })->count();
    }

    private function carbonDate($date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        if ($date instanceof CarbonInterface) {
            return Carbon::instance($date)->copy();
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    private function averageMoveLeadTimeDays($quotes): float
    {
        $leadTimes = $quotes
            ->filter(fn (QuoteRequest $quote) => $quote->created_at && $quote->move_date)
            ->map(fn (QuoteRequest $quote) => $quote->created_at->startOfDay()->diffInDays($quote->move_date, false))
            ->filter(fn (int $days) => $days >= 0);

        return $leadTimes->isNotEmpty() ? (float) $leadTimes->avg() : 0.0;
    }

    private function reportCard(
        string $title,
        string $value,
        string $icon,
        string $valueClass,
        array $sparkline = [],
        string $sparklineType = 'area',
        string $sparklineColor = '#4d5761'
    ): array {
        return [
            'title' => $title,
            'value' => $value,
            'icon' => $icon,
            'value_class' => $valueClass,
            'sparkline' => $sparkline,
            'sparkline_type' => $sparklineType,
            'sparkline_color' => $sparklineColor,
        ];
    }

    private function areaChart(
        string $key,
        string $title,
        string $description,
        array $seriesData,
        string $seriesName,
        array $colors,
        string $colClass,
        string $valuePrefix = '',
        string $valueSuffix = ''
    ): array {
        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'type' => 'area',
            'col_class' => $colClass,
            'categories' => $seriesData['labels'],
            'series' => [[
                'name' => $seriesName,
                'data' => $seriesData['series'],
            ]],
            'colors' => $colors,
            'value_prefix' => $valuePrefix,
            'value_suffix' => $valueSuffix,
        ];
    }

    private function donutChart(
        string $key,
        string $title,
        string $description,
        array $chartData,
        array $colors,
        string $colClass
    ): array {
        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'type' => 'donut',
            'col_class' => $colClass,
            'labels' => $chartData['labels'],
            'series' => $chartData['series'],
            'colors' => $colors,
        ];
    }

    private function barChart(
        string $key,
        string $title,
        string $description,
        array $chartData,
        bool $horizontal,
        array $colors,
        string $valueSuffix,
        string $colClass,
        string $valuePrefix = ''
    ): array {
        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'type' => $horizontal ? 'bar-horizontal' : 'bar-vertical',
            'col_class' => $colClass,
            'categories' => $chartData['categories'],
            'series' => $chartData['series'],
            'colors' => $colors,
            'value_prefix' => $valuePrefix,
            'value_suffix' => $valueSuffix,
        ];
    }

    private function insightCard(string $label, string $value, string $note): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'note' => $note,
        ];
    }

    private function tableFilter(string $id, string $label, string $dataset, array $options, string $allLabel): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'dataset' => $dataset,
            'all_label' => $allLabel,
            'options' => collect($options)
                ->filter(fn ($option) => trim((string) $option) !== '')
                ->unique()
                ->values()
                ->map(fn ($option) => [
                    'value' => $this->normalizeFilterValue($option),
                    'label' => $option,
                ])
                ->all(),
        ];
    }

    private function sortOption(string $value, string $label, string $dataset, string $type, string $direction): array
    {
        return [
            'value' => $value,
            'label' => $label,
            'dataset' => $dataset,
            'type' => $type,
            'direction' => $direction,
        ];
    }

    private function stackCell(string $primary, string $secondary = ''): array
    {
        return [
            'type' => 'stack',
            'primary' => $primary,
            'secondary' => $secondary,
        ];
    }

    private function textCell(string $text): array
    {
        return [
            'type' => 'text',
            'text' => $text,
        ];
    }

    private function badgeCell(string $label, string $class): array
    {
        return [
            'type' => 'badge',
            'label' => $label,
            'class' => $class,
        ];
    }

    private function actionLink(string $label, string $url, string $icon, string $class): array
    {
        return [
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
            'class' => $class,
        ];
    }

    private function followUpAlert(
        string $type,
        $detectedAt,
        string $person,
        string $issue,
        string $severity,
        string $url
    ): array {
        $severityRank = match ($severity) {
            'High' => 3,
            'Medium' => 2,
            default => 1,
        };

        return [
            'type' => $type,
            'detected_at' => $detectedAt,
            'person' => $person,
            'issue' => $issue,
            'severity' => $severity,
            'severity_rank' => $severityRank,
            'url' => $url,
        ];
    }

    private function safeLabel($value, string $fallback = 'Unknown'): string
    {
        $label = trim((string) $value);

        return $label !== '' ? $label : $fallback;
    }

    private function normalizeFilterValue($value): string
    {
        $label = trim((string) $value);

        return $label === '' ? 'unknown' : Str::slug(Str::lower($label), '-');
    }

    private function percentage($numerator, $denominator): int
    {
        $base = (float) $denominator;

        if ($base <= 0) {
            return 0;
        }

        return (int) round(((float) $numerator / $base) * 100);
    }

    private function formatPercent($numerator, $denominator): string
    {
        return $this->percentage($numerator, $denominator).'%';
    }

    private function formatRatePercent(float $rate): string
    {
        return number_format(max(0, $rate) * 100, 1).'%';
    }

    private function formatCurrency($amount): string
    {
        return 'KES '.number_format((float) $amount, 2);
    }

    private function emailDeliveryReportPage(): View
    {
        return view('reports.email-delivery', $this->emailDeliveryReportViewData());
    }

    private function emailDeliveryDownloadData(): array
    {
        $data = $this->emailDeliveryReportViewData();
        $summary = $data['emailDeliverySummary'];
        $insights = $data['emailDeliveryInsights'];

        return [
            'slug' => 'email-delivery',
            'title' => 'Email Delivery Report',
            'subtitle' => 'Downloadable email delivery summary, success indicators, and log details.',
            'badge' => 'Source: email_delivery_logs',
            'cards' => [
                $this->reportCard('Total Logs', (string) $summary['total'], 'mail', 'text-primary', $data['emailDeliveryReportData']['charts']['sparklines']['total']->all()),
                $this->reportCard('Sent', (string) $summary['sent'], 'mail-check', 'text-success', $data['emailDeliveryReportData']['charts']['sparklines']['sent']->all(), 'bar', '#22b956'),
                $this->reportCard('Failed', (string) $summary['failed'], 'shield-alert', 'text-danger', $data['emailDeliveryReportData']['charts']['sparklines']['failed']->all(), 'area', '#f95c5c'),
                $this->reportCard('Pending', (string) $summary['pending'], 'clock-3', 'text-warning', $data['emailDeliveryReportData']['charts']['sparklines']['pending']->all(), 'bar', '#f59e0b'),
            ],
            'insights' => [
                $this->insightCard('Success Rate', $insights['success_rate'].'%', 'Sent logs divided by all tracked email delivery logs.'),
                $this->insightCard('Failure Rate', $insights['failure_rate'].'%', 'Failed logs divided by all tracked email delivery logs.'),
                $this->insightCard('Top Purpose', $insights['top_purpose'], 'The busiest email journey in the current logs.'),
                $this->insightCard('Latest Activity', $insights['latest_activity'], $insights['latest_activity_at']),
            ],
            'table' => [
                'title' => 'Email Delivery Logs',
                'description' => 'Full delivery log data available at download time.',
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'purpose', 'label' => 'Purpose'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'direction', 'label' => 'Direction'],
                    ['key' => 'transport', 'label' => 'Transport'],
                    ['key' => 'subject', 'label' => 'Subject'],
                    ['key' => 'response', 'label' => 'Response'],
                ],
                'rows' => $data['emailDeliveryReportData']['tableRows']->map(fn (array $row) => [
                    'cells' => [
                        'date' => $this->stackCell($row['date_label'], $row['time_label']),
                        'email' => $this->textCell($row['email'] !== '' ? $row['email'] : 'No email recorded'),
                        'purpose' => $this->textCell($row['purpose']),
                        'status' => $this->badgeCell($row['status'], $row['status_badge_class']),
                        'direction' => $this->textCell($row['direction']),
                        'transport' => $this->textCell($row['transport']),
                        'subject' => $this->textCell($row['subject']),
                        'response' => $this->textCell($row['response_message'] !== '' ? $row['response_message'] : 'No response recorded'),
                    ],
                    'datasets' => [],
                    'actions' => [],
                ])->all(),
            ],
        ];
    }

    private function emailDeliveryReportViewData(): array
    {
        $emailDeliveryLogs = $this->emailDeliveryLogs();
        $totalLogs = $emailDeliveryLogs->count();
        $sentLogs = $emailDeliveryLogs->where('status_label', 'Sent')->count();
        $failedLogs = $emailDeliveryLogs->where('status_label', 'Failed')->count();
        $pendingLogs = $emailDeliveryLogs->where('status_label', 'Pending')->count();
        $statusCounts = $emailDeliveryLogs
            ->map(fn (object $log) => $log->status_label ?: 'Unknown')
            ->countBy()
            ->sortDesc();
        $logsByDate = $emailDeliveryLogs->groupBy(fn (object $log) => $log->created_at->toDateString());

        $purposeCounts = $emailDeliveryLogs
            ->map(fn (object $log) => $log->purpose ?: 'Unknown')
            ->countBy()
            ->sortDesc();

        $directionCounts = $emailDeliveryLogs
            ->map(fn (object $log) => $log->direction ?: 'N/A')
            ->countBy()
            ->sortDesc();

        $dailyCounts = $emailDeliveryLogs
            ->map(fn (object $log) => $log->created_at->format('Y-m-d'))
            ->countBy();

        $trendEndDate = now();
        $buildSparklineSeries = function (string $statusKey = 'all') use ($logsByDate, $trendEndDate) {
            return collect(range(10, 0))->map(function (int $daysAgo) use ($logsByDate, $statusKey, $trendEndDate) {
                $date = $trendEndDate->copy()->subDays($daysAgo)->toDateString();
                $logs = $logsByDate->get($date, collect());

                if ($statusKey === 'all') {
                    return $logs->count();
                }

                return $logs->where('status_label', $statusKey)->count();
            })->values();
        };

        $trendWindow = collect(range(6, 0))->map(function (int $daysAgo) use ($dailyCounts, $trendEndDate) {
            $date = $trendEndDate->copy()->subDays($daysAgo);
            $key = $date->toDateString();

            return [
                'label' => $date->format('d M'),
                'value' => (int) ($dailyCounts[$key] ?? 0),
            ];
        });

        $tableRows = $emailDeliveryLogs->map(function (object $log) {
            $subject = trim($log->subject) !== '' ? $log->subject : 'No subject recorded';
            $responseMessage = trim($log->response_message) !== '' ? $log->response_message : '';

            return [
                'id' => $log->id,
                'date_label' => $log->created_at?->format('Y-m-d') ?? 'N/A',
                'time_label' => $log->created_at?->format('h:i A') ?? '',
                'date_sort' => $log->created_at?->timestamp ?? 0,
                'email' => (string) ($log->recipient_email ?? ''),
                'purpose' => $log->purpose ?: 'Unknown',
                'status' => $log->status_label,
                'status_badge_class' => $log->status_badge_class,
                'direction' => $log->direction ?: 'N/A',
                'subject' => $subject,
                'response_message' => $responseMessage,
                'transport' => trim((string) $log->transport) !== '' ? $log->transport : 'N/A',
            ];
        })->values();
        $latestActivity = $emailDeliveryLogs->first()?->created_at;

        return [
            'emailDeliverySummary' => [
                'total' => $totalLogs,
                'sent' => $sentLogs,
                'failed' => $failedLogs,
                'pending' => $pendingLogs,
            ],
            'emailDeliveryInsights' => [
                'success_rate' => $totalLogs > 0 ? (int) round(($sentLogs / $totalLogs) * 100) : 0,
                'top_purpose' => $purposeCounts->keys()->first() ?? 'No activity yet',
                'latest_activity' => $latestActivity?->diffForHumans() ?? 'No activity yet',
                'latest_activity_at' => $latestActivity?->format('Y-m-d h:i A') ?? 'N/A',
                'failure_rate' => $totalLogs > 0 ? (int) round(($failedLogs / $totalLogs) * 100) : 0,
            ],
            'emailDeliveryFilterOptions' => [
                'statuses' => $tableRows->pluck('status')->unique()->values(),
                'purposes' => $tableRows->pluck('purpose')->unique()->values(),
                'directions' => $tableRows->pluck('direction')->unique()->values(),
            ],
            'emailDeliveryReportData' => [
                'tableRows' => $tableRows,
                'charts' => [
                    'status' => [
                        'labels' => $statusCounts->keys()->values(),
                        'series' => $statusCounts->values()->values(),
                    ],
                    'purpose' => [
                        'labels' => $purposeCounts->keys()->take(6)->values(),
                        'series' => $purposeCounts->values()->take(6)->values(),
                    ],
                    'trend' => [
                        'labels' => $trendWindow->pluck('label')->values(),
                        'series' => $trendWindow->pluck('value')->values(),
                    ],
                    'direction' => [
                        'labels' => $directionCounts->keys()->values(),
                        'series' => $directionCounts->values()->values(),
                    ],
                    'sparklines' => [
                        'total' => $buildSparklineSeries(),
                        'sent' => $buildSparklineSeries('Sent'),
                        'failed' => $buildSparklineSeries('Failed'),
                        'pending' => $buildSparklineSeries('Pending'),
                    ],
                ],
                'totalRows' => $tableRows->count(),
            ],
        ];
    }

    public function destroyEmailDeliveryLog(int $log): RedirectResponse
    {
        abort_unless(Schema::hasTable('email_delivery_logs'), 404);
        abort_unless(DB::table('email_delivery_logs')->where('id', $log)->exists(), 404);

        DB::table('email_delivery_logs')->where('id', $log)->delete();

        return redirect()
            ->route('second', ['reports', 'email-delivery'])
            ->with('toast-success', 'Email delivery log deleted successfully.');
    }

    private function emailDeliveryLogs(?int $limit = null)
    {
        if (! Schema::hasTable('email_delivery_logs')) {
            return collect();
        }

        $query = DB::table('email_delivery_logs')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($limit !== null) {
            $query->take($limit);
        }

        return $query->get()->map(fn (object $log) => $this->mapEmailDeliveryLog($log));
    }

    private function mapEmailDeliveryLog(object $log): object
    {
        $purpose = match ($log->form_type) {
            'quote' => 'Quotation',
            'contact' => 'Contact',
            'career_application' => 'Job Application',
            'review' => 'Review',
            'invoice' => 'Invoice',
            default => Str::headline((string) $log->form_type),
        };

        $status = Str::lower((string) $log->status);
        $statusLabel = match ($status) {
            'sent', 'success', 'delivered' => 'Sent',
            'failed', 'bounced', 'rejected' => 'Failed',
            'pending', 'queued', 'processing' => 'Pending',
            default => Str::headline((string) $log->status),
        };

        $statusBadgeClass = match ($statusLabel) {
            'Sent' => 'success',
            'Failed' => 'danger',
            'Pending' => 'warning',
            default => 'secondary',
        };

        return (object) [
            'id' => $log->id,
            'created_at' => Carbon::parse($log->created_at),
            'recipient_email' => $log->recipient_email,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'purpose' => $purpose,
            'direction' => Str::headline((string) ($log->direction ?? '')),
            'subject' => (string) ($log->subject ?? ''),
            'transport' => (string) ($log->transport ?? ''),
            'response_message' => (string) ($log->response_message ?? ''),
        ];
    }
}
