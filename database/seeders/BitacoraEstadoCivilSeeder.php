<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\BitacoraEstadoCivil;
use App\Models\EstadoCivil;

class BitacoraEstadoCivilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunos usuarios para crear bitácora
       /* $usuarios = User::take(10)->get();
        $estadosCiviles = EstadoCivil::all();

        if ($usuarios->count() > 0 && $estadosCiviles->count() > 1) {
            foreach ($usuarios as $usuario) {
                // Registro inicial
                if ($usuario->estado_civil_id) {
                    BitacoraEstadoCivil::firstOrCreate([
                        'user_id' => $usuario->id,
                        'estado_civil_id_anterior' => null,
                        'estado_civil_id_nuevo' => $usuario->estado_civil_id,
                        'autor_id' => User::first()->id ?? null,
                        'created_at' => now()->subMonths(2),
                        'updated_at' => now()->subMonths(2),
                    ]);
                }

                // Cambio de estado civil
                $nuevoEstado = $estadosCiviles->where('id', '!=', $usuario->estado_civil_id)->first();
                if ($nuevoEstado) {
                    BitacoraEstadoCivil::firstOrCreate([
                        'user_id' => $usuario->id,
                        'estado_civil_id_anterior' => $usuario->estado_civil_id,
                        'estado_civil_id_nuevo' => $nuevoEstado->id,
                        'autor_id' => User::first()->id ?? null,
                        'created_at' => now()->subMonth(),
                        'updated_at' => now()->subMonth(),
                    ]);
                }
            }
        }*/
    }
}
