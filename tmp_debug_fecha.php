<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Grupo;
use App\Models\Configuracion;

// Simular usuario (asumimos el ID 1 o buscamos uno con el permiso)
$user = User::first(); 
auth()->login($user);

$rolActivo = $user->roles()->wherePivot('activo', true)->first();
$configuracion = Configuracion::first();

echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
echo "Rol Activo: " . ($rolActivo ? $rolActivo->name : 'N/A') . "\n";
echo "Permiso 'reportes_grupos.privilegio_reportar_grupo_cualquier_fecha': " . ($rolActivo?->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha') ? 'SI' : 'NO') . "\n";
echo "Config 'reportar_grupo_cualquier_dia': " . ($configuracion->reportar_grupo_cualquier_dia ? 'SI' : 'NO') . "\n";

$grupos = Grupo::take(5)->get();
foreach ($grupos as $grupo) {
    echo "Grupo: " . $grupo->nombre . " (ID: " . $grupo->id . ")\n";
    echo "  Tipo Grupo ID: " . $grupo->tipo_grupo_id . "\n";
    echo "  Max reportes/semana: " . $grupo->tipoGrupo->cantidad_maxima_reportes_semana . "\n";
    echo "  Verifica fecha automatica: " . ($grupo->verificaFechaAutomaticaReporte() ? 'TRUE' : 'FALSE') . "\n";
}
