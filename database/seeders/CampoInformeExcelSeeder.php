<?php

namespace Database\Seeders;

use App\Models\CampoInformeExcel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampoInformeExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campos = [
            ['id' => 62, 'nombre_campo_bd' => 'grupo_id', 'nombre_campo_informe' => 'Grupo directo', 'selector_id' => 5, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 62],
            ['id' => 1, 'nombre_campo_bd' => 'tipo_identificacion', 'nombre_campo_informe' => 'Tipo de identificación', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 1],
            ['id' => 2, 'nombre_campo_bd' => 'identificacion', 'nombre_campo_informe' => 'Identificación', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 2],
            ['id' => 6, 'nombre_campo_bd' => 'segundo_nombre', 'nombre_campo_informe' => 'Segundo nombre', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 5],
            ['id' => 5, 'nombre_campo_bd' => 'primer_nombre', 'nombre_campo_informe' => 'Primer nombre', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 4],
            ['id' => 8, 'nombre_campo_bd' => 'primer_apellido', 'nombre_campo_informe' => 'Primer apellido', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 6],
            ['id' => 9, 'nombre_campo_bd' => 'segundo_apellido', 'nombre_campo_informe' => 'Segundo apellido', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 7],
            ['id' => 10, 'nombre_campo_bd' => 'estado_civil', 'nombre_campo_informe' => 'Estado civil', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 8],
            ['id' => 11, 'nombre_campo_bd' => 'pais_id', 'nombre_campo_informe' => 'País de nacimiento', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 9],
            ['id' => 12, 'nombre_campo_bd' => 'telefono_fijo', 'nombre_campo_informe' => 'Teléfono fijo', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 10],
            ['id' => 45, 'nombre_campo_bd' => 'telefono_otro', 'nombre_campo_informe' => 'Otro teléfono', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 11],
            ['id' => 13, 'nombre_campo_bd' => 'telefono_movil', 'nombre_campo_informe' => 'Teléfono móvil', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 12],
            ['id' => 14, 'nombre_campo_bd' => 'email', 'nombre_campo_informe' => 'E-mail', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 13],
            ['id' => 15, 'nombre_campo_bd' => 'direccion', 'nombre_campo_informe' => 'Dirección', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 14],
            ['id' => 16, 'nombre_campo_bd' => 'tipo_vivienda', 'nombre_campo_informe' => 'Vivienda en calidad de', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 15],
            ['id' => 17, 'nombre_campo_bd' => 'nivel_academico', 'nombre_campo_informe' => 'Nivel académico', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 16],
            ['id' => 18, 'nombre_campo_bd' => 'estado_nivel_academico', 'nombre_campo_informe' => 'Estado nivel académico', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 17],
            ['id' => 19, 'nombre_campo_bd' => 'profesion', 'nombre_campo_informe' => 'Profesión', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 18],
            ['id' => 20, 'nombre_campo_bd' => 'sector_economico', 'nombre_campo_informe' => 'Sector económico', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 19],
            ['id' => 21, 'nombre_campo_bd' => 'tipo_sangre', 'nombre_campo_informe' => 'Tipo de sangre', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 20],
            ['id' => 22, 'nombre_campo_bd' => 'indicaciones_medicas', 'nombre_campo_informe' => 'Indicaciones médicas', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 21],
            ['id' => 31, 'nombre_campo_bd' => 'nombre', 'nombre_campo_informe' => 'Nombre', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 34],
            ['id' => 32, 'nombre_campo_bd' => 'fecha_apertura', 'nombre_campo_informe' => 'Fecha de creación', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 37],
            ['id' => 33, 'nombre_campo_bd' => 'tipo_vivienda', 'nombre_campo_informe' => 'Vivienda en calidad De', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 38],
            ['id' => 34, 'nombre_campo_bd' => 'direccion', 'nombre_campo_informe' => 'Dirección', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 39],
            ['id' => 35, 'nombre_campo_bd' => 'telefono', 'nombre_campo_informe' => 'Teléfono', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 40],
            ['id' => 36, 'nombre_campo_bd' => 'dia', 'nombre_campo_informe' => 'Día', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 41],
            ['id' => 37, 'nombre_campo_bd' => 'hora', 'nombre_campo_informe' => 'Hora', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 42],
            ['id' => 38, 'nombre_campo_bd' => 'dia_planeacion', 'nombre_campo_informe' => 'Dia planeación', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 43],
            ['id' => 39, 'nombre_campo_bd' => 'hora_planeacion', 'nombre_campo_informe' => 'Hora planeación', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 44],
            ['id' => 40, 'nombre_campo_bd' => 'encargados', 'nombre_campo_informe' => 'Encargados', 'selector_id' => 5, 'tabla' => 'encargados_grupo.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 45],
            ['id' => 41, 'nombre_campo_bd' => 'fecha', 'nombre_campo_informe' => 'Fecha ultimo reporte', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 46],
            ['id' => 42, 'nombre_campo_bd' => 'latitud', 'nombre_campo_informe' => 'Georreferencia realizada', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 47],
            ['id' => 44, 'nombre_campo_bd' => 'sede_id', 'nombre_campo_informe' => 'Sede', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 48],
            ['id' => 43, 'nombre_campo_bd' => 'cantidad_asistentes', 'nombre_campo_informe' => 'Cantidad asistentes directos', 'selector_id' => 5, 'tabla' => 'users.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 49],
            ['id' => 46, 'nombre_campo_bd' => 'dado_alta', 'nombre_campo_informe' => 'Motivo dado alta', 'selector_id' => 1, 'tabla' => 'null.', 'raw_sql' => null, 'eloquent_sql' => null, 'orden' => 23],
            ['id' => 47, 'nombre_campo_bd' => 'dado_baja', 'nombre_campo_informe' => 'Motivo dado baja', 'selector_id' => 1, 'tabla' => 'null.', 'raw_sql' => null, 'eloquent_sql' => null, 'orden' => 24],
            ['id' => 48, 'nombre_campo_bd' => 'fecha_dado_alta', 'nombre_campo_informe' => 'Fecha dado alta', 'selector_id' => 1, 'tabla' => 'null.', 'raw_sql' => null, 'eloquent_sql' => null, 'orden' => 25],
            ['id' => 49, 'nombre_campo_bd' => 'fecha_dado_baja', 'nombre_campo_informe' => 'Fecha dado baja', 'selector_id' => 1, 'tabla' => 'null.', 'raw_sql' => null, 'eloquent_sql' => null, 'orden' => 26],
            ['id' => 54, 'nombre_campo_bd' => 'contacto_adulto_responsable', 'nombre_campo_informe' => 'Contacto adulto responsable', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 28],
            ['id' => 55, 'nombre_campo_bd' => 'nombre_acudiente', 'nombre_campo_informe' => 'Nombre acudiente', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 29],
            ['id' => 56, 'nombre_campo_bd' => 'telefono_acudiente', 'nombre_campo_informe' => 'Contacto acudiente', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 30],
            ['id' => 52, 'nombre_campo_bd' => 'edad', 'nombre_campo_informe' => 'Edad', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 3],
            ['id' => 3, 'nombre_campo_bd' => 'fecha_nacimiento', 'nombre_campo_informe' => 'Fecha nacimiento', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 33],
            ['id' => 50, 'nombre_campo_bd' => 'genero', 'nombre_campo_informe' => 'Sexo', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 34],
            ['id' => 29, 'nombre_campo_bd' => 'fecha', 'nombre_campo_informe' => 'Fecha de los procesos', 'selector_id' => 4, 'tabla' => 'crecimiento_users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 60],
            ['id' => 30, 'nombre_campo_bd' => 'detalle', 'nombre_campo_informe' => 'Detalle de los procesos', 'selector_id' => 4, 'tabla' => 'crecimiento_users.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 61],
            ['id' => 28, 'nombre_campo_bd' => 'estado', 'nombre_campo_informe' => 'Estados de los proceso', 'selector_id' => 4, 'tabla' => 'crecimiento_users.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 62],
            ['id' => 58, 'nombre_campo_bd' => 'ultimo_reporte_grupo', 'nombre_campo_informe' => 'Última asistencia a grupo', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 35],
            ['id' => 59, 'nombre_campo_bd' => 'ultimo_reporte_reunion', 'nombre_campo_informe' => 'Última asistencia a reunión', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 36],
            ['id' => 24, 'nombre_campo_bd' => 'tipo_vinculacion_id', 'nombre_campo_informe' => 'Tipo de vinculación', 'selector_id' => 2, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 37],
            ['id' => 25, 'nombre_campo_bd' => 'tipo_usuario_id', 'nombre_campo_informe' => 'Tipo de usuario', 'selector_id' => 2, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 38],
            ['id' => 27, 'nombre_campo_bd' => 'sede_id', 'nombre_campo_informe' => 'Sede', 'selector_id' => 2, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 40],
            ['id' => 61, 'nombre_campo_bd' => 'tipo_grupo_id', 'nombre_campo_informe' => 'Tipo grupo', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 51],
            ['id' => 60, 'nombre_campo_bd' => 'label_campo_opcional1', 'nombre_campo_informe' => 'Subdominio', 'selector_id' => 5, 'tabla' => 'grupos.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 50],
            ['id' => 63, 'nombre_campo_bd' => 'tipo_usuario_id', 'nombre_campo_informe' => 'Tipo usuario', 'selector_id' => 8, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 63],
            ['id' => 53, 'nombre_campo_bd' => 'nombre_adulto_responsable', 'nombre_campo_informe' => 'Nombre adulto responsable', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => false, 'eloquent_sql' => true, 'orden' => 27],
            ['id' => 65, 'nombre_campo_bd' => 'created_at', 'nombre_campo_informe' => 'Fecha de creación', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 49],
            ['id' => 23, 'nombre_campo_bd' => 'informacion_opcional', 'nombre_campo_informe' => 'Información opcional', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 22],
            ['id' => 64, 'nombre_campo_bd' => 'grupo_id', 'nombre_campo_informe' => 'Grupo directo', 'selector_id' => 8, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 64],
            ['id' => 26, 'nombre_campo_bd' => 'grupo_id', 'nombre_campo_informe' => 'Grupo al que pertenece', 'selector_id' => 2, 'tabla' => 'users.', 'raw_sql' => false, 'eloquent_sql' => false, 'orden' => 39],
            ['id' => 66, 'nombre_campo_bd' => 'usuario_creacion_id', 'nombre_campo_informe' => 'Usuario creación', 'selector_id' => 1, 'tabla' => 'users.', 'raw_sql' => true, 'eloquent_sql' => false, 'orden' => 49],
        ];

        foreach ($campos as $campo) {
            CampoInformeExcel::updateOrCreate(['id' => $campo['id']], $campo);
        }

        $x = CampoInformeExcel::select('id')->orderBy('id', 'desc')->first();

        CampoInformeExcel::firstOrCreate(
            ['nombre_campo_bd' => 'fecha_baja', 'selector_id' => 5, 'tabla' => 'grupos.'],
            [
                'id' => $x ? $x->id + 1 : 1,
                'nombre_campo_informe' => 'fecha_baja',
                'raw_sql' => 1,
                'eloquent_sql' => 0,
                'orden' => 63,
            ]);

        CampoInformeExcel::firstOrCreate(
            ['nombre_campo_bd' => 'motivo_baja', 'selector_id' => 5, 'tabla' => 'grupos.'],
            [
                'id' => $x ? $x->id + 2 : 2,
                'nombre_campo_informe' => 'motivo_baja',
                'raw_sql' => 1,
                'eloquent_sql' => 0,
                'orden' => 64,
            ]);

        CampoInformeExcel::firstOrCreate(
            ['nombre_campo_bd' => 'fecha_alta', 'selector_id' => 5, 'tabla' => 'grupos.'],
            [
                'id' => $x ? $x->id + 3 : 3,
                'nombre_campo_informe' => 'fecha_alta',
                'raw_sql' => 1,
                'eloquent_sql' => 0,
                'orden' => 65,
            ]);

        CampoInformeExcel::firstOrCreate(
            ['nombre_campo_bd' => 'motivo_alta', 'selector_id' => 5, 'tabla' => 'grupos.'],
            [
                'id' => $x ? $x->id + 4 : 4,
                'nombre_campo_informe' => 'motivo_alta',
                'raw_sql' => 1,
                'eloquent_sql' => 0,
                'orden' => 66,
            ]);

        // Fix postgres sequence after manual inserts
        DB::unprepared("SELECT setval('campos_informe_excel_id_seq', (SELECT MAX(id) FROM campos_informe_excel));");
    }
}
