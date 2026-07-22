@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $student->name.' — Directeur')

@section('content')
<a href="{{ route('directeur.school.students.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Élèves
</a>

@include('accounting.shared.students._profile-header', ['student' => $student])

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Moyenne générale</div>
            <div class="h5 mb-0">
                @if($generalAverage !== null)
                    {{ round($generalAverage['normalized'], 2) }}/{{ $referenceMaxGrade }}
                @else
                    <span class="text-muted fs-6">Pas encore de notes</span>
                @endif
            </div>
        </div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Présences enregistrées</div>
            <div class="h5 mb-0 text-success">{{ $attendanceStats['present'] }} <span class="fs-6 text-muted">présent(s)</span></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Absences / Retards</div>
            <div class="h5 mb-0"><span class="text-danger">{{ $attendanceStats['absent'] }}</span> / <span class="text-warning">{{ $attendanceStats['late'] }}</span></div>
        </div></div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Notes par matière --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Notes par matière</h5></div>
            <div class="card-body p-0">
                @if($bySubject->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucune note enregistrée cette année.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
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
                                                <a href="{{ route('directeur.school.subjects.grades', [$student->schoolClass, $row['subject']]) }}">
                                                    {{ $row['subject']->name }}
                                                </a>
                                            @else
                                                {{ $row['subject']->name }}
                                            @endif
                                        </td>
                                        <td>{{ $row['average'] !== null ? $row['average'].'/'.$row['max_grade'] : '—' }}</td>
                                        <td>{{ $row['count'] }}</td>
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
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Présences récentes</h5></div>
            <div class="card-body p-0">
                @if($attendance->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucun enregistrement de présence.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($attendance->take(15) as $row)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="small">{{ $row->date->format('d/m/Y') }}</span>
                                @php
                                    $badge = match($row->status) {
                                        'present' => 'bg-success',
                                        'absent' => 'bg-danger',
                                        'late' => 'bg-warning text-dark',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ ucfirst($row->status) }}</span>
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
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Factures</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                                'paid' => 'bg-success',
                                                'partial' => 'bg-warning text-dark',
                                                'cancelled' => 'bg-secondary',
                                                default => 'bg-danger',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $invoice->statusLabel() }}</span>
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
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Historique des paiements</h5></div>
            <div class="card-body p-0">
                @if($payments->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucun paiement enregistré.</p>
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
@endsection
