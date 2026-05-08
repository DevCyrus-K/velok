<?php

use App\Mail\InvoiceMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Message;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\CustomerSyncService;
use App\Services\GoogleAnalyticsService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function googleAnalyticsVisitorsForTest(): array
{
    $endDate = now()->copy()->startOfDay();

    return [
        'configured' => true,
        'error' => null,
        'source' => 'Google Analytics Data API',
        'property' => 'properties/123456789',
        'start_date' => $endDate->copy()->subDays(29)->toDateString(),
        'end_date' => $endDate->toDateString(),
        'date_range_label' => $endDate->copy()->subDays(29)->format('d M Y') . ' - ' . $endDate->format('d M Y'),
        'summary' => [
            'active_users' => 6482,
            'new_users' => 812,
            'sessions' => 7190,
            'screen_page_views' => 15440,
            'engagement_rate' => 0.64,
            'engaged_sessions' => 4602,
        ],
        'today' => [
            'active_users' => 53,
            'new_users' => 33,
            'sessions' => 61,
            'screen_page_views' => 144,
        ],
        'daily' => collect(range(10, 0))->map(fn (int $daysAgo) => [
            'date' => $endDate->copy()->subDays($daysAgo)->toDateString(),
            'label' => $endDate->copy()->subDays($daysAgo)->format('d M'),
            'active_users' => 530 + $daysAgo,
            'new_users' => 70 + $daysAgo,
            'sessions' => 610 + $daysAgo,
            'screen_page_views' => 1300 + $daysAgo,
        ])->values()->all(),
        'pages' => [
            ['page' => '/properties/for-sale', 'active_users' => 3200, 'sessions' => 3440, 'screen_page_views' => 7200, 'engagement_rate' => 0.71],
        ],
        'devices' => [
            'labels' => ['Desktop', 'Mobile', 'Tablet'],
            'series' => [4380, 1800, 302],
        ],
        'channels' => [
            ['channel' => 'Organic Search', 'active_users' => 4020, 'sessions' => 4380],
        ],
    ];
}

function bindGoogleAnalyticsVisitorsForTest(): void
{
    app()->instance(GoogleAnalyticsService::class, new class(googleAnalyticsVisitorsForTest()) extends GoogleAnalyticsService {
        public function __construct(private readonly array $report)
        {
        }

        public function visitorReport(?CarbonInterface $start = null, ?CarbonInterface $end = null): array
        {
            return $this->report;
        }
    });
}

it('normalizes moving and relocation quote categories into the residential relocation service', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->post(route('quotes.store'), [
        'full_name' => 'Brian Otieno',
        'email' => 'brian.otieno@gmail.com',
        'phone' => '0712 345 678',
        'moving_from' => 'Westlands, Nairobi',
        'moving_to' => 'Syokimau, Machakos',
        'move_date' => now()->addWeek()->toDateString(),
        'service_type' => 'Relocation',
        'move_size' => '2 bedroom apartment',
        'additional_notes' => 'Handle fragile items carefully.',
        'source_page' => '/services/relocation',
        'status' => 'new',
    ])->assertRedirect();

    $quote = QuoteRequest::firstOrFail();

    expect($quote->service_type)->toBe('Residential Relocation');

    app(CustomerSyncService::class)->sync();

    $this->get(route('quotes.index'))
        ->assertOk()
        ->assertSee('Residential Relocation');

    $this->get(route('any', 'customers'))
        ->assertOk()
        ->assertSee('Residential Relocation');
});

