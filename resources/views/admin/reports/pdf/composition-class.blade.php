<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $compositionLabel }} — {{ $class->name }} — {{ $academicYear->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; margin: 0; }
        .page { page-break-after: always; padding: 12px 16px; }
        .page:last-child { page-break-after: auto; }
        .header { background: #1a5f2a; color: #fff; padding: 12px; text-align: center; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 14px; }
        .header p { margin: 4px 0 0; font-size: 9px; opacity: 0.9; }
        .info { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .info td { padding: 3px 6px; vertical-align: top; }
        .info .label { font-weight: bold; color: #1a5f2a; width: 28%; }
        table.grades { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.grades th { background: #1a5f2a; color: #fff; padding: 5px 3px; font-size: 8px; border: 1px solid #155724; }
        table.grades td { border: 1px solid #ccc; padding: 4px 3px; text-align: center; font-size: 9px; }
        table.grades td.subject { text-align: left; font-weight: 600; }
        table.grades tr.total td { background: #1a5f2a; color: #fff; font-weight: bold; }
        .summary { margin-top: 10px; padding: 8px; border: 2px solid #f9a825; background: #fffde7; text-align: center; }
        .summary .avg { font-size: 20px; font-weight: bold; color: #1a5f2a; }
        .footer-note { font-size: 8px; color: #666; text-align: center; margin-top: 12px; }
    </style>
</head>
<body>
@foreach($bulletins as $bulletin)
    @php
        $info = $bulletin['studentInfo'];
        $maxGrade = $bulletin['overallMaxGrade'];
        $maxLabel = rtrim(rtrim(number_format($maxGrade, 2), '0'), '.');
        $totalCoef = collect($bulletin['bulletinData'])->sum('coefficient');
    @endphp
    <div class="page">
        <div class="header">
            <h1>{{ $schoolName }}</h1>
            <p>{{ strtoupper($compositionLabel) }} — {{ $academicYear->name }}</p>
        </div>

        <table class="info">
            <tr>
                <td class="label">Élève</td><td>{{ $info['name'] }}</td>
                <td class="label">Identifiant</td><td>{{ $info['identifier'] }}</td>
            </tr>
            <tr>
                <td class="label">Classe</td><td>{{ $info['class'] }}</td>
                <td class="label">Niveau</td><td>{{ $info['level'] }}</td>
            </tr>
        </table>

        <table class="grades">
            <thead>
                <tr><th>Matière</th><th>Coef.</th><th>Note</th><th>Appréciation</th></tr>
            </thead>
            <tbody>
                @forelse($bulletin['bulletinData'] as $data)
                    @php $subjectMax = $data['max_grade'] ?? $maxGrade; @endphp
                    <tr>
                        <td class="subject">{{ $data['subject'] }}</td>
                        <td>{{ $data['coefficient'] }}</td>
                        <td>
                            {{ $data['moyenne_matiere'] ?? '—' }}@if($data['moyenne_matiere'] !== null)/{{ rtrim(rtrim(number_format($subjectMax, 2), '0'), '.') }}@endif
                        </td>
                        <td>{{ $data['appreciation'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Aucune note enregistrée pour cette composition.</td></tr>
                @endforelse
                @if(count($bulletin['bulletinData']) > 0)
                    <tr class="total">
                        <td>TOTAL</td>
                        <td>{{ $totalCoef }}</td>
                        <td colspan="2">{{ $bulletin['moyenne'] !== null ? number_format($bulletin['moyenne'], 2) : '—' }}/{{ $maxLabel }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="summary">
            <div>MOYENNE — {{ strtoupper($compositionLabel) }}</div>
            <div class="avg">{{ $bulletin['moyenne'] !== null ? number_format($bulletin['moyenne'], 2) : '—' }}/{{ $maxLabel }}</div>
        </div>

        <p class="footer-note">Document généré le {{ $generatedAt }} — {{ $schoolName }}</p>
    </div>
@endforeach
</body>
</html>
