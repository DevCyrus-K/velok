@php
  $stylesheet = (string) file_get_contents(base_path('Invoma-template/assets/css/style.css'));
  $stylesheet = (string) preg_replace('/@import\s+url\([^)]+\);\s*/', '', $stylesheet);
  $fontFace = '';
  $regularFont = '/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf';
  $boldFont = '/usr/share/fonts/truetype/noto/NotoSans-Bold.ttf';

  if (is_file($regularFont)) {
      $fontFace .= '@font-face{font-family:"Inter";src:url("file://'.$regularFont.'") format("truetype");font-weight:300;font-style:normal;}';
      $fontFace .= '@font-face{font-family:"Inter";src:url("file://'.$regularFont.'") format("truetype");font-weight:400;font-style:normal;}';
      $fontFace .= '@font-face{font-family:"Inter";src:url("file://'.$regularFont.'") format("truetype");font-weight:500;font-style:normal;}';
  }

  if (is_file($boldFont)) {
      $fontFace .= '@font-face{font-family:"Inter";src:url("file://'.$boldFont.'") format("truetype");font-weight:600;font-style:normal;}';
      $fontFace .= '@font-face{font-family:"Inter";src:url("file://'.$boldFont.'") format("truetype");font-weight:700;font-style:normal;}';
  }

  $quotation = $quotation ?? $quote;
  $quoteRequest = $quotation instanceof \App\Models\Quotation ? $quotation->quoteRequest : $quote;
  $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
  $user = $user ?? auth()->user();
  $companyName = $companyName ?? (trim((string) ($quotation->company_name ?? $company['name'] ?? '')) ?: 'Kwikshift Movers');
  $companyAddress = $companyAddress ?? collect([$company['address_line_1'] ?? null, $company['address_line_2'] ?? null])->map(fn ($line) => trim((string) $line))->filter()->implode(' ');
  $companyPhone = $companyPhone ?? trim((string) ($quotation->company_phone ?? $company['phone'] ?? ''));
  $companyEmail = $companyEmail ?? trim((string) ($quotation->company_email ?? $company['email'] ?? ''));
  $companyAddressLine = trim(collect([$companyAddress, $companyPhone])->filter()->implode('<br>'));
  $logoDataUri = $logoDataUri ?? null;
  $signatureDataUri = $signatureDataUri ?? null;
  $canRenderRaster = extension_loaded('gd');
  $imageCanRender = function (?string $dataUri) use ($canRenderRaster): bool {
      if (! is_string($dataUri) || trim($dataUri) === '') {
          return false;
      }

      return str_starts_with($dataUri, 'data:image/svg+xml') || $canRenderRaster;
  };
  $logoBase64 = $logoBase64 ?? null;
  $logoMime = $logoMime ?? 'image/png';
  $sigBase64 = $sigBase64 ?? null;
  $sigMime = $sigMime ?? 'image/png';

  if (! $logoBase64 && $imageCanRender($logoDataUri)) {
      [$logoMeta, $logoBody] = array_pad(explode(',', $logoDataUri, 2), 2, null);
      $logoBase64 = $logoBody;
      $logoMime = str($logoMeta)->between('data:', ';')->toString() ?: $logoMime;
  }

  if (! $sigBase64 && $imageCanRender($signatureDataUri)) {
      [$sigMeta, $sigBody] = array_pad(explode(',', $signatureDataUri, 2), 2, null);
      $sigBase64 = $sigBody;
      $sigMime = str($sigMeta)->between('data:', ';')->toString() ?: $sigMime;
  }

  $reference = $quotation->reference ?? (method_exists($quoteRequest, 'reference') ? $quoteRequest->reference() : '#QT'.$quotation->getKey());
  $quoteDate = $quotation->created_at?->format('d M Y') ?? $quotation->quote_date?->format('d M Y') ?? now()->format('d M Y');
  $validUntil = $quotation->valid_until?->format('d M Y') ?? $quotation->quote_valid_until?->format('d M Y') ?? $quotation->created_at?->copy()->addDays(7)->format('d M Y') ?? now()->addDays(7)->format('d M Y');
  $customerName = $quotation->customer_name ?? $quoteRequest?->full_name;
  $customerEmail = $quotation->customer_email ?? $quoteRequest?->email;
  $customerPhone = $quotation->customer_phone ?? $quoteRequest?->phone;
  $customerAddress = trim(collect([$quotation->pickup_location ?? $quoteRequest?->moving_from, $quotation->dropoff_location ?? $quoteRequest?->moving_to])->filter()->implode(' to '));
  $paymentMethods = collect($paymentMethods ?? [])->map(function ($method) {
      if (is_object($method) && isset($method->display_line)) {
          return $method;
      }

      if (is_object($method) && isset($method->display)) {
          return (object) ['display_line' => $method->display];
      }

      return null;
  })->filter()->values();
  $paymentSummary = $paymentMethods->isNotEmpty() ? $paymentMethods->pluck('display_line')->implode(', ') : 'To be agreed';
  $lineItems = collect($quotation->services_included ?: [['name' => 'Moving Service', 'description' => $quoteRequest?->serviceTypeLabel()]]);
  $lineCount = max(1, $lineItems->count());
  $subtotal = (float) ($quotation->subtotal ?? $quotation->quote_amount ?? 0);
  $discount = (float) ($quotation->discount ?? 0);
  $tax = (float) ($quotation->tax ?? 0);
  $total = (float) ($quotation->total ?? $quotation->quote_amount ?? ($subtotal - $discount + $tax));
  $baseAmount = $lineCount > 0 ? round($subtotal / $lineCount, 2) : $subtotal;
  $deposit = (float) ($quotation->deposit_amount ?? $quotation->depositAmount());
  $depositPercentage = (float) ($quotation->deposit_percentage ?? 30);
  $balance = (float) ($quotation->balance ?? $quotation->balanceDue());
  $paymentTerms = trim((string) ($paymentTerms ?? $quotation->payment_terms ?? ''));
  $paymentTerms = $paymentTerms !== '' ? $paymentTerms : 'Deposit confirms booking; balance is due on move day.';
  $cancellation = trim((string) ($cancellation ?? $quotation->cancellationPolicyText()));
  $liability = trim((string) ($liability ?? ''));
  $thankYou = trim((string) ($thankYou ?? $thankYouMessage ?? ''));
