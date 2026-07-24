@php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.student')

@section('title', 'Ma Carte Scolaire')

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════
   VARIABLES UCAO
══════════════════════════════════════════════════════════════════ */
:root {
    --c-blue:      {{ $cardSettings['primary_color'] ?? '#003087' }};
    --c-blue-dk:   color-mix(in srgb, {{ $cardSettings['primary_color'] ?? '#003087' }} 70%, #000 30%);
    --c-blue-lt:   color-mix(in srgb, {{ $cardSettings['primary_color'] ?? '#003087' }} 12%, #fff 88%);
    --c-gold:      {{ $cardSettings['accent_color'] ?? '#c9a84c' }};
    --c-text:      #111111;
    --c-muted:     #5a6475;
    --card-scale:  2.5;
}

/* ── Wrapper de page ──────────────────────────────────────────── */
.card-page-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    padding: 8px 0 32px;
}

/* ── En-tête / boutons ────────────────────────────────────────── */
.card-page-header {
    text-align: center;
    width: 100%;
}

.card-page-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #92400e;
    margin-bottom: 4px;
}

.card-page-sub {
    font-size: .82rem;
    color: #78716c;
    margin-bottom: 14px;
}

.card-page-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 6px;
}

.btn-card-print {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--c-blue); color: #fff;
    border: none; border-radius: 10px;
    padding: 11px 22px; font-size: .88rem; font-weight: 700;
    cursor: pointer; transition: background .18s;
}
.btn-card-print:hover { background: var(--c-blue-dk); color: #fff; }

.print-tip {
    font-size: .72rem; color: #6b7280;
    max-width: 480px; margin: 6px auto 0;
    line-height: 1.55;
}

/* ── Rangée des deux faces ────────────────────────────────────── */
.cards-row {
    display: flex;
    gap: 28px;
    flex-wrap: wrap;
    justify-content: center;
    align-items: flex-start;
}

.card-wrapper-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.face-label {
    font-size: .65rem;
    font-weight: 800;
    color: #78716c;
    text-transform: uppercase;
    letter-spacing: .12em;
}

/* ══════════════════════════════════════════════════════════════════
   CARTE — dimensions réelles CR80 : 85,6 mm × 54 mm
══════════════════════════════════════════════════════════════════ */
.card-outer {
    width:  calc(85.6mm * var(--card-scale));
    height: calc(54mm   * var(--card-scale));
    position: relative;
    flex-shrink: 0;
}

.card-face {
    width:  85.6mm;
    height: 54mm;
    border-radius: 3.2mm;
    overflow: hidden;
    position: absolute;
    top: 0; left: 0;
    transform: scale(var(--card-scale));
    transform-origin: top left;
    box-shadow:
        0 8px 28px rgba(0,0,0,.28),
        0 2px 6px rgba(0,0,0,.16);
}

/* ══════════════════════════════════════════════════════════════════
   RECTO
══════════════════════════════════════════════════════════════════ */
.recto {
    background: #fff;
    display: flex;
    flex-direction: column;
}

/* En-tête bleu */
.r-header {
    background: var(--c-blue);
    height: 13mm;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    padding: 0 2.2mm;
    gap: 1.8mm;
}

.r-logo {
    width: 9mm; height: 9mm;
    border-radius: 50%;
    background: #fff;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    border: .4mm solid rgba(255,255,255,.3);
}

.r-logo img { width: 100%; height: 100%; object-fit: contain; }

.r-logo-initial {
    font-size: 4.5pt;
    font-weight: 900;
    color: var(--c-blue);
    text-align: center;
    line-height: 1;
}

.r-school { flex: 1; min-width: 0; }

.r-school-name {
    font-size: 4.6pt;
    font-weight: 800;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: .04em;
    line-height: 1.25;
}

.r-school-sub {
    font-size: 3.6pt;
    color: rgba(255,255,255,.78);
    line-height: 1.3;
    margin-top: .5mm;
}

/* Drapeau sénégalais CSS pur */
.sn-flag {
    width: 7.5mm; height: 5mm;
    border-radius: .5mm;
    overflow: hidden;
    display: flex;
    flex-shrink: 0;
    border: .2mm solid rgba(255,255,255,.25);
}
.sn-flag .s  { flex: 1; }
.sn-flag .sg { background: #00853F; }
.sn-flag .sy {
    background: #FDEF42;
    display: flex; align-items: center; justify-content: center;
    font-size: 3.5pt; color: #00853F; line-height: 1;
}
.sn-flag .sr { background: #E31B23; }

/* Corps */
.r-body {
    flex: 1;
    display: flex;
    padding: 1.8mm 2mm 1mm;
    gap: 2mm;
    min-height: 0;
}

.r-photo {
    width: 18mm; height: 22mm;
    border: .35mm solid #b0b8c0;
    overflow: hidden;
    background: #eef0f3;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.r-photo img { width: 100%; height: 100%; object-fit: cover; }

.r-photo-initials {
    font-size: 9pt;
    font-weight: 900;
    color: var(--c-blue);
}

.r-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: .9mm;
}

.r-badge {
    display: inline-block;
    font-size: 3.6pt;
    font-weight: 800;
    color: var(--c-blue);
    background: var(--c-blue-lt);
    border-left: .7mm solid var(--c-blue);
    padding: .4mm 1.4mm;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .3mm;
}

.r-name {
    font-size: 7.5pt;
    font-weight: 900;
    color: var(--c-text);
    text-transform: uppercase;
    letter-spacing: .02em;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.r-gold-line { width: 14mm; height: .35mm; background: var(--c-gold); margin: .4mm 0; }

.r-row { display: flex; gap: .8mm; align-items: baseline; line-height: 1.4; }

.r-lbl { font-size: 3.4pt; color: var(--c-muted); white-space: nowrap; flex-shrink: 0; }

.r-val {
    font-size: 4pt; font-weight: 700; color: var(--c-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* Pied de carte */
.r-footer {
    background: linear-gradient(90deg, var(--c-blue) 0%, #1a4a9a 100%);
    height: 8.5mm;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    padding: 0 2.2mm;
    gap: 1.5mm;
}

.r-footer-id     { font-size: 6pt; font-weight: 900; color: #fff; letter-spacing: .04em; }
.r-footer-sep    { width: .3mm; height: 4mm; background: rgba(255,255,255,.3); flex-shrink: 0; }
.r-footer-year   { font-size: 3.8pt; color: rgba(255,255,255,.85); }
.r-footer-issued { font-size: 3.2pt; color: rgba(255,255,255,.65); margin-left: auto; }
.r-footer-brand  { font-size: 3pt;   color: rgba(255,255,255,.45); font-style: italic; }

/* ══════════════════════════════════════════════════════════════════
   VERSO
══════════════════════════════════════════════════════════════════ */
.verso {
    background: linear-gradient(150deg, var(--c-blue-dk) 0%, var(--c-blue) 55%, var(--c-blue-dk) 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.6mm;
    position: relative;
    overflow: hidden;
}

.verso::before {
    content: '';
    display: none;
}

.verso-stripe { position: absolute; left: 0; right: 0; height: .6mm; background: linear-gradient(90deg, transparent, var(--c-gold), transparent); }
.verso-stripe.top    { top: 0; }
.verso-stripe.bottom { bottom: 0; }

.verso-qr {
    background: #fff;
    padding: 1.5mm;
    border-radius: 1.5mm;
    box-shadow: 0 1.5mm 5mm rgba(0,0,0,.45);
    position: relative; z-index: 1;
}

#qrcode-print        { width: 22mm; height: 22mm; display: flex; align-items: center; justify-content: center; }
#qrcode-print canvas,
#qrcode-print img    { width: 22mm !important; height: 22mm !important; }

.verso-label {
    font-size: 3.8pt; font-weight: 800;
    color: rgba(255,255,255,.92);
    text-transform: uppercase; letter-spacing: .05em; text-align: center;
    position: relative; z-index: 1;
}

.verso-divider {
    width: 22mm; height: .25mm;
    background: linear-gradient(90deg, transparent, rgba(201,168,76,.6), transparent);
    position: relative; z-index: 1;
}

.verso-sub {
    font-size: 3pt; color: rgba(255,255,255,.6);
    text-align: center; line-height: 1.6;
    position: relative; z-index: 1; padding: 0 2mm;
}

/* ══════════════════════════════════════════════════════════════════
   RESPONSIVE — l'échelle se réduit selon la largeur disponible
══════════════════════════════════════════════════════════════════ */
@media (max-width: 1100px) { :root { --card-scale: 2.0; } .cards-row { gap: 20px; } }
@media (max-width:  800px) { :root { --card-scale: 1.7; } .cards-row { gap: 16px; } }
@media (max-width:  640px) { :root { --card-scale: 1.3; } .cards-row { gap: 12px; } }
@media (max-width:  480px) {
    :root { --card-scale: 1.05; }
    .cards-row { flex-direction: column; align-items: center; gap: 16px; }
}

/* ══════════════════════════════════════════════════════════════════
   IMPRESSION
══════════════════════════════════════════════════════════════════ */
@media print {
    @page { size: 85.6mm 54mm; margin: 0; }

    /* Cacher tout l'habillage de la plateforme */
    .sidebar, .sidebar-overlay,
    .portal-top-navbar, [class*="portal-top-navbar"],
    .card-page-header, .face-label,
    .alert, .ai-agent-widget,
    .no-print { display: none !important; }

    .main-content  { margin-left: 0 !important; width: 100% !important; }
    .portal-page-body { padding: 0 !important; }
    body { background: #fff !important; }
    .card-page-wrap { padding: 0 !important; gap: 0; }
    .cards-row { display: block !important; gap: 0; }
    .card-wrapper-label { margin: 0; }

    /* Annuler le zoom écran */
    .card-outer { width: 85.6mm !important; height: 54mm !important; }

    .card-face {
        transform: none !important;
        width:  85.6mm !important;
        height: 54mm   !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        page-break-after: always;
        page-break-inside: avoid;
        position: relative !important;
    }

    .card-face:last-of-type { page-break-after: avoid; }
}
</style>
@endpush

@section('content')
@php
    $initials = Str::upper(mb_substr($student->first_name ?? $student->name, 0, 1))
              . Str::upper(mb_substr($student->last_name ?? '', 0, 1));
@endphp

<div class="card-page-wrap">

    {{-- En-tête (masqué à l'impression) --}}
    <div class="card-page-header">
        <h1 class="card-page-title"><i class="fas fa-id-card me-2"></i>Ma Carte Scolaire</h1>
        <p class="card-page-sub">{{ $school->name ?? 'AzelieEdu' }} · Année {{ $acadYear }}</p>
        <div class="card-page-actions">
            <button class="btn-card-print" onclick="window.print()">
                <i class="fas fa-print"></i>&nbsp; Imprimer Recto / Verso
            </button>
        </div>
        <p class="print-tip">
            Dans la fenêtre d'impression : désactivez <strong>« Adapter à la page »</strong>,
            réglez les marges sur <strong>Aucune</strong> et sélectionnez le format
            <strong>85,6 × 54 mm</strong> (CR80 / carte de crédit).
        </p>
    </div>

    {{-- Les deux faces --}}
    <div class="cards-row">

        {{-- ════════  RECTO  ════════ --}}
        <div class="card-wrapper-label">
            <span class="face-label">◆ Recto</span>
            <div class="card-outer">
                <div class="card-face recto">

                    <div class="r-header">
                        {{-- Logo de l'école (priorité : view composer → base64 → path → initiales) --}}
                        <div class="r-logo">
                            @if(!empty($schoolLogoDataUri))
                                <img src="{{ $schoolLogoDataUri }}" alt="Logo">
                            @elseif($school?->logo_data)
                                <img src="data:{{ $school->logo_mime ?? 'image/png' }};base64,{{ $school->logo_data }}" alt="Logo">
                            @elseif($school?->logo_path)
                                <img src="{{ Storage::url($school->logo_path) }}" alt="Logo">
                            @else
                                <span class="r-logo-initial">
                                    {{ Str::upper(Str::substr($school->name ?? 'E', 0, 2)) }}
                                </span>
                            @endif
                        </div>

                        <div class="r-school">
                            <div class="r-school-name">
                                {{ Str::upper(Str::limit($school->name ?? 'Établissement', 42)) }}
                            </div>
                            <div class="r-school-sub">
                                @if($school?->address)
                                    {{ Str::limit($school->address, 40) }}
                                @else
                                    Institut Supérieur · Carte Officielle
                                @endif
                            </div>
                        </div>

                        <div class="sn-flag" title="Sénégal">
                            <div class="s sg"></div>
                            <div class="s sy">★</div>
                            <div class="s sr"></div>
                        </div>
                    </div>

                    <div class="r-body">
                        <div class="r-photo">
                            @if($student->profile_photo_path)
                                <img src="{{ Storage::url($student->profile_photo_path) }}" alt="Photo">
                            @else
                                <span class="r-photo-initials">{{ $initials }}</span>
                            @endif
                        </div>

                        <div class="r-info">
                            <div class="r-badge">{{ $cardSettings['badge_text'] ?? 'Élève' }}</div>

                            <div class="r-name">
                                {{ Str::upper($student->last_name ?? '') }}&nbsp;{{ Str::upper($student->first_name ?? $student->name) }}
                            </div>

                            <div class="r-gold-line"></div>

                            @if(($cardSettings['show_dob'] ?? true) && $student->date_of_birth)
                            <div class="r-row">
                                <span class="r-lbl">Né(e) :</span>
                                <span class="r-val">
                                    {{ $student->date_of_birth->format('d/m/Y') }}
                                    @if($student->city) · {{ Str::upper($student->city) }} @endif
                                </span>
                            </div>
                            @endif

                            @if($cardSettings['show_nationality'] ?? true)
                            <div class="r-row">
                                <span class="r-lbl">Nationalité :</span>
                                <span class="r-val">{{ Str::upper($student->country ?? 'Sénégalaise') }}</span>
                            </div>
                            @endif

                            @if($student->schoolClass)
                            <div class="r-row">
                                <span class="r-lbl">Classe :</span>
                                <span class="r-val">
                                    {{ $student->schoolClass->name }}@if($student->schoolClass->level) — {{ $student->schoolClass->level->name }}@endif
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="r-footer">
                        <span class="r-footer-id">
                            N°&nbsp;{{ $student->identifier ?? str_pad((string)$student->id, 7, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="r-footer-sep"></span>
                        <span class="r-footer-year">{{ $acadYear }}</span>
                        <span class="r-footer-issued">Délivrée le : {{ $issuedDate }}</span>
                        <span class="r-footer-brand">{{ $cardSettings['footer_brand'] ?? 'AzelieEdu' }}</span>
                    </div>

                </div>
            </div>
        </div>

        {{-- ════════  VERSO  ════════ --}}
        <div class="card-wrapper-label">
            <span class="face-label">◆ Verso</span>
            <div class="card-outer">
                <div class="card-face verso">

                    <div class="verso-stripe top"></div>
                    <div class="verso-stripe bottom"></div>

                    <div class="verso-qr">
                        <div id="qrcode-print"></div>
                    </div>

                    <div class="verso-label">Scanner pour vérifier l'authenticité</div>

                    <div class="verso-divider"></div>

                    <div class="verso-sub">
                        {{ Str::upper(Str::limit($school->name ?? 'AzelieEdu', 38)) }}<br>
                        Carte étudiante officielle · {{ $acadYear }}
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- /cards-row --}}
</div>{{-- /card-page-wrap --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(function () {
    'use strict';

    var SIGNED_URL = @json($signedUrl);
    var SIZE = 83; /* 22 mm à 96 dpi */

    function buildQr() {
        var el = document.getElementById('qrcode-print');
        if (!el || typeof QRCode === 'undefined') return;
        new QRCode(el, {
            text:         SIGNED_URL,
            width:        SIZE,
            height:       SIZE,
            colorDark:    '#003087',
            colorLight:   '#ffffff',
            correctLevel: QRCode.CorrectLevel.H,
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildQr);
    } else {
        buildQr();
    }
})();
</script>
@endpush
