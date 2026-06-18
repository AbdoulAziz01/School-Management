@extends('admin.layouts.app')

@section('title', 'Personnalisation de la carte')

@push('styles')
<style>
/* ── Variables live (mises à jour par JS) ── */
:root {
    --c-blue:   {{ $cardSettings['primary_color'] }};
    --c-gold:   {{ $cardSettings['accent_color'] }};
    --c-blue-lt: color-mix(in srgb, {{ $cardSettings['primary_color'] }} 12%, #fff 88%);
}

.settings-grid {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 28px;
    align-items: start;
}

@media (max-width: 1024px) { .settings-grid { grid-template-columns: 1fr; } }

/* ── Aperçu carte miniature ── */
.preview-wrap {
    position: sticky;
    top: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.preview-title {
    font-size: .7rem;
    font-weight: 800;
    color: #78716c;
    text-transform: uppercase;
    letter-spacing: .1em;
}

/* Carte à 2× pour l'aperçu */
:root { --prev-scale: 2.0; }
@media (max-width:640px){ :root { --prev-scale: 1.2; } }

.prev-outer { width: calc(85.6mm * var(--prev-scale)); height: calc(54mm * var(--prev-scale)); position: relative; flex-shrink: 0; }
.prev-face  { width: 85.6mm; height: 54mm; border-radius: 3.2mm; overflow: hidden; position: absolute; top:0; left:0;
              transform: scale(var(--prev-scale)); transform-origin: top left;
              box-shadow: 0 6px 24px rgba(0,0,0,.25); }

/* RECTO preview */
.recto { background:#fff; display:flex; flex-direction:column; }
.r-header { background:var(--c-blue); height:13mm; flex-shrink:0; display:flex; align-items:center; padding:0 2.2mm; gap:1.8mm; }
.r-logo { width:9mm; height:9mm; border-radius:50%; background:#fff; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0; border:.4mm solid rgba(255,255,255,.3); }
.r-logo img { width:100%; height:100%; object-fit:contain; }
.r-logo-initial { font-size:4.5pt; font-weight:900; color:var(--c-blue); }
.r-school { flex:1; min-width:0; }
.r-school-name { font-size:4.6pt; font-weight:800; color:#fff; text-transform:uppercase; letter-spacing:.04em; line-height:1.25; }
.r-school-sub  { font-size:3.6pt; color:rgba(255,255,255,.78); line-height:1.3; margin-top:.5mm; }
.sn-flag { width:7.5mm; height:5mm; border-radius:.5mm; overflow:hidden; display:flex; flex-shrink:0; border:.2mm solid rgba(255,255,255,.25); }
.sn-flag .s{ flex:1; } .sn-flag .sg{ background:#00853F; } .sn-flag .sy{ background:#FDEF42; display:flex; align-items:center; justify-content:center; font-size:3.5pt; color:#00853F; } .sn-flag .sr{ background:#E31B23; }
.r-body { flex:1; display:flex; padding:1.8mm 2mm 1mm; gap:2mm; min-height:0; }
.r-photo { width:18mm; height:22mm; border:.35mm solid #b0b8c0; overflow:hidden; background:#eef0f3; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:9pt; font-weight:900; color:var(--c-blue); }
.r-info { flex:1; min-width:0; display:flex; flex-direction:column; justify-content:center; gap:.9mm; }
.r-badge { display:inline-block; font-size:3.6pt; font-weight:800; color:var(--c-blue); background:var(--c-blue-lt); border-left:.7mm solid var(--c-blue); padding:.4mm 1.4mm; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.3mm; }
.r-name { font-size:7.5pt; font-weight:900; color:#111; text-transform:uppercase; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.r-gold-line { width:14mm; height:.35mm; background:var(--c-gold); margin:.4mm 0; }
.r-row { display:flex; gap:.8mm; align-items:baseline; }
.r-lbl { font-size:3.4pt; color:#5a6475; white-space:nowrap; flex-shrink:0; }
.r-val { font-size:4pt; font-weight:700; color:#111; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.r-footer { background:linear-gradient(90deg,var(--c-blue) 0%, color-mix(in srgb,var(--c-blue) 80%,#4060ff 20%) 100%); height:8.5mm; flex-shrink:0; display:flex; align-items:center; padding:0 2.2mm; gap:1.5mm; }
.r-footer-id { font-size:6pt; font-weight:900; color:#fff; } .r-footer-sep { width:.3mm; height:4mm; background:rgba(255,255,255,.3); flex-shrink:0; }
.r-footer-year { font-size:3.8pt; color:rgba(255,255,255,.85); }
.r-footer-issued { font-size:3.2pt; color:rgba(255,255,255,.65); margin-left:auto; }
.r-footer-brand  { font-size:3pt; color:rgba(255,255,255,.45); font-style:italic; }

/* VERSO preview */
.verso { background:linear-gradient(150deg,color-mix(in srgb,var(--c-blue) 70%,#000 30%) 0%,var(--c-blue) 55%,color-mix(in srgb,var(--c-blue) 70%,#000 30%) 100%); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1.6mm; position:relative; overflow:hidden; }
.verso-stripe{ position:absolute; left:0; right:0; height:.6mm; background:linear-gradient(90deg,transparent,var(--c-gold),transparent); }
.verso-stripe.top{ top:0; } .verso-stripe.bottom{ bottom:0; }
.verso-qr-box { background:#fff; padding:1.5mm; border-radius:1.5mm; box-shadow:0 1.5mm 5mm rgba(0,0,0,.45); }
.verso-qr-placeholder { width:22mm; height:22mm; display:flex; align-items:center; justify-content:center; font-size:5pt; color:#003087; font-weight:700; }
.verso-label { font-size:3.8pt; font-weight:800; color:rgba(255,255,255,.92); text-transform:uppercase; letter-spacing:.05em; text-align:center; }
.verso-divider{ width:22mm; height:.25mm; background:linear-gradient(90deg,transparent,rgba(201,168,76,.6),transparent); }
.verso-sub{ font-size:3pt; color:rgba(255,255,255,.6); text-align:center; line-height:1.6; padding:0 2mm; }

/* ── Formulaire ── */
.settings-form .section-title {
    font-size: .75rem;
    font-weight: 800;
    color: #92400e;
    text-transform: uppercase;
    letter-spacing: .08em;
    border-bottom: 2px solid #fde68a;
    padding-bottom: 8px;
    margin-bottom: 16px;
}

.color-field {
    display: flex;
    align-items: center;
    gap: 10px;
}

.color-field input[type=color] {
    width: 44px; height: 44px;
    border: 2px solid #fde68a;
    border-radius: 10px;
    cursor: pointer;
    padding: 2px;
    background: none;
}

.color-field input[type=text] {
    font-family: monospace;
    font-size: .85rem;
    max-width: 110px;
}
</style>
@endpush

@section('content')
@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
$schoolName = $school->name ?? 'Établissement';
$acadYear   = date('Y').'/'.(date('Y') + 1);
@endphp

<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="mb-3 no-print">
        <a href="{{ route('admin.cards.index') }}" class="text-muted text-decoration-none small">
            <i class="fas fa-arrow-left me-1"></i> Cartes scolaires
        </a>
        <h1 class="h4 mt-1 mb-0 fw-800" style="color:#92400e;">
            <i class="fas fa-palette me-2" style="color:#f59e0b;"></i>Personnalisation de la carte
        </h1>
        <p class="text-muted small">Les changements s'appliquent immédiatement à l'aperçu avant sauvegarde.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="settings-grid">

        {{-- ════ FORMULAIRE ════ --}}
        <div class="settings-form">
            <form method="POST" action="{{ route('admin.cards.settings.save') }}" id="card-settings-form">
                @csrf

                {{-- Couleurs --}}
                <div class="card mb-3" style="border:1px solid #fde68a;border-radius:14px;">
                    <div class="card-body">
                        <div class="section-title"><i class="fas fa-fill-drip me-1"></i> Couleurs</div>

                        <div class="mb-3">
                            <label class="form-label fw-600 small">Couleur principale (en-tête, pied, QR)</label>
                            <div class="color-field">
                                <input type="color" id="picker-primary" value="{{ $cardSettings['primary_color'] }}"
                                       oninput="syncColor('primary_color', this.value)">
                                <input type="text" id="hex-primary" name="primary_color"
                                       class="form-control form-control-sm"
                                       value="{{ $cardSettings['primary_color'] }}"
                                       pattern="^#[0-9a-fA-F]{6}$"
                                       oninput="syncColorFromText('primary_color', this.value)">
                            </div>
                            @error('primary_color')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-600 small">Couleur accent (ligne, filets)</label>
                            <div class="color-field">
                                <input type="color" id="picker-accent" value="{{ $cardSettings['accent_color'] }}"
                                       oninput="syncColor('accent_color', this.value)">
                                <input type="text" id="hex-accent" name="accent_color"
                                       class="form-control form-control-sm"
                                       value="{{ $cardSettings['accent_color'] }}"
                                       pattern="^#[0-9a-fA-F]{6}$"
                                       oninput="syncColorFromText('accent_color', this.value)">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Textes --}}
                <div class="card mb-3" style="border:1px solid #fde68a;border-radius:14px;">
                    <div class="card-body">
                        <div class="section-title"><i class="fas fa-font me-1"></i> Textes</div>

                        <div class="mb-3">
                            <label class="form-label fw-600 small">Insigne (badge sous le nom)</label>
                            <input type="text" name="badge_text" id="field-badge"
                                   class="form-control form-control-sm @error('badge_text') is-invalid @enderror"
                                   value="{{ old('badge_text', $cardSettings['badge_text']) }}"
                                   maxlength="30"
                                   oninput="document.getElementById('prev-badge').textContent = this.value">
                            @error('badge_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600 small">Sous-titre de l'en-tête</label>
                            <input type="text" name="header_subtitle" id="field-subtitle"
                                   class="form-control form-control-sm"
                                   value="{{ old('header_subtitle', $cardSettings['header_subtitle']) }}"
                                   maxlength="80"
                                   oninput="document.getElementById('prev-subtitle').textContent = this.value">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-600 small">Marque en pied de carte</label>
                            <input type="text" name="footer_brand"
                                   class="form-control form-control-sm"
                                   value="{{ old('footer_brand', $cardSettings['footer_brand']) }}"
                                   maxlength="30"
                                   oninput="document.getElementById('prev-brand').textContent = this.value">
                        </div>
                    </div>
                </div>

                {{-- Champs visibles --}}
                <div class="card mb-4" style="border:1px solid #fde68a;border-radius:14px;">
                    <div class="card-body">
                        <div class="section-title"><i class="fas fa-eye me-1"></i> Informations affichées</div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="show_dob" value="1" id="chk-dob"
                                   {{ ($cardSettings['show_dob'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="chk-dob">Date de naissance</label>
                        </div>

                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="show_nationality" value="1" id="chk-nat"
                                   {{ ($cardSettings['show_nationality'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="chk-nat">Nationalité</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn w-100 fw-700"
                        style="background:#003087;color:#fff;border-radius:12px;padding:13px;">
                    <i class="fas fa-save me-2"></i> Sauvegarder la personnalisation
                </button>
            </form>
        </div>

        {{-- ════ APERÇU LIVE ════ --}}
        <div class="preview-wrap">
            <span class="preview-title">◆ Aperçu en direct</span>

            {{-- Recto --}}
            <div>
                <div class="preview-title mb-1">Recto</div>
                <div class="prev-outer">
                    <div class="prev-face recto">
                        <div class="r-header">
                            <div class="r-logo">
                                @if(!empty($schoolLogoDataUri))
                                    <img src="{{ $schoolLogoDataUri }}" alt="">
                                @elseif($school?->logo_data)
                                    <img src="data:{{ $school->logo_mime ?? 'image/png' }};base64,{{ $school->logo_data }}" alt="">
                                @else
                                    <span class="r-logo-initial">{{ Str::upper(Str::substr($schoolName, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div class="r-school">
                                <div class="r-school-name">{{ Str::upper(Str::limit($schoolName, 42)) }}</div>
                                <div class="r-school-sub" id="prev-subtitle">{{ $cardSettings['header_subtitle'] }}</div>
                            </div>
                            <div class="sn-flag"><div class="s sg"></div><div class="s sy">★</div><div class="s sr"></div></div>
                        </div>
                        <div class="r-body">
                            <div class="r-photo">AB</div>
                            <div class="r-info">
                                <div class="r-badge" id="prev-badge">{{ $cardSettings['badge_text'] }}</div>
                                <div class="r-name">DIALLO Aminata</div>
                                <div class="r-gold-line"></div>
                                <div class="r-row"><span class="r-lbl">Né(e) :</span><span class="r-val">12/03/2005 · DAKAR</span></div>
                                <div class="r-row"><span class="r-lbl">Nationalité :</span><span class="r-val">SÉNÉGALAISE</span></div>
                                <div class="r-row"><span class="r-lbl">Classe :</span><span class="r-val">Terminale A</span></div>
                            </div>
                        </div>
                        <div class="r-footer">
                            <span class="r-footer-id">N° 0001234</span>
                            <span class="r-footer-sep"></span>
                            <span class="r-footer-year">{{ $acadYear }}</span>
                            <span class="r-footer-issued">Délivrée le : {{ now()->format('d/m/Y') }}</span>
                            <span class="r-footer-brand" id="prev-brand">{{ $cardSettings['footer_brand'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Verso --}}
            <div>
                <div class="preview-title mb-1">Verso</div>
                <div class="prev-outer">
                    <div class="prev-face verso">
                        <div class="verso-stripe top"></div>
                        <div class="verso-stripe bottom"></div>
                        <div class="verso-qr-box">
                            <div class="verso-qr-placeholder">QR CODE</div>
                        </div>
                        <div class="verso-label">Scanner pour vérifier l'authenticité</div>
                        <div class="verso-divider"></div>
                        <div class="verso-sub">{{ Str::upper(Str::limit($schoolName, 38)) }}<br>Carte officielle · {{ $acadYear }}</div>
                    </div>
                </div>
            </div>

        </div>{{-- /preview-wrap --}}

    </div>{{-- /settings-grid --}}
</div>
@endsection

@push('scripts')
<script>
function syncColor(prop, value) {
    // Met à jour le champ texte
    var map = { 'primary_color': 'hex-primary', 'accent_color': 'hex-accent' };
    document.getElementById(map[prop]).value = value;
    updateCssVars();
}

function syncColorFromText(prop, value) {
    if (!/^#[0-9a-fA-F]{6}$/.test(value)) return;
    var map = { 'primary_color': 'picker-primary', 'accent_color': 'picker-accent' };
    document.getElementById(map[prop]).value = value;
    updateCssVars();
}

function updateCssVars() {
    var primary = document.getElementById('hex-primary').value;
    var accent  = document.getElementById('hex-accent').value;
    var root    = document.documentElement;

    if (/^#[0-9a-fA-F]{6}$/.test(primary)) {
        root.style.setProperty('--c-blue', primary);
        root.style.setProperty('--c-blue-lt', hexToRgba(primary, 0.12));
    }
    if (/^#[0-9a-fA-F]{6}$/.test(accent)) {
        root.style.setProperty('--c-gold', accent);
    }
}

function hexToRgba(hex, alpha) {
    var r = parseInt(hex.slice(1,3),16);
    var g = parseInt(hex.slice(3,5),16);
    var b = parseInt(hex.slice(5,7),16);
    return 'rgba('+r+','+g+','+b+','+alpha+')';
}
</script>
@endpush
