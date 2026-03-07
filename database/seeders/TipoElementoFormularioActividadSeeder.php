<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoElementoFormularioActividad;

class TipoElementoFormularioActividadSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Encabezado de sección',
      'tiene_respuesta' => false,
      'clase' => 'encabezado'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Texto respuesta corta (input sencillo)',
      'tiene_respuesta' => true,
      'clase' => 'corta'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Texto respuesta larga (text area)',
      'tiene_respuesta' => true,
      'clase' => 'larga'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Única opción SI - NO',
      'tiene_respuesta' => true,
      'clase' => 'si_no'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Opción multiple unica respuesta',
      'tiene_respuesta' => true,
      'clase' => 'unica_respuesta'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Opción multiple con multiple respuesta',
      'tiene_respuesta' => true,
      'clase' => 'multiple_respuesta'
    ]);


    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Tipo Fecha',
      'tiene_respuesta' => true,
      'clase' => 'fecha'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Númerico',
      'tiene_respuesta' => true,
      'clase' => 'numero'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Moneda',
      'tiene_respuesta' => true,
      'clase' => 'moneda'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Subir archivos',
      'tiene_respuesta' => true,
      'clase' => 'archivo'
    ]);

    TipoElementoFormularioActividad::firstOrCreate([
      'nombre' => 'Subir imagen',
      'tiene_respuesta' => true,
      'clase' => 'imagen'
    ]);
  }
}
