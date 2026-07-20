@extends('admin.layouts.app')

@section('title', 'Importer des élèves')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <p class="mb-3">
                <a href="{{ route('admin.students.index') }}" class="text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste des élèves
                </a>
            </p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-import me-2"></i>Importer des élèves depuis un fichier</h5>
                </div>
                <div class="card-body">
                    <ol class="small text-muted mb-4">
                        <li>Téléchargez le modèle Excel ci-dessous (ou utilisez votre propre fichier existant).</li>
                        <li>Associez les colonnes de votre fichier aux champs attendus.</li>
                        <li>Vérifiez l'aperçu — les lignes en erreur (classe introuvable, doublon, champ manquant) sont signalées avant tout import.</li>
                        <li>Confirmez : les élèves valides sont créés, un fichier avec leurs identifiants/mots de passe est généré.</li>
                    </ol>

                    <a href="{{ route('admin.students.import.template') }}" class="btn btn-outline-secondary mb-4">
                        <i class="fas fa-download me-1"></i> Télécharger le modèle Excel
                    </a>

                    <form method="POST" action="{{ route('admin.students.import.upload') }}" enctype="multipart/form-data">
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
