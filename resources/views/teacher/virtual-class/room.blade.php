@extends('teacher.layouts.app')

@section('title', 'Salle — ' . $vc->title)

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════
   Override layout : plein écran, zero-padding, pas de scroll global
═══════════════════════════════════════════════════════════════════ */
.portal-page-body { padding: 0 !important; overflow: hidden; }
body              { overflow: hidden; }

/* ═══════════════════════════════════════════════════════════════════
   Enveloppe principale (flex column, prend tout l'espace disponible)
═══════════════════════════════════════════════════════════════════ */
.vc-room {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 56px); /* 56 = hauteur topbar EduManager */
    overflow: hidden;
}

/* ═══════════════════════════════════════════════════════════════════
   Barre de contrôle supérieure
═══════════════════════════════════════════════════════════════════ */
.vc-ctrl {
    flex-shrink: 0;
    height: 62px;
    background: linear-gradient(90deg, #1c1917 0%, #292524 100%);
    color: #fef3c7;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid rgba(251, 191, 36, .18);
    position: relative;
    z-index: 200;
}

.vc-live-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #dc2626;
    color: #fff;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .06em;
    padding: 4px 11px;
    border-radius: 99px;
    animation: livePulse 1.8s ease-in-out infinite;
    flex-shrink: 0;
}
@keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:.55} }
.vc-live-dot { width: 7px; height: 7px; border-radius: 50%; background: #fff; }

.vc-ctrl-title { font-weight: 700; font-size: .95rem; color: #fef3c7; line-height: 1.25; }
.vc-ctrl-sub   { font-size: .73rem; color: #a8a29e; line-height: 1; }

.vc-timer {
    font-family: 'SF Mono', 'Cascadia Code', 'Fira Code', ui-monospace, monospace;
    font-size: 1.02rem;
    font-weight: 700;
    color: #fbbf24;
    background: rgba(251, 191, 36, .12);
    border: 1px solid rgba(251, 191, 36, .28);
    border-radius: 8px;
    padding: 6px 14px;
    letter-spacing: .07em;
    flex-shrink: 0;
}

.vc-pcount {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(251, 191, 36, .15);
    color: #fbbf24;
    font-size: .8rem;
    font-weight: 700;
    border-radius: 99px;
    padding: 5px 13px;
    flex-shrink: 0;
}

/* ═══════════════════════════════════════════════════════════════════
   Corps : Jitsi + Panneau latéral
═══════════════════════════════════════════════════════════════════ */
.vc-body {
    flex: 1;
    display: flex;
    overflow: hidden;
    min-height: 0;
}

/* ──── Colonne Jitsi ──── */
.vc-jitsi-col {
    flex: 0 0 66.6667%;
    max-width: 66.6667%;
    position: relative;
    background: #0c0a09;
    overflow: hidden;
}

#jitsi-container {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
}

/* Overlay de chargement */
.vc-loading {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    background: #0c0a09;
    z-index: 10;
    transition: opacity .4s ease, visibility .4s ease;
}
.vc-loading.hidden { opacity: 0; visibility: hidden; }

.vc-spinner {
    width: 48px;
    height: 48px;
    border: 3px solid rgba(251, 191, 36, .18);
    border-top-color: #f59e0b;
    border-radius: 50%;
    animation: spin .85s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.vc-loading-text {
    font-size: .85rem;
    color: #78716c;
    text-align: center;
}

/* ──── Colonne Panneau ──── */
.vc-panel-col {
    flex: 0 0 33.3333%;
    max-width: 33.3333%;
    background: #fffbeb;
    border-left: 1px solid #fde68a;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* ═══════════════════════════════════════════════════════════════════
   Onglets du panneau
═══════════════════════════════════════════════════════════════════ */
.panel-tabs {
    flex-shrink: 0;
    display: flex;
    background: linear-gradient(90deg, #fffbeb, #fff7ed);
    border-bottom: 2px solid #fde68a;
}

.panel-tab-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 13px 6px;
    border: none;
    background: transparent;
    font-size: .8rem;
    font-weight: 600;
    color: #a8a29e;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: color .2s, background .2s, border-bottom-color .2s;
}
.panel-tab-btn:hover:not(.active) {
    color: #92400e;
    background: rgba(251, 191, 36, .06);
}
.panel-tab-btn.active {
    color: #d97706;
    border-bottom-color: #f59e0b;
    background: rgba(251, 191, 36, .08);
}

.tab-counter {
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 99px;
    font-size: .65rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fbbf24;
    color: #1c1917;
    transition: transform .15s;
}
.tab-counter.alert-dot {
    background: #dc2626;
    color: #fff;
}

/* ═══════════════════════════════════════════════════════════════════
   Zones de contenu des onglets
═══════════════════════════════════════════════════════════════════ */
.panel-body {
    flex: 1;
    overflow: hidden;
    position: relative;
}

.tab-panel {
    position: absolute;
    inset: 0;
    overflow-y: auto;
    padding: 12px;
    display: none;
}
.tab-panel.active { display: block; }

/* Scrollbar discrète */
.tab-panel::-webkit-scrollbar { width: 4px; }
.tab-panel::-webkit-scrollbar-track { background: transparent; }
.tab-panel::-webkit-scrollbar-thumb { background: #fde68a; border-radius: 99px; }

/* ═══════════════════════════════════════════════════════════════════
   Liste des participants
═══════════════════════════════════════════════════════════════════ */
.vc-empty-state {
    text-align: center;
    padding: 44px 20px;
    color: #a8a29e;
}
.vc-empty-state i   { font-size: 2.2rem; opacity: .3; display: block; margin-bottom: 12px; }
.vc-empty-title     { font-weight: 700; font-size: .9rem; color: #92400e; }
.vc-empty-sub       { font-size: .78rem; margin-top: 4px; color: #b45309; opacity: .75; }

.participant-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 11px;
    border-radius: 12px;
    background: #fff;
    border: 1px solid #fde68a;
    margin-bottom: 7px;
    animation: slideInRight .25s ease forwards;
    transition: opacity .3s ease, transform .3s ease;
}
.participant-item.leaving {
    opacity: 0;
    transform: translateX(12px);
}

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(12px); }
    to   { opacity: 1; transform: none; }
}

.p-avatar {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #1c1917;
    font-weight: 800;
    font-size: .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    letter-spacing: .02em;
    text-transform: uppercase;
}

/* Couleurs de rotation pour les avatars */
.p-avatar.c1 { background: linear-gradient(135deg,#f59e0b,#d97706); }
.p-avatar.c2 { background: linear-gradient(135deg,#10b981,#059669); }
.p-avatar.c3 { background: linear-gradient(135deg,#6366f1,#4f46e5); }
.p-avatar.c4 { background: linear-gradient(135deg,#ec4899,#db2777); }
.p-avatar.c5 { background: linear-gradient(135deg,#3b82f6,#2563eb); }

.p-name {
    flex: 1;
    min-width: 0;
    font-size: .855rem;
    font-weight: 600;
    color: #1e293b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.p-badges { display: flex; gap: 5px; flex-shrink: 0; }

.p-badge {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .68rem;
    transition: background .2s, color .2s, border-color .2s;
}
.p-badge.on     { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.p-badge.off    { background: #f1f5f9; color: #cbd5e1; border: 1px solid #e2e8f0; }
.p-badge.screen { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }

/* ═══════════════════════════════════════════════════════════════════
   Journal d'activité
═══════════════════════════════════════════════════════════════════ */
.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 8px 10px;
    border-radius: 10px;
    background: #fff;
    border-left: 3px solid #e2e8f0;
    margin-bottom: 6px;
    animation: slideInRight .2s ease forwards;
}

.activity-item.ev-join    { border-left-color: #86efac; }
.activity-item.ev-leave   { border-left-color: #fca5a5; }
.activity-item.ev-mic     { border-left-color: #fbbf24; }
.activity-item.ev-cam     { border-left-color: #a78bfa; }
.activity-item.ev-screen  { border-left-color: #60a5fa; }
.activity-item.ev-raise   { border-left-color: #fb923c; }
.activity-item.ev-start   { border-left-color: #86efac; }

.activity-icon {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .68rem;
    margin-top: 1px;
}
.activity-item.ev-join   .activity-icon { background:#f0fdf4; color:#16a34a; }
.activity-item.ev-leave  .activity-icon { background:#fef2f2; color:#dc2626; }
.activity-item.ev-mic    .activity-icon { background:#fef3c7; color:#d97706; }
.activity-item.ev-cam    .activity-icon { background:#f5f3ff; color:#7c3aed; }
.activity-item.ev-screen .activity-icon { background:#eff6ff; color:#2563eb; }
.activity-item.ev-raise  .activity-icon { background:#fff7ed; color:#ea580c; }
.activity-item.ev-start  .activity-icon { background:#f0fdf4; color:#16a34a; }

.activity-body { flex: 1; min-width: 0; }
.activity-name { font-size: .8rem;  font-weight: 700; color: #1e293b; line-height: 1.3; }
.activity-msg  { font-size: .77rem; color: #64748b;  line-height: 1.3; }
.activity-time { font-size: .69rem; color: #94a3b8; white-space: nowrap; margin-top: 3px; flex-shrink: 0; }

/* ═══════════════════════════════════════════════════════════════════
   Pied du panneau
═══════════════════════════════════════════════════════════════════ */
.panel-footer {
    flex-shrink: 0;
    padding: 9px 14px;
    background: linear-gradient(90deg, #fffbeb, #fff7ed);
    border-top: 1px solid #fde68a;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.pf-brand { font-size: .73rem; color: #92400e; display: flex; align-items: center; gap: 5px; }
.pf-status { font-size: .7rem; color: #a8a29e; display: flex; align-items: center; gap: 5px; }
.pf-dot    { width: 7px; height: 7px; border-radius: 50%; background: #16a34a;
             animation: livePulse 2s ease-in-out infinite; }

/* ═══════════════════════════════════════════════════════════════════
   Responsive
═══════════════════════════════════════════════════════════════════ */
@media (max-width: 991.98px) {
    .vc-room { height: auto; }
    .vc-body { flex-direction: column; height: auto; overflow: visible; }
    .vc-jitsi-col { flex: none; max-width: 100%; height: 56vw; min-height: 240px; }
    .vc-panel-col { flex: none; max-width: 100%; height: 380px; }
    body { overflow: auto; }
    .portal-page-body { overflow: visible !important; }
}
</style>
@endpush

@section('content')
<div class="vc-room">

    {{-- ── Barre de contrôle ─────────────────────────────────────────────── --}}
    <div class="vc-ctrl">

        {{-- Gauche : état + titre --}}
        <div class="d-flex align-items-center gap-3 min-w-0">
            <span class="vc-live-badge">
                <span class="vc-live-dot"></span>EN DIRECT
            </span>
            <div class="min-w-0">
                <div class="vc-ctrl-title text-truncate">{{ $vc->title }}</div>
                <div class="vc-ctrl-sub">
                    {{ $vc->classRoom->name ?? '—' }}
                    @if($vc->subject)&nbsp;·&nbsp;{{ $vc->subject->name }}@endif
                </div>
            </div>
        </div>

        {{-- Centre : chrono + compteur --}}
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <span class="vc-timer" id="sessionTimer">00:00</span>
            <span class="vc-pcount" title="Élèves connectés">
                <i class="fas fa-users" style="font-size:.72rem;"></i>
                <span id="pCountLabel">0</span>
            </span>
            @if($vc->meeting_password)
                <span style="background:rgba(251,191,36,.15); color:#fbbf24; border:1px solid rgba(251,191,36,.3);
                             font-size:.72rem; font-weight:600; padding:4px 10px; border-radius:99px;">
                    <i class="fas fa-lock me-1"></i>{{ $vc->meeting_password }}
                </span>
            @endif
        </div>

        {{-- Droite : actions --}}
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <form action="{{ route('teacher.virtual-class.toggle', $vc) }}" method="POST" id="toggleForm">
                @csrf @method('PATCH')
                <button type="submit"
                        class="btn btn-sm btn-danger fw-semibold"
                        style="border-radius:8px;"
                        onclick="return confirm('Terminer et fermer la séance pour tous les élèves ?')">
                    <i class="fas fa-stop-circle me-1"></i>
                    <span class="d-none d-md-inline">Terminer</span>
                </button>
            </form>
            <a href="{{ route('teacher.virtual-class.index') }}"
               class="btn btn-sm btn-outline-light"
               style="border-radius:8px;"
               title="Retour au tableau de bord">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </div>

    {{-- ── Corps principal ──────────────────────────────────────────────── --}}
    <div class="vc-body">

        {{-- ── Colonne gauche : Jitsi ─────────────────────────────────── --}}
        <div class="vc-jitsi-col">

            {{-- Overlay de chargement --}}
            <div class="vc-loading" id="jitsiLoading">
                <div class="vc-spinner"></div>
                <div class="vc-loading-text">
                    Connexion à la visioconférence…<br>
                    <small style="opacity:.6;">{{ $vc->room_name }}</small>
                </div>
            </div>

            <div id="jitsi-container"></div>
        </div>

        {{-- ── Colonne droite : Panneau EduManager Pro ───────────────── --}}
        <div class="vc-panel-col">

            {{-- Onglets --}}
            <div class="panel-tabs">
                <button class="panel-tab-btn active" id="tab-students" onclick="vcSwitchTab('students')">
                    <i class="fas fa-users" style="font-size:.75rem;"></i>
                    Élèves connectés
                    <span class="tab-counter" id="studentTabCount">0</span>
                </button>
                <button class="panel-tab-btn" id="tab-activity" onclick="vcSwitchTab('activity')">
                    <i class="fas fa-chart-line" style="font-size:.75rem;"></i>
                    Suivi d'activité
                    <span class="tab-counter" id="activityTabCount">0</span>
                </button>
            </div>

            {{-- Contenu des onglets --}}
            <div class="panel-body">

                {{-- Onglet Élèves --}}
                <div class="tab-panel active" id="panel-students">
                    <div id="studentEmpty" class="vc-empty-state">
                        <i class="fas fa-user-clock"></i>
                        <div class="vc-empty-title">En attente d'élèves</div>
                        <div class="vc-empty-sub">Les élèves apparaîtront ici dès qu'ils rejoindront la salle.</div>
                    </div>
                    <div id="studentList"></div>
                </div>

                {{-- Onglet Activité --}}
                <div class="tab-panel" id="panel-activity">
                    <div id="activityLog">
                        {{-- Entrée initiale injectée par JS --}}
                    </div>
                </div>
            </div>

            {{-- Pied de panneau --}}
            <div class="panel-footer">
                <div class="pf-brand">
                    <i class="fas fa-shield-alt" style="color:#f59e0b;"></i>
                    <strong>EduManager Pro</strong>
                    <span style="opacity:.5;">·</span>
                    Salle sécurisée
                </div>
                <div class="pf-status">
                    <span class="pf-dot"></span>
                    Actif
                </div>
            </div>
        </div>
    </div>

</div>{{-- .vc-room --}}
@endsection

@push('scripts')
<script src="https://meet.jit.si/external_api.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    /* ════════════════════════════════════════════════════════════════════
       État interne
    ════════════════════════════════════════════════════════════════════ */
    /** @type {Map<string, {name:string, mic:boolean, cam:boolean, screen:boolean, colorIdx:number}>} */
    const participants = new Map();
    let colorCursor    = 0;     // Rotation des couleurs d'avatar
    let newActUnread   = 0;     // Notifications non lues sur l'onglet activité
    let activeTab      = 'students';

    /* ════════════════════════════════════════════════════════════════════
       Chrono de séance
    ════════════════════════════════════════════════════════════════════ */
    let elapsed = 0;
    const timerEl = document.getElementById('sessionTimer');

    setInterval(function () {
        elapsed++;
        timerEl.textContent =
            String(Math.floor(elapsed / 60)).padStart(2, '0') + ':' +
            String(elapsed % 60).padStart(2, '0');
    }, 1000);

    /* ════════════════════════════════════════════════════════════════════
       Onglets
    ════════════════════════════════════════════════════════════════════ */
    window.vcSwitchTab = function (tab) {
        document.querySelectorAll('.panel-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

        document.getElementById('tab-' + tab).classList.add('active');
        document.getElementById('panel-' + tab).classList.add('active');
        activeTab = tab;

        if (tab === 'activity') {
            newActUnread = 0;
            const badge = document.getElementById('activityTabCount');
            badge.classList.remove('alert-dot');
            badge.textContent = document.querySelectorAll('#activityLog .activity-item').length;
        }
    };

    /* ════════════════════════════════════════════════════════════════════
       Initialisation de Jitsi Meet External API
    ════════════════════════════════════════════════════════════════════ */
    const api = new JitsiMeetExternalAPI('meet.jit.si', {
        roomName:   '{{ $vc->room_name }}',
        width:      '100%',
        height:     '100%',
        parentNode: document.getElementById('jitsi-container'),
        userInfo: {
            displayName: '{{ addslashes("Prof. " . $teacher->first_name . " " . $teacher->last_name) }}',
            email:       '{{ $teacher->email }}',
        },
        configOverwrite: {
            prejoinPageEnabled:  false,
            startWithAudioMuted: false,
            startWithVideoMuted: false,
            disableDeepLinking:  true,
            @if($vc->meeting_password)
            password: '{{ addslashes($vc->meeting_password) }}',
            @endif
        },
        interfaceConfigOverwrite: {
            SHOW_JITSI_WATERMARK:        false,
            SHOW_WATERMARK_FOR_GUESTS:   false,
            DEFAULT_REMOTE_DISPLAY_NAME: 'Élève',
            TOOLBAR_BUTTONS: [
                'microphone', 'camera', 'desktop', 'fullscreen',
                'fodeviceselection', 'hangup', 'chat',
                'sharedvideo', 'settings', 'raisehand',
                'videoquality', 'filmstrip', 'tileview',
                'stats', 'mute-everyone', 'shortcuts',
            ],
        },
    });

    /* ════════════════════════════════════════════════════════════════════
       Écouteurs d'événements Jitsi
    ════════════════════════════════════════════════════════════════════ */

    /** Salle prête : cacher l'overlay de chargement */
    api.addEventListener('videoConferenceJoined', function () {
        document.getElementById('jitsiLoading').classList.add('hidden');
        addActivity('Séance ouverte', 'La visioconférence est active', 'ev-start', 'fas fa-play');
    });

    /** Nouveau participant */
    api.addEventListener('participantJoined', function ({ id, displayName }) {
        const name = (displayName || '').trim() || 'Élève';
        addParticipant(id, name);
        addActivity(name, 'a rejoint la salle', 'ev-join', 'fas fa-sign-in-alt');
    });

    /** Participant parti */
    api.addEventListener('participantLeft', function ({ id }) {
        const p    = participants.get(id);
        const name = p ? p.name : 'Élève';
        removeParticipant(id);
        addActivity(name, 'a quitté la salle', 'ev-leave', 'fas fa-sign-out-alt');
    });

    /** Changement d'état du micro */
    api.addEventListener('audioMuteStatusChanged', function ({ id, muted }) {
        if (!id || !participants.has(id)) return;
        const p = participants.get(id);
        p.mic   = !muted;
        refreshBadge(id, 'mic', p.mic);
        addActivity(
            p.name,
            muted ? 'a coupé son micro' : 'a réactivé son micro',
            'ev-mic',
            muted ? 'fas fa-microphone-slash' : 'fas fa-microphone'
        );
    });

    /** Changement d'état de la caméra */
    api.addEventListener('videoMuteStatusChanged', function ({ id, muted }) {
        if (!id || !participants.has(id)) return;
        const p = participants.get(id);
        p.cam   = !muted;
        refreshBadge(id, 'cam', p.cam);
        addActivity(
            p.name,
            muted ? 'a coupé sa caméra' : 'a activé sa caméra',
            'ev-cam',
            muted ? 'fas fa-video-slash' : 'fas fa-video'
        );
    });

    /** Partage d'écran */
    api.addEventListener('screenSharingStatusChanged', function ({ on, id }) {
        const p    = id && participants.has(id) ? participants.get(id) : null;
        const name = p ? p.name : 'Un participant';
        if (p) {
            p.screen = on;
            refreshBadge(id, 'screen', on);
        }
        addActivity(
            name,
            on ? 'a activé le partage d\'écran' : 'a arrêté le partage d\'écran',
            'ev-screen',
            'fas fa-desktop'
        );
    });

    /** Main levée */
    api.addEventListener('raiseHandUpdated', function ({ id, handRaised }) {
        if (!handRaised || !participants.has(id)) return;
        addActivity(participants.get(id).name, 'a levé la main ✋', 'ev-raise', 'fas fa-hand-paper');
    });

    /** L'enseignant raccroche → fermer la salle */
    api.addEventListener('readyToClose', function () {
        document.getElementById('toggleForm').submit();
    });

    /* ════════════════════════════════════════════════════════════════════
       Helpers — Participants
    ════════════════════════════════════════════════════════════════════ */

    function addParticipant(id, name) {
        const colorIdx = (colorCursor++ % 5) + 1;
        participants.set(id, { name, mic: true, cam: true, screen: false, colorIdx });

        document.getElementById('studentEmpty').style.display = 'none';

        const initials = name
            .split(/\s+/)
            .slice(0, 2)
            .map(w => (w[0] || '').toUpperCase())
            .join('') || '?';

        const el        = document.createElement('div');
        el.className    = 'participant-item';
        el.id           = 'ptc-' + id;
        el.dataset.name = name;
        el.innerHTML    = `
            <div class="p-avatar c${colorIdx}">${escHtml(initials)}</div>
            <div class="p-name">${escHtml(name)}</div>
            <div class="p-badges">
                <span class="p-badge on" id="mic-${id}" title="Micro actif">
                    <i class="fas fa-microphone"></i>
                </span>
                <span class="p-badge on" id="cam-${id}" title="Caméra active">
                    <i class="fas fa-video"></i>
                </span>
                <span class="p-badge off" id="scr-${id}" title="Sans partage d'écran">
                    <i class="fas fa-desktop"></i>
                </span>
            </div>`;

        document.getElementById('studentList').appendChild(el);
        syncStudentCount();
    }

    function removeParticipant(id) {
        participants.delete(id);
        const el = document.getElementById('ptc-' + id);
        if (!el) return;

        el.classList.add('leaving');
        setTimeout(function () {
            el.remove();
            if (participants.size === 0) {
                document.getElementById('studentEmpty').style.display = '';
            }
            syncStudentCount();
        }, 330);
    }

    function refreshBadge(id, type, isOn) {
        const prefix = type === 'screen' ? 'scr' : type;
        const el     = document.getElementById(prefix + '-' + id);
        if (!el) return;

        if (type === 'mic') {
            el.className = 'p-badge ' + (isOn ? 'on' : 'off');
            el.title     = isOn ? 'Micro actif' : 'Micro coupé';
            el.innerHTML = `<i class="fas fa-${isOn ? 'microphone' : 'microphone-slash'}"></i>`;
        } else if (type === 'cam') {
            el.className = 'p-badge ' + (isOn ? 'on' : 'off');
            el.title     = isOn ? 'Caméra active' : 'Caméra coupée';
            el.innerHTML = `<i class="fas fa-${isOn ? 'video' : 'video-slash'}"></i>`;
        } else if (type === 'screen') {
            el.className = 'p-badge ' + (isOn ? 'screen' : 'off');
            el.title     = isOn ? 'Partage d\'écran actif' : 'Sans partage d\'écran';
        }
    }

    function syncStudentCount() {
        const n = participants.size;
        document.getElementById('pCountLabel').textContent       = n;
        document.getElementById('studentTabCount').textContent   = n;
    }

    /* ════════════════════════════════════════════════════════════════════
       Helpers — Journal d'activité
    ════════════════════════════════════════════════════════════════════ */

    function addActivity(name, msg, eventClass, iconClass) {
        const log  = document.getElementById('activityLog');
        const item = document.createElement('div');
        item.className = 'activity-item ' + (eventClass || 'ev-join');
        item.innerHTML = `
            <div class="activity-icon"><i class="${iconClass || 'fas fa-info-circle'}"></i></div>
            <div class="activity-body">
                <div class="activity-name">${escHtml(name)}</div>
                <div class="activity-msg">${escHtml(msg)}</div>
            </div>
            <div class="activity-time">${currentTime()}</div>`;

        log.insertBefore(item, log.firstChild);

        // Compteur / badge non-lu si on est sur l'onglet "students"
        if (activeTab === 'students') {
            newActUnread++;
            const badge = document.getElementById('activityTabCount');
            badge.textContent = newActUnread;
            badge.classList.add('alert-dot');
        } else {
            // Juste mettre à jour le total
            document.getElementById('activityTabCount').textContent =
                document.querySelectorAll('#activityLog .activity-item').length;
        }
    }

    /* ════════════════════════════════════════════════════════════════════
       Utilitaires
    ════════════════════════════════════════════════════════════════════ */

    function currentTime() {
        const d = new Date();
        return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});
</script>
@endpush
