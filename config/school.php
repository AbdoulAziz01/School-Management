<?php

return [
    /** Mot de passe des comptes créés par SenegalTenSchoolsLoadTestSeeder. */
    'load_test_default_password' => env('LOAD_TEST_DEFAULT_PASSWORD', 'password'),

    /** Email admin des établissements du jeu de données charge. */
    'load_test_admin_email_pattern' => '/^admin@[a-z0-9\-]+\.edu\.sn$/i',
];
