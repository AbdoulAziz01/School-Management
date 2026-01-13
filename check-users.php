<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

try {
    $users = User::all();
    
    if ($users->isEmpty()) {
        echo "Aucun utilisateur trouvé dans la base de données.\n";
    } else {
        echo "Liste des utilisateurs :\n";
        echo "ID - Nom - Email - Identifiant\n";
        echo str_repeat("-", 50) . "\n";
        
        foreach ($users as $user) {
            echo sprintf(
                "%d - %s - %s - %s\n",
                $user->id,
                $user->name,
                $user->email,
                $user->identifier
            );
        }
    }
} catch (Exception $e) {
    echo "Erreur lors de la récupération des utilisateurs : " . $e->getMessage() . "\n";
}
