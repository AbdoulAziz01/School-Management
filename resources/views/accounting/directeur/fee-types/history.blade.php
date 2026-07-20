@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Historique — '.$level->name)

@section('content')
<p class="mb-3">
    <a href="{{ route('directeur.fee-types.index') }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour à la grille tarifaire
    </a>
</p>
<h1 class="h3 mb-1"><i class="fas fa-history me-2"></i>Historique — {{ $level->name }}</h1>
<p class="text-muted mb-4">Modifications des montants de frais pour ce niveau, tous types confondus.</p>

<div class="card">
    <div class="card-body">
        @if($activities->isEmpty())
            <div class="alert alert-info mb-0">Aucune modification enregistrée pour ce niveau.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
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
                                        <span class="badge bg-success">Créé</span>
                                    @elseif($activity->event === 'updated')
                                        <span class="badge bg-warning text-dark">Modifié</span>
                                    @elseif($activity->event === 'deleted')
                                        <span class="badge bg-danger">Désactivé</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $activity->event }}</span>
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
        @endif
    </div>
</div>
@endsection
