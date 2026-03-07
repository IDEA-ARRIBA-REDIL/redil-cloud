<?php

use App\Models\User;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Iniciando pruebas de optimización...\n\n";

try {
    $user = User::first();
    if (!$user) {
        echo "No hay usuarios en la base de datos.\n";
        exit;
    }

    echo "Probando con usuario ID: " . $user->id . " (" . $user->name . ")\n";

    // 1. Probar gruposMinisterio
    echo "Ejecutando gruposMinisterio()...\n";
    $start = microtime(true);
    $grupos = $user->gruposMinisterio();
    $end = microtime(true);
    echo "gruposMinisterio (objeto) count: " . $grupos->count() . "\n";
    echo "Tiempo: " . ($end - $start) . "s\n";

    $start = microtime(true);
    $gruposIds = $user->gruposMinisterio('array');
    $end = microtime(true);
    echo "gruposMinisterio (array) count: " . count($gruposIds) . "\n";
    echo "Tiempo: " . ($end - $start) . "s\n";
    
    // 2. Probar discipulos
    echo "\nEjecutando discipulos()...\n";
    $start = microtime(true);
    $discipulos = $user->discipulos();
    $end = microtime(true);
    echo "discipulos count: " . $discipulos->count() . "\n";
    echo "Tiempo: " . ($end - $start) . "s\n";

    // 3. Probar discipulos con parametros
    echo "\nEjecutando discipulos('solo-eliminados')...\n";
    $discipulosElim = $user->discipulos('solo-eliminados');
    echo "discipulos eliminados count: " . $discipulosElim->count() . "\n";

    echo "\nPruebas completadas con éxito.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
