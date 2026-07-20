<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletins annuels {{ $class->name }} — {{ $academicYear->name }}</title>
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
        .semester-block { margin-bottom: 10px; }
        .semester-title { background: #f5f5f5; padding: 4px 8px; font-weight: bold; color: #1a5f2a; font-size: 10px; }
        .summary { margin-top: 10px; padding: 8px; border: 2px solid #f9a825; background: #fffde7; text-align: center; }
        .summary .avg { font-size: 22px; font-weight: bold; color: #1a5f2a; }
        .summary strong { color: #1a5f2a; }
        .decision-success { color: #1a5f2a; }
        .decision-warning { color: #b45309; }
        .decision-danger { color: #b91c1c; }
        .footer-note { font-size: 8px; color: #666; text-align: center; margin-top: 12px; }
    </style>
</head>
<body>
@foreach($bulletins as $bulletin)
    @php
        $info = $bulletin['studentInfo'];
        $isPrimaire = $bulletin['isPrimaire'];
        $maxGrade = $bulletin['overallMaxGrade'];
        $maxLabel = rtrim(rtrim(number_format($maxGrade, 2), '0'), '.');
    @endphp
    <div class="page">
        <div class="header">
            <h1>{{ $schoolName }}</h1>
            <p>BULLETIN ANNUEL — {{ $academicYear->name }}</p>
        </div>

        <table class="info">
            <tr>
                <td class="label">Élève</td><td>{{ $info['name'] }}</td>
                <td class="label">Identifiant</td><td>{{ $info['identifier'] }}</td>
            </tr>
            <tr>
                <td class="label">Classe</td><td>{{ $info['class'] }}</td>
                <td class="label">Niveau</td><td>{{ $info['level'] }}{{ $info['serie'] ? ' — '.$info['serie'] : '' }}</td>
            </tr>
            <tr>
                <td class="label">Date de naissance</td><td>{{ $info['date_of_birth'] }}</td>
                <td class="label">Absences</td><td>{{ $bulletin['absenceCount'] ?? 0 }}</td>
            </tr>
        </table>

        @if($isPrimaire)
            <div class="semester-block">
                <div class="semester-title">COMPOSITIONS DE L'ANNÉE</div>
                <table class="grades">
                    <thead>
                        <tr><th>Matière</th><th>Coef.</th><th>Moyenne</th></tr>
                    </thead>
                    <tbody>
                        @forelse($bulletin['semestre1Data']['data'] as $data)
                            @php $subjectMax = $data['max_grade'] ?? 10; @endphp
                            <tr>
                                <td class="subject">{{ $data['subject'] }}</td>
                                <td>{{ $data['coefficient'] }}</td>
                                <td>
                                    {{ $data['moyenne_matiere'] ?? '—' }}@if($data['moyenne_matiere'] !== null)/{{ rtrim(rtrim(number_format($subjectMax, 2), '0'), '.') }}@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Aucune note enregistrée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="semester-block">
                <div class="semester-title">SEMESTRE 1</div>
                <table class="grades">
                    <thead>
                        <tr><th>Matière</th><th>Coef.</th><th>Moyenne</th></tr>
                    </thead>
                    <tbody>
                        @forelse($bulletin['semestre1Data']['data'] as $data)
                            <tr>
                                <td class="subject">{{ $data['subject'] }}</td>
                                <td>{{ $data['coefficient'] }}</td>
                                <td>{{ $data['moyenne_matiere'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Aucune note enregistrée.</td></tr>
                        @endforelse
                        <tr class="total">
                            <td colspan="2">Moyenne Semestre 1</td>
                            <td>{{ $bulletin['semestre1Data']['moyenne'] > 0 ? number_format($bulletin['semestre1Data']['moyenne'], 2) : '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="semester-block">
                <div class="semester-title">SEMESTRE 2</div>
                <table class="grades">
                    <thead>
                        <tr><th>Matière</th><th>Coef.</th><th>Moyenne</th></tr>
                    </thead>
                    <tbody>
                        @forelse($bulletin['semestre2Data']['data'] as $data)
                            <tr>
                                <td class="subject">{{ $data['subject'] }}</td>
                                <td>{{ $data['coefficient'] }}</td>
                                <td>{{ $data['moyenne_matiere'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Aucune note enregistrée.</td></tr>
                        @endforelse
                        <tr class="total">
                            <td colspan="2">Moyenne Semestre 2</td>
                            <td>{{ $bulletin['semestre2Data']['moyenne'] > 0 ? number_format($bulletin['semestre2Data']['moyenne'], 2) : '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <div class="summary">
            <div>MOYENNE ANNUELLE</div>
            <div class="avg">{{ number_format($bulletin['moyenneAnnuelle'], 2) }}/{{ $maxLabel }}</div>
            @if($bulletin['decision']['mention'])
                <div><strong>{{ $bulletin['decision']['mention'] }}</strong></div>
            @endif
            <div class="decision-{{ $bulletin['decision']['color'] }}">{{ $bulletin['decision']['text'] }}</div>
        </div>

        <p class="footer-note">Document généré le {{ $generatedAt }} — {{ $schoolName }}</p>
    </div>
@endforeach
</body>
</html>
