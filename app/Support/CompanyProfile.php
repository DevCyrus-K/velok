<?php

namespace App\Support;

use App\Models\AppSetting;

class CompanyProfile
{
    public const DEFAULT_THANK_YOU_TEMPLATE = 'Thank you for choosing {company_name}. We appreciate your business and look forward to serving you again. For any queries regarding this invoice, please contact us at {company_email} or {company_phone}.';

    public function data(): array
    {
        return AppSetting::groupValues('company', [
            'name' => '',
            'email' => '',
            'phone' => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'logo_path' => 'images/logo-dark.png',
            'website' => (string) config('company.website', ''),
            'business_registration_number' => (string) config('company.business_registration_number', ''),
            'authorized_representative_name' => (string) config('company.authorized_representative_name', ''),
            'authorized_representative_title' => (string) config('company.authorized_representative_title', ''),
            'liability_cap_amount' => (string) config('company.liability_cap_amount', ''),
        ]);
    }

    public function logoDataUri(): ?string
    {
        $path = public_path(ltrim((string) ($this->data()['logo_path'] ?? 'images/logo-dark.png'), '/'));

        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }

    public function thankYouMessage(): string
    {
        $company = $this->data();
        $template = AppSetting::value('invoice', 'thank_you_message', self::DEFAULT_THANK_YOU_TEMPLATE)
            ?: self::DEFAULT_THANK_YOU_TEMPLATE;

        return strtr((string) $template, [
            '{company_name}' => (string) ($company['name'] ?? ''),
            '{company_email}' => (string) ($company['email'] ?? ''),
            '{company_phone}' => (string) ($company['phone'] ?? ''),
        ]);
    }
}
