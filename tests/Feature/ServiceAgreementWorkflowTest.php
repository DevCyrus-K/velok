<?php

use App\Mail\ServiceAgreementMail;
use App\Models\ActivityNotification;
use App\Models\AppSetting;
use App\Models\EmailLog;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\ServiceAgreementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function agreementQuotePayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Agreement Client',
        'email' => 'agreement.client@example.com',
        'phone' => '+254700111222',
        'contact_preference' => 'both',
        'moving_from' => 'Nairobi',
        'moving_to' => 'Naivasha',
        'move_date' => '2026-05-20',
        'service_type' => 'Residential Relocation',
        'move_size' => '3 bedroom house',
        'additional_notes' => 'Include packing for kitchen items.',
        'source_page' => '/get-quote',
        'status' => QuoteRequest::STATUS_EMAILED,
    ], $overrides);
}

function agreementCompanySettings(): void
{
    AppSetting::setMany('company', [
        'name' => 'KwikShift Movers & Relocators',
        'email' => 'info@kwikshiftmovers.co.ke',
        'phone' => '+254 112587581',
        'address_line_1' => 'Londiani Road, off Likoni Road',
        'address_line_2' => 'Industrial Area, Nairobi',
        'logo_path' => 'images/logo-dark.png',
        'website' => 'https://kwikshiftmovers.co.ke',
        'business_registration_number' => 'BN-123456',
        'authorized_representative_name' => 'Operations Manager',
        'authorized_representative_title' => 'Head of Operations',
        'liability_cap_amount' => 'KES 100,000',
    ]);
}

it('generates stores emails and downloads a service agreement when a quotation is approved', function () {
    Mail::fake();
    Storage::fake('local');
    agreementCompanySettings();

    $user = User::factory()->create();
    $quote = QuoteRequest::query()->create(agreementQuotePayload());
    $quotation = Quotation::query()->create([
        'quote_request_id' => $quote->id,
        'company_name' => 'KwikShift Movers & Relocators',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112587581',
        'quote_date' => '2026-05-13',
        'quote_valid_until' => '2026-05-20',
        'status' => Quotation::STATUS_SENT,
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 25000,
        'deposit_percentage' => 50,
        'deposit_amount' => 12500,
        'payment_terms' => '50% deposit required to confirm booking. Balance due on move day.',
        'services_included' => [
            ['name' => 'Packing', 'description' => 'Kitchen and fragile items'],
            ['name' => 'Transport', 'description' => 'Truck and crew'],
        ],
        'authorized_by' => 'Admin',
        'authorized_role' => 'Manager',
        'approval_date' => '2026-05-13',
    ]);

    $approveRoute = route('quotations.approve', $quotation);

    $response = $this->actingAs($user)
        ->patch($approveRoute);

    $response
        ->assertRedirect(route('quotations.show', $quotation))
        ->assertSessionHas('toast-success');

    $quotation->refresh();

    expect($quotation->status)->toBe(Quotation::STATUS_APPROVED)
        ->and($quotation->service_agreement_path)->not->toBeNull()
        ->and($quotation->service_agreement_filename)->toStartWith('service_agreement_'.$quote->id.'_')
        ->and($quotation->service_agreement_email_status)->toBe('sent')
        ->and($quotation->service_agreement_email_attempts)->toBe(1)
        ->and($quotation->service_agreement_emailed_at)->not->toBeNull();

    Storage::disk('local')->assertExists($quotation->service_agreement_path);
    Mail::assertSent(ServiceAgreementMail::class, fn (ServiceAgreementMail $mail) => $mail->hasTo('agreement.client@example.com'));

    $emailLog = EmailLog::query()
        ->where('emailable_type', Quotation::class)
        ->where('emailable_id', $quotation->id)
        ->where('recipient_email', 'agreement.client@example.com')
        ->firstOrFail();

    expect($emailLog->status)->toBe(EmailLog::STATUS_SENT)
        ->and($emailLog->sent_at)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('admin.agreements.download', $quote))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'attachment; filename=service_agreement_'.$quote->id.'.pdf');
});

it('marks agreement email failed after retry and alerts admin when delivery fails', function () {
    Storage::fake('local');
    agreementCompanySettings();

    $quote = QuoteRequest::query()->create(agreementQuotePayload([
        'email' => 'agreement.fail@example.com',
        'status' => QuoteRequest::STATUS_CREATED,
    ]));
    $quotation = Quotation::query()->create([
        'quote_request_id' => $quote->id,
        'company_name' => 'KwikShift Movers & Relocators',
        'company_email' => 'info@kwikshiftmovers.co.ke',
        'company_phone' => '+254 112587581',
        'quote_date' => '2026-05-13',
        'quote_valid_until' => '2026-05-20',
        'status' => Quotation::STATUS_APPROVED,
        'moving_from' => $quote->moving_from,
        'moving_to' => $quote->moving_to,
        'move_date' => $quote->move_date,
        'quote_amount' => 25000,
        'deposit_percentage' => 50,
        'deposit_amount' => 12500,
        'payment_terms' => '50% deposit required to confirm booking. Balance due on move day.',
        'services_included' => [
            ['name' => 'Packing', 'description' => 'Kitchen and fragile items'],
        ],
    ]);

    $failingMailer = new class {
        public function send($message): void
        {
            throw new RuntimeException('SMTP down');
        }
    };

    Mail::shouldReceive('to')
        ->twice()
        ->with('agreement.fail@example.com')
        ->andReturn($failingMailer);

    $result = app(ServiceAgreementService::class)->generateAndSendForApprovedQuotation($quotation);
    $quotation->refresh();

    expect($result['emailed'])->toBeFalse()
        ->and($quotation->service_agreement_path)->not->toBeNull()
        ->and($quotation->service_agreement_email_status)->toBe('email_failed')
        ->and($quotation->service_agreement_email_attempts)->toBe(2)
        ->and($quotation->service_agreement_email_failed_reason)->toContain('SMTP down');

    $emailLog = EmailLog::query()
        ->where('emailable_type', Quotation::class)
        ->where('emailable_id', $quotation->id)
        ->where('recipient_email', 'agreement.fail@example.com')
        ->firstOrFail();

    expect($emailLog->status)->toBe(EmailLog::STATUS_FAILED)
        ->and($emailLog->attempts)->toBe(2)
        ->and($emailLog->failed_reason)->toContain('SMTP down')
        ->and(ActivityNotification::query()
            ->where('type', 'service_agreement_email_failed')
            ->where('severity', 'danger')
            ->exists())->toBeTrue();
});
