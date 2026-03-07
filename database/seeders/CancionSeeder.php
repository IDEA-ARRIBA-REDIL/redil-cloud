<?php

namespace Database\Seeders;

use App\Models\Cancion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CancionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Cancion::firstOrCreate([
        'nombre' => 'Worship One',
        'album_id' => 1,
        'orden' => 1,
        'archivo' => 'cancion1.mp3',
        'artista' => 'David y sus bam bam',
       ]);

      Cancion::firstOrCreate([
        'nombre' => 'Worship Two',
        'album_id' => 1,
        'orden' => 2,
        'archivo' => 'cancion2.mp3',
        'artista' => 'David y sus bam bam',
      ]);

      Cancion::firstOrCreate([
        'nombre' => 'Worship Tres',
        'album_id' => 2,
        'orden' => 3,
        'archivo' => 'cancion3.mp3',
        'artista' => 'Asaf',
      ]);

      Cancion::firstOrCreate([
        'nombre' => 'Worship Four',
        'orden' => 4,
        'archivo' => 'cancion4.mp3',
        'artista' => 'Jedetún',
      ]);

    }
}
