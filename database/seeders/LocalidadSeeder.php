<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('storage/app/archivos_desarrollador/localidades.sql');
        DB::unprepared(file_get_contents($path));
    }
}
