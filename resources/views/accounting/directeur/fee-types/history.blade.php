@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Historique — '.$level->name)

@section('content')
<a href="{{ route('directeur.fee-types.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Retour à la grille tarifaire
</a>
<h1 class="h3 mb-1"><i class="fas fa-clock-rotate-left me-2"></i>Historique — {{ $level->name }}</h1>
<p class="text-muted mb-4">Modifications des montants de frais pour ce niveau, tous types confondus.</p>

@if($activities->isEmpty())
    <div class="empty-state"><i class="fas fa-clock-rotate-left"></i><p class="mb-0">Aucune modification enregistrée pour ce niveau.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type de frais</th>
                            <th>Action</th>
                            <th>Ancien montant</th>
                            <th>Nouveau montant</th>
                            <th>Par</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            @php
                                $new = $activity->properties->get('attributes');
                                $old = $activity->properties->get('old');
                                $feeTypeId = $new['fee_type_id'] ?? $old['fee_type_id'] ?? null;
                            @endphp
                            <tr>
                                <td class="small text-muted">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $feeTypeNames[$feeTypeId] ?? '—' }}</td>
                                <td>
                                    @if($activity->event === 'created')
                                        <span class="status-badge status-badge-success">Créé</span>
                                    @elseif($activity->event === 'updated')
                                        <span class="status-badge status-badge-warning">Modifié</span>
                                    @elseif($activity->event === 'deleted')
                                        <span class="status-badge status-badge-danger">Désactivé</span>
                                    @else
                                        <span class="status-badge status-badge-neutral">{{ $activity->event }}</span>
                                    @endif
                                </td>
                                <td>{{ isset($old['amount']) ? number_format($old['amount'], 0, ',', ' ').' FCFA' : '—' }}</td>
                                <td>{{ isset($new['amount']) ? number_format($new['amount'], 0, ',', ' ').' FCFA' : '—' }}</td>
                                <td class="small text-muted">{{ $activity->causer?->name ?? 'Système' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
