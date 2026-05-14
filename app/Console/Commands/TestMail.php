<?php

namespace App\Console\Commands;

use App\Mail\SimpleTextMail;
use App\Services\MailConfigService;
use App\Support\EmailLogRecorder;
use App\Support\MailSender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestMail extends Command
{
    protected $signature = 'mail:test {email}';

    protected $description = 'Send a test email to verify SMTP configuration.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $subject = 'Test email - '.config('app.name');
        $emailLog = app(EmailLogRecorder::class)->create($email, $subject);

        try {
            MailConfigService::apply();
            $from = app(MailSender::class)->sender(MailSender::INFO);

            // Queue hardening: test mail uses a queued mailable instead of raw synchronous mail.
            Mail::to($email)->send(new SimpleTextMail(
                $subject,
                'This is a test email from '.config('app.name').'.',
                $from,
            ));
            app(EmailLogRecorder::class)->sent($emailLog);
        } catch (Throwable $exception) {
            app(EmailLogRecorder::class)->failed($emailLog, $exception);
            $this->error('❌ Failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('✅ Mail sent successfully');

        return self::SUCCESS;
    }
}
