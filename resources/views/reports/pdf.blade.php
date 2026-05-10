<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] ?? 'Report' }}</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            color: #1f2937;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        .muted {
            color: #6b7280;
        }

        .header {
            border-bottom: 1px solid #d1d5db;
            margin-bottom: 18px;
            padding-bottom: 12px;
        }

        .badge {
            background: #e0f2fe;
            border-radius: 999px;
            color: #075985;
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 8px;
            padding: 4px 8px;
            text-transform: uppercase;
        }

        .meta {
            margin-top: 6px;
        }

        .section {
            margin-top: 16px;
        }

        .cards {
            width: 100%;
            border-collapse: collapse;
        }

        .cards td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            vertical-align: top;
            width: 25%;
        }

        .card-title {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
        }

        .card-value {
            color: #111827;
            font-size: 18px;
            font-weight: 700;
            margin-top: 4px;
        }

        .insights {
            width: 100%;
            border-collapse: collapse;
        }

        .insights td {
            border: 1px solid #e5e7eb;
            padding: 9px;
            vertical-align: top;
            width: 25%;
        }

        .insight-value {
            color: #111827;
            font-size: 14px;
            font-weight: 700;
            margin: 3px 0;
        }

        .data-table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #d1d5db;
            overflow-wrap: anywhere;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background: #f3f4f6;
            color: #111827;
            font-size: 9px;
            text-transform: uppercase;
        }

        .cell-secondary {
            color: #6b7280;
            display: block;
            font-size: 9px;
            margin-top: 2px;
        }

        .status {
            background: #f3f4f6;
            border-radius: 999px;
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
        }
    </style>
</head>
<body>
    @php
        $cards = collect($report['cards'] ?? []);
        $insights = collect($report['insights'] ?? []);
        $table = $report['table'] ?? null;
    @endphp

    <div class="header">
        <span class="badge">{{ $report['badge'] ?? 'Report' }}</span>
        <h1>{{ $report['title'] ?? 'Report' }}</h1>
        @if(!empty($report['subtitle']))
            <p class="muted">{{ $report['subtitle'] }}</p>
        @endif
        <p class="muted meta">Generated {{ $generatedAt->format('Y-m-d H:i') }}</p>
    </div>

    @if($cards->isNotEmpty())
        <div class="section">
            <h2>Summary</h2>
            <table class="cards">
                @foreach($cards->chunk(4) as $cardRow)
                    <tr>
                        @foreach($cardRow as $card)
                            <td>
                                <div class="card-title">{{ $card['title'] ?? '' }}</div>
                                <div class="card-value">{{ $card['value'] ?? '' }}</div>
                            </td>
                        @endforeach
                        @for($i = $cardRow->count(); $i < 4; $i++)
                            <td></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if($insights->isNotEmpty())
        <div class="section">
            <h2>Insights</h2>
            <table class="insights">
                @foreach($insights->chunk(4) as $insightRow)
                    <tr>
                        @foreach($insightRow as $insight)
                            <td>
                                <div class="card-title">{{ $insight['label'] ?? '' }}</div>
                                <div class="insight-value">{{ $insight['value'] ?? '' }}</div>
                                <div class="muted">{{ $insight['note'] ?? '' }}</div>
                            </td>
                        @endforeach
                        @for($i = $insightRow->count(); $i < 4; $i++)
                            <td></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if($table)
        <div class="section">
            <h2>{{ $table['title'] ?? 'Report Details' }}</h2>
            @if(!empty($table['description']))
                <p class="muted">{{ $table['description'] }}</p>
            @endif

            <table class="data-table" style="margin-top: 8px;">
                <thead>
                    <tr>
                        @foreach($table['columns'] ?? [] as $column)
                            <th>{{ $column['label'] ?? '' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($table['rows'] ?? [] as $row)
                        <tr>
                            @foreach($table['columns'] ?? [] as $column)
                                @php
                                    $cell = $row['cells'][$column['key']] ?? ['type' => 'text', 'text' => ''];
                                @endphp
                                <td>
                                    @if(($cell['type'] ?? 'text') === 'stack')
                                        {{ $cell['primary'] ?? '' }}
                                        @if(!empty($cell['secondary']))
                                            <span class="cell-secondary">{{ $cell['secondary'] }}</span>
                                        @endif
                                    @elseif(($cell['type'] ?? 'text') === 'badge')
                                        <span class="status">{{ $cell['label'] ?? '' }}</span>
                                    @else
                                        {{ $cell['text'] ?? '' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($table['columns'] ?? []) }}">No report rows are available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>
