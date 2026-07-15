<?php

namespace App\Http\View\Composers;

use App\Services\CashSessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * "Ouvrir la caisse" et "Solde actuel" pointent tous les deux vers
 * caisse.dashboard (même page, contenu différent selon qu'une session est
 * ouverte ou non) — ce composer résout l'état de session pour que la
 * sidebar puisse mettre en surbrillance le bon des deux plutôt que les deux
 * à la fois ou aucun.
 */
class CaisseSidebarComposer
{
    public function compose(View $view): void
    {
        $sessionOpen = false;

        if (Auth::check()) {
            $sessionOpen = app(CashSessionService::class)->currentOpenSession(Auth::user()) !== null;
        }

        $view->with('sidebarCashSessionOpen', $sessionOpen);
    }
}
