<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MailSender
{
    public const INFO = 'info';
    public const NOREPLY = 'noreply';
    public const SALES = 'sales';
    public const CAREERS = 'careers';

    public const MESSAGE_ROLES = [
        self::INFO,
        self::NOREPLY,
        self::SALES,
    ];

    public function address(string $role): Address
    {
        $sender = $this->sender($role);

        return new Address($sender['address'], $sender['name']);
    }

    /**
     * @return array{address: string, name: string}
     */
    public function sender(string $role): array
    {
        $role = strtolower($role);
        $company = $this->company();
        $configured = (array) config("mail.senders.{$role}", []);
        $purpose = $this->purpose($role);
        $purposeSender = $this->purposeSender($purpose);
        $address = $this->email($purposeSender['address'] ?? null)
            ?: $this->email($configured['address'] ?? null);

        if ($role === self::INFO) {
            $address = $address ?: $this->email($company['email'] ?? null);
        }

        $address ??= $this->email(config('mail.from.address')) ?: $this->fallbackAddress($role);

        $name = $this->text($purposeSender['name'] ?? null)
            ?: $this->text($configured['name'] ?? null)
            ?: $this->text($company['name'] ?? null)
            ?: $this->text(config('mail.from.name'))
            ?: (string) config('app.name', 'Velok');

        return [
            'address' => $address,
            'name' => $name,
        ];
    }

    /**
     * @return array<int, array{role: string, label: string, address: string, name: string}>
     */
    public function messageOptions(): array
    {
        return collect(self::MESSAGE_ROLES)
            ->map(function (string $role): array {
                $sender = $this->sender($role);

                return [
                    'role' => $role,
                    'label' => $this->label($role),
                    'address' => $sender['address'],
                    'name' => $sender['name'],
                ];
            })
            ->all();
    }

    public function label(string $role): string
    {
        return match (strtolower($role)) {
            self::NOREPLY => 'No Reply',
            self::SALES => 'Sales',
            self::CAREERS => 'Careers',
            default => 'Info',
        };
    }

    public function validMessageRole(?string $role): string
    {
        $role = strtolower(trim((string) $role));

        return in_array($role, self::MESSAGE_ROLES, true) ? $role : self::INFO;
    }

    private function company(): array
    {
        try {
            return app(CompanyProfile::class)->data();
        } catch (Throwable) {
            return [];
        }
    }

    private function fallbackAddress(string $role): string
    {
        return match ($role) {
            self::INFO => 'info@kwikshiftmovers.co.ke',
            self::NOREPLY => 'noreply@kwikshiftmovers.co.ke',
            self::SALES => 'sales@kwikshiftmovers.co.ke',
            self::CAREERS => 'careers@kwikshiftmovers.co.ke',
            default => $this->email(config('mail.from.address')) ?: 'info@kwikshiftmovers.co.ke',
        };
    }

    private function purpose(string $role): string
    {
        return match ($role) {
            self::SALES => 'invoices',
            self::NOREPLY => 'noreply',
            self::CAREERS => 'careers',
            default => 'messages',
        };
    }

    /**
     * @return array{address: ?string, name: ?string}
     */
    private function purposeSender(string $purpose): array
    {
        if (! Schema::hasTable('app_settings')) {
            return ['address' => null, 'name' => null];
        }

        try {
            return [
                'address' => AppSetting::value('email', "mail_from_{$purpose}_address"),
                'name' => AppSetting::value('email', "mail_from_{$purpose}_name"),
            ];
        } catch (Throwable) {
            return ['address' => null, 'name' => null];
        }
    }

    private function email(mixed $value): ?string
    {
        $email = trim((string) $value);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function text(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }
}
