<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

// Vider les caches
Artisan::call('cache:clear');
Artisan::call('config:clear');
Artisan::call('view:clear');

// Mettre à jour la configuration de la session
$sessionConfigPath = config_path('session.php');
$sessionConfig = file_get_contents($sessionConfigPath);

// Mettre à jour la configuration de la session
$updates = [
    "'driver' => env('SESSION_DRIVER', 'file')" => "'driver' => 'file'",
    "'lifetime' => (int) env('SESSION_LIFETIME', 120)" => "'lifetime' => 120",
    "'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false)" => "'expire_on_close' => false",
    "'encrypt' => env('SESSION_ENCRYPT', false)" => "'encrypt' => false",
    "'connection' => env('SESSION_CONNECTION')" => "'connection' => null",
    "'domain' => env('SESSION_DOMAIN')" => "'domain' => null",
    "'secure' => env('SESSION_SECURE_COOKIE')" => "'secure' => false",
    "'http_only' => true" => "'http_only' => true",
    "'same_site' => 'lax'" => "'same_site' => 'lax'"
];

foreach ($updates as $search => $replace) {
    $sessionConfig = str_replace($search, $replace, $sessionConfig);
}

file_put_contents($sessionConfigPath, $sessionConfig);

// Vider le dossier des sessions
$sessionPath = storage_path('framework/sessions');
if (File::exists($sessionPath)) {
    $files = File::files($sessionPath);
    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            File::delete($file->getPathname());
        }
    }
}

echo "Configuration de la session mise à jour avec succès.\n";
echo "Veuvez redémarrer votre serveur web (Apache/Nginx) et votre navigateur.\n";

// Générer une nouvelle clé d'application
Artisan::call('key:generate', ['--force' => true]);

echo "Nouvelle clé d'application générée.\n";
