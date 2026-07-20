@extends('admin.layouts.app')

@section('title', 'Notation du primaire')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-table me-2"></i>Notation du primaire</h1>
        <p class="text-muted mb-0">
            Note maximale, coefficient et nombre de compositions par classe et par matière.
            Sans configuration, une matière utilise les valeurs par défaut (10, coef. 1, 3 compositions — 2 pour la CM2).
        </p>
    </div>
</div>

@if($levels->isEmpty())
    <div class="alert alert-info mb-0">Aucune classe de primaire configurée pour cet établissement.</div>
@elseif($subjects->isEmpty())
    <div class="alert alert-info mb-0">Aucune matière du primaire configurée pour cet établissement.</div>
@else

<div style="background:#ffffff;border:1px solid #fde68a;border-radius:.75rem;overflow:hidden;box-shadow:0 1px 3px rgba(217,119,6,.08);">
    <div style="max-height:65vh;overflow-y:auto;">
        <table style="width:100%;border-collapse:collapse;color:#44403c;">
            <thead>
                <tr>
                    <th style="position:sticky;top:0;background:#fef3c7;text-align:left;font-weight:700;font-size:.95rem;color:#92400e;padding:1.1rem 1.25rem;border-bottom:2px solid #fde68a;white-space:nowrap;">Classe</th>
                    <th style="position:sticky;top:0;background:#fef3c7;text-align:left;font-weight:700;font-size:.95rem;color:#92400e;padding:1.1rem 1.25rem;border-bottom:2px solid #fde68a;white-space:nowrap;">Compositions</th>
                    <th style="position:sticky;top:0;background:#fef3c7;text-align:left;font-weight:700;font-size:.95rem;color:#92400e;padding:1.1rem 1.25rem;border-bottom:2px solid #fde68a;white-space:nowrap;">Note max (aperçu)</th>
                    <th style="position:sticky;top:0;background:#fef3c7;text-align:left;font-weight:700;font-size:.95rem;color:#92400e;padding:1.1rem 1.25rem;border-bottom:2px solid #fde68a;white-space:nowrap;">Matières actives</th>
                    <th style="position:sticky;top:0;background:#fef3c7;text-align:left;font-weight:700;font-size:.95rem;color:#92400e;padding:1.1rem 1.25rem;border-bottom:2px solid #fde68a;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($levels as $level)
                    @php
                        $activeCount = collect($grid[$level->id] ?? [])->where('isActive', true)->count();
                    @endphp
                    <tr style="background:#ffffff;">
                        <td style="padding:1rem 1.25rem;border-bottom:1px solid #fef3c7;white-space:nowrap;font-weight:600;color:#1c1917;">{{ $level->name }}</td>
                        <td style="padding:1rem 1.25rem;border-bottom:1px solid #fef3c7;white-space:nowrap;color:#57534e;">
                            {{ collect($grid[$level->id] ?? [])->pluck('compositionsCount')->unique()->sort()->implode(' / ') }} par matière
                        </td>
                        <td style="padding:1rem 1.25rem;border-bottom:1px solid #fef3c7;white-space:nowrap;color:#57534e;">
                            {{ collect($grid[$level->id] ?? [])->pluck('maxGrade')->unique()->sort()->implode(' / ') }}
                        </td>
                        <td style="padding:1rem 1.25rem;border-bottom:1px solid #fef3c7;white-space:nowrap;color:#57534e;">
                            {{ $activeCount }} / {{ $subjects->count() }}
                        </td>
                        <td style="padding:1rem 1.25rem;border-bottom:1px solid #fef3c7;white-space:nowrap;">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#gradingModal{{ $level->id }}" style="color:#d97706;text-decoration:none;font-weight:600;">Modifier</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Un modal par niveau, entièrement rendu par Blade (aucune dépendance JS pour la soumission) --}}
@foreach($levels as $level)
    <div class="modal fade" id="gradingModal{{ $level->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.primary-grading.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="level_id" value="{{ $level->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-graduation-cap me-2"></i>Notation — {{ $level->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Décochez « Active » pour une matière que cet établissement n'enseigne pas à ce niveau —
                            elle disparaîtra des choix proposés aux enseignants pour cette classe.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:70px;">Active</th>
                                        <th>Matière</th>
                                        <th style="width:110px;">Note max</th>
                                        <th style="width:110px;">Coefficient</th>
                                        <th style="width:130px;">Compositions</th>
                                        <th style="width:150px;">Type d'évaluation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $subject)
                                        @php
                                            $settings = $grid[$level->id][$subject->id];
                                        @endphp
                                        <tr class="{{ $settings->isActive ? '' : 'text-muted' }}">
                                            <td class="text-center">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" class="form-check-input"
                                                           name="settings[{{ $subject->id }}][is_active]"
                                                           value="1"
                                                           {{ $settings->isActive ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>{{ $subject->name }}</td>
                                            <td>
                                                <input type="number" step="0.5" min="1" max="1000" class="form-control form-control-sm"
                                                       name="settings[{{ $subject->id }}][max_grade]"
                                                       value="{{ $settings->maxGrade }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.5" min="0.5" max="10" class="form-control form-control-sm"
                                                       name="settings[{{ $subject->id }}][coefficient]"
                                                       value="{{ $settings->coefficient }}">
                                            </td>
                                            <td>
                                                <input type="number" step="1" min="1" max="6" class="form-control form-control-sm"
                                                       name="settings[{{ $subject->id }}][compositions_count]"
                                                       value="{{ $settings->compositionsCount }}">
                                            </td>
                                            <td>
                                                <select name="settings[{{ $subject->id }}][evaluation_type]" class="form-select form-select-sm">
                                                    <option value="composition" {{ $settings->evaluationType === 'composition' ? 'selected' : '' }}>Composition</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@endif
@endsection
