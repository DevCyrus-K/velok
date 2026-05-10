@php
    $timelineStages = isset($stages)
        ? collect($stages)
        : collect(data_get($stageable ?? null, 'stages', []));
@endphp

@if($timelineStages->isNotEmpty())
    <div class="mt-4">
        <h5 class="fw-semibold text-muted mb-3">Booking Timeline</h5>
        <div class="relative">
            @foreach($timelineStages as $stage)
                @php
                    $stageName = (string) $stage->stage;
                    $tone = str_contains($stageName, 'APPROVED') || str_contains($stageName, 'RECEIVED') || str_contains($stageName, 'CONFIRMED') || str_contains($stageName, 'COMPLETED')
                        ? 'bg-success-subtle text-success'
                        : ((str_contains($stageName, 'OPENED') || str_contains($stageName, 'CLICKED'))
                            ? 'bg-primary-subtle text-primary'
                            : ((str_contains($stageName, 'REJECTED') || str_contains($stageName, 'FAILED'))
                                ? 'bg-danger-subtle text-danger'
                                : 'bg-secondary-subtle text-secondary'));
                    $icon = str_contains($stageName, 'APPROVED') || str_contains($stageName, 'RECEIVED') || str_contains($stageName, 'CONFIRMED') || str_contains($stageName, 'COMPLETED')
                        ? 'check'
                        : ((str_contains($stageName, 'OPENED') || str_contains($stageName, 'CLICKED'))
                            ? 'eye'
                            : ((str_contains($stageName, 'REJECTED') || str_contains($stageName, 'FAILED')) ? 'x' : (str_contains($stageName, 'SENT') ? 'send' : 'clock')));
                @endphp
                <div class="d-flex gap-3 mb-3">
                    <div class="flex-shrink-0 rounded-circle d-inline-flex align-items-center justify-content-center {{ $tone }}" style="width:32px;height:32px">
                        <i data-lucide="{{ $icon }}" class="icon-xs"></i>
                    </div>
                    <div class="flex-grow-1 pb-3 border-bottom">
                        <p class="small fw-medium text-dark mb-1">{{ $stage->description }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="text-muted small">{{ $stage->created_at?->format('d M Y \a\t H:i') }}</span>
                            @if($stage->actor_name)
                                <span class="text-muted small">· {{ $stage->actor_name }}</span>
                            @endif
                            @if($stage->channel)
                                <span class="text-primary small text-capitalize">· via {{ $stage->channel }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