it('renders live dashboard metrics from the database', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $quote = QuoteRequest::create([
        'full_name' => 'Amina Hassan',
        'email' => 'amina.hassan@gmail.com',
        'phone' => '0743 210 987',
        'moving_from' => 'Mombasa Road, Nairobi',
        'moving_to' => 'Nyali, Mombasa',
        'move_date' => now()->addDays(10),
        'service_type' => 'Residential Relocation',
        'move_size' => 'Boutique shelves and cartons',
        'additional_notes' => 'Need overnight dispatch.',
        'source_page' => '/services/moving',
        'ip_address' => '102.89.14.88',
        'user_agent' => 'Mozilla/5.0',
        'status' => 'quoted',
    ]);

    Customer::create([
        'contact_key' => Customer::makeContactKey('completed.customer@example.com', '0700 000 001'),
        'full_name' => 'Completed Customer',
        'email' => 'completed.customer@example.com',
        'phone' => '0700 000 001',
        'moving_from' => 'Kilimani, Nairobi',
        'moving_to' => 'Runda, Nairobi',
        'latest_service_type' => 'Residential Relocation',
        'quotes_count' => 1,
        'approved_quotes_count' => 1,
        'declined_quotes_count' => 0,
        'status' => Customer::STATUS_COMPLETED,
        'first_seen_at' => now()->subDays(6),
        'last_quote_at' => now()->subDay(),
    ]);

    foreach (['Spam Lead One', 'Spam Lead Two'] as $index => $name) {
        QuoteRequest::create([
            'full_name' => $name,
            'email' => 'spam' . $index . '@example.com',
            'phone' => '0700 000 00' . ($index + 2),
            'moving_from' => 'Westlands, Nairobi',
            'moving_to' => 'Karen, Nairobi',
            'move_date' => now()->addDays(7 + $index),
            'service_type' => 'Residential Relocation',
            'move_size' => '1 bedroom apartment',
            'additional_notes' => 'Marked as invalid lead.',
            'source_page' => '/services/moving',
            'ip_address' => '102.89.14.' . (90 + $index),
            'user_agent' => 'Mozilla/5.0',
            'status' => 'spam',
        ]);
    }

    Message::create([
        'name' => 'Cynthia Anyango',
        'email' => 'cynthia.anyango@gmail.com',
        'phone' => '0711 234 987',
        'subject' => 'Need moving quote for this Saturday',
        'message' => 'I need a same-day moving quote from South C to Lang’ata this Saturday morning.',
        'status' => 'unread',
        'origin_page' => 'moving',
    ]);

    app(CustomerSyncService::class)->sync();

    $invoice = Invoice::create([
        'invoice_number' => 'INV-00001',
        'quote_request_id' => $quote->id,
        'customer_name' => $quote->full_name,
        'customer_email' => $quote->email,
        'customer_phone' => $quote->phone,
        'move_origin' => $quote->moving_from,
        'move_destination' => $quote->moving_to,
        'move_date' => now()->addDays(10),
        'move_size' => $quote->move_size,
        'quote_reference' => $quote->reference(),
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'subtotal' => 25000,
        'tax' => 4000,
        'total_amount' => 29000,
        'status' => 'paid',
        'payment_method' => 'mobile_money',
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Transport Truck',
        'quantity' => 1,
        'unit_price' => 25000,
        'total' => 25000,
    ]);

    bindGoogleAnalyticsVisitorsForTest();

    $this->get(route('second', ['dashboard', 'index']))
        ->assertOk()
        ->assertSee('KES 29,000.00')
        ->assertSee('Completed Moves')
        ->assertSee('Cancelled Bookings')
        ->assertSeeInOrder([
            '<p class="mb-2 card-title">Completed Moves</p>',
            '<h4 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">1</h4>',
            'Customers marked complete after quote and follow-up activity.',
        ], false)
        ->assertSeeInOrder([
            '<p class="mb-2 card-title">Cancelled Bookings</p>',
            '<h4 class="fw-bold text-primary d-flex align-items-center gap-2 mb-2">2</h4>',
            'Quote requests that ended as declined or spam.',
        ], false)
        ->assertDontSee('5,312')
        ->assertDontSee('1,120')
        ->assertSee('Total Visitors')
        ->assertSee('6,482')
        ->assertSee('Google Analytics shows')
        ->assertSee('<span class="fw-semibold text-success">+4</span> new inquiries today.', false)
        ->assertSee('<span class="fw-semibold text-success">1</span> move this week.', false)
        ->assertDontSee('<span class="fw-semibold text-success">+3</span> new inquiries today.', false)
        ->assertDontSee('<span class="fw-semibold text-success">5</span> moves this week.', false)
        ->assertSee('data-lucide="users"', false)
        ->assertSee('Desktop')
        ->assertSee('Mobile')
        ->assertSee('Tablet')
        ->assertSee('Residential Relocation')
        ->assertSee('Amina Hassan');
});

