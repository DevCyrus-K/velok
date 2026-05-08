<?php

use App\Mail\QuotationMail;
use App\Models\QuoteRequest;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;
use Symfony\Component\Mime\Email;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

function quotePayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '+254700123456',
        'moving_from' => 'Nairobi',
        'moving_to' => 'Mombasa',
        'move_date' => '2026-05-14',
        'service_type' => 'Residential Relocation',
        'move_size' => '2 bedroom house',
        'additional_notes' => 'Handle with care.',
        'source_page' => '/get-quote',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'status' => 'new',
    ], $overrides);
}

function quotationPayload(QuoteRequest $quote, array $overrides = []): array
{
    return array_merge([
        'quote_request_id' => $quote->id,
        'quote_date' => '2026-05-08',
        'quote_valid_until' => '2026-05-15',
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date?->format('Y-m-d') ?? '2026-05-14',
        'quote_amount' => 12000,
        'deposit_percentage' => 50,
        'cancellation_notice_hours' => 48,
        'cancellation_policy' => 'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.',
        'payment_terms' => '50% deposit required to confirm booking. Remaining balance due on day of move. Accepted payments: M-Pesa, Bank Transfer, Cash.',
        'additional_notes' => $quote->additional_notes,
        'services' => [
            'name' => ['Transportation & Fuel'],
            'description' => ['Moving vehicle rental and fuel'],
        ],
    ], $overrides);
}

function acceptedQuotationMailMessage(string $messageId = 'quotation-test-message@example.com'): SentMessage
{
    $email = (new Email())
        ->from('info@kwikshiftmovers.co.ke')
        ->to('client@example.com')
        ->subject('Quotation accepted')
        ->text('Quotation accepted by test transport.');

    $symfonyMessage = new SymfonySentMessage($email, Envelope::create($email));
    $symfonyMessage->setMessageId($messageId);

    return new SentMessage($symfonyMessage);
}

it('lists quotes from the quote requests table', function () {
    $quote = QuoteRequest::query()->create(quotePayload());

    $this->actingAs($this->user)
        ->get(route('quotes.index'))
        ->assertOk()
        ->assertSee('Jane Doe')
        ->assertSee('Residential Relocation')
        ->assertSee('#QT00001')
        ->assertSee('href="tel:+254700123456"', false)
        ->assertDontSee('href="https://wa.me/254700123456"', false)
        ->assertSee(route('invoice.create', ['quote' => $quote->id]), false)
        ->assertSee('data-created-at=', false);
});

it('creates a new quote', function () {
    $response = $this->actingAs($this->user)
        ->post(route('quotes.store'), quotePayload([
            'full_name' => 'Create Quote Lead',
            'email' => 'create@example.com',
        ]));

    $quote = QuoteRequest::query()->first();

    $response->assertRedirect(route('quotes.show', $quote));

    $this->assertDatabaseHas('quote_requests', [
        'full_name' => 'Create Quote Lead',
        'email' => 'create@example.com',
        'status' => 'new',
    ]);
});

it('updates an existing quote', function () {
    $quote = QuoteRequest::query()->create(quotePayload());

    $response = $this->actingAs($this->user)
        ->put(route('quotes.update', $quote), quotePayload([
            'full_name' => 'Updated Lead',
            'status' => 'processing',
        ]));

    $response->assertRedirect(route('quotes.show', $quote));

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'full_name' => 'Updated Lead',
        'status' => 'processing',
    ]);
});

it('keeps long-distance quote services selected on edit and quotation screens', function () {
    $quote = QuoteRequest::query()->create(quotePayload([
        'service_type' => 'Long Distance Move',
        'move_size' => 'Nairobi to Eldoret',
        'status' => 'quoted',
    ]));

    $this->actingAs($this->user)
        ->get(route('quotes.edit', $quote))
        ->assertOk()
        ->assertSee('value="Long-Distance Move" selected', false);

    $this->actingAs($this->user)
        ->get(route('quotations.create', $quote))
        ->assertOk()
        ->assertSee('value="Long-Distance Move" selected', false);
});

it('normalizes legacy quote service labels to the allowed service list', function () {
    $quote = QuoteRequest::query()->create(quotePayload([
        'service_type' => 'Moving & Relocation',
        'status' => 'quoted',
    ]));

    expect($quote->serviceTypeLabel())->toBe('Residential Relocation');

    $this->actingAs($this->user)
        ->get(route('quotes.edit', $quote))
        ->assertOk()
        ->assertSee('value="Residential Relocation" selected', false)
        ->assertDontSee('value="Moving &amp; Relocation"', false);

    $this->actingAs($this->user)
        ->get(route('quotations.create', $quote))
        ->assertOk()
        ->assertSee('value="Residential Relocation" selected', false)
        ->assertDontSee('value="Moving &amp; Relocation"', false);
});

