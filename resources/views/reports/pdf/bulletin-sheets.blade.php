<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle }}</title>
    @include('reports.partials.bulletin-print-styles')
</head>
<body>
    @forelse($sheets as $sheet)
        @include('reports.partials.bulletin-print', ['sheet' => $sheet])
    @empty
        <p>Aucun bulletin à générer.</p>
    @endforelse
</body>
</html>
