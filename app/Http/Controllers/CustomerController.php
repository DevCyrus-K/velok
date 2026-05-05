<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Services\CustomerSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function show(Customer $customer): View
    {
        return view('customers.show', [
            'customer' => $customer,
            'customerDate' => $this->customerDate($customer),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', [
            'customer' => $customer,
            'customerDate' => $this->customerDate($customer),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $payload = [
            'full_name' => $this->squish($validated['full_name']),
            'email' => Str::lower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'contact_key' => Customer::makeContactKey(
                Str::lower(trim($validated['email'])),
                trim($validated['phone'])
            ),
        ];

        $originalEmail = $customer->email;
        $originalPhone = $customer->phone;

        DB::transaction(function () use ($customer, $payload, $originalEmail, $originalPhone) {
            $customer->update($payload);

            if (Schema::hasTable('quote_requests')) {
                QuoteRequest::query()
                    ->where('email', $originalEmail)
                    ->where('phone', $originalPhone)
                    ->update([
                        'full_name' => $payload['full_name'],
                        'email' => $payload['email'],
                        'phone' => $payload['phone'],
                    ]);
            }
        });

        app(CustomerSyncService::class)->sync();

        $updatedCustomer = Customer::query()
            ->where('email', $payload['email'])
            ->where('phone', $payload['phone'])
            ->latest('last_quote_at')
            ->latest('id')
            ->first();

        if (!$updatedCustomer) {
            return redirect()
                ->route('any', 'customers')
                ->with('toast-success', 'Customer updated successfully.');
        }

        return redirect()
            ->route('customers.show', $updatedCustomer)
            ->with('toast-success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $email = $customer->email;
        $phone = $customer->phone;

        DB::transaction(function () use ($customer, $email, $phone) {
            if (Schema::hasTable('quote_requests')) {
                QuoteRequest::query()
                    ->where('email', $email)
                    ->where('phone', $phone)
                    ->delete();
            }

            $customer->delete();
        });

        app(CustomerSyncService::class)->sync();

        return redirect()
            ->route('any', 'customers')
            ->with('toast-success', 'Customer deleted successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customers_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $this->parseCustomerImport($validated['customers_file']);

        if ($result['records'] === []) {
            return redirect()
                ->route('any', 'customers')
                ->with('customer-import-error', $result['message'] ?? 'No valid customers were found in the uploaded CSV.');
        }

        DB::transaction(function () use ($result) {
            Customer::query()->upsert(
                $result['records'],
                ['contact_key'],
                [
                    'full_name',
                    'email',
                    'phone',
                    'status',
                    'first_seen_at',
                    'last_quote_at',
                    'updated_at',
                ]
            );

            if (Schema::hasTable('quote_requests')) {
                foreach ($result['records'] as $record) {
                    QuoteRequest::query()
                        ->where('email', $record['email'])
                        ->where('phone', $record['phone'])
                        ->update([
                            'full_name' => $record['full_name'],
                            'email' => $record['email'],
                            'phone' => $record['phone'],
                        ]);
                }
            }
        });

        app(CustomerSyncService::class)->sync();

        $message = 'Imported ' . count($result['records']) . ' customer' . (count($result['records']) === 1 ? '' : 's') . ' successfully.';

        if ($result['skipped'] > 0) {
            $message .= ' Skipped ' . $result['skipped'] . ' invalid row' . ($result['skipped'] === 1 ? '' : 's') . '.';
        }

        return redirect()
            ->route('any', 'customers')
            ->with('toast-success', $message);
    }

    private function customerDate(Customer $customer): string
    {
        return $customer->first_seen_at?->format('d M Y')
            ?? $customer->last_quote_at?->format('d M Y')
            ?? $customer->created_at?->format('d M Y')
            ?? 'Not available';
    }

    private function squish(string $value): string
    {
        return (string) Str::of($value)->squish();
    }

    private function parseCustomerImport(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [
                'records' => [],
                'skipped' => 0,
                'message' => 'The uploaded CSV could not be read.',
            ];
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false) {
            fclose($handle);

            return [
                'records' => [],
                'skipped' => 0,
                'message' => 'The uploaded CSV is empty.',
            ];
        }

        $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $headerRow);
        $records = [];
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $data = $this->combineCsvRow($headers, $row);

            $fullName = $this->squish((string) $this->csvValue($data, ['full_name', 'customer_name', 'name']));
            $email = Str::lower(trim((string) $this->csvValue($data, ['email', 'customer_email'])));
            $phone = trim((string) $this->csvValue($data, ['phone', 'phone_number', 'mobile', 'telephone']));

            if ($fullName === '' || $email === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $contactKey = Customer::makeContactKey($email, $phone);
            $date = $this->parseImportedDate((string) $this->csvValue($data, ['date', 'first_seen_at', 'last_quote_at', 'created_at']));
            $status = Customer::normalizeImportedStatus((string) $this->csvValue($data, ['status']), $date);

            $records[$contactKey] = [
                'contact_key' => $contactKey,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'status' => $status,
                'first_seen_at' => $date,
                'last_quote_at' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        fclose($handle);

        return [
            'records' => array_values($records),
            'skipped' => $skipped,
            'message' => null,
        ];
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim($header))) ?? '';

        return trim($header, '_');
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function combineCsvRow(array $headers, array $row): array
    {
        $values = array_slice(array_pad($row, count($headers), null), 0, count($headers));

        return array_combine($headers, $values) ?: [];
    }

    private function csvValue(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && trim((string) $data[$key]) !== '') {
                return (string) $data[$key];
            }
        }

        return null;
    }

    private function parseImportedDate(string $value): Carbon
    {
        if (trim($value) === '') {
            return now();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return now();
        }
    }

}
