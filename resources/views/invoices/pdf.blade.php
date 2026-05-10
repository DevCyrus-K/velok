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

  $company = $company ?? app(\App\Support\CompanyProfile::class)->data();
  $user = $user ?? auth()->user();
  $companyName = $companyName ?? (trim((string) ($company['name'] ?? '')) ?: 'Kwikshift Movers');
  $companyAddress = $companyAddress ?? collect([$company['address_line_1'] ?? null, $company['address_line_2'] ?? null])->map(fn ($line) => trim((string) $line))->filter()->implode(' ');
  $companyPhone = $companyPhone ?? trim((string) ($company['phone'] ?? ''));
  $companyEmail = $companyEmail ?? trim((string) ($company['email'] ?? ''));
  $companyAddressLine = trim(collect([$companyAddress, $companyPhone])->filter()->implode('<br>'));
  $logoDataUri = $logoDataUri ?? null;
  $signatureDataUri = $signatureDataUri ?? ($authorization['signature_url'] ?? null);
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

  $reference = $invoice->reference ?? $invoice->invoice_number;
  $invoiceDate = $invoice->created_at?->format('d M Y') ?? $invoice->invoice_date?->format('d M Y') ?? now()->format('d M Y');
  $customerAddress = $invoice->customer_address ?? trim(collect([$invoice->move_origin ?? null, $invoice->move_destination ?? null])->filter()->implode(' to '));
  $paymentMethods = collect($paymentMethods ?? [])->map(function ($method) {
      if (is_object($method) && isset($method->display_line)) {
          return $method;
      }

      if (is_object($method) && isset($method->display)) {
          return (object) ['display_line' => $method->display];
      }

      if (is_array($method)) {
          $rows = collect($method['rows'] ?? [])->map(function (array $row) use ($method) {
              $title = trim((string) ($method['title'] ?? 'Payment'));
              $label = trim((string) ($row['label'] ?? ''));
              $value = trim((string) ($row['value'] ?? ''));

              return trim($title.($label !== '' ? ' - '.$label : '').($value !== '' ? ': '.$value : ''));
          })->filter();

          return $rows->map(fn ($display) => (object) ['display_line' => $display]);
      }

      return null;
  })->flatten()->filter()->values();
  $paymentSummary = $paymentMethods->isNotEmpty()
      ? $paymentMethods->pluck('display_line')->implode(', ')
      : $invoice->paymentMethodLabel();
  $paymentTerms = trim((string) ($paymentTerms ?? $invoice->notes ?? ''));
  $paymentTerms = $paymentTerms !== '' ? $paymentTerms : 'All claims relating to quantity or shipping errors shall be waived unless made in writing within thirty (30) days after delivery of goods to the address stated.';
  $cancellation = trim((string) ($cancellation ?? ''));
  $liability = trim((string) ($liability ?? ''));
  $thankYou = trim((string) ($thankYou ?? $thankYouMessage ?? ''));
  $subtotal = (float) ($invoice->subtotal ?? 0);
  $discount = (float) ($invoice->discount ?? 0);
  $tax = (float) ($invoice->tax ?? 0);
  $total = (float) ($invoice->total ?? $invoice->total_amount ?? ($subtotal - $discount + $tax));
  $deposit = (float) ($invoice->deposit_amount ?? 0);
  $balance = (float) ($invoice->balance ?? max(0, $total - $deposit));
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
  <title>Invoice {{ $reference }}</title>
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
              <div class="tm_f50 tm_text_uppercase tm_white_color">Invoice</div>
            </div>
            <div class="tm_shape_bg tm_accent_bg tm_mobile_hide"></div>
          </div>
          <div class="tm_invoice_info tm_mb25">
            <div class="tm_card_note tm_mobile_hide"><b class="tm_primary_color">Payment Method: </b>{{ $paymentSummary }}</div>
            <div class="tm_invoice_info_list tm_white_color">
              <p class="tm_invoice_number tm_m0">Invoice No: <b>#{{ ltrim((string) $reference, '#') }}</b></p>
              <p class="tm_invoice_date tm_m0">Date: <b>{{ $invoiceDate }}</b></p>
            </div>
            <div class="tm_invoice_seperator tm_accent_bg"></div>
          </div>
          <div class="tm_invoice_head tm_mb10">
            <div class="tm_invoice_left">
              <p class="tm_mb2"><b class="tm_primary_color">Invoice To:</b></p>
              <p>
                {{ $invoice->customer_name }} <br>
                @if($customerAddress !== ''){{ $customerAddress }} <br>@endif
                @if($invoice->customer_phone){{ $invoice->customer_phone }} <br>@endif
                {{ $invoice->customer_email }}
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
                    @forelse($invoice->items as $index => $item)
                    <tr>
                      <td class="tm_width_3">{{ $index + 1 }}.</td>
                      <td class="tm_width_4">{{ $item->description }}</td>
                      <td class="tm_width_2">KES {{ number_format((float) $item->unit_price, 2) }}</td>
                      <td class="tm_width_1">{{ $item->quantity }}</td>
                      <td class="tm_width_2 tm_text_right">KES {{ number_format((float) ($item->amount ?? $item->total ?? 0), 2) }}</td>
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
                <p class="tm_m0">Due Date: {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</p>
                <p class="tm_m0">Status: {{ ucfirst((string) $invoice->status) }}</p>
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
                      <td class="tm_width_3 tm_primary_color">Deposit Paid</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right">-KES {{ number_format($deposit, 2) }}</td>
                    </tr>
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color">Balance Due</td>
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
            <div class="tm_invoice_footer tm_type1">
              <div class="tm_left_footer"></div>
              <div class="tm_right_footer">
                <div class="tm_sign tm_text_center">
                  @if($sigBase64)
                  <img src="data:{{ $sigMime }};base64,{{ $sigBase64 }}" alt="Sign" style="border:none !important;outline:none !important;box-shadow:none !important;background:transparent !important;padding:0 !important;">
                  @else
                  <p class="tm_m0 tm_ternary_color" style="font-style:italic">Signature not available</p>
                  @endif
                  <p class="tm_m0 tm_ternary_color">{{ $user?->name ?? $authorization['name'] ?? $companyName }}</p>
                  <p class="tm_m0 tm_f16 tm_primary_color">{{ $user?->job_title ?? $authorization['job_title'] ?? 'Authorized Signatory' }}</p>
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
