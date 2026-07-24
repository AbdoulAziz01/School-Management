@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Tableau de bord — Directeur')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-satellite-dish me-2"></i>Centre de Commande</h1>
        <p class="text-muted mb-0">{{ $schoolDisplayName ?? 'Votre établissement' }} — {{ now()->locale('fr')->translatedFormat('d F Y') }}</p>
    </div>
    <a href="{{ route('directeur.ledger.export') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-file-csv me-1"></i> Exporter le grand livre (CSV)
    </a>
</div>

{{-- État général de l'établissement --}}
<div class="section-eyebrow"><i class="fas fa-building-columns"></i>État général de l'établissement</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.school.students.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-amber"><i class="fas fa-user-graduate"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Élèves</div>
                <div class="kpi-value">{{ $headcounts['students'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.school.classes.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-amber"><i class="fas fa-door-open"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Classes</div>
                <div class="kpi-value">{{ $headcounts['classes'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.school.teachers.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-amber"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Enseignants</div>
                <div class="kpi-value">{{ $headcounts['teachers'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.personnel.show', 'surveillants') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-slate"><i class="fas fa-user-shield"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Surveillants</div>
                <div class="kpi-value">{{ $headcounts['surveillants'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.personnel.show', 'admins') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-slate"><i class="fas fa-user-tie"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Personnel administratif</div>
                <div class="kpi-value">{{ $headcounts['admins'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.personnel.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-slate"><i class="fas fa-calculator"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Comptables / Caissiers</div>
                <div class="kpi-value">{{ $headcounts['comptables'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-slate"><i class="fas fa-people-roof"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Parents renseignés</div>
                <div class="kpi-value">{{ $headcounts['parents'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-blue"><i class="fas fa-user-plus"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Nouveaux inscrits (7j)</div>
                <div class="kpi-value">{{ $academicSnapshot['new_enrollments'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Présence du jour --}}
<div class="section-eyebrow"><i class="fas fa-calendar-day"></i>Présence du jour</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-green"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Élèves présents aujourd'hui</div>
                <div class="kpi-value text-success">{{ $attendanceToday['students_present'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-red"><i class="fas fa-times-circle"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Élèves absents aujourd'hui</div>
                <div class="kpi-value text-danger">{{ $attendanceToday['students_absent'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-orange"><i class="fas fa-user-clock"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Enseignants absents</div>
                <div class="kpi-value text-warning">{{ $attendanceToday['teachers_absent'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-red"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Élèves en difficulté</div>
                <div class="kpi-value text-danger">{{ $academicSnapshot['students_in_difficulty'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Vision académique --}}
<div class="section-eyebrow"><i class="fas fa-graduation-cap"></i>Vision académique</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-6">
        <div class="kpi-tile kpi-tile-static kpi-tile-lg">
            <div class="kpi-icon kpi-icon-purple"><i class="fas fa-star"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Moyenne générale de l'école</div>
                <div class="kpi-value">
                    @if($academicSnapshot['general_average'] !== null)
                        {{ $academicSnapshot['general_average'] }} <span class="kpi-value-suffix">/ 20</span>
                    @else
                        <span class="kpi-value-suffix">Pas encore de notes</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-6">
        <div class="kpi-tile kpi-tile-static kpi-tile-lg">
            <div class="kpi-icon kpi-icon-purple"><i class="fas fa-medal"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Taux de réussite</div>
                <div class="kpi-value">
                    {{ $academicSnapshot['success_rate'] !== null ? $academicSnapshot['success_rate'].'%' : '—' }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-eyebrow section-eyebrow-finance mt-5"><i class="fas fa-coins"></i>Finances</div>

{{-- Indicateurs clés --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.ledger.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-amber"><i class="fas fa-wallet"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Solde actuel</div>
                <div class="kpi-value">{{ number_format($summary['solde_actuel'], 0, ',', ' ') }} <span class="kpi-value-suffix">FCFA</span></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.ledger.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-green"><i class="fas fa-arrow-trend-up"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Recettes du mois</div>
                <div class="kpi-value text-success">{{ number_format($summary['recettes_mois'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.ledger.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-red"><i class="fas fa-arrow-trend-down"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Dépenses du mois</div>
                <div class="kpi-value text-danger">{{ number_format($summary['depenses_mois'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.salaries.checklist') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-slate"><i class="fas fa-money-check-alt"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Masse salariale mensuelle</div>
                <div class="kpi-value">{{ number_format($summary['masse_salariale'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.ledger.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-green"><i class="fas fa-coins"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Recettes du jour</div>
                <div class="kpi-value kpi-value-sm text-success">{{ number_format($summary['recettes_jour'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.students.debtors') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-green"><i class="fas fa-circle-check"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Élèves à jour</div>
                <div class="kpi-value kpi-value-sm">{{ $summary['eleves_payes'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.students.debtors') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-orange"><i class="fas fa-user-clock"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Élèves débiteurs</div>
                <div class="kpi-value kpi-value-sm text-warning">{{ $summary['eleves_debiteurs'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.payments.index') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-slate"><i class="fas fa-file-invoice"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Factures en attente</div>
                <div class="kpi-value kpi-value-sm">{{ $summary['paiements_en_attente'] }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.students.debtors') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-blue"><i class="fas fa-percent"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Taux de paiement des élèves</div>
                <div class="kpi-value kpi-value-sm">{{ $paymentRate }}%</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('directeur.salaries.checklist') }}" class="kpi-tile">
            <div class="kpi-icon kpi-icon-orange"><i class="fas fa-hourglass-half"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Salaires en attente ({{ now()->locale('fr')->translatedFormat('F') }})</div>
                <div class="kpi-value kpi-value-sm text-warning">{{ $pendingPayroll['count'] }} <span class="kpi-value-suffix">({{ number_format($pendingPayroll['amount'], 0, ',', ' ') }} FCFA)</span></div>
            </div>
        </a>
    </div>
</div>

@push('styles')
@include('accounting.directeur.partials.design-system')
<style>
    /* Extras propres à cette page (non repris dans le système partagé) */
    .section-eyebrow-finance { color: #78716c; }
    .kpi-tile-lg .kpi-icon { width: 52px; height: 52px; font-size: 1.15rem; }
</style>
@endpush

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
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Répartition des dépenses ({{ now()->locale('fr')->translatedFormat('F Y') }})</h5>
            </div>
            <div class="card-body">
                @if(empty($expenseBreakdown['labels']))
                    <p class="text-muted mb-0">Aucune dépense ce mois-ci.</p>
                @else
                    <canvas id="expenseChart" aria-label="Répartition des dépenses par catégorie" height="200"></canvas>
                    <table class="visually-hidden">
                        <caption>Dépenses par catégorie</caption>
                        <thead><tr><th>Catégorie</th><th>Montant</th></tr></thead>
                        <tbody>
                            @foreach($expenseBreakdown['labels'] as $i => $label)
                                <tr><td>{{ $label }}</td><td>{{ $expenseBreakdown['amounts'][$i] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Principaux élèves débiteurs</h5>
            </div>
            <div class="card-body p-0">
                @if($topDebtors->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucun élève débiteur.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($topDebtors as $row)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small fw-semibold">{{ $row['student']->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $row['student']->schoolClass?->name ?? '—' }}</div>
                                </div>
                                <span class="text-danger fw-semibold small">{{ number_format($row['total_due'], 0, ',', ' ') }} FCFA</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="p-2 text-center border-top">
                        <a href="{{ route('directeur.students.debtors') }}" class="small">Voir tous les élèves débiteurs</a>
                    </div>
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

    const pieCtx = document.getElementById('expenseChart');
    if (pieCtx && typeof Chart !== 'undefined') {
        // Palette fixe (ordre catégoriel, jamais recalculée dynamiquement),
        // dérivée de la palette ambre/cuivre déjà utilisée dans toute l'app.
        const palette = ['#f59e0b', '#dc2626', '#0891b2', '#7c3aed', '#16a34a', '#d97706', '#64748b', '#be185d'];

        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: @json($expenseBreakdown['labels']),
                datasets: [{
                    data: @json($expenseBreakdown['amounts']),
                    backgroundColor: palette,
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (item) {
                                return item.label + ' : ' + item.parsed.toLocaleString('fr-FR') + ' FCFA';
                            },
                        },
                    },
                },
            },
        });
    }
})();
</script>
@endpush
@endsection
