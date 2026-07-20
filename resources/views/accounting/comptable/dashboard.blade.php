@extends('admin.layouts.app', ['sidebarView' => 'accounting.comptable.sidebar', 'navbarView' => 'accounting.comptable.navbar'])

@section('title', 'Tableau de bord — Comptable')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-book me-2"></i>Comptabilité</h1>
        <p class="text-muted mb-0">{{ now()->locale('fr')->translatedFormat('d F Y') }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="{{ route('comptable.ledger.index') }}" class="card h-100 stat-card-link">
            <div class="card-body">
                <div class="small text-muted mb-1">Solde de trésorerie</div>
                <div class="h4 mb-0">{{ number_format($summary['solde_actuel'], 0, ',', ' ') }} <span class="fs-6 text-muted">FCFA</span></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('comptable.ledger.index') }}" class="card h-100 stat-card-link">
            <div class="card-body">
                <div class="small text-muted mb-1">Recettes du jour</div>
                <div class="h5 mb-0 text-success">{{ number_format($summary['recettes_jour'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('comptable.expenses.index') }}" class="card h-100 stat-card-link">
            <div class="card-body">
                <div class="small text-muted mb-1">Dépenses du jour</div>
                <div class="h5 mb-0 text-danger">{{ number_format($summary['depenses_jour'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('comptable.students.debtors') }}" class="card h-100 stat-card-link">
            <div class="card-body">
                <div class="small text-muted mb-1">Élèves débiteurs</div>
                <div class="h5 mb-0 text-warning">{{ $summary['eleves_debiteurs'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('comptable.ledger.index') }}" class="card h-100 stat-card-link">
            <div class="card-body">
                <div class="small text-muted mb-1">Recettes du mois</div>
                <div class="h5 mb-0 text-success">{{ number_format($summary['recettes_mois'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('comptable.expenses.index') }}" class="card h-100 stat-card-link">
            <div class="card-body">
                <div class="small text-muted mb-1">Dépenses du mois</div>
                <div class="h5 mb-0 text-danger">{{ number_format($summary['depenses_mois'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('comptable.salaries.index') }}" class="card h-100 stat-card-link">
            <div class="card-body">
                <div class="small text-muted mb-1">Masse salariale mensuelle</div>
                <div class="h5 mb-0">{{ number_format($summary['masse_salariale'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('comptable.payments.index') }}" class="card h-100 stat-card-link">
            <div class="card-body">
                <div class="small text-muted mb-1">Factures en attente</div>
                <div class="h5 mb-0">{{ $summary['paiements_en_attente'] }}</div>
            </div>
        </a>
    </div>
</div>

@push('styles')
<style>
    .stat-card-link {
        display: block;
        text-decoration: none !important;
        color: inherit !important;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .stat-card-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
    }
</style>
@endpush

<div class="row g-4">
    <div class="col-lg-7">
        <div class="row g-4">
            <div class="col-md-4">
                <a href="{{ route('comptable.payments.index') }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-receipt fa-2x text-warning mb-2"></i>
                            <h5 class="mb-1">Paiements élèves</h5>
                            <p class="text-muted mb-0 small">Consulter, corriger ou annuler un paiement.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('comptable.expenses.index') }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                            <h5 class="mb-1">Dépenses</h5>
                            <p class="text-muted mb-0 small">Fournitures, factures, entretien, primes...</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('comptable.salaries.index') }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-money-check-alt fa-2x text-warning mb-2"></i>
                            <h5 class="mb-1">Salaires</h5>
                            <p class="text-muted mb-0 small">Générer et payer les salaires du mois.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Dernières opérations</h5>
            </div>
            <div class="card-body p-0">
                @if($recentOperations->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucune opération pour le moment.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($recentOperations as $entry)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="small">{{ $entry->description }}</div>
                                    <div class="text-muted" style="font-size: .75rem;">{{ $entry->recorded_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <span class="{{ $entry->amount >= 0 ? ($entry->entry_type === 'recette' ? 'text-success' : 'text-danger') : 'text-muted' }} fw-semibold small">
                                    {{ $entry->amount >= 0 ? '+' : '' }}{{ number_format($entry->amount, 0, ',', ' ') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="p-2 text-center border-top">
                        <a href="{{ route('comptable.ledger.index') }}" class="small">Voir le journal complet</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
