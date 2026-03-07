<?php

namespace Database\Seeders;

use App\Models\BloqueClasificacionAsistente;
use App\Models\ClasificacionAsistente;
use Illuminate\Database\Seeder;

class BloqueClasificacionAsistenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear el bloque de "Adultos"
        $bloqueAdultos = BloqueClasificacionAsistente::firstOrCreate([
            'nombre' => 'Adultos',
            'tipo_calculo' => 'promedio',
        ]);

        // Asociar clasificaciones típicas de adultos si existen
        $clasificacionesAdultos = ClasificacionAsistente::whereIn('id', [
            1, //Adultos que llegaron por grupo
            2, //Adultos no creados
        ])->get();

        if ($clasificacionesAdultos->isNotEmpty()) {
            $bloqueAdultos->clasificaciones()->syncWithoutDetaching($clasificacionesAdultos->pluck('id'));
        }

        // 2. Crear el bloque de "Desglose por niños"
        $bloqueMenores = BloqueClasificacionAsistente::firstOrCreate([
            'nombre' => 'Niños',
            'tipo_calculo' => 'promedio',
        ]);

        // Asociar clasificaciones típicas de niños si existen
        $clasificacionesMenores = ClasificacionAsistente::whereIn('id', [
            3, //Niños que llegaron por grupo
            4, //Niños no creados
        ])->get();

        if ($clasificacionesMenores->isNotEmpty()) {
            $bloqueMenores->clasificaciones()->syncWithoutDetaching($clasificacionesMenores->pluck('id'));
        }

        // 3. Crear el bloque de "Conversiones"
        $bloqueConversiones = BloqueClasificacionAsistente::firstOrCreate([
            'nombre' => 'Conversiones',
            'tipo_calculo' => 'sumatoria',
        ]);

        // Asociar clasificaciones típicas de conversiones si existen
        $clasificacionesConversiones = ClasificacionAsistente::whereIn('id', [
            5, //Conversiones
        ])->get();

        if ($clasificacionesConversiones->isNotEmpty()) {
            $bloqueConversiones->clasificaciones()->syncWithoutDetaching($clasificacionesConversiones->pluck('id'));
        }

    }
}
