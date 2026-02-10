<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur 500 - Erreur serveur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            max-width: 600px;
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .error-code {
            font-size: 5rem;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #6c757d;
        }
        .error-details {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 1.5rem 0;
            text-align: left;
            font-family: monospace;
            max-height: 200px;
            overflow-y: auto;
            font-size: 0.875rem;
        }
        .btn-home {
            background-color: #0d6efd;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }
        .btn-home:hover {
            background-color: #0b5ed7;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <h1>Oups ! Quelque chose s'est mal passé</h1>
        <p class="error-message">
            @if(isset($message) && $message)
                {{ $message }}
            @else
                Une erreur est survenue lors du chargement de la page.
            @endif
        </p>
        
        @if(config('app.debug'))
            <div class="error-details">
                @if(isset($exception) && $exception instanceof Exception)
                    <p><strong>Message:</strong> {{ $exception->getMessage() }}</p>
                    <p><strong>Fichier:</strong> {{ $exception->getFile() }}:{{ $exception->getLine() }}</p>
                    <div><strong>Stack trace:</strong>
                        <pre>{{ $exception->getTraceAsString() }}</pre>
                    </div>
                @endif
            </div>
        @endif
        
        <a href="{{ url('/') }}" class="btn btn-primary">
            <i class="fas fa-home me-2"></i> Retour à l'accueil
        </a>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
