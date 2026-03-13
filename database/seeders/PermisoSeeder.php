<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermisoSeeder extends Seeder
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
    $nuevo = Role::findByName('Nuevo');
    $alumno = Role::findByName('Alumno');
    $maestro = Role::findByName('Maestro');
    $administrador = Role::findByName('Administrativo');
    $consejero = Role::findByName('Consejero');
    $consolidadorMedellin = Role::findByName('Consolidador Medellin');
    $consolidadorBogota = Role::findByName('Consolidador Bogota');
    $cajero = Role::findByName('Cajero PDP');
    // $coordinador = Role::findByName('Coordinador');

    // Personas
    Permission::firstOrCreate([
      'titulo' => 'lista_asistentes_todos',
      'descripcion' => '',
      'name' => 'personas.lista_asistentes_todos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'lista_asistentes_solo_ministerio',
      'descripcion' => '',
      'name' => 'personas.lista_asistentes_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::firstOrCreate([
      'titulo' => 'item_asistentes',
      'descripcion' => '',
      'name' => 'personas.item_asistentes',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nuevo_asistente',
      'descripcion' => '',
      'name' => 'personas.subitem_nuevo_asistente',
    ])->syncRoles([$superAdmin, $nuevo]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_asistentes',
      'descripcion' => '',
      'name' => 'personas.subitem_lista_asistentes',
    ])->syncRoles([$superAdmin]);



    /*Crear privilegios de ver secciones del perfil del usuario en su pestaña */

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente',
      'descripcion' => '',
      'name' => 'personas.perfil.principal',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_familia',
      'descripcion' => '',
      'name' => 'personas.perfil.familia',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_congregacion',
      'descripcion' => '',
      'name' => 'personas.perfil.congregacion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_escuelas',
      'descripcion' => '',
      'name' => 'personas.perfil.escuelas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_finaciera',
      'descripcion' => '',
      'name' => 'personas.perfil.finaciera',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_hitos',
      'descripcion' => '',
      'name' => 'personas.perfil.hitos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.principal_autogestion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_familia_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.familia_autogestion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_congregacion_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.congregacion_autogestion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_escuelas_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.escuelas_autogestion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_finaciera_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.finaciera_autogestion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_asistente_hitos_autogestion',
      'descripcion' => '',
      'name' => 'personas.perfil.hitos_autogestion',
    ]);


    /*fin Crear privilegios de ver secciones del perfil del usuario en su pestaña */



    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_modificar_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_cambiar_contrasena_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_cambiar_contrasena_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_descargar_qr',
      'descripcion' => '',
      'name' => 'personas.opcion_descargar_qr',
    ])->syncRoles([$superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_eliminar_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_baja_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_dar_de_baja_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_gentionar_relaciones_familiares',
      'descripcion' => '',
      'name' => 'personas.opcion_gentionar_relaciones_familiares',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_geoasignar_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_geoasignar_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_alta_asistente',
      'descripcion' => '',
      'name' => 'personas.opcion_dar_de_alta_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_informacion_congregacional',
      'descripcion' => '',
      'name' => 'personas.opcion_modificar_informacion_congregacional',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_editar_autocontraseña',
      'descripcion' => '',
      'name' => 'personas.opcion_editar_autocontraseña',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'panel_tipos_asistente',
      'descripcion' => '',
      'name' => 'personas.panel_tipos_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'panel_procesos_asistente',
      'descripcion' => '',
      'name' => 'personas.panel_procesos_asistente',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'panel_asignar_grupo_al_asistente',
      'descripcion' => '',
      'name' => 'personas.panel_asignar_grupo_al_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_actualizar_asistente',
      'descripcion' => '',
      'name' => 'personas.pestana_actualizar_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_informacion_congregacional',
      'descripcion' => '',
      'name' => 'personas.pestana_informacion_congregacional',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'autogestion_pestana_informacion_congregacional',
      'descripcion' => '',
      'name' => 'personas.autogestion_pestana_informacion_congregacional',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_geoasignacion',
      'descripcion' => '',
      'name' => 'personas.pestana_geoasignacion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'auto_gestion_pestana_geoasignacion_grupo',
      'descripcion' => '',
      'name' => 'personas.auto_gestion_pestana_geoasignacion_grupo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_gentionar_relaciones_familiares',
      'descripcion' => '',
      'name' => 'personas.pestana_gentionar_relaciones_familiares',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'auto_gestion_pestana_gentionar_relaciones_familiares',
      'descripcion' => '',
      'name' => 'personas.auto_gestion_pestana_gentionar_relaciones_familiares',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ajax_obtiene_asistentes_solo_ministerio',
      'descripcion' => '',
      'name' => 'personas.ajax_obtiene_asistentes_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::firstOrCreate([
      'titulo' => 'mostrar_todos_los_grupos_en_geoasignacion',
      'descripcion' => '',
      'name' => 'personas.mostrar_todos_los_grupos_en_geoasignacion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_campo_reservado_visible',
      'descripcion' => '',
      'name' => 'personas.ver_campo_reservado_visible',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_panel_asignar_tipo_usuario',
      'descripcion' => '',
      'name' => 'personas.ver_panel_asignar_tipo_usuario',
    ])->syncRoles([$superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'ver_campo_informacion_opcional',
      'descripcion' => '',
      'name' => 'personas.ver_campo_informacion_opcional',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_crear_asistentes_aprobados',
      'descripcion' => '',
      'name' => 'personas.privilegio_crear_asistentes_aprobados',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_modificar_asistentes_desaprobados',
      'descripcion' => '',
      'name' => 'personas.privilegio_modificar_asistentes_desaprobados',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_actualizar_estado_aprobado_asistentes',
      'descripcion' => '',
      'name' => 'personas.privilegio_actualizar_estado_aprobado_asistentes',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_sin_aprobar',
      'descripcion' => '',
      'name' => 'personas.subitem_lista_sin_aprobar',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'editar_tipos_asistente',
      'descripcion' => '',
      'name' => 'personas.editar_tipos_asistente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'editar_procesos_asistente',
      'descripcion' => '',
      'name' => 'personas.editar_procesos_asistente',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'eliminar_asistentes_forzadamente',
      'descripcion' => '',
      'name' => 'personas.eliminar_asistentes_forzadamente',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_gestionar_todos_los_pasos_de_crecimiento',
      'descripcion' => '',
      'name' => 'personas.privilegio_gestionar_todos_los_pasos_de_crecimiento',
    ])->syncRoles([$superAdmin]);

    /*Permission::firstOrCreate([
      'titulo' => 'visible_seccion_campos_extra',
      'descripcion' => '',
      'name' => 'personas.visible_seccion_campos_extra',
    ])->syncRoles([$superAdmin]);*/

    Permission::firstOrCreate([
      'titulo' => 'ver_perfil_propio',
      'descripcion' => '',
      'name' => 'personas.ver_perfil_propio',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_panel_pasos_crecimiento_perfil',
      'descripcion' => '',
      'name' => 'personas.ver_panel_pasos_crecimiento_perfil',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_panel_archivos',
      'descripcion' => '',
      'name' => 'personas.ver_panel_archivos',
    ])->syncRoles([$superAdmin]);

    // Grupos
    Permission::firstOrCreate([
      'titulo' => 'lista_grupos_todos',
      'descripcion' => '',
      'name' => 'grupos.lista_grupos_todos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'lista_grupos_solo_ministerio',
      'descripcion' => '',
      'name' => 'grupos.lista_grupos_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::firstOrCreate([
      'titulo' => 'item_grupos',
      'descripcion' => '',
      'name' => 'grupos.item_grupos',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'item_mi_grupo',
      'descripcion' => '',
      'name' => 'grupos.mi_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_grupos',
      'descripcion' => '',
      'name' => 'grupos.subitem_lista_grupos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nuevo_grupo',
      'descripcion' => '',
      'name' => 'grupos.subitem_nuevo_grupo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_informes_grupo',
      'descripcion' => '',
      'name' => 'grupos.subitem_lista_informes_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_mapa_grupos',
      'descripcion' => '',
      'name' => 'grupos.subitem_mapa_grupos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_grafico_ministerio',
      'descripcion' => '',
      'name' => 'grupos.subitem_grafico_ministerio',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_dashboard',
      'descripcion' => '',
      'name' => 'grupos.subitem_dashboard',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_perfil_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_ver_perfil_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_modificar_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_anadir_lideres_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_anadir_lideres_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_anadir_integrantes_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_anadir_integrantes_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_georreferencia_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_georreferencia_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_baja_alta_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_dar_de_baja_alta_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_eliminar_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_actualizar_grupo',
      'descripcion' => '',
      'name' => 'grupos.pestana_actualizar_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_anadir_lideres_grupo',
      'descripcion' => '',
      'name' => 'grupos.pestana_anadir_lideres_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_anadir_integrantes_grupo',
      'descripcion' => '',
      'name' => 'grupos.pestana_anadir_integrantes_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_georreferencia_grupo',
      'descripcion' => '',
      'name' => 'grupos.pestana_georreferencia_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ajax_obtiene_grupos_solo_ministerio',
      'descripcion' => '',
      'name' => 'grupos.ajax_obtiene_grupos_solo_ministerio',
    ])->syncRoles([$lider]);

    /* Permission::firstOrCreate([
      'titulo' => 'informe_asistencia_semanal_grupos',
      'descripcion' => '',
      'name' => 'grupos.informe_asistencia_semanal_grupos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'informe_asistencia_mensual_grupos',
      'descripcion' => '',
      'name' => 'grupos.informe_asistencia_mensual_grupos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'informe_generar_pdf_yumbo',
      'descripcion' => '',
      'name' => 'grupos.informe_generar_pdf_yumbo',
    ]);*/

    Permission::firstOrCreate([
      'titulo' => 'mapa_grupos_todos',
      'descripcion' => '',
      'name' => 'grupos.mapa_grupos_todos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'mapa_grupos_solo_ministerio',
      'descripcion' => '',
      'name' => 'grupos.mapa_grupos_solo_ministerio',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'grafico_ministerio_todos',
      'descripcion' => '',
      'name' => 'grupos.grafico_ministerio_todos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'grafico_ministerio_solo_ministerio',
      'descripcion' => '',
      'name' => 'grupos.grafico_ministerio_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::firstOrCreate([
      'titulo' => 'mostar_modal_informe_asignacion_de_lideres',
      'descripcion' => '',
      'name' => 'grupos.mostar_modal_informe_asignacion_de_lideres',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'mostar_modal_informe_asignacion_de_asistentes',
      'descripcion' => '',
      'name' => 'grupos.mostar_modal_informe_asignacion_de_asistentes',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'mostar_modal_informe_desvinculacion_de_lideres',
      'descripcion' => '',
      'name' => 'grupos.mostar_modal_informe_desvinculacion_de_lideres',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'mostar_modal_informe_desvinculacion_de_asistentes',
      'descripcion' => '',
      'name' => 'grupos.mostar_modal_informe_desvinculacion_de_asistentes',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_asignar_asistente_todo_tipo_asistente_a_un_grupo',
      'descripcion' => '',
      'name' => 'grupos.privilegio_asignar_asistente_todo_tipo_asistente_a_un_grupo',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_desvincular_asistentes_grupos',
      'descripcion' => '',
      'name' => 'grupos.opcion_desvincular_asistentes_grupos',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_excluir_asistentes_grupos',
      'descripcion' => '',
      'name' => 'grupos.subitem_excluir_asistentes_grupos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_excluir_grupo',
      'descripcion' => '',
      'name' => 'grupos.opcion_excluir_grupo',
    ])->syncRoles([$superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'visible_seccion_campos_extra_grupo',
      'descripcion' => '',
      'name' => 'grupos.visible_seccion_campos_extra_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_informes_evidencia',
      'descripcion' => '',
      'name' => 'grupos.opcion_ver_informes_evidencia',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'boton_crear_informe_evidencia',
      'descripcion' => '',
      'name' => 'grupos.boton_crear_informe_evidencia',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_editar_informe_evidencia',
      'descripcion' => '',
      'name' => 'grupos.opcion_editar_informe_evidencia',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_informe_evidencia',
      'descripcion' => '',
      'name' => 'grupos.opcion_eliminar_informe_evidencia',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_informe_evidencia',
      'descripcion' => '',
      'name' => 'grupos.opcion_ver_informe_evidencia',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informe_administrativo_de_evidencia_de_grupos',
      'descripcion' => '',
      'name' => 'grupos.subitem_informe_administrativo_de_evidencia_de_grupos',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_descargar_informe_evidencia',
      'descripcion' => '',
      'name' => 'grupos.opcion_descargar_informe_evidencia',
    ])->syncRoles([$superAdmin]);


    // Reporte Grupos
    Permission::firstOrCreate([
      'titulo' => 'lista_reportes_grupo_todos',
      'descripcion' => '',
      'name' => 'reportes_grupos.lista_reportes_grupo_todos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'lista_reportes_grupo_solo_ministerio',
      'descripcion' => '',
      'name' => 'reportes_grupos.lista_reportes_grupo_solo_ministerio',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_reportes_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.subitem_lista_reportes_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nuevo_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.subitem_nuevo_reporte_grupo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_boton_aprobar_desaprobar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.ver_boton_aprobar_desaprobar_reporte_grupo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_opciones_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.ver_opciones_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_aprobar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_aprobar_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_desaprobar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_desaprobar_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_perfil_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_ver_perfil_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_actualizar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_actualizar_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_reporte_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.opcion_eliminar_reporte_grupo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_reportar_grupo_cualquier_fecha',
      'descripcion' => '',
      'name' => 'reportes_grupos.privilegio_reportar_grupo_cualquier_fecha',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'panel_ingresos_en_lista_reportes_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.panel_ingresos_en_lista_reportes_grupo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'boton_configurar_semanas_informes_reportes_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.boton_configurar_semanas_informes_reportes_grupo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'cierre_caja_ingresos_reportes_grupo',
      'descripcion' => '',
      'name' => 'reportes_grupos.cierre_caja_ingresos_reportes_grupo',
    ]);

    // Reuniones
    Permission::firstOrCreate([
      'titulo' => 'lista_reuniones_todas',
      'descripcion' => '',
      'name' => 'reuniones.lista_reuniones_todas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'lista_reuniones_solo_ministerio',
      'descripcion' => '',
      'name' => 'reuniones.lista_reuniones_solo_ministerio',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'item_reuniones',
      'descripcion' => '',
      'name' => 'reuniones.item_reuniones',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_reuniones',
      'descripcion' => '',
      'name' => 'reuniones.subitem_lista_reuniones',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_reunion',
      'descripcion' => '',
      'name' => 'reuniones.subitem_nueva_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informes_reunion',
      'descripcion' => '',
      'name' => 'reuniones.subitem_informes_reunion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'crea_reuniones_para_todas_las_sedes',
      'descripcion' => '',
      'name' => 'reuniones.crea_reuniones_para_todas_las_sedes',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_perfil_reunion',
      'descripcion' => '',
      'name' => 'reuniones.opcion_ver_perfil_reunion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_reunion',
      'descripcion' => '',
      'name' => 'reuniones.opcion_modificar_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_baja_alta_reunion',
      'descripcion' => '',
      'name' => 'reuniones.opcion_dar_de_baja_alta_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_reunion',
      'descripcion' => '',
      'name' => 'reuniones.opcion_eliminar_reunion',
    ])->syncRoles([$superAdmin]);

    // Reporte Reuniones
    Permission::firstOrCreate([
      'titulo' => 'lista_reportes_reunion_todos',
      'descripcion' => '',
      'name' => 'reporte_reuniones.lista_reportes_reunion_todos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'lista_reportes_reunion_solo_ministerio',
      'descripcion' => '',
      'name' => 'reporte_reuniones.lista_reportes_reunion_solo_ministerio',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'nuevo_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.nuevo_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_reportes_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.subitem_lista_reportes_reunion',
    ])->syncRoles([$superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'subitem_proximas_reuniones',
      'descripcion' => '',
      'name' => 'reporte_reuniones.subitem_proximas_reuniones',
    ])->syncRoles([$superAdmin]);


    /*Permission::firstOrCreate([
      'titulo' => 'ajax_obtiene_todas_las_reuniones_para_reportarlas',
      'descripcion' => '',
      'name' => 'reporte_reuniones.ajax_obtiene_todas_las_reuniones_para_reportarlas',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_informacion_principal_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.pestana_informacion_principal_reporte_reunion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_anadir_asistentes_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.pestana_anadir_asistentes_reporte_reunion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_anadir_ingresos_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.pestana_anadir_ingresos_reporte_reunion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_anadir_servidores_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.pestana_anadir_servidores_reporte_reunion',
    ]);
    */
    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_perfil_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_ver_perfil_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_modificar_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_anadir_asistentes_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_anadir_asistentes_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_eliminar_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_anadir_asistentes_reservas_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_anadir_asistentes_reservas_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_subitem_anadir_servidores_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_subitem_anadir_servidores_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_descargar_informe_servidores_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_descargar_informe_servidores_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_descargar_informe_reservas_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_descargar_informe_reservas_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_descargar_informe_asistencias_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_descargar_informe_asistencias_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_descargar_informe_visualizaciones_reporte_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.opcion_descargar_informe_visualizaciones_reporte_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ajax_obtiene_todos_los_asistentes_para_reportar_reunion',
      'descripcion' => '',
      'name' => 'reporte_reuniones.ajax_obtiene_todos_los_asistentes_para_reportar_reunion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_anadir_asistente_reporte_reunion_cualquier_fecha',
      'descripcion' => '',
      'name' => 'reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_iglesia_infantil',
      'descripcion' => '',
      'name' => 'reporte_reuniones.subitem_iglesia_infantil',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_conteo_preliminar_reuniones',
      'descripcion' => '',
      'name' => 'reporte_reuniones.ver_conteo_preliminar_reuniones',
    ]);

    // Sedes
    Permission::firstOrCreate([
      'titulo' => 'lista_sedes_todas',
      'descripcion' => '',
      'name' => 'sedes.lista_sedes_todas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'lista_sedes_solo_ministerio',
      'descripcion' => '',
      'name' => 'sedes.lista_sedes_solo_ministerio',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'item_sedes',
      'descripcion' => '',
      'name' => 'sedes.item_sedes',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_sedes',
      'descripcion' => '',
      'name' => 'sedes.subitem_lista_sedes',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_sede',
      'descripcion' => '',
      'name' => 'sedes.subitem_nueva_sede',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_perfil_sede',
      'descripcion' => '',
      'name' => 'sedes.opcion_ver_perfil_sede',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_sede',
      'descripcion' => '',
      'name' => 'sedes.opcion_modificar_sede',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_baja_sede',
      'descripcion' => '',
      'name' => 'sedes.opcion_dar_de_baja_sede',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_sede',
      'descripcion' => '',
      'name' => 'sedes.opcion_eliminar_sede',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'crear_banners_videos_sede',
      'descripcion' => '',
      'name' => 'sedes.crear_banners_videos_sede',
    ]);

    // Ingresos
    Permission::firstOrCreate([
      'titulo' => 'lista_ingresos_todos',
      'descripcion' => '',
      'name' => 'ingresos.lista_ingresos_todos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'lista_ingresos_solo_ministerio',
      'descripcion' => '',
      'name' => 'ingresos.lista_ingresos_solo_ministerio',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'item_ingresos',
      'descripcion' => '',
      'name' => 'ingresos.item_ingresos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informes_por_persona_ingresos',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informes_por_persona_ingresos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informes_por_grupo_ingresos',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informes_por_grupo_ingresos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informes_por_reunion_ingresos',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informes_por_reunion_ingresos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informe_sumatoria_ingresos_reportes_grupo',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informe_sumatoria_ingresos_reportes_grupo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_perfil_ingreso',
      'descripcion' => '',
      'name' => 'ingresos.opcion_ver_perfil_ingreso',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_ingreso',
      'descripcion' => '',
      'name' => 'ingresos.opcion_modificar_ingreso',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_ingreso',
      'descripcion' => '',
      'name' => 'ingresos.opcion_eliminar_ingreso',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informes_donaciones_online',
      'descripcion' => '',
      'name' => 'ingresos.subitem_informes_donaciones_online',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_ofrenda',
      'descripcion' => '',
      'name' => 'ingresos.subitem_nueva_ofrenda',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_ver_todos_los_ingresos_informes_donaciones_online',
      'descripcion' => '',
      'name' => 'ingresos.privilegio_ver_todos_los_ingresos_informes_donaciones_online',
    ]);

    // Informes
    Permission::firstOrCreate([
      'titulo' => 'opcion_descargar_informe_excel_informe_ingresos_persona',
      'descripcion' => '',
      'name' => 'informes.opcion_descargar_informe_excel_informe_ingresos_persona',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_descargar_informe_pdf_informe_ingresos_persona',
      'descripcion' => '',
      'name' => 'informes.opcion_descargar_informe_pdf_informe_ingresos_persona',
    ]);

    // Temas
    Permission::firstOrCreate([
      'titulo' => 'item_temas',
      'descripcion' => '',
      'name' => 'temas.item_temas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'item_nuevo_tema',
      'descripcion' => '',
      'name' => 'temas.item_nuevo_tema',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'item_listado_temas',
      'descripcion' => '',
      'name' => 'temas.item_listado_temas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_todos_los_temas',
      'descripcion' => '',
      'name' => 'temas.ver_todos_los_temas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_tema',
      'descripcion' => '',
      'name' => 'temas.ver_tema',
    ])->syncRoles([$superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'editar_tema',
      'descripcion' => '',
      'name' => 'temas.editar_tema',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'eliminar_tema',
      'descripcion' => '',
      'name' => 'temas.eliminar_tema',
    ])->syncRoles([$superAdmin]);

    // Iglesia
    Permission::firstOrCreate([
      'titulo' => 'ver_configuracion_iglesia',
      'descripcion' => '',
      'name' => 'iglesia.ver_configuracion_iglesia',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'crear_banners_videos_iglesia',
      'descripcion' => '',
      'name' => 'iglesia.crear_banners_videos_iglesia',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'logo_personalizado',
      'descripcion' => '',
      'name' => 'iglesia.logo_personalizado',
    ]);

    // Actividades
    Permission::firstOrCreate([
      'titulo' => 'item_actividades',
      'descripcion' => '',
      'name' => 'actividades.item_actividades',
    ])->syncRoles([$superAdmin, $lider, $nuevo]);



    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_actividad',
      'descripcion' => '',
      'name' => 'actividades.subitem_nueva_actividad',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_listado_actividad',
      'descripcion' => '',
      'name' => 'actividades.subitem_listado_actividad',
    ])->syncRoles([$superAdmin, $lider, $nuevo]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_historial_carga_de_achivo',
      'descripcion' => '',
      'name' => 'actividades.subitem_historial_carga_de_achivo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.subitem_informe_inscripciones',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informe_compras',
      'descripcion' => '',
      'name' => 'actividades.subitem_informe_compras',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.subitem_informe_pagos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_actualizar_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_actualizar_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_categorias_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_categorias_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_categorias_escuelas_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_categorias_escuelas_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_anadir_encargados_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_anadir_encargados_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_anadir_asistencias_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_anadir_asistencias_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_multimedia_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_multimedia_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_opciones_actividad',
      'descripcion' => '',
      'name' => 'actividades.ver_opciones_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_actualizar_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_actualizar_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_categorias_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_categorias_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_anadir_encargados_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_anadir_encargados_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_anadir_asistencias_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_anadir_asistencias_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_multimediar_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_multimediar_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_boton_exportar_excel_informe_compras',
      'descripcion' => '',
      'name' => 'actividades.ver_boton_exportar_excel_informe_compras',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_filtros_informe_compras',
      'descripcion' => '',
      'name' => 'actividades.ver_filtros_informe_compras',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_columna_compra_informe_compra',
      'descripcion' => '',
      'name' => 'actividades.ver_columna_compra_informe_compra',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_boton_exportar_excel_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.ver_boton_exportar_excel_informe_pagos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_filtros_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.ver_filtros_informe_pagos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_columna_compra_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.ver_columna_compra_informe_pagos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_boton_exportar_excel_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.ver_boton_exportar_excel_informe_inscripciones',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_filtros_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.ver_filtros_informe_inscripciones',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_columna_compra_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.ver_columna_compra_informe_inscripciones',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'lista_asistentes_todos_informe_inscripciones',
      'descripcion' => '',
      'name' => 'actividades.lista_asistentes_todos_informe_inscripciones',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'lista_asistentes_todos_informe_compras',
      'descripcion' => '',
      'name' => 'actividades.lista_asistentes_todos_informe_compras',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'lista_asistentes_todos_informe_pagos',
      'descripcion' => '',
      'name' => 'actividades.lista_asistentes_todos_informe_pagos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_boton_cargar_archivo_historial_carga_de_archivo',
      'descripcion' => '',
      'name' => 'actividades.ver_boton_cargar_archivo_historial_carga_de_archivo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_abonos_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_abonos_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_novedades_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_novedades_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_novedades_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_novedades_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_abonos_actividad',
      'descripcion' => '',
      'name' => 'actividades.opcion_abonos_actividad',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_todas_las_actividades',
      'descripcion' => '',
      'name' => 'actividades.ver_todas_las_actividades',
    ])->syncRoles([$superAdmin]);;

    Permission::firstOrCreate([
      'titulo' => 'sub_item_configuracion_general_web_checking',
      'descripcion' => '',
      'name' => 'actividades.sub_item_configuracion_general_web_checking',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_web_checkin',
      'descripcion' => '',
      'name' => 'actividades.ver_web_checkin',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_anadir_servidores_actividad',
      'descripcion' => '',
      'name' => 'actividades.pestana_anadir_servidores_actividad',
    ]);

    // Puntos de pago
    Permission::firstOrCreate([
      'titulo' => 'item_puntos_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.item_puntos_de_pago',
    ])->syncRoles([$superAdmin, $cajero]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_lista_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_cajas',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_lista_cajas',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_persona_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_nueva_persona_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_compras_de_actividades_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_compras_de_actividades_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_donaciones_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_donaciones_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_boton_nuevo_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.ver_boton_nuevo_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_modificar_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_eliminar_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_alta_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_dar_de_alta_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_baja_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_dar_de_baja_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_boton_nueva_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.ver_boton_nueva_caja',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_historial_de_cierres_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_historial_de_cierres_caja',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_registros_de_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_registros_de_caja',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_alta_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_dar_de_alta_caja',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_cierre_de_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_cierre_de_caja',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_desactivar_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_desactivar_caja',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_activar_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_activar_caja',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_dar_de_baja_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_dar_de_baja_caja',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_abonos_de_actividades_punto_de_pago',
      'descripcion' => '',
      'name' => 'puntos_de_pago.subitem_abonos_de_actividades_punto_de_pago',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'lista_cajas_todas',
      'descripcion' => '',
      'name' => 'puntos_de_pago.lista_cajas_todas',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_anular_registro_caja',
      'descripcion' => '',
      'name' => 'puntos_de_pago.opcion_anular_registro_caja',
    ]);

    // Informes
    Permission::firstOrCreate([
      'titulo' => 'item_informes',
      'descripcion' => '',
      'name' => 'informes.item_informes',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_administrar_informes',
      'descripcion' => '',
      'name' => 'informes.privilegio_administrar_informes',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'privilegio_configurar_semanas',
      'descripcion' => '',
      'name' => 'informes.privilegio_configurar_semanas',
    ]);


    /* Permission::firstOrCreate([
      'titulo' => 'subitem_informe_ministerios_generales',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_ministerios_generales',
    ]);*/

    /*Permission::firstOrCreate([
      'titulo' => 'subitem_informe_mima',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_mima',
    ]);*/

    /*Permission::firstOrCreate([
      'titulo' => 'subitem_informe_no_reportados',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_no_reportados',
    ]);*/

    /*Permission::firstOrCreate([
      'titulo' => 'subitem_informe_almah',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_almah',
    ]);*/

    /*Permission::firstOrCreate([
      'titulo' => 'subitem_informe_inasistencia_grupos',
      'descripcion' => '',
      'name' => 'informes.subitem_informe_inasistencia_grupos',
    ]);*/

    /*Permission::firstOrCreate([
      'titulo' => 'seccion_informes_personalizados',
      'descripcion' => '',
      'name' => 'informes.seccion_informes_personalizados',
    ]);*/

    // Peticiones
    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_peticion',
      'descripcion' => '',
      'name' => 'peticiones.subitem_nueva_peticion',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'item_peticiones',
      'descripcion' => '',
      'name' => 'peticiones.item_peticiones',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_mis_peticiones',
      'descripcion' => '',
      'name' => 'peticiones.subitem_mis_peticiones',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_panel_peticiones',
      'descripcion' => '',
      'name' => 'peticiones.subitem_panel_peticiones',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_peticiones',
      'descripcion' => '',
      'name' => 'peticiones.subitem_gestionar_peticiones',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'lista_peticiones_todas',
      'descripcion' => '',
      'name' => 'peticiones.lista_peticiones_todas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'lista_peticiones_solo_ministerio',
      'descripcion' => '',
      'name' => 'peticiones.lista_peticiones_solo_ministerio',
    ])->syncRoles([$lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar',
      'descripcion' => '',
      'name' => 'peticiones.opcion_eliminar',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminacion_masiva',
      'descripcion' => '',
      'name' => 'peticiones.opcion_eliminacion_masiva',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'boton_descargar_excel',
      'descripcion' => '',
      'name' => 'peticiones.boton_descargar_excel',
    ])->syncRoles([$superAdmin]);

    // Padres
    Permission::firstOrCreate([
      'titulo' => 'item_padres',
      'descripcion' => '',
      'name' => 'padres.item_padres',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_hijos',
      'descripcion' => '',
      'name' => 'padres.subitem_lista_hijos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nuevo_hijo',
      'descripcion' => '',
      'name' => 'padres.subitem_nuevo_hijo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_hijo',
      'descripcion' => '',
      'name' => 'padres.opcion_modificar_hijo',
    ]);

    // Escuelas
    Permission::firstOrCreate([
      'titulo' => 'item_escuelas',
      'descripcion' => '',
      'name' => 'escuelas.item_escuelas',
    ])->syncRoles([$superAdmin, $maestro, $alumno, $lider, $nuevo]);


    // ITEM MENU ESCUELAS Y CONTENIDO INTERIOR

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_escuela',
      'descripcion' => '',
      'name' => 'escuelas.opcion_eliminar_escuela',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_actualizar_escuela',
      'descripcion' => '',
      'name' => 'escuelas.opcion_actualizar_escuela',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'panel_perfil_dashboard',
      'descripcion' => '',
      'name' => 'escuelas.panel_perfil_dashboard',
    ])->syncRoles([$administrador]);


    Permission::firstOrCreate([
      'titulo' => 'todas_las_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.todas_las_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_escuelas',
      'descripcion' => '',
      'name' => 'escuelas.subitem_lista_escuelas',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_escuela',
      'descripcion' => '',
      'name' => 'escuelas.subitem_nueva_escuela',
    ])->syncRoles([$administrador, $superAdmin]);
    Permission::firstOrCreate([
      'titulo' => 'opcion_anadir_materia_escuela',
      'descripcion' => '',
      'name' => 'escuelas.opcion_anadir_materia_escuela',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'listar_opciones_materia',
      'descripcion' => '',
      'name' => 'escuelas.listar_opciones_materia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_materia',
      'descripcion' => '',
      'name' => 'escuelas.opcion_modificar_materia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_materia',
      'descripcion' => '',
      'name' => 'escuelas.opcion_eliminar_materia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_activar_materia',
      'descripcion' => '',
      'name' => 'escuelas.opcion_activar_materia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'escuelas.reportar_asistencia_cualquier_dia',
      'descripcion' => '',
      'name' => 'escuelas.reportar_asistencia_cualquier_dia',
    ])->syncRoles([$administrador, $superAdmin]);

    //aulas

    Permission::firstOrCreate([
      'titulo' => 'item_aula',
      'descripcion' => '',
      'name' => 'escuelas.item_aula',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'gestionar_aulas',
      'descripcion' => '',
      'name' => 'escuelas.gestionar_aulas',
    ])->syncRoles([$administrador, $superAdmin]);


    /// horarios administrativos
    Permission::firstOrCreate([
      'titulo' => 'item_horarios',
      'descripcion' => '',
      'name' => 'escuelas.item_horarios',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'gestionar_horarios',
      'descripcion' => '',
      'name' => 'escuelas.gestionar_horarios',
    ])->syncRoles([$administrador, $superAdmin]);


    /// periodos

    Permission::firstOrCreate([
      'titulo' => 'item_periodos',
      'descripcion' => '',
      'name' => 'escuelas.item_periodos',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_periodos',
      'descripcion' => '',
      'name' => 'escuelas.subitem_lista_periodos',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_periodo',
      'descripcion' => '',
      'name' => 'escuelas.opcion_eliminar_periodo',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_periodo',
      'descripcion' => '',
      'name' => 'escuelas.opcion_modificar_periodo',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_finalizar_periodo',
      'descripcion' => '',
      'name' => 'escuelas.opcion_finalizar_periodo',
    ])->syncRoles([$administrador, $superAdmin]);

    ///CALIFICACIONES

    Permission::firstOrCreate([
      'titulo' => 'calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.calificaciones',
    ])->syncRoles([$administrador, $superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_mis_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_mis_calificaciones',
    ])->syncRoles([$administrador, $superAdmin, $lider]);

    ///HOMOLGACIONES
    Permission::firstOrCreate([
      'titulo' => 'homologaciones',
      'descripcion' => '',
      'name' => 'escuelas.homologaciones',
    ])->syncRoles([$administrador, $superAdmin]);


    //MATRICULAS


    Permission::firstOrCreate([
      'titulo' => 'item_matriculas',
      'descripcion' => '',
      'name' => 'escuelas.item_matriculas',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_matriculas',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_matriculas',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_traslados',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_traslados',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_solicitudes_traslado',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_solicitudes_traslado',
    ])->syncRoles([$administrador, $superAdmin]);

     Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_mis_solicitudes_traslado',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_mis_solicitudes_traslado',
    ])->syncRoles([$alumno]);



    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_matricula',
      'descripcion' => '',
      'name' => 'escuelas.opcion_eliminar_matricula',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_historial_matriculas',
      'descripcion' => '',
      'name' => 'escuelas.subitem_historial_matriculas',
    ])->syncRoles([$administrador, $superAdmin]);


    // MAESTROS

    Permission::firstOrCreate([
      'titulo' => 'item_maestros',
      'descripcion' => '',
      'name' => 'escuelas.item_maestros',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'opcion_gestionar_maestro',
      'descripcion' => '',
      'name' => 'escuelas.opcion_gestionar_maestro',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_crear_maestro',
      'descripcion' => '',
      'name' => 'escuelas.opcion_crear_maestro',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_perfil_maestro',
      'descripcion' => '',
      'name' => 'escuelas.opcion_ver_perfil_maestro',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_maestros',
      'descripcion' => '',
      'name' => 'escuelas.subitem_lista_maestros',
    ])->syncRoles([$administrador, $superAdmin]);

    // BANNERS

    Permission::firstOrCreate([
      'titulo' => 'item_banners',
      'descripcion' => '',
      'name' => 'escuelas.item_banners',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_banners',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_banners',
    ])->syncRoles([$administrador, $superAdmin]);

    // INFORMES

    Permission::firstOrCreate([
      'titulo' => 'item_informes_escuelas',
      'descripcion' => '',
      'name' => 'escuelas.item_informes_escuelas',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_asistencias',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_asistencias',
    ])->syncRoles([$administrador, $superAdmin]);




    /// GENERALES ESCUELAS

    Permission::firstOrCreate([
      'titulo' => 'calificar_cualquier_fecha',
      'descripcion' => '',
      'name' => 'escuelas.calificar_cualquier_fecha',
    ])->syncRoles([$administrador, $superAdmin, $maestro]);

    Permission::firstOrCreate([
      'titulo' => 'reportar_cualquier_fecha',
      'descripcion' => '',
      'name' => 'escuelas.reportar_cualquier_fecha',
    ])->syncRoles([$administrador, $superAdmin, $maestro]);

    Permission::firstOrCreate([
      'titulo' => 'icono',
      'descripcion' => '',
      'name' => 'escuelas.icono',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'item_gestionar_banners',
      'descripcion' => '',
      'name' => 'escuelas.item_gestionar_banners',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'es_maestro',
      'descripcion' => '',
      'name' => 'escuelas.es_maestro',
    ])->syncRoles([$maestro]);

    Permission::firstOrCreate([
      'titulo' => 'es_estudiante',
      'descripcion' => '',
      'name' => 'escuelas.es_estudiante',
    ])->syncRoles([$alumno, $lider, $pastor, $oveja, $nuevo]);

    Permission::firstOrCreate([
      'titulo' => 'es_administrativo',
      'descripcion' => '',
      'name' => 'escuelas.es_administrativo',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'subitem_recursos_generales',
      'descripcion' => '',
      'name' => 'escuelas.subitem_recursos_generales',
    ])->syncRoles([$administrador, $superAdmin, $alumno, $lider, $pastor, $oveja, $nuevo]);

    Permission::firstOrCreate([
      'titulo' => 'gestionar_recursos_generales',
      'descripcion' => '',
      'name' => 'escuelas.gestionar_recursos_generales',
    ])->syncRoles([$administrador, $superAdmin,]);

    Permission::firstOrCreate([
      'titulo' => 'mis_recursos_generales',
      'descripcion' => '',
      'name' => 'escuelas.mis_recursos_generales',
    ])->syncRoles([$alumno, $lider, $pastor, $oveja, $nuevo]);




    Permission::firstOrCreate([
      'titulo' => 'subitem_homologaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_homologaciones',
    ])->syncRoles([$administrador, $superAdmin]);




    Permission::firstOrCreate([
      'titulo' => 'item_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.item_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_historial_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_historial_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_maestro',
      'descripcion' => '',
      'name' => 'escuelas.pestana_maestro',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'pestana_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.pestana_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_materia_como_un_maestro',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_materia_como_un_maestro',
    ])->syncRoles([$administrador, $superAdmin]);




    Permission::firstOrCreate([
      'titulo' => 'auto_matricula',
      'descripcion' => '',
      'name' => 'escuelas.auto_matricula',
    ])->syncRoles([$alumno]);



    Permission::firstOrCreate([
      'titulo' => 'subitem_historial_homologaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_historial_homologaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_homologacion',
      'descripcion' => '',
      'name' => 'escuelas.subitem_nueva_homologacion',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_mis_homologaciones',
      'descripcion' => '',
      'name' => 'escuelas.subitem_mis_homologaciones',
    ])->syncRoles([$oveja]);


    Permission::firstOrCreate([
      'titulo' => 'sub_item_bitacora_matriculas',
      'descripcion' => '',
      'name' => 'escuelas.sub_item_bitacora_matriculas',
    ])->syncRoles([$administrador, $superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'opcion_gestionar_pensum',
      'descripcion' => '',
      'name' => 'escuelas.opcion_gestionar_pensum',
    ])->syncRoles([$administrador, $superAdmin]);





    Permission::firstOrCreate([
      'titulo' => 'item_informes',
      'descripcion' => '',
      'name' => 'escuelas.item_informes',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'item_bitacoras',
      'descripcion' => '',
      'name' => 'escuelas.item_bitacoras',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'sub_bitacoras_item',
      'descripcion' => '',
      'name' => 'escuelas.sub_bitacoras_item',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'sub_bitacoras_calificaciones',
      'descripcion' => '',
      'name' => 'escuelas.sub_bitacoras_calificaciones',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'sub_bitacoras_asistencias',
      'descripcion' => '',
      'name' => 'escuelas.sub_bitacoras_asistencias',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'sub_bitacoras_gestion_asistencia',
      'descripcion' => '',
      'name' => 'escuelas.sub_bitacoras_gestion_asistencia',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'item_certificados',
      'descripcion' => '',
      'name' => 'escuelas.item_certificados',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_diplomas',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_diplomas',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_mis_certificados',
      'descripcion' => '',
      'name' => 'escuelas.subitem_mis_certificados',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_certificados',
      'descripcion' => '',
      'name' => 'escuelas.subitem_gestionar_certificados',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'sub_item_nuevo_recurso_escuela',
      'descripcion' => '',
      'name' => 'escuelas.sub_item_nuevo_recurso_escuela',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'sub_item_mis_recursos',
      'descripcion' => '',
      'name' => 'escuelas.sub_item_mis_recursos',
    ])->syncRoles([$alumno]);

    Permission::firstOrCreate([
      'titulo' => 'item_recursos',
      'descripcion' => '',
      'name' => 'escuelas.item_recursos',
    ])->syncRoles([$administrador, $superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'habilitar_cierrar_corte',
      'descripcion' => '',
      'name' => 'escuelas.habilitar_cierrar_corte',
    ])->syncRoles([$administrador, $superAdmin]);

    // Permisos para Tabs del Dashboard de Clase
    Permission::firstOrCreate([
        'titulo' => 'tab_dashboard_general',
        'descripcion' => 'Acceso al tab Dashboard General',
        'name' => 'escuelas.tab_dashboard_general',
    ])->syncRoles([$administrador, $superAdmin, $maestro]);

    Permission::firstOrCreate([
        'titulo' => 'tab_calificacion_detallada',
        'descripcion' => 'Acceso al tab Calificación Detallada',
        'name' => 'escuelas.tab_calificacion_detallada',
    ])->syncRoles([$administrador, $superAdmin, $maestro]);

    Permission::firstOrCreate([
        'titulo' => 'tab_reportes_asistencia',
        'descripcion' => 'Acceso al tab Reportes de Asistencia',
        'name' => 'escuelas.tab_reportes_asistencia',
    ])->syncRoles([$administrador, $superAdmin, $maestro]);

    Permission::firstOrCreate([
        'titulo' => 'tab_recursos_alumnos',
        'descripcion' => 'Acceso al tab Recursos Alumnos',
        'name' => 'escuelas.tab_recursos_alumnos',
    ])->syncRoles([$administrador, $superAdmin, $maestro]);

    Permission::firstOrCreate([
        'titulo' => 'tab_calificacion_grilla',
        'descripcion' => 'Acceso al tab Calificación Grilla',
        'name' => 'escuelas.tab_calificacion_grilla',
    ])->syncRoles([$administrador, $superAdmin, $maestro]);




    /// FIN ESCUELAS



    ///// PUNTOS DE PAGO
    Permission::firstOrCreate([
      'titulo' => 'item_puntos_de_pago',
      'descripcion' => '',
      'name' => 'pdp.item_puntos_de_pago',
    ])->syncRoles([$superAdmin, $cajero]);

    Permission::firstOrCreate([
      'titulo' => 'item_pdp_gestionar_pdp',
      'descripcion' => '',
      'name' => 'pdp.gestionar_pdp',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'item_pdp_gestionar_asesores',
      'descripcion' => '',
      'name' => 'pdp.gestionar_asesores',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'item_pdp_gestionar_taquillas',
      'descripcion' => '',
      'name' => 'pdp.gestionar_taquillas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'item_pdp_gestionar_anulaciones',
      'descripcion' => '',
      'name' => 'pdp.gestionar_anulaciones',
    ])->syncRoles([$superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'item_pdp_historial_anulaciones',
      'descripcion' => '',
      'name' => 'pdp.historial_anulaciones',
    ])->syncRoles([$superAdmin, $cajero]);

    Permission::firstOrCreate([
      'titulo' => 'item_pdp_mis_cajas',
      'descripcion' => '',
      'name' => 'pdp.mis_cajas',
    ])->syncRoles([$superAdmin, $cajero ]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_listar_todos_los_pdp',
      'descripcion' => '',
      'name' => 'pdp.opcion_listar_todos_los_pdp',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_listar_todos_las_cajas',
      'descripcion' => '',
      'name' => 'pdp.opcion_listar_todos_las_cajas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_anular_registros_pdp_cualquier_momento',
      'descripcion' => '',
      'name' => 'pdp.opcion_anular_registros_pdp_cualquier_momento',
    ])->syncRoles([$superAdmin]);








    ///// FIN PUNTOS DE PAGO

    // Familiar
    Permission::firstOrCreate([
      'titulo' => 'item_familiar',
      'descripcion' => '',
      'name' => 'familiar.item_familiar',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gentionar_relaciones',
      'descripcion' => '',
      'name' => 'familiar.subitem_gentionar_relaciones',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_informes',
      'descripcion' => '',
      'name' => 'familiar.subitem_informes',
    ])->syncRoles([$superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_relacion_familiar',
      'descripcion' => '',
      'name' => 'familiar.opcion_modificar_relacion_familiar',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_relacion_familiar',
      'descripcion' => '',
      'name' => 'familiar.opcion_eliminar_relacion_familiar',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'ver_boton_nueva_relacion_familiar',
      'descripcion' => '',
      'name' => 'familiar.ver_boton_nueva_relacion_familiar',
    ])->syncRoles([$superAdmin]);

    //Dashboard
    Permission::firstOrCreate([
      'titulo' => 'dashboard_mostrar_calendario',
      'descripcion' => '',
      'name' => 'dashboard.dashboard_mostrar_calendario',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_banners_videos_todos',
      'descripcion' => '',
      'name' => 'dashboard.ver_banners_videos_todos',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_video_software_redil_defecto',
      'descripcion' => '',
      'name' => 'dashboard.ver_video_software_redil_defecto',
    ]);

    // Administracion
    Permission::firstOrCreate([
      'titulo' => 'ver_cronograma_desarrollo',
      'descripcion' => '',
      'name' => 'administracion.ver_cronograma_desarrollo',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'editar_item_etapas_crecimiento',
      'descripcion' => '',
      'name' => 'administracion.editar_item_etapas_crecimiento',
    ]);

    Permission::firstOrCreate([
      'titulo' => 'ver_item_etapas_crecimiento',
      'descripcion' => '',
      'name' => 'administracion.ver_item_etapas_crecimiento',
    ]);

    // rueda de la vida
    Permission::firstOrCreate([
      'titulo' => 'item_rueda_de_la_vida',
      'descripcion' => '',
      'name' => 'rueda_de_la_vida.item_rueda_de_la_vida',
    ])->syncRoles([$superAdmin, $nuevo]);

    // tiempo con DIOS
    Permission::firstOrCreate([
      'titulo' => 'item_tiempo_con_dios',
      'descripcion' => '',
      'name' => 'tiempo_con_dios.item_tiempo_con_dios',
    ])->syncRoles([$superAdmin, $lider, $nuevo]);

    // finanzas
    Permission::firstOrCreate([
      'titulo' => 'item_finanzas',
      'descripcion' => '',
      'name' => 'finanzas.item_finanzas',
    ])->syncRoles([$superAdmin]);

    // configuraciones
    Permission::firstOrCreate([
      'titulo' => 'item_configuraciones',
      'descripcion' => '',
      'name' => 'configuraciones.item_configuraciones',
    ])->syncRoles([$superAdmin]);


    Permission::firstOrCreate([
      'titulo' => 'subitem_general',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_general',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_roles',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_roles',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_roles',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_zonas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_plantilla',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_plantilla',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_tarea_consolidacion',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tarea_consolidacion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_pasos_de_crecimiento',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_pasos_de_crecimiento',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_tipos_de_grupos',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tipos_de_grupos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_tipo_de_usuarios',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tipo_de_usuarios',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_filtro_de_consolidacion',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_filtro_de_consolidacion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_rangos_de_edad',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_rangos_de_edad',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_de_reproduccion',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_lista_de_reproduccion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_banner_general',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_banner_general',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_tipo_pagos',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tipo_pagos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_videos',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_gestionar_videos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_formulario_usuarios',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_formulario_usuarios',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_formulario_usuarios',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_gestionar_formulario_usuarios',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_tipos_de_ofrendas',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_tipos_de_ofrendas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_campos_formulario_usuario',
      'descripcion' => '',
      'name' => 'configuraciones.subitem_gestionar_campos_formulario_usuario',
    ])->syncRoles([$superAdmin]);


    // Iglesia
    Permission::firstOrCreate([
      'titulo' => 'item_iglesia',
      'descripcion' => '',
      'name' => 'iglesia.item_iglesia',
    ])->syncRoles([$superAdmin]);


    // consolidación

    Permission::firstOrCreate([
      'titulo' => 'item_consolidacion',
      'descripcion' => '',
      'name' => 'consolidacion.item_consolidacion',
    ])->syncRoles([$superAdmin, $consolidadorMedellin, $consolidadorBogota]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_lista_consolidacion',
      'descripcion' => '',
      'name' => 'consolidacion.subitem_lista_consolidacion',
    ])->syncRoles([$superAdmin, $consolidadorMedellin, $consolidadorBogota]);

    Permission::firstOrCreate([
      'titulo' => 'lista_toda_consolidacion',
      'descripcion' => '',
      'name' => 'consolidacion.lista_toda_consolidacion',
    ])->syncRoles([$superAdmin ]);

    Permission::firstOrCreate([
      'titulo' => 'lista_consolidacion_solo_ministerio',
      'descripcion' => '',
      'name' => 'consolidacion.lista_consolidacion_solo_ministerio',
    ])->syncRoles([$consolidadorMedellin, $consolidadorBogota]);

    Permission::firstOrCreate([
      'titulo' => 'dashboard_consolidacion',
      'descripcion' => '',
      'name' => 'consolidacion.dashboard_consolidacion',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'reporte_desempeño',
      'descripcion' => '',
      'name' => 'consolidacion.reporte_desempeño',
    ])->syncRoles([$superAdmin]);


    // Consejeria
    Permission::firstOrCreate([
      'titulo' => 'item_consejeria',
      'descripcion' => '',
      'name' => 'consejeria.item_consejeria',
    ])->syncRoles([$superAdmin, $consejero, $nuevo]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_consejeros',
      'descripcion' => '',
      'name' => 'consejeria.subitem_gestionar_consejeros',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_cita',
      'descripcion' => '',
      'name' => 'consejeria.subitem_nueva_cita',
    ])->syncRoles([$superAdmin, $nuevo]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_mis_citas',
      'descripcion' => '',
      'name' => 'consejeria.subitem_mis_citas',
    ])->syncRoles([$superAdmin, $nuevo]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_calendario_citas',
      'descripcion' => '',
      'name' => 'consejeria.subitem_calendario_citas',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_reprogramar_cita',
      'descripcion' => '',
      'name' => 'consejeria.opcion_reprogramar_cita',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_cancelar_cita',
      'descripcion' => '',
      'name' => 'consejeria.opcion_cancelar_cita',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_activar_desactivar_consejero',
      'descripcion' => '',
      'name' => 'consejeria.opcion_activar_desactivar_consejero',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_consejero',
      'descripcion' => '',
      'name' => 'consejeria.opcion_eliminar_consejero',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_editar_consejero',
      'descripcion' => '',
      'name' => 'consejeria.opcion_editar_consejero',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_configurar_horarios',
      'descripcion' => '',
      'name' => 'consejeria.opcion_configurar_horarios',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'boton_nuevo_consejero',
      'descripcion' => '',
      'name' => 'consejeria.boton_nuevo_consejero',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_agendar_cita',
      'descripcion' => '',
      'name' => 'consejeria.opcion_agendar_cita',
    ])->syncRoles([$superAdmin]);

    // Permisos para Publicaciones (Posts)
    Permission::firstOrCreate([
      'titulo' => 'item_publicaciones',
      'descripcion' => 'Ítem del menú de publicaciones',
      'name' => 'posts.item_publicaciones',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_publicaciones',
      'descripcion' => 'Subítem para gestionar publicaciones',
      'name' => 'posts.subitem_gestionar_publicaciones',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'listar_todas_publicaciones',
      'descripcion' => 'Permite listar todas las publicaciones',
      'name' => 'posts.listar_todas_publicaciones',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'listar_solo_mis_publicaciones',
      'descripcion' => 'Permite listar únicamente las publicaciones que el usuario ha creado',
      'name' => 'posts.listar_solo_mis_publicaciones',
    ])->syncRoles([$lider]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nueva_publicacion',
      'descripcion' => 'Subítem para crear una nueva publicación',
      'name' => 'posts.subitem_nueva_publicacion',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_publicacion',
      'descripcion' => 'Opción para eliminar una publicación',
      'name' => 'posts.opcion_eliminar_publicacion',
    ])->syncRoles([$superAdmin, $lider]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_publicacion',
      'descripcion' => 'Opción para modificar una publicación',
      'name' => 'posts.opcion_modificar_publicacion',
    ])->syncRoles([$superAdmin, $lider]);

    // Permisos para Versículos del Día
    Permission::firstOrCreate([
      'titulo' => 'item_versiculos',
      'descripcion' => 'Ítem del menú de versículos',
      'name' => 'versiculos.item_versiculos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_versiculos',
      'descripcion' => 'Subítem para gestionar versículos',
      'name' => 'versiculos.subitem_gestionar_versiculos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_nuevo_versiculo',
      'descripcion' => 'Subítem para crear un nuevo versículo',
      'name' => 'versiculos.subitem_nuevo_versiculo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_versiculo',
      'descripcion' => 'Opción para eliminar un versículo',
      'name' => 'versiculos.opcion_eliminar_versiculo',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_modificar_versiculo',
      'descripcion' => 'Opción para modificar un versículo',
      'name' => 'versiculos.opcion_modificar_versiculo',
    ])->syncRoles([$superAdmin]);

    // Permisos para Cursos (LMS)
    Permission::firstOrCreate([
      'titulo' => 'item_cursos',
      'descripcion' => 'Ítem del menú de cursos (LMS)',
      'name' => 'cursos.item_cursos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_gestionar_cursos',
      'descripcion' => 'Subítem para gestionar cursos',
      'name' => 'cursos.subitem_gestionar_cursos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_campus_cursos',
      'descripcion' => 'Subítem para acceder al campus de cursos',
      'name' => 'cursos.subitem_campus_cursos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'subitem_foro_cursos',
      'descripcion' => 'Subítem para acceder al foro de cursos',
      'name' => 'cursos.subitem_foro_cursos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_crear_curso',
      'descripcion' => 'Opción para crear un nuevo curso',
      'name' => 'cursos.opcion_crear_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_ver_detalles_curso',
      'descripcion' => 'Opción para ver detalles de un curso',
      'name' => 'cursos.opcion_ver_detalles_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_editar_curso',
      'descripcion' => 'Opción para editar un curso',
      'name' => 'cursos.opcion_editar_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_restricciones_curso',
      'descripcion' => 'Opción para gestionar restricciones de un curso',
      'name' => 'cursos.opcion_restricciones_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_contenido_curso',
      'descripcion' => 'Opción para gestionar contenido de un curso',
      'name' => 'cursos.opcion_contenido_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_detalle_aprendizaje_curso',
      'descripcion' => 'Opción para gestionar detalle de aprendizaje',
      'name' => 'cursos.opcion_detalle_aprendizaje_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_gestion_equipo_curso',
      'descripcion' => 'Opción para gestionar equipo del curso',
      'name' => 'cursos.opcion_gestion_equipo_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_gestion_inscritos_curso',
      'descripcion' => 'Opción para gestionar inscritos del curso',
      'name' => 'cursos.opcion_gestion_inscritos_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'opcion_eliminar_curso',
      'descripcion' => 'Opción para eliminar un curso',
      'name' => 'cursos.opcion_eliminar_curso',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'listar_todos_cursos',
      'descripcion' => 'Permite listar todos los cursos independientemente de su equipo',
      'name' => 'cursos.listar_todos_cursos',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'listar_solo_cursos_asignados',
      'descripcion' => 'Permite listar únicamente los cursos en los que el usuario hace parte del equipo',
      'name' => 'cursos.listar_solo_cursos_asignados',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'listar_todas_conversaciones',
      'descripcion' => 'Permite ver todas las conversaciones del foro de todos los cursos',
      'name' => 'cursos.listar_todas_conversaciones',
    ])->syncRoles([$superAdmin]);

    Permission::firstOrCreate([
      'titulo' => 'conversaciones_cursos_asignados',
      'descripcion' => 'Permite ver las conversaciones solo de cursos donde el cargo permite responder foro',
      'name' => 'cursos.conversaciones_cursos_asignados',
    ])->syncRoles([$superAdmin]);
  }
}