it('renders visitor insights only from google analytics data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    bindGoogleAnalyticsVisitorsForTest();

    $this->get(route('second', ['reports', 'visitor-reports']))
        ->assertOk()
        ->assertSee('Google Analytics Data')
        ->assertSee('Active Users')
        ->assertSee('6,482')
        ->assertSee('/properties/for-sale')
        ->assertSee('Organic Search')
        ->assertDontSee('Captured Visitor Signals')
        ->assertDontSee('Quote Lead Signals')
        ->assertDontSee('Contact Signals');
});

it('renders reports overview from the full live report catalog and database counts', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $quote = QuoteRequest::create([
        'full_name' => 'Reports Customer',
        'email' => 'reports.customer@example.com',
        'phone' => '0700 111 222',
        'moving_from' => 'Kilimani, Nairobi',
        'moving_to' => 'Nyali, Mombasa',
        'move_date' => now()->addDays(8),
        'service_type' => 'Residential Relocation',
        'move_size' => 'Two bedroom apartment',
        'additional_notes' => 'Reports overview fixture.',
        'source_page' => '/services/moving',
        'ip_address' => '102.89.14.70',
        'user_agent' => 'Mozilla/5.0',
        'status' => 'quoted',
    ]);

    Message::create([
        'name' => 'Reports Sender',
        'email' => 'reports.sender@example.com',
        'phone' => '0700 111 333',
        'subject' => 'Reports message',
        'message' => 'Checking that report counters come from database rows.',
        'status' => 'unread',
        'origin_page' => '/contact',
    ]);

    Quotation::create([
        'quote_request_id' => $quote->id,
        'company_name' => 'Kwikshift Movers Ltd',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112 587 581',
        'quote_date' => now()->toDateString(),
        'quote_valid_until' => now()->addDays(14)->toDateString(),
        'deposit_percentage' => 30,
        'cancellation_notice_hours' => 24,
        'services_included' => ['Transport'],
        'status' => 'draft',
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 25000,
    ]);

    Invoice::create([
        'invoice_number' => 'INV-REPORT-001',
        'quote_request_id' => $quote->id,
        'customer_name' => 'Reports Customer',
        'customer_email' => 'reports.customer@example.com',
        'customer_phone' => '0700 111 222',
        'move_origin' => $quote->moving_from,
        'move_destination' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'move_size' => $quote->move_size,
        'quote_reference' => $quote->reference(),
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'subtotal' => 20000,
        'tax' => 3200,
        'total_amount' => 23200,
        'status' => Invoice::STATUS_SENT,
        'payment_method' => 'bank_transfer',
    ]);

    app(CustomerSyncService::class)->sync();

    $this->get(route('second', ['reports', 'overview']))
        ->assertOk()
        ->assertSee('10 live report pages are built')
        ->assertSee('Quote Funnel Report')
        ->assertSee('Lead Sources Report')
        ->assertSee('Customer Report')
        ->assertSee('Financial Report')
        ->assertSee('Quotation Pipeline Report')
        ->assertSee('Message Response Report')
        ->assertSee('Route Demand Report')
        ->assertSee('At-Risk Follow-Up Report')
        ->assertSee('Email Delivery Report')
        ->assertSee('Visitor Insights')
        ->assertSeeInOrder(['10', 'Live Reports'])
        ->assertSeeInOrder(['1', 'Tracked Quotes'])
        ->assertSeeInOrder(['1', 'Tracked Customers'])
        ->assertSeeInOrder(['1', 'Tracked Messages'])
        ->assertSeeInOrder(['1', 'Tracked Invoices'])
        ->assertSeeInOrder(['1', 'Tracked Quotations'])
        ->assertDontSee('These four report pages');
});

