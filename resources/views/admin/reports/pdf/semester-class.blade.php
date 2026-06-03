<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletins {{ $class->name }} — S{{ $semester }}</title>
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
        .summary { margin-top: 10px; padding: 8px; border: 2px solid #f9a825; background: #fffde7; }
        .summary strong { color: #1a5f2a; }
        .stats { font-size: 9px; color: #444; margin-top: 6px; }
        .footer-note { font-size: 8px; color: #666; text-align: center; margin-top: 12px; }
    </style>
</head>
<body>
@foreach($bulletins as $bulletin)
    @php
        $info = $bulletin['studentInfo'];
        $data = $bulletin['bulletinData'];
        $avg = $bulletin['generalAverage'];
        $rank = $bulletin['rankData'];
        $stats = $bulletin['classStats'];
        $usesLmd = $bulletin['usesLmd'] ?? false;
        $totalCoef = collect($data)->sum('coefficient');
        $totalPoints = collect($data)->sum('points');
    @endphp
    <div class="page">
        <div class="header">
            <h1>{{ $schoolName }}</h1>
            <p>BULLETIN SEMESTRIEL — Semestre {{ $semester }} — {{ $academicYear->name }}</p>
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
                <td class="label">Rang</td>
                <td>@if($rank['rank']){{ $rank['rank'] }}<sup>e</sup> / {{ $rank['total'] }}@else — @endif</td>
            </tr>
        </table>

        <table class="grades">
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Coef.</th>
                    @unless($usesLmd)
                        <th>D1</th><th>D2</th><th>Comp.</th><th>Moy. dev.</th>
                    @endunless
                    <th>Moy.</th>
                    <th>Points</th>
                    <th>Appréciation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td class="subject">{{ $row['subject'] }}</td>
                        <td>{{ $row['coefficient'] }}</td>
                        @unless($usesLmd)
                            <td>{{ $row['devoir1'] ?? '—' }}</td>
                            <td>{{ $row['devoir2'] ?? '—' }}</td>
                            <td>{{ $row['composition'] ?? '—' }}</td>
                            <td>{{ $row['moyenne_devoirs'] ?? '—' }}</td>
                        @endunless
                        <td>{{ $row['moyenne_matiere'] ?? '—' }}</td>
                        <td>{{ $row['points'] ?? '—' }}</td>
                        <td>{{ $row['appreciation'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9">Aucune note pour ce semestre.</td></tr>
                @endforelse
                @if(count($data) > 0)
                    <tr class="total">
                        <td class="subject">TOTAL</td>
                        <td>{{ $totalCoef }}</td>
                        @unless($usesLmd)<td colspan="4"></td>@endunless
                        <td><strong>{{ $avg > 0 ? number_format($avg, 2) : '—' }}</strong></td>
                        <td>{{ $totalPoints > 0 ? number_format($totalPoints, 2) : '—' }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="summary">
            <strong>Moyenne générale :</strong> {{ $avg > 0 ? number_format($avg, 2).'/20' : 'Non calculable' }}
            @if($stats['average'])
                <div class="stats">
                    Stats classe — Moyenne : {{ $stats['average'] }} | Max : {{ $stats['highest'] }} | Min : {{ $stats['lowest'] }}
                </div>
            @endif
        </div>

        <p class="footer-note">Document généré le {{ $generatedAt }} — {{ $schoolName }}</p>
    </div>
@endforeach
</body>
</html>
