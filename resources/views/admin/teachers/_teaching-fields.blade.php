@php
    $selectedSubjectIds = array_map('intval', $selectedSubjectIds ?? []);
    $selectedClassIds = array_map('intval', $selectedClassIds ?? []);
@endphp

<div class="mb-4">
    <h5 class="mb-3">
        Matières enseignées
        @if($requireTeaching ?? false)<span class="text-danger">*</span>@endif
    </h5>
    @if($subjects->isEmpty())
        <div class="alert alert-warning mb-0">
            Aucune matière disponible. <a href="{{ route('admin.subjects.create') }}">Créer une matière</a> d'abord.
        </div>
    @else
        <div class="row">
            @foreach($subjects as $subject)
                <div class="col-md-4 col-lg-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="subjects[]" value="{{ $subject->id }}"
                               id="subject_{{ $subject->id }}"
                               {{ in_array($subject->id, $selectedSubjectIds) ? 'checked' : '' }}>
                        <label class="form-check-label" for="subject_{{ $subject->id }}">
                            {{ $subject->name }}
                            @if($subject->code)
                                <small class="text-muted">({{ $subject->code }})</small>
                            @endif
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        @error('subjects')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        @error('subjects.*')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    @endif
</div>

<div class="mb-4">
    <h5 class="mb-3">
        Classes affectées
        @if($requireTeaching ?? false)<span class="text-danger">*</span>@endif
    </h5>
    @if($classes->isEmpty())
        <div class="alert alert-warning mb-0">
            Aucune classe pour l'année en cours. <a href="{{ route('admin.classes.create') }}">Créer une classe</a> d'abord.
        </div>
    @else
        <div class="row">
            @foreach($classes as $class)
                <div class="col-md-4 col-lg-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="classes[]" value="{{ $class->id }}"
                               id="class_{{ $class->id }}"
                               {{ in_array($class->id, $selectedClassIds) ? 'checked' : '' }}>
                        <label class="form-check-label" for="class_{{ $class->id }}">
                            {{ $class->name }}
                            <small class="text-muted d-block">
                                {{ $class->level->name ?? '' }}{{ $class->academicYear ? ' — '.$class->academicYear->name : '' }}
                            </small>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        @error('classes')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        @error('classes.*')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    @endif
</div>