it('renders the financial report from invoice and quotation database records', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $quote = QuoteRequest::create([
        'full_name' => 'Finance Quote Customer',
        'email' => 'finance.quote@example.com',
        'phone' => '0700 222 111',
        'moving_from' => 'Westlands, Nairobi',
        'moving_to' => 'Nyali, Mombasa',
        'move_date' => now()->addDays(9),
        'service_type' => 'Residential Relocation',
        'move_size' => 'Two bedroom apartment',
        'additional_notes' => 'Financial report fixture.',
        'source_page' => '/services/moving',
        'ip_address' => '102.89.14.71',
        'user_agent' => 'Mozilla/5.0',
        'status' => 'quoted',
    ]);

    Invoice::create([
        'invoice_number' => 'INV-FIN-PAID',
        'quote_request_id' => $quote->id,
        'customer_name' => 'Paid Finance Customer',
        'customer_email' => 'paid.finance@example.com',
        'customer_phone' => '0700 222 112',
        'move_origin' => 'Westlands, Nairobi',
        'move_destination' => 'Nyali, Mombasa',
        'move_date' => now()->addDays(9),
        'move_size' => 'Two bedroom apartment',
        'quote_reference' => $quote->reference(),
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'subtotal' => 25000,
        'tax' => 4000,
        'total_amount' => 29000,
        'status' => Invoice::STATUS_PAID,
        'payment_method' => 'mobile_money',
    ]);

    Invoice::create([
        'invoice_number' => 'INV-FIN-SENT',
        'customer_name' => 'Sent Finance Customer',
        'customer_email' => 'sent.finance@example.com',
        'customer_phone' => '0700 222 113',
        'move_origin' => 'Kilimani, Nairobi',
        'move_destination' => 'Karen, Nairobi',
        'move_date' => now()->addDays(12),
        'move_size' => 'Office move',
        'quote_reference' => '#QTFIN',
        'invoice_date' => now(),
        'due_date' => now()->addDays(10),
        'subtotal' => 15000,
        'tax' => 1000,
        'total_amount' => 16000,
        'status' => Invoice::STATUS_SENT,
        'payment_method' => 'bank_transfer',
    ]);

    Quotation::create([
        'quote_request_id' => $quote->id,
        'company_name' => 'Kwikshift Movers Ltd',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112 587 581',
        'quote_date' => now()->toDateString(),
        'quote_valid_until' => now()->addDays(14)->toDateString(),
        'deposit_percentage' => 30,
        'cancellation_notice_hours' => 24,
        'services_included' => ['Transport'],
        'status' => 'draft',
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 25000,
    ]);

    $this->get(route('second', ['reports', 'financial-reports']))
        ->assertOk()
        ->assertSee('Live Finance Data')
        ->assertSee('Total Invoiced')
        ->assertSee('KES 45,000.00')
        ->assertSee('Paid Revenue')
        ->assertSee('KES 29,000.00')
        ->assertSee('Outstanding')
        ->assertSee('KES 16,000.00')
        ->assertSee('Quoted Pipeline')
        ->assertSee('KES 25,000.00')
        ->assertSee('Invoice Revenue Trend')
        ->assertSee('Invoice Value by Status')
        ->assertSee('Quotation Pipeline by Status')
        ->assertSee('Financial Invoice Details')
        ->assertSee('INV-FIN-PAID')
        ->assertSee('INV-FIN-SENT')
        ->assertDontSee('Financial Quotation Details');
});

