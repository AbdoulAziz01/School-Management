@extends('admin.layouts.app', ['sidebarView' => 'accounting.caisse.sidebar', 'navbarView' => 'accounting.caisse.navbar'])

@section('title', 'Guichet')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('caisse.sessions.index') }}" class="card h-100 text-center stat-card-link">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Fond de caisse</div>
                <div class="h5 mb-0">{{ number_format($summary['opening_balance'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('caisse.history') }}" class="card h-100 text-center stat-card-link">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Encaissé aujourd'hui</div>
                <div class="h5 mb-0 text-success">{{ number_format($summary['encaissements'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('caisse.history') }}" class="card h-100 text-center stat-card-link">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Solde attendu</div>
                <div class="h5 mb-0">{{ number_format($summary['expected_balance'], 0, ',', ' ') }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('caisse.history') }}" class="card h-100 text-center stat-card-link">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Paiements</div>
                <div class="h5 mb-0">{{ $summary['payments_count'] }}</div>
            </div>
        </a>
    </div>
</div>

@push('styles')
<style>
    .stat-card-link {
        display: block;
        text-decoration: none !important;
        color: inherit !important;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .stat-card-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
    }
</style>
@endpush

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('caisse.students.search') }}">
            <label class="form-label fw-semibold">Rechercher un élève</label>
            <div class="input-group input-group-lg">
                <input type="text" name="q" class="form-control" placeholder="Nom ou matricule..." autofocus>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center">
    <a href="{{ route('caisse.history') }}" class="btn btn-outline-secondary">
        <i class="fas fa-history me-1"></i> Historique de mes paiements
    </a>
    <a href="{{ route('caisse.session.close-form') }}" class="btn btn-outline-danger">
        <i class="fas fa-lock me-1"></i> Clôturer la caisse
    </a>
</div>
@endsection
