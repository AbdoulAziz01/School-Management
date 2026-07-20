<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sauvegarde quotidienne de la base de données à 00:00, avec nettoyage des anciens backups
Schedule::command('backup:run --only-db')
    ->dailyAt('00:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->emailOutputOnFailure(env('BACKUP_NOTIFICATION_EMAIL'));

Schedule::command('backup:clean')
    ->dailyAt('01:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->emailOutputOnFailure(env('BACKUP_NOTIFICATION_EMAIL'));