@endphp
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
  <!-- Meta Tags -->
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="Laralink">
  <!-- Site Title -->
  <title>Quotation {{ $reference }}</title>
  <style>{!! $fontFace !!}{!! $stylesheet !!}</style>
</head>

<body>
  <div class="tm_container">
    <div class="tm_invoice_wrap">
      <div class="tm_invoice tm_style1 tm_type1" id="tm_download_section">
        <div class="tm_invoice_in">
          <div class="tm_invoice_head tm_top_head tm_mb15 tm_align_center">
            <div class="tm_invoice_left">
              <div class="tm_logo">@if($logoBase64)<img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" alt="Logo">@endif</div>
            </div>
            <div class="tm_invoice_right tm_text_right tm_mobile_hide">
              <div class="tm_f50 tm_text_uppercase tm_white_color">Quotation</div>
            </div>
            <div class="tm_shape_bg tm_accent_bg tm_mobile_hide"></div>
          </div>
          <div class="tm_invoice_info tm_mb25">
            <div class="tm_card_note tm_mobile_hide"><b class="tm_primary_color">Payment Method: </b>{{ $paymentSummary }}</div>
            <div class="tm_invoice_info_list tm_white_color">
              <p class="tm_invoice_number tm_m0">Invoice No: <b>#{{ ltrim((string) $reference, '#') }}</b></p>
              <p class="tm_invoice_date tm_m0">Date: <b>{{ $quoteDate }}</b></p>
            </div>
            <div class="tm_invoice_seperator tm_accent_bg"></div>
          </div>
          <div class="tm_invoice_head tm_mb10">
            <div class="tm_invoice_left">
              <p class="tm_mb2"><b class="tm_primary_color">Invoice To:</b></p>
              <p>
                {{ $customerName }} <br>
                @if($customerAddress !== ''){{ $customerAddress }} <br>@endif
                @if($customerPhone){{ $customerPhone }} <br>@endif
                {{ $customerEmail }}
              </p>
            </div>
            <div class="tm_invoice_right tm_text_right">
              <p class="tm_mb2"><b class="tm_primary_color">Pay To:</b></p>
              <p>
                {{ $companyName }} <br>
                @if($companyAddressLine !== ''){!! $companyAddressLine !!}<br>@endif
                {{ $companyEmail }}
              </p>
            </div>
          </div>
          <div class="tm_table tm_style1">
            <div class="">
              <div class="tm_table_responsive">
                <table>
                  <thead>
                    <tr class="tm_accent_bg">
                      <th class="tm_width_3 tm_semi_bold tm_white_color">Item</th>
                      <th class="tm_width_4 tm_semi_bold tm_white_color">Description</th>
                      <th class="tm_width_2 tm_semi_bold tm_white_color">Price</th>
                      <th class="tm_width_1 tm_semi_bold tm_white_color">Qty</th>
                      <th class="tm_width_2 tm_semi_bold tm_white_color tm_text_right">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($lineItems as $index => $item)
                    @php
                      $amount = $loop->last ? $subtotal - ($baseAmount * max(0, $lineCount - 1)) : $baseAmount;
                      $description = trim(collect([$item['name'] ?? 'Moving Service', $item['description'] ?? null])->filter()->implode(' - '));
                    @endphp
                    <tr>
                      <td class="tm_width_3">{{ $index + 1 }}.</td>
                      <td class="tm_width_4">{{ $description }}</td>
                      <td class="tm_width_2">KES {{ number_format((float) $amount, 2) }}</td>
                      <td class="tm_width_1">1</td>
                      <td class="tm_width_2 tm_text_right">KES {{ number_format((float) $amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="5" style="text-align:center;padding:16px">No items</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            <div class="tm_invoice_footer tm_border_top tm_mb15 tm_m0_md">
              <div class="tm_left_footer">
                <p class="tm_mb2"><b class="tm_primary_color">Payment info:</b></p>
                @forelse($paymentMethods as $method)
                <p class="tm_m0">{{ $method->display_line }}</p>
                @empty
                <p class="tm_m0">No payment methods configured.</p>
                @endforelse
                <p class="tm_m0">Valid Until: {{ $validUntil }}</p>
                <p class="tm_m0">Status: {{ ucfirst((string) $quotation->status) }}</p>
              </div>
              <div class="tm_right_footer">
                <table class="tm_mb15">
                  <tbody>
                    <tr class="tm_gray_bg ">
                      <td class="tm_width_3 tm_primary_color tm_bold">Subtoal</td>
                      <td class="tm_width_3 tm_primary_color tm_bold tm_text_right">KES {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($discount > 0)
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color">Discount</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right">-KES {{ number_format($discount, 2) }}</td>
                    </tr>
                    @endif
                    @if($tax > 0)
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color">Tax</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right">KES {{ number_format($tax, 2) }}</td>
                    </tr>
                    @endif
                    @if($deposit > 0)
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color">Deposit Required ({{ number_format($depositPercentage, 0) }}%)</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right">KES {{ number_format($deposit, 2) }}</td>
                    </tr>
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color">Balance Due on Move Day</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right">KES {{ number_format($balance, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="tm_accent_bg">
                      <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_white_color">Grand Total	</td>
                      <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_white_color tm_text_right">KES {{ number_format($total, 2) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            @if($deposit > 0)
            <div style="border: 2px solid #007aff;border-radius: 6px;padding: 14px 16px;margin: 16px 0;background: rgba(0, 122, 255, 0.1);">
              <p style="font-family: &quot;Inter&quot;, sans-serif;font-size: 11px;font-weight: 700;color: #007aff;margin: 0 0 6px 0;text-transform: uppercase;letter-spacing: 0.5px;">DEPOSIT REQUIRED TO CONFIRM BOOKING</p>
              <p style="font-size: 20px;font-weight: 700;color: #007aff;margin: 0 0 10px 0;">KES {{ number_format($deposit, 2) }}</p>
              @foreach($paymentMethods as $method)
              <p style="font-size: 10px;font-family: &quot;Inter&quot;, sans-serif;color: #666;margin: 3px 0;">{{ $method->display_line }}</p>
              @endforeach
              <p style="font-size: 9px;color: #007aff;margin: 8px 0 0 0;font-style: italic;">Your booking is NOT confirmed until the deposit has been received and verified.</p>
            </div>
            @endif
            @if(isset($approvalUrl) && $approvalUrl)
            <div style="border: 2px solid #34c759;border-radius: 6px;padding: 14px 16px;margin: 16px 0;background: rgba(52, 199, 89, 0.1);">
              <p style="font-size: 11px;font-weight: 700;color: #34c759;margin: 0 0 6px 0;text-transform: uppercase;letter-spacing: 0.5px;">APPROVE THIS QUOTATION</p>
              <p style="font-size: 10px;color: #666;margin: 0 0 10px 0;">Scan the QR code or visit the link below in your browser to approve:</p>
              <table style="width:100%;border-collapse:collapse;border:none">
                <tr>
                  <td style="width:90px;vertical-align:middle;border:none;padding:0">{!! QrCode::size(85)->generate($approvalUrl) !!}</td>
                  <td style="vertical-align:middle;padding-left:12px;border:none">
                    <a href="{{ $approvalUrl }}" style="font-size: 9px;color: #34c759;word-break: break-all;text-decoration: none;display: block;margin-bottom: 6px;">{{ $approvalUrl }}</a>
                    @if($quotation->approval_token_expires_at)
                    <p style="font-size: 9px;color: #b5b5b5;margin: 0 0 4px 0;">Link expires: {{ $quotation->approval_token_expires_at->format('d M Y') }}</p>
                    @endif
                    <p style="font-size: 9px;color: #b5b5b5;margin: 0;">By approving you agree to all terms stated in this quotation.</p>
                  </td>
                </tr>
              </table>
            </div>
            @endif
            <div class="tm_invoice_footer tm_type1">
              <div class="tm_left_footer"></div>
              <div class="tm_right_footer">
                <div class="tm_sign tm_text_center">
                  @if($sigBase64)
                  <img src="data:{{ $sigMime }};base64,{{ $sigBase64 }}" alt="Sign" style="border:none !important;outline:none !important;box-shadow:none !important;background:transparent !important;padding:0 !important;">
                  @else
                  <p class="tm_m0 tm_ternary_color" style="font-style:italic">Signature not available</p>
                  @endif
                  <p class="tm_m0 tm_ternary_color">{{ $user?->name ?? $authorization['name'] ?? $quotation->authorized_by ?? $companyName }}</p>
                  <p class="tm_m0 tm_f16 tm_primary_color">{{ $user?->job_title ?? $authorization['job_title'] ?? $quotation->authorized_role ?? 'Authorized Signatory' }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="tm_note tm_text_center tm_font_style_normal">
            <hr class="tm_mb15">
            <p class="tm_mb2"><b class="tm_primary_color">Terms & Conditions:</b></p>
            <p class="tm_m0">{{ $paymentTerms }}@if($cancellation !== '')<br>{{ $cancellation }}@endif @if($liability !== '')<br>{{ $liability }}@endif @if($thankYou !== '')<br>{{ $thankYou }}@endif</p>
          </div><!-- .tm_note -->
        </div>
      </div>
    </div>
  </div>
</body>
</html>
