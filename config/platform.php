<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nom de la plateforme (marque ERP)
    |--------------------------------------------------------------------------
    |
    | Utilisé sur la console super admin, la page de connexion, etc.
    | Distinct du nom d'un établissement scolaire.
    |
    */
    'name' => env('PLATFORM_NAME', 'AzelieEdu'),

    /*
    |--------------------------------------------------------------------------
    | Logo de la plateforme
    |--------------------------------------------------------------------------
    |
    | Chemins relatifs à public/, exposés via $platformLogoIcon /
    | $platformLogoHorizontal (voir PlatformBrandingComposer). Icône carrée
    | utilisée en repli quand un établissement n'a pas son propre logo
    | (favicon, sidebars, console super admin) ; version horizontale pour
    | les écrans publics (connexion, page vitrine).
    |
    */
    'logo_icon' => 'images/azeliedu-logo-icon.png',
    'logo_horizontal' => 'images/azeliedu-logo-horizontal.png',

];
