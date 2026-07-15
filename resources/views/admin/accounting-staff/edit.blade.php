@extends('admin.layouts.app')

@section('title', 'Modifier — '.$staffMember->name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Modifier le compte</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.accounting-staff.update', $staffMember) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <input type="text" class="form-control" value="{{ $roleLabels[$staffMember->role] ?? $staffMember->role }}" disabled>
                            <div class="form-text">Le rôle ne peut pas être modifié — supprimez le compte et recréez-en un si nécessaire.</div>
                        </div>

                        <x-admin.form-field type="text" name="name" label="Nom complet" :value="$staffMember->name" required />
                        <x-admin.form-field type="email" name="email" label="Adresse email" :value="$staffMember->email" required />
                        <x-admin.form-field type="tel" name="phone" label="Téléphone" :value="$staffMember->phone" />

                        <x-admin.form-field type="select" name="status" label="Statut" required>
                            <option value="approved" {{ $staffMember->status === 'approved' ? 'selected' : '' }}>Approuvé</option>
                            <option value="pending" {{ $staffMember->status === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="rejected" {{ $staffMember->status === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                        </x-admin.form-field>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('admin.accounting-staff.show', $staffMember) }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
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
