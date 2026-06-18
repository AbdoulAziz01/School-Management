<div class="vc-row">
    <div class="d-flex align-items-start gap-3 flex-wrap">
        {{-- Icône statut --}}
        <div style="width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                    background:{{ $vc->is_active ? '#dcfce7' : ($vc->isPast() ? '#f1f5f9' : '#fffbeb') }};
                    color:{{ $vc->is_active ? '#16a34a' : ($vc->isPast() ? '#94a3b8' : '#d97706') }};font-size:1.2rem;">
            <i class="fas {{ $vc->is_active ? 'fa-broadcast-tower' : ($vc->isPast() ? 'fa-check' : 'fa-clock') }}"></i>
        </div>

        {{-- Informations --}}
        <div style="flex:1; min-width:0;">
            <div class="fw-bold" style="color:#1e293b;">{{ $vc->title }}</div>
            <div class="text-muted small mt-1 d-flex flex-wrap gap-3">
                <span><i class="fas fa-users me-1"></i>{{ $vc->classRoom->name ?? '—' }}</span>
                @if($vc->subject)
                    <span><i class="fas fa-book me-1"></i>{{ $vc->subject->name }}</span>
                @endif
                <span><i class="fas fa-calendar me-1"></i>{{ $vc->scheduled_at->format('d/m/Y H:i') }}</span>
                <span><i class="fas fa-hourglass me-1"></i>{{ $vc->duration_minutes }} min</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2 flex-shrink-0 flex-wrap align-items-center">
            <span class="badge bg-{{ $vc->statusColor() }}-subtle text-{{ $vc->statusColor() }} fw-semibold">
                {{ $vc->statusLabel() }}
            </span>

            @if($vc->is_active)
                {{-- Rejoindre --}}
                <a href="{{ route('teacher.virtual-class.join', $vc) }}"
                   class="btn btn-sm btn-success fw-semibold" style="border-radius:8px;" target="_blank">
                    <i class="fas fa-video me-1"></i>Rejoindre
                </a>
                {{-- Fermer --}}
                <form action="{{ route('teacher.virtual-class.toggle', $vc) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:8px;"
                            onclick="return confirm('Terminer et fermer cette séance ?')">
                        <i class="fas fa-stop me-1"></i>Terminer
                    </button>
                </form>
            @elseif(!$vc->isPast())
                {{-- Ouvrir --}}
                <form action="{{ route('teacher.virtual-class.toggle', $vc) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-warning fw-semibold" style="border-radius:8px;">
                        <i class="fas fa-play me-1"></i>Ouvrir la salle
                    </button>
                </form>
            @endif

            @unless($vc->is_active)
                <form action="{{ route('teacher.virtual-class.destroy', $vc) }}" method="POST" class="delete-vc-form">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;"
                            onclick="return confirm('Supprimer définitivement ?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            @endunless
        </div>
    </div>
</div>
