@extends('teacher.layouts.app')

@section('title', 'Planifier une classe virtuelle')

@section('content')
<div class="container" style="max-width:720px;">

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.virtual-class.index') }}" class="text-warning text-decoration-none">
                    <i class="fas fa-video me-1"></i>Classes Virtuelles
                </a>
            </li>
            <li class="breadcrumb-item active">Nouvelle séance</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
        <div class="card-header py-3 px-4"
             style="background:linear-gradient(135deg,#fffbeb,#fff7ed); border-bottom:1px solid #fed7aa;">
            <h5 class="mb-0 fw-bold" style="color:#1e293b;">
                <i class="fas fa-calendar-plus me-2 text-warning"></i>Planifier une séance Jitsi Meet
            </h5>
        </div>
        <div class="card-body p-4">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('teacher.virtual-class.store') }}" method="POST">
                @csrf

                {{-- Titre --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Titre de la séance <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" placeholder="Ex. : Révision chapitre 3 — Fonctions"
                           maxlength="200" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Classe --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Classe <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($myClasses as $cls)
                                <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->name }}{{ $cls->level ? ' (' . $cls->level->name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Matière <span class="text-muted small">(optionnel)</span></label>
                        <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror">
                            <option value="">— Aucune —</option>
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}" {{ old('subject_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Date & durée --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Date et heure <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="scheduled_at"
                               class="form-control @error('scheduled_at') is-invalid @enderror"
                               value="{{ old('scheduled_at') }}" required>
                        @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Durée (minutes) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_minutes"
                               class="form-control @error('duration_minutes') is-invalid @enderror"
                               value="{{ old('duration_minutes', 60) }}" min="15" max="480">
                        @error('duration_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Mot de passe (optionnel) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Mot de passe de la salle <span class="text-muted small">(optionnel)</span>
                    </label>
                    <input type="text" name="meeting_password"
                           class="form-control @error('meeting_password') is-invalid @enderror"
                           value="{{ old('meeting_password') }}" placeholder="Laissez vide pour une salle ouverte"
                           maxlength="64">
                    @error('meeting_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Si renseigné, communiquez-le aux élèves avant la séance.</div>
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Description <span class="text-muted small">(optionnel)</span></label>
                    <textarea name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Objectifs de la séance, pré-requis…" maxlength="1000">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Info Jitsi --}}
                <div class="alert alert-info d-flex gap-2 mb-4" style="border-radius:10px; font-size:.88rem;">
                    <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                    <div>
                        La visioconférence utilise <strong>Jitsi Meet</strong> (meet.jit.si).
                        Un lien unique sera généré automatiquement. Vous devrez <strong>ouvrir la salle</strong>
                        depuis votre tableau de bord pour que les élèves puissent rejoindre.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('teacher.virtual-class.index') }}"
                       class="btn btn-light fw-semibold" style="border-radius:10px;">Annuler</a>
                    <button type="submit" class="btn btn-warning fw-bold" style="border-radius:10px;">
                        <i class="fas fa-calendar-check me-1"></i>Planifier la séance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
