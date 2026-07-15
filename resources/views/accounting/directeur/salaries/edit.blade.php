@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Salaire — '.$employee->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">{{ $employee->name }}</h1>
        <p class="text-muted mb-0">Matricule : {{ $employee->identifier ?? '—' }}</p>
    </div>
    <a href="{{ route('directeur.salaries.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-check-alt me-2"></i>Salaire mensuel</h5>
            </div>
            <div class="card-body">
                @if($currentProfile)
                    <p class="text-muted small mb-3">
                        Actuellement <strong>{{ number_format($currentProfile->monthly_amount, 0, ',', ' ') }} FCFA</strong>/mois,
                        depuis le {{ $currentProfile->effective_from->format('d/m/Y') }}.
                    </p>
                @else
                    <p class="text-muted small mb-3">Aucun salaire défini pour le moment.</p>
                @endif

                <form method="POST" action="{{ route('directeur.salaries.update', $employee) }}">
                    @csrf
                    @method('PUT')

                    <x-admin.form-field type="number" name="monthly_amount" label="Nouveau salaire mensuel (FCFA)"
                        :value="old('monthly_amount', $currentProfile?->monthly_amount)" required
                        help="Modifier ce montant ferme la période précédente et démarre une nouvelle période à partir d'aujourd'hui — l'historique reste consultable." />

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique</h5>
            </div>
            <div class="card-body">
                @if($history->isEmpty())
                    <p class="text-muted mb-0">Aucun historique.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Montant</th>
                                    <th>Du</th>
                                    <th>Au</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $entry)
                                    <tr class="{{ $entry->isActive() ? 'table-success' : '' }}">
                                        <td>{{ number_format($entry->monthly_amount, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ $entry->effective_from->format('d/m/Y') }}</td>
                                        <td>{{ $entry->effective_to?->format('d/m/Y') ?? 'En cours' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
