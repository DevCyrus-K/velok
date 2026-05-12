<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SERVICE AGREEMENT</title>
    <style>
        @page {
            margin: 2.5cm;
        }

        body {
            color: #04223e;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        h1 {
            font-size: 24px;
            letter-spacing: 0;
            text-align: center;
        }

        h2 {
            border-bottom: 1px solid #df1119;
            font-size: 13px;
            margin: 18px 0 8px;
            padding-bottom: 5px;
        }

        h3 {
            font-size: 11px;
            margin: 9px 0 5px;
            text-transform: uppercase;
        }

        table {
            border-collapse: collapse;
            margin: 6px 0 10px;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #e6edf5;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #fff5f5;
            color: #04223e;
            font-weight: bold;
        }

        ul {
            margin: 4px 0 10px 16px;
            padding: 0;
        }

        li {
            margin-bottom: 4px;
        }

        .top {
            margin-bottom: 14px;
            text-align: center;
        }

        .logo {
            margin-bottom: 10px;
            text-align: center;
        }

        .logo img {
            max-height: 52px;
            max-width: 190px;
        }

        .meta-table td:first-child,
        .field-table td:first-child {
            font-weight: bold;
            width: 34%;
        }

        .party-title {
            background: #f8fafc;
            border: 1px solid #e6edf5;
            font-weight: bold;
            margin-top: 8px;
            padding: 6px 8px;
        }

        .dash {
            color: #04223e;
            font-family: Helvetica, Arial, sans-serif;
            white-space: nowrap;
        }

        .money {
            text-align: right;
            white-space: nowrap;
        }

        .clause {
            margin-bottom: 8px;
        }

        .clause strong {
            display: block;
            margin-bottom: 2px;
        }

        .declaration {
            border-left: 3px solid #df1119;
            margin: 8px 0 10px;
            padding: 8px 10px;
        }
    </style>
