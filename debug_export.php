<?php
// Debug Script to verify relationship data
use App\Models\Periodo;
use App\Models\Actividad;
use App\Models\User;
use App\Models\Compra;
use App\Models\Matricula;

// ID of a user from the screenshot (e.g., 5 or 6)
$userId = 6;

// Get the latest Period ID associated with this user's matricula or just pick one
$matricula = Matricula::where('user_id', $userId)->first();
if (!$matricula) {
    echo "No matricula found for user $userId\n";
    exit;
}
$periodoId = $matricula->periodo_id;

echo "Checking for User ID: $userId, Periodo ID: $periodoId\n";

// 1. Check Activity for Period
$actividad = Actividad::where('periodo_id', $periodoId)->first();
if (!$actividad) {
    echo "No Actividad found for Period $periodoId\n";
} else {
    echo "Found Actividad ID: " . $actividad->id . "\n";

    // 2. Check Compra for User and Activity
    $compra = Compra::where('actividad_id', $actividad->id)
                    ->where('user_id', $userId)
                    ->first();

    if (!$compra) {
        echo "No Compra found for User $userId and Actividad " . $actividad->id . "\n";
        // Check if ANY compra exists for this user
        $p = Compra::where('user_id', $userId)->get();
        echo "User has " . $p->count() . " total compras.\n";
        foreach($p as $c) {
            echo " - Compra ID: " . $c->id . " | Actividad ID: '" . $c->actividad_id . "' | Metodo Pago ID: " . $c->metodo_pago_id . "\n";
        }
    } else {
        echo "Found Compra ID: " . $compra->id . "\n";
        if ($compra->metodoPago) {
            echo "Metodo Pago: " . $compra->metodoPago->nombre . "\n";
        } else {
            echo "Metodo Pago relation is NULL (metodo_pago_id: " . $compra->metodo_pago_id . ")\n";
        }
    }
}
