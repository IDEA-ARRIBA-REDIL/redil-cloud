<?php

namespace Database\Seeders;

use App\Models\DocumentoEquivalente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentoEquivalenteSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // DOCUMENTO EQUIVALENTE
    $docEq1 = DocumentoEquivalente::firstOrCreate([
      'nombre' => 'Juan Pérez',
      'identificacion' => '987654321',
      'cantidad' => 2,
      'detalle' => 'Servicios de logística',
      'telefono' => '3123456789',
      'direccion' => 'Carrera 12 #34-56',
      'valor' => 150000.4521
    ]);
    $docEq2 = DocumentoEquivalente::firstOrCreate([
      'nombre' => 'Juan Mera',
      'identificacion' => '897654321',
      'cantidad' => 3,
      'detalle' => 'Servicios de alimentación',
      'telefono' => '3123456789',
      'direccion' => 'Carrera 21 #34-56',
      'valor' => 160000.4521
    ]);
  }
}
