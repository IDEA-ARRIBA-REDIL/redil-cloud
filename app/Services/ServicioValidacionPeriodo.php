<?php

namespace App\Services;

use App\Models\Periodo;
use App\Models\Calificaciones;
use App\Models\MateriaAprobadaUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\Models\Materia;
use Carbon\Carbon;

/**
 * Servicio encargado de la lógica de negocio para finalizar un periodo académico.
 * Funciona procesando alumnos en lotes para ser más eficiente y evitar timeouts.
 */
class ServicioValidacionPeriodo
{
    /**
     * Orquesta el procesamiento de un único LOTE de alumnos para un periodo.
     * Este es el método principal que será llamado por el FinalizarPeriodoJob.
     *
     * @param Periodo $periodo El periodo que se está procesando.
     * @param int $pagina El número del lote actual (ej: 1, 2, 3...).
     * @param int $porPagina El tamaño de cada lote (ej: 200 alumnos por lote).
     * @return int El número de alumnos que fueron procesados en este lote.
     */
    public function procesarLoteDeAlumnos(Periodo $periodo, int $pagina, int $porPagina): int
    {
        // --- PASO 1: OBTENER LOS ALUMNOS DE ESTE LOTE ---
        Log::info("Servicio: Buscando alumnos para el lote {$pagina} (hasta {$porPagina} alumnos).");

        // Se obtiene una lista única de IDs de usuarios matriculados en el periodo, paginada.
        $idsAlumnosDelLote = DB::table('matriculas')
            ->where('periodo_id', $periodo->id)
            ->distinct()
            ->orderBy('user_id')
            ->offset(($pagina - 1) * $porPagina) // Calcula el punto de inicio del lote
            ->limit($porPagina) // Limita el número de resultados al tamaño del lote
            ->pluck('user_id');

        // Si la consulta no devuelve IDs, significa que ya no hay más alumnos por procesar.
        if ($idsAlumnosDelLote->isEmpty()) {
            Log::info("Servicio: No se encontraron más alumnos para el lote {$pagina}. Finalizando.");
            return 0; // Se devuelve 0 para indicarle al Job que debe detenerse.
        }
        Log::info("Servicio: Se procesarán " . $idsAlumnosDelLote->count() . " alumnos en este lote.");


        // --- PASO 2: OBTENER LOS DATOS ACADÉMICOS SOLO PARA ESE LOTE DE ALUMNOS ---
        $resultadosAcademicos = $this->obtenerResultadosAcademicos($periodo, $idsAlumnosDelLote);
        Log::info("Servicio: La consulta SQL para el lote se completó. Se encontraron " . count($resultadosAcademicos) . " registros de alumno/materia.");

        if (empty($resultadosAcademicos)) {
            Log::warning("No se encontraron datos académicos para los alumnos de este lote.");
            return $idsAlumnosDelLote->count(); // Devolvemos el conteo para que el job sepa que debe continuar con el siguiente lote.
        }


        // --- PASO 3: APLICAR LAS REGLAS DE NEGOCIO (igual que antes) ---
        $notaMinimaAprobacion = Calificaciones::where('sistema_calificacion_id', $periodo->sistema_calificaciones_id)
            ->where('aprobado', true)
            ->min('nota_minima');

        if (is_null($notaMinimaAprobacion)) {
            throw new \Exception("No se pudo encontrar la nota mínima de aprobación para el sistema de calificación ID: {$periodo->sistema_calificaciones_id}");
        }

        $datosParaUpsert = [];
        foreach ($resultadosAcademicos as $resultado) {
            $estadoFinal = $this->determinarEstadoFinal($resultado, (float) $notaMinimaAprobacion);

            $datosParaUpsert[] = [
                'user_id' => $resultado->user_id,
                'materia_id' => $resultado->materia_id,
                'materia_periodo_id' => $resultado->materia_periodo_id,
                'periodo_id' => $periodo->id,
                'nota_final' => $resultado->nota_final_calculada,
                'total_asistencias' => $resultado->total_asistencias,
                'aprobado' => $estadoFinal['aprobado'],
                'motivo_reprobacion' => $estadoFinal['motivo'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }


        // --- PASO 4: PERSISTIR LOS RESULTADOS DE ESTE LOTE (igual que antes) ---
        $this->persistirResultados($datosParaUpsert);

        // --- PASO 4.1: APLICAR EFECTOS COLATERALES DE APROBACIÓN (NUEVO) ---
        // Actualizar tareas de consolidación y pasos de crecimiento para los aprobados
        $this->aplicarEfectosCulminacion($datosParaUpsert);

        // --- PASO 5: DEVOLVER LA CANTIDAD DE ALUMNOS PROCESADOS ---
        // El Job usará este número para saber si debe continuar con el siguiente lote.
        return $idsAlumnosDelLote->count();
    }

    /**
     * Ejecuta la consulta SQL optimizada para un lote específico de IDs de alumnos.
     *
     * @param Periodo $periodo
     * @param Collection $idsAlumnos Colección de IDs de los alumnos a consultar.
     * @return array
     */
    private function obtenerResultadosAcademicos(Periodo $periodo, Collection $idsAlumnos): array
    {
        // Transforma la colección de IDs en un array plano para usar en la consulta.
        $idsArray = $idsAlumnos->toArray();
        // Crea los placeholders (?, ?, ?) para la cláusula WHERE IN de SQL.
        $placeholders = implode(',', array_fill(0, count($idsArray), '?'));

        $sql = "
            SELECT
                mat.user_id,
                mp.id AS materia_periodo_id, mp.materia_id,
                m.habilitar_calificaciones, m.habilitar_asistencias, m.asistencias_minimas,
                COALESCE(SUM(ari.nota_obtenida * (icp.porcentaje / 100.0) * (cp.porcentaje / 100.0)), 0) AS nota_final_calculada,
                (
                    SELECT COUNT(*) FROM reportes_asistencia_alumnos AS raa
                    JOIN reportes_asistencia_clase AS rac ON raa.reporte_asistencia_clase_id = rac.id
                    JOIN horarios_materia_periodo AS hmp_asistencia ON rac.horario_materia_periodo_id = hmp_asistencia.id
                    WHERE raa.user_id = mat.user_id AND hmp_asistencia.materia_periodo_id = mp.id AND raa.asistio = TRUE
                ) AS total_asistencias
            FROM matriculas AS mat
            JOIN horarios_materia_periodo AS hmp ON mat.horario_materia_periodo_id = hmp.id
            JOIN materia_periodo AS mp ON hmp.materia_periodo_id = mp.id
            JOIN materias AS m ON mp.materia_id = m.id
            LEFT JOIN item_corte_materia_periodo AS icp ON icp.horario_materia_periodo_id = hmp.id
            LEFT JOIN alumno_respuesta_items AS ari ON ari.item_corte_materia_periodo_id = icp.id AND ari.user_id = mat.user_id
            LEFT JOIN cortes_periodo AS cp ON icp.corte_periodo_id = cp.id
            WHERE mat.periodo_id = ? AND mat.user_id IN ({$placeholders}) -- <-- AQUÍ FILTRAMOS POR EL LOTE DE ALUMNOS
            GROUP BY
                mat.user_id, mp.id, mp.materia_id, m.habilitar_calificaciones, m.habilitar_asistencias, m.asistencias_minimas;
        ";

        // Los "bindings" son los valores que reemplazarán a los '?'.
        // Es importante unir el ID del periodo con el array de IDs de alumnos.
        $bindings = array_merge([$periodo->id], $idsArray);

        return DB::select($sql, $bindings);
    }

    /**
     * Aplica las reglas de negocio para determinar si un alumno aprueba.
     * (Este método no necesita cambios)
     */
    private function determinarEstadoFinal(object $resultado, float $notaMinima): array
    {
        $aproboPorNota = true;
        $aproboPorAsistencia = true;
        $motivos = [];
        if ($resultado->habilitar_calificaciones && $resultado->nota_final_calculada < $notaMinima) {
            $aproboPorNota = false;
            $motivos[] = 'NOTA_INSUFICIENTE';
        }
        if ($resultado->habilitar_asistencias && $resultado->total_asistencias < $resultado->asistencias_minimas) {
            $aproboPorAsistencia = false;
            $motivos[] = 'ASISTENCIA_INSUFICIENTE';
        }
        return ['aprobado' => $aproboPorNota && $aproboPorAsistencia, 'motivo' => empty($motivos) ? null : implode(', ', $motivos)];
    }

    /**
     * Guarda los resultados finales en la BD con validación manual para evitar operaciones innecesarias.
     *
     * @param array $datosCalculados El conjunto completo de resultados calculados.
     */
    private function persistirResultados(array $datosCalculados): void
    {
        if (empty($datosCalculados)) {
            return;
        }

        Log::info("Servicio: Iniciando persistencia manual para " . count($datosCalculados) . " registros.");

        // --- PASO 1: Obtener todos los registros que YA existen para este periodo ---
        // Hacemos UNA sola consulta para traer todos los registros existentes a memoria.
        $periodoId = $datosCalculados[0]['periodo_id'];
        $registrosExistentes = MateriaAprobadaUsuario::where('periodo_id', $periodoId)
            ->get()
            // Creamos un "mapa" para búsquedas súper rápidas, usando una clave compuesta.
            ->keyBy(function ($item) {
                return $item->user_id . '-' . $item->materia_periodo_id;
            });

        Log::info("Se encontraron " . $registrosExistentes->count() . " registros existentes en la BD para este periodo.");

        // --- PASO 2: Separar los datos en "para insertar" y "para actualizar" ---
        $paraInsertar = [];
        $paraActualizar = [];

        foreach ($datosCalculados as $dato) {
            $clave = $dato['user_id'] . '-' . $dato['materia_periodo_id'];

            // Comprobamos si el registro ya existe en nuestro "mapa"
            if (isset($registrosExistentes[$clave])) {
                $registroExistente = $registrosExistentes[$clave];

                // Comparamos si la nota O las asistencias han cambiado.
                // Usamos una comparación no estricta para floats por posibles problemas de precisión.
                if (
                    abs($registroExistente->nota_final - $dato['nota_final']) > 0.001 ||
                    $registroExistente->total_asistencias != $dato['total_asistencias']
                ) {
                    // Si algo cambió, lo añadimos a la lista de registros a actualizar.
                    $paraActualizar[] = $dato;
                }
                // Si no hay cambios, simplemente lo ignoramos.
            } else {
                // Si no existe en nuestro mapa, es un registro nuevo.
                $paraInsertar[] = $dato;
            }
        }

        Log::info("Análisis completado: " . count($paraInsertar) . " registros para insertar, " . count($paraActualizar) . " para actualizar.");

        // --- PASO 3: Ejecutar las operaciones en la base de datos ---

        // Insertamos todos los registros nuevos en una sola operación masiva.
        if (!empty($paraInsertar)) {
            // Usamos insert para mayor rendimiento, ya que son datos nuevos.
            foreach (array_chunk($paraInsertar, 500) as $chunk) {
                MateriaAprobadaUsuario::insert($chunk);
            }
            Log::info("Se insertaron " . count($paraInsertar) . " nuevos registros.");
        }

        // Actualizamos los registros que cambiaron, uno por uno.
        // Aunque es un bucle, solo se ejecuta para los registros que REALMENTE cambiaron.
        if (!empty($paraActualizar)) {
            foreach ($paraActualizar as $datoActualizar) {
                MateriaAprobadaUsuario::where('user_id', $datoActualizar['user_id'])
                    ->where('materia_periodo_id', $datoActualizar['materia_periodo_id'])
                    ->update([
                        'nota_final' => $datoActualizar['nota_final'],
                        'total_asistencias' => $datoActualizar['total_asistencias'],
                        'aprobado' => $datoActualizar['aprobado'],
                        'motivo_reprobacion' => $datoActualizar['motivo_reprobacion'],
                        'updated_at' => now(),
                    ]);
            }
            Log::info("Se actualizaron " . count($paraActualizar) . " registros existentes.");
        }
    }

    /**
     * ===== MÉTODO NUEVO =====
     * Cierra administrativamente todos los componentes asociados a un periodo.
     * Marca todas las materias como 'finalizadas' y todos los cortes como 'cerrados'.
     *
     * @param Periodo $periodo El periodo a finalizar.
     * @return void
     */
    public function finalizarComponentesDelPeriodo(Periodo $periodo): void
    {
        Log::info("Servicio: Finalizando componentes para el periodo ID: {$periodo->id} '{$periodo->nombre}'.");

        // 1. Cerrar todos los cortes del periodo en una sola consulta.
        // El método update() sobre la relación devuelve el número de filas afectadas.
        $cortesAfectados = $periodo->cortesPeriodo;

        foreach ($cortesAfectados as $corte) {
            $corte->cerrado = true;
            $corte->save();
        }
        Log::info("Se cerraron {$cortesAfectados} cortes del periodo.");

        // 2. Finalizar todas las materias del periodo en una sola consulta.
        $materiasAfectadas = $periodo->materiasPeriodo;

        foreach ($materiasAfectadas as $materia) {
            $materia->finalizada = true;
            $materia->save();
        }

        Log::info("Se finalizaron {$materiasAfectadas} materias del periodo.");
    }

    /**
     * Aplica los cambios en Tareas de Consolidación y Pasos de Crecimiento
     * para los alumnos que han aprobado sus materias en este lote.
     *
     * @param array $datosCalculados resultados del procesamiento del lote.
     */
    private function aplicarEfectosCulminacion(array $datosCalculados): void
    {
        // 1. Filtramos solo los registros APROBADOS
        $aprobados = array_filter($datosCalculados, function ($dato) {
            return $dato['aprobado'] === true;
        });

        if (empty($aprobados)) {
            return;
        }

        Log::info("Servicio: Aplicando efectos de culminación para " . count($aprobados) . " aprobaciones.");

        // 2. Cargamos la configuración de las materias involucradas
        //    (Tareas a culminar, Pasos a culminar y ahora también TipoUsuarioObjetivo con Rol Dependiente)
        $materiaIds = collect($aprobados)->pluck('materia_id')->unique();
        
        $materiasConfig = Materia::whereIn('id', $materiaIds)
            ->with([
                'tipoUsuarioObjetivo' => function ($query) {
                    // Seleccionamos los campos necesarios, principalmente el rol dependiente
                    $query->select('id', 'puntaje', 'id_rol_dependiente'); 
                },
                'tareasCulminadas', 
                'pasosCrecimiento' => function ($query) {
                    $query->wherePivot('al_iniciar', false);
                }
            ])
            ->get()
            ->keyBy('id');

        // 3. Iteramos y aplicamos cambios (Upserts para eficiencia)
        foreach ($aprobados as $dato) {
            $materiaId = $dato['materia_id'];
            $userId = $dato['user_id'];
            
            $materia = $materiasConfig->get($materiaId);
            if (!$materia) continue;

            // --- A. Actualizar Tareas de Consolidación ---
            foreach ($materia->tareasCulminadas as $tareaConfig) {
                // Usamos updateOrInsert para asegurar idempotencia
                DB::table('tarea_consolidacion_usuario')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'tarea_consolidacion_id' => $tareaConfig->tarea_consolidacion_id
                    ],
                    [
                        'estado_tarea_consolidacion_id' => $tareaConfig->estado_tarea_consolidacion_id,
                        'updated_at' => now(),
                        // Nota: Si es insert, created_at quedará null o default. 
                        // Si es crítico, se debería usar raw query o manejarlo diferente.
                        // Para este caso, updated_at es suficiente señal de cambio.
                    ]
                );
            }

            // --- B. Actualizar Pasos de Crecimiento ---
            foreach ($materia->pasosCrecimiento as $pasoConfig) {
                // El estado objetivo está en la tabla pivote de la configuración de la materia
                $estadoObjetivoId = $pasoConfig->pivot->estado_paso_crecimiento_usuario_id;
                
                if ($estadoObjetivoId) {
                    DB::table('crecimiento_usuario')->updateOrInsert(
                        [
                            'user_id' => $userId,
                            'paso_crecimiento_id' => $pasoConfig->id
                        ],
                        [
                            'estado_id' => $estadoObjetivoId,
                            'fecha' => now(), // Fecha de cumplimiento: Ahora
                            'updated_at' => now()
                        ]
                    );
                }
            }

            // --- C. Actualizar Tipo de Usuario y Roles (NUEVO) ---
            if ($materia->tipo_usuario_objetivo_id) {
                // Obtenemos el usuario de la DB para conocer su tipo actual
                $usuario = \App\Models\User::with('tipoUsuario')->find($userId);
                $tipoObjetivo = $materia->tipoUsuarioObjetivo;
                
                if ($usuario && $tipoObjetivo) {
                    // 1. Validar Jerarquía por Puntaje
                    $puntajeActual = $usuario->tipoUsuario ? $usuario->tipoUsuario->puntaje : 0;
                    $puntajeObjetivo = $tipoObjetivo->puntaje;

                    // Si el usuario tiene mayor o igual rango que el objetivo, NO hacemos nada.
                    // (Cambio aquí: <= permite ascender si es igual puntaje pero difiere en algo más, o < estricto)
                    // Siguiendo la lógica de asistencia: solo si actual <= objetivo (lo cual incluye re-setear iguales)
                    // Pero usualmente se quiere ascender. Aquí usaremos logic estricta: Si puntajeActual <= puntajeObjetivo, aplicamos.
                    // Si ya tiene un puntaje SUPERIOR, no lo degradamos.
                    if ($puntajeActual <= $puntajeObjetivo) {
                        
                        Log::info("Servicio: Actualizando Tipo de Usuario para User ID {$userId}. Objetivo: {$tipoObjetivo->id}");

                        // 2. Actualizar Tipo de Usuario en tabla users
                        // Evitamos disparar eventos de Eloquent masivos si podemos, pero User::update dispara observers (Bitacora).
                        // Es MEJOR usar el modelo para que la bitácora funcione.
                        $usuario->update(['tipo_usuario_id' => $tipoObjetivo->id]);

                        // 3. Gestión de Roles (Transacción anidada es segura en Laravel o parte de la global)
                        $nuevoRolId = $tipoObjetivo->id_rol_dependiente;

                        if ($nuevoRolId) {
                            // A. Desactivar TODOS los roles actuales
                            DB::table('model_has_roles')
                                ->where('model_id', $usuario->id)
                                ->where('model_type', 'App\Models\User')
                                ->update(['activo' => false]);

                            // B. Obtener IDs de roles dependientes para eliminar
                            $rolesDependientesIds = \App\Models\Role::where('dependiente', true)->pluck('id');

                            // C. Eliminar conexiones con roles dependientes antiguos
                            if ($rolesDependientesIds->isNotEmpty()) {
                                DB::table('model_has_roles')
                                    ->where('model_id', $usuario->id)
                                    ->where('model_type', 'App\Models\User')
                                    ->whereIn('role_id', $rolesDependientesIds)
                                    ->delete();
                            }

                            // D. Asignar nuevo rol dependiente activo
                            // Usamos insert o updateOrInsert directo para evitar problema de caché aqui mismo o duplicados
                            // Ojo: attach() puede fallar si ya existe. Usamos insertIgnore o check.
                            $existeRelacion = DB::table('model_has_roles')
                                ->where('model_id', $usuario->id)
                                ->where('role_id', $nuevoRolId)
                                ->exists();

                            if (!$existeRelacion) {
                                DB::table('model_has_roles')->insert([
                                    'role_id' => $nuevoRolId,
                                    'model_type' => 'App\Models\User',
                                    'model_id' => $usuario->id,
                                    'activo' => true
                                ]);
                            } else {
                                DB::table('model_has_roles')
                                    ->where('model_id', $usuario->id)
                                    ->where('role_id', $nuevoRolId)
                                    ->update(['activo' => true]);
                            }
                            
                            // Limpieza de caché de permisos necesaria
                            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                        }
                    }
                }
            }
        }
    }
}
