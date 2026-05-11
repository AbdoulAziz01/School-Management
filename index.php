<?php

/**
 * Point d'entrée lorsque tout le projet Laravel est à la racine du serveur (ex. InfinityFree htdocs).
 * En local, utilisez plutôt `php artisan serve` ou pointez le vhost vers le dossier `public/`.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
