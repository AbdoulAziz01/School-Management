<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du bulletin — EduManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); min-height: 100vh; }
        .verify-card { max-width: 720px; margin: 2rem auto; }
        .badge-authentic { background: linear-gradient(135deg, #16a34a, #15803d); font-size: 1rem; padding: .6rem 1.2rem; }
        .table th { background: #fef3c7; color: #92400e; }
        .header-bar { background: linear-gradient(135deg, #1c1917, #292524); color: #fbbf24; padding: 1.5rem; border-radius: 12px 12px 0 0; }
    </style>
</head>
<body>
<div class="verify-card">
    <div class="card shadow-lg border-0">
        <div class="header-bar text-center">
            <h4 class="mb-1"><i class="fas fa-shield-alt me-2"></i>Vérification d'authenticité</h4>
            <p class="mb-0 opacity-75" style="font-size:.85rem">EduManager — Système de Gestion Scolaire</p>
        </div>

        <div class="card-body p-4">

            {{-- Badge authentifié --}}
            <div class="text-center mb-4">
                <span class="badge badge-authentic text-white rounded-pill">
                    <i class="fas fa-check-circle me-2"></i>Document authentique et vérifié
                </span>
                <p class="text-muted mt-2 mb-0" style="font-size:.8rem">
                    Ce bulletin a été émis par EduManager et son contenu est garanti intègre.
                </p>
            </div>

            {{-- Identité de l'élève --}}
            <h6 class="fw-bold text-warning mb-2"><i class="fas fa-user me-2"></i>Identité de l'élève</h6>
            <table class="table table-bordered table-sm mb-4">
                <tr><th>Nom complet</th><td>{{ $bulletin['studentInfo']['name'] }}</td></tr>
                <tr><th>Identifiant</th><td>{{ $bulletin['studentInfo']['identifier'] }}</td></tr>
                <tr><th>Classe</th><td>{{ $bulletin['studentInfo']['class'] }}</td></tr>
                <tr><th>Niveau</th><td>{{ $bulletin['studentInfo']['level'] }}{{ $bulletin['studentInfo']['serie'] ? ' — '.$bulletin['studentInfo']['serie'] : '' }}</td></tr>
                <tr><th>Année scolaire</th><td>{{ $year->name }}</td></tr>
                <tr><th>Période</th><td>{{ $semester !== null ? 'Semestre '.$semester : 'Année scolaire complète' }}</td></tr>
            </table>

            {{-- Résultat --}}
            <h6 class="fw-bold text-warning mb-2"><i class="fas fa-chart-bar me-2"></i>Résultats certifiés</h6>
            <table class="table table-bordered table-sm mb-4">
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th class="text-center">Coef.</th>
                        <th class="text-center">Moyenne</th>
                        <th class="text-center">Points</th>
                        <th class="text-center">Appréciation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bulletin['bulletinData'] as $row)
                    <tr>
                        <td>{{ $row['subject'] }}</td>
                        <td class="text-center">{{ $row['coefficient'] }}</td>
                        <td class="text-center">{{ $row['moyenne_matiere'] ?? '—' }}</td>
                        <td class="text-center">{{ $row['points'] ?? '—' }}</td>
                        <td class="text-center">{{ $row['appreciation'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-warning fw-bold">
                        <td colspan="2">Moyenne générale</td>
                        @php $verifyMaxLabel = rtrim(rtrim(number_format($bulletin['overallMaxGrade'] ?? 20, 2), '0'), '.'); @endphp
                        <td class="text-center">
                            {{ $bulletin['generalAverage'] > 0 ? number_format($bulletin['generalAverage'], 2).'/'.$verifyMaxLabel : '—' }}
                        </td>
                        <td colspan="2" class="text-center">
                            @if($bulletin['rankData']['rank'])
                                Rang : {{ $bulletin['rankData']['rank'] }}<sup>e</sup> / {{ $bulletin['rankData']['total'] }}
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="alert alert-warning border-0" style="background:#fffbeb; border-left: 4px solid #f59e0b !important;">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Ce lien est cryptographiquement signé par EduManager. Toute modification de l'URL invalide la vérification.
                    Vérifié le {{ now()->format('d/m/Y à H:i') }}.
                </small>
            </div>
        </div>
    </div>
</div>
</body>
</html>
