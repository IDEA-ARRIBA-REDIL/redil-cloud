<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap Console Kernel to register facades and container bindings
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo '<h3>Laravel Cache & File Diagnostics</h3>';

// 1. Clear caches
try {
    Artisan::call('optimize:clear');
    echo '<b>optimize:clear completed:</b> <pre>'.Artisan::output().'</pre><br>';
} catch (\Exception $e) {
    echo '<b>Error running optimize:clear:</b> '.$e->getMessage().'<br>';
}

// 2. Inspect the EditarCurso.php file on the server
$filePath = __DIR__.'/../app/Livewire/Cursos/EditarCurso.php';
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    if (strpos($content, 'cropped_imagen_portada') !== false) {
        echo "<span style='color: green;'><b>SUCCESS:</b> 'cropped_imagen_portada' property exists in EditarCurso.php on the server.</span><br><br>";
    } else {
        echo "<span style='color: red;'><b>WARNING:</b> 'cropped_imagen_portada' property WAS NOT FOUND in EditarCurso.php on the server. Please ensure you uploaded/synced the file App/Livewire/Cursos/EditarCurso.php!</span><br><br>";
    }
} else {
    echo "<span style='color: darkred;'><b>ERROR:</b> EditarCurso.php does not exist at path: ".htmlspecialchars($filePath).'</span><br><br>';
}

// 3. Reset OPcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<span style='color: green;'><b>OPcache reset successfully!</b></span><br>";
    } else {
        echo "<span style='color: orange;'><b>OPcache reset returned false (might be disabled or empty).</b></span><br>";
    }
} else {
    echo 'opcache_reset() is not available on this server.<br>';
}
