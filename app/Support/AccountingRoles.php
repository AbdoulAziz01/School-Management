<?php

namespace App\Support;

/**
 * Catalogue de rôles/permissions comptables (spatie/laravel-permission),
 * séparation des tâches : directeur/comptable/caissier/trésorier.
 *
 * DIRECTEUR, COMPTABLE et CAISSIER sont de vrais rôles assignables depuis le
 * module Comptabilité (Phase 6.1) — voir User::ROLE_DIRECTEUR/ROLE_COMPTABLE/
 * ROLE_CAISSIER, qui référencent directement ces constantes pour que les deux
 * systèmes (colonne users.role et rôle Spatie synchronisé par
 * User::syncRoleFromColumn()) ne divergent jamais.
 *
 * TRESORIER reste une fondation dormante : aucun compte, tableau de bord ou
 * route ne le reconnaît encore (hors périmètre du besoin actuel, qui ne
 * demande pas de validation d'écriture séparée).
 */
class AccountingRoles
{
    public const DIRECTEUR = 'directeur';

    public const COMPTABLE = 'comptable';

    public const CAISSIER = 'caissier';

    public const TRESORIER = 'tresorier';

    public const ALL = [self::DIRECTEUR, self::COMPTABLE, self::CAISSIER, self::TRESORIER];

    /**
     * Séparation des tâches : celui qui saisit une écriture (comptable) ne
     * peut pas la valider (trésorier) ; celui qui encaisse (caissier) ne
     * gère ni les écritures ni le rapprochement bancaire ; le directeur
     * paramètre et pilote mais n'effectue aucune opération quotidienne.
     *
     * @return array<string, list<string>>
     */
    public static function permissionMatrix(): array
    {
        return [
            self::DIRECTEUR => [
                'parametrage.frais', 'parametrage.salaires', 'parametrage.modules',
                'ecriture.consulter', 'journal.consulter', 'grand_livre.consulter', 'balance.consulter',
                'caisse.consulter', 'personnel.compta.gerer',
                // Exception voulue à la séparation des tâches : le directeur
                // peut payer lui-même les salaires (profs/surveillants...),
                // en toute transparence — chaque paiement enregistre qui l'a
                // exécuté (SalaryPayment::paid_by), affiché sur le suivi.
                'salaire.payer',
                'rapport_financier.consulter', 'rapport_financier.exporter',
            ],
            self::COMPTABLE => [
                'ecriture.creer', 'ecriture.consulter',
                'journal.consulter', 'grand_livre.consulter', 'balance.consulter',
                'fournisseur.gerer', 'depense.creer', 'depense.annuler', 'recette.creer',
                'paiement.annuler', 'salaire.generer', 'salaire.payer', 'salaire.annuler',
                'rapport_financier.consulter', 'rapport_financier.exporter',
            ],
            self::CAISSIER => [
                'caisse.consulter', 'caisse.ouvrir', 'caisse.cloturer',
                'caisse.encaisser', 'caisse.decaisser',
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
