@extends('admin.layouts.app', ['sidebarView' => 'accounting.caisse.sidebar', 'navbarView' => 'accounting.caisse.navbar'])

@section('title', 'Ouvrir la caisse')

@section('content')
<div class="text-center mb-4">
    <h1 class="h3 mb-1"><i class="fas fa-cash-register me-2"></i>Ouvrir la caisse</h1>
    <p class="text-muted mb-0">Renseignez le fond de caisse avant de commencer les encaissements.</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('caisse.session.open') }}">
            @csrf
            <x-admin.form-field type="number" name="opening_balance" label="Fond de caisse (FCFA)" :value="old('opening_balance', 0)" required />

            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-unlock me-1"></i> Ouvrir la caisse
            </button>
        </form>
    </div>
</div>
@endsection
