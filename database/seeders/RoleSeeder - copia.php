<?php

namespace Database\Seeders;

use App\Models\Role as ModelsRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // RolAdmistrador
    $superAdmin = Role::create(['name' => 'Super Administrador', 'icono' => 'ti ti-key', 'dependiente' => false]);

    // creo relacion create_privilegios_tipo_grupo_rol
    ModelsRole::find(1)->privilegiosTiposGrupo()->attach(3, ['asignar_asistente' => false, 'desvincular_asistente' => true, 'asignar_encargado' => false, 'desvincular_encargado' => true]);
    ModelsRole::find(1)->privilegiosTiposGrupo()->attach(4, ['asignar_asistente' => true, 'desvincular_asistente' => false, 'asignar_encargado' => true, 'desvincular_encargado' => false]);

    $usuario1 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'admin@redil.com',
      'password' => bcrypt('12345678'),
      'activo' => 0,
      'asistente_id' => 1,
      'primer_nombre' => 'Admin',
      'primer_apellido' => 'Admin',
      'genero' => 0,
      'identificacion' => '111222333',
      'tipo_usuario_id' => 6,
      'foto' => 'default-m.png',
      'fecha_nacimiento' => '2000-08-05',
      'esta_aprobado' => 1,
      'tipo_vinculacion_id' => 4,
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario1->roles()->attach($superAdmin->id, ['activo' => 1, 'dependiente' => 0, 'model_type' => 'App\Models\User']);


    // RolPastor
    $pastor = Role::create(['name' => 'Pastor', 'icono' => 'ti ti-user-shield', 'dependiente' => true]);

    $usuario2 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'pastorprincipal@redil.com',
      'password' => bcrypt('12345678'),
      'activo' => 0,
      'asistente_id' => 1,
      'primer_nombre' => 'Hector Fabio',
      'primer_apellido' => 'Jaramillo',
      'genero' => 0,
      'identificacion' => '2384283482',
      'tipo_usuario_id' => 1,
      'foto' => 'default-m.png',
      'fecha_nacimiento' => '1977-08-05',
      'tipo_vinculacion_id' => 2,
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario2->roles()->attach($pastor->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    // Persona encargada de la iglesia con id 1
    $usuario2->iglesiaEncargada()->attach(2798);

    // RolLider
    $lider = Role::create(['name' => 'Lider', 'icono' => 'ti ti-user-star', 'dependiente' => true]);

    // creo relacion create_privilegios_tipo_grupo_rol
    ModelsRole::find(3)->privilegiosTiposGrupo()->attach(2, ['asignar_asistente' => true, 'desvincular_asistente' => true, 'asignar_encargado' => false, 'desvincular_encargado' => false]);

    $usuario3 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'lider_a@redil.com',
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
      'fecha_nacimiento' => '1985-08-05',
      'ultimo_reporte_grupo' => '2024-08-20',
      'ultimo_reporte_reunion' => '2024-08-20',
      'tipo_vinculacion_id' => 2,
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario3->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    $usuario4 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'lider_b@redil.com',
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
      'fecha_nacimiento' => '1980-08-05',
      'ultimo_reporte_grupo' => '2024-01-13',
      'ultimo_reporte_reunion' => '2024-08-20',
      'tipo_vinculacion_id' => 1,
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario4->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    $usuario5 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'lider_c@redil.com',
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
      'fecha_nacimiento' => '2010-08-05',
      'ultimo_reporte_grupo' => '2024-01-25',
      'ultimo_reporte_reunion' => '2024-01-30',
      'tipo_vinculacion_id' => 1,
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario5->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    $usuario6 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'lider_d@redil.com',
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
      'pais_id' => 45,
      'estado_civil_id' => 3,
      'telefono_fijo' => '435354',
      'telefono_otro' => '453868',
      'telefono_movil' => '3155552546',
      'tipo_vivienda_id' => 1,
      'direccion' => 'Calle falsa 123',
      'sector_economico_id' => 5,
      'tipo_sangre_id' => 1,
      'indicaciones_medicas' => 'Sanito gracias a DIOS',
      'informacion_opcional' => 'Epa ',
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario6->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    // RolOveja
    $oveja = Role::create(['name' => 'Oveja', 'icono' => 'ti ti-mood-heart', 'dependiente' => true]);

    $usuario7 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'carlos@redil.com',
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
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario7->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    $usuario8 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'usuarionoaprobado@redil.com',
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
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario8->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    $usuario9 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'ovejaengrupo@redil.com',
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
      'pais_id' => 3,
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
      'sede_id' => 1,
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario9->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    $usuario10 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'ovejasingrupo@redil.com',
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
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario10->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    $usuario11 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'softjuancarlos@gmail.com',
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
      'pais_id' => 3,
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
    ]);

    $usuario11->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    // Rol administrador para los design de manantial
    $usuario12 = \App\Models\User::create([
      'pais_id' => 45,
      'email' => 'adminmanantial@redil.com',
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
      'email_verified_at' => '2016-01-01 05:00:01'
    ]);

    $usuario12->roles()->attach($superAdmin->id, ['activo' => 1, 'dependiente' => 0, 'model_type' => 'App\Models\User']);


    // RolNuevo
    $nuevo = Role::create(['name' => 'Nuevo', 'icono' => 'ti ti-paper-bag', 'dependiente' => true]);

    // RolEmpleado
    $oveja = Role::create(['name' => 'Empleado', 'icono' => 'ti ti-brand-ctemplar', 'dependiente' => true]);

    // RolDesarrollador
    $desarrollador = Role::create(['name' => 'Desarrollador', 'icono' => 'ti ti-anchor', 'dependiente' => true]);

    // RolDP
    $pdp = Role::create(['name' => 'PDP', 'icono' => 'ti ti-paperclip', 'dependiente' => false]);

    // Personas
    Permission::create([
      'titulo' => 'lista_asistentes_todos',
      'descripcion' => '',
      'name' => 'personas.lista_asistentes_todos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_asistentes_solo_ministerio',
      'descripcion' => '',
      'name' => 'personas.lista_asistentes_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::create([
      'titulo' => 'item_asistentes',
      'descripcion' => '',
      'name' => 'personas.item_asistentes',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_nuevo_asistente',
      'descripcion' => '',
      'name' => 'personas.subitem_nuevo_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_asistentes',
      'descripcion' => '',
      'name' => 'personas.subitem_lista_asistentes',
    ])->syncRoles([$superAdmin]);



    /*Crear privilegios de ver secciones del perfil del usuario en su pestaña */

    Permission::create([
      'titulo' => 'ver_perfil_asistente',
      'descripcion' => '',
      'name' => 'personas.perfil.principal',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_familia',
      'descripcion' => '',
      'name' => 'personas.perfil.familia',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_congregacion',
      'descripcion' => '',
      'name' => 'personas.perfil.congregacion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_escuelas',
      'descripcion' => '',
      'name' => 'personas.perfil.escuelas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_finaciera',
      'descripcion' => '',
      'name' => 'personas.perfil.finaciera',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_hitos',
      'descripcion' => '',
      'name' => 'personas.perfil.hitos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.principal_autogestion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_familia_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.familia_autogestion',
    ]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_congregacion_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.congregacion_autogestion',
    ]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_escuelas_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.escuelas_autogestion',
    ]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_finaciera_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.finaciera_autogestion',
    ]);

    Permission::create([
      'titulo' => 'ver_perfil_asistente_hitos_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.hitos_autogestion',
    ]);


    /*fin Crear privilegios de ver secciones del perfil del usuario en su pestaña */



    Permission::create([
      'titulo' => 'opcion_modificar_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_modificar_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_cambiar_contrasena_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_cambiar_contrasena_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_descargar_qr',
      'descripcion' => '',
      'name' => 'personas.opcion_descargar_qr',
    ])->syncRoles([$superAdmin]);


    Permission::create([
      'titulo' => 'opcion_eliminar_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_eliminar_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_dar_de_baja_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_dar_de_baja_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_gentionar_relaciones_familiares',
      'descripcion' => '',
      'name' => 'personas.opcion_gentionar_relaciones_familiares',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_geoasignar_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_geoasignar_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_dar_de_alta_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_dar_de_alta_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_modificar_informacion_congregacional',
      'descripcion' => '',
      'name' => 'personas.opcion_modificar_informacion_congregacional',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'opcion_editar_autocontraseña',
      'descripcion' => '',
      'name' => 'personas.opcion_editar_autocontraseña',
    ]);

    Permission::create([
      'titulo' => 'panel_tipos_asistente',
      'descripcion' => '',
      'name' => 'personas.panel_tipos_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'panel_procesos_asistente',
      'descripcion' => '',
      'name' => 'personas.panel_procesos_asistente',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'panel_asignar_grupo_al_asistente',
      'descripcion' => '',
      'name' => 'personas.panel_asignar_grupo_al_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'pestana_actualizar_asistente',
      'descripcion' => '',
      'name' => 'personas.pestana_actualizar_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'pestana_informacion_congregacional',
      'descripcion' => '',
      'name' => 'personas.pestana_informacion_congregacional',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'autogestion_pestana_informacion_congregacional',
      'descripcion' => '',
      'name' => 'personas.autogestion_pestana_informacion_congregacional',
    ]);

    Permission::create([
      'titulo' => 'pestana_geoasignacion',
      'descripcion' => '',
      'name' => 'personas.pestana_geoasignacion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'auto_gestion_pestana_geoasignacion_grupo',
      'descripcion' => '',
      'name' => 'personas.auto_gestion_pestana_geoasignacion_grupo',
    ]);

    Permission::create([
      'titulo' => 'pestana_gentionar_relaciones_familiares',
      'descripcion' => '',
      'name' => 'personas.pestana_gentionar_relaciones_familiares',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'auto_gestion_pestana_gentionar_relaciones_familiares',
      'descripcion' => '',
      'name' => 'personas.auto_gestion_pestana_gentionar_relaciones_familiares',
    ]);

    Permission::create([
      'titulo' => 'ajax_obtiene_asistentes_solo_ministerio',
      'descripcion' => '',
      'name' => 'personas.ajax_obtiene_asistentes_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::create([
      'titulo' => 'mostrar_todos_los_grupos_en_geoasignacion',
      'descripcion' => '',
      'name' => 'personas.mostrar_todos_los_grupos_en_geoasignacion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_campo_reservado_visible',
      'descripcion' => '',
      'name' => 'personas.ver_campo_reservado_visible',
    ]);

    Permission::create([
      'titulo' => 'ver_panel_asignar_tipo_usuario',
      'descripcion' => '',
      'name' => 'personas.ver_panel_asignar_tipo_usuario',
    ])->syncRoles([$superAdmin]);


    Permission::create([
      'titulo' => 'ver_campo_informacion_opcional',
      'descripcion' => '',
      'name' => 'personas.ver_campo_informacion_opcional',
    ]);

    Permission::create([
      'titulo' => 'privilegio_crear_asistentes_aprobados',
      'descripcion' => '',
      'name' => 'personas.privilegio_crear_asistentes_aprobados',
    ]);

    Permission::create([
      'titulo' => 'privilegio_modificar_asistentes_desaprobados',
      'descripcion' => '',
      'name' => 'personas.privilegio_modificar_asistentes_desaprobados',
    ]);

    Permission::create([
      'titulo' => 'privilegio_actualizar_estado_aprobado_asistentes',
      'descripcion' => '',
      'name' => 'personas.privilegio_actualizar_estado_aprobado_asistentes',
    ]);

    Permission::create([
      'titulo' => 'subitem_lista_sin_aprobar',
      'descripcion' => '',
      'name' => 'personas.subitem_lista_sin_aprobar',
    ]);

    Permission::create([
      'titulo' => 'editar_tipos_asistente',
      'descripcion' => '',
      'name' => 'personas.editar_tipos_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'editar_procesos_asistente',
      'descripcion' => '',
      'name' => 'personas.editar_procesos_asistente',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'eliminar_asistentes_forzadamente',
      'descripcion' => '',
      'name' => 'personas.eliminar_asistentes_forzadamente',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'privilegio_gestionar_todos_los_pasos_de_crecimiento',
      'descripcion' => '',
      'name' => 'personas.privilegio_gestionar_todos_los_pasos_de_crecimiento',
    ])->syncRoles([$superAdmin]);

    /*Permission::create([
      'titulo' => 'visible_seccion_campos_extra',
      'descripcion' => '',
      'name' => 'personas.visible_seccion_campos_extra',
    ])->syncRoles([$superAdmin]);*/

    Permission::create([
      'titulo' => 'ver_perfil_propio',
      'descripcion' => '',
      'name' => 'personas.ver_perfil_propio',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_panel_pasos_crecimiento_perfil',
      'descripcion' => '',
      'name' => 'personas.ver_panel_pasos_crecimiento_perfil',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_panel_archivos',
      'descripcion' => '',
      'name' => 'personas.ver_panel_archivos',
    ])->syncRoles([$superAdmin]);

    // Grupos
    Permission::create([
      'titulo' => 'lista_grupos_todos',
      'descripcion' => '',
      'name' => 'grupos.lista_grupos_todos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_grupos_solo_ministerio',
      'descripcion' => '',
      'name' => 'grupos.lista_grupos_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::create([
      'titulo' => 'item_grupos',
      'descripcion' => '',
      'name' => 'grupos.item_grupos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'item_mi_grupo',
      'descripcion' => '',
      'name' => 'grupos.mi_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_grupos',
      'descripcion' => '',
      'name' => 'grupos.subitem_lista_grupos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_nuevo_grupo',
      'descripcion' => '',
      'name' => 'grupos.subitem_nuevo_grupo',
    ]);

    Permission::create([
      'titulo' => 'subitem_lista_informes_grupo',
      'descripcion' => '',
      'name' => 'grupos.subitem_lista_informes_grupo',
    ]);

    Permission::create([
      'titulo' => 'subitem_mapa_grupos',
      'descripcion' => '',
      'name' => 'grupos.subitem_mapa_grupos',
    ]);

    Permission::create([
      'titulo' => 'subitem_grafico_ministerio',
      'descripcion' => '',
      'name' => 'grupos.subitem_grafico_ministerio',
    ]);

    Permission::create([
      'titulo' => 'opcion_ver_perfil_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_ver_perfil_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'opcion_modificar_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_modificar_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'opcion_anadir_lideres_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_anadir_lideres_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'opcion_anadir_integrantes_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_anadir_integrantes_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'opcion_georreferencia_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_georreferencia_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'opcion_dar_de_baja_alta_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_dar_de_baja_alta_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminar_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_eliminar_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'pestana_actualizar_grupo',
      'descripcion' => '',
      'name' => 'grupos.pestana_actualizar_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'pestana_anadir_lideres_grupo',
      'descripcion' => '',
      'name' => 'grupos.pestana_anadir_lideres_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'pestana_anadir_integrantes_grupo',
      'descripcion' => '',
      'name' => 'grupos.pestana_anadir_integrantes_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'pestana_georreferencia_grupo',
      'descripcion' => '',
      'name' => 'grupos.pestana_georreferencia_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ajax_obtiene_grupos_solo_ministerio',
      'descripcion' => '',
      'name' => 'grupos.ajax_obtiene_grupos_solo_ministerio',
    ]);

    /* Permission::create([
      'titulo' => 'informe_asistencia_semanal_grupos',
      'descripcion' => '',
      'name' => 'grupos.informe_asistencia_semanal_grupos',
    ]);

    Permission::create([
      'titulo' => 'informe_asistencia_mensual_grupos',
      'descripcion' => '',
      'name' => 'grupos.informe_asistencia_mensual_grupos',
    ]);

    Permission::create([
      'titulo' => 'informe_generar_pdf_yumbo',
      'descripcion' => '',
      'name' => 'grupos.informe_generar_pdf_yumbo',
    ]);*/

    Permission::create([
      'titulo' => 'mapa_grupos_todos',
      'descripcion' => '',
      'name' => 'grupos.mapa_grupos_todos',
    ]);

    Permission::create([
      'titulo' => 'mapa_grupos_solo_ministerio',
      'descripcion' => '',
      'name' => 'grupos.mapa_grupos_solo_ministerio',
    ]);

    Permission::create([
      'titulo' => 'grafico_ministerio_todos',
      'descripcion' => '',
      'name' => 'grupos.grafico_ministerio_todos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'grafico_ministerio_solo_ministerio',
      'descripcion' => '',
      'name' => 'grupos.grafico_ministerio_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::create([
      'titulo' => 'mostar_modal_informe_asignacion_de_lideres',
      'descripcion' => '',
      'name' => 'grupos.mostar_modal_informe_asignacion_de_lideres',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'mostar_modal_informe_asignacion_de_asistentes',
      'descripcion' => '',
      'name' => 'grupos.mostar_modal_informe_asignacion_de_asistentes',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'mostar_modal_informe_desvinculacion_de_lideres',
      'descripcion' => '',
      'name' => 'grupos.mostar_modal_informe_desvinculacion_de_lideres',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'mostar_modal_informe_desvinculacion_de_asistentes',
      'descripcion' => '',
      'name' => 'grupos.mostar_modal_informe_desvinculacion_de_asistentes',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'privilegio_asignar_asistente_todo_tipo_asistente_a_un_grupo',
      'descripcion' => '',
      'name' => 'grupos.privilegio_asignar_asistente_todo_tipo_asistente_a_un_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'opcion_desvincular_asistentes_grupos',
      'descripcion' => '',
      'name' => 'grupos.opcion_desvincular_asistentes_grupos',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'subitem_excluir_asistentes_grupos',
      'descripcion' => '',
      'name' => 'grupos.subitem_excluir_asistentes_grupos',
    ]);

    Permission::create([
      'titulo' => 'opcion_excluir_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_excluir_grupo',
    ])->syncRoles([$superAdmin]);


    Permission::create([
      'titulo' => 'visible_seccion_campos_extra_grupo',
      'descripcion' => '',
      'name' => 'grupos.visible_seccion_campos_extra_grupo',
    ])->syncRoles([$superAdmin]);

    // Reporte Grupos
    Permission::create([
      'titulo' => 'lista_reportes_grupo_todos',
      'descripcion' => '',
      'name' => 'reportes_grupos.lista_reportes_grupo_todos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_reportes_grupo_solo_ministerio',
      'descripcion' => '',
      'name' => 'reportes_grupos.lista_reportes_grupo_solo_ministerio',
    ]);

    Permission::create([
      'titulo' => 'subitem_lista_reportes_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.subitem_lista_reportes_grupo',
    ]);

    Permission::create([
      'titulo' => 'subitem_nuevo_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.subitem_nuevo_reporte_grupo',
    ]);

    Permission::create([
      'titulo' => 'ver_boton_aprobar_desaprobar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.ver_boton_aprobar_desaprobar_reporte_grupo',
    ]);

    Permission::create([
      'titulo' => 'ver_opciones_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.ver_opciones_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_aprobar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_aprobar_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_desaprobar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_desaprobar_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_ver_perfil_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_ver_perfil_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_actualizar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_actualizar_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_eliminar_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'privilegio_reportar_grupo_cualquier_fecha',
      'descripcion' => '',
      'name' => 'reportes_grupos.privilegio_reportar_grupo_cualquier_fecha',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'panel_ingresos_en_lista_reportes_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.panel_ingresos_en_lista_reportes_grupo',
    ]);

    Permission::create([
      'titulo' => 'boton_configurar_semanas_informes_reportes_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.boton_configurar_semanas_informes_reportes_grupo',
    ]);

    Permission::create([
      'titulo' => 'cierre_caja_ingresos_reportes_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.cierre_caja_ingresos_reportes_grupo',
    ]);

    // Reuniones
    Permission::create([
      'titulo' => 'lista_reuniones_todas',
      'descripcion' => '',
      'name' => 'reuniones.lista_reuniones_todas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_reuniones_solo_ministerio',
      'descripcion' => '',
      'name' => 'reuniones.lista_reuniones_solo_ministerio',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'item_reuniones',
      'descripcion' => '',
      'name' => 'reuniones.item_reuniones',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_reuniones',
      'descripcion' => '',
      'name' => 'reuniones.subitem_lista_reuniones',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_nueva_reunion',
      'descripcion' => '',
      'name' => 'reuniones.subitem_nueva_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_informes_reunion',
      'descripcion' => '',
      'name' => 'reuniones.subitem_informes_reunion',
    ]);

    Permission::create([
      'titulo' => 'crea_reuniones_para_todas_las_sedes',
      'descripcion' => '',
      'name' => 'reuniones.crea_reuniones_para_todas_las_sedes',
    ]);

    Permission::create([
      'titulo' => 'opcion_ver_perfil_reunion',
      'descripcion' => '',
      'name' => 'reuniones.opcion_ver_perfil_reunion',
    ]);

    Permission::create([
      'titulo' => 'opcion_modificar_reunion',
      'descripcion' => '',
      'name' => 'reuniones.opcion_modificar_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_dar_de_baja_alta_reunion',
      'descripcion' => '',
      'name' => 'reuniones.opcion_dar_de_baja_alta_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminar_reunion',
      'descripcion' => '',
      'name' => 'reuniones.opcion_eliminar_reunion',
    ])->syncRoles([$superAdmin]);

    // Reporte Reuniones
    Permission::create([
      'titulo' => 'lista_reportes_reunion_todos',
      'descripcion' => '',
      'name' => 'reporte_reuniones.lista_reportes_reunion_todos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_reportes_reunion_solo_ministerio',
      'descripcion' => '',
      'name' => 'reporte_reuniones.lista_reportes_reunion_solo_ministerio',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'nuevo_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.nuevo_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_reportes_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.subitem_lista_reportes_reunion',
    ])->syncRoles([$superAdmin]);


    Permission::create([
      'titulo' => 'subitem_proximas_reuniones',
      'descripcion' => '',
      'name' => 'reporte_reuniones.subitem_proximas_reuniones',
    ])->syncRoles([$superAdmin]);


    /*Permission::create([
      'titulo' => 'ajax_obtiene_todas_las_reuniones_para_reportarlas',
      'descripcion' => '',
      'name' => 'reporte_reuniones.ajax_obtiene_todas_las_reuniones_para_reportarlas',
    ]);

    Permission::create([
      'titulo' => 'pestana_informacion_principal_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.pestana_informacion_principal_reporte_reunion',
    ]);

    Permission::create([
      'titulo' => 'pestana_anadir_asistentes_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.pestana_anadir_asistentes_reporte_reunion',
    ]);

    Permission::create([
      'titulo' => 'pestana_anadir_ingresos_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.pestana_anadir_ingresos_reporte_reunion',
    ]);

    Permission::create([
      'titulo' => 'pestana_anadir_servidores_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.pestana_anadir_servidores_reporte_reunion',
    ]);
    */
    Permission::create([
      'titulo' => 'opcion_ver_perfil_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_ver_perfil_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_modificar_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_modificar_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_anadir_asistentes_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_anadir_asistentes_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminar_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_eliminar_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_anadir_asistentes_reservas_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_anadir_asistentes_reservas_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_subitem_anadir_servidores_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_subitem_anadir_servidores_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_descargar_informe_servidores_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_descargar_informe_servidores_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_descargar_informe_reservas_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_descargar_informe_reservas_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_descargar_informe_asistencias_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_descargar_informe_asistencias_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_descargar_informe_visualizaciones_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_descargar_informe_visualizaciones_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ajax_obtiene_todos_los_asistentes_para_reportar_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.ajax_obtiene_todos_los_asistentes_para_reportar_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'privilegio_anadir_asistente_reporte_reunion_cualquier_fecha',
      'descripcion' => '',
      'name' => 'reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_iglesia_infantil',
      'descripcion' => '',
      'name' => 'reporte_reuniones.subitem_iglesia_infantil',
    ]);

    Permission::create([
      'titulo' => 'ver_conteo_preliminar_reuniones',
      'descripcion' => '',
      'name' => 'reporte_reuniones.ver_conteo_preliminar_reuniones',
    ]);

    // Sedes
    Permission::create([
      'titulo' => 'lista_sedes_todas',
      'descripcion' => '',
      'name' => 'sedes.lista_sedes_todas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_sedes_solo_ministerio',
      'descripcion' => '',
      'name' => 'sedes.lista_sedes_solo_ministerio',
    ]);

    Permission::create([
      'titulo' => 'item_sedes',
      'descripcion' => '',
      'name' => 'sedes.item_sedes',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_sedes',
      'descripcion' => '',
      'name' => 'sedes.subitem_lista_sedes',
    ]);

    Permission::create([
      'titulo' => 'subitem_nueva_sede',
      'descripcion' => '',
      'name' => 'sedes.subitem_nueva_sede',
    ]);

    Permission::create([
      'titulo' => 'opcion_ver_perfil_sede',
      'descripcion' => '',
      'name' => 'sedes.opcion_ver_perfil_sede',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_modificar_sede',
      'descripcion' => '',
      'name' => 'sedes.opcion_modificar_sede',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_dar_de_baja_sede',
      'descripcion' => '',
      'name' => 'sedes.opcion_dar_de_baja_sede',
    ]);

    Permission::create([
      'titulo' => 'opcion_eliminar_sede',
      'descripcion' => '',
      'name' => 'sedes.opcion_eliminar_sede',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'crear_banners_videos_sede',
      'descripcion' => '',
      'name' => 'sedes.crear_banners_videos_sede',
    ]);

    // Ingresos
    Permission::create([
      'titulo' => 'lista_ingresos_todos',
      'descripcion' => '',
      'name' => 'ingresos.lista_ingresos_todos',
    ]);

    Permission::create([
      'titulo' => 'lista_ingresos_solo_ministerio',
      'descripcion' => '',
      'name' => 'ingresos.lista_ingresos_solo_ministerio',
    ]);

    Permission::create([
      'titulo' => 'item_ingresos',
      'descripcion' => '',
      'name' => 'ingresos.item_ingresos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_informes_por_persona_ingresos',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informes_por_persona_ingresos',
    ]);

    Permission::create([
      'titulo' => 'subitem_informes_por_grupo_ingresos',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informes_por_grupo_ingresos',
    ]);

    Permission::create([
      'titulo' => 'subitem_informes_por_reunion_ingresos',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informes_por_reunion_ingresos',
    ]);

    Permission::create([
      'titulo' => 'subitem_informe_sumatoria_ingresos_reportes_grupo',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informe_sumatoria_ingresos_reportes_grupo',
    ]);

    Permission::create([
      'titulo' => 'opcion_ver_perfil_ingreso',
      'descripcion' => '',
      'name' => 'ingresos.opcion_ver_perfil_ingreso',
    ]);

    Permission::create([
      'titulo' => 'opcion_modificar_ingreso',
      'descripcion' => '',
      'name' => 'ingresos.opcion_modificar_ingreso',
    ]);

    Permission::create([
      'titulo' => 'opcion_eliminar_ingreso',
      'descripcion' => '',
      'name' => 'ingresos.opcion_eliminar_ingreso',
    ]);

    Permission::create([
      'titulo' => 'subitem_informes_donaciones_online',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informes_donaciones_online',
    ]);

    Permission::create([
      'titulo' => 'subitem_nueva_ofrenda',
      'descripcion' => '',
      'name' => 'ingresos.subitem_nueva_ofrenda',
    ]);

    Permission::create([
      'titulo' => 'privilegio_ver_todos_los_ingresos_informes_donaciones_online',
      'descripcion' => '',
      'name' => 'ingresos.privilegio_ver_todos_los_ingresos_informes_donaciones_online',
    ]);

    // Informes
    Permission::create([
      'titulo' => 'opcion_descargar_informe_excel_informe_ingresos_persona',
      'descripcion' => '',
      'name' => 'informes.opcion_descargar_informe_excel_informe_ingresos_persona',
    ]);

    Permission::create([
      'titulo' => 'opcion_descargar_informe_pdf_informe_ingresos_persona',
      'descripcion' => '',
      'name' => 'informes.opcion_descargar_informe_pdf_informe_ingresos_persona',
    ]);

    // Temas
    Permission::create([
      'titulo' => 'item_temas',
      'descripcion' => '',
      'name' => 'temas.item_temas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'item_nuevo_tema',
      'descripcion' => '',
      'name' => 'temas.item_nuevo_tema',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'item_listado_temas',
      'descripcion' => '',
      'name' => 'temas.item_listado_temas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_todos_los_temas',
      'descripcion' => '',
      'name' => 'temas.ver_todos_los_temas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_tema',
      'descripcion' => '',
      'name' => 'temas.ver_tema',
    ])->syncRoles([$superAdmin]);


    Permission::create([
      'titulo' => 'editar_tema',
      'descripcion' => '',
      'name' => 'temas.editar_tema',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'eliminar_tema',
      'descripcion' => '',
      'name' => 'temas.eliminar_tema',
    ])->syncRoles([$superAdmin]);

    // Iglesia
    Permission::create([
      'titulo' => 'ver_configuracion_iglesia',
      'descripcion' => '',
      'name' => 'iglesia.ver_configuracion_iglesia',
    ]);

    Permission::create([
      'titulo' => 'crear_banners_videos_iglesia',
      'descripcion' => '',
      'name' => 'iglesia.crear_banners_videos_iglesia',
    ]);

    Permission::create([
      'titulo' => 'logo_personalizado',
      'descripcion' => '',
      'name' => 'iglesia.logo_personalizado',
    ]);

    // Actividades
    Permission::create([
      'titulo' => 'item_actividades',
      'descripcion' => '',
      'name' => 'actividades.item_actividades',
    ])->syncRoles([$superAdmin, $lider]);



    Permission::create([
      'titulo' => 'subitem_nueva_actividad',
      'descripcion' => '',
      'name' => 'actividades.subitem_nueva_actividad',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_listado_actividad',
      'descripcion' => '',
      'name' => 'actividades.subitem_listado_actividad',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'subitem_historial_carga_de_achivo',
      'descripcion' => '',
      'name' => 'actividades.subitem_historial_carga_de_achivo',
    ]);

    Permission::create([
      'titulo' => 'subitem_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.subitem_informe_inscripciones',
    ]);

    Permission::create([
      'titulo' => 'subitem_informe_compras',
      'descripcion' => '',
      'name' => 'actividades.subitem_informe_compras',
    ]);

    Permission::create([
      'titulo' => 'subitem_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.subitem_informe_pagos',
    ]);

    Permission::create([
      'titulo' => 'pestana_actualizar_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_actualizar_actividad',
    ]);

    Permission::create([
      'titulo' => 'pestana_categorias_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_categorias_actividad',
    ]);

    Permission::create([
      'titulo' => 'pestana_categorias_escuelas_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_categorias_escuelas_actividad',
    ]);

    Permission::create([
      'titulo' => 'pestana_anadir_encargados_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_anadir_encargados_actividad',
    ]);

    Permission::create([
      'titulo' => 'pestana_anadir_asistencias_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_anadir_asistencias_actividad',
    ]);

    Permission::create([
      'titulo' => 'pestana_multimedia_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_multimedia_actividad',
    ]);

    Permission::create([
      'titulo' => 'ver_opciones_actividad',
      'descripcion' => '',
      'name' => 'actividades.ver_opciones_actividad',
    ]);

    Permission::create([
      'titulo' => 'opcion_actualizar_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_actualizar_actividad',
    ]);

    Permission::create([
      'titulo' => 'opcion_categorias_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_categorias_actividad',
    ]);

    Permission::create([
      'titulo' => 'opcion_anadir_encargados_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_anadir_encargados_actividad',
    ]);

    Permission::create([
      'titulo' => 'opcion_anadir_asistencias_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_anadir_asistencias_actividad',
    ]);

    Permission::create([
      'titulo' => 'opcion_multimediar_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_multimediar_actividad',
    ]);

    Permission::create([
      'titulo' => 'ver_boton_exportar_excel_informe_compras',
      'descripcion' => '',
      'name' => 'actividades.ver_boton_exportar_excel_informe_compras',
    ]);

    Permission::create([
      'titulo' => 'ver_filtros_informe_compras',
      'descripcion' => '',
      'name' => 'actividades.ver_filtros_informe_compras',
    ]);

    Permission::create([
      'titulo' => 'ver_columna_compra_informe_compra',
      'descripcion' => '',
      'name' => 'actividades.ver_columna_compra_informe_compra',
    ]);

    Permission::create([
      'titulo' => 'ver_boton_exportar_excel_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.ver_boton_exportar_excel_informe_pagos',
    ]);

    Permission::create([
      'titulo' => 'ver_filtros_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.ver_filtros_informe_pagos',
    ]);

    Permission::create([
      'titulo' => 'ver_columna_compra_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.ver_columna_compra_informe_pagos',
    ]);

    Permission::create([
      'titulo' => 'ver_boton_exportar_excel_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.ver_boton_exportar_excel_informe_inscripciones',
    ]);

    Permission::create([
      'titulo' => 'ver_filtros_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.ver_filtros_informe_inscripciones',
    ]);

    Permission::create([
      'titulo' => 'ver_columna_compra_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.ver_columna_compra_informe_inscripciones',
    ]);

    Permission::create([
      'titulo' => 'lista_asistentes_todos_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.lista_asistentes_todos_informe_inscripciones',
    ]);

    Permission::create([
      'titulo' => 'lista_asistentes_todos_informe_compras',
      'descripcion' => '',
      'name' => 'actividades.lista_asistentes_todos_informe_compras',
    ]);

    Permission::create([
      'titulo' => 'lista_asistentes_todos_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.lista_asistentes_todos_informe_pagos',
    ]);

    Permission::create([
      'titulo' => 'ver_boton_cargar_archivo_historial_carga_de_archivo',
      'descripcion' => '',
      'name' => 'actividades.ver_boton_cargar_archivo_historial_carga_de_archivo',
    ]);

    Permission::create([
      'titulo' => 'pestana_abonos_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_abonos_actividad',
    ]);

    Permission::create([
      'titulo' => 'pestana_novedades_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_novedades_actividad',
    ]);

    Permission::create([
      'titulo' => 'opcion_novedades_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_novedades_actividad',
    ]);

    Permission::create([
      'titulo' => 'opcion_abonos_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_abonos_actividad',
    ]);

    Permission::create([
      'titulo' => 'ver_todas_las_actividades',
      'descripcion' => '',
      'name' => 'actividades.ver_todas_las_actividades',
    ])->syncRoles([$superAdmin]);;

    Permission::create([
      'titulo' => 'sub_item_configuracion_general_web_checking',
      'descripcion' => '',
      'name' => 'actividades.sub_item_configuracion_general_web_checking',
    ]);

    Permission::create([
      'titulo' => 'ver_web_checkin',
      'descripcion' => '',
      'name' => 'actividades.ver_web_checkin',
    ]);

    Permission::create([
      'titulo' => 'pestana_anadir_servidores_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_anadir_servidores_actividad',
    ]);

    // Puntos de pago
    Permission::create([
      'titulo' => 'item_puntos_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.item_puntos_de_pago',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_lista_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'subitem_lista_cajas',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_lista_cajas',
    ]);

    Permission::create([
      'titulo' => 'subitem_nueva_persona_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_nueva_persona_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'subitem_compras_de_actividades_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_compras_de_actividades_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'subitem_donaciones_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_donaciones_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'ver_boton_nuevo_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.ver_boton_nuevo_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'opcion_modificar_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_modificar_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'opcion_eliminar_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_eliminar_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'opcion_dar_de_alta_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_dar_de_alta_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'opcion_dar_de_baja_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_dar_de_baja_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'ver_boton_nueva_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.ver_boton_nueva_caja',
    ]);

    Permission::create([
      'titulo' => 'opcion_historial_de_cierres_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_historial_de_cierres_caja',
    ]);

    Permission::create([
      'titulo' => 'opcion_registros_de_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_registros_de_caja',
    ]);

    Permission::create([
      'titulo' => 'opcion_dar_de_alta_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_dar_de_alta_caja',
    ]);

    Permission::create([
      'titulo' => 'opcion_cierre_de_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_cierre_de_caja',
    ]);

    Permission::create([
      'titulo' => 'opcion_desactivar_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_desactivar_caja',
    ]);

    Permission::create([
      'titulo' => 'opcion_activar_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_activar_caja',
    ]);

    Permission::create([
      'titulo' => 'opcion_dar_de_baja_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_dar_de_baja_caja',
    ]);

    Permission::create([
      'titulo' => 'subitem_abonos_de_actividades_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_abonos_de_actividades_punto_de_pago',
    ]);

    Permission::create([
      'titulo' => 'lista_cajas_todas',
      'descripcion' => '',
      'name' => 'puntos_de_pago.lista_cajas_todas',
    ]);

    Permission::create([
      'titulo' => 'opcion_anular_registro_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_anular_registro_caja',
    ]);

    // Informes
    Permission::create([
      'titulo' => 'item_informes',
      'descripcion' => '',
      'name' => 'informes.item_informes',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'privilegio_administrar_informes',
      'descripcion' => '',
      'name' => 'informes.privilegio_administrar_informes',
    ]);

    Permission::create([
      'titulo' => 'privilegio_configurar_semanas',
      'descripcion' => '',
      'name' => 'informes.privilegio_configurar_semanas',
    ]);


    /* Permission::create([
      'titulo' => 'subitem_informe_ministerios_generales',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_ministerios_generales',
    ]);*/

    /*Permission::create([
      'titulo' => 'subitem_informe_mima',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_mima',
    ]);*/

    /*Permission::create([
      'titulo' => 'subitem_informe_no_reportados',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_no_reportados',
    ]);*/

    /*Permission::create([
      'titulo' => 'subitem_informe_almah',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_almah',
    ]);*/

    /*Permission::create([
      'titulo' => 'subitem_informe_inasistencia_grupos',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_inasistencia_grupos',
    ]);*/

    /*Permission::create([
      'titulo' => 'seccion_informes_personalizados',
      'descripcion' => '',
      'name' => 'informes.seccion_informes_personalizados',
    ]);*/

    // Peticiones
    Permission::create([
      'titulo' => 'subitem_nueva_peticion',
      'descripcion' => '',
      'name' => 'peticiones.subitem_nueva_peticion',
    ]);

    Permission::create([
      'titulo' => 'item_peticiones',
      'descripcion' => '',
      'name' => 'peticiones.item_peticiones',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_mis_peticiones',
      'descripcion' => '',
      'name' => 'peticiones.subitem_mis_peticiones',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_panel_peticiones',
      'descripcion' => '',
      'name' => 'peticiones.subitem_panel_peticiones',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'subitem_gestionar_peticiones',
      'descripcion' => '',
      'name' => 'peticiones.subitem_gestionar_peticiones',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::create([
      'titulo' => 'lista_peticiones_todas',
      'descripcion' => '',
      'name' => 'peticiones.lista_peticiones_todas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_peticiones_solo_ministerio',
      'descripcion' => '',
      'name' => 'peticiones.lista_peticiones_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::create([
      'titulo' => 'opcion_eliminar',
      'descripcion' => '',
      'name' => 'peticiones.opcion_eliminar',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminacion_masiva',
      'descripcion' => '',
      'name' => 'peticiones.opcion_eliminacion_masiva',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'boton_descargar_excel',
      'descripcion' => '',
      'name' => 'peticiones.boton_descargar_excel',
    ])->syncRoles([$superAdmin]);

    // Padres
    Permission::create([
      'titulo' => 'item_padres',
      'descripcion' => '',
      'name' => 'padres.item_padres',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_hijos',
      'descripcion' => '',
      'name' => 'padres.subitem_lista_hijos',
    ]);

    Permission::create([
      'titulo' => 'subitem_nuevo_hijo',
      'descripcion' => '',
      'name' => 'padres.subitem_nuevo_hijo',
    ]);

    Permission::create([
      'titulo' => 'opcion_modificar_hijo',
      'descripcion' => '',
      'name' => 'padres.opcion_modificar_hijo',
    ]);

    /// roles para escuelas

    $alumno = Role::create(['name' => 'Alumno', 'icono' => 'ti ti-user-square-rounded', 'dependiente' => false]);
    $maestro = Role::create(['name' => 'Maestro', 'icono' => 'ti ti-user-square', 'dependiente' => false]);
    $coordinador = Role::create(['name' => 'Coordinador', 'icono' => 'ti ti ti-user-pentagon', 'dependiente' => false]);
    $administrador = Role::create(['name' => 'Administrativo', 'icono' => 'ti ti ti-user-pentagon', 'dependiente' => false]);



    $usuario5->roles()->attach($alumno->id, ['activo' => 0, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
    $usuario1->roles()->attach($administrador->id, ['activo' => 0, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    $usuario6->roles()->attach($alumno->id, ['activo' => 0, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
    $usuario6->roles()->attach($maestro->id, ['activo' => 0, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

    // Escuelas
    Permission::create([
      'titulo' => 'item_escuelas',
      'descripcion' => '',
      'name' => 'escuelas.item_escuelas',
    ])->syncRoles([$superAdmin, $maestro, $alumno, $lider]);


    // ITEM MENU ESCUELAS Y CONTENIDO INTERIOR

    Permission::create([
      'titulo' => 'opcion_eliminar_escuela',
      'descripcion' => '',
      'name' => 'escuelas.opcion_eliminar_escuela',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_actualizar_escuela',
      'descripcion' => '',
      'name' => 'escuelas.opcion_actualizar_escuela',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'panel_perfil_dashboard',
      'descripcion' => '',
      'name' => 'escuelas.panel_perfil_dashboard',
    ])->syncRoles([$administrador]);


    Permission::create([
      'titulo' => 'todas_las_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.todas_las_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_escuelas',
      'descripcion' => '',
      'name' => 'escuelas.subitem_lista_escuelas',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_nueva_escuela',
      'descripcion' => '',
      'name' => 'escuelas.subitem_nueva_escuela',
    ])->syncRoles([$administrador, $superAdmin]);
    Permission::create([
      'titulo' => 'opcion_anadir_materia_escuela',
      'descripcion' => '',
      'name' => 'escuelas.opcion_anadir_materia_escuela',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'listar_opciones_materia',
      'descripcion' => '',
      'name' => 'escuelas.listar_opciones_materia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_modificar_materia',
      'descripcion' => '',
      'name' => 'escuelas.opcion_modificar_materia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminar_materia',
      'descripcion' => '',
      'name' => 'escuelas.opcion_eliminar_materia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_activar_materia',
      'descripcion' => '',
      'name' => 'escuelas.opcion_activar_materia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'escuelas.reportar_asistencia_cualquier_dia',
      'descripcion' => '',
      'name' => 'escuelas.reportar_asistencia_cualquier_dia',
    ])->syncRoles([$administrador, $superAdmin]);

    //aulas

    Permission::create([
      'titulo' => 'item_aula',
      'descripcion' => '',
      'name' => 'escuelas.item_aula',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'gestionar_aulas',
      'descripcion' => '',
      'name' => 'escuelas.gestionar_aulas',
    ])->syncRoles([$administrador, $superAdmin]);


    /// horarios administrativos
    Permission::create([
      'titulo' => 'item_horarios',
      'descripcion' => '',
      'name' => 'escuelas.item_horarios',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'gestionar_horarios',
      'descripcion' => '',
      'name' => 'escuelas.gestionar_horarios',
    ])->syncRoles([$administrador, $superAdmin]);


    /// periodos

    Permission::create([
      'titulo' => 'item_periodos',
      'descripcion' => '',
      'name' => 'escuelas.item_periodos',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_periodos',
      'descripcion' => '',
      'name' => 'escuelas.subitem_lista_periodos',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminar_periodo',
      'descripcion' => '',
      'name' => 'escuelas.opcion_eliminar_periodo',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_modificar_periodo',
      'descripcion' => '',
      'name' => 'escuelas.opcion_modificar_periodo',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_finalizar_periodo',
      'descripcion' => '',
      'name' => 'escuelas.opcion_finalizar_periodo',
    ])->syncRoles([$administrador, $superAdmin]);

    ///CALIFICACIONES

    Permission::create([
      'titulo' => 'calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.calificaciones',
    ])->syncRoles([$administrador, $superAdmin, $lider]);

    Permission::create([
      'titulo' => 'subitem_gestionar_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_mis_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_mis_calificaciones',
    ])->syncRoles([$administrador, $superAdmin, $lider]);

    ///HOMOLGACIONES
    Permission::create([
      'titulo' => 'homologaciones',
      'descripcion' => '',
      'name' => 'escuelas.homologaciones',
    ])->syncRoles([$administrador, $superAdmin]);


    //MATRICULAS


    Permission::create([
      'titulo' => 'item_matriculas',
      'descripcion' => '',
      'name' => 'escuelas.item_matriculas',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_gestionar_matriculas',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_matriculas',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_gestionar_traslados',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_traslados',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminar_matricula',
      'descripcion' => '',
      'name' => 'escuelas.opcion_eliminar_matricula',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_historial_matriculas',
      'descripcion' => '',
      'name' => 'escuelas.subitem_historial_matriculas',
    ])->syncRoles([$administrador, $superAdmin]);


    // MAESTROS

    Permission::create([
      'titulo' => 'item_maestros',
      'descripcion' => '',
      'name' => 'escuelas.item_maestros',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::create([
      'titulo' => 'opcion_gestionar_maestro',
      'descripcion' => '',
      'name' => 'escuelas.opcion_gestionar_maestro',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_crear_maestro',
      'descripcion' => '',
      'name' => 'escuelas.opcion_crear_maestro',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'opcion_ver_perfil_maestro',
      'descripcion' => '',
      'name' => 'escuelas.opcion_ver_perfil_maestro',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::create([
      'titulo' => 'subitem_lista_maestros',
      'descripcion' => '',
      'name' => 'escuelas.subitem_lista_maestros',
    ])->syncRoles([$administrador, $superAdmin]);

    // BANNERS

    Permission::create([
      'titulo' => 'item_banners',
      'descripcion' => '',
      'name' => 'escuelas.item_banners',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::create([
      'titulo' => 'subitem_gestionar_banners',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_banners',
    ])->syncRoles([$administrador, $superAdmin]);

    // INFORMES

    Permission::create([
      'titulo' => 'item_informes_escuelas',
      'descripcion' => '',
      'name' => 'escuelas.item_informes_escuelas',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::create([
      'titulo' => 'subitem_gestionar_asistencias',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_asistencias',
    ])->syncRoles([$administrador, $superAdmin]);






    Permission::create([
      'titulo' => 'icono',
      'descripcion' => '',
      'name' => 'escuelas.icono',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::create([
      'titulo' => 'item_gestionar_banners',
      'descripcion' => '',
      'name' => 'escuelas.item_gestionar_banners',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::create([
      'titulo' => 'es_maestro',
      'descripcion' => '',
      'name' => 'escuelas.es_maestro',
    ])->syncRoles([$maestro]);;

    Permission::create([
      'titulo' => 'es_estudiante',
      'descripcion' => '',
      'name' => 'escuelas.es_estudiante',
    ])->syncRoles([$alumno, $lider, $pastor, $oveja, $nuevo]);

    Permission::create([
      'titulo' => 'es_administrativo',
      'descripcion' => '',
      'name' => 'escuelas.es_administrativo',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::create([
      'titulo' => 'subitem_recursos_generales',
      'descripcion' => '',
      'name' => 'escuelas.subitem_recursos_generales',
    ])->syncRoles([$administrador, $superAdmin, $alumno, $lider, $pastor, $oveja, $nuevo]);

    Permission::create([
      'titulo' => 'gestionar_recursos_generales',
      'descripcion' => '',
      'name' => 'escuelas.gestionar_recursos_generales',
    ])->syncRoles([$administrador, $superAdmin,]);

    Permission::create([
      'titulo' => 'mis_recursos_generales',
      'descripcion' => '',
      'name' => 'escuelas.mis_recursos_generales',
    ])->syncRoles([$alumno, $lider, $pastor, $oveja, $nuevo]);




    Permission::create([
      'titulo' => 'subitem_homologaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_homologaciones',
    ])->syncRoles([$administrador, $superAdmin]);




    Permission::create([
      'titulo' => 'item_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.item_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_historial_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_historial_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'pestana_maestro',
      'descripcion' => '',
      'name' => 'escuelas.pestana_maestro',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'pestana_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.pestana_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_gestionar_materia_como_un_maestro',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_materia_como_un_maestro',
    ])->syncRoles([$administrador, $superAdmin]);




    Permission::create([
      'titulo' => 'auto_matricula',
      'descripcion' => '',
      'name' => 'escuelas.auto_matricula',
    ])->syncRoles([$alumno]);



    Permission::create([
      'titulo' => 'subitem_historial_homologaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_historial_homologaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_nueva_homologacion',
      'descripcion' => '',
      'name' => 'escuelas.subitem_nueva_homologacion',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_mis_homologaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_mis_homologaciones',
    ])->syncRoles([$oveja]);


    Permission::create([
      'titulo' => 'sub_item_bitacora_matriculas',
      'descripcion' => '',
      'name' => 'escuelas.sub_item_bitacora_matriculas',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::create([
      'titulo' => 'opcion_gestionar_pensum',
      'descripcion' => '',
      'name' => 'escuelas.opcion_gestionar_pensum',
    ])->syncRoles([$administrador, $superAdmin]);





    Permission::create([
      'titulo' => 'item_informes',
      'descripcion' => '',
      'name' => 'escuelas.item_informes',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'item_bitacoras',
      'descripcion' => '',
      'name' => 'escuelas.item_bitacoras',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'sub_bitacoras_item',
      'descripcion' => '',
      'name' => 'escuelas.sub_bitacoras_item',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'sub_bitacoras_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.sub_bitacoras_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'sub_bitacoras_asistencias',
      'descripcion' => '',
      'name' => 'escuelas.sub_bitacoras_asistencias',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'sub_bitacoras_gestion_asistencia',
      'descripcion' => '',
      'name' => 'escuelas.sub_bitacoras_gestion_asistencia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'item_certificados',
      'descripcion' => '',
      'name' => 'escuelas.item_certificados',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_gestionar_diplomas',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_diplomas',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_mis_certificados',
      'descripcion' => '',
      'name' => 'escuelas.subitem_mis_certificados',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'subitem_gestionar_certificados',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_certificados',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'sub_item_nuevo_recurso_escuela',
      'descripcion' => '',
      'name' => 'escuelas.sub_item_nuevo_recurso_escuela',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'sub_item_mis_recursos',
      'descripcion' => '',
      'name' => 'escuelas.sub_item_mis_recursos',
    ])->syncRoles([$alumno]);

    Permission::create([
      'titulo' => 'item_recursos',
      'descripcion' => '',
      'name' => 'escuelas.item_recursos',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::create([
      'titulo' => 'habilitar_cierrar_corte',
      'descripcion' => '',
      'name' => 'escuelas.habilitar_cierrar_corte',
    ])->syncRoles([$administrador, $superAdmin]);




    /// FIN ESCUELAS

    // Familiar
    Permission::create([
      'titulo' => 'item_familiar',
      'descripcion' => '',
      'name' => 'familiar.item_familiar',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_gentionar_relaciones',
      'descripcion' => '',
      'name' => 'familiar.subitem_gentionar_relaciones',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_informes',
      'descripcion' => '',
      'name' => 'familiar.subitem_informes',
    ])->syncRoles([$superAdmin]);


    Permission::create([
      'titulo' => 'opcion_modificar_relacion_familiar',
      'descripcion' => '',
      'name' => 'familiar.opcion_modificar_relacion_familiar',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'opcion_eliminar_relacion_familiar',
      'descripcion' => '',
      'name' => 'familiar.opcion_eliminar_relacion_familiar',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'ver_boton_nueva_relacion_familiar',
      'descripcion' => '',
      'name' => 'familiar.ver_boton_nueva_relacion_familiar',
    ])->syncRoles([$superAdmin]);

    //Dashboard
    Permission::create([
      'titulo' => 'dashboard_mostrar_calendario',
      'descripcion' => '',
      'name' => 'dashboard.dashboard_mostrar_calendario',
    ]);

    Permission::create([
      'titulo' => 'ver_banners_videos_todos',
      'descripcion' => '',
      'name' => 'dashboard.ver_banners_videos_todos',
    ]);

    Permission::create([
      'titulo' => 'ver_video_software_redil_defecto',
      'descripcion' => '',
      'name' => 'dashboard.ver_video_software_redil_defecto',
    ]);

    // Administracion
    Permission::create([
      'titulo' => 'ver_cronograma_desarrollo',
      'descripcion' => '',
      'name' => 'administracion.ver_cronograma_desarrollo',
    ]);

    Permission::create([
      'titulo' => 'editar_item_etapas_crecimiento',
      'descripcion' => '',
      'name' => 'administracion.editar_item_etapas_crecimiento',
    ]);

    Permission::create([
      'titulo' => 'ver_item_etapas_crecimiento',
      'descripcion' => '',
      'name' => 'administracion.ver_item_etapas_crecimiento',
    ]);

    // rueda de la vida
    Permission::create([
      'titulo' => 'item_rueda_de_la_vida',
      'descripcion' => '',
      'name' => 'rueda_de_la_vida.item_rueda_de_la_vida',
    ])->syncRoles([$superAdmin]);

    // finanzas
    Permission::create([
      'titulo' => 'item_finanzas',
      'descripcion' => '',
      'name' => 'finanzas.item_finanzas',
    ])->syncRoles([$superAdmin]);

    // configuraciones
    Permission::create([
      'titulo' => 'item_configuraciones',
      'descripcion' => '',
      'name' => 'configuraciones.item_configuraciones',
    ])->syncRoles([$superAdmin]);


    Permission::create([
      'titulo' => 'subitem_general',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_general',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_roles',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_roles',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_roles',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_zonas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_plantilla',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_plantilla',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_tarea_consolidacion',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tarea_consolidacion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_pasos_de_crecimiento',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_pasos_de_crecimiento',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_tipos_de_grupos',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tipos_de_grupos',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_tipo_de_usuarios',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tipo_de_usuarios',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_filtro_de_consolidacion',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_filtro_de_consolidacion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_rangos_de_edad',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_rangos_de_edad',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_de_reproduccion',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_lista_de_reproduccion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_formulario_usuarios',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_formulario_usuarios',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_gestionar_formulario_usuarios',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_gestionar_formulario_usuarios',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_tipos_de_ofrendas',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tipos_de_ofrendas',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_gestionar_campos_formulario_usuario',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_gestionar_campos_formulario_usuario',
    ])->syncRoles([$superAdmin]);


    // Iglesia
    Permission::create([
      'titulo' => 'item_iglesia',
      'descripcion' => '',
      'name' => 'iglesia.item_iglesia',
    ])->syncRoles([$superAdmin]);


    // consolidación

    Permission::create([
      'titulo' => 'item_consolidacion',
      'descripcion' => '',
      'name' => 'consolidacion.item_consolidacion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'subitem_lista_consolidacion',
      'descripcion' => '',
      'name' => 'consolidacion.subitem_lista_consolidacion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_toda_consolidacion',
      'descripcion' => '',
      'name' => 'consolidacion.lista_toda_consolidacion',
    ])->syncRoles([$superAdmin]);

    Permission::create([
      'titulo' => 'lista_consolidacion_solo_ministerio',
      'descripcion' => '',
      'name' => 'consolidacion.lista_consolidacion_solo_ministerio',
    ]);
  }
}
