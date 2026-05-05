<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function create(QuoteRequest $quote)
    {
        // Check if quotation already exists
        $quotation = Quotation::where('quote_request_id', $quote->id)->first() ?? new Quotation();
        
        return view('quotations.create', compact('quote', 'quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_request_id' => 'required|exists:quote_requests,id',
            'quote_date' => 'required|date',
            'quote_valid_until' => 'required|date|after:quote_date',
            'moving_from' => 'required|string|max:255',
            'moving_to' => 'required|string|max:255',
            'move_date' => 'required|date',
            'quote_amount' => 'required|numeric|min:0',
            'deposit_percentage' => 'required|numeric|min:0|max:100',
            'cancellation_notice_hours' => 'required|integer|min:0',
            'services' => 'required|array|min:1',
            'services.*.name' => 'required|string|max:255',
            'services.*.description' => 'nullable|string',
            'additional_notes' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'authorized_by' => 'required|string|max:255',
            'authorized_role' => 'nullable|string|max:255',
            'approval_date' => 'required|date',
        ]);

        $quote = QuoteRequest::findOrFail($validated['quote_request_id']);

        // Format services array
        $services = [];
        if (isset($validated['services']) && is_array($validated['services'])) {
            foreach ($validated['services']['name'] as $index => $name) {
                if ($name) {
                    $services[] = [
                        'name' => $name,
                        'description' => $validated['services']['description'][$index] ?? '',
                    ];
                }
            }
        }

        // Create or update quotation
        $quotation = Quotation::updateOrCreate(
            ['quote_request_id' => $quote->id],
            [
                'quote_date' => $validated['quote_date'],
                'quote_valid_until' => $validated['quote_valid_until'],
                'moving_from' => $validated['moving_from'],
                'moving_to' => $validated['moving_to'],
                'move_date' => $validated['move_date'],
                'quote_amount' => $validated['quote_amount'],
                'deposit_percentage' => $validated['deposit_percentage'],
                'cancellation_notice_hours' => $validated['cancellation_notice_hours'],
                'services_included' => $services,
                'additional_notes' => $validated['additional_notes'],
                'payment_terms' => $validated['payment_terms'],
                'authorized_by' => $validated['authorized_by'],
                'authorized_role' => $validated['authorized_role'],
                'approval_date' => $validated['approval_date'],
                'status' => 'draft',
            ]
        );

        return redirect()->route('quotations.show', $quotation)
            ->with('toast-success', 'Quotation saved as draft successfully!');
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'quote_request_id' => 'required|exists:quote_requests,id',
            'quote_date' => 'required|date',
            'quote_valid_until' => 'required|date|after:quote_date',
            'moving_from' => 'required|string|max:255',
            'moving_to' => 'required|string|max:255',
            'move_date' => 'required|date',
            'quote_amount' => 'required|numeric|min:0',
            'deposit_percentage' => 'required|numeric|min:0|max:100',
            'cancellation_notice_hours' => 'required|integer|min:0',
            'services' => 'required|array|min:1',
            'services.*.name' => 'required|string|max:255',
            'services.*.description' => 'nullable|string',
            'additional_notes' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'authorized_by' => 'required|string|max:255',
            'authorized_role' => 'nullable|string|max:255',
            'approval_date' => 'required|date',
        ]);

        // Format services array
        $services = [];
        if (isset($validated['services']) && is_array($validated['services'])) {
            foreach ($validated['services']['name'] as $index => $name) {
                if ($name) {
                    $services[] = [
                        'name' => $name,
                        'description' => $validated['services']['description'][$index] ?? '',
                    ];
                }
            }
        }

        $quotation->update([
            'quote_date' => $validated['quote_date'],
            'quote_valid_until' => $validated['quote_valid_until'],
            'moving_from' => $validated['moving_from'],
            'moving_to' => $validated['moving_to'],
            'move_date' => $validated['move_date'],
            'quote_amount' => $validated['quote_amount'],
            'deposit_percentage' => $validated['deposit_percentage'],
            'cancellation_notice_hours' => $validated['cancellation_notice_hours'],
            'services_included' => $services,
            'additional_notes' => $validated['additional_notes'],
            'payment_terms' => $validated['payment_terms'],
            'authorized_by' => $validated['authorized_by'],
            'authorized_role' => $validated['authorized_role'],
            'approval_date' => $validated['approval_date'],
        ]);

        return redirect()->route('quotations.show', $quotation)
            ->with('toast-success', 'Quotation updated successfully!');
    }

    public function show(Quotation $quotation)
    {
        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $quote = $quotation->quoteRequest;
        return view('quotations.create', compact('quote', 'quotation'));
    }

    public function pdf(Quotation $quotation)
    {
        $pdf = Pdf::loadView('quotations.pdf', ['quotation' => $quotation]);
        return $pdf->download('Quotation_' . $quotation->quoteRequest->reference() . '.pdf');
    }

    public function send(Quotation $quotation)
    {
        try {
            // Update quotation status
            $quotation->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Send email to client with PDF attachment
            $this->sendQuotationEmail($quotation);

            // Update quote request status to quoted
            $quotation->quoteRequest->update(['status' => 'quoted']);

            return redirect()->route('quotations.show', $quotation)
                ->with('toast-success', 'Quotation sent to client successfully!');
        } catch (\Exception $e) {
            return redirect()->route('quotations.show', $quotation)
                ->with('toast-error', 'Failed to send quotation: ' . $e->getMessage());
        }
    }

    private function sendQuotationEmail(Quotation $quotation)
    {
        $client = $quotation->quoteRequest;
        
        // Generate PDF
        $pdf = Pdf::loadView('quotations.pdf', ['quotation' => $quotation]);

        // Send email with PDF attachment
        Mail::send('emails.quotation', ['quotation' => $quotation, 'client' => $client], function ($message) use ($client, $quotation, $pdf) {
            $message->to($client->email)
                ->subject('Your Professional Moving Quotation - ' . $client->reference())
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->attachData($pdf->output(), 'Quotation_' . $client->reference() . '.pdf', [
                    'mime' => 'application/pdf',
                ]);
        });
    }
}
