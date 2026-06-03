<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport fin d'année — {{ $class->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; margin: 16px; }
        .header { background: #1a5f2a; color: #fff; padding: 14px; text-align: center; margin-bottom: 14px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 4px 0 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1a5f2a; color: #fff; padding: 8px 6px; font-size: 9px; border: 1px solid #155724; }
        td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        td.name { text-align: left; font-weight: 600; }
        tr:nth-child(even) { background: #f5f5f5; }
        .meta { margin-bottom: 12px; font-size: 10px; }
        .footer { margin-top: 16px; font-size: 8px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <p>RAPPORT DE FIN D'ANNÉE — {{ $academicYear->name }}</p>
        <p>Classe : {{ $class->name }}</p>
    </div>

    <p class="meta">Généré le {{ $generatedAt }} — {{ count($rows) }} élève(s)</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Identifiant</th>
                <th>Élève</th>
                <th>Moy. S1</th>
                <th>Moy. S2</th>
                <th>Moy. annuelle</th>
                <th>Décision conseil</th>
                <th>Mention</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['identifier'] }}</td>
                    <td class="name">{{ $row['name'] }}</td>
                    <td>{{ $row['semestre1'] > 0 ? number_format($row['semestre1'], 2) : '—' }}</td>
                    <td>{{ $row['semestre2'] > 0 ? number_format($row['semestre2'], 2) : '—' }}</td>
                    <td><strong>{{ $row['moyenne_annuelle'] > 0 ? number_format($row['moyenne_annuelle'], 2) : '—' }}</strong></td>
                    <td>{{ $row['decision'] }}</td>
                    <td>{{ $row['mention'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Aucun élève dans cette classe.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Document officiel de synthèse — {{ $schoolName }}</p>
</body>
</html>
