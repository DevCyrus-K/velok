<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Message;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\CustomerSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function seedDashboardMetricsForTest(): void
{
    $rows = [
        ['completed_moves' => 764, 'cancelled_bookings' => 146, 'desktop_visitors' => 438, 'mobile_visitors' => 337, 'tablet_visitors' => 67],
        ['completed_moves' => 812, 'cancelled_bookings' => 161, 'desktop_visitors' => 477, 'mobile_visitors' => 384, 'tablet_visitors' => 75],
        ['completed_moves' => 858, 'cancelled_bookings' => 178, 'desktop_visitors' => 514, 'mobile_visitors' => 432, 'tablet_visitors' => 82],
        ['completed_moves' => 917, 'cancelled_bookings' => 195, 'desktop_visitors' => 549, 'mobile_visitors' => 481, 'tablet_visitors' => 90],
        ['completed_moves' => 961, 'cancelled_bookings' => 211, 'desktop_visitors' => 583, 'mobile_visitors' => 533, 'tablet_visitors' => 98],
        ['completed_moves' => 1000, 'cancelled_bookings' => 229, 'desktop_visitors' => 644, 'mobile_visitors' => 591, 'tablet_visitors' => 107],
    ];

    foreach ($rows as $index => $row) {
        DB::table('dashboard_monthly_metrics')->insert(array_merge($row, [
            'month' => now()->copy()->startOfMonth()->subMonths(5 - $index)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}

it('normalizes moving and relocation quote categories into one label across quotes and customers', function () {
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

    expect($quote->service_type)->toBe('Moving & Relocation');

    app(CustomerSyncService::class)->sync();

    $this->get(route('quotes.index'))
        ->assertOk()
        ->assertSee('Moving & Relocation');

    $this->get(route('any', 'customers'))
        ->assertOk()
        ->assertSee('Moving & Relocation');
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
        'service_type' => 'Moving & Relocation',
        'move_size' => 'Boutique shelves and cartons',
        'additional_notes' => 'Need overnight dispatch.',
        'source_page' => '/services/moving',
        'ip_address' => '102.89.14.88',
        'user_agent' => 'Mozilla/5.0',
        'status' => 'quoted',
    ]);

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

    seedDashboardMetricsForTest();

    $this->get(route('second', ['dashboard', 'index']))
        ->assertOk()
        ->assertSee('KES 29,000.00')
        ->assertSee('Completed Moves')
        ->assertSee('5,312')
        ->assertSee('Cancelled Bookings')
        ->assertSee('1,120')
        ->assertSee('Total Visitors')
        ->assertSee('6,482')
        ->assertSee('data-lucide="users"', false)
        ->assertSee('Desktop')
        ->assertSee('Mobile')
        ->assertSee('Tablet')
        ->assertSee('Moving & Relocation')
        ->assertSee('Amina Hassan');
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
        'service_type' => 'Moving & Relocation',
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
        ->assertSee('Open full page');
});
