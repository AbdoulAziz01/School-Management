<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Émis quand un élève est inscrit (StudentController::store()), après
 * confirmation en base (post DB::commit()) — pas avant, pour qu'un futur
 * listener ne réagisse jamais à une inscription qui pourrait encore échouer.
 *
 * Fondation pour le futur module Comptabilité (voir audit de préparation) :
 * un listener pourra un jour générer automatiquement les frais de scolarité
 * d'un établissement privé sans que StudentController ait besoin de
 * connaître quoi que ce soit sur la facturation — c'est tout l'intérêt de
 * passer par un événement plutôt qu'un appel direct depuis le contrôleur.
 *
 * Aucun listener n'est encore abonné : ce fichier pose le pattern, pas le
 * module Comptabilité lui-même.
 */
class StudentEnrolled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $student
    ) {}
}