it('renders location autocomplete hooks on quote and quotation forms', function () {
    $quote = QuoteRequest::query()->create(quotePayload([
        'status' => 'quoted',
    ]));

    $this->actingAs($this->user)
        ->get(route('quotes.edit', $quote))
        ->assertOk()
        ->assertSee('id="pickup_location"', false)
        ->assertSee('id="dropoff_location"', false)
        ->assertSee('name="moving_from"', false)
        ->assertSee('name="moving_to"', false)
        ->assertSee('data-location-autocomplete', false);

    $this->actingAs($this->user)
        ->get(route('quotations.create', $quote))
        ->assertOk()
        ->assertSee('id="pickup_location"', false)
        ->assertSee('id="dropoff_location"', false)
        ->assertSee('name="moving_from"', false)
        ->assertSee('name="moving_to"', false)
        ->assertSee('data-location-autocomplete', false);
});

it('approves and declines quotes', function () {
    Carbon::setTestNow('2026-05-08 12:30:00');
    $quote = QuoteRequest::query()->create(quotePayload());

    $this->actingAs($this->user)
        ->patch(route('quotes.approve', $quote))
        ->assertRedirect();

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'status' => 'quoted',
        'approval_date' => '2026-05-08 00:00:00',
    ]);

    $this->actingAs($this->user)
        ->patch(route('quotes.decline', $quote))
        ->assertRedirect();

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'status' => 'closed',
    ]);

    Carbon::setTestNow();
});

it('returns approval date details for ajax approvals', function () {
    Carbon::setTestNow('2026-05-08 09:15:00');

    $quote = QuoteRequest::query()->create(quotePayload());

    $this->actingAs($this->user)
        ->patchJson(route('quotes.approve', $quote))
        ->assertOk()
        ->assertJsonPath('approval_date', '2026-05-08')
        ->assertJsonPath('approval_date_formatted', '08 May 2026')
        ->assertJsonPath('status_label', 'Approved');

    $this->assertDatabaseHas('quote_requests', [
        'id' => $quote->id,
        'approval_date' => '2026-05-08 00:00:00',
    ]);

    Carbon::setTestNow();
});

it('prefills quotation creation from the approved quote request', function () {
    Carbon::setTestNow('2026-05-08 09:15:00');

    $quote = QuoteRequest::query()->create(quotePayload([
        'full_name' => 'Autofill Customer',
        'email' => 'autofill@example.com',
        'phone' => '+254711222333',
        'status' => 'quoted',
        'approval_date' => '2026-05-08',
    ]));

    $this->actingAs($this->user)
        ->get(route('quotations.create', $quote))
        ->assertOk()
        ->assertSee('value="Autofill Customer"', false)
        ->assertSee('value="autofill@example.com • +254711222333"', false)
        ->assertSee('value="Nairobi"', false)
        ->assertSee('value="Mombasa"', false)
        ->assertSee('value="2026-05-15"', false)
        ->assertSee('Quote validity: <span id="quoteValidityDays">7</span> days', false)
        ->assertSee('50% deposit required to confirm booking')
        ->assertSee('Free cancellation up to 48 hours before the scheduled move date');

    Carbon::setTestNow();
});

it('stores quotation authorization from the authenticated user profile', function () {
    $user = User::factory()->create([
        'name' => 'Approver Jane',
        'job_title' => 'Operations Manager',
        'signature' => 'signatures/jane.png',
        'signature_path' => 'signatures/jane.png',
    ]);

    $quote = QuoteRequest::query()->create(quotePayload([
        'status' => 'quoted',
        'approval_date' => '2026-05-08',
    ]));

    $response = $this->actingAs($user)
        ->post(route('quotations.store'), quotationPayload($quote));

    $quotation = Quotation::query()->first();

    $response->assertRedirect(route('quotations.show', $quotation));

    $this->assertDatabaseHas('quotations', [
        'id' => $quotation->id,
        'quote_request_id' => $quote->id,
        'authorized_by' => 'Approver Jane',
        'authorized_role' => 'Operations Manager',
        'signature' => 'signatures/jane.png',
        'approval_date' => '2026-05-08 00:00:00',
        'quote_valid_until' => '2026-05-15 00:00:00',
        'cancellation_policy' => 'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.',
    ]);
});

