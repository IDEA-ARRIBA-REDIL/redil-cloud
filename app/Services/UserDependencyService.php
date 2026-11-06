<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class UserDependencyService
{
    /**
     * Diccionario centralizado de etiquetas descriptivas para los módulos del sistema.
     *
     * @return array<string, string>
     */
    public function obtenerEtiquetas(): array
    {
        return [
            'reuniones' => 'Asistencias a reuniones congregacionales',
            'grupos_asistencia' => 'Asistencias a grupos pequeños',
            'grupos_encargado' => 'Grupos donde figura como encargado/líder',
            'matriculas' => 'Matrículas en escuelas de formación',
            'cursos_lms' => 'Inscripciones, evaluaciones o cargos docentes en Cursos (LMS)',
            'actividades' => 'Inscripciones o cargos en Actividades/Eventos',
            'iglesia_infantil' => 'Registros como menor en Iglesia Infantil',
            'rueda_de_la_vida' => 'Evaluaciones de Rueda de la Vida',
            'compras' => 'Compras o inscripciones realizadas',
            'ofrendas' => 'Aportes u ofrendas registradas',
            'ingresos' => 'Ingresos registrados',
            'peticiones' => 'Peticiones de oración radicadas',
            'citas_consejeria' => 'Citas de consejería',
            'consolidacion' => 'Tareas de consolidación asignadas',
            'parientes' => 'Parientes vinculados en el núcleo familiar',
            'cajas' => 'Cajas de recaudo asignadas',
        ];
    }

    /**
     * Obtiene el resumen de dependencias y registros históricos del usuario.
     *
     * @return array<string, int>
     */
    public function obtenerResumenRegistros(User $usuario): array
    {
        $resumen = [];

        $resumen['reuniones'] = $this->contarSiExiste('asistencia_reuniones', 'user_id', $usuario->id);
        $resumen['grupos_asistencia'] = $this->contarSiExiste('asistencia_grupos', 'user_id', $usuario->id);
        $resumen['grupos_encargado'] = $this->contarSiExiste('encargados_grupo', 'user_id', $usuario->id);
        $resumen['matriculas'] = $this->contarSiExiste('matriculas', 'user_id', $usuario->id)
            + $this->contarSiExiste('matriculas_nivel', 'usuario_id', $usuario->id);
        $resumen['cursos_lms'] = $this->contarSiExiste('curso_users', 'user_id', $usuario->id)
            + $this->contarSiExiste('curso_usuario_cargo', 'usuario_id', $usuario->id)
            + $this->contarSiExiste('curso_evaluacion_resultados', 'user_id', $usuario->id)
            + $this->contarSiExiste('curso_item_user', 'user_id', $usuario->id)
            + $this->contarSiExiste('curso_foro_hilos', 'user_id', $usuario->id)
            + $this->contarSiExiste('curso_foro_respuestas', 'user_id', $usuario->id);
        $resumen['actividades'] = $this->contarSiExiste('actividad_asistencias', 'user_id', $usuario->id)
            + $this->contarSiExiste('actividad_asistencias_inscripcion', 'user_id', $usuario->id)
            + $this->contarSiExiste('actividad_encargados_cargo', 'user_id', $usuario->id);
        $resumen['iglesia_infantil'] = $this->contarSiExiste('registros_iglesia_infantil', 'menor_user_id', $usuario->id);
        $resumen['rueda_de_la_vida'] = $this->contarSiExiste('rueda_de_la_vida_user', 'usuario_id', $usuario->id);
        $resumen['compras'] = $this->contarSiExiste('compras', 'user_id', $usuario->id)
            + $this->contarSiExiste('carritos_curso_user', 'user_id', $usuario->id);
        $resumen['ofrendas'] = $this->contarSiExiste('ofrendas', 'user_id', $usuario->id);
        $resumen['ingresos'] = $this->contarSiExiste('ingresos', 'user_id', $usuario->id);
        $resumen['peticiones'] = $this->contarSiExiste('peticiones', 'user_id', $usuario->id);
        $resumen['citas_consejeria'] = $this->contarSiExiste('citas_consejeria', 'user_id', $usuario->id);
        $resumen['consolidacion'] = $this->contarSiExiste('tarea_consolidacion_usuario', 'user_id', $usuario->id);
        $resumen['parientes'] = $this->contarParientes($usuario->id);
        $resumen['cajas'] = $this->contarSiExiste('cajas', 'user_id', $usuario->id);

        return $resumen;
    }

    /**
     * Determina si el usuario tiene algún registro histórico o crítico.
     */
    public function tieneRegistrosCriticos(User $usuario): bool
    {
        $resumen = $this->obtenerResumenRegistros($usuario);

        return array_sum($resumen) > 0;
    }

    /**
     * Construye el HTML con el detalle de registros para la advertencia en "Eliminar".
     *
     * @param  array<string, int>  $resumen
     */
    public function generarHtmlResumen(array $resumen, User $usuario): string
    {
        $etiquetas = $this->obtenerEtiquetas();
        $items = '';

        foreach ($resumen as $clave => $cantidad) {
            if ($cantidad > 0 && isset($etiquetas[$clave])) {
                $items .= "<li><b>{$cantidad}</b> {$etiquetas[$clave]}</li>";
            }
        }

        return "
            <p class=\"text-start\">No es recomendado eliminar a <b>{$usuario->nombre(3)}</b> debido a que tiene historial activo en el sistema:</p>
            <ul class=\"text-start text-black mb-3\">
                {$items}
            </ul>
            <p class=\"text-start fw-semibold\">Para preservar la trazabilidad histórica, se recomienda <u>darlo de baja</u>. ¿Deseas darlo de baja?</p>
        ";
    }

    /**
     * Construye el HTML con el detalle de registros para la confirmación de "Eliminación Forzada".
     *
     * @param  array<string, int>  $resumen
     */
    public function generarHtmlResumenPurga(array $resumen, User $usuario): string
    {
        $etiquetas = $this->obtenerEtiquetas();
        $items = '';

        foreach ($resumen as $clave => $cantidad) {
            if ($cantidad > 0 && isset($etiquetas[$clave])) {
                $items .= "<li><b>{$cantidad}</b> {$etiquetas[$clave]}</li>";
            }
        }

        if ($items !== '') {
            return "
                <p class=\"text-danger fw-bold text-start mb-2\">¡ATENCIÓN! La eliminación forzada es irreversible y destruirá definitivamente los siguientes registros asociados:</p>
                <ul class=\"text-start text-black small mb-3\">
                    {$items}
                    <li>Archivos adjuntos y fotos de perfil en disco</li>
                    <li>Sesiones activas y credenciales de acceso</li>
                </ul>
                <p class=\"text-start small fw-semibold text-black\">Para autorizar la eliminación total de estos datos, ingresa tu contraseña de administrador:</p>
            ";
        }

        return '
            <p class="text-danger fw-bold text-start mb-2">¡ATENCIÓN! La eliminación forzada es irreversible.</p>
            <p class="text-start text-black small mb-3">Esta persona no tiene historial congregacional ni financiero. Se eliminará su perfil, credenciales y archivos asociados.</p>
            <p class="text-start small fw-semibold text-black">Para autorizar la eliminación definitiva, ingresa tu contraseña de administrador:</p>
        ';
    }

    /**
     * Ejecuta la purga relacional total y definitiva del usuario y sus archivos en disco.
     */
    public function ejecutarPurgaTotal(User $usuario): void
    {
        DB::transaction(function () use ($usuario) {
            $id = $usuario->id;

            // 1. Eliminación de archivos físicos
            $this->eliminarArchivosFisicos($usuario);

            // 2. Desvincular relaciones Many-to-Many / pivotes
            $this->desvincularPivotes($usuario);

            // 3. Eliminar registros hijos dependientes directos
            $this->eliminarRegistrosHijos($id);

            // 4. Nullify o reasignación en auditorías o registros de terceros
            $this->anularAuditoriasTerceros($id);

            // 5. Borrado definitivo de la tabla users
            $usuario->forceDelete();
        });
    }

    /**
     * Elimina los archivos físicos del usuario en el Storage.
     */
    protected function eliminarArchivosFisicos(User $usuario): void
    {
        $archivos = [
            'archivos/usuario/'.$usuario->archivo_a,
            'archivos/usuario/'.$usuario->archivo_b,
            'archivos/usuario/'.$usuario->archivo_c,
            'archivos/usuario/'.$usuario->archivo_d,
        ];

        foreach ($archivos as $archivo) {
            if ($archivo && Storage::exists($archivo)) {
                Storage::delete($archivo);
            }
        }

        if ($usuario->foto && Storage::exists('img/usuario/fotos/'.$usuario->foto)) {
            Storage::delete('img/usuario/fotos/'.$usuario->foto);
        }

        if ($usuario->portada && $usuario->portada !== 'profile-banner.png' && Storage::exists('img/usuario/banners/'.$usuario->portada)) {
            Storage::delete('img/usuario/banners/'.$usuario->portada);
        }
    }

    /**
     * Desvincula todas las relaciones pivote del usuario.
     */
    protected function desvincularPivotes(User $usuario): void
    {
        // Spatie RBAC
        if (method_exists($usuario, 'roles')) {
            $usuario->roles()->detach();
        }
        if (method_exists($usuario, 'permissions')) {
            $usuario->permissions()->detach();
        }

        // Relaciones familiares
        if (method_exists($usuario, 'parientesDelUsuario')) {
            $usuario->parientesDelUsuario()->detach();
        }
        if (method_exists($usuario, 'usuariosDelPariente')) {
            $usuario->usuariosDelPariente()->detach();
        }

        // Grupos
        if (method_exists($usuario, 'gruposDondeAsiste')) {
            $usuario->gruposDondeAsiste()->detach();
        }
        if (method_exists($usuario, 'gruposEncargados')) {
            $usuario->gruposEncargados()->detach();
        }
        if (method_exists($usuario, 'gruposExcluidos')) {
            $usuario->gruposExcluidos()->detach();
        }
        if (method_exists($usuario, 'grupoServicio')) {
            $usuario->grupoServicio()->detach();
        }
        if (method_exists($usuario, 'iglesiaEncargada')) {
            $usuario->iglesiaEncargada()->detach();
        }

        // Crecimiento, hitos, insignias
        if (method_exists($usuario, 'pasosCrecimiento')) {
            $usuario->pasosCrecimiento()->detach();
        }
        if (method_exists($usuario, 'insignias')) {
            $usuario->insignias()->detach();
        }
        if (method_exists($usuario, 'versiculosDiariosLike')) {
            $usuario->versiculosDiariosLike()->detach();
        }

        // Campos personalizados
        if (method_exists($usuario, 'camposExtras')) {
            $usuario->camposExtras()->detach();
        }
        if (method_exists($usuario, 'camposFormularioUsuario')) {
            $usuario->camposFormularioUsuario()->detach();
        }

        // Escuelas y Cursos
        if (method_exists($usuario, 'horariosMatriculados')) {
            $usuario->horariosMatriculados()->detach();
        }
        if (method_exists($usuario, 'cargoActividad')) {
            $usuario->cargoActividad()->detach();
        }

        // Consolidación
        if (method_exists($usuario, 'tareasConsolidacion')) {
            $usuario->tareasConsolidacion()->detach();
        }
    }

    /**
     * Elimina registros de tablas donde el usuario es el sujeto principal.
     */
    protected function eliminarRegistrosHijos(int $usuarioId): void
    {
        $tablasPorUserId = [
            'reporte_bajas_altas',
            'integrantes_grupo',
            'servidores_grupo',
            'asistencia_grupos',
            'asistencia_reuniones',
            'reservas_reuniones',
            'peticiones',
            'seguimientos_peticion',
            'tiempos_con_dios',
            'citas_consejeria',
            'consejeros',
            'intercesores',
            'matriculas',
            'reportes_asistencia_alumnos',
            'materias_aprobada_usuario',
            'niveles_aprobado_usuario',
            'alumno_respuesta_items',
            'maestros',
            'curso_users',
            'curso_item_user',
            'curso_evaluacion_resultados',
            'curso_foro_respuestas',
            'curso_foro_hilos',
            'carritos_curso_user',
            'compras',
            'inscripciones',
            'actividad_carritos_compra',
            'actividad_asistencias',
            'actividad_asistencias_inscripcion',
            'actividad_encargados_cargo',
            'actividad_campos_adicionales_compra',
            'respuestas_formulario_elemento_compra',
            'ofrendas',
            'ingresos',
            'cajas',
            'asesores_pdp',
            'transacciones_puntos',
            'solicitudes_canje',
            'plan_lector_users',
            'plan_lector_dia_users',
            'posts',
            'post_user_likes',
            'hito_usuario',
            'hito_likes',
            'hito_denuncias',
            'bitacora_integrantes_grupo',
            'bitacora_estados_civiles',
            'bitacora_tipos_usuarios',
            'bitacora_sedes',
            'bitacora_sedes_del_grupo',
            'bitacora_tipos_grupo',
            'bitacora_crecimiento_usuario',
            'bitacora_tareas_consolidacion',
            'historiales_tarea_consolidacion_usuario',
            'tarea_consolidacion_usuario',
            'novedades_actividad',
            'sessions',
            'email_change_requests',
        ];

        foreach ($tablasPorUserId as $tabla) {
            $this->borrarPorColumnaSiExiste($tabla, 'user_id', $usuarioId);
        }

        // Tablas que usan 'usuario_id'
        $tablasPorUsuarioId = [
            'matriculas_nivel',
            'rueda_de_la_vida_user',
            'curso_usuario_cargo',
        ];

        foreach ($tablasPorUsuarioId as $tabla) {
            $this->borrarPorColumnaSiExiste($tabla, 'usuario_id', $usuarioId);
        }

        // Casos particulares donde el id de usuario está en otras columnas específicas:
        // 1. Iglesia Infantil (como menor)
        $this->borrarPorColumnaSiExiste('registros_iglesia_infantil', 'menor_user_id', $usuarioId);

        // 2. Parientes en ambos sentidos
        if (Schema::hasTable('parientes_usuarios')) {
            DB::table('parientes_usuarios')
                ->where('user_id', $usuarioId)
                ->orWhere('pariente_user_id', $usuarioId)
                ->delete();
        }

        // 3. Respuestas de compras por pariente o respuesta_unica
        if (Schema::hasTable('respuestas_formulario_elemento_compra')) {
            DB::table('respuestas_formulario_elemento_compra')
                ->where('pariente_usuario_id', $usuarioId)
                ->orWhere('respuesta_unica', $usuarioId)
                ->delete();
        }

        // 4. Historial de pagos donde fue el usuario afectado
        $this->borrarPorColumnaSiExiste('historial_modificacion_pagos', 'usuario_afectado_id', $usuarioId);

        // 5. Planes lectores creados por el usuario
        $this->borrarPorColumnaSiExiste('planes_lectores', 'autor_id', $usuarioId);

        // 6. Sanctum tokens y WebPush subscriptions
        $this->borrarMorficosSiExiste('personal_access_tokens', 'tokenable_type', 'tokenable_id', $usuarioId);
        $this->borrarMorficosSiExiste('push_subscriptions', 'subscribable_type', 'subscribable_id', $usuarioId);
    }

    /**
     * Anula (coloca NULL) o reasigna campos donde el usuario actuó como auditor, autor o responsable.
     */
    protected function anularAuditoriasTerceros(int $usuarioId): void
    {
        $camposAnulables = [
            // Usuarios y grupos
            ['tabla' => 'users', 'columna' => 'usuario_creacion_id'],
            ['tabla' => 'grupos', 'columna' => 'usuario_creacion_id'],
            ['tabla' => 'informe_personalizados', 'columna' => 'usuario_creacion_id'],
            ['tabla' => 'megainformes', 'columna' => 'usuario_creacion_id'],

            // Auditorías congregacionales y reuniones
            ['tabla' => 'reservas_reuniones', 'columna' => 'responsable_id'],
            ['tabla' => 'reservas_reuniones', 'columna' => 'autor_creacion_reserva_id'],
            ['tabla' => 'reporte_reuniones', 'columna' => 'finalizado_por'],
            ['tabla' => 'asistencia_reuniones', 'columna' => 'autor_creacion_asistencia_id'],

            // Iglesia Infantil (adultos acompañantes y servidores)
            ['tabla' => 'registros_iglesia_infantil', 'columna' => 'adulto_ingreso_user_id'],
            ['tabla' => 'registros_iglesia_infantil', 'columna' => 'adulto_retiro_user_id'],
            ['tabla' => 'registros_iglesia_infantil', 'columna' => 'servidor_ingreso_user_id'],
            ['tabla' => 'registros_iglesia_infantil', 'columna' => 'servidor_retiro_user_id'],

            // Educación / Escuelas
            ['tabla' => 'materias_aprobada_usuario', 'columna' => 'homologado_por_user_id'],
            ['tabla' => 'alumno_respuesta_items', 'columna' => 'calificador_user_id'],
            ['tabla' => 'reportes_asistencia_alumnos', 'columna' => 'reportado_por_user_id'],

            // Finanzas y cajas
            ['tabla' => 'historial_modificacion_pagos', 'columna' => 'asesor_id'],
            ['tabla' => 'puntos_de_pago', 'columna' => 'encargado_id'],
            ['tabla' => 'ingresos', 'columna' => 'usuario_anulacion'],
            ['tabla' => 'ingresos', 'columna' => 'usuario_anulacion_id'],
            ['tabla' => 'ofrendas', 'columna' => 'usuario_anulacion'],
            ['tabla' => 'compras', 'columna' => 'usuario_anulacion'],

            // Consejería y Gamificación
            ['tabla' => 'citas_consejeria', 'columna' => 'cancelado_por'],
            ['tabla' => 'transacciones_puntos', 'columna' => 'creado_por_user_id'],
            ['tabla' => 'solicitudes_canje', 'columna' => 'procesado_por_user_id'],

            // Hitos y Actividades
            ['tabla' => 'hito_denuncias', 'columna' => 'resuelto_por'],
            ['tabla' => 'hito_usuario', 'columna' => 'asignado_por'],
            ['tabla' => 'novedades_actividad', 'columna' => 'respondido_por_id'],

            // Bitácoras
            ['tabla' => 'bitacora_tipos_usuarios', 'columna' => 'autor_id'],
            ['tabla' => 'bitacora_estados_civiles', 'columna' => 'autor_id'],
            ['tabla' => 'bitacora_sedes', 'columna' => 'autor_id'],
            ['tabla' => 'bitacora_sedes_del_grupo', 'columna' => 'autor_id'],
            ['tabla' => 'bitacora_tipos_grupo', 'columna' => 'autor_id'],
            ['tabla' => 'bitacora_integrantes_grupo', 'columna' => 'autor_id'],
            ['tabla' => 'bitacora_tareas_consolidacion', 'columna' => 'autor_id'],
        ];

        foreach ($camposAnulables as $meta) {
            if (Schema::hasTable($meta['tabla']) && Schema::hasColumn($meta['tabla'], $meta['columna'])) {
                DB::table($meta['tabla'])
                    ->where($meta['columna'], $usuarioId)
                    ->update([$meta['columna'] => null]);
            }
        }

        // Hitos administrativos: al ser user_id no nullable, reasignar al admin activo
        if (Schema::hasTable('hitos') && Schema::hasColumn('hitos', 'user_id')) {
            $adminId = auth()->id() ?? DB::table('users')->whereNull('deleted_at')->value('id');
            if ($adminId) {
                DB::table('hitos')
                    ->where('user_id', $usuarioId)
                    ->update(['user_id' => $adminId]);
            }
        }
    }

    /**
     * Cuenta parientes en la tabla parientes_usuarios.
     */
    protected function contarParientes(int $usuarioId): int
    {
        if (! Schema::hasTable('parientes_usuarios')) {
            return 0;
        }

        return DB::table('parientes_usuarios')
            ->where('user_id', $usuarioId)
            ->orWhere('pariente_user_id', $usuarioId)
            ->count();
    }

    /**
     * Cuenta registros de forma segura si la tabla y la columna existen.
     */
    protected function contarSiExiste(string $tabla, string $columna, int $id): int
    {
        if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columna)) {
            return 0;
        }

        return DB::table($tabla)->where($columna, $id)->count();
    }

    /**
     * Borra registros de forma segura si la tabla y la columna existen.
     */
    protected function borrarPorColumnaSiExiste(string $tabla, string $columna, int $id): void
    {
        if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, $columna)) {
            DB::table($tabla)->where($columna, $id)->delete();
        }
    }

    /**
     * Borra registros polimórficos de forma segura si la tabla existe.
     */
    protected function borrarMorficosSiExiste(string $tabla, string $columnaTipo, string $columnaId, int $id): void
    {
        if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, $columnaTipo) && Schema::hasColumn($tabla, $columnaId)) {
            DB::table($tabla)
                ->where($columnaTipo, User::class)
                ->where($columnaId, $id)
                ->delete();
        }
    }
}
