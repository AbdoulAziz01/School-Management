@extends('admin.layouts.app')

@section('title', 'Importer des enseignants')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <p class="mb-3">
                <a href="{{ route('admin.teachers.index') }}" class="text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste des enseignants
                </a>
            </p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-import me-2"></i>Importer des enseignants depuis un fichier</h5>
                </div>
                <div class="card-body">
                    <ol class="small text-muted mb-4">
                        <li>Téléchargez le modèle Excel ci-dessous (ou utilisez votre propre fichier existant).</li>
                        <li>Associez les colonnes de votre fichier aux champs attendus.</li>
                        <li>Vérifiez l'aperçu — les lignes en erreur (matière introuvable, doublon, champ manquant) sont signalées avant tout import.</li>
                        <li>Confirmez : les comptes enseignants valides sont créés, et un fichier avec leurs identifiants/mots de passe est généré.</li>
                    </ol>

                    <p class="small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        La colonne Matières est optionnelle (utile au collège/lycée ; au primaire un enseignant
                        couvre toutes les matières de sa classe). Si renseignée, elle doit reprendre exactement le
                        nom des matières tel qu'affiché sur la plateforme, séparées par un point-virgule.
                    </p>
                    <p class="small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Les <strong>classes ne s'importent pas</strong> — l'affectation se fait manuellement par
                        l'admin après l'import, depuis la fiche de chaque enseignant.
                    </p>

                    <a href="{{ route('admin.teachers.import.template') }}" class="btn btn-outline-secondary mb-4">
                        <i class="fas fa-download me-1"></i> Télécharger le modèle Excel
                    </a>

                    <form method="POST" action="{{ route('admin.teachers.import.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">Fichier (.xlsx, .xls ou .csv, 5 Mo max)</label>
                            <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-1"></i> Continuer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
