<?php

namespace Database\Seeders;

use App\Models\ConfiguracionRv;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfiguracionRvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        ConfiguracionRv::firstOrCreate(
            ['nombre_general' => 'Rueda de la vida'],
            [
            'nombre_metas' => 'Metas',
            'nombre_habitos' => 'Habitos',
            'label_promedio_general' => 'Promedio',
            'titulo_vista_final' => '!Felicitaciones¡',
            'label_btn_vista_final' => 'Salir',
            'url_vista_final' => 'dashboard',
            'mensaje_vista_final' => 'Has finalizado tu rueda de la vida, sigue interactuando con nuestra platadorma',
            'periodicidad' => '30',
            'promedio_general' => '6'
        ]);
    }
}
