@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $student->name.' — Directeur')

@section('content')
<a href="{{ route('directeur.school.students.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Élèves
</a>

@include('accounting.shared.students._profile-header', ['student' => $student])

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-purple"><i class="fas fa-star"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Moyenne générale</div>
                <div class="kpi-value kpi-value-sm">
                    @if($generalAverage !== null)
                        {{ round($generalAverage['normalized'], 2) }}<span class="kpi-value-suffix">/{{ $referenceMaxGrade }}</span>
                    @else
                        <span class="kpi-value-suffix">Pas encore de notes</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-green"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Présences enregistrées</div>
                <div class="kpi-value kpi-value-sm text-success">{{ $attendanceStats['present'] }} <span class="kpi-value-suffix">présent(s)</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-red"><i class="fas fa-user-clock"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Absences / Retards</div>
                <div class="kpi-value kpi-value-sm"><span class="text-danger">{{ $attendanceStats['absent'] }}</span> / <span class="text-warning">{{ $attendanceStats['late'] }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Notes par matière --}}
    <div class="col-lg-7">
        <div class="panel-card h-100">
            <div class="panel-card-header"><i class="fas fa-book me-2 text-warning"></i>Notes par matière</div>
            <div class="p-0">
                @if($bySubject->isEmpty())
                    <div class="empty-state py-4"><i class="fas fa-book"></i><p class="mb-0">Aucune note enregistrée cette année.</p></div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 data-table">
                            <thead>
                                <tr>
                                    <th>Matière</th>
                                    <th>Moyenne</th>
                                    <th>Nb. notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bySubject as $row)
                                    <tr>
                                        <td>
                                            @if($student->schoolClass)
                                                <a href="{{ route('directeur.school.subjects.grades', [$student->schoolClass, $row['subject']]) }}" class="fw-semibold text-decoration-none">
                                                    {{ $row['subject']->name }}
                                                </a>
                                            @else
                                                {{ $row['subject']->name }}
                                            @endif
                                        </td>
                                        <td>{{ $row['average'] !== null ? $row['average'].'/'.$row['max_grade'] : '—' }}</td>
                                        <td class="text-muted">{{ $row['count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Présences récentes --}}
    <div class="col-lg-5">
        <div class="panel-card h-100">
            <div class="panel-card-header"><i class="fas fa-calendar-check me-2 text-warning"></i>Présences récentes</div>
            <div class="p-0">
                @if($attendance->isEmpty())
                    <div class="empty-state py-4"><i class="fas fa-calendar-check"></i><p class="mb-0">Aucun enregistrement de présence.</p></div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($attendance->take(15) as $row)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="small">{{ $row->date->format('d/m/Y') }}</span>
                                @php
                                    $badge = match($row->status) {
                                        'present' => 'status-badge-success',
                                        'absent' => 'status-badge-danger',
                                        'late' => 'status-badge-warning',
                                        default => 'status-badge-neutral',
                                    };
                                @endphp
                                <span class="status-badge {{ $badge }}">{{ ucfirst($row->status) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Factures --}}
    <div class="col-lg-7">
        <div class="panel-card h-100">
            <div class="panel-card-header"><i class="fas fa-file-invoice me-2 text-warning"></i>Factures</div>
            <div class="p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 data-table">
                        <thead>
                            <tr>
                                <th>Motif</th>
                                <th>Échéance</th>
                                <th class="text-end">Dû</th>
                                <th class="text-end">Payé</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->label }}</td>
                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                    <td class="text-end">{{ number_format($invoice->amount_due, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($invoice->amount_paid, 0, ',', ' ') }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($invoice->status) {
                                                'paid' => 'status-badge-success',
                                                'partial' => 'status-badge-warning',
                                                'cancelled' => 'status-badge-neutral',
                                                default => 'status-badge-danger',
                                            };
                                        @endphp
                                        <span class="status-badge {{ $badgeClass }}">{{ $invoice->statusLabel() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center py-3">Aucune facture.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Paiements --}}
    <div class="col-lg-5">
        <div class="panel-card h-100">
            <div class="panel-card-header"><i class="fas fa-receipt me-2 text-warning"></i>Historique des paiements</div>
            <div class="p-0">
                @if($payments->isEmpty())
                    <div class="empty-state py-4"><i class="fas fa-receipt"></i><p class="mb-0">Aucun paiement enregistré.</p></div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($payments->take(10) as $payment)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="small">{{ $payment->paid_at?->format('d/m/Y') }}</span>
                                <span class="text-success fw-semibold small">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
