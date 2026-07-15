<?php

namespace App\Support;

use App\Models\School;

/**
 * Modules activables par établissement (JSON school.enabled_modules), suivant
 * le même pattern que FormationLmdSettings/card_settings déjà en place sur
 * School plutôt qu'une table dédiée.
 *
 * Fondation pour le futur module Comptabilité : un établissement privé aura
 * par défaut la comptabilité complète activée, un établissement public
 * pourra la laisser désactivée — sans que cela empêche un administrateur
 * plateforme de changer ce réglage établissement par établissement.
 */
class SchoolModules
{
    public const ACCOUNTING = 'accounting';

    public const ALL_MODULES = [
        self::ACCOUNTING,
    ];

    /**
     * Modules activés par défaut selon la catégorie de l'établissement,
     * tant qu'aucun réglage explicite n'a été enregistré dans enabled_modules.
     *
     * @return list<string>
     */
    public static function defaultsFor(?School $school): array
    {
        if ($school?->isPrivateEstablishment()) {
            return [self::ACCOUNTING];
        }

        return [];
    }

    public static function isEnabled(?School $school, string $module): bool
    {
        if (! $school) {
            return false;
        }

        $enabled = $school->enabled_modules;

        if (! is_array($enabled)) {
            return in_array($module, self::defaultsFor($school), true);
        }

        return in_array($module, $enabled, true);
    }

    public static function enable(School $school, string $module): void
    {
        $current = is_array($school->enabled_modules) ? $school->enabled_modules : self::defaultsFor($school);

        if (! in_array($module, $current, true)) {
            $current[] = $module;
        }

        $school->update(['enabled_modules' => array_values($current)]);
    }

    public static function disable(School $school, string $module): void
    {
        $current = is_array($school->enabled_modules) ? $school->enabled_modules : self::defaultsFor($school);

        $school->update(['enabled_modules' => array_values(array_diff($current, [$module]))]);
    }
}
