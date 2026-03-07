<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\CampoExtra;
use Illuminate\Support\Facades\DB;

class CampoExtraSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Id: 1
    $campo1 = CampoExtra::firstOrCreate([
      'nombre' => 'Red',
      'placeholder' => 'Selecciona la red',
      'tipo_de_campo' => 3,
      'required' => true,
      'class_col' => 'col-lg-3 col-sm-12 col-12 col-md-4',
      'class_id' => 'ministerio_asociado',
      'opciones_select' =>
      '[{"id": "1","nombre":"PRE JUVENIL","visible":"1","value":"1"},{"id": "2","nombre":"JUVENIL","visible":"1","value":"2"}]',
      'visible' => true,
    ]);

    // Id: 2
    $campo2 = CampoExtra::firstOrCreate([
      'nombre' => 'Número de hijos',
      'placeholder' => 'Ingresa el número de hijo',
      'tipo_de_campo' => 1,
      'required' => false,
      'class_col' => 'col-lg-3 col-sm-12 col-12 col-md-3',
      'class_id' => 'hijos',
      'opciones_select' => '',
      'visible' => true,
    ]);

    // Id: 3
    $campo3 = CampoExtra::firstOrCreate([
      'nombre' => 'Multiple',
      'tipo_de_campo' => 4,
      'placeholder' => 'Selecciona el multiple',
      'required' => false,
      'class_col' => 'col-lg-3 col-sm-12 col-12 col-md-3',
      'class_id' => 'multiple',
      'opciones_select' => '[{"id": "1","nombre":"AAA","visible":"1","value":"1"},{"id": "2","nombre":"BBB","visible":"1","value":"2"}]',
      'visible' => true,
    ]);

    // Id: 4
    $campo4 = CampoExtra::firstOrCreate([
      'nombre' => 'Textarea',
      'placeholder' => 'Ingresa el textarea',
      'tipo_de_campo' => 2,
      'required' => false,
      'class_col' => 'col-lg-3 col-sm-12 col-12 col-md-3',
      'class_id' => 'textarea',
      'opciones_select' => '',
      'visible' => true,
    ]);

    // relacion autogestion perfil
    $campo1->roles()->attach(1, ['requerido' => true]);
    $campo2->roles()->attach(1, ['requerido' => true]);
    $campo3->roles()->attach(1, ['requerido' => true]);
    $campo4->roles()->attach(1, ['requerido' => true]);

    // relación con el formulario 3
    DB::table('campos_extras_formularios')->updateOrInsert(
      ['campo_extra_id' => 1, 'formulario_id' => 3],
      ['visible' => TRUE, 'required' => TRUE]
    );

    DB::table('campos_extras_formularios')->updateOrInsert(
      ['campo_extra_id' => 2, 'formulario_id' => 3],
      ['visible' => TRUE, 'required' => TRUE]
    );

    DB::table('campos_extras_formularios')->updateOrInsert(
      ['campo_extra_id' => 3, 'formulario_id' => 3],
      ['visible' => TRUE, 'required' => TRUE]
    );

    DB::table('campos_extras_formularios')->updateOrInsert(
      ['campo_extra_id' => 4, 'formulario_id' => 3],
      ['visible' => TRUE, 'required' => TRUE]
    );

    // relación con el formulario 4
    DB::table('campos_extras_formularios')->updateOrInsert(
      ['campo_extra_id' => 1, 'formulario_id' => 4],
      ['visible' => TRUE, 'required' => TRUE]
    );

    DB::table('campos_extras_formularios')->updateOrInsert(
      ['campo_extra_id' => 2, 'formulario_id' => 4],
      ['visible' => TRUE, 'required' => TRUE]
    );

    DB::table('campos_extras_formularios')->updateOrInsert(
      ['campo_extra_id' => 3, 'formulario_id' => 4],
      ['visible' => TRUE, 'required' => TRUE]
    );

    DB::table('campos_extras_formularios')->updateOrInsert(
      ['campo_extra_id' => 4, 'formulario_id' => 4],
      ['visible' => TRUE, 'required' => TRUE]
    );
  }
}
