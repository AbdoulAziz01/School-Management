@extends('admin.layouts.app')

@section('title', 'Mon établissement')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Mon établissement</h1>
        <p class="text-muted mb-0">
            La fiche officielle (type, direction, autorisation, paramètres système) est gérée par le
            <strong>super administrateur</strong> de la plateforme {{ $platformName }}.
            Vous pouvez mettre à jour le <strong>contact</strong>, la <strong>présentation</strong> et le <strong>logo</strong>.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Fiche officielle (lecture seule) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2 text-muted"></i>Fiche officielle</h5>
                    <span class="badge bg-secondary">Lecture seule</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 small">
                        <div class="col-md-6">
                            <span class="text-muted d-block">Nom</span>
                            <strong>{{ $school->name }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Type</span>
                            <strong>{{ $school->establishmentTypeLabel() ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Devise / slogan</span>
                            <strong>{{ $school->motto ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">N° autorisation</span>
                            <strong>{{ $school->authorization_number ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Proviseur / Directeur</span>
                            <strong>{{ $school->director_name ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Censeur / Adjoint</span>
                            <strong>{{ $school->deputy_director_name ?? '—' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block">Région</span>
                            <strong>{{ $school->region ?? '—' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block">Département</span>
                            <strong>{{ $school->department ?? '—' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block">Langue</span>
                            <strong>{{ \App\Models\School::LOCALES[$school->locale] ?? $school->locale ?? '—' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact & communication (modifiable) --}}
            <form method="POST" action="{{ route('admin.school.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-edit me-2 text-warning"></i>Contact & communication</h5>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <label class="form-label">Logo de l'établissement</label>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="border rounded d-flex align-items-center justify-content-center bg-light"
                                     style="width:72px;height:72px;overflow:hidden;">
                                    @if($school->logo_data)
                                        <img src="{{ \App\Support\SchoolLogoStorage::dataUri($school) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
                                    @else
                                        <i class="fas fa-school fa-2x text-muted"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if($school->logo_data)
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                                            <label class="form-check-label" for="remove_logo">Supprimer le logo</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email de l'établissement</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $school->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="secretariat_email" class="form-label">Email secrétariat</label>
                            <input type="email" id="secretariat_email" name="secretariat_email" class="form-control"
                                   value="{{ old('secretariat_email', $school->secretariat_email) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="phone" class="form-label">Téléphone principal</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="phone_secondary" class="form-label">Second téléphone</label>
                            <input type="text" id="phone_secondary" name="phone_secondary" class="form-control"
                                   value="{{ old('phone_secondary', $school->phone_secondary) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="whatsapp" class="form-label">WhatsApp</label>
                            <input type="text" id="whatsapp" name="whatsapp" class="form-control" value="{{ old('whatsapp', $school->whatsapp) }}">
                        </div>
                        <div class="col-12">
                            <label for="website" class="form-label">Site web</label>
                            <input type="url" id="website" name="website" class="form-control @error('website') is-invalid @enderror"
                                   value="{{ old('website', $school->website) }}">
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">Ville / commune</label>
                            <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $school->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="district" class="form-label">Quartier / zone</label>
                            <input type="text" id="district" name="district" class="form-control" value="{{ old('district', $school->district) }}">
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Adresse complète</label>
                            <textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $school->address) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Présentation de l'établissement</label>
                            <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $school->description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="secretariat_hours" class="form-label">Horaires d'accueil du secrétariat</label>
                            <textarea id="secretariat_hours" name="secretariat_hours" class="form-control" rows="2">{{ old('secretariat_hours', $school->secretariat_hours) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </form>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Équipe de direction (comptes)</h5></div>
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
                                        <span class="badge bg-{{ $member->role === 'admin' ? 'primary' : 'info' }}">
                                            {{ $member->role === 'admin' ? 'Administrateur' : 'Surveillant' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Aucun compte.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-warning"><i class="fas fa-shield-alt me-1"></i> Super administrateur</h6>
                    <p class="small text-muted mb-0">
                        Pour modifier le type d'établissement, la direction, le numéro d'autorisation,
                        la région ou les paramètres système, contactez le super administrateur de {{ $platformName }}.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="text-muted">Informations système</h6>
                    <p class="mb-1 small"><strong>Code d'inscription :</strong><br><code>{{ $school->code }}</code></p>
                    <p class="mb-0 small"><strong>Identifiant technique :</strong><br><code>{{ $school->slug }}</code></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
