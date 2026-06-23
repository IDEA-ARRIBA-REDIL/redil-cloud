<?php

// Habilitar reporte de errores completo antes de cualquier otra cosa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<h3>--- DIAGNÓSTICO EN NAVEGADOR (WEB) ---</h3>';
echo 'Versión de PHP: '.PHP_VERSION.'<br>';
echo 'Usuario del Servidor Web (posix): '.(function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'No disponible').'<br>';
echo 'Directorio actual: '.getcwd().'<br>';
echo 'Script ejecutado: '.__FILE__.'<br><br>';

try {
    echo '1. Cargando autoload.php...<br>';
    require __DIR__.'/../vendor/autoload.php';
    echo '   Autoload cargado con éxito.<br><br>';

    echo '2. Cargando bootstrap/app.php...<br>';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo '   Application instanciada con éxito.<br><br>';

    echo '3. Inicializando Kernel de Consola...<br>';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo '   Kernel inicializado con éxito.<br><br>';

    if (! class_exists('App\Models\Tenant')) {
        echo "ERROR: La clase App\Models\Tenant no existe.<br>";
        exit;
    }

    echo '4. Buscando tenants en la base de datos...<br>';
    $tenants = \App\Models\Tenant::all();
    echo '   Se encontraron '.$tenants->count().' tenants.<br><br>';

    if ($tenants->isEmpty()) {
        echo 'ERROR: No hay ningún tenant registrado en la base de datos central.<br>';
        exit;
    }

    foreach ($tenants as $tenant) {
        echo '<hr>';
        echo '<h4>PROBANDO TENANT ID: '.$tenant->id.'</h4>';
        echo '-----------------------------------------<br>';

        echo 'Inicializando tenancy...<br>';
        tenancy()->initialize($tenant);
        echo 'Tenancy inicializado con éxito.<br>';

        // 1. Obtener la ruta de almacenamiento de Livewire
        $tmpDir = storage_path('app/livewire-tmp');
        echo 'Ruta de Livewire tmp: '.$tmpDir.'<br>';
        echo '¿Existe la carpeta?: '.(is_dir($tmpDir) ? '<b>SÍ</b>' : '<b>NO</b>').'<br>';

        if (is_dir($tmpDir)) {
            $isWritable = is_writable($tmpDir);
            echo '¿Es escribible por el Servidor Web?: '.($isWritable ? "<span style='color:green;'><b>SÍ</b></span>" : "<span style='color:red;'><b>NO</b></span>").'<br>';

            // Obtener permisos en octal
            $permissions = substr(sprintf('%o', fileperms($tmpDir)), -4);
            echo 'Permisos de la carpeta: '.$permissions.'<br>';

            // Dueño y grupo de la carpeta
            if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
                $owner = posix_getpwuid(fileowner($tmpDir));
                $group = posix_getgrgid(filegroup($tmpDir));
                echo 'Propietario de la carpeta: '.($owner['name'] ?? 'desconocido').'<br>';
                echo 'Grupo de la carpeta: '.($group['name'] ?? 'desconocido').'<br>';
            }

            // Intentar crear un archivo usando tempnam
            echo 'Probando tempnam()...<br>';
            $tmpFile = @tempnam($tmpDir, 'livewire-tmp');
            if ($tmpFile) {
                echo '   tempnam() exitoso! Archivo temporal: '.$tmpFile.'<br>';

                // Verificar si se creó en el directorio del sistema (fallback) o en el correcto
                if (strpos($tmpFile, $tmpDir) === false) {
                    echo "   <span style='color:red;'><b>ATENCIÓN: PHP hizo FALLBACK al directorio temporal del sistema (".sys_get_temp_dir().')!</b></span><br>';
                } else {
                    echo "   <span style='color:green;'><b>ÉXITO: PHP escribió el archivo directamente en la carpeta del tenant.</b></span><br>";
                    @unlink($tmpFile); // Limpiar
                }
            } else {
                echo "   <span style='color:red;'><b>ERROR: tempnam() falló por completo y retornó false.</b></span><br>";
            }
        } else {
            echo 'La carpeta no existe. Intentando crearla con mkdir...<br>';
            $created = @mkdir($tmpDir, 0775, true);
            if ($created) {
                echo '   Carpeta creada exitosamente. Recarga esta página para validar permisos.<br>';
            } else {
                echo "   <span style='color:red;'><b>ERROR: No se pudo crear la carpeta. ¿Tiene permisos el directorio padre? (".storage_path('app').')</b></span><br>';
            }
        }

        echo 'Finalizando contexto de tenant...<br>';
        tenancy()->end();
        echo '<br>';
    }

} catch (\Throwable $e) {
    echo "<br><span style='color:red;'><b>!!! SE DETECTÓ UNA EXCEPCIÓN / ERROR FATAL !!!</b></span><br>";
    echo 'Mensaje: '.$e->getMessage().'<br>';
    echo 'Archivo: '.$e->getFile().'<br>';
    echo 'Línea: '.$e->getLine().'<br>';
    echo 'Trace:<br><pre>'.$e->getTraceAsString().'</pre><br>';
}