it('clamps quotation deposit percentage at one hundred percent on save', function () {
    $quote = QuoteRequest::query()->create(quotePayload([
        'status' => 'quoted',
    ]));

    $response = $this->actingAs($this->user)
        ->post(route('quotations.store'), quotationPayload($quote, [
            'deposit_percentage' => 150,
        ]));

    $quotation = Quotation::query()->first();

    $response->assertRedirect(route('quotations.show', $quotation));

    expect((float) $quotation->deposit_percentage)->toBe(100.0)
        ->and($quotation->depositAmount())->toBe(12000.0)
        ->and($quotation->balanceDue())->toBe(0.0);
});

it('saves and redirects to the pdf when download is selected from quotation preview', function () {
    $quote = QuoteRequest::query()->create(quotePayload([
        'status' => 'quoted',
    ]));

    $response = $this->actingAs($this->user)
        ->post(route('quotations.store'), quotationPayload($quote, [
            'action' => 'download',
        ]));

    $quotation = Quotation::query()->first();

    $response->assertRedirect(route('quotations.pdf', $quotation));

    expect($quotation->status)->toBe('draft');
});

it('resets a sent quotation to draft when save as draft is selected', function () {
    $quote = QuoteRequest::query()->create(quotePayload([
        'status' => QuoteRequest::STATUS_EMAILED,
    ]));

    $quotation = Quotation::query()->create([
        'quote_request_id' => $quote->id,
        'company_name' => 'KwikShift Movers & Relocators',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112587581',
        'quote_date' => '2026-05-08',
        'quote_valid_until' => '2026-05-15',
        'status' => 'sent',
        'sent_at' => '2026-05-08 10:00:00',
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 12000,
        'deposit_percentage' => 50,
        'authorized_by' => 'Admin',
        'approval_date' => '2026-05-08',
    ]);

    $this->actingAs($this->user)
        ->put(route('quotations.update', $quotation), quotationPayload($quote, [
            'action' => 'draft',
        ]))
        ->assertRedirect(route('quotations.show', $quotation));

    $quote->refresh();
    $quotation->refresh();

    expect($quotation->status)->toBe('draft')
        ->and($quotation->sent_at)->toBeNull()
        ->and($quote->status)->toBe(QuoteRequest::STATUS_CREATED);
});

it('shows send download close and continue actions in the quotation preview', function () {
    $quote = QuoteRequest::query()->create(quotePayload([
        'status' => 'quoted',
    ]));

    $this->actingAs($this->user)
        ->get(route('quotations.create', $quote))
        ->assertOk()
        ->assertSee('name="action" value="download"', false)
        ->assertSee('name="action" value="send"', false)
        ->assertSee('data-bs-dismiss="modal">Close</button>', false)
        ->assertSee('name="action" value="continue"', false);
});

it('marks a quote as emailed only after the quotation email is accepted', function () {
    config(['mail.default' => 'smtp']);

    $quote = QuoteRequest::query()->create(quotePayload([
        'full_name' => 'Sent Quote Client',
        'email' => 'sent.quote@example.com',
        'status' => QuoteRequest::STATUS_CREATED,
    ]));

    $quotation = Quotation::query()->create([
        'quote_request_id' => $quote->id,
        'company_name' => 'KwikShift Movers & Relocators',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112587581',
        'quote_date' => '2026-05-08',
        'quote_valid_until' => '2026-05-15',
        'status' => 'draft',
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 12000,
        'authorized_by' => 'Admin',
        'approval_date' => '2026-05-08',
    ]);

    Mail::shouldReceive('to')
        ->once()
        ->with('sent.quote@example.com')
        ->andReturn(new class {
            public function send(QuotationMail $mail): SentMessage
            {
                return acceptedQuotationMailMessage('quotation-accepted@example.com');
            }
        });

    $this->actingAs($this->user)
        ->post(route('quotations.send', $quotation))
        ->assertRedirect(route('quotations.show', $quotation))
        ->assertSessionHas('toast-success', 'Quotation sent to client successfully.');

    $quote->refresh();
    $quotation->refresh();

    expect($quote->status)->toBe(QuoteRequest::STATUS_EMAILED)
        ->and($quotation->status)->toBe('sent')
        ->and($quotation->sent_at)->not->toBeNull()
        ->and(DB::table('email_delivery_logs')
            ->where('form_type', 'quotation')
            ->where('recipient_email', 'sent.quote@example.com')
            ->where('status', 'sent')
            ->where('response_message', 'like', '%quotation-accepted@example.com%')
            ->exists())->toBeTrue();
});

