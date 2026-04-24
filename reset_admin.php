<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('identifier', 'ADMIN001')->first();

if ($admin) {
    $admin->password = Hash::make('passer01');
    $admin->email_verified_at = now();
    $admin->status = 'approved';
    $admin->save();
    
    echo "Admin password reset successfully!\n";
    echo "Identifier: " . $admin->identifier . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Password: passer01\n";
    echo "Status: " . $admin->status . "\n";
} else {
    echo "Admin user not found!\n";
}