it('renders invoice and quotation previews inline without relying on page reloads', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $quote = QuoteRequest::create([
        'full_name' => 'Kevin Kipchoge',
        'email' => 'kevin.kipchoge@gmail.com',
        'phone' => '0726 908 451',
        'moving_from' => 'Pioneer, Eldoret',
        'moving_to' => 'Kileleshwa, Nairobi',
        'move_date' => now()->addDays(12),
        'service_type' => 'Residential Relocation',
        'move_size' => 'Bedsitter and workstation desk',
        'additional_notes' => 'Treadmill needs wrapping.',
        'source_page' => '/services/moving',
        'ip_address' => '105.160.19.47',
        'user_agent' => 'Mozilla/5.0',
        'status' => 'processing',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV-00002',
        'quote_request_id' => $quote->id,
        'customer_name' => $quote->full_name,
        'customer_email' => $quote->email,
        'customer_phone' => $quote->phone,
        'move_origin' => $quote->moving_from,
        'move_destination' => $quote->moving_to,
        'move_date' => now()->addDays(12),
        'move_size' => $quote->move_size,
        'quote_reference' => $quote->reference(),
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'subtotal' => 18000,
        'tax' => 2880,
        'total_amount' => 20880,
        'status' => 'sent',
        'payment_method' => 'bank_transfer',
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Loading Crew',
        'quantity' => 1,
        'unit_price' => 18000,
        'total' => 18000,
    ]);

    $this->get(route('quotations.create', $quote))
        ->assertOk()
        ->assertSee('id="quotationPreviewModal"', false)
        ->assertSee('id="previewQuotationButton"', false)
        ->assertDontSee('name="action" value="preview"', false);

    $this->get(route('second', ['invoice', 'invoices']))
        ->assertOk()
        ->assertSee('id="invoicePreviewModal"', false)
        ->assertSee('data-invoice-preview-open', false)
        ->assertSee('Open full page')
        ->assertSee('INV-00002')
        ->assertSee('Kevin Kipchoge')
        ->assertSee('Loading Crew')
        ->assertDontSee('Ethan Walker');

    $this->get(route('invoice.details', ['invoice' => $invoice->id]))
        ->assertOk()
        ->assertSee('Invoice: INV-00002')
        ->assertSee('Kevin Kipchoge')
        ->assertSee('Loading Crew')
        ->assertSee('KES 20,880.00')
        ->assertDontSee('Wireless Bluetooth Earbuds');
});

it('creates an invoice from database input and shows it immediately', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Mail::fake();

    $quote = QuoteRequest::create([
        'full_name' => 'Mercy Wanjiku',
        'email' => 'mercy.wanjiku@gmail.com',
        'phone' => '0712 111 222',
        'moving_from' => 'Ruiru, Kiambu',
        'moving_to' => 'Nyali, Mombasa',
        'move_date' => now()->addDays(9),
        'service_type' => 'Residential Relocation',
        'move_size' => 'Two bedroom apartment',
        'additional_notes' => 'Use extra wrapping for mirrors.',
        'source_page' => '/services/moving',
        'ip_address' => '102.89.14.44',
        'user_agent' => 'Mozilla/5.0',
        'status' => 'quoted',
    ]);

    Quotation::create([
        'quote_request_id' => $quote->id,
        'company_name' => 'KwikShift Movers & Relocators',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112587581',
        'quote_date' => now(),
        'quote_valid_until' => now()->addDays(7),
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 18500,
        'services_included' => [
            ['name' => 'Transportation & Fuel', 'description' => 'Moving vehicle rental and fuel'],
            ['name' => 'Labour Charges', 'description' => 'Professional moving team (2-3 persons)'],
            ['name' => 'Loading & Unloading', 'description' => 'Professional loading and unloading service'],
        ],
        'status' => 'draft',
    ]);

    $this->get(route('invoice.create', ['quote' => $quote->id]))
        ->assertOk()
        ->assertSee('Create Invoice')
        ->assertSee('Kwikshift')
        ->assertSee('Movers Ltd')
        ->assertSee('Email: info@kwikshiftmovers.co.ke')
        ->assertSee('Invoice ID')
        ->assertSee('Generate')
        ->assertSee('data-quote-url', false)
        ->assertSee('Mercy Wanjiku')
        ->assertSee('Nyali, Mombasa')
        ->assertSee('Transportation &amp; Fuel - Moving vehicle rental and fuel', false)
        ->assertSee('Labour Charges - Professional moving team (2-3 persons)', false)
        ->assertSee('Loading &amp; Unloading - Professional loading and unloading service', false)
        ->assertSee('value="6166.67"', false)
        ->assertSee('value="6166.66"', false)
        ->assertSee('Preview Invoice')
        ->assertSee('Send Invoice')
        ->assertDontSee('Save Invoice');

    $this->getJson(route('invoice.next-number'))
        ->assertOk()
        ->assertJson(['invoice_number' => 'INV-00001']);

    $this->getJson(route('invoice.quote', $quote))
        ->assertOk()
        ->assertJsonPath('reference', $quote->reference())
        ->assertJsonPath('customer_name', 'Mercy Wanjiku')
        ->assertJsonPath('move_destination', 'Nyali, Mombasa')
        ->assertJsonPath('quote_amount', 18500)
        ->assertJsonCount(3, 'line_items')
        ->assertJsonPath('line_items.0.description', 'Transportation & Fuel - Moving vehicle rental and fuel')
        ->assertJsonPath('line_items.0.unit_price', 6166.67)
        ->assertJsonPath('line_items.2.description', 'Loading & Unloading - Professional loading and unloading service')
        ->assertJsonPath('line_items.2.unit_price', 6166.66);

    $response = $this->post(route('invoice.store'), [
        'quote_request_id' => $quote->id,
        'invoice_number' => 'INV-CREATE-01',
        'customer_name' => $quote->full_name,
        'customer_email' => $quote->email,
        'customer_phone' => $quote->phone,
        'move_origin' => $quote->moving_from,
        'move_destination' => $quote->moving_to,
        'move_date' => now()->addDays(9)->toDateString(),
        'move_size' => $quote->move_size,
        'quote_reference' => $quote->reference(),
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'sent',
        'payment_method' => 'mobile_money',
        'tax' => 320,
        'notes' => 'Pay before move day.',
        'items' => [
            'description' => ['Packing Materials', 'Moving Truck'],
            'quantity' => [2, 1],
            'unit_price' => [500, 10000],
        ],
    ]);

    $invoice = Invoice::where('invoice_number', 'INV-CREATE-01')->firstOrFail();

    $response->assertRedirect(route('invoice.details', ['invoice' => $invoice->id]));

    expect((float) $invoice->subtotal)->toBe(11000.0)
        ->and((float) $invoice->tax)->toBe(320.0)
        ->and((float) $invoice->total_amount)->toBe(11320.0)
        ->and($invoice->status)->toBe('sent')
        ->and($invoice->items()->count())->toBe(2);

    Mail::assertSent(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->hasTo($quote->email)
        && $mail->invoice->invoice_number === 'INV-CREATE-01');

    expect(DB::table('email_delivery_logs')
        ->where('form_type', 'invoice')
        ->where('recipient_email', $quote->email)
        ->where('status', 'sent')
        ->exists())->toBeTrue();

    $this->get(route('invoice.details', ['invoice' => $invoice->id]))
        ->assertOk()
        ->assertSee('Invoice: INV-CREATE-01')
        ->assertSee('Mercy Wanjiku')
        ->assertSee('Packing Materials')
        ->assertSee('KES 11,320.00');

    $this->get(route('invoice.index'))
        ->assertOk()
        ->assertSee('INV-CREATE-01')
        ->assertSee('Mercy Wanjiku')
        ->assertSee(route('invoice.edit', $invoice), false)
        ->assertSee('data-delete-confirm', false);
});

