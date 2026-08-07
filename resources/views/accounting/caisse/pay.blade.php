@extends('admin.layouts.app', ['sidebarView' => 'accounting.caisse.sidebar', 'navbarView' => 'accounting.caisse.navbar'])

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
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Montants à payer</h5>
            <div class="btn-group btn-group-sm" role="group" aria-label="Sélection rapide">
                <button type="button" class="btn btn-outline-primary" id="select-current-month">
                    <i class="fas fa-calendar-day me-1"></i> Ce mois-ci uniquement
                </button>
                <button type="button" class="btn btn-outline-primary" id="select-all-invoices">
                    <i class="fas fa-calendar-check me-1"></i> Toute l'année
                </button>
                <button type="button" class="btn btn-outline-secondary" id="select-none-invoices">
                    <i class="fas fa-eraser me-1"></i> Effacer
                </button>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                <i class="fas fa-hand-pointer me-1"></i>
                Cliquez sur un mois pour le sélectionner — tous les mois qui le précèdent sont automatiquement
                cochés avec leur montant exact, pour éviter toute erreur de saisie. Décochez pour revenir en arrière.
            </p>

            <form method="POST" action="{{ route('caisse.students.pay', $student) }}" id="pay-form">
                @csrf

                <div class="table-responsive mb-3">
                    <table class="table table-hover align-middle mb-0" id="invoices-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;"></th>
                                <th>Motif</th>
                                <th>Échéance</th>
                                <th class="text-end">Solde dû</th>
                                <th style="max-width: 180px;">Montant à encaisser</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                @php
                                    $isOverdue = $invoice->due_date->lt(now()->startOfMonth());
                                    $isCurrentMonth = $invoice->due_date->isSameMonth(now());
                                @endphp
                                <tr class="invoice-row" data-index="{{ $loop->index }}" data-current-month="{{ $isCurrentMonth ? '1' : '0' }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input invoice-select" data-index="{{ $loop->index }}">
                                    </td>
                                    <td>
                                        {{ $invoice->label }}
                                        @if($invoice->status === 'partial')
                                            <span class="badge bg-warning text-dark">Partiel</span>
                                        @endif
                                        @if($isOverdue)
                                            <span class="badge bg-danger">En retard</span>
                                        @elseif($isCurrentMonth)
                                            <span class="badge bg-warning text-dark">Ce mois-ci</span>
                                        @else
                                            <span class="badge bg-light text-muted border">À venir</span>
                                        @endif
                                    </td>
                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                    <td class="text-end">{{ number_format($invoice->balanceDue(), 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <input type="number" step="1" min="0" max="{{ $invoice->balanceDue() }}"
                                               name="amounts[{{ $invoice->id }}]" class="form-control invoice-amount"
                                               data-index="{{ $loop->index }}" data-balance="{{ $invoice->balanceDue() }}"
                                               value="{{ old('amounts.'.$invoice->id) }}" placeholder="0">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="3"></td>
                                <td class="text-end"><span id="selected-count" class="text-muted fw-normal small"></span></td>
                                <td><span id="total-to-collect">0 FCFA</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <x-admin.form-field type="select" name="payment_method" label="Mode de paiement" required>
                    <option value="">Sélectionner...</option>
                    @foreach(\App\Models\Payment::METHOD_LABELS as $value => $label)
                        <option value="{{ $value }}"
                                data-requires-reference="{{ in_array($value, \App\Models\Payment::METHODS_REQUIRING_REFERENCE, true) ? '1' : '0' }}"
                                data-requires-bank="{{ in_array($value, \App\Models\Payment::METHODS_REQUIRING_BANK, true) ? '1' : '0' }}"
                                {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-admin.form-field>

                {{-- Champs affichés uniquement pour Chèque/Virement/Wave/Orange Money — traçabilité du paiement --}}
                <div id="payment-reference-field" class="d-none">
                    <x-admin.form-field type="text" name="payment_reference"
                        label="Référence" :value="old('payment_reference')"
                        help="Numéro de chèque, référence de virement, ou ID de transaction selon le mode choisi." />
                </div>
                <div id="payment-bank-field" class="d-none">
                    <x-admin.form-field type="text" name="payment_bank" label="Banque" :value="old('payment_bank')"
                        help="Banque émettrice du chèque ou du virement." />
                </div>

                <x-admin.form-field type="text" name="notes" label="Remarque (optionnel)" :value="old('notes')" />

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-cash-register me-1"></i> Encaisser
                </button>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script>
    (function () {
        var select = document.getElementById('payment_method');
        var referenceField = document.getElementById('payment-reference-field');
        var bankField = document.getElementById('payment-bank-field');
        var referenceLabel = referenceField ? referenceField.querySelector('label') : null;
        var referenceInput = document.getElementById('payment_reference');

        var referenceLabels = {
            cheque: 'Numéro de chèque',
            virement: 'Référence de virement',
            wave: 'ID de transaction',
            orange_money: 'ID de transaction',
        };

        function sync() {
            if (!select) return;
            var option = select.options[select.selectedIndex];
            var needsReference = option && option.dataset.requiresReference === '1';
            var needsBank = option && option.dataset.requiresBank === '1';

            referenceField.classList.toggle('d-none', !needsReference);
            bankField.classList.toggle('d-none', !needsBank);
            if (referenceInput) referenceInput.required = needsReference;

            if (referenceLabel && select.value) {
                var text = referenceLabels[select.value] || 'Référence';
                referenceLabel.childNodes[0].nodeValue = text + ' ';
            }
        }

        if (select) {
            select.addEventListener('change', sync);
            sync();
        }
    })();

    // Sélection des mois à payer : cocher un mois coche automatiquement tous
    // les mois précédents (paiement dans l'ordre, jamais de saut), avec le
    // montant exact rempli tout seul — plus de saisie manuelle sujette à erreur.
    (function () {
        var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.invoice-select'));
        if (checkboxes.length === 0) return;

        var amountInputs = Array.prototype.slice.call(document.querySelectorAll('.invoice-amount'));
        var totalEl = document.getElementById('total-to-collect');
        var countEl = document.getElementById('selected-count');

        function formatFcfa(amount) {
            var rounded = Math.round(amount);
            return rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
        }

        function amountInputFor(index) {
            return amountInputs.filter(function (el) { return el.dataset.index === String(index); })[0];
        }

        function applyCascade(upToIndex, checked) {
            checkboxes.forEach(function (cb) {
                var idx = parseInt(cb.dataset.index, 10);
                if (checked) {
                    cb.checked = idx <= upToIndex;
                } else if (idx >= upToIndex) {
                    // Décocher un mois décoche aussi tous les mois suivants
                    // (on ne laisse jamais un mois payé après un mois non payé).
                    cb.checked = false;
                }
            });
            refreshAmounts();
        }

        function refreshAmounts() {
            var total = 0;
            var count = 0;

            checkboxes.forEach(function (cb) {
                var idx = cb.dataset.index;
                var input = amountInputFor(idx);
                if (!input) return;

                if (cb.checked) {
                    input.value = input.dataset.balance;
                    count++;
                    total += parseFloat(input.dataset.balance) || 0;
                } else {
                    input.value = '';
                }
            });

            totalEl.textContent = formatFcfa(total);
            countEl.textContent = count > 0 ? count + ' mois sélectionné(s)' : '';
        }

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                applyCascade(parseInt(cb.dataset.index, 10), cb.checked);
            });
        });

        // Un montant modifié manuellement (paiement partiel d'un mois) ne
        // doit pas être écrasé tant que la case reste cochée.
        amountInputs.forEach(function (input) {
            input.addEventListener('input', function () {
                var total = 0;
                checkboxes.forEach(function (cb) {
                    if (!cb.checked) return;
                    var amt = amountInputFor(cb.dataset.index);
                    total += parseFloat(amt.value) || 0;
                });
                totalEl.textContent = formatFcfa(total);
            });
        });

        document.getElementById('select-current-month').addEventListener('click', function () {
            // S'il n'y a pas de ligne pile sur le mois en cours (ex. tout est
            // déjà en retard, ou tout est encore à venir), on retombe sur le
            // premier mois impayé — c'est toujours celui à régler en priorité.
            var row = document.querySelector('.invoice-row[data-current-month="1"]');
            var idx = row ? parseInt(row.dataset.index, 10) : 0;
            applyCascade(idx, true);
        });

        document.getElementById('select-all-invoices').addEventListener('click', function () {
            applyCascade(checkboxes.length - 1, true);
        });

        document.getElementById('select-none-invoices').addEventListener('click', function () {
            checkboxes.forEach(function (cb) { cb.checked = false; });
            refreshAmounts();
        });

        refreshAmounts();
    })();
</script>
@endpush
@endsection
