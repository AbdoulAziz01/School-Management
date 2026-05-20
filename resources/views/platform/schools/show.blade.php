@extends('platform.layouts.app')

@section('title', $school->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">{{ $school->name }}</h1>
        <p class="text-muted mb-0">Identifiant : <code>{{ $school->slug }}</code> · Plateforme : <strong>EduManager</strong></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('platform.schools.edit', $school) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-pen me-1"></i> Modifier les informations
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
                <h6 class="text-muted">Code d'inscription</h6>
                <p class="h4"><code id="school-code">{{ $school->code }}</code></p>
                <form method="POST" action="{{ route('platform.schools.regenerate-code', $school) }}" class="mt-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Régénérer le code ? L\'ancien ne fonctionnera plus.')">
                        Régénérer le code
                    </button>
                </form>
                <p class="small text-muted mt-3 mb-0">Communiquez ce code aux élèves et enseignants pour s'inscrire.</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Statistiques</h6>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h3 mb-0">{{ $school->users_count }}</div>
                        <small class="text-muted">Utilisateurs</small>
                    </div>
                    <div class="col-4">
                        <div class="h3 mb-0">{{ $school->students_count }}</div>
                        <small class="text-muted">Élèves</small>
                    </div>
                    <div class="col-4">
                        <div class="h3 mb-0">{{ $school->teachers_count }}</div>
                        <small class="text-muted">Enseignants</small>
                    </div>
                </div>
                <hr>
                <p class="mb-1"><strong>Statut :</strong>
                    @if($school->is_active)
                        <span class="badge bg-success">Actif</span>
                    @else
                        <span class="badge bg-secondary">Inactif</span>
                    @endif
                </p>
                @if($school->email)<p class="mb-1"><strong>Email :</strong> {{ $school->email }}</p>@endif
                @if($school->phone)<p class="mb-1"><strong>Téléphone :</strong> {{ $school->phone }}</p>@endif
                @if($school->city)<p class="mb-0"><strong>Ville :</strong> {{ $school->city }}</p>@endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Administrateurs</h5>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($admins as $admin)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <span>{{ $admin->name }}<br><small class="text-muted">{{ $admin->email }}</small></span>
                            <code>{{ $admin->identifier }}</code>
                        </div>
                        <form method="POST" action="{{ route('platform.schools.admins.reset-password', [$school, $admin]) }}" class="row g-2 align-items-end">
                            @csrf
                            @method('PATCH')
                            <div class="col-md-5">
                                <label class="form-label small mb-0">Nouveau mot de passe</label>
                                <input type="password" name="admin_password" class="form-control form-control-sm" required minlength="8">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small mb-0">Confirmer</label>
                                <input type="password" name="admin_password_confirmation" class="form-control form-control-sm" required minlength="8">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">Réinitialiser</button>
                            </div>
                        </form>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aucun administrateur.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Ajouter un administrateur</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.schools.admins.store', $school) }}">
                    @csrf
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
                    <button type="submit" class="btn btn-primary btn-sm">Créer l'admin</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <strong>Accès administrateur école :</strong>
    <ol class="mb-0 ps-3">
        <li>Se <strong>déconnecter</strong> du compte super admin si besoin.</li>
        <li>Aller sur <a href="{{ route('login') }}" target="_blank">{{ url('/login') }}</a>.</li>
        <li>Se connecter avec l'<strong>email</strong> ou l'<strong>identifiant</strong> admin + mot de passe défini à la création.</li>
        <li>Accès : <code>/admin/dashboard</code> — données limitées à « {{ $school->name }} ».</li>
    </ol>
    @if(session('new_admin_login'))
        <hr>
        <p class="mb-0"><strong>Compte admin créé :</strong>
            {{ session('new_admin_login.email') }} · identifiant <code>{{ session('new_admin_login.identifier') }}</code>
        </p>
    @endif
    @if(!$school->is_active)
        <hr>
        <p class="mb-0 text-danger"><strong>Attention :</strong> cet établissement est inactif — l'admin ne pourra pas se connecter tant qu'il n'est pas activé.</p>
    @endif
</div>
@endsection
