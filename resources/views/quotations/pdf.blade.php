<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
        }
        .clearfix {
            overflow: auto;
            margin-bottom: 30px;
        }
        .float-end {
            float: right;
        }
        .float-start {
            float: left;
        }
        .logo-section {
            text-align: right;
            margin-bottom: 20px;
        }
        .logo-section img {
            height: 40px;
            margin-right: 10px;
        }
        .header-title {
            float: left;
            margin-top: 10px;
        }
        .header-title h5 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }
        .header-title p {
            font-size: 13px;
            color: #666;
            margin: 3px 0;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-info {
            background: #e7f1f8;
            color: #0c5394;
        }
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .badge-warning {
            background: #fff3e0;
            color: #e65100;
        }
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        
        .section-row {
            display: flex;
            gap: 30px;
            margin: 30px 0;
        }
        .section-col {
            flex: 1;
        }
        .section-col h6 {
            color: #999;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-col .title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .section-col address {
            font-style: normal;
            line-height: 1.6;
            font-size: 13px;
            color: #666;
        }
        .section-col abbr {
            border: none;
            cursor: default;
            text-decoration: none;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
        }
        table thead {
            background: #f5f5f5;
        }
        table thead tr th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border: none;
        }
        table tbody tr td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #666;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
        table .text-end {
            text-align: right;
        }
        
        .notes-section {
            display: flex;
            gap: 30px;
            margin: 30px 0;
        }
        .notes-col {
            flex: 1;
        }
        .notes-col h6 {
            color: #999;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .notes-col small {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .summary-col {
            flex: 1;
            text-align: right;
        }
        .summary-col p {
            font-size: 13px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }
        .summary-col .label {
            font-weight: 600;
            color: #333;
        }
        .summary-col .value {
            color: #666;
        }
        .summary-col h3 {
            font-size: 18px;
            margin-top: 15px;
            color: #333;
        }
        
        .print-actions {
            margin-top: 40px;
            text-align: right;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .container {
                padding: 30px;
            }
            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="clearfix">
            <div class="logo-section float-end">
                <img alt="logo-dark" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAA5klEQVR42u3QMQqCMBCG4S/EpXELHBqcHJ0cnJ0cnBycHBwcHBwd3NtaXBoanp6e7/u+/73h4H4nHNzfBARBEARBEARBEARBEARBEARBEARB/Ccsy5LneZIkSZZliOM4z/M0TdO2bQzDoOu6vu93u93P8zxVVZVl2XmepmnyPC/LMhzHcRyHbduWZRkOh8NhGIYQQgiRJImUUkqplBJC6LpOKaXrOqWU53ksy0IIMQxDCCGmafI87zoP23bTNLqua5rGNE2maRiGIYRQFMWyLF3XVVWVZRnHcZqmabve7/ePx4OUUkqJ4zjXdZ1lWZZlIoSQJMlxHNd1XdftdjtN02RZll3Xfd/nPM+z7zpVVeV5/vN+PxwO/wAAAP//dFpomcYbFegAAAAASUVORK5CYII=" />
            </div>
            <div class="header-title float-start">
                <h5>PROFESSIONAL QUOTATION</h5>
                <p>Quotation: {{ $quotation->quoteRequest->reference() }}</p>
                <p>{{ $quotation->quote_date?->format('d M, Y h:i A') ?? 'N/A' }}</p>
                <span class="badge {{ $quotation->status === 'sent' ? 'badge-success' : ($quotation->status === 'draft' ? 'badge-info' : 'badge-warning') }}">
                    {{ ucfirst($quotation->status) }}
                </span>
            </div>
        </div>

        <!-- Customer and Route Information -->
        <div class="section-row">
            <div class="section-col">
                <h6>Customer</h6>
                <div class="title">{{ $quotation->quoteRequest->full_name }}</div>
                <address>
                    {{ $quotation->quoteRequest->email }}<br />
                    <abbr title="Phone">P:</abbr> {{ $quotation->quoteRequest->phone }}<br />
                    <span style="color: #999;">{{ $quotation->quoteRequest->serviceTypeLabel() }}</span>
                </address>
            </div>
            <div class="section-col">
                <h6>Requested Route</h6>
                <div class="title">{{ $quotation->moving_from ?? $quotation->quoteRequest->moving_from }}</div>
                <address>
                    to {{ $quotation->moving_to ?? $quotation->quoteRequest->moving_to }}<br />
                    Scheduled move: {{ $quotation->move_date?->format('d M, Y') ?? $quotation->quoteRequest->move_date?->format('d M, Y') ?? 'Not specified' }}<br />
                    Move size: {{ $quotation->quoteRequest->move_size ?: 'Not specified' }}
                </address>
            </div>
        </div>

        <!-- Quotation Details Table -->
        <table>
            <thead>
                <tr>
                    <th>Details</th>
                    <th>Description</th>
                    <th class="text-end">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Quote Amount (KES)</strong></td>
                    <td>Professional moving service quotation</td>
                    <td class="text-end"><strong>KES {{ number_format($quotation->quote_amount ?? 0, 2) }}</strong></td>
                </tr>
                <tr>
                    <td>Deposit Required</td>
                    <td>{{ ($quotation->deposit_percentage ?? 30) }}% of total amount</td>
                    <td class="text-end">KES {{ number_format(($quotation->quote_amount ?? 0) * (($quotation->deposit_percentage ?? 30) / 100), 2) }}</td>
                </tr>
                <tr>
                    <td>Quote Valid Until</td>
                    <td>{{ $quotation->quote_valid_until?->format('d M, Y') ?? 'Not specified' }}</td>
                    <td class="text-end">{{ $quotation->quote_valid_until?->diffInDays() }} days</td>
                </tr>
                <tr>
                    <td>Service Type</td>
                    <td>{{ $quotation->quoteRequest->serviceTypeLabel() }}</td>
                    <td class="text-end">{{ $quotation->quoteRequest->move_size }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Services Included -->
        @if ($quotation->services_included && count($quotation->services_included) > 0)
            <h6 style="color: #999; font-size: 12px; font-weight: 500; margin-top: 30px; margin-bottom: 15px; text-transform: uppercase;">Services Included</h6>
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotation->services_included as $service)
                        <tr>
                            <td><strong>{{ $service['name'] }}</strong></td>
                            <td>{{ $service['description'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Payment Terms & Additional Notes -->
        <div class="notes-section">
            <div class="notes-col">
                @if ($quotation->payment_terms)
                    <h6>Payment Terms</h6>
                    <small>{{ $quotation->payment_terms }}</small>
                @endif
                
                @if ($quotation->additional_notes)
                    <div style="margin-top: 20px;">
                        <h6>Additional Notes</h6>
                        <small>{{ $quotation->additional_notes }}</small>
                    </div>
                @endif
                
                @if ($quotation->cancellation_notice_hours)
                    <div style="margin-top: 20px;">
                        <h6>Cancellation Policy</h6>
                        <small>{{ $quotation->cancellation_notice_hours }} hours notice required</small>
                    </div>
                @endif
            </div>
            
            <div class="summary-col">
                <p>
                    <span class="label">Quote Reference:</span>
                    <span class="value">{{ $quotation->quoteRequest->reference() }}</span>
                </p>
                <p>
                    <span class="label">Authorized By:</span>
                    <span class="value">{{ $quotation->authorized_by ?? 'Admin' }}</span>
                </p>
                <p>
                    <span class="label">Approval Date:</span>
                    <span class="value">{{ $quotation->approval_date?->format('d M, Y') ?? 'Pending' }}</span>
                </p>
                @if ($quotation->signature)
                    <p>
                        <span class="label">Signature:</span>
                        <span class="value">{{ $quotation->signature }}</span>
                    </p>
                @endif
                <h3 style="color: #007bff;">KES {{ number_format($quotation->quote_amount ?? 0, 2) }}</h3>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>KwikShift Movers</strong><br>
            Professional Moving & Storage Services<br>
            +254 112587581 / +254111330980 | info@kwikshiftmovers.co.ke<br>
            Londiani Road, off Likoni Road, Industrial Area, Nairobi, 00200, KE<br>
            <br>
            <em>This quotation is valid until {{ $quotation->quote_valid_until?->format('d M, Y') ?? 'the specified date' }}. All prices are in Kenyan Shillings (KES).</em>
        </div>
    </div>
</body>
</html>
