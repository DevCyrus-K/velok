@php
  if (! isset($logoBase64) && isset($logoDataUri) && is_string($logoDataUri) && str_starts_with($logoDataUri, 'data:')) {
    [$logoMeta, $logoPayload] = array_pad(explode(',', $logoDataUri, 2), 2, null);
    $logoBase64 = $logoPayload;
    $logoMime = str_replace(['data:', ';base64'], '', (string) $logoMeta) ?: 'image/png';
  }

  if (! isset($sigBase64) && isset($signatureDataUri) && is_string($signatureDataUri) && str_starts_with($signatureDataUri, 'data:')) {
    [$sigMeta, $sigPayload] = array_pad(explode(',', $signatureDataUri, 2), 2, null);
    $sigBase64 = $sigPayload;
    $sigMime = str_replace(['data:', ';base64'], '', (string) $sigMeta) ?: 'image/png';
  }

  $company = $company ?? [];
  $companyName = $companyName ?? (trim((string) ($company['name'] ?? '')) ?: config('app.name'));
  $companyAddress = $companyAddress ?? collect([$company['address_line_1'] ?? null, $company['address_line_2'] ?? null])->map(fn ($line) => trim((string) $line))->filter()->implode(', ');
  $companyPhone = $companyPhone ?? trim((string) ($company['phone'] ?? ''));
  $companyEmail = $companyEmail ?? trim((string) ($company['email'] ?? ''));
  $logoBase64 = $logoBase64 ?? null;
  $logoMime = $logoMime ?? 'image/png';
  $sigBase64 = $sigBase64 ?? null;
  $sigMime = $sigMime ?? 'image/png';
  $thankYou = $thankYou ?? $thankYouMessage ?? '';
  $paymentTerms = $paymentTerms ?? trim((string) ($invoice->notes ?? ''));
  $cancellation = $cancellation ?? trim((string) ($invoice->quoteRequest?->quotation?->cancellation_policy ?? ''));
  $liability = $liability ?? '';
  $authorizedName = $user?->name ?? ($authorization['name'] ?? 'Authorized Signatory');
  $authorizedJobTitle = $user?->job_title ?? ($authorization['job_title'] ?? 'Authorized Signatory');
  $authorizationDate = $authorization['date_label'] ?? now()->format('d M Y');
  $documentTitle = app(\App\Support\PdfDocumentName::class)->invoiceTitle($invoice);
  $paymentMethods = collect($paymentMethods ?? [])
    ->flatMap(function ($method): array {
      if (is_object($method)) {
        $line = trim((string) ($method->display_line ?? $method->display ?? ''));

        return $line !== '' ? [(object) ['display_line' => $line]] : [];
      }

      if (is_string($method)) {
        $line = trim($method);

        return $line !== '' ? [(object) ['display_line' => $line]] : [];
      }

      if (is_array($method)) {
        $directLine = trim((string) ($method['display_line'] ?? $method['display'] ?? ''));

        if ($directLine !== '') {
          return [(object) ['display_line' => $directLine]];
        }

        $title = trim((string) ($method['title'] ?? 'Payment'));

        return collect($method['rows'] ?? [])
          ->map(function (array $row) use ($title): object {
            $label = trim((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            $displayLine = trim($title.($label !== '' ? ' - '.$label : '').($value !== '' ? ': '.$value : ''));

            return (object) ['display_line' => $displayLine];
          })
          ->filter(fn (object $row): bool => $row->display_line !== '')
          ->values()
          ->all();
      }

      return [];
    })
    ->values();
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
  <title>{{ $documentTitle }}</title>
  <style>
    *,
    ::after,
    ::before {
      -webkit-box-sizing: border-box;
              box-sizing: border-box;
    }

    html {
      line-height: 1.15;
      -webkit-text-size-adjust: 100%;
    }

    body {
      margin: 0;
    }

    body,
    html {
      color: #04223E;
      font-family: "Inter", sans-serif;
      font-size: 14px;
      font-weight: 400;
      line-height: 1.6em;
      overflow-x: hidden;
      background-color: #ffffff;
    }

    p,
    div {
      margin-top: 0;
      line-height: 1.5em;
    }

    p {
      margin-bottom: 15px;
    }

    ul {
      margin: 0 0 25px 0;
      padding-left: 15px;
      list-style: disc;
    }

    img {
      border: 0;
      max-width: 100%;
      height: auto;
      vertical-align: middle;
    }

    table {
      width: 100%;
      caption-side: bottom;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      padding: 10px 15px;
      line-height: 1.55em;
    }

    td {
      border-top: 1px solid #e6edf5;
      color: #04223E;
      padding: 10px 15px;
      line-height: 1.55em;
    }

    b,
    strong {
      font-weight: bold;
    }

    .tm_f16 {
      font-size: 16px;
    }

    .tm_f50 {
      font-size: 50px;
    }

    .tm_semi_bold {
      font-weight: 600;
    }

    .tm_bold {
      font-weight: 700;
    }

    .tm_m0 {
      margin: 0px;
    }

    .tm_mb2 {
      margin-bottom: 2px;
    }

    .tm_mb5 {
      margin-bottom: 5px;
    }

    .tm_mb10 {
      margin-bottom: 10px;
    }

    .tm_mb20 {
      margin-bottom: 20px;
    }

    .tm_mb30 {
      margin-bottom: 30px;
    }

    .tm_pt0 {
      padding-top: 0;
    }

    .tm_width_1 {
      width: 8.33333333%;
    }

    .tm_width_2 {
      width: 16.66666667%;
    }

    .tm_width_3 {
      width: 25%;
    }

    .tm_width_4 {
      width: 33.33333333%;
    }

    .tm_border_top {
      border-top: 1px solid #e6edf5;
    }

    .tm_border_bottom {
      border-bottom: 1px solid #e6edf5;
    }

    .tm_round_border {
      border: 1px solid #e6edf5;
      overflow: hidden;
      border-radius: 6px;
    }

    .tm_primary_color {
      color: #04223E;
    }

    .tm_secondary_color {
      color: #04223E;
    }

    .tm_ternary_color {
      color: #04223E;
    }

    .tm_gray_bg {
      background: #fff5f5;
    }

    .tm_invoice_in {
      position: relative;
      z-index: 100;
    }

    .tm_container {
      max-width: 880px;
      padding: 30px 15px;
      margin-left: auto;
      margin-right: auto;
      position: relative;
    }

    .tm_text_uppercase {
      text-transform: uppercase;
    }

    .tm_text_right {
      text-align: right;
    }

    .tm_align_center {
      -webkit-box-align: center;
          -ms-flex-align: center;
              align-items: center;
    }

    .tm_border_top_0 {
      border-top: 0;
    }

    .tm_border_none {
      border: none !important;
    }

    .tm_table_responsive {
      overflow-x: auto;
    }

    .tm_table_responsive > table {
      min-width: 600px;
    }

    .tm_invoice {
      background: #ffffff;
      border: 1px solid #e6edf5;
      border-radius: 8px;
      padding: 50px;
    }

    .tm_invoice_footer {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
    }

    .tm_invoice_footer table {
      margin-top: -1px;
    }

    .tm_invoice_footer .tm_left_footer {
      width: 58%;
      padding: 10px 15px;
      -webkit-box-flex: 0;
          -ms-flex: none;
              flex: none;
    }

    .tm_invoice_footer .tm_right_footer {
      width: 42%;
    }

    .tm_sign img {
      max-height: 45px;
    }

    .tm_invoice.tm_style1 .tm_invoice_right {
      -webkit-box-flex: 0;
          -ms-flex: none;
              flex: none;
      width: 60%;
    }

    .tm_invoice.tm_style1 .tm_invoice_head {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      -webkit-box-pack: justify;
          -ms-flex-pack: justify;
              justify-content: space-between;
    }

    .tm_invoice.tm_style1 .tm_invoice_head .tm_invoice_right div {
      line-height: 1em;
    }

    .tm_invoice.tm_style1 .tm_invoice_info {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      -webkit-box-align: center;
          -ms-flex-align: center;
              align-items: center;
      -webkit-box-pack: justify;
          -ms-flex-pack: justify;
              justify-content: space-between;
    }

    .tm_invoice.tm_style1 .tm_invoice_seperator {
      min-height: 8px;
      background: #DF1119;
      border-radius: 1.6em;
      -webkit-box-flex: 1;
          -ms-flex: 1;
              flex: 1;
      margin-right: 20px;
    }

    .tm_invoice.tm_style1 .tm_invoice_info_list {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
    }

    .tm_invoice.tm_style1 .tm_invoice_info_list > *:not(:last-child) {
      margin-right: 20px;
    }

    .tm_invoice.tm_style1 .tm_logo img {
      max-height: 50px;
    }

    .tm_invoice_wrap {
      position: relative;
    }

    .tm_note_list li:not(:last-child) {
      margin-bottom: 5px;
    }

    .tm_padd_15_20 {
      padding: 15px 20px;
    }

    .tm_invoice_body {
      background-color: #ffffff;
    }

    .tm_brand_invoice {
      background: #ffffff;
      color: #04223E;
    }

    .tm_brand_invoice .tm_primary_color {
      color: #04223E;
    }

    .tm_brand_invoice .tm_secondary_color {
      color: #04223E;
    }

    .tm_brand_invoice .tm_ternary_color {
      color: #04223E;
    }

    .tm_brand_invoice .tm_gray_bg {
      background: #fff5f5;
    }

    .tm_brand_invoice .tm_border_color,
    .tm_brand_invoice .tm_round_border,
    .tm_brand_invoice td,
    .tm_brand_invoice th,
    .tm_brand_invoice .tm_border_top,
    .tm_brand_invoice .tm_border_bottom {
      border-color: #e6edf5;
    }

    .tm_document_title {
      color: #04223E;
      font-weight: 700;
      letter-spacing: 0;
    }

    .tm_accent_rule {
      width: 82px;
      height: 4px;
      margin-top: 8px;
      margin-left: auto;
      border-radius: 6px;
      background: #DF1119;
    }

    .tm_brand_invoice th {
      border-top: 2px solid #DF1119;
      color: #04223E;
    }

    .tm_total_row td {
      border-top: 2px solid #DF1119;
    }
  </style>
</head>

<body class="tm_invoice_body">
  <div class="tm_container">
    <div class="tm_invoice_wrap">
      <div class="tm_invoice tm_style1 tm_brand_invoice" id="tm_download_section">
        <div class="tm_invoice_in">
          <div class="tm_invoice_head tm_align_center tm_mb20">
            <div class="tm_invoice_left">
              <div class="tm_logo">
                @if($logoBase64)
                  <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" alt="Logo" style="max-height: 50px;">
                @endif
              </div>
            </div>
            <div class="tm_invoice_right tm_text_right">
              <div class="tm_document_title tm_f50 tm_text_uppercase">INVOICE</div>
              <div class="tm_accent_rule"></div>
            </div>
          </div>
          <div class="tm_invoice_info tm_mb20">
            <div class="tm_invoice_seperator tm_gray_bg"></div>
            <div class="tm_invoice_info_list">
              <p class="tm_invoice_number tm_m0">Invoice No: <b class="tm_primary_color">{{ $invoice->reference }}</b></p>
              <p class="tm_invoice_date tm_m0">Date: <b class="tm_primary_color">{{ $invoice->created_at->format('d M Y') }}</b></p>
              <p class="tm_invoice_date tm_m0">Due Date: <b class="tm_primary_color">{{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</b></p>
              <p class="tm_invoice_date tm_m0">Status: <b class="tm_primary_color">{{ ucfirst($invoice->status) }}</b></p>
            </div>
          </div>
          <div class="tm_invoice_head tm_mb10">
            <div class="tm_invoice_left">
              <p class="tm_mb2"><b class="tm_primary_color">Invoice To:</b></p>
              <p>
                {{ $invoice->customer_name }} <br>
                {{ $invoice->customer_address ?? '' }} <br>
                {{ $invoice->customer_email }} <br>
                {{ $invoice->customer_phone }}
              </p>
              <p class="tm_mb2"><b class="tm_primary_color">Move Details:</b></p>
              <p>
                From: {{ $invoice->pickup_location ?? '' }} <br>
                To: {{ $invoice->dropoff_location ?? '' }} <br>
                Move Date: {{ $invoice->move_date?->format('d M Y') ?? '' }} <br>
                Service Type: {{ $invoice->service_type ?? $invoice->move_size ?? '' }}
              </p>
            </div>
            <div class="tm_invoice_right tm_text_right">
              <p class="tm_mb2"><b class="tm_primary_color">Pay To:</b></p>
              <p>
                {{ $companyName }} <br>
                {{ $companyAddress }}<br>
                {{ $companyPhone }} <br>
                {{ $companyEmail }}
              </p>
            </div>
          </div>
          <div class="tm_table tm_style1 tm_mb30">
            <div class="tm_round_border">
              <div class="tm_table_responsive">
                <table>
                  <thead>
                    <tr>
                      <th class="tm_width_3 tm_semi_bold tm_primary_color tm_gray_bg">Item</th>
                      <th class="tm_width_4 tm_semi_bold tm_primary_color tm_gray_bg">Description</th>
                      <th class="tm_width_2 tm_semi_bold tm_primary_color tm_gray_bg">Price</th>
                      <th class="tm_width_1 tm_semi_bold tm_primary_color tm_gray_bg">Qty</th>
                      <th class="tm_width_2 tm_semi_bold tm_primary_color tm_gray_bg tm_text_right">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($invoice->items as $index => $item)
                    <tr>
                      <td class="tm_width_3">{{ $index + 1 }}</td>
                      <td class="tm_width_4">{{ $item->description }}</td>
                      <td class="tm_width_2">KES {{ number_format((float) $item->unit_price, 2) }}</td>
                      <td class="tm_width_1">{{ $item->quantity }}</td>
                      <td class="tm_width_2 tm_text_right">KES {{ number_format((float) $item->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="5" style="text-align:center; padding:16px">No items found</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            <div class="tm_invoice_footer">
              <div class="tm_left_footer">
                <p class="tm_mb2"><b class="tm_primary_color">Payment info:</b></p>
                <p class="tm_m0">
                  @forelse($paymentMethods as $method)
                    {{ $method->display_line }}@if(! $loop->last)<br>@endif
                  @empty
                    No payment methods configured.
                  @endforelse
                </p>
              </div>
              <div class="tm_right_footer">
                <table>
                  <tbody>
                    <tr>
                      <td class="tm_width_3 tm_primary_color tm_border_none tm_bold">Subtotal</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_bold">KES {{ number_format((float) $invoice->subtotal, 2) }}</td>
                    </tr>
                    @if(isset($invoice->discount) && $invoice->discount > 0)
                    <tr>
                      <td class="tm_width_3 tm_primary_color tm_border_none tm_pt0">Discount</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_pt0">-KES {{ number_format((float) $invoice->discount, 2) }}</td>
                    </tr>
                    @endif
                    @if(isset($invoice->tax) && $invoice->tax > 0)
                    <tr>
                      <td class="tm_width_3 tm_primary_color tm_border_none tm_pt0">Tax</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_pt0">KES {{ number_format((float) $invoice->tax, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="tm_border_top tm_border_bottom tm_total_row">
                      <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_primary_color">Grand Total</td>
                      <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_primary_color tm_text_right">KES {{ number_format((float) $invoice->total, 2) }}</td>
                    </tr>
                    @if($invoice->deposit_amount > 0)
                    <tr>
                      <td class="tm_width_3 tm_primary_color tm_border_none tm_pt0">Deposit Paid</td>
                      <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_pt0">-KES {{ number_format((float) $invoice->deposit_amount, 2) }}</td>
                    </tr>
                    <tr class="tm_border_top tm_border_bottom tm_total_row">
                      <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_primary_color">Balance Due</td>
                      <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_primary_color tm_text_right">KES {{ number_format((float) $invoice->balance, 2) }}</td>
                    </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="tm_padd_15_20 tm_round_border">
            <p class="tm_mb5"><b class="tm_primary_color">Terms & Conditions:</b></p>
            <ul class="tm_m0 tm_note_list">
              @if($paymentTerms)
                <li>{{ $paymentTerms }}</li>
              @endif
              @if($cancellation)
                <li>{{ $cancellation }}</li>
              @endif
              @if($liability)
                <li>{{ $liability }}</li>
              @endif
              <li>{{ $thankYou }}</li>
            </ul>
            <div class="tm_border_top" style="margin-top: 15px; padding-top: 15px;">
              <p class="tm_mb5"><b class="tm_primary_color">Authorization:</b></p>
              <table>
                <tbody>
                  <tr>
                    <td class="tm_border_none" style="padding: 0; width: 45%; vertical-align: top;">
                      <p class="tm_mb2"><b class="tm_primary_color">Signature:</b></p>
                      <div class="tm_sign">
                        @if(isset($sigBase64) && $sigBase64)
                          {{-- Signature image rendered as base64 --}}
                          <img
                            src="data:{{ $sigMime ?? 'image/png' }};base64,{{ $sigBase64 }}"
                            style="
                              max-height: 60px;
                              max-width: 200px;
                              height: auto;
                              width: auto;
                              display: block;
                              border: none !important;
                              outline: none !important;
                              box-shadow: none !important;
                              background: transparent !important;
                              background-color: transparent !important;
                              padding: 0 !important;
                              margin: 0 0 4px 0;">
                        @else
                          {{-- Fallback when no signature --}}
                          <div style="
                            height: 50px;
                            width: 180px;
                            border-bottom: 1px solid #DF1119;
                            margin-bottom: 4px;">
                          </div>
                          <p style="
                            font-size: 10px;
                            color: #04223E;
                            font-style: italic;
                            margin: 0 0 4px 0;">
                            Signature not uploaded
                          </p>
                        @endif
                        {{-- Always show name and title below --}}
                        <p style="
                          font-family: &quot;Inter&quot;, sans-serif;
                          font-size: 14px;
                          color: #04223E;
                          font-weight: 700;
                          margin: 2px 0 0 0;">
                          {{ $authorizedName }}
                        </p>
                        <p style="
                          font-family: &quot;Inter&quot;, sans-serif;
                          font-size: 14px;
                          color: #04223E;
                          margin: 2px 0 0 0;">
                          {{ $authorizedJobTitle }}
                        </p>
                        <p style="
                          font-family: &quot;Inter&quot;, sans-serif;
                          font-size: 14px;
                          color: #04223E;
                          margin: 2px 0 0 0;">
                          {{ $authorizationDate }}
                        </p>
                      </div>
                    </td>
                    <td class="tm_border_none tm_text_right" style="padding: 0; width: 55%; vertical-align: top;">
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div><!-- .tm_note -->
        </div>
      </div>
    </div>
  </div>
</body>
</html>
