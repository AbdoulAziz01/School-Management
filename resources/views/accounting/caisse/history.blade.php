@extends('admin.layouts.app', ['sidebarView' => 'accounting.caisse.sidebar', 'navbarView' => 'accounting.caisse.navbar'])

@section('title', $scope === 'today' ? 'Paiements du jour' : 'Historique de mes paiements')

@section('content')
<div class="mb-4">
    <h1 class="h4 mb-0"><i class="fas fa-history me-2"></i>{{ $scope === 'today' ? 'Paiements du jour' : 'Historique des paiements' }}</h1>
    @if($scope === 'today')
        <p class="text-muted mb-0">{{ now()->locale('fr')->translatedFormat('d F Y') }} — <a href="{{ route('caisse.history') }}">Voir tout l'historique</a></p>
    @endif
</div>

<div class="card">
    <div class="card-body">
        @if($payments->isEmpty())
            <div class="alert alert-info mb-0">Aucun paiement enregistré pour le moment.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Reçu</th>
                            <th>Élève</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td><code>{{ $payment->receipt_number }}</code></td>
                                <td>{{ $payment->student->name }}</td>
                                <td>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                                <td>{{ $payment->methodLabel() }}</td>
                                <td>{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($payment->isCancelled())
                                        <span class="badge bg-danger">Annulé</span>
                                    @else
                                        <span class="badge bg-success">Complété</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('caisse.receipts.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3 d-flex justify-content-center">
                    {{ $payments->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
