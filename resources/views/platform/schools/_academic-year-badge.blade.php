@if($year)
    <span class="badge bg-{{ $year->statusBadgeClass() }}">{{ $year->statusLabel() }}</span>
    <small class="text-muted">{{ $year->name }}</small>
@else
    <span class="text-muted">—</span>
@endif
