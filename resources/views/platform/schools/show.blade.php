@extends('platform.layouts.app')

@section('title', $school->name)

@section('content')
@php $schoolLogo = \App\Support\SchoolLogoStorage::dataUri($school); @endphp
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="width:64px;height:64px;overflow:hidden;">
            @if($schoolLogo)
                <img src="{{ $schoolLogo }}" alt="Logo" class="img-fluid" style="max-height:60px;">
            @else
                <i class="fas fa-school fa-2x text-muted"></i>
            @endif
        </div>
        <div>
            <h1 class="h3 mb-1">{{ $school->name }}</h1>
            <p class="text-muted mb-0">ID BDD : <code>{{ $school->id }}</code> · Slug : <code>{{ $school->slug }}</code></p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('platform.schools.edit', $school) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-pen me-1"></i> Modifier
        </a>
        <form method="POST" action="{{ route('platform.schools.toggle-active', $school) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $school->is_active ? 'btn-warning' : 'btn-success' }}">
                {{ $school->is_active ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Logo (stocké en BDD)</h6>
                <form method="POST" action="{{ route('platform.schools.update', $school) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $school->name }}">
                    <input type="hidden" name="is_active" value="{{ $school->is_active ? '1' : '0' }}">
                    <input type="file" name="logo" class="form-control form-control-sm mb-2" accept="image/*">
                    @if($school->logo_data)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                            <label class="form-check-label" for="remove_logo">Supprimer le logo</label>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Mettre à jour le logo</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Code d'inscription</h6>
                <p class="h4"><code>{{ $school->code }}</code></p>
                <form method="POST" action="{{ route('platform.schools.regenerate-code', $school) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Régénérer le code ?')">Régénérer</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="row">
                    <div class="col-4"><div class="h4 mb-0">{{ $school->users_count }}</div><small>Total</small></div>
                    <div class="col-4"><div class="h4 mb-0">{{ $school->students_count }}</div><small>Élèves</small></div>
                    <div class="col-4"><div class="h4 mb-0">{{ $school->teachers_count }}</div><small>Profs</small></div>
                </div>
                <p class="mt-2 mb-0">
                    @if($school->is_active)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Inactif</span>@endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Admin & surveillants (table users)</h5>
                <span class="badge bg-secondary">{{ $staffMembers->count() }} compte(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Identifiant</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffMembers as $member)
                            <tr>
                                <td><code>{{ $member->identifier }}</code></td>
                                <td>{{ $member->name }}</td>
                                <td><small>{{ $member->email }}</small></td>
                                <td>
                                    @if($member->role === 'admin')
                                        <span class="badge bg-primary">Admin</span>
                                    @else
                                        <span class="badge bg-info text-dark">Surveillant</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-3">Aucun compte.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($staffMembers as $member)
                    <li class="list-group-item">
                        <small class="text-muted">Réinitialiser mot de passe — {{ $member->identifier }}</small>
                        <form method="POST" action="{{ route('platform.schools.admins.reset-password', [$school, $member]) }}" class="row g-2 align-items-end mt-1">
                            @csrf
                            @method('PATCH')
                            <div class="col-md-5">
                                <input type="password" name="admin_password" class="form-control form-control-sm" placeholder="Nouveau mot de passe" required minlength="8">
                            </div>
                            <div class="col-md-5">
                                <input type="password" name="admin_password_confirmation" class="form-control form-control-sm" placeholder="Confirmer" required minlength="8">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">OK</button>
                            </div>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Ajouter admin ou surveillant</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.schools.admins.store', $school) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="staff_role" class="form-select" required>
                            <option value="admin">Administrateur (ADM…)</option>
                            <option value="surveillant">Surveillant (SUR…)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="admin_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="admin_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="admin_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer</label>
                        <input type="password" name="admin_password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Créer le compte</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('new_admin_login'))
<div class="alert alert-success mt-4">
    Compte créé : {{ session('new_admin_login.email') }} · identifiant <code>{{ session('new_admin_login.identifier') }}</code>
</div>
@endif
@endsection
