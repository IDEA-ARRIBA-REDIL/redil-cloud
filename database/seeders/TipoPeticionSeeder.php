<?php

namespace Database\Seeders;

use App\Models\TipoPeticion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoPeticionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('storage/app/archivos_desarrollador/tipoPeticiones.sql');
        DB::unprepared(file_get_contents($path));

        // Asignar iconos predeterminados a los registros insertados
        $tipos = TipoPeticion::all();
        foreach ($tipos as $tipo) {
            $nombreLower = strtolower(trim($tipo->nombre));
            $icono = 'ti ti-help-circle';

            if (str_contains($nombreLower, 'famili')) {
                $icono = 'ti ti-users';
            } elseif (str_contains($nombreLower, 'sanid') || str_contains($nombreLower, 'salud') || str_contains($nombreLower, 'enfer')) {
                $icono = 'ti ti-heart';
            } elseif (str_contains($nombreLower, 'financ') || str_contains($nombreLower, 'econom') || str_contains($nombreLower, 'diner') || str_contains($nombreLower, 'trabaj') || str_contains($nombreLower, 'empleo')) {
                $icono = 'ti ti-coin';
            } elseif (str_contains($nombreLower, 'convers') || str_contains($nombreLower, 'salvac') || str_contains($nombreLower, 'arrepent')) {
                $icono = 'ti ti-circle';
            } elseif (str_contains($nombreLower, 'espirit') || str_contains($nombreLower, 'fe') || str_contains($nombreLower, 'orac')) {
                $icono = 'ti ti-user';
            }

            $this->command->info("Icono {$icono} asignado al tipo de petición {$tipo->nombre}.");

            $tipo->icono = $icono;
            $tipo->banner_email = null;
            $tipo->save();
        }
    }
}
