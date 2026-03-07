<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TipoPago;
use App\Models\EstadoPago;

echo "--- TIPOS DE PAGO ---\n";
$tipos = TipoPago::all();
foreach ($tipos as $tipo) {
    echo "ID: {$tipo->id} | Nombre: {$tipo->nombre} | Key: {$tipo->key_reservada}\n";
}

echo "\n--- ESTADOS DE PAGO ---\n";
$estados = EstadoPago::all();
foreach ($estados as $estado) {
    echo "ID: {$estado->id} | Nombre: {$estado->nombre} | TipoPagoID: {$estado->tipo_pago_id} | InicialDefecto: " . ($estado->estado_inicial_defecto ? 'SI' : 'NO') . "\n";
}
