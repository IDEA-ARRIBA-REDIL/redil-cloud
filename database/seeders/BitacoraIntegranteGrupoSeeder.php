<?php

namespace Database\Seeders;

use App\Models\BitacoraIntegranteGrupo;
use App\Models\IntegranteGrupo;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BitacoraIntegranteGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $integrantes = IntegranteGrupo::all();

        foreach ($integrantes as $integrante) {
            // Evitar duplicados si ya se empezó a registrar
            $existe = BitacoraIntegranteGrupo::where('user_id', $integrante->user_id)
                ->where('grupo_id', $integrante->grupo_id)
                ->exists();

            if (!$existe) {
                BitacoraIntegranteGrupo::create([
                    'user_id' => $integrante->user_id,
                    'grupo_id' => $integrante->grupo_id,
                    'estado_vinculacion' => true,
                    'autor_id' => 1, // Administrador por defecto para la inicialización
                    'created_at' => Carbon::now()->subYear(), // Una fecha antigua para que aparezcan como ubicados desde siempre
                    'updated_at' => Carbon::now()->subYear(),
                ]);
            }
        }
    }
}
