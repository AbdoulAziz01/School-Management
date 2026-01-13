<?php

$viewsPath = __DIR__ . '/resources/views/admin';

function updateViewFile($filePath) {
    $content = file_get_contents($filePath);
    
    // Remplacer l'extension admin.layouts.app par layouts.app
    $newContent = str_replace(
        "@extends('admin.layouts.app')", 
        "@extends('layouts.app')", 
        $content
    );
    
    // Écrire le contenu modifié uniquement s'il y a eu des changements
    if ($content !== $newContent) {
        file_put_contents($filePath, $newContent);
        echo "Updated: " . $filePath . "\n";
    }
}

// Fonction récursive pour parcourir les dossiers
function scanDirectory($dir) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            scanDirectory($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'blade.php') {
            updateViewFile($path);
        }
    }
}

// Lancer la mise à jour
scanDirectory($viewsPath);

echo "Mise à jour des vues terminée.\n";
