<?php

namespace Database\Seeders;

use App\Models\SeccionTiempoConDios;
use Illuminate\Database\Seeder;

class SeccionTiempoConDiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 1',
            'titulo_step' => 'Adoración y oración',
            'titulo' => 'Conectate con Dios',
            'subtitulo' => 'Disfruta de un momento con Dios, conectate con Él y deja que te hable lo que quiere para ti hoy. Puedes colocar una de nuestras canciones instrumentales para este tiempo.',
            'orden' => 1,
            'icono' => 'ti ti-music-heart',
        ]);

        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 2',
            'titulo_step' => 'Leer y reflexiona',
            'titulo' => 'Deja que Dios te hable con su palabra',
            'subtitulo' => 'Elige el versiculo que deseas leer hoy.',
            'orden' => 2,
            'icono' => 'ti ti-notebook',
        ]);

        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 3',
            'titulo_step' => 'PAMPE',
            'titulo' => 'Diligencia la siguiente información',
            'subtitulo' => '',
            'orden' => 3,
            'icono' => 'ti ti-writing',
        ]);

        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 4',
            'titulo_step' => 'PAMPE',
            'titulo' => 'Diligencia la siguiente información',
            'subtitulo' => '',
            'orden' => 4,
            'icono' => 'ti ti-writing',
        ]);

        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 5',
            'titulo_step' => 'PAMPE',
            'titulo' => 'Diligencia la siguiente información',
            'subtitulo' => '',
            'orden' => 5,
            'icono' => 'ti ti-writing',
        ]);

        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 6',
            'titulo_step' => 'PAMPE',
            'titulo' => 'Diligencia la siguiente información',
            'subtitulo' => '',
            'orden' => 6,
            'icono' => 'ti ti-writing',
        ]);

        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 7',
            'titulo_step' => 'PAMPE',
            'titulo' => 'Diligencia la siguiente información',
            'subtitulo' => '',
            'orden' => 7,
            'icono' => 'ti ti-writing',
        ]);

        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 8',
            'titulo_step' => 'Habla con Dios',
            'titulo' => 'Ahora es tiempo para que hables con Dios',
            'subtitulo' => 'Sigue los siguientes pasos para aprovechar este tiempo:',
            'orden' => 8,
            'icono' => 'ti ti-speakerphone',
        ]);

        SeccionTiempoConDios::firstOrCreate([
            'nombre' => 'Sección 9',
            'titulo_step' => 'Toma nota',
            'titulo' => 'Toma nota y reflexiona',
            'subtitulo' => '',
            'orden' => 9,
            'icono' => 'ti ti-writing',
        ]);
    }
}
