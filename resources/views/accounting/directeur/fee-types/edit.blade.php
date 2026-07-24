@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Modifier — '.$feeType->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="panel-card">
            <div class="panel-card-header"><i class="fas fa-edit me-2 text-warning"></i>Modifier le type de frais</div>
            <div class="p-4">
                <form method="POST" action="{{ route('directeur.fee-types.update', $feeType) }}">
                    @csrf
                    @method('PUT')

                    <x-admin.form-field type="text" name="code" label="Code" :value="$feeType->code" required />
                    <x-admin.form-field type="text" name="name" label="Nom" :value="$feeType->name" required />

                    <x-admin.form-field type="select" name="category" label="Catégorie" required>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category', $feeType->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-admin.form-field>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_recurring" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_recurring" name="is_recurring" value="1"
                               @checked(old('is_recurring', $feeType->is_recurring))>
                        <label class="form-check-label" for="is_recurring">
                            Frais récurrent (dû chaque mois, ex. mensualité)
                        </label>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn-pill-primary">
                            <i class="fas fa-save"></i>Enregistrer
                        </button>
                        <a href="{{ route('directeur.fee-types.amounts', $feeType) }}" class="btn-pill-outline">
                            <i class="fas fa-coins"></i>Voir les montants
                        </a>
                        <a href="{{ route('directeur.fee-types.index') }}" class="btn-pill-outline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
