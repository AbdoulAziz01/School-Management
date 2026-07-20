<?php

namespace App\Services\Import;

use Illuminate\Support\Str;

/**
 * Normalisation de texte pour la correspondance approximative (accents/casse/
 * espaces ignorés) lors des imports en masse — ex. faire correspondre
 * "6eme A" du fichier avec "6ème A" en base, ou "maths" avec "Mathématiques".
 */
class TextNormalizer
{
    public static function normalize(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = strtr($value, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',
        ]);

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
