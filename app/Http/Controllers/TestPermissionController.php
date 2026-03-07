<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestPermissionController extends Controller
{
    public function check()
    {
        $user = auth()->user();
        if (!$user) {
            return 'ERROR: No hay un usuario autenticado.';
        }

        $permissionName = 'personas.subitem_lista_asistentes';

        // Lógica de Spatie que usa el middleware y @can
        $canResult = $user->can($permissionName);

        // Lógica manual directa sobre el rol activo
        $activeRole = $user->roles()->wherePivot('activo', true)->first();
        $activeRoleHasPermission = false;
        if ($activeRole) {
            $activeRoleHasPermission = $activeRole->hasPermissionTo($permissionName);
        }

        echo "<h1>Diagnóstico Aislado</h1>";
        echo "------------------------------------<br>";
        echo "<b>Usuario:</b> " . $user->email . "<br>";
        echo "<b>Rol Activo:</b> " . ($activeRole ? $activeRole->name : 'Ninguno') . "<br>";
        echo "<b>Permiso Requerido:</b> " . $permissionName . "<br>";
        echo "------------------------------------<br>";

        echo "<h2>Resultado de la Lógica de Spatie (\$user->can): <font color='" . ($canResult ? "green" : "red") . "'>" . ($canResult ? 'SÍ (Acceso Permitido)' : 'NO (Acceso Denegado)') . "</font></h2>";

        echo "<h2>Resultado de la Lógica Manual (\$rolActivo->hasPermissionTo): <font color='" . ($activeRoleHasPermission ? "green" : "red") . "'>" . ($activeRoleHasPermission ? 'SÍ' : 'NO') . "</font></h2>";

        echo "<br><hr><br>";

        // Mostramos los permisos del rol activo para confirmar
        echo "<h3>Permisos reales del rol activo ('" . ($activeRole ? $activeRole->name : 'N/A') . "'):</h3>";
        if ($activeRole) {
            echo "<pre>";
            print_r($activeRole->permissions->pluck('name')->toArray());
            echo "</pre>";
        } else {
            echo "No se encontró rol activo.";
        }
    }
}
