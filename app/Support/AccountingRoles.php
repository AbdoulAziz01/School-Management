<?php

namespace App\Support;

/**
 * Catalogue de rôles/permissions comptables (spatie/laravel-permission),
 * préparé en fondation pour le futur module Comptabilité — voir l'audit de
 * préparation ("séparation des tâches : comptable/caissier/trésorier").
 *
 * Volontairement PAS encore ajouté à User::ROLE_* / la colonne users.role :
 * aucun compte réel ne peut aujourd'hui porter un de ces rôles (pas de
 * tableau de bord, pas de route, pas de middleware qui les reconnaît). Ce
 * catalogue existe pour que RolePermissionSeeder puisse créer les
 * permissions Spatie à l'avance, prêtes à être assignées le jour où le
 * module Comptabilité (et ses écrans de gestion des comptes) sera construit.
 */
class AccountingRoles
{
    public const COMPTABLE = 'comptable';

    public const CAISSIER = 'caissier';

    public const TRESORIER = 'tresorier';

    public const ALL = [self::COMPTABLE, self::CAISSIER, self::TRESORIER];

    /**
     * Séparation des tâches : celui qui saisit une écriture (comptable) ne
     * peut pas la valider (trésorier) ; celui qui encaisse (caissier) ne
     * gère ni les écritures ni le rapprochement bancaire.
     *
     * @return array<string, list<string>>
     */
    public static function permissionMatrix(): array
    {
        return [
            self::COMPTABLE => [
                'ecriture.creer', 'ecriture.consulter',
                'journal.consulter', 'grand_livre.consulter', 'balance.consulter',
                'fournisseur.gerer', 'depense.creer', 'recette.creer',
                'rapport_financier.consulter', 'rapport_financier.exporter',
            ],
            self::CAISSIER => [
                'caisse.consulter', 'caisse.encaisser', 'caisse.decaisser',
                'recette.creer', 'penalite.appliquer',
            ],
            self::TRESORIER => [
                'ecriture.valider', 'ecriture.consulter',
                'banque.gerer', 'rapprochement.effectuer', 'virement.effectuer',
                'rapport_financier.consulter', 'rapport_financier.exporter',
            ],
        ];
    }
}
