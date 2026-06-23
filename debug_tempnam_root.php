<?php

// Habilitar reporte de errores completo antes de cualquier otra cosa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "--- INICIANDO DIAGNÓSTICO EN RAÍZ ---\n";
echo 'Versión de PHP del CLI: '.PHP_VERSION."\n";
echo 'Directorio actual: '.getcwd()."\n";
echo 'Script ejecutado: '.__FILE__."\n\n";

try {
    echo "1. Cargando autoload.php...\n";
    require __DIR__.'/vendor/autoload.php';
    echo "   Autoload cargado con éxito.\n\n";

    echo "2. Cargando bootstrap/app.php...\n";
    $app = require_once __DIR__.'/bootstrap/app.php';
    echo "   Application instanciada con éxito.\n\n";

    echo "3. Inicializando Kernel de Consola...\n";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "   Kernel inicializado con éxito.\n\n";

    // Registrar la clase de Tenant para poder buscarla
    if (! class_exists('App\Models\Tenant')) {
        echo "ERROR: La clase App\Models\Tenant no existe.\n";
        exit;
    }

    echo "4. Buscando tenants en la base de datos...\n";
    $tenants = \App\Models\Tenant::all();
    echo '   Se encontraron '.$tenants->count()." tenants.\n\n";

    if ($tenants->isEmpty()) {
        echo "ERROR: No hay ningún tenant registrado en la base de datos central.\n";
        exit;
    }

    foreach ($tenants as $tenant) {
        echo "=========================================\n";
        echo 'PROBANDO TENANT ID: '.$tenant->id."\n";
        echo "=========================================\n";

        echo "Inicializando tenancy...\n";
        tenancy()->initialize($tenant);
        echo "Tenancy inicializado con éxito.\n";

        // 1. Obtener la ruta de almacenamiento de Livewire
        $tmpDir = storage_path('app/livewire-tmp');
        echo 'Ruta de Livewire tmp: '.$tmpDir."\n";
        echo '¿Existe la carpeta?: '.(is_dir($tmpDir) ? 'SÍ' : 'NO')."\n";

        if (is_dir($tmpDir)) {
            echo '¿Es escribible por PHP?: '.(is_writable($tmpDir) ? 'SÍ' : 'NO')."\n";

            // Obtener permisos en octal
            $permissions = substr(sprintf('%o', fileperms($tmpDir)), -4);
            echo 'Permisos de la carpeta: '.$permissions."\n";

            // Dueño y grupo de la carpeta
            if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
                $owner = posix_getpwuid(fileowner($tmpDir));
                $group = posix_getgrgid(filegroup($tmpDir));
                echo 'Propietario de la carpeta: '.($owner['name'] ?? 'desconocido')."\n";
                echo 'Grupo de la carpeta: '.($group['name'] ?? 'desconocido')."\n";
            }

            // Intentar crear un archivo usando tempnam
            echo "Probando tempnam()...\n";
            $tmpFile = @tempnam($tmpDir, 'livewire-tmp');
            if ($tmpFile) {
                echo '   tempnam() exitoso! Archivo temporal: '.$tmpFile."\n";

                // Verificar si se creó en el directorio del sistema (fallback) o en el correcto
                if (strpos($tmpFile, $tmpDir) === false) {
                    echo '   ATENCIÓN: PHP hizo FALLBACK al directorio temporal del sistema ('.sys_get_temp_dir().")!\n";
                } else {
                    echo "   ÉXITO: PHP escribió el archivo directamente en la carpeta del tenant.\n";
                    @unlink($tmpFile); // Limpiar
                }
            } else {
                echo "   ERROR: tempnam() falló por completo y retornó false.\n";
            }
        } else {
            echo "La carpeta no existe. Intentando crearla con mkdir...\n";
            $created = @mkdir($tmpDir, 0775, true);
            if ($created) {
                echo "   Carpeta creada exitosamente. Ejecuta de nuevo para validar permisos.\n";
            } else {
                echo '   ERROR: No se pudo crear la carpeta. ¿Tiene permisos el directorio padre? ('.storage_path('app').")\n";
            }
        }

        echo "Finalizando contexto de tenant...\n";
        tenancy()->end();
        echo "\n";
    }

} catch (\Throwable $e) {
    echo "\n!!! SE DETECTÓ UNA EXCEPCIÓN / ERROR FATAL !!!\n";
    echo 'Mensaje: '.$e->getMessage()."\n";
    echo 'Archivo: '.$e->getFile()."\n";
    echo 'Línea: '.$e->getLine()."\n";
    echo "Trace:\n".$e->getTraceAsString()."\n";
}
