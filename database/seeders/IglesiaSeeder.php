<?php

namespace Database\Seeders;

use App\Models\Iglesia;
use Illuminate\Database\Seeder;

class IglesiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Iglesia::firstOrCreate([
            'nombre' => 'Iglesia Manatial de Vida Eterna',
            'logo' => 'logo_1784134587.png',
            'logo_negro' => 'logo_negro_1784134587.png',
            'municipio_id' => 1089,
            'pais_id' => 45,
            'latitud' => '4.0747',
            'longitud' => '-76.2016',
            'website' => 'soymanantial.com',
            'identificacion' => '800.116.748-1',
            'direccion' => 'Av. Calle 17 # 80 A - 50',
            'telefono1' => '(305) 7341942',
            'email_soporte' => 'eventos@manantial.co',
            'url_subdominio' => 'redil.ubicalo.com',
            'facebook' => 'https://www.facebook.com/software.redil',
            'instagram' => 'https://www.instagram.com/software.redil',
            'youtube' => 'https://www.youtube.com/@SoftwareRedil',
            'tiktok' => 'https://www.tiktok.com/@softwareredil',

        ]);
    }
}
