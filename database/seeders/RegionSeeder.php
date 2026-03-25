<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('storage/app/archivos_desarrollador/regiones.sql');
        DB::unprepared(file_get_contents($path));
    }
}