it('edits an invoice from the invoice list action', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Mail::fake();

    $invoice = Invoice::create([
        'invoice_number' => 'INV-EDIT-01',
        'customer_name' => 'Edit Client',
        'customer_email' => 'edit.client@gmail.com',
        'customer_phone' => '0712 333 444',
        'move_origin' => 'Westlands, Nairobi',
        'move_destination' => 'Nakuru Town',
        'move_date' => now()->addDays(5),
        'move_size' => 'Small office',
        'quote_reference' => '#QTEDIT',
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'subtotal' => 10000,
        'tax' => 0,
        'total_amount' => 10000,
        'status' => 'draft',
        'payment_method' => 'mobile_money',
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Original service',
        'quantity' => 1,
        'unit_price' => 10000,
        'total' => 10000,
    ]);

    $this->get(route('invoice.edit', $invoice))
        ->assertOk()
        ->assertSee('Edit Invoice')
        ->assertSee('INV-EDIT-01')
        ->assertSee('Original service')
        ->assertSee('Update Invoice');

    $response = $this->put(route('invoice.update', $invoice), [
        'invoice_number' => 'INV-EDIT-01',
        'customer_name' => 'Edited Client',
        'customer_email' => 'edited.client@gmail.com',
        'customer_phone' => '0712 555 666',
        'move_origin' => 'Kileleshwa, Nairobi',
        'move_destination' => 'Nyali, Mombasa',
        'move_date' => now()->addDays(8)->toDateString(),
        'move_size' => 'Two bedroom apartment',
        'quote_reference' => '#QTEDIT',
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(10)->toDateString(),
        'status' => 'unpaid',
        'payment_method' => 'bank_transfer',
        'tax' => 300,
        'notes' => 'Updated after customer call.',
        'items' => [
            'description' => ['Updated moving service', 'Packing supplies'],
            'quantity' => [1, 3],
            'unit_price' => [14000, 500],
        ],
    ]);

    $invoice->refresh();

    $response
        ->assertRedirect(route('invoice.details', ['invoice' => $invoice->id]))
        ->assertSessionHas('toast-success', 'Invoice updated successfully.');

    expect($invoice->customer_name)->toBe('Edited Client')
        ->and($invoice->status)->toBe('unpaid')
        ->and((float) $invoice->subtotal)->toBe(15500.0)
        ->and((float) $invoice->tax)->toBe(300.0)
        ->and((float) $invoice->total_amount)->toBe(15800.0)
        ->and($invoice->items()->count())->toBe(2);

    Mail::assertNothingSent();

    $this->get(route('invoice.details', ['invoice' => $invoice->id]))
        ->assertOk()
        ->assertSee('Edited Client')
        ->assertSee('Updated moving service')
        ->assertSee('KES 15,800.00');
});

