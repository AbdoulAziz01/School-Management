@extends('admin.layouts.app')

@section('title', 'Détails de l\'étudiant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations personnelles</h4>
                </div>
                <div class="text-center card-body">
                    <div class="mb-3">
                        <div class="avatar avatar-xxl">
                            <span class="text-white avatar-text rounded-circle bg-primary" style="font-size: 2.5rem; width: 100px; height: 100px; line-height: 100px;">
                                {{ substr($student->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <h4>{{ $student->name }}</h4>
                    <p class="text-muted">{{ $student->email }}</p>
                    <hr>
                    <p><strong>Date de naissance :</strong> {{ $student->date_of_birth->format('d/m/Y') ?? 'Non spécifiée' }}</p>
                    <p><strong>Téléphone :</strong> {{ $student->phone ?? 'Non spécifié' }}</p>
                    <p><strong>Adresse :</strong> {{ $student->address ?? 'Non spécifiée' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations scolaires</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-graduation-cap"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Niveau</span>
                                    <span class="info-box-number">{{ $student->level->name ?? 'Non spécifié' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-chalkboard"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Classe</span>
                                    <span class="info-box-number">{{ $student->schoolClass->name ?? 'Non affecté' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($student->studentGrades->isNotEmpty())
                        <h5 class="mt-4 mb-3">Notes récentes</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Matière</th>
                                        <th>Note</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->studentGrades->take(5) as $grade)
                                        <tr>
                                            <td>{{ $grade->subject->name ?? 'N/A' }}</td>
                                            <td>{{ $grade->grade }}/20</td>
                                            <td>{{ $grade->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="mt-4 alert alert-info">
                            Aucune note enregistrée pour cet étudiant.
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    display: flex;
    min-height: 80px;
    background: #fff;
    width: 100%;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: .25rem;
    margin-bottom: 1rem;
}
.info-box .info-box-icon {
    display: block;
    float: left;
    height: 80px;
    width: 80px;
    text-align: center;
    font-size: 1.875rem;
    line-height: 80px;
    background: rgba(0,0,0,0.1);
    border-radius: .25rem 0 0 .25rem;
}
.info-box .info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 80px;
}
.info-box .info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}
.info-box .info-box-number {
    display: block;
    font-weight: 700;
    font-size: 18px;
}
</style>
@endsection
