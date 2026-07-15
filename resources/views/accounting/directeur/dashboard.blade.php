@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Tableau de bord — Directeur')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i>Tableau de bord financier</h1>
        <p class="text-muted mb-0">{{ $schoolDisplayName ?? 'Votre établissement' }} — {{ now()->locale('fr')->translatedFormat('d F Y') }}</p>
    </div>
    <a href="{{ route('directeur.ledger.export') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-file-csv me-1"></i> Exporter le grand livre (CSV)
    </a>
</div>

{{-- Indicateurs clés --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Solde actuel</div>
                <div class="h4 mb-0">{{ number_format($summary['solde_actuel'], 0, ',', ' ') }} <span class="fs-6 text-muted">FCFA</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Recettes du mois</div>
                <div class="h4 mb-0 text-success">{{ number_format($summary['recettes_mois'], 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Dépenses du mois</div>
                <div class="h4 mb-0 text-danger">{{ number_format($summary['depenses_mois'], 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Masse salariale mensuelle</div>
                <div class="h4 mb-0">{{ number_format($summary['masse_salariale'], 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Recettes du jour</div>
                <div class="h5 mb-0 text-success">{{ number_format($summary['recettes_jour'], 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Élèves à jour</div>
                <div class="h5 mb-0">{{ $summary['eleves_payes'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Élèves débiteurs</div>
                <div class="h5 mb-0 text-warning">{{ $summary['eleves_debiteurs'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Factures en attente</div>
                <div class="h5 mb-0">{{ $summary['paiements_en_attente'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Évolution recettes / dépenses (6 derniers mois)</h5>
            </div>
            <div class="card-body">
                <canvas id="evolutionChart" aria-label="Graphique d'évolution des recettes et dépenses sur 6 mois" height="110"></canvas>
                <table class="visually-hidden">
                    <caption>Recettes et dépenses par mois</caption>
                    <thead><tr><th>Mois</th><th>Recettes</th><th>Dépenses</th></tr></thead>
                    <tbody>
                        @foreach($evolution['labels'] as $i => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $evolution['recettes'][$i] }}</td>
                                <td>{{ $evolution['depenses'][$i] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
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
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-6">
        <a href="{{ route('directeur.fee-types.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <i class="fas fa-file-invoice-dollar fa-2x text-warning mb-2"></i>
                    <h5 class="mb-1">Frais scolaires</h5>
                    <p class="text-muted mb-0 small">Types de frais et montants par niveau (inscription, mensualité...).</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('directeur.salaries.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <i class="fas fa-money-check-alt fa-2x text-warning mb-2"></i>
                    <h5 class="mb-1">Salaires du personnel</h5>
                    <p class="text-muted mb-0 small">Enseignants, surveillants, administratifs.</p>
                </div>
            </div>
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('evolutionChart');
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($evolution['labels']),
            datasets: [
                {
                    label: 'Recettes',
                    data: @json($evolution['recettes']),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.12)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: 'Dépenses',
                    data: @json($evolution['depenses']),
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.08)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#dc2626',
                    tension: 0.3,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function (item) {
                            return item.dataset.label + ' : ' + item.parsed.y.toLocaleString('fr-FR') + ' FCFA';
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        callback: function (value) { return value.toLocaleString('fr-FR'); },
                    },
                },
                x: { grid: { display: false } },
            },
        },
    });
})();
</script>
@endpush
@endsection
