@extends('admin.layouts.app')

@section('title', 'Compte Comptabilité — '.$staffMember->name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0">{{ $staffMember->name }}</h1>
                <a href="{{ route('admin.accounting-staff.edit', $staffMember) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit me-1"></i> Modifier
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Rôle</dt>
                        <dd class="col-sm-8"><span class="badge bg-info">{{ $roleLabels[$staffMember->role] ?? $staffMember->role }}</span></dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $staffMember->email }}</dd>

                        <dt class="col-sm-4">Téléphone</dt>
                        <dd class="col-sm-8">{{ $staffMember->phone ?? 'Non renseigné' }}</dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @if($staffMember->status == 'approved')
                                <span class="badge bg-success">Approuvé</span>
                            @elseif($staffMember->status == 'pending')
                                <span class="badge bg-warning">En attente</span>
                            @else
                                <span class="badge bg-danger">Rejeté</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Identifiants de connexion</h5>
                </div>
                <div class="card-body">
                    @include('admin.partials._user-credentials-panel', [
                        'user' => $staffMember,
                        'roleLabel' => strtolower($roleLabels[$staffMember->role] ?? 'compte'),
                        'regenerateRoute' => route('admin.accounting-staff.regenerate-credentials', $staffMember),
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
