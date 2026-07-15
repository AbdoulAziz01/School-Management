{{-- Bandeau profil élève (photo, classe, infos) — partagé caissier/comptable/directeur --}}
<div class="card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        @if($student->profile_photo_path)
            <img src="{{ Storage::url($student->profile_photo_path) }}" alt="" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
        @else
            <span class="rounded-circle bg-warning bg-opacity-25 text-warning d-flex align-items-center justify-content-center fw-bold fs-3" style="width:80px;height:80px;">
                {{ strtoupper(substr($student->name, 0, 1)) }}
            </span>
        @endif

        <div class="flex-grow-1">
            <h1 class="h4 mb-1">{{ $student->name }}</h1>
            <div class="d-flex flex-wrap gap-3 small text-muted">
                <span><i class="fas fa-id-card me-1"></i>{{ $student->identifier ?? '—' }}</span>
                <span><i class="fas fa-door-open me-1"></i>{{ $student->schoolClass?->name ?? 'Sans classe' }}</span>
                @if($student->schoolClass?->level)
                    <span><i class="fas fa-layer-group me-1"></i>{{ $student->schoolClass->level->name }}</span>
                @endif
                @if($student->date_of_birth)
                    <span><i class="fas fa-cake-candles me-1"></i>{{ $student->date_of_birth->format('d/m/Y') }}</span>
                @endif
                @if($student->gender)
                    <span><i class="fas fa-venus-mars me-1"></i>{{ $student->gender === 'M' ? 'Masculin' : ($student->gender === 'F' ? 'Féminin' : $student->gender) }}</span>
                @endif
            </div>
            @if($student->parent_name || $student->guardian_phone)
                <div class="small text-muted mt-1">
                    <i class="fas fa-user-shield me-1"></i>
                    Tuteur/parent : {{ $student->parent_name ?? '—' }}
                    @if($student->guardian_phone) · {{ $student->guardian_phone }} @endif
                </div>
            @endif
        </div>
    </div>
</div>
