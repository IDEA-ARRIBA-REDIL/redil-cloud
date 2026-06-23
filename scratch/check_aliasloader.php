<?php

$path = __DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/AliasLoader.php';
if (file_exists($path)) {
    echo "AliasLoader found. Searching for tempnam...\n";
    $lines = file($path);
    foreach ($lines as $i => $line) {
        if (strpos($line, 'tempnam') !== false) {
            echo 'Line '.($i + 1).': '.trim($line)."\n";
        }
    }
} else {
    echo "AliasLoader.php not found locally.\n";
}
