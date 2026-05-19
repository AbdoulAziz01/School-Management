<?php

use App\Http\Controllers\Api\SchoolBotController;
use Illuminate\Support\Facades\Route;

Route::prefix('bot/school')
    ->middleware('school.bot')
    ->group(function () {
        Route::get('/stats', [SchoolBotController::class, 'stats']);
        Route::get('/stats/repeaters', [SchoolBotController::class, 'repeaters']);
        Route::get('/stats/outcomes', [SchoolBotController::class, 'outcomes']);
        Route::get('/students/search', [SchoolBotController::class, 'searchStudents']);
        Route::get('/users/search', [SchoolBotController::class, 'searchUsers']);
        Route::get('/students/{id}', [SchoolBotController::class, 'showStudent'])
            ->whereNumber('id');
    });