it('marks an invoice as failed when invoice email delivery fails', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Mail::shouldReceive('to')
        ->once()
        ->with('failed.invoice@gmail.com')
        ->andReturn(new class {
            public function send(): void
            {
                throw new RuntimeException('SMTP unavailable');
            }
        });

    $response = $this->post(route('invoice.store'), [
        'invoice_number' => 'INV-FAILED-01',
        'customer_name' => 'Failed Invoice',
        'customer_email' => 'failed.invoice@gmail.com',
        'customer_phone' => '0712 999 111',
        'move_origin' => 'Kasarani, Nairobi',
        'move_destination' => 'Kitengela, Kajiado',
        'move_date' => now()->addDays(6)->toDateString(),
        'move_size' => 'One bedroom apartment',
        'quote_reference' => '#QTFAILED',
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'sent',
        'payment_method' => 'mobile_money',
        'tax' => 0,
        'items' => [
            'description' => ['Moving Truck'],
            'quantity' => [1],
            'unit_price' => [12000],
        ],
    ]);

    $invoice = Invoice::where('invoice_number', 'INV-FAILED-01')->firstOrFail();

    $response
        ->assertRedirect(route('invoice.details', ['invoice' => $invoice->id]))
        ->assertSessionHas('toast-success', 'Invoice created and email queued successfully.');

    expect($invoice->status)->toBe('failed')
        ->and(DB::table('email_delivery_logs')
            ->where('form_type', 'invoice')
            ->where('recipient_email', 'failed.invoice@gmail.com')
            ->where('status', 'failed')
            ->where('response_message', 'Email failed: SMTP unavailable')
            ->exists())->toBeTrue();
});

it('deletes an invoice and its line items from the invoice list action', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $invoice = Invoice::create([
        'invoice_number' => 'INV-DELETE-01',
        'customer_name' => 'Delete Client',
        'customer_email' => 'delete.client@gmail.com',
        'customer_phone' => '0712 222 333',
        'move_origin' => 'Kilimani, Nairobi',
        'move_destination' => 'Karen, Nairobi',
        'move_date' => now()->addDays(4),
        'move_size' => 'Studio apartment',
        'quote_reference' => '#QT99999',
        'invoice_date' => now(),
        'due_date' => now()->addDays(7),
        'subtotal' => 15000,
        'tax' => 0,
        'total_amount' => 15000,
        'status' => 'sent',
        'payment_method' => 'mobile_money',
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Removal service',
        'quantity' => 1,
        'unit_price' => 15000,
        'total' => 15000,
    ]);

    $this->delete(route('invoice.destroy', $invoice))
        ->assertRedirect(route('invoice.index'));

    expect(Invoice::whereKey($invoice->id)->exists())->toBeFalse()
        ->and(InvoiceItem::where('invoice_id', $invoice->id)->exists())->toBeFalse();
});