it('marks a quote email failed when quotation delivery throws', function () {
    config(['mail.default' => 'smtp']);

    $quote = QuoteRequest::query()->create(quotePayload([
        'full_name' => 'Failed Quote Client',
        'email' => 'failed.quote@example.com',
        'status' => QuoteRequest::STATUS_CREATED,
    ]));

    $quotation = Quotation::query()->create([
        'quote_request_id' => $quote->id,
        'company_name' => 'KwikShift Movers & Relocators',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112587581',
        'quote_date' => '2026-05-08',
        'quote_valid_until' => '2026-05-15',
        'status' => 'draft',
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 12000,
        'authorized_by' => 'Admin',
        'approval_date' => '2026-05-08',
    ]);

    Mail::shouldReceive('to')
        ->once()
        ->with('failed.quote@example.com')
        ->andReturn(new class {
            public function send(QuotationMail $mail): void
            {
                throw new RuntimeException('SMTP unavailable');
            }
        });

    $this->actingAs($this->user)
        ->post(route('quotations.send', $quotation))
        ->assertRedirect(route('quotations.show', $quotation))
        ->assertSessionHas('toast-error', 'Quotation email failed. Delivery status was logged.');

    $quote->refresh();
    $quotation->refresh();

    expect($quote->status)->toBe(QuoteRequest::STATUS_EMAIL_FAILED)
        ->and($quotation->status)->toBe('draft')
        ->and($quotation->sent_at)->toBeNull()
        ->and(DB::table('email_delivery_logs')
            ->where('form_type', 'quotation')
            ->where('recipient_email', 'failed.quote@example.com')
            ->where('status', 'failed')
            ->where('response_message', 'Email failed: SMTP unavailable')
            ->exists())->toBeTrue();
});

it('does not mark a quote emailed when the mailer only logs locally', function () {
    config(['mail.default' => 'log']);

    $quote = QuoteRequest::query()->create(quotePayload([
        'full_name' => 'Logged Quote Client',
        'email' => 'logged.quote@example.com',
        'status' => QuoteRequest::STATUS_CREATED,
    ]));

    $quotation = Quotation::query()->create([
        'quote_request_id' => $quote->id,
        'company_name' => 'KwikShift Movers & Relocators',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112587581',
        'quote_date' => '2026-05-08',
        'quote_valid_until' => '2026-05-15',
        'status' => 'draft',
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 12000,
        'authorized_by' => 'Admin',
        'approval_date' => '2026-05-08',
    ]);

    $this->actingAs($this->user)
        ->post(route('quotations.send', $quotation))
        ->assertRedirect(route('quotations.show', $quotation))
        ->assertSessionHas('toast-error', 'Quotation email failed. Delivery status was logged.');

    $quote->refresh();

    expect($quote->status)->toBe(QuoteRequest::STATUS_EMAIL_FAILED)
        ->and(DB::table('email_delivery_logs')
            ->where('form_type', 'quotation')
            ->where('recipient_email', 'logged.quote@example.com')
            ->where('status', 'failed')
            ->where('response_message', 'like', '%MAIL_MAILER=log%')
            ->exists())->toBeTrue();
});

it('shows create quote only after approval and view quote after the quote is created', function () {
    $quote = QuoteRequest::query()->create(quotePayload([
        'status' => 'new',
    ]));

    $this->actingAs($this->user)
        ->get(route('quotes.show', $quote))
        ->assertOk()
        ->assertDontSee('Create Quote')
        ->assertSee('Create Invoice')
        ->assertSee(route('invoice.create', ['quote' => $quote->id]), false);

    $quote->update(['status' => 'quoted']);

    $this->actingAs($this->user)
        ->get(route('quotes.show', $quote))
        ->assertOk()
        ->assertSee('Create Quotation')
        ->assertDontSee('View Quotation');

    Quotation::query()->create([
        'quote_request_id' => $quote->id,
        'company_name' => 'KwikShift Movers & Relocators',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112587581',
        'quote_date' => now(),
        'quote_valid_until' => now()->addDays(14),
        'status' => 'draft',
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 12000,
        'authorized_by' => 'Admin',
        'approval_date' => now(),
    ]);

    $quote->update(['status' => 'created']);

    $this->actingAs($this->user)
        ->get(route('quotes.show', $quote))
        ->assertOk()
        ->assertSee('View Quotation')
        ->assertSee('Edit Quotation')
        ->assertSee('Delete Quotation')
        ->assertSee('Download Quotation')
        ->assertSee('Send Quotation via Email')
        ->assertDontSee('Create Quotation')
        ->assertDontSee('Approve Quote Request')
        ->assertDontSee('Decline');
});

it('deletes a quote', function () {
    $quote = QuoteRequest::query()->create(quotePayload());

    $this->actingAs($this->user)
        ->delete(route('quotes.destroy', $quote))
        ->assertRedirect(route('quotes.index'));

    $this->assertDatabaseMissing('quote_requests', [
        'id' => $quote->id,
    ]);
});
