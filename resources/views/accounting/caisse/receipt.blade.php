@extends('admin.layouts.app', ['sidebarView' => 'accounting.caisse.sidebar', 'navbarView' => 'accounting.caisse.navbar'])

@section('title', 'Reçu — '.$payment->receipt_number)

@section('content')
<div class="mb-4 d-print-none">
    <a href="{{ route('caisse.dashboard') }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour au guichet
    </a>
</div>

<div class="card">
    <div class="card-header text-center">
        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Reçu de paiement</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-3">
            <div class="h4 mb-0">{{ $payment->receipt_number }}</div>
            <div class="text-muted small">{{ $payment->paid_at->format('d/m/Y H:i') }}</div>
        </div>

        <dl class="row mb-3">
            <dt class="col-5">Élève</dt>
            <dd class="col-7">{{ $payment->student->name }} ({{ $payment->student->identifier ?? '—' }})</dd>
            <dt class="col-5">Mode de paiement</dt>
            <dd class="col-7">{{ $payment->methodLabel() }}</dd>
            @if($payment->payment_reference)
                <dt class="col-5">{{ $payment->referenceLabel() }}</dt>
                <dd class="col-7">{{ $payment->payment_reference }}</dd>
            @endif
            @if($payment->payment_bank)
                <dt class="col-5">Banque</dt>
                <dd class="col-7">{{ $payment->payment_bank }}</dd>
            @endif
            <dt class="col-5">Encaissé par</dt>
            <dd class="col-7">{{ $payment->recordedBy->name }}</dd>
        </dl>

        <table class="table table-sm">
            <thead class="table-light">
                <tr><th>Motif</th><th class="text-end">Montant</th></tr>
            </thead>
            <tbody>
                @foreach($payment->allocations as $allocation)
                    <tr>
                        <td>{{ $allocation->studentInvoice->label }}</td>
                        <td class="text-end">{{ number_format($allocation->amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td>Total</td>
                    <td class="text-end">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tfoot>
        </table>

        <div class="d-flex gap-2 d-print-none mt-3">
            <a href="{{ route('caisse.receipts.pdf', $payment) }}" target="_blank" class="btn btn-primary">
                <i class="fas fa-file-pdf me-1"></i> Télécharger / Imprimer le PDF
            </a>
            <a href="{{ route('caisse.students.show', $payment->student) }}" class="btn btn-outline-secondary">
                Nouvel encaissement pour cet élève
            </a>
        </div>
    </div>
</div>
@endsection
