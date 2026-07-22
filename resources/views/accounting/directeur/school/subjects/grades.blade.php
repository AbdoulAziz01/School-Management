@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $subject->name.' — '.$class->name)

@section('content')
<a href="{{ route('directeur.school.classes.show', $class) }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>{{ $class->name }}
</a>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-book me-2"></i>{{ $subject->name }} — {{ $class->name }}</h1>
    <span class="badge bg-secondary fs-6">Barème /{{ $maxGrade }}</span>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($rows->isEmpty())
            <p class="text-muted p-3 mb-0">Aucun élève dans cette classe.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Élève</th>
                            <th>Nombre de notes</th>
                            <th>Moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td><a href="{{ route('directeur.school.students.show', $row['student']) }}">{{ $row['student']->name }}</a></td>
                                <td>{{ $row['grades']->count() }}</td>
                                <td>
                                    @if($row['average'] !== null)
                                        {{ $row['average'] }}/{{ $maxGrade }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
