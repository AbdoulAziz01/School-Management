@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Salaire — '.$employee->name)

@section('content')
<a href="{{ route('directeur.salaries.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Salaires du personnel
</a>

<div class="class-hero mb-4">
    <span class="class-hero-avatar">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
    <div class="flex-grow-1">
        <h1 class="h4 mb-1">{{ $employee->name }}</h1>
        <p class="text-muted mb-0 small"><i class="fas fa-id-card me-1"></i>Matricule : {{ $employee->identifier ?? '—' }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="panel-card">
            <div class="panel-card-header"><i class="fas fa-money-check-alt me-2 text-warning"></i>Salaire mensuel</div>
            <div class="p-4">
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

                    <button type="submit" class="btn-pill-primary">
                        <i class="fas fa-save"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel-card">
            <div class="panel-card-header"><i class="fas fa-clock-rotate-left me-2 text-warning"></i>Historique</div>
            <div class="p-0">
                @if($history->isEmpty())
                    <div class="empty-state py-4"><i class="fas fa-clock-rotate-left"></i><p class="mb-0">Aucun historique.</p></div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 data-table">
                            <thead>
                                <tr>
                                    <th>Montant</th>
                                    <th>Du</th>
                                    <th>Au</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $entry)
                                    <tr>
                                        <td><strong>{{ number_format($entry->monthly_amount, 0, ',', ' ') }} FCFA</strong></td>
                                        <td>{{ $entry->effective_from->format('d/m/Y') }}</td>
                                        <td>
                                            @if($entry->isActive())
                                                <span class="status-badge status-badge-success">En cours</span>
                                            @else
                                                {{ $entry->effective_to?->format('d/m/Y') ?? '—' }}
                                            @endif
                                        </td>
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

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
