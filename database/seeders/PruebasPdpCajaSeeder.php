<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PuntoDePago;
use App\Models\Caja;
use App\Models\AsesorPdp;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class PruebasPdpCajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener Roles de Encargado y Cajero
        // Basado en la lógica de AsesorPdpController
        $rolEncargado = Role::where('es_encargado_pdp', true)->first();
        $rolCajero = Role::where('es_cajero_pdp', true)->first();

        if (!$rolEncargado || !$rolCajero) {
            $this->command->error('No se encontraron roles con flags es_encargado_pdp o es_cajero_pdp. Asegúrate de correr PermisoSeeder primero o tener los roles creados.');
            return;
        }

        $this->command->info("Rol Encargado encontrado: {$rolEncargado->name}");
        $this->command->info("Rol Cajero encontrado: {$rolCajero->name}");

        // 2. Definir distribución de PDPs por Sede
        $distribucionSedes = [
            2 => 2, // Sede ID 2: 2 PDPs
            1 => 2, // Sede ID 1: 2 PDPs
            6 => 1, // Sede ID 6: 1 PDP
            24 => 1, // Sede ID 24: 1 PDP
        ];

        $puntosCreados = [];

        DB::transaction(function () use ($distribucionSedes, &$puntosCreados) {
            // 3. Crear Puntos de Pago
            foreach ($distribucionSedes as $sedeId => $cantidad) {
                for ($i = 1; $i <= $cantidad; $i++) {
                    $pdp = PuntoDePago::firstOrCreate([
                        'nombre' => "Punto de Pago Sede {$sedeId} - {$i}",
                        'sede_id' => $sedeId,
                        'estado' => true,
                        // encargado_id se asignará más tarde
                    ]);
                    $puntosCreados[] = $pdp;
                }
            }
        });

        $this->command->info('Puntos de Pago creados: ' . count($puntosCreados));

        // 4. Crear Cajas (2 por cada PDP)
        $cajasCreadas = [];
        foreach ($puntosCreados as $pdp) {
            for ($i = 1; $i <= 2; $i++) {
                $caja = Caja::firstOrCreate([
                    'nombre' => "Caja {$i} - {$pdp->nombre}",
                    'punto_de_pago_id' => $pdp->id,
                    'estado' => true,
                    // user_id (cajero) se asignará más tarde
                ]);
                $cajasCreadas[] = $caja;
            }
        }
        $this->command->info('Cajas creadas: ' . count($cajasCreadas));

        // 5. Crear Asesores Tipo Encargado (User ID 2 y 5)
        $encargadosIds = [2, 5];
        foreach ($encargadosIds as $index => $userId) {
            $user = User::find($userId);
            if ($user) {
                // Crear registro en asesores_pdp
                AsesorPdp::firstOrCreate(
                    ['user_id' => $userId],
                    [
                        'es_encargado' => true,
                        'es_cajero' => false,
                        'activo' => true,
                        'descripcion' => 'Encargado de prueba creado por seeder'
                    ]
                );

                // Asignar Rol
                if (!$user->hasRole($rolEncargado)) {
                    // Usamos attach explícito para evitar error de model_type null
                    $user->roles()->attach($rolEncargado->id, [
                        'model_type' => 'App\Models\User',
                        'activo' => true, // Lo dejamos activo para pruebas
                        'dependiente' => 0
                    ]);
                }

                // Asignar como encargado a un PDP (distribuir)
                // Asignamos al PDP en la posición $index (0 o 1) de la lista creada
                if (isset($puntosCreados[$index])) {
                    $pdp = $puntosCreados[$index];
                    $pdp->encargado_id = $userId;
                    $pdp->save();
                    $this->command->info("Usuario ID {$userId} asignado como encargado de PDP: {$pdp->nombre}");
                }
            } else {
                $this->command->warn("Usuario ID {$userId} no encontrado. No se pudo crear como encargado.");
            }
        }

        // 6. Crear Asesores Tipo Cajero (User ID 6 y 4)
        $cajerosIds = [6, 4];
        foreach ($cajerosIds as $index => $userId) {
            $user = User::find($userId);
            if ($user) {
                // Crear registro en asesores_pdp
                AsesorPdp::firstOrCreate(
                    ['user_id' => $userId],
                    [
                        'es_encargado' => false,
                        'es_cajero' => true,
                        'activo' => true,
                        'descripcion' => 'Cajero de prueba creado por seeder'
                    ]
                );

                // Asignar Rol
                if (!$user->hasRole($rolCajero)) {
                     // Usamos attach explícito para evitar error de model_type null
                    $user->roles()->attach($rolCajero->id, [
                        'model_type' => 'App\Models\User',
                        'activo' => true, // Lo dejamos activo para pruebas
                        'dependiente' => 0
                    ]);
                }

                // Asignar a una Caja (distribuir)
                // Asignamos a la Caja en la posición $index de la lista creada
                if (isset($cajasCreadas[$index])) {
                    $caja = $cajasCreadas[$index];
                    $caja->user_id = $userId; // Asumiendo que user_id es el cajero asignado
                    $caja->save();
                    $this->command->info("Usuario ID {$userId} asignado a Caja: {$caja->nombre}");
                }
            } else {
                $this->command->warn("Usuario ID {$userId} no encontrado. No se pudo crear como cajero.");
            }
        }
    }
}