</head>
<body>
    <div class="top">
        @if(! empty($company['logo_data_uri']))
            <div class="logo">
                <img src="{{ $company['logo_data_uri'] }}" alt="{{ $company['name'] }} logo">
            </div>
        @endif
        <h1>SERVICE AGREEMENT</h1>
    </div>

    <table class="meta-table">
        <tr>
            <td>Agreement Reference No.:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Date of Agreement:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Proposed Move Date:</td>
            <td>{{ $proposedMoveDate }}</td>
        </tr>
        <tr>
            <td>Estimated Start Time:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
    </table>

    <h2>SECTION 1 — PARTIES TO THE AGREEMENT</h2>

    <div class="party-title">SERVICE PROVIDER</div>
    <table class="field-table">
        <tr>
            <td>Company Name:</td>
            <td>{{ $company['name'] }}</td>
        </tr>
        <tr>
            <td>Business Registration Number:</td>
            <td>{{ $company['business_registration_number'] }}</td>
        </tr>
        <tr>
            <td>Physical Address:</td>
            <td>{{ $company['physical_address'] }}</td>
        </tr>
        <tr>
            <td>Phone Number:</td>
            <td>{{ $company['phone'] }}</td>
        </tr>
        <tr>
            <td>Email Address:</td>
            <td>{{ $company['email'] }}</td>
        </tr>
        <tr>
            <td>Authorized Representative:</td>
            <td>{{ $company['authorized_representative_name'] }}</td>
        </tr>
    </table>

    <div class="party-title">CLIENT</div>
    <table class="field-table">
        <tr>
            <td>Full Name:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>National ID / Passport No.:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Phone Number:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Email Address:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Origin Address (Moving From):</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Destination Address (Moving To):</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
    </table>

    <h2>SECTION 2 — SCOPE OF SERVICES</h2>
    <table class="field-table">
        <tr>
            <td>Area Type:</td>
            <td>{{ $areaType }}</td>
        </tr>
        <tr>
            <td>Type of Services:</td>
            <td>{{ $typeOfServices }}</td>
        </tr>
    </table>

    <h3>Details of Services Provided:</h3>
    <ul>
        @foreach($serviceDescriptions as $description)
            <li>{{ $description }}</li>
        @endforeach
    </ul>

    <h2>SECTION 3 — PRICING AND PAYMENT TERMS</h2>
    @php
        $depositLabel = 'Deposit Required';

        if ($depositPercentage > 0) {
            $depositLabel .= ' (' . rtrim(rtrim(number_format($depositPercentage, 2), '0'), '.') . '%)';
        }
    @endphp
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th class="money">Unit Price</th>
                <th class="money">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pricingItems as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td class="money">KES {{ number_format($item['unit_price'], 2) }}</td>
                    <td class="money">KES {{ number_format($item['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Subtotal</th>
                <th class="money">KES {{ number_format($subtotal, 2) }}</th>
            </tr>
            <tr>
                <th colspan="3">Taxes</th>
                <th class="money">KES {{ number_format($tax, 2) }}</th>
            </tr>
            <tr>
                <th colspan="3">Total Amount Due</th>
                <th class="money">KES {{ number_format($total, 2) }}</th>
            </tr>
            <tr>
                <th colspan="3">{{ $depositLabel }}</th>
                <th class="money">KES {{ number_format($depositAmount, 2) }}</th>
            </tr>
            <tr>
                <th colspan="3">Balance Due</th>
                <th class="money">KES {{ number_format($balanceDue, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    <table class="field-table">
        <tr>
            <td>Payment Method:</td>
            <td>
                @forelse($paymentMethods as $method)
                    {{ $method }}@if(! $loop->last)<br>@endif
                @empty
                    {{ $paymentTerms ?: 'Payment details will be confirmed by our team.' }}
                @endforelse
            </td>
        </tr>
        <tr>
            <td>Payment Due Date:</td>
            <td>{{ $paymentDueDate }}</td>
        </tr>
        <tr>
            <td>Payment Terms:</td>
            <td>{{ $paymentTerms ?: 'Payment terms will be confirmed by our team.' }}</td>
        </tr>
    </table>

    <h2>SECTION 4 — TERMS AND CONDITIONS</h2>
    <div class="clause">
        <strong>4.1 SCOPE OF WORK</strong>
        <p>The service provider agrees to perform only the services listed in Section 2 of this agreement. Any work not explicitly described herein is excluded from this agreement unless a written amendment is signed by both parties.</p>
    </div>
    <div class="clause">
        <strong>4.2 ADDITIONAL ITEMS OR EXTRA WORK</strong>
        <p>Any additional items presented on the day of the move that were not included in the original quote may incur additional charges. The client will be informed of any extra costs before work commences on those items. A verbal or written approval from the client will be required.</p>
    </div>
    <div class="clause">
        <strong>4.3 CLIENT RESPONSIBILITIES</strong>
        <p>The client agrees to: (a) ensure all items to be moved are properly packed unless packing services have been purchased; (b) disclose any fragile, high-value, or hazardous items in advance; (c) ensure clear and safe access to both the origin and destination addresses; (d) be present or have an authorized representative present on the day of the move.</p>
    </div>
    <div class="clause">
        <strong>4.4 RIGHT TO REFUSE CERTAIN WORK</strong>
        <p>The service provider reserves the right to refuse to move any item that poses a safety risk to staff or property, is prohibited by law, or was not disclosed during the quoting process. This includes but is not limited to hazardous materials, illegal items, and items requiring specialist equipment not agreed upon.</p>
    </div>
    <div class="clause">
        <strong>4.5 COMPANY MATERIALS</strong>
        <p>Any packing materials, boxes, blankets, or equipment supplied by the service provider remain the property of the service provider unless explicitly sold to the client. Rental materials must be returned in good condition upon completion.</p>
    </div>
    <div class="clause">
        <strong>4.6 DAMAGES AND LIABILITY</strong>
        <p>The service provider will exercise due care with all items. In the event of damage caused directly by the service provider's negligence, liability is limited to the actual replacement or repair cost of the item, up to a maximum of {{ $company['liability_cap_amount'] }}. The service provider is not liable for pre-existing damage, items packed by the client, or Acts of God. The client is encouraged to arrange independent insurance for high-value items.</p>
    </div>
    <div class="clause">
        <strong>4.7 COMPLETION OF WORK</strong>
        <p>The job is considered complete when all items listed in the scope of services have been delivered to the destination address and the client or their representative has signed the job completion form. Any concerns must be raised before signing.</p>
    </div>
    <div class="clause">
        <strong>4.8 DISPUTE RESOLUTION</strong>
        <p>Any dispute arising from this agreement shall first be addressed through good-faith negotiation between the parties. If unresolved within 14 days, the matter shall be referred to a mutually agreed mediator or the relevant consumer protection authority in the jurisdiction of service.</p>
    </div>

    <h2>SECTION 5 — CLIENT DECLARATION &amp; SIGNATURE</h2>
    <div class="declaration">
        "I, the undersigned, confirm that I have read, understood, and agree to all terms and conditions set out in this Service Agreement. I authorize the service provider to proceed with the services as described."
    </div>
    <table class="field-table">
        <tr>
            <td>Client Full Name:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Client Signature:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Date:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
    </table>

    <h2>SECTION 6 — SERVICE PROVIDER AUTHORIZATION</h2>
    <div class="declaration">
        "This agreement is issued on behalf of {{ $company['name'] }} and is binding upon approval of the quote."
    </div>
    <table class="field-table">
        <tr>
            <td>Authorized Representative Name:</td>
            <td>{{ $company['authorized_representative_name'] }}</td>
        </tr>
        <tr>
            <td>Designation / Title:</td>
            <td>{{ $company['authorized_representative_title'] }}</td>
        </tr>
        <tr>
            <td>Signature:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Date:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
        <tr>
            <td>Company Stamp / Seal:</td>
            <td><span class="dash">{{ $dash }}</span></td>
        </tr>
    </table>
</body>
</html>
