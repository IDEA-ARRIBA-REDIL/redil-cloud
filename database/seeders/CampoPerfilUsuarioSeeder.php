<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CampoPerfilUsuario;

class CampoPerfilUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

      /*Seccion 1*/
      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Nombre',
        'nombre_bd'=> 'primer_nombre',
        'placeholder'=> 'Ingresa el nombre',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      /*
      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Nombre 2',
        'nombre_bd'=> 'segundo_nombre',
        'seccion'=> 1
      ]);

      $campo->roles()->attach(1, [ 'requerido' => false ]);*/

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Apellido',
        'nombre_bd'=> 'primer_apellido',
        'placeholder'=> 'Ingresa el apellido',
        'seccion'=> 1

      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      /*$campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Apellido 2',
        'nombre_bd'=> 'segundo_apellido',
        'seccion'=> 1

      ]);
      $campo->roles()->attach(1, [ 'requerido' => false ]);*/

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Fecha de nacimiento',
        'nombre_bd'=> 'fecha_nacimiento',
        'placeholder'=> 'Ingresa la fecha de nacimiento',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Sexo',
        'nombre_bd'=> 'genero',
        'placeholder'=> 'Selecciona el sexo',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Estado civil',
        'nombre_bd'=> 'estado_civil_id',
        'placeholder'=> 'Selecciona el estado civil',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Tipo identificación',
        'nombre_bd'=> 'tipo_identificacion_id',
        'placeholder'=> 'Selecciona el tipo de identificación',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Identificación',
        'nombre_bd'=> 'identificacion',
        'placeholder'=> 'Ingresa la identificación',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Pais de nacimiento',
        'nombre_bd'=> 'pais_id',
        'placeholder'=> 'Selecciona el pais de nacimiento',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> '¿Qué vivienda tienes?',
        'nombre_bd'=> 'tipo_vivienda_id',
        'placeholder'=> 'Selecciona el tipo de vivienda',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Dirección',
        'nombre_bd'=> 'direccion',
        'placeholder'=> 'Ingresa la dirección',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Teléfono fijo',
        'nombre_bd'=> 'telefono_fijo',
        'placeholder'=> 'Ingresa el teléfono fijo',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Teléfono movil',
        'nombre_bd'=> 'telefono_movil',
        'placeholder'=> 'Ingresa el teléfono movil',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Otro teléfono',
        'nombre_bd'=> 'telefono_otro',
        'placeholder'=> 'Ingresa el teléfono',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);


      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Email',
        'nombre_bd'=> 'email',
        'placeholder'=> 'Ingresa el email',
        'seccion'=> 1
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);


      /*Seccion 2*/
      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Nivel académico',
        'nombre_bd'=> 'nivel_academico_id',
        'placeholder'=> 'Selecciona el nivel académico',
        'seccion'=> 2
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Estado nivel académico',
        'nombre_bd'=> 'estado_nivel_academico_id',
        'placeholder'=> 'Selecciona el estado del nivel académico',
        'seccion'=> 2
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Profesión',
        'nombre_bd'=> 'profesion_id',
        'placeholder'=> 'Selecciona la profesión',
        'seccion'=> 2
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Ocupación',
        'nombre_bd'=> 'ocupacion_id',
        'placeholder'=> 'Selecciona la ocupación',
        'seccion'=> 2
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Sector económico',
        'nombre_bd'=> 'sector_economico_id',
        'placeholder'=> 'Selecciona el sector económico',
        'seccion'=> 2
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      /*Seccion 3*/
      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Tipo de sangre',
        'nombre_bd'=> 'tipo_sangre_id',
        'placeholder'=> 'Selecciona tipo de sangre',
        'seccion'=> 3
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Indicaciones médicas',
        'nombre_bd'=> 'indicaciones_medicas',
        'placeholder'=> 'Ingrese las indicaciones médica',
        'seccion'=> 3
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      /*Seccion 4*/
      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Nombre',
        'nombre_bd'=> 'nombre_acudiente',
        'placeholder'=> 'Ingrese el nombre',
        'seccion'=> 4
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Teléfono',
        'nombre_bd'=> 'telefono_acudiente',
        'placeholder'=> 'Ingrese el teléfono',
        'seccion'=> 4
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Tipo identificación',
        'nombre_bd'=> 'tipo_identificacion_acudiente_id',
        'placeholder'=> 'Ingrese el tipo de identificación',
        'seccion'=> 4
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Identificación',
        'nombre_bd'=> 'identificacion_acudiente',
        'placeholder'=> 'Ingrese la identificación',
        'seccion'=> 4
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      /*Seccion 5*/
      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Archivo a',
        'nombre_bd'=> 'archivo_a',
        'placeholder'=> 'Ingrese el archivo A',
        'tiene_descargable' => true,
        'seccion'=> 5
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Archivo b',
        'nombre_bd'=> 'archivo_b',
        'placeholder'=> 'Ingrese el archivo B',
        'tiene_descargable' => true,
        'seccion'=> 5
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Archivo c',
        'nombre_bd'=> 'archivo_c',
        'placeholder'=> 'Ingrese el archivo C',
        'tiene_descargable' => true,
        'seccion'=> 5
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Archivo d',
        'nombre_bd'=> 'archivo_d',
        'placeholder'=> 'Ingrese el archivo D',
        'tiene_descargable' => true,
        'seccion'=> 5
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      /*Seccion 6*/
      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Información opcional',
        'nombre_bd'=> 'informacion_opcional',
        'placeholder'=> 'Ingrese la información opcional',
        'seccion'=> 6
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> 'Campo reservado',
        'nombre_bd'=> 'campo_reservado',
        'placeholder'=> 'Ingrese el campo reservado',
        'seccion'=> 6
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

      /*Seccion 7*/

      /*Seccion 8*/
      $campo = CampoPerfilUsuario::firstOrCreate([
        'nombre'=> '¿Cómo te vinculaste a la iglesia?',
        'nombre_bd'=> 'tipo_vinculacion_id',
        'placeholder'=> 'Seleccione el tipo de vinculación',
        'seccion'=> 8
      ]);
      $campo->roles()->attach(1, [ 'requerido' => true ]);

    }
}
