<?php

use App\Mail\MessageMail;
use App\Models\EmailLog;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function messagePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Buyer Lead',
        'email' => 'buyer@example.com',
        'phone' => '+254700000001',
        'subject' => 'Listing inquiry',
        'message' => 'I would like to know if this property is still available.',
        'status' => 'unread',
        'origin_page' => '/properties/for-sale',
    ], $overrides);
}

it('renders the messages pages without missing route errors', function () {
    $user = User::factory()->create();
    $message = Message::query()->create(messagePayload());

    $this->actingAs($user)
        ->get(route('messages.index'))
        ->assertOk()
        ->assertSee('Listing inquiry');

    $this->actingAs($user)
        ->get(route('messages.compose'))
        ->assertOk()
        ->assertSee('Compose');

    $this->actingAs($user)
        ->get(route('messages.show', $message))
        ->assertOk()
        ->assertSee('Buyer Lead');
});

it('deletes a message from the inbox', function () {
    $user = User::factory()->create();
    $message = Message::query()->create(messagePayload());

    $this->actingAs($user)
        ->delete(route('messages.destroy', $message))
        ->assertRedirect(route('messages.index'));

    $this->assertSoftDeleted('messages', [
        'id' => $message->id,
    ]);
});

it('sends composed emails through a mailable and logs delivery', function () {
    Mail::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('messages.store'), [
            'email' => 'buyer@example.com',
            'subject' => 'Viewing request',
            'message' => 'Please confirm the earliest viewing slot.',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Email sent',
        ]);

    $message = Message::query()->where('email', 'buyer@example.com')->firstOrFail();
    $log = EmailLog::query()->where('recipient_email', 'buyer@example.com')->firstOrFail();

    expect($message->status)->toBe('sent')
        ->and($message->email_log_id)->toBe($log->id)
        ->and($log->status)->toBe(EmailLog::STATUS_SENT)
        ->and($log->attempts)->toBe(1)
        ->and($log->tracking_token)->not->toBeNull();

    $deliveryLog = DB::table('email_delivery_logs')
        ->where('recipient_email', 'buyer@example.com')
        ->where('form_type', 'message')
        ->first();

    expect($deliveryLog)->not->toBeNull()
        ->and($deliveryLog->status)->toBe('sent')
        ->and($deliveryLog->subject)->toBe('Viewing request')
        ->and($deliveryLog->response_message)->toBe('Message email sent successfully.');

    Mail::assertSent(MessageMail::class, fn (MessageMail $mail) => $mail->hasTo('buyer@example.com'));
});

it('sends replies through a mailable and updates the message response', function () {
    Mail::fake();

    $user = User::factory()->create();
    $message = Message::query()->create(messagePayload());

    $response = $this->actingAs($user)
        ->postJson(route('messages.respond', $message), [
            'recipient_email' => 'buyer@example.com',
            'subject' => 'Re: Listing inquiry',
            'response' => 'Yes, the property is still available for viewing.',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Email sent',
        ]);

    $message->refresh();
    $log = EmailLog::query()->where('recipient_email', 'buyer@example.com')->latest()->firstOrFail();

    expect($message->status)->toBe('responded')
        ->and($message->response)->toBe('Yes, the property is still available for viewing.')
        ->and($message->email_log_id)->toBe($log->id)
        ->and($log->status)->toBe(EmailLog::STATUS_SENT);

    $deliveryLog = DB::table('email_delivery_logs')
        ->where('recipient_email', 'buyer@example.com')
        ->where('form_type', 'message_reply')
        ->first();

    expect($deliveryLog)->not->toBeNull()
        ->and($deliveryLog->status)->toBe('sent')
        ->and($deliveryLog->subject)->toBe('Re: Listing inquiry')
        ->and($deliveryLog->response_message)->toBe('Message reply email sent successfully.');

    Mail::assertSent(MessageMail::class, fn (MessageMail $mail) => $mail->hasTo('buyer@example.com'));
});

it('logs failed composed email delivery', function () {
    Mail::shouldReceive('to')
        ->once()
        ->with('buyer@example.com')
        ->andThrow(new RuntimeException('SMTP offline'));

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('messages.store'), [
            'email' => 'buyer@example.com',
            'subject' => 'Viewing request',
            'message' => 'Please confirm the earliest viewing slot.',
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'success' => false,
            'error' => 'SMTP offline',
        ]);

    $message = Message::query()->where('email', 'buyer@example.com')->firstOrFail();
    $log = EmailLog::query()->where('recipient_email', 'buyer@example.com')->firstOrFail();
    $deliveryLog = DB::table('email_delivery_logs')
        ->where('recipient_email', 'buyer@example.com')
        ->where('form_type', 'message')
        ->first();

    expect($message->email_log_id)->toBe($log->id)
        ->and($log->status)->toBe(EmailLog::STATUS_FAILED)
        ->and($log->failed_reason)->toBe('SMTP offline')
        ->and($deliveryLog)->not->toBeNull()
        ->and($deliveryLog->status)->toBe('failed')
        ->and($deliveryLog->subject)->toBe('Viewing request')
        ->and($deliveryLog->response_message)->toContain('SMTP offline');
});
