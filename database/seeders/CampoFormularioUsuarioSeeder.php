<?php

namespace Database\Seeders;

use App\Models\CampoFormularioUsuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampoFormularioUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Foto',
        'nombre_bd'=> 'foto',
        'placeholder'=> 'Ingresa la foto',
        'name_id' => 'foto'
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12', 'orden' => 1 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12', 'orden' => 1 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12', 'orden' => 1 ]);
      $campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12', 'orden' => 1 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Fecha de nacimiento',
        'nombre_bd'=> 'fecha_nacimiento',
        'placeholder'=> 'Ingresa la fecha',
        'name_id' => 'fecha_de_nacimiento',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 4 ]);
      $campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 4 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 3 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Tipo de identificación',
        'nombre_bd'=> 'tipo_identificacion_id',
        'placeholder'=> 'Ingresa el tipo de identificación',
        'name_id' => 'tipo_de_identificación',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 5 ]);
      $campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 5 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 6 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Identificación',
        'nombre_bd'=> 'identificacion',
        'placeholder'=> 'Ingresa el número',
        'name_id' => 'identificación',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 7 ]);
      $campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 7 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 7 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Email',
        'nombre_bd'=> 'email',
        'placeholder'=> 'Ingresa el email',
        'name_id' => 'email',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(2, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(5, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(9, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 2 ]);
      $campo->secciones()->attach(12, [ 'requerido' => false, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 9 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 14 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Nombre',
        'nombre_bd'=> 'primer_nombre',
        'placeholder'=> 'Ingresa el nombre',
        'name_id' => 'primer_nombre',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 3 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 3 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 3 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 2 ]);
      $campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 2 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 1 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Apellido',
        'nombre_bd'=> 'primer_apellido',
        'placeholder'=> 'Ingresa el apellido',
        'name_id' => 'primer_apellido',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 3 ]);
      $campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 3 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 2 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Segundo nombre',
        'nombre_bd'=> 'segundo_nombre',
        'placeholder'=> 'Ingresa el nombre',
        'name_id' => 'segundo_nombre',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 3 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 3 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Segundo apellido',
        'nombre_bd'=> 'segundo_apellido',
        'placeholder'=> 'Ingresa el apellido',
        'name_id' => 'segundo_apellido',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 3 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Sexo',
        'nombre_bd'=> 'genero',
        'placeholder'=> 'Selecciona tu sexo',
        'name_id' => 'genero',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(7, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 6 ]);
      $campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-4', 'orden' => 6 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 4 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Estado civil',
        'nombre_bd'=> 'estado_civil_id',
        'placeholder'=> 'Selecciona tu estado civil',
        'name_id' => 'estado_civil',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 8 ]);
      //$campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 8 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Pais de nacimiento',
        'nombre_bd'=> 'pais_id',
        'placeholder'=> 'Selecciona tu pais de nacimiento',
        'name_id' => 'pais_de_nacimiento',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(1, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(4, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 9 ]);
      $campo->secciones()->attach(12, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 8 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 8 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Teléfono fijo',
        'nombre_bd'=> 'telefono_fijo',
        'placeholder'=> 'Ingrese el teléfono',
        'name_id' => 'teléfono_fijo',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(2, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(5, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 11 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Teléfono movil',
        'nombre_bd'=> 'telefono_movil',
        'placeholder'=> 'Ingrese el teléfono',
        'name_id' => 'teléfono_movil',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(2, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(5, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(9, [ 'requerido' => true, 'class' => 'col-12 col-sm-12 col-md-12', 'orden' => 1 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 12 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Otro teléfono',
        'nombre_bd'=> 'telefono_otro',
        'placeholder'=> 'Ingrese el teléfono',
        'name_id' => 'teléfono_otro',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(2, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(5, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 13 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> '¿Tipo de vivienda?',
        'nombre_bd'=> 'tipo_vivienda_id',
        'placeholder'=> 'Seleccione el tipo de vivienda',
        'name_id' => 'tipo_de_vivienda',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(2, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(5, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 9 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Dirección',
        'nombre_bd'=> 'direccion',
        'placeholder'=> 'Ingrese la dirección',
        'name_id' => 'dirección',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(2, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);
      $campo->secciones()->attach(5, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);
      $campo->secciones()->attach(9, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 3 ]);
      $campo->secciones()->attach(13, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 10 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> '¿Vives en Bogota?',
        'nombre_bd'=> 'pregunta_vives_en',
        'placeholder'=> '',
        'name_id' => 'pregunta_vives_en'
      ]);
      $campo->secciones()->attach(2, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 6 ]);
      $campo->secciones()->attach(5, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 6 ]);
      $campo->secciones()->attach(9, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 4 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Barrio o localidad',
        'nombre_bd'=> 'ubicacion',
        'placeholder'=> 'Busca aquí el barrio o la localidad',
        'name_id' => 'ubicación',
      ]);
      $campo->secciones()->attach(2, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 7 ]);
      $campo->secciones()->attach(5, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 7 ]);
      $campo->secciones()->attach(9, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Nivel académico',
        'nombre_bd'=> 'nivel_academico_id',
        'placeholder'=> 'Selecciona el nivel académico',
        'name_id' => 'nivel_académico',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(14, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 1 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Estado nivel académico',
        'nombre_bd'=> 'estado_nivel_academico_id',
        'placeholder'=> 'Selecciona el estado nivel académico',
        'name_id' => 'estado_nivel_académico',
        'visible_resumen' => true
      ]);
     // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(14, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 2 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Profesión',
        'nombre_bd'=> 'profesion_id',
        'placeholder'=> 'Selecciona la profesión',
        'name_id' => 'profesión',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(14, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 3 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Ocupación',
        'nombre_bd'=> 'ocupacion_id',
        'placeholder'=> 'Selecciona la ocupación',
        'name_id' => 'ocupación',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(14, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 4 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Sector económico',
        'nombre_bd'=> 'sector_economico_id',
        'placeholder'=> 'Selecciona el sector económico',
        'name_id' => 'sector_económico',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(14, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Tipo de sangre',
        'nombre_bd'=> 'tipo_sangre_id',
        'placeholder'=> 'Selecciona el tipo de sangre',
        'name_id' => 'tipo_de_sangre',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(15, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 1 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Indicaciones médicas',
        'nombre_bd'=> 'indicaciones_medicas',
        'placeholder'=> 'Ingresa las indicaciones médicas',
        'name_id' => 'indicaciones_médicas',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      $campo->secciones()->attach(15, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 2 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> '¿Tienes una petición?',
        'nombre_bd'=> 'tienes_una_peticion',
        'placeholder'=> '',
        'name_id' => 'tienes_una_peticion'
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);


      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Tipo de petición',
        'nombre_bd'=> 'tipo_peticion_id',
        'placeholder'=> 'Selecciona el tipo de petición',
        'name_id' => 'tipo_de_petición',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Escribe tu petición',
        'nombre_bd'=> 'descripcion_peticion',
        'placeholder'=> 'Escribe tu petición',
        'name_id' => 'descripción_de_la_petición',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 4 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> '¿En que sede quieres congregarte?',
        'nombre_bd'=> 'sede_id',
        'placeholder'=> 'selecciona la sede',
        'name_id' => 'sede',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(21, [ 'requerido' => true, 'class' => 'col-12 col-sm-12 col-md-12', 'orden' => 1 ]);
      $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 3 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> '¿Comó ingresaste?',
        'nombre_bd'=> 'tipo_vinculacion_id',
        'placeholder'=> 'Selecciona una opción',
        'name_id' => 'vinculación',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 2 ]);
      $campo->secciones()->attach(20, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 1 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Información opcional',
        'nombre_bd'=> 'informacion_opcional',
        'placeholder'=> 'Ingresa la información opcional',
        'name_id' => 'información_opcional',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      $campo->secciones()->attach(18, [ 'requerido' => true, 'class' => 'col-12 col-sm-12 col-md-12', 'orden' => 1 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Campo reservado',
        'nombre_bd'=> 'campo_reservado',
        'placeholder'=> 'Ingresa el campo reservado',
        'name_id' => 'campo_reservado',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Archivo a',
        'nombre_bd'=> 'archivo_a',
        'placeholder'=> 'Sin archivo',
        'name_id' => 'archivo_a',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);
      $campo->secciones()->attach(17, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 1 ]);
/*
      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Archivo b',
        'nombre_bd'=> 'archivo_b',
        'placeholder'=> 'Sin archivo',
        'name_id' => 'archivo_b',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Archivo c',
        'nombre_bd'=> 'archivo_c',
        'placeholder'=> 'Sin archivo',
        'name_id' => 'archivo_c',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);


      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Archivo d',
        'nombre_bd'=> 'archivo_d',
        'placeholder'=> 'Sin archivo',
        'name_id' => 'archivo_d',
        'visible_resumen' => true
      ]);
      $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 5 ]);*/

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Tipo identificación del acudiente',
        'nombre_bd'=> 'tipo_identificacion_acudiente_id',
        'placeholder'=> 'Selecciona la identificación',
        'name_id' => 'tipo_de_identificación_del_acudiente',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
       $campo->secciones()->attach(16, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 3 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Identificación del acudiente',
        'nombre_bd'=> 'identificacion_acudiente',
        'placeholder'=> 'Ingresa la identificación',
        'name_id' => 'identificación_del_acudiente',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
       $campo->secciones()->attach(16, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 4 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Nombre acudiente',
        'nombre_bd'=> 'nombre_acudiente',
        'placeholder'=> 'Ingresa el nombre acudiente',
        'name_id' => 'nombre_del_acudiente',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
       $campo->secciones()->attach(16, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 1 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Teléfono acudiente',
        'nombre_bd'=> 'telefono_acudiente',
        'placeholder'=> 'Ingresa el teléfono acudiente',
        'name_id' => 'teléfono_del_acudiente',
        'visible_resumen' => true
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
       $campo->secciones()->attach(16, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 2 ]);


      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> '¿Tienes hijos menores de edad?',
        'nombre_bd'=> 'tienes_hijos_menores_de_edad',
        'placeholder'=> '¿Tienes hijos menores de edad?',
        'name_id' => 'tienes_hijos_menores_de_edad',
        'visible_resumen' => true
      ]);

      $campo->secciones()->attach(8, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 10 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Contraseña',
        'nombre_bd'=> 'password',
        'placeholder'=> 'Ingresa la contraseña',
        'name_id' => 'password',
        'visible_resumen' => true
      ]);

      $campo->secciones()->attach(10, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 1, 'informacion_de_apoyo' => 'Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial (*, -, ., ?, &, $, #).' ]);


      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Confirmar contraseña',
        'nombre_bd'=> 'password_confirmation',
        'placeholder'=> 'Confirmar contraseña',
        'name_id' => 'password_confirmation'
      ]);

      $campo->secciones()->attach(10, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 2 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> '¿Qué tipo de parentesco tienes con el menor?',
        'nombre_bd'=> 'tipo_pariente_id',
        'placeholder'=> 'Tipo de parentesco',
        'name_id' => 'tipo_de_parentesco',
        'visible_resumen' => true
      ]);

      $campo->secciones()->attach(11, [ 'requerido' => true, 'class' => 'col-12 col-sm-12 col-md-12', 'orden' => 1 ]);


      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Fecha y hora de creación',
        'nombre_bd'=> 'created_at',
        'placeholder'=> '',
        'name_id' => '',
        'visible_resumen' => true
      ]);

      $campo->secciones()->attach(20, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 2 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre'=> 'Creado por',
        'nombre_bd'=> 'usuario_creacion_id',
        'placeholder'=> '',
        'name_id' => '',
        'visible_resumen' => true
      ]);

      $campo->secciones()->attach(20, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 3 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre' => 'Red',
        'nombre_bd' => '',
        'placeholder' => 'Selecciona la red',
        'name_id' => 'red',
        'es_campo_extra' => true,
        'visible_resumen' => true,
        'tipo_de_campo' => 3,
        'opciones_select' => '[{"id": "1","nombre":"PRE JUVENIL","visible":"1","value":"1"},{"id": "2","nombre":"JUVENIL","visible":"1","value":"2"}]'
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 6 ]);
      $campo->secciones()->attach(19, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 1 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre' => 'Número de hijos',
        'nombre_bd' => '',
        'placeholder' => 'Ingresa el número de hijo',
        'name_id' => 'numero_de_hijos',
        'es_campo_extra' => true,
        'visible_resumen' => true,
        'tipo_de_campo' => 1,
        'opciones_select' => ''
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 6 ]);
      $campo->secciones()->attach(19, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 2 ]);


      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre' => 'Multiple',
        'nombre_bd' => '',
        'placeholder' => 'Selecciona el multiple',
        'name_id' => 'multiple',
        'es_campo_extra' => true,
        'visible_resumen' => true,
        'tipo_de_campo' => 4,
        'opciones_select' => '[{"id": "1","nombre":"AAA","visible":"1","value":"1"},{"id": "2","nombre":"BBB","visible":"1","value":"2"}]',
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 6 ]);
      $campo->secciones()->attach(19, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 3 ]);

      $campo = CampoFormularioUsuario::firstOrCreate([
        'nombre' => 'Textarea',
        'nombre_bd' => '',
        'placeholder' => 'Ingresa el textarea',
        'name_id' => 'textarea',
        'es_campo_extra' => true,
        'visible_resumen' => true,
        'tipo_de_campo' => 2,
        'opciones_select' => ''
      ]);
       // seccion c $campo->secciones()->attach(3, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 5 ]);
      // seccion cc $campo->secciones()->attach(6, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-3', 'orden' => 6 ]);
      $campo->secciones()->attach(19, [ 'requerido' => true, 'class' => 'col-12 col-sm-6 col-md-6', 'orden' => 4 ]);

    }
}
