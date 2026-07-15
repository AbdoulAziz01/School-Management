@php
    $portalPrefix = auth()->user()->role === \App\Models\User::ROLE_CAISSIER ? 'caisse' : 'comptable';
@endphp
@extends('admin.layouts.app', ['sidebarView' => "accounting.{$portalPrefix}.sidebar", 'navbarView' => "accounting.{$portalPrefix}.navbar"])

@section('title', 'Rechercher un reçu')

@section('content')
<div class="mb-4">
    <h1 class="h4 mb-0"><i class="fas fa-search me-2"></i>Rechercher un reçu</h1>
    <p class="text-muted mb-0">Par numéro de reçu ou nom d'élève</p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route($portalPrefix.'.receipts.search') }}">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="N° de reçu ou nom d'élève..." value="{{ $search }}" autofocus>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

@if($search !== '')
    <div class="card">
        <div class="card-body">
            @if($payments->isEmpty())
                <div class="alert alert-info mb-0">Aucun reçu trouvé pour « {{ $search }} ».</div>
            @else
                <div class="list-group">
                    @foreach($payments as $payment)
                        <a href="{{ route($portalPrefix.'.receipts.show', $payment) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><code>{{ $payment->receipt_number }}</code> — {{ $payment->student->name }}</div>
                                <div class="small text-muted">{{ $payment->paid_at->format('d/m/Y H:i') }} · {{ number_format($payment->amount, 0, ',', ' ') }} FCFA</div>
                            </div>
                            @if($payment->isCancelled())
                                <span class="badge bg-danger">Annulé</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
@endsection
