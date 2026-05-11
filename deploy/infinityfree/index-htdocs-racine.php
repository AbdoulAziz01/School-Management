<?php

/**
 * Point d'entrée Laravel lorsque tout le projet est à la racine htdocs
 * et que vous avez déplacé l'ancien contenu de "public/" ici à côté
 * de app/, bootstrap/, vendor/, storage/, etc.
 *
 * InfinityFree : après fusion public/ → htdocs, remplacez index.php à la racine
 * par CE fichier (renommé en index.php ou copie du contenu).
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
