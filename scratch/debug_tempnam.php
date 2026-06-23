<?php

// Requerir el bootstrap de la aplicación para poder usar funciones de Laravel
// Corregido: subimos un nivel para llegar a la raíz del proyecto
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Iniciar kernel de consola para poder inicializar tenancy
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;

echo "--- DIAGNÓSTICO DE TEMPNAM Y PERMISOS ---\n\n";

// Intentar obtener el primer tenant para probar el contexto
$tenant = Tenant::first();
if (! $tenant) {
    echo "ERROR: No hay ningún tenant registrado en la base de datos central.\n";
    exit;
}

echo 'Inicializando contexto para el Tenant ID: '.$tenant->id."\n";
tenancy()->initialize($tenant);

// 1. Obtener la ruta de almacenamiento de Livewire
$tmpDir = storage_path('app/livewire-tmp');
echo "Ruta storage_path('app/livewire-tmp'): ".$tmpDir."\n";
echo 'Existe el directorio?: '.(is_dir($tmpDir) ? 'SÍ' : 'NO')."\n";

if (is_dir($tmpDir)) {
    echo 'Es escribible?: '.(is_writable($tmpDir) ? 'SÍ' : 'NO')."\n";

    // Obtener los permisos actuales en formato octal
    $permissions = substr(sprintf('%o', fileperms($tmpDir)), -4);
    echo 'Permisos de la carpeta: '.$permissions."\n";

    // Intentar crear un archivo usando tempnam
    $tmpFile = @tempnam($tmpDir, 'livewire-tmp');
    if ($tmpFile) {
        echo 'tempnam() exitoso! Archivo creado en: '.$tmpFile."\n";

        // Verificar si se creó en el directorio del sistema (fallback) o en el correcto
        if (strpos($tmpFile, $tmpDir) === false) {
            echo "ATENCIÓN: PHP hizo FALLBACK al directorio temporal del sistema!\n";
        } else {
            echo "ÉXITO: PHP escribió el archivo directamente en la carpeta del tenant.\n";
            unlink($tmpFile); // Limpiar
        }
    } else {
        echo "ERROR: tempnam() falló por completo y retornó false.\n";
    }
} else {
    echo "Intentando crear la carpeta con mkdir...\n";
    $created = @mkdir($tmpDir, 0775, true);
    if ($created) {
        echo "Carpeta creada exitosamente. Vuelve a ejecutar este script para validar los permisos.\n";
    } else {
        echo 'ERROR: No se pudo crear la carpeta. ¿Tiene permisos de escritura el directorio padre? ('.storage_path('app').")\n";
    }
}
