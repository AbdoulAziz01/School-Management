@extends('admin.layouts.app')

@section('title', 'Mon établissement')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Mon établissement</h1>
            <p class="text-muted mb-0">
                Le nom ci-dessous est celui de <strong>votre école</strong>. <strong>EduManager</strong> reste le nom de la plateforme.
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.school.settings.update') }}" enctype="multipart/form-data" class="card border-0 shadow-sm">
                @csrf
                @method('PUT')
                <div class="card-header bg-white">
                    <h5 class="mb-0">Identité de l'établissement</h5>
                </div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Logo de l'établissement</label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="border rounded d-flex align-items-center justify-content-center bg-light"
                                 style="width:72px;height:72px;overflow:hidden;">
                                @if($school->logo_data)
                                    <img src="{{ \App\Support\SchoolLogoStorage::dataUri($school) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;object-position:center;display:block;">
                                @else
                                    <i class="fas fa-school fa-2x text-muted"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">PNG, JPG, WEBP ou SVG — max 2 Mo. Stocké en base de données.</small>
                                @if($school->logo_data)
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                                        <label class="form-check-label" for="remove_logo">Supprimer le logo actuel</label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="name" class="form-label">Nom de l'établissement *</label>
                        <input type="text" id="name" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror"
                               value="{{ old('name', $school->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email de l'établissement</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $school->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Téléphone</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="city" class="form-label">Ville</label>
                        <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $school->city) }}">
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Adresse</label>
                        <textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $school->address) }}</textarea>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Équipe de direction (comptes en base)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Identifiant</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
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
                                            <span class="badge bg-primary">Administrateur</span>
                                        @else
                                            <span class="badge bg-info text-dark">Surveillant</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-success">{{ $member->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">Aucun compte admin ou surveillant.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white small text-muted">
                    Les identifiants sont enregistrés dans la table <code>users</code> (colonnes <code>identifier</code>, <code>role</code>, <code>school_id</code>).
                    Seul le super admin peut créer de nouveaux comptes depuis la plateforme.
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Informations système</h6>
                    <p class="mb-1"><strong>Code d'inscription :</strong><br><code>{{ $school->code }}</code></p>
                    <p class="mb-1"><strong>Identifiant technique :</strong><br><code>{{ $school->slug }}</code></p>
                    <p class="mb-0"><strong>ID école (BDD) :</strong><br><code>{{ $school->id }}</code></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
