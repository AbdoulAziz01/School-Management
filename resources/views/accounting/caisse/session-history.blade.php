@extends('admin.layouts.app', ['sidebarView' => 'accounting.caisse.sidebar', 'navbarView' => 'accounting.caisse.navbar'])

@section('title', 'Historique de caisse')

@section('content')
<a href="{{ route('caisse.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="mb-4">
    <h1 class="h4 mb-0"><i class="fas fa-clock-rotate-left me-2"></i>Historique de caisse</h1>
    <p class="text-muted mb-0">Mes ouvertures et clôtures de caisse</p>
</div>

<div class="card">
    <div class="card-body">
        @if($sessions->isEmpty())
            <div class="alert alert-info mb-0">Aucune session de caisse enregistrée.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Ouverture</th>
                            <th>Clôture</th>
                            <th class="text-end">Fond initial</th>
                            <th class="text-end">Attendu</th>
                            <th class="text-end">Compté</th>
                            <th class="text-end">Écart</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                            <tr>
                                <td>{{ $session->opened_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $session->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-end">{{ number_format($session->opening_balance, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ $session->expected_closing_balance !== null ? number_format($session->expected_closing_balance, 0, ',', ' ') : '—' }}</td>
                                <td class="text-end">{{ $session->actual_closing_balance !== null ? number_format($session->actual_closing_balance, 0, ',', ' ') : '—' }}</td>
                                <td class="text-end {{ $session->difference && abs($session->difference) > 0.01 ? 'text-danger fw-semibold' : '' }}">
                                    {{ $session->difference !== null ? number_format($session->difference, 0, ',', ' ') : '—' }}
                                </td>
                                <td>
                                    @if($session->isOpen())
                                        <span class="badge bg-success">Ouverte</span>
                                    @else
                                        <span class="badge bg-secondary">Clôturée</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3 d-flex justify-content-center">
                    {{ $sessions->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
