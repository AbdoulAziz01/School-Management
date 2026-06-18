<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification — {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            background:linear-gradient(135deg,#001f5b,#003087);
            font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
            padding:20px;
        }
        .card {
            background:#fff; border-radius:20px; padding:32px 28px;
            max-width:360px; width:100%;
            box-shadow:0 20px 60px rgba(0,0,0,.35);
            text-align:center;
        }
        .valid-badge {
            display:inline-flex; align-items:center; gap:8px;
            background:#f0fdf4; color:#15803d; border:1.5px solid #86efac;
            border-radius:99px; padding:6px 16px; font-size:.82rem; font-weight:700;
            margin-bottom:20px;
        }
        .valid-dot { width:8px; height:8px; border-radius:50%; background:#22c55e; }
        .photo {
            width:88px; height:88px; border-radius:50%;
            background:linear-gradient(135deg,#003087,#1a4a9a);
            margin:0 auto 16px; overflow:hidden;
            display:flex; align-items:center; justify-content:center;
            border:3px solid #003087;
        }
        .photo img { width:100%; height:100%; object-fit:cover; }
        .photo-initials { font-size:1.8rem; font-weight:800; color:#fff; }
        h2 { font-size:1.15rem; font-weight:800; color:#111; margin-bottom:4px; }
        .sub { font-size:.8rem; color:#6b7280; margin-bottom:20px; }
        .info-grid {
            background:#f9fafb; border-radius:12px; padding:14px;
            text-align:left; display:flex; flex-direction:column; gap:8px;
            margin-bottom:20px;
        }
        .info-row { display:flex; justify-content:space-between; gap:8px; }
        .info-label { font-size:.75rem; color:#9ca3af; }
        .info-value { font-size:.8rem; font-weight:700; color:#111; text-align:right; }
        .school-name {
            font-size:.72rem; color:#6b7280; border-top:1px solid #e5e7eb;
            padding-top:12px; line-height:1.5;
        }
        .btn {
            display:inline-block; background:#003087; color:#fff; border-radius:10px;
            padding:10px 24px; font-size:.85rem; font-weight:700; text-decoration:none;
            margin-top:4px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="valid-badge"><span class="valid-dot"></span> Carte valide ✓</div>

    <div class="photo">
        @if($student->profile_photo_path)
            <img src="{{ Storage::url($student->profile_photo_path) }}" alt="Photo">
        @else
            <span class="photo-initials">
                {{ strtoupper(mb_substr($student->first_name ?? $student->name, 0, 1)) }}{{ strtoupper(mb_substr($student->last_name ?? '', 0, 1)) }}
            </span>
        @endif
    </div>

    <h2>{{ strtoupper($student->last_name ?? '') }} {{ $student->first_name }}</h2>
    <p class="sub">Étudiant(e) enregistré(e)</p>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Matricule</span>
            <span class="info-value">{{ $student->identifier ?? '—' }}</span>
        </div>
        @if($student->schoolClass)
        <div class="info-row">
            <span class="info-label">Classe</span>
            <span class="info-value">{{ $student->schoolClass->name }}</span>
        </div>
        @endif
        @if($student->date_of_birth)
        <div class="info-row">
            <span class="info-label">Date de naissance</span>
            <span class="info-value">{{ $student->date_of_birth->format('d/m/Y') }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Année académique</span>
            <span class="info-value">{{ date('Y') }}/{{ date('Y') + 1 }}</span>
        </div>
    </div>

    <p class="school-name">{{ $student->school?->name ?? 'EduManager' }}</p>

    @if(auth()->id() !== $student->id)
        <a href="{{ route('admin.dashboard') }}" class="btn" style="margin-top:14px;">
            ← Tableau de bord
        </a>
    @endif
</div>
</body>
</html>
