@if($status === 'approved')
    <span class="badge bg-success">Approuvé</span>
@elseif($status === 'pending')
    <span class="badge bg-warning text-dark">En attente</span>
@elseif($status === 'rejected')
    <span class="badge bg-danger">Rejeté</span>
@else
    <span class="badge bg-secondary">{{ $status }}</span>
@endif
