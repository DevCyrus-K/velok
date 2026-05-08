@php
    $logs = collect($logs ?? []);
    $latest = $logs->first();
    $retryTarget = $retryTarget ?? null;

    $formatEmailLog = function ($log) {
        $status = strtolower((string) $log->status);
        $date = match ($status) {
            'opened' => $log->opened_at,
            'sent' => $log->sent_at,
            default => $log->created_at,
        };
        $dateLabel = $date ? $date->format('d M Y \a\t H:i') : null;

        return match ($status) {
            'sent' => [
                'icon' => '✅',
                'class' => 'text-success',
                'label' => 'Sent',
                'detail' => $dateLabel ?: 'Accepted by the mail transport',
            ],
            'opened' => [
                'icon' => '👁',
                'class' => 'text-info',
                'label' => 'Opened',
                'detail' => $dateLabel ?: 'Opened by the recipient',
            ],
            'failed', 'bounced' => [
                'icon' => '❌',
                'class' => 'text-danger',
                'label' => ucfirst($status),
                'detail' => filled($log->failed_reason) ? '"' . $log->failed_reason . '"' : 'Delivery failed',
            ],
            default => [
                'icon' => '🕐',
                'class' => 'text-warning',
                'label' => 'Queued',
                'detail' => 'Waiting to be sent...',
            ],
        };
    };
@endphp

@if($latest)
    @php($latestStatus = $formatEmailLog($latest))
    <div class="email-history-panel border-top mt-3 pt-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="{{ $latestStatus['class'] }} fw-medium">
                <span aria-hidden="true">{{ $latestStatus['icon'] }}</span>
                {{ $latestStatus['label'] }} — {{ $latestStatus['detail'] }}
            </div>
            @if(in_array($latest->status, ['failed', 'bounced'], true) && $retryTarget)
                <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="{{ $retryTarget }}">Retry</button>
            @endif
        </div>

        @if($logs->count() > 1)
            <details class="mt-2">
                <summary class="text-muted small fw-semibold">Email History ({{ $logs->count() }})</summary>
                <div class="list-group list-group-flush mt-2">
                    @foreach($logs as $log)
                        @php($entry = $formatEmailLog($log))
                        <div class="list-group-item px-0">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <span class="{{ $entry['class'] }} fw-medium">
                                    <span aria-hidden="true">{{ $entry['icon'] }}</span>
                                    {{ $entry['label'] }}
                                </span>
                                <span class="text-muted small">{{ $log->created_at?->format('d M Y \a\t H:i') }}</span>
                            </div>
                            <div class="small text-muted">{{ $log->recipient_email }}</div>
                            <div class="small">{{ $entry['detail'] }}</div>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    </div>
@endif
