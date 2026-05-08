<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\Invoice;

class PaymentSettings
{
    public const SECRET_KEYS = [
        'mpesa_till_number',
        'mpesa_paybill_business_number',
        'mpesa_paybill_account_number',
        'mpesa_pochi_phone',
        'bank_account_number',
    ];

    public function values(): array
    {
        return AppSetting::groupValues('payments', $this->defaults());
    }

    public function defaults(): array
    {
        return [
            'mpesa_enabled' => '0',
            'mpesa_type' => 'till',
            'mpesa_till_number' => '',
            'mpesa_till_account_name' => '',
            'mpesa_paybill_business_number' => '',
            'mpesa_paybill_account_number' => '',
            'mpesa_paybill_account_name' => '',
            'mpesa_pochi_phone' => '',
            'mpesa_pochi_registered_name' => '',
            'cash_enabled' => '1',
            'cash_instruction' => 'Pay cash on day of move to our representative.',
            'bank_enabled' => '0',
            'bank_name' => '',
            'bank_account_name' => '',
            'bank_account_number' => '',
            'bank_branch' => '',
            'bank_swift_code' => '',
        ];
    }

    public function methodsForInvoice(?Invoice $invoice = null): array
    {
        $settings = $this->values();
        $methods = [];

        if (($settings['mpesa_enabled'] ?? '0') === '1') {
            $type = $settings['mpesa_type'] ?? 'till';

            if ($type === 'paybill') {
                $methods[] = [
                    'key' => 'mpesa_paybill',
                    'title' => 'M-Pesa Payment',
                    'subtitle' => 'Pay via Paybill',
                    'rows' => [
                        ['label' => 'Business No', 'value' => $this->invoiceText($settings['mpesa_paybill_business_number'] ?? '', $invoice)],
                        ['label' => 'Account No', 'value' => $this->invoiceText($settings['mpesa_paybill_account_number'] ?? '', $invoice)],
                        ['label' => 'Account Name', 'value' => $settings['mpesa_paybill_account_name'] ?? ''],
                    ],
                ];
            } elseif ($type === 'pochi') {
                $methods[] = [
                    'key' => 'mpesa_pochi',
                    'title' => 'M-Pesa Pochi la Biashara',
                    'subtitle' => 'Send to registered business wallet',
                    'rows' => [
                        ['label' => 'Send to', 'value' => $settings['mpesa_pochi_phone'] ?? ''],
                        ['label' => 'Name', 'value' => $settings['mpesa_pochi_registered_name'] ?? ''],
                    ],
                ];
            } else {
                $methods[] = [
                    'key' => 'mpesa_till',
                    'title' => 'M-Pesa Payment',
                    'subtitle' => 'Pay via Buy Goods (Till)',
                    'rows' => [
                        ['label' => 'Till Number', 'value' => $this->invoiceText($settings['mpesa_till_number'] ?? '', $invoice)],
                        ['label' => 'Account Name', 'value' => $settings['mpesa_till_account_name'] ?? ''],
                    ],
                ];
            }
        }

        if (($settings['bank_enabled'] ?? '0') === '1') {
            $methods[] = [
                'key' => 'bank_transfer',
                'title' => 'Bank Transfer',
                'subtitle' => 'Transfer to company bank account',
                'rows' => [
                    ['label' => 'Bank', 'value' => $settings['bank_name'] ?? ''],
                    ['label' => 'Account Name', 'value' => $settings['bank_account_name'] ?? ''],
                    ['label' => 'Account No', 'value' => $this->invoiceText($settings['bank_account_number'] ?? '', $invoice)],
                    ['label' => 'Branch', 'value' => $settings['bank_branch'] ?? ''],
                    ['label' => 'Swift Code', 'value' => $settings['bank_swift_code'] ?? ''],
                ],
            ];
        }

        if (($settings['cash_enabled'] ?? '0') === '1') {
            $methods[] = [
                'key' => 'cash',
                'title' => 'Cash Payment',
                'subtitle' => '',
                'rows' => [
                    ['label' => 'Instruction', 'value' => $settings['cash_instruction'] ?: 'Pay cash on day of move to our representative.'],
                ],
            ];
        }

        return collect($methods)
            ->map(function (array $method): array {
                $method['rows'] = collect($method['rows'])
                    ->filter(fn (array $row) => trim((string) ($row['value'] ?? '')) !== '')
                    ->values()
                    ->all();

                return $method;
            })
            ->filter(fn (array $method) => ! empty($method['rows']))
            ->values()
            ->all();
    }

    private function invoiceText(string $value, ?Invoice $invoice): string
    {
        if (! $invoice) {
            return $value;
        }

        return strtr($value, [
            '{invoice_number}' => (string) $invoice->invoice_number,
            '{customer_name}' => (string) $invoice->customer_name,
        ]);
    }
}
