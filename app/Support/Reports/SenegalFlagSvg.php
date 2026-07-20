<?php

namespace App\Support\Reports;

/**
 * Drapeau du Sénégal en SVG vectoriel (3 bandes verticales égales + étoile
 * verte à 5 branches centrée dans la bande jaune, ratio 3:2 officiel).
 *
 * Exposé en data-URI plutôt qu'en <svg> inline : DomPDF ne rend pas de
 * façon fiable les balises <svg> inline selon la version, alors que le
 * même SVG passé en source d'un <img> (comme le logo école, voir
 * SchoolLogoStorage::dataUri()) est correctement rasterisé.
 */
class SenegalFlagSvg
{
    public static function dataUri(): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(self::markup());
    }

    private static function markup(): string
    {
        return <<<'SVG'
<svg viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
    <rect x="0" y="0" width="300" height="600" fill="#00853F"/>
    <rect x="300" y="0" width="300" height="600" fill="#FDEF42"/>
    <rect x="600" y="0" width="300" height="600" fill="#E31B23"/>
    <polygon fill="#00853F" points="450,190 474.70,266.00 554.62,266.01 489.96,312.98 514.66,388.99 450,342.02 385.34,388.99 410.04,312.98 345.38,266.01 425.30,266.00"/>
</svg>
SVG;
    }
}
