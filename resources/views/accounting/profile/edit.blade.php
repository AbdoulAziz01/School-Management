@extends($layout, $layoutParams ?? [])

@section('title', 'Mon profil')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-user-circle me-2"></i>Mon profil</h1>
    <p class="text-muted mb-0">Matricule : {{ $accountingUser->identifier ?? '—' }}</p>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations personnelles</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route("{$routePrefix}.profile.update") }}">
                    @csrf
                    @method('PUT')

                    <x-admin.form-field type="text" name="name" label="Nom complet" :value="$accountingUser->name" required />
                    <x-admin.form-field type="email" name="email" label="Adresse email" :value="$accountingUser->email" required />
                    <x-admin.form-field type="tel" name="phone" label="Téléphone" :value="$accountingUser->phone" />

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Changer le mot de passe</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route("{$routePrefix}.profile.update-password") }}">
                    @csrf

                    <x-admin.form-field type="password" name="current_password" label="Mot de passe actuel" required />
                    <x-admin.form-field type="password" name="password" label="Nouveau mot de passe" required />
                    <x-admin.form-field type="password" name="password_confirmation" label="Confirmer le nouveau mot de passe" required />

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key me-1"></i> Mettre à jour le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
