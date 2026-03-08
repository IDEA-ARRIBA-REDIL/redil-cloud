<?php

namespace Database\Seeders;

use App\Models\Role as ModelsRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // 1. Buscar los roles que necesitaremos
    $superAdmin = Role::findByName('Super Administrador');
    $pastor = Role::findByName('Pastor');
    $lider = Role::findByName('Lider');
    $oveja = Role::findByName('Oveja');
    $alumno = Role::findByName('Alumno');
    $maestro = Role::findByName('Maestro');
    $administrador = Role::findByName('Administrativo');
    $consejero = Role::findByName('Consejero');
    $consolidadorMedellin = Role::findByName('Consolidador Medellin');
    $consolidadorBogota = Role::findByName('Consolidador Bogota');

    // 2. Crear usuarios y asignar roles

    // 2. Crear usuarios y asignar roles
    $usuario1 = \App\Models\User::withTrashed()->firstOrCreate(
      ['email' => 'idea.arriba@gmail.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'Admin',
        'primer_apellido' => 'Admin',
        'genero' => 0,
        'identificacion' => '111222333',
        'tipo_usuario_id' => 6,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '2000-01-05',
        'esta_aprobado' => 1,
        'tipo_vinculacion_id' => 4,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 2,
      ]
    );
    if($usuario1->wasRecentlyCreated || $usuario1->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario1->roles()->detach($superAdmin->id);
        $usuario1->roles()->attach($superAdmin->id, ['activo' => 1, 'dependiente' => 0, 'model_type' => 'App\Models\User']);
        $usuario1->roles()->detach($administrador->id);
        $usuario1->roles()->attach($administrador->id, ['activo' => 0, 'dependiente' => 0, 'model_type' => 'App\Models\User']); // Rol de escuela
    }

    $usuario2 = \App\Models\User::withTrashed()->firstOrCreate(
      ['email' => 'pastorprincipal@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'Hector Fabio',
        'primer_apellido' => 'Jaramillo',
        'genero' => 0,
        'identificacion' => '2384283482',
        'tipo_usuario_id' => 1,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '1977-02-05',
        'tipo_vinculacion_id' => 2,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 2,
      ]
    );
    if($usuario2->wasRecentlyCreated || $usuario2->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario2->roles()->detach($pastor->id);
        $usuario2->roles()->attach($pastor->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
        $usuario2->iglesiaEncargada()->detach(1);
        $usuario2->iglesiaEncargada()->attach(1); // Relación de iglesia
    }

    $usuario3 = \App\Models\User::withTrashed()->firstOrCreate(
      ['email' => 'lider_d@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'telefono_fijo' => '435354',
        'telefono_otro' => '453868',
        'telefono_movil' => '3155552546',
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'Fabian',
        'primer_apellido' => 'Aguirre',
        'genero' => 0,
        'identificacion' => '243599756',
        'tipo_usuario_id' => 2,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '1985-11-10',
        'ultimo_reporte_grupo' => '2024-08-20',
        'ultimo_reporte_reunion' => '2024-08-20',
        'tipo_vinculacion_id' => 2,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 2,
      ]
    );
    if($usuario3->wasRecentlyCreated || $usuario3->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario3->roles()->detach($lider->id);
        $usuario3->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
        $usuario3->roles()->detach($consolidadorBogota->id);
        $usuario3->roles()->attach($consolidadorBogota->id, ['activo' => 0, 'dependiente' => 0, 'model_type' => 'App\Models\User']);
    }

    $usuario4 = \App\Models\User::withTrashed()->firstOrCreate(
      ['email' => 'lider_b@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'activo' => 0,
        'telefono_fijo' => '435354',
        'telefono_otro' => '453868',
        'telefono_movil' => '3155552546',
        'asistente_id' => 1,
        'primer_nombre' => 'James',
        'primer_apellido' => 'Cano',
        'genero' => 0,
        'identificacion' => '43545345345',
        'tipo_usuario_id' => 2,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '1980-11-25',
        'ultimo_reporte_grupo' => '2024-01-13',
        'ultimo_reporte_reunion' => '2024-08-20',
        'tipo_vinculacion_id' => 1,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 2,
      ]
    );
    if($usuario4->wasRecentlyCreated || $usuario4->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario4->roles()->detach($lider->id);
        $usuario4->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
        $usuario4->roles()->detach($consolidadorMedellin->id);
        $usuario4->roles()->attach($consolidadorMedellin->id, ['activo' => 0, 'dependiente' => 0, 'model_type' => 'App\Models\User']);
    }

    $usuario5 = \App\Models\User::withTrashed()->firstOrCreate(
      ['email' => 'lider_c@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'telefono_fijo' => '435354',
        'telefono_otro' => '453868',
        'telefono_movil' => '3155552546',
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'Asiste',
        'primer_apellido' => 'a dos grupos',
        'genero' => 0,
        'identificacion' => '735837375',
        'tipo_usuario_id' => 2,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '2010-12-05',
        'ultimo_reporte_grupo' => '2024-01-25',
        'ultimo_reporte_reunion' => '2024-01-30',
        'tipo_vinculacion_id' => 1,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 2,
      ]
    );
    if($usuario5->wasRecentlyCreated || $usuario5->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario5->roles()->detach($lider->id);
        $usuario5->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
        $usuario5->roles()->detach($alumno->id);
        $usuario5->roles()->attach($alumno->id, ['activo' => 0, 'dependiente' => 0, 'model_type' => 'App\Models\User']);
    }

    $usuario6 = \App\Models\User::withTrashed()->firstOrCreate(
      ['email' => 'softjuancarlos@gmail.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'email_verified_at' => '2016-01-01 05:00:01',
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'Juan',
        'segundo_nombre' => 'Carlos',
        'primer_apellido' => 'Velásquez Muñoz',
        'fecha_ingreso' => '2025-05-20',
        'genero' => 0,
        'tipo_identificacion_id' => 3,
        'identificacion' => '1112101544',
        'tipo_usuario_id' => 2,
        'foto' => 'asistente-6.png',
        'fecha_nacimiento' => '1989-08-05',
        'ultimo_reporte_grupo' => '2023-12-19',
        'ultimo_reporte_reunion' => '2023-12-31',
        'estado_civil_id' => 3,
        'tipo_vinculacion_id' => 2,
        'profesion_id' => 5,
        'nivel_academico_id' => 11,
        'estado_nivel_academico_id' => 2,
        'ocupacion_id' => 7,
        'telefono_fijo' => '435354',
        'telefono_otro' => '453868',
        'telefono_movil' => '3160498011',
        'tipo_vivienda_id' => 1,
        'direccion' => 'Calle falsa 123',
        'sector_economico_id' => 5,
        'tipo_sangre_id' => 1,
        'indicaciones_medicas' => 'Sanito gracias a DIOS',
        'informacion_opcional' => 'Epa ',
        'sede_id' => 2,
      ]
    );
    if($usuario6->wasRecentlyCreated || $usuario6->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario6->roles()->detach($lider->id); $usuario6->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
        $usuario6->roles()->detach($alumno->id); $usuario6->roles()->attach($alumno->id, ['activo' => 0, 'dependiente' => 0, 'model_type' => 'App\Models\User']);
        $usuario6->roles()->detach($maestro->id); $usuario6->roles()->attach($maestro->id, ['activo' => 0, 'dependiente' => 0, 'model_type' => 'App\Models\User']);
        $usuario6->roles()->detach($consejero->id); $usuario6->roles()->attach($consejero->id, ['activo' => 0, 'dependiente' => 0, 'model_type' => 'App\Models\User']);
    }

    $usuario7 = \App\Models\User::withTrashed()->firstOrCreate(
      ['email' => 'carlos@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'El dado de baja',
        'primer_apellido' => 'Velásquez',
        'genero' => 0,
        'identificacion' => '9652412552',
        'tipo_usuario_id' => 3,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '2001-08-05',
        'ultimo_reporte_grupo' => '2024-01-30',
        'ultimo_reporte_reunion' => '2024-01-30',
        'telefono_otro' => '3255141245',
        'deleted_at' => '2023-09-21 12:23:28',
        'tipo_vinculacion_id' => 4,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 2,
      ]
    );
    if($usuario7->wasRecentlyCreated || $usuario7->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario7->roles()->detach($oveja->id);
        $usuario7->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
    }

    $usuario8 = \App\Models\User::firstOrCreate(
      ['email' => 'usuarionoaprobado@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'No esta',
        'primer_apellido' => 'Aprobado',
        'genero' => 0,
        'identificacion' => '346437456',
        'tipo_usuario_id' => 3,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '2020-08-05',
        'ultimo_reporte_grupo' => '2024-01-30',
        'ultimo_reporte_reunion' => '2024-01-30',
        'esta_aprobado' => 0,
        'tipo_vinculacion_id' => 4,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 2,
      ]
    );
    if($usuario8->wasRecentlyCreated || $usuario8->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario8->roles()->detach($oveja->id);
        $usuario8->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
    }

    $usuario9 = \App\Models\User::firstOrCreate(
      ['email' => 'ovejaengrupo@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'Oveja',
        'primer_apellido' => 'Uno',
        'genero' => 0,
        'tipo_identificacion_id' => 3,
        'identificacion' => '934930454',
        'tipo_usuario_id' => 4,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '2015-08-05',
        'tipo_vinculacion_id' => 1,
        'profesion_id' => 4,
        'nivel_academico_id' => 10,
        'estado_nivel_academico_id' => 3,
        'ocupacion_id' => 5,
        'estado_civil_id' => 2,
        'telefono_fijo' => '25434538',
        'telefono_otro' => '737865786',
        'telefono_movil' => '47584538',
        'tipo_vivienda_id' => 2,
        'direccion' => 'Calle falsa 123',
        'sector_economico_id' => 8,
        'tipo_sangre_id' => 1,
        'indicaciones_medicas' => 'Todo bien',
        'informacion_opcional' => 'Que más quieres de mi',
        'sede_id' => 2,
        'email_verified_at' => '2016-01-01 05:00:01'
      ]
    );
    if($usuario9->wasRecentlyCreated || $usuario9->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario9->roles()->detach($oveja->id);
        $usuario9->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
    }

    $usuario10 = \App\Models\User::firstOrCreate(
      ['email' => 'ovejasingrupo@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'Oveja',
        'primer_apellido' => 'Sin grupo',
        'genero' => 1,
        'identificacion' => '73838287',
        'tipo_usuario_id' => 3,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '2018-08-05',
        'usuario_creacion_id' => 3,
        'estado_civil_id' => 1,
        'tipo_vinculacion_id' => 3,
        'profesion_id' => 4,
        'nivel_academico_id' => 10,
        'estado_nivel_academico_id' => 3,
        'ocupacion_id' => 5,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 33,
      ]
    );
    if($usuario10->wasRecentlyCreated || $usuario10->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario10->roles()->detach($oveja->id);
        $usuario10->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
    }

    $usuario11 = \App\Models\User::firstOrCreate(
      ['email' => 'softjuancarlos2@gmail.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'email_verified_at' => '2016-01-01 05:00:01',
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'Hija',
        'fecha_ingreso' => '2025-05-20',
        'primer_apellido' => 'De Juan',
        'genero' => 1,
        'tipo_identificacion_id' => 3,
        'identificacion' => '963852741',
        'tipo_usuario_id' => 3,
        'foto' => 'default-f.png',
        'fecha_nacimiento' => '2002-08-05',
        'tipo_vinculacion_id' => 1,
        'profesion_id' => 4,
        'nivel_academico_id' => 10,
        'estado_nivel_academico_id' => 3,
        'ocupacion_id' => 5,
        'estado_civil_id' => 2,
        'telefono_fijo' => '7267676764',
        'telefono_otro' => '7386728766',
        'telefono_movil' => '456435435',
        'tipo_vivienda_id' => 2,
        'direccion' => 'Calle falsa 456',
        'sector_economico_id' => 8,
        'tipo_sangre_id' => 1,
        'indicaciones_medicas' => 'esadf sdf dsf',
        'informacion_opcional' => 'sdfsdaf asdf sadf',
        'ultimo_reporte_grupo' => '2024-10-21',
        'sede_id' => 2,
      ]
    );
    if($usuario11->wasRecentlyCreated || $usuario11->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario11->roles()->detach($oveja->id);
        $usuario11->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
    }

    $usuario12 = \App\Models\User::firstOrCreate(
      ['email' => 'adminmanantial@redil.com'],
      [
        'pais_id' => 45,
        'password' => bcrypt('12345678'),
        'activo' => 0,
        'asistente_id' => 1,
        'primer_nombre' => 'admin',
        'primer_apellido' => 'manantial',
        'genero' => 0,
        'identificacion' => '1122334455',
        'tipo_usuario_id' => 6,
        'foto' => 'default-m.png',
        'fecha_nacimiento' => '2000-08-05',
        'esta_aprobado' => 1,
        'tipo_vinculacion_id' => 4,
        'email_verified_at' => '2016-01-01 05:00:01',
        'sede_id' => 2,
      ]
    );
    if($usuario12->wasRecentlyCreated || $usuario12->roles()->wherePivot('activo', 1)->count() == 0) {
        $usuario12->roles()->detach($superAdmin->id);
        $usuario12->roles()->attach($superAdmin->id, ['activo' => 1, 'dependiente' => 0, 'model_type' => 'App\Models\User']);
    }
  }
}
