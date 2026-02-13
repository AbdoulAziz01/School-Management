<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 via CDN, pas de Vite --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            min-height: 100vh;
        }
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        }
        .form-control:focus, .form-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
        }
        /* Tailwind amber colors for primary-button component */
        .bg-amber-600 { background-color: #d97706 !important; }
        .bg-amber-500 { background-color: #f59e0b !important; }
        .hover\\:bg-amber-700:hover { background-color: #b45309 !important; }
        .hover\\:bg-amber-400:hover { background-color: #fbbf24 !important; }
        .focus\\:bg-amber-700:focus { background-color: #b45309 !important; }
        .focus\\:bg-amber-400:focus { background-color: #fbbf24 !important; }
        .active\\:bg-amber-800:active { background-color: #92400e !important; }
        .active\\:bg-amber-300:active { background-color: #fcd34d !important; }
        .focus\\:ring-amber-500:focus { --tw-ring-color: #f59e0b !important; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="shadow-sm card">
                <div class="card-body">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
