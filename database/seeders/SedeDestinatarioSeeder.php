<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SedeDestinatario;


class SedeDestinatarioSeeder extends Seeder
{
   
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SedeDestinatario::firstOrCreate([
            'nombre' => 'Sede Usaquén',
            'barrio' => 'Usaquén',
            'direccion' => 'Calle 123',
            'latitud' => 4.7240832,
            'longitud' => -74.0320435,
            'detalle' => 'Sede principal en el norte de Bogotá'
        ]);

        SedeDestinatario::firstOrCreate([
            'nombre' => 'Sede Chapinero',
            'barrio' => 'Chapinero',
            'direccion' => 'Calle 123',
            'latitud' => 4.6463074,
            'longitud' => -74.0636443,
            'detalle' => 'Sede cultural en zona comercial'
        ]);

        SedeDestinatario::firstOrCreate([
            'nombre' => 'Sede La Candelaria',
            'barrio' => 'La Candelaria',
            'direccion' => 'Calle 123',
            'latitud' => 4.5978901,
            'longitud' => -74.0760437,
            'detalle' => 'Sede histórica en el centro'
        ]);

        SedeDestinatario::firstOrCreate([
            'nombre' => 'Sede Kennedy',
            'barrio' => 'Kennedy',
            'direccion' => 'Calle 123',
            'latitud' => 4.6436289,
            'longitud' => -74.1560431,
            'detalle' => 'Sede comunitaria popular'
        ]);

        SedeDestinatario::firstOrCreate([
            'nombre' => 'Sede Suba',
            'barrio' => 'Suba',
            'direccion' => 'Calle 123',
            'latitud' => 4.7420832,
            'longitud' => -74.0920435,
            'detalle' => 'Sede con enfoque familiar'
        ]);
    }
}
