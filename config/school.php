<?php

return [
    'default_name' => env('DEFAULT_SCHOOL_NAME', 'Établissement principal'),

    /** Moyenne annuelle minimale pour le passage en classe supérieure (6ème → Terminale). */
    'passing_grade_min' => (float) env('SCHOOL_PASSING_GRADE_MIN', 10),
];
