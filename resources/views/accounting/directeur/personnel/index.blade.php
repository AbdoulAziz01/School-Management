@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Personnel & Élèves')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i>Personnel &amp; Élèves</h1>
    <p class="text-muted mb-0">Vue d'ensemble de qui travaille et étudie dans votre établissement</p>
</div>

<div class="row g-3">
    @foreach($counts as $key => $group)
        <div class="col-6 col-lg-4">
            <a href="{{ route('directeur.personnel.show', $key) }}" class="kpi-tile">
                <div class="kpi-icon kpi-icon-amber"><i class="fas {{ $group['icon'] }}"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">{{ $group['label'] }}</div>
                    <div class="kpi-value">{{ $group['count'] }}</div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<p class="text-muted small mt-4">
    <i class="fas fa-info-circle me-1"></i>
    Vous pouvez créer directement vos comptes comptable et caissier. Les autres rôles (admin,
    surveillant, enseignant, élève) restent gérés depuis le panneau d'administration de l'établissement.
</p>

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
