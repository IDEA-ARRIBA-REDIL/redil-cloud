<?php

namespace Database\Seeders;

use App\Models\TipoOfrenda;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoOfrendaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $data = [
      [
        'descripcion' => 'Ofrenda de diezmo',
        'generica' => true,
        'nombre' => 'Generica 1',
        'formulario_donaciones' => false,
        'codigo_sap' => 'SAP001',
        'tipo_reunion' => false,
      ],
      [
        'descripcion' => 'Ofrenda de acción de gracias',
        'generica' => false,
        'nombre' => 'Especifica 1',
        'formulario_donaciones' => true,
        'codigo_sap' => 'SAP002',
        'tipo_reunion' => true,
      ],
      [
        'descripcion' => 'Ofrenda misionera',
        'generica' => true,
        'nombre' => 'Generica 2',
        'formulario_donaciones' => true,
        'codigo_sap' => 'SAP003',
        'tipo_reunion' => false,
      ],
      [
        'descripcion' => 'Ofrenda especial de amor',
        'generica' => false,
        'nombre' => 'Especifica 2',
        'formulario_donaciones' => false,
        'codigo_sap' => 'SAP004',
        'tipo_reunion' => true,
      ],
      [
        'descripcion' => 'Ofrenda adultos...', // 5
        'generica' => true,
        'nombre' => 'Generica 3',
        'formulario_donaciones' => false,
        'codigo_sap' => 'SAP004',
        'tipo_reunion' => false,
        'ofrenda_obligatoria' => true
      ],
      [
        'descripcion' => 'Ofrenda niños...', //6
        'generica' => true,
        'nombre' => 'Generica 4',
        'formulario_donaciones' => false,
        'codigo_sap' => 'SAP004',
        'tipo_reunion' => false,
        'ofrenda_obligatoria' => true
      ]
    ];

    foreach ($data as $ofrenda) {
      TipoOfrenda::firstOrCreate(['codigo_sap' => $ofrenda['codigo_sap'], 'nombre' => $ofrenda['nombre']], $ofrenda);
    }
  }
}
