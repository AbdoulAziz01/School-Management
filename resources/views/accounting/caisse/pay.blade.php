@extends('accounting.layouts.caisse')

@section('title', 'Encaissement — '.$student->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('caisse.students.search') }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour à la recherche
    </a>
    <h1 class="h4 mt-2 mb-0">{{ $student->name }}</h1>
    <p class="text-muted mb-0">
        {{ $student->identifier ?? '—' }}
        @if($student->schoolClass) · {{ $student->schoolClass->name }} @endif
    </p>
</div>

@if($invoices->isEmpty())
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-1"></i> Aucun montant en attente pour cet élève.
    </div>
@else
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Montants à payer</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('caisse.students.pay', $student) }}">
                @csrf

                <div class="table-responsive mb-3">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Motif</th>
                                <th>Échéance</th>
                                <th>Solde dû</th>
                                <th style="max-width: 180px;">Montant à encaisser</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        {{ $invoice->label }}
                                        @if($invoice->status === 'partial')
                                            <span class="badge bg-warning text-dark">Partiel</span>
                                        @endif
                                    </td>
                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                    <td>{{ number_format($invoice->balanceDue(), 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <input type="number" step="1" min="0" max="{{ $invoice->balanceDue() }}"
                                               name="amounts[{{ $invoice->id }}]" class="form-control"
                                               value="{{ old('amounts.'.$invoice->id) }}" placeholder="0">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-admin.form-field type="select" name="payment_method" label="Mode de paiement" required>
                    <option value="">Sélectionner...</option>
                    @foreach(\App\Models\Payment::METHOD_LABELS as $value => $label)
                        <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-admin.form-field>

                <x-admin.form-field type="text" name="notes" label="Remarque (optionnel)" :value="old('notes')" />

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-cash-register me-1"></i> Encaisser
                </button>
            </form>
        </div>
    </div>
@endif
@endsection
