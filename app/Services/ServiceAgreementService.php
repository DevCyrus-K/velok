<?php

namespace App\Services;

use App\Mail\ServiceAgreementMail;
use App\Models\EmailLog;
use App\Models\Quotation;
use App\Models\User;
use App\Support\BookingFlow;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use App\Support\NotificationLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ServiceAgreementService
{
    private const STORAGE_DISK = 'local';
    private const STORAGE_DIRECTORY = 'service-agreements';
    private const EMAIL_PENDING = 'pending';
    private const EMAIL_SENT = 'sent';
    private const EMAIL_FAILED = 'email_failed';

    /**
     * @return array{path: string, emailed: bool}
     */
    public function generateAndSendForApprovedQuotation(Quotation $quotation, ?User $actor = null): array
    {
        $path = $this->generateForApprovedQuotation($quotation, $actor);
        $quotation->refresh()->loadMissing('quoteRequest');

        return [
            'path' => $path,
            'emailed' => $this->sendAgreementEmail($quotation, $path, $actor),
        ];
    }

    public function generateForApprovedQuotation(Quotation $quotation, ?User $actor = null): string
    {
        $quotation->loadMissing('quoteRequest');
        $this->validateApprovedQuotation($quotation);

        $company = $this->companyProfileForAgreement();
        $timestamp = now();
        $filename = $this->storedFilename($quotation, $timestamp);
        $path = self::STORAGE_DIRECTORY.'/'.$filename;
        $pdf = Pdf::loadView('agreements.service', $this->pdfData($quotation, $company))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'enable_html5_parser' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Helvetica',
            ]);

        $output = $this->renderPdf($pdf, $company['name']);
        Storage::disk(self::STORAGE_DISK)->put($path, $output);

        if (! Storage::disk(self::STORAGE_DISK)->exists($path) || Storage::disk(self::STORAGE_DISK)->size($path) <= 0) {
            throw new RuntimeException('Service Agreement generation halted: PDF could not be written to disk.');
        }

        $quotation->update([
            'service_agreement_path' => $path,
            'service_agreement_filename' => $filename,
            'service_agreement_generated_at' => $timestamp,
            'service_agreement_email_status' => self::EMAIL_PENDING,
            'service_agreement_email_failed_reason' => null,
        ]);

        $quotation->logStage(
            'SERVICE_AGREEMENT_GENERATED',
            'Service Agreement PDF generated',
            'system',
            $actor?->name,
            null,
            'pdf',
            ['path' => $path]
        );

        return $path;
    }

    public function downloadFilename(Quotation $quotation): string
    {
        return 'service_agreement_'.$this->quoteId($quotation).'.pdf';
    }

    public function storageDisk(): string
    {
        return self::STORAGE_DISK;
    }

    private function sendAgreementEmail(Quotation $quotation, string $path, ?User $actor = null): bool
    {
        $quotation->loadMissing('quoteRequest');
        $this->validateApprovedQuotation($quotation);

        if (! Storage::disk(self::STORAGE_DISK)->exists($path) || Storage::disk(self::STORAGE_DISK)->size($path) <= 0) {
            throw new RuntimeException('Service Agreement email halted: generated PDF was not found on disk.');
        }

        $company = $this->companyProfileForAgreement();
        $recipient = Str::lower(trim((string) $quotation->customer_email));
        $subject = 'Your Service Agreement — '.$company['name'].' | Quote Ref: '.$quotation->reference;
        $sender = [
            'address' => $company['email'],
            'name' => $company['name'],
        ];
        $emailLog = $this->createEmailLog($quotation, $sender, $recipient, $subject);
        $lastException = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                if ($attempt > 1) {
                    $emailLog?->update(['status' => EmailLog::STATUS_SENDING]);
                }

                $this->ensureDeliverableMailTransport();

                $mailer = Mail::to($recipient);
                $message = new ServiceAgreementMail(
                    quotation: $quotation,
                    company: $company,
                    agreementPath: $path,
                    agreementFilename: $this->downloadFilename($quotation),
                    subject: $subject,
                    emailLogId: $emailLog?->getKey(),
                );
                $sentMessage = $mailer->send($message);

                $messageId = $this->validatedSentMessageId($sentMessage);
                $this->markEmailLogSent($emailLog, $attempt);
                $quotation->update([
                    'service_agreement_email_status' => self::EMAIL_SENT,
                    'service_agreement_emailed_at' => now(),
                    'service_agreement_email_failed_reason' => null,
                    'service_agreement_email_attempts' => $attempt,
                ]);

                $quotation->logStage(
                    'SERVICE_AGREEMENT_EMAIL_SENT',
                    'Service Agreement emailed to client',
                    'system',
                    $actor?->name,
                    null,
                    'email',
                    ['recipient' => $recipient, 'message_id' => $messageId]
                );

                return true;
            } catch (Throwable $exception) {
                $lastException = $exception;
                $this->markEmailLogFailed($emailLog, $exception, $attempt);
                $quotation->update([
                    'service_agreement_email_status' => self::EMAIL_FAILED,
                    'service_agreement_email_failed_reason' => Str::limit($exception->getMessage(), 1000, ''),
                    'service_agreement_email_attempts' => $attempt,
                ]);

                Log::error('Service Agreement email attempt failed', [
                    'quotation_id' => $quotation->getKey(),
                    'quote_request_id' => $quotation->quote_request_id,
                    'recipient' => $recipient,
                    'attempt' => $attempt,
                    'error' => $exception->getMessage(),
                ]);

                if ($attempt === 1) {
                    $this->waitBeforeRetry();
                }
            }
        }

        if ($lastException) {
            app(NotificationLogger::class)->serviceAgreementEmailFailed($quotation, $recipient, $lastException->getMessage());
            report($lastException);
        }

        return false;
    }

    private function validateApprovedQuotation(Quotation $quotation): void
    {
        if (! $quotation->exists || ! $quotation->quoteRequest) {
            throw new RuntimeException('Service Agreement generation halted: approved quote data was not found.');
        }

        if ($quotation->status !== Quotation::STATUS_APPROVED) {
            throw new RuntimeException('Service Agreement generation halted: quote status must be approved.');
        }

        $email = Str::lower(trim((string) $quotation->customer_email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Service Agreement generation halted: client email address is missing or invalid.');
        }
    }

    /**
     * @return array{name: string, email: string, phone: string, physical_address: string, logo_path: string, logo_data_uri: ?string, website: string, business_registration_number: string, authorized_representative_name: string, authorized_representative_title: string, liability_cap_amount: string}
     */
    private function companyProfileForAgreement(): array
    {
        $company = app(CompanyProfile::class)->data();
        $physicalAddress = collect([
            $company['address_line_1'] ?? null,
            $company['address_line_2'] ?? null,
        ])->map(fn ($line) => trim((string) $line))->filter()->implode(', ');

        $values = [
            'name' => trim((string) ($company['name'] ?? '')),
            'email' => trim((string) ($company['email'] ?? '')),
            'phone' => trim((string) ($company['phone'] ?? '')),
            'physical_address' => $physicalAddress,
            'business_registration_number' => trim((string) ($company['business_registration_number'] ?? '')),
            'authorized_representative_name' => trim((string) ($company['authorized_representative_name'] ?? '')),
            'authorized_representative_title' => trim((string) ($company['authorized_representative_title'] ?? '')),
            'liability_cap_amount' => trim((string) ($company['liability_cap_amount'] ?? '')),
        ];

        $missing = collect($values)
            ->filter(fn (string $value): bool => $value === '')
            ->keys()
            ->map(fn (string $key): string => Str::headline(str_replace('_', ' ', $key)))
            ->values();

        if ($missing->isNotEmpty()) {
            $message = 'Service Agreement generation halted: missing company settings: '.$missing->implode(', ').'.';
            Log::error($message);

            throw new RuntimeException($message);
        }

        if (! filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Service Agreement generation halted: company email address is invalid.');
        }

        return array_merge($values, [
            'logo_path' => trim((string) ($company['logo_path'] ?? '')),
            'logo_data_uri' => app(CompanyProfile::class)->logoDataUri(),
            'website' => trim((string) ($company['website'] ?? '')),
        ]);
    }

    private function pdfData(Quotation $quotation, array $company): array
    {
        $quote = $quotation->quoteRequest;
        $serviceDescriptions = $this->serviceDescriptions($quotation);
        $pricingItems = $this->pricingItems($quotation, $serviceDescriptions);
        $paymentMethods = $this->paymentMethodLines();

        return [
            'quotation' => $quotation,
            'quote' => $quote,
            'company' => $company,
            'dash' => '___________________________',
            'proposedMoveDate' => $this->dateLabel($quotation->move_date ?: $quote->move_date),
            'areaType' => $this->areaType($quote->serviceTypeLabel()),
            'typeOfServices' => $quote->serviceTypeLabel(),
            'serviceDescriptions' => $serviceDescriptions,
            'pricingItems' => $pricingItems,
            'subtotal' => (float) ($quotation->subtotal ?? $quotation->quote_amount ?? 0),
            'tax' => 0.0,
            'total' => (float) ($quotation->total ?? $quotation->quote_amount ?? 0),
            'depositAmount' => $quotation->depositAmount(),
            'depositPercentage' => (float) ($quotation->deposit_percentage ?? 0),
            'balanceDue' => $quotation->balanceDue(),
            'paymentTerms' => trim((string) ($quotation->payment_terms ?? '')),
            'paymentMethods' => $paymentMethods,
            'paymentDueDate' => $this->paymentDueDate($quotation),
            'quoteReference' => $quotation->reference,
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function serviceDescriptions(Quotation $quotation): Collection
    {
        $services = collect($quotation->services_included ?? [])
            ->map(function (mixed $service): string {
                if (is_array($service)) {
                    $name = trim((string) ($service['name'] ?? ''));
                    $description = trim((string) ($service['description'] ?? ''));

                    return collect([$name, $description])->filter()->implode(' - ');
                }

                return trim((string) $service);
            })
            ->filter()
            ->values();

        if ($services->isNotEmpty()) {
            return $services;
        }

        $quote = $quotation->quoteRequest;

        return collect([
            collect([
                $quote?->serviceTypeLabel() ?: 'Moving service',
                $quotation->pickup_location ?: $quote?->moving_from,
                $quotation->dropoff_location ?: $quote?->moving_to,
                $quote?->move_size,
            ])->filter()->implode(' - '),
        ])->filter()->values();
    }

    /**
     * @param Collection<int, string> $serviceDescriptions
     * @return Collection<int, array{description: string, quantity: int, unit_price: float, amount: float}>
     */
    private function pricingItems(Quotation $quotation, Collection $serviceDescriptions): Collection
    {
        $totalCents = max(0, (int) round((float) ($quotation->quote_amount ?? 0) * 100));
        $count = max(1, $serviceDescriptions->count());
        $baseCents = intdiv($totalCents, $count);
        $remainderCents = $totalCents % $count;

        return $serviceDescriptions
            ->values()
            ->map(function (string $description, int $index) use ($baseCents, $remainderCents): array {
                $lineCents = $baseCents + ($index < $remainderCents ? 1 : 0);
                $amount = round($lineCents / 100, 2);

                return [
                    'description' => $description,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'amount' => $amount,
                ];
            });
    }

    /**
     * @return Collection<int, string>
     */
    private function paymentMethodLines(): Collection
    {
        return app(BookingFlow::class)
            ->paymentMethodDisplays()
            ->pluck('display')
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values();
    }

    private function areaType(string $serviceType): string
    {
        $serviceType = Str::lower($serviceType);

        return match (true) {
            Str::contains($serviceType, ['office', 'commercial', 'warehouse']) => 'Commercial',
            Str::contains($serviceType, ['industrial']) => 'Industrial',
            default => 'Residential',
        };
    }

    private function paymentDueDate(Quotation $quotation): string
    {
        if ($quotation->move_date) {
            return 'Balance due on move date: '.$quotation->move_date->format('d M Y');
        }

        if ($quotation->quote_valid_until) {
            return 'Deposit due by quote validity date: '.$quotation->quote_valid_until->format('d M Y');
        }

        return 'As stated in the payment terms';
    }

    private function renderPdf(mixed $pdf, string $companyName): string
    {
        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();

        $canvas->page_script(function (int $pageNumber, int $pageCount, Canvas $canvas, FontMetrics $fontMetrics) use ($companyName): void {
            $width = $canvas->get_width();
            $height = $canvas->get_height();
            $margin = 70.87;
            $font = $fontMetrics->getFont('Helvetica', 'normal');
            $bold = $fontMetrics->getFont('Helvetica', 'bold');
            $dark = [0.016, 0.133, 0.243];

            if ($pageNumber > 1) {
                $header = Str::limit($companyName.' | SERVICE AGREEMENT', 90, '');
                $canvas->text($margin, 32, $header, $bold, 9, $dark);
                $canvas->line($margin, 46, $width - $margin, 46, [0.9, 0.93, 0.96], 0.5);
            }

            $pageText = 'Page '.$pageNumber.' of '.$pageCount;
            $textWidth = $fontMetrics->getTextWidth($pageText, $font, 8);
            $canvas->line($margin, $height - 42, $width - $margin, $height - 42, [0.9, 0.93, 0.96], 0.5);
            $canvas->text(($width - $textWidth) / 2, $height - 30, $pageText, $font, 8, $dark);
        });

        return $pdf->output();
    }

    private function storedFilename(Quotation $quotation, Carbon $timestamp): string
    {
        return 'service_agreement_'.$this->quoteId($quotation).'_'.$timestamp->format('YmdHis').'.pdf';
    }

    private function quoteId(Quotation $quotation): string
    {
        return (string) ($quotation->quote_request_id ?: $quotation->getKey());
    }

    private function dateLabel(mixed $date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->format('d M Y');
        }

        if (is_string($date) && trim($date) !== '') {
            return Carbon::parse($date)->format('d M Y');
        }

        return 'To be confirmed';
    }

    /**
     * @param array{address: string, name: string} $sender
     */
    private function createEmailLog(Quotation $quotation, array $sender, string $recipient, string $subject): ?EmailLog
    {
        if (! Schema::hasTable('email_logs')) {
            return null;
        }

        try {
            return $quotation->emailLogs()->create([
                'sender_role' => MailSender::SALES,
                'sender_email' => Str::limit($sender['address'], 190, ''),
                'sender_name' => Str::limit($sender['name'], 190, ''),
                'recipient_email' => Str::limit($recipient, 190, ''),
                'subject' => Str::limit($subject, 190, ''),
                'status' => EmailLog::STATUS_SENDING,
                'tracking_token' => (string) Str::uuid(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function markEmailLogSent(?EmailLog $emailLog, int $attempt): void
    {
        $emailLog?->update([
            'status' => EmailLog::STATUS_SENT,
            'sent_at' => now(),
            'failed_reason' => null,
            'attempts' => $attempt,
        ]);
    }

    private function markEmailLogFailed(?EmailLog $emailLog, Throwable $exception, int $attempt): void
    {
        $emailLog?->update([
            'status' => EmailLog::STATUS_FAILED,
            'failed_reason' => Str::limit($exception->getMessage(), 1000, ''),
            'attempts' => $attempt,
        ]);
    }

    private function ensureDeliverableMailTransport(): void
    {
        if ($this->mailerIsTestDouble()) {
            return;
        }

        $mailer = (string) config('mail.default', '');
        $transport = (string) (config("mail.mailers.{$mailer}.transport") ?: $mailer ?: 'unknown');

        if (in_array(Str::lower($transport), ['array', 'log'], true)) {
            throw new RuntimeException(
                'Service Agreement email failed: MAIL_MAILER='.$transport
                .' only stores email locally. Configure smtp, resend, postmark, mailgun, or ses before sending agreements.'
            );
        }
    }

    private function validatedSentMessageId(mixed $sentMessage): string
    {
        if ($this->mailerIsTestDouble() && $sentMessage === null) {
            return 'mail-test-double';
        }

        if (! $sentMessage instanceof SentMessage) {
            throw new RuntimeException('Service Agreement email failed: mail transport did not confirm that the message was accepted.');
        }

        $messageId = trim((string) $sentMessage->getMessageId());

        if ($messageId === '') {
            throw new RuntimeException('Service Agreement email failed: mail transport accepted the message without a delivery message ID.');
        }

        return $messageId;
    }

    private function mailerIsTestDouble(): bool
    {
        if (! app()->runningUnitTests()) {
            return false;
        }

        $mailer = Mail::getFacadeRoot();

        if (! is_object($mailer)) {
            return false;
        }

        if (is_a($mailer, \Illuminate\Support\Testing\Fakes\MailFake::class)) {
            return true;
        }

        return interface_exists(\Mockery\MockInterface::class)
            && $mailer instanceof \Mockery\MockInterface;
    }

    private function waitBeforeRetry(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        sleep(30);
    }
}
