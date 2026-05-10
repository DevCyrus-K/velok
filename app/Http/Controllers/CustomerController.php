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
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function create(): View
    {
        return view('customers.create', [
            'statusOptions' => Customer::statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:'.implode(',', array_keys(Customer::statusOptions()))],
        ]);

        $email = Str::lower(trim($validated['email']));
        $phone = trim($validated['phone']);
        $contactKey = Customer::makeContactKey($email, $phone);

        if (Customer::query()->where('contact_key', $contactKey)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['phone' => 'A customer with this email and phone number already exists.']);
        }

        $customer = Customer::query()->create([
            'contact_key' => $contactKey,
            'source_quote_request_id' => null,
            'full_name' => $this->squish($validated['full_name']),
            'email' => $email,
            'phone' => $phone,
            'quotes_count' => 0,
            'approved_quotes_count' => 0,
            'declined_quotes_count' => 0,
            'status' => $validated['status'] ?? Customer::STATUS_LEAD,
            'first_seen_at' => now(),
            'last_quote_at' => null,
        ]);

        return redirect()
            ->route('customers.show', $customer)
            ->with('toast-success', 'Customer added successfully.');
    }

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

        if (! $updatedCustomer) {
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

    public function export(Request $request): StreamedResponse
    {
        app(CustomerSyncService::class)->sync();

        $customers = Customer::query()
            ->orderByDesc('last_quote_at')
            ->orderByDesc('id')
            ->get();

        $search = Str::lower(trim((string) $request->query('search', '')));
        $status = trim((string) $request->query('status', 'all'));
        $service = trim((string) $request->query('service', 'all'));
        $sort = trim((string) $request->query('sort', 'newest'));

        $customers = $customers
            ->filter(function (Customer $customer) use ($search, $status, $service): bool {
                $customerDate = $this->customerRawDate($customer);
                $matchesStatus = $status === 'all' || $customer->status === $status;
                $matchesService = $service === 'all' || Str::slug($customer->latestServiceLabel()) === $service;
                $haystack = Str::lower(implode(' ', [
                    $customer->full_name,
                    $customer->email,
                    $customer->phone,
                    $customer->latestServiceLabel(),
                    $customer->latestRouteSummary(),
                    $customer->statusLabel(),
                    $customerDate?->format('d M Y') ?? '',
                ]));

                return $matchesStatus
                    && $matchesService
                    && ($search === '' || str_contains($haystack, $search));
            });

        $customers = match ($sort) {
            'oldest' => $customers->sortBy(fn (Customer $customer) => $this->customerRawDate($customer)?->timestamp ?? 0),
            'customer' => $customers->sortBy(fn (Customer $customer) => Str::lower($customer->full_name)),
            default => $customers->sortByDesc(fn (Customer $customer) => $this->customerRawDate($customer)?->timestamp ?? 0),
        };

        $filename = 'customers-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Customer Name',
                'Customer Email',
                'Phone Number',
                'Latest Service',
                'Latest Route',
                'Status',
                'First Seen',
                'Last Quote',
                'Quotes Count',
                'Approved Quotes',
                'Declined Quotes',
            ]);

            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->full_name,
                    $customer->email,
                    $customer->phone,
                    $customer->latestServiceLabel(),
                    $customer->latestRouteSummary(),
                    $customer->statusLabel(),
                    $customer->first_seen_at?->format('Y-m-d') ?? '',
                    $customer->last_quote_at?->format('Y-m-d') ?? '',
                    $customer->quotes_count,
                    $customer->approved_quotes_count,
                    $customer->declined_quotes_count,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

        $message = 'Imported '.count($result['records']).' customer'.(count($result['records']) === 1 ? '' : 's').' successfully.';

        if ($result['skipped'] > 0) {
            $message .= ' Skipped '.$result['skipped'].' invalid row'.($result['skipped'] === 1 ? '' : 's').'.';
        }

        return redirect()
            ->route('any', 'customers')
            ->with('toast-success', $message);
    }

    private function customerDate(Customer $customer): string
    {
        return $this->customerRawDate($customer)?->format('d M Y')
            ?? 'Not available';
    }

    private function customerRawDate(Customer $customer): ?Carbon
    {
        return $customer->first_seen_at
            ?? $customer->last_quote_at
            ?? $customer->created_at;
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

            if ($fullName === '' || $email === '' || $phone === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
