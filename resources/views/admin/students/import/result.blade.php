@extends('admin.layouts.app')

@section('title', 'Import terminé')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Import terminé</h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>{{ session('import_created', 0) }}</strong> élève(s) créé(s),
                        <strong>{{ session('import_skipped', 0) }}</strong> ligne(s) ignorée(s) (erreurs).
                    </p>

                    @if($credentialsToken)
                        <div class="alert alert-warning">
                            <i class="fas fa-key me-1"></i>
                            <strong>Identifiants générés</strong> — téléchargez-les maintenant et transmettez-les
                            aux familles. Ils ne seront affichés qu'une seule fois via ce lien.
                        </div>
                        <a href="{{ route('admin.students.import.credentials', $credentialsToken) }}" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i> Télécharger les identifiants (Excel)
                        </a>
                    @endif

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i> Voir la liste des élèves
                        </a>
                        <a href="{{ route('admin.students.import') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-file-import me-1"></i> Importer un autre fichier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
