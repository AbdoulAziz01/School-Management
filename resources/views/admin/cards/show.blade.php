@php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Carte — '.($student->last_name ?? $student->name))

@push('styles')
<style>
:root {
    --c-blue:    {{ $cardSettings['primary_color'] }};
    --c-blue-dk: color-mix(in srgb, {{ $cardSettings['primary_color'] }} 70%, #000 30%);
    --c-blue-lt: color-mix(in srgb, {{ $cardSettings['primary_color'] }} 12%, #fff 88%);
    --c-gold:    {{ $cardSettings['accent_color'] }};
    --c-text:    #111;
    --c-muted:   #5a6475;
    --card-scale: 2.5;
}
@media (max-width:1100px){ :root{ --card-scale:2.0; } }
@media (max-width:800px) { :root{ --card-scale:1.7; } }
@media (max-width:640px) { :root{ --card-scale:1.3; } }
@media (max-width:480px) { :root{ --card-scale:1.05; } .cards-row{ flex-direction:column; align-items:center; } }

.card-outer{ width:calc(85.6mm * var(--card-scale)); height:calc(54mm * var(--card-scale)); position:relative; flex-shrink:0; }
.card-face { width:85.6mm; height:54mm; border-radius:3.2mm; overflow:hidden; position:absolute; top:0; left:0;
             transform:scale(var(--card-scale)); transform-origin:top left;
             box-shadow:0 8px 28px rgba(0,0,0,.28),0 2px 6px rgba(0,0,0,.16); }

/* RECTO */
.recto{ background:#fff; display:flex; flex-direction:column; }
.r-header{ background:var(--c-blue); height:13mm; flex-shrink:0; display:flex; align-items:center; padding:0 2.2mm; gap:1.8mm; }
.r-logo{ width:9mm; height:9mm; border-radius:50%; background:#fff; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0; border:.4mm solid rgba(255,255,255,.3); }
.r-logo img{ width:100%; height:100%; object-fit:contain; }
.r-logo-initial{ font-size:4.5pt; font-weight:900; color:var(--c-blue); }
.r-school{ flex:1; min-width:0; }
.r-school-name{ font-size:4.6pt; font-weight:800; color:#fff; text-transform:uppercase; letter-spacing:.04em; line-height:1.25; }
.r-school-sub{ font-size:3.6pt; color:rgba(255,255,255,.78); line-height:1.3; margin-top:.5mm; }
.sn-flag{ width:7.5mm; height:5mm; border-radius:.5mm; overflow:hidden; display:flex; flex-shrink:0; border:.2mm solid rgba(255,255,255,.25); }
.sn-flag .s{ flex:1; } .sn-flag .sg{ background:#00853F; } .sn-flag .sy{ background:#FDEF42; display:flex; align-items:center; justify-content:center; font-size:3.5pt; color:#00853F; } .sn-flag .sr{ background:#E31B23; }
.r-body{ flex:1; display:flex; padding:1.8mm 2mm 1mm; gap:2mm; min-height:0; }
.r-photo{ width:18mm; height:22mm; border:.35mm solid #b0b8c0; overflow:hidden; background:#eef0f3; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.r-photo img{ width:100%; height:100%; object-fit:cover; }
.r-photo-initials{ font-size:9pt; font-weight:900; color:var(--c-blue); }
.r-info{ flex:1; min-width:0; display:flex; flex-direction:column; justify-content:center; gap:.9mm; }
.r-badge{ display:inline-block; font-size:3.6pt; font-weight:800; color:var(--c-blue); background:var(--c-blue-lt); border-left:.7mm solid var(--c-blue); padding:.4mm 1.4mm; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.3mm; }
.r-name{ font-size:7.5pt; font-weight:900; color:#111; text-transform:uppercase; letter-spacing:.02em; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.r-gold-line{ width:14mm; height:.35mm; background:var(--c-gold); margin:.4mm 0; }
.r-row{ display:flex; gap:.8mm; align-items:baseline; line-height:1.4; }
.r-lbl{ font-size:3.4pt; color:var(--c-muted); white-space:nowrap; flex-shrink:0; }
.r-val{ font-size:4pt; font-weight:700; color:#111; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.r-footer{ background:linear-gradient(90deg, var(--c-blue) 0%, color-mix(in srgb, var(--c-blue) 80%, #4060ff 20%) 100%); height:8.5mm; flex-shrink:0; display:flex; align-items:center; padding:0 2.2mm; gap:1.5mm; }
.r-footer-id{ font-size:6pt; font-weight:900; color:#fff; letter-spacing:.04em; }
.r-footer-sep{ width:.3mm; height:4mm; background:rgba(255,255,255,.3); flex-shrink:0; }
.r-footer-year{ font-size:3.8pt; color:rgba(255,255,255,.85); }
.r-footer-issued{ font-size:3.2pt; color:rgba(255,255,255,.65); margin-left:auto; }
.r-footer-brand{ font-size:3pt; color:rgba(255,255,255,.45); font-style:italic; }

/* VERSO */
.verso{ background:linear-gradient(150deg, color-mix(in srgb,var(--c-blue) 70%,#000 30%) 0%, var(--c-blue) 55%, color-mix(in srgb,var(--c-blue) 70%,#000 30%) 100%); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1.6mm; position:relative; overflow:hidden; }
.verso-stripe{ position:absolute; left:0; right:0; height:.6mm; background:linear-gradient(90deg,transparent,var(--c-gold),transparent); }
.verso-stripe.top{ top:0; } .verso-stripe.bottom{ bottom:0; }
.verso-qr{ background:#fff; padding:1.5mm; border-radius:1.5mm; box-shadow:0 1.5mm 5mm rgba(0,0,0,.45); position:relative; z-index:1; }
#qrcode-admin{ width:22mm; height:22mm; display:flex; align-items:center; justify-content:center; }
#qrcode-admin canvas, #qrcode-admin img{ width:22mm!important; height:22mm!important; }
.verso-label{ font-size:3.8pt; font-weight:800; color:rgba(255,255,255,.92); text-transform:uppercase; letter-spacing:.05em; text-align:center; position:relative; z-index:1; }
.verso-divider{ width:22mm; height:.25mm; background:linear-gradient(90deg,transparent,rgba(201,168,76,.6),transparent); position:relative; z-index:1; }
.verso-sub{ font-size:3pt; color:rgba(255,255,255,.6); text-align:center; line-height:1.6; position:relative; z-index:1; padding:0 2mm; }

.cards-row{ display:flex; gap:28px; flex-wrap:wrap; justify-content:center; align-items:flex-start; }
.card-wrapper-label{ display:flex; flex-direction:column; align-items:center; gap:6px; }
.face-label{ font-size:.65rem; font-weight:800; color:#78716c; text-transform:uppercase; letter-spacing:.12em; }

@media print {
    @page{ size:85.6mm 54mm; margin:0; }
    .sidebar,.sidebar-overlay,.topbar,.admin-topbar,.navbar,.breadcrumb,.btn-toolbar,.card-header,.card-footer,
    .admin-toolbar,.face-label,.no-print{ display:none!important; }
    .content-wrapper,.main-content{ margin:0!important; padding:0!important; width:100%!important; }
    body{ background:#fff!important; }
    .cards-row{ display:block!important; gap:0; }
    .card-outer{ width:85.6mm!important; height:54mm!important; }
    .card-face{ transform:none!important; width:85.6mm!important; height:54mm!important; border-radius:0!important; box-shadow:none!important; page-break-after:always; page-break-inside:avoid; position:relative!important; }
    .card-face:last-of-type{ page-break-after:avoid; }
}
</style>
@endpush

@section('content')
@php
$initials = Str::upper(mb_substr($student->first_name ?? $student->name, 0, 1))
          . Str::upper(mb_substr($student->last_name ?? '', 0, 1));
@endphp

<div class="container-fluid">

    {{-- Toolbar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 no-print">
        <div>
            <a href="{{ route('admin.cards.index') }}" class="text-muted text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i> Retour à la liste
            </a>
            <h1 class="h4 mb-0 mt-1 fw-800" style="color:#92400e;">
                {{ strtoupper($student->last_name ?? '') }} {{ $student->first_name }}
            </h1>
            @if($student->schoolClass)
                <span class="badge mt-1" style="background:#fef3c7;color:#92400e;">
                    {{ $student->schoolClass->name }}
                </span>
            @endif
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.cards.settings') }}"
               class="btn btn-sm fw-600"
               style="border:1.5px solid #003087;color:#003087;border-radius:8px;">
                <i class="fas fa-palette me-1"></i> Personnaliser
            </a>
            <button class="btn btn-sm fw-700"
                    style="background:#003087;color:#fff;border-radius:8px;"
                    onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimer
            </button>
        </div>
    </div>

    {{-- Cartes --}}
    <div class="cards-row">

        {{-- RECTO --}}
        <div class="card-wrapper-label">
            <span class="face-label">◆ Recto</span>
            <div class="card-outer">
                <div class="card-face recto">
                    <div class="r-header">
                        <div class="r-logo">
                            @if(!empty($schoolLogoDataUri))
                                <img src="{{ $schoolLogoDataUri }}" alt="Logo">
                            @elseif($school?->logo_data)
                                <img src="data:{{ $school->logo_mime ?? 'image/png' }};base64,{{ $school->logo_data }}" alt="Logo">
                            @elseif($school?->logo_path)
                                <img src="{{ Storage::url($school->logo_path) }}" alt="Logo">
                            @else
                                <span class="r-logo-initial">{{ Str::upper(Str::substr($school->name ?? 'E', 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="r-school">
                            <div class="r-school-name">{{ Str::upper(Str::limit($school->name ?? 'Établissement', 42)) }}</div>
                            <div class="r-school-sub">{{ $cardSettings['header_subtitle'] ?? 'Institut Supérieur · Carte Officielle' }}</div>
                        </div>
                        <div class="sn-flag"><div class="s sg"></div><div class="s sy">★</div><div class="s sr"></div></div>
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
                            <div class="r-name">{{ Str::upper($student->last_name ?? '') }}&nbsp;{{ Str::upper($student->first_name ?? $student->name) }}</div>
                            <div class="r-gold-line"></div>
                            @if(($cardSettings['show_dob'] ?? true) && $student->date_of_birth)
                            <div class="r-row">
                                <span class="r-lbl">Né(e) :</span>
                                <span class="r-val">{{ $student->date_of_birth->format('d/m/Y') }}@if($student->city) · {{ Str::upper($student->city) }}@endif</span>
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
                                <span class="r-val">{{ $student->schoolClass->name }}@if($student->schoolClass->level) — {{ $student->schoolClass->level->name }}@endif</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="r-footer">
                        <span class="r-footer-id">N°&nbsp;{{ $student->identifier ?? str_pad((string)$student->id, 7, '0', STR_PAD_LEFT) }}</span>
                        <span class="r-footer-sep"></span>
                        <span class="r-footer-year">{{ $acadYear }}</span>
                        <span class="r-footer-issued">Délivrée le : {{ $issuedDate }}</span>
                        <span class="r-footer-brand">{{ $cardSettings['footer_brand'] ?? 'AzelieEdu' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- VERSO --}}
        <div class="card-wrapper-label">
            <span class="face-label">◆ Verso</span>
            <div class="card-outer">
                <div class="card-face verso">
                    <div class="verso-stripe top"></div>
                    <div class="verso-stripe bottom"></div>
                    <div class="verso-qr"><div id="qrcode-admin"></div></div>
                    <div class="verso-label">Scanner pour vérifier l'authenticité</div>
                    <div class="verso-divider"></div>
                    <div class="verso-sub">
                        {{ Str::upper(Str::limit($school->name ?? 'AzelieEdu', 38)) }}<br>
                        Carte étudiante officielle · {{ $acadYear }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(function(){
    var el = document.getElementById('qrcode-admin');
    if (!el || typeof QRCode === 'undefined') return;
    new QRCode(el, {
        text: @json($signedUrl),
        width: 83, height: 83,
        colorDark: '{{ $cardSettings['primary_color'] }}',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H,
    });
})();
</script>
@endpush
