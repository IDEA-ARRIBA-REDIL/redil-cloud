<?php

namespace App\Services;

use App\Models\ReporteReunion;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReunionStatsService
{
    /**
     * Genera el conjunto completo de métricas del dashboard según los filtros y permisos del usuario.
     */
    public function generarDashboard(array $filtros, User $usuario): array
    {
        $rolActivo = $usuario->roles()->wherePivot('activo', true)->first();

        // 1. Resolver sedes autorizadas para el usuario
        $sedesAutorizadasIds = $this->obtenerSedesAutorizadasIds($usuario, $rolActivo);

        // 2. Normalizar fechas
        $fechaInicio = ! empty($filtros['fecha_inicio'])
            ? Carbon::parse($filtros['fecha_inicio'])->format('Y-m-d')
            : Carbon::now()->subDays(30)->format('Y-m-d');

        $fechaFin = ! empty($filtros['fecha_fin'])
            ? Carbon::parse($filtros['fecha_fin'])->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        $estado = $filtros['estado'] ?? ReporteReunion::ESTADO_FINALIZADO;

        // 3. Filtrar sedes organizadoras solicitadas respetando autorización
        $sedesFiltro = ! empty($filtros['sedes_id']) ? (array) $filtros['sedes_id'] : [];
        if (! empty($sedesFiltro)) {
            $sedesEfectivasIds = array_values(array_intersect($sedesFiltro, $sedesAutorizadasIds));
        } else {
            $sedesEfectivasIds = $sedesAutorizadasIds;
        }

        // Si el usuario no tiene sedes asignadas, retornar estructura vacía
        if (empty($sedesEfectivasIds)) {
            return $this->estructuraVacia($fechaInicio, $fechaFin, $estado);
        }

        $reunionesFiltro = ! empty($filtros['reuniones_id']) ? (array) $filtros['reuniones_id'] : [];
        $tiposFiltro = ! empty($filtros['tipos_reunion_id']) ? (array) $filtros['tipos_reunion_id'] : [];

        // 4. Query base de reportes
        $reportesQuery = ReporteReunion::query()
            ->join('reuniones', 'reuniones.id', '=', 'reporte_reuniones.reunion_id')
            ->whereBetween('reporte_reuniones.fecha', [$fechaInicio, $fechaFin])
            ->whereIn('reuniones.sede_id', $sedesEfectivasIds);

        if (! empty($estado) && $estado !== 'todos') {
            $reportesQuery->where('reporte_reuniones.estado', $estado);
        }

        if (! empty($reunionesFiltro)) {
            $reportesQuery->whereIn('reporte_reuniones.reunion_id', $reunionesFiltro);
        }

        if (! empty($tiposFiltro)) {
            $reportesQuery->whereIn('reuniones.tipo_servicio_reporte_reunion_id', $tiposFiltro);
        }

        $reportesIds = (clone $reportesQuery)->pluck('reporte_reuniones.id')->toArray();

        // 5. KPIs Generales
        $kpis = $this->calcularKpisGenerales($reportesQuery, $reportesIds);

        // 6. Desglose comparativo por reunión
        $tablaReuniones = $this->calcularTablaPorReunion($reportesIds, $sedesEfectivasIds);

        // 7. Bloques por Sede Organizadora
        $bloquesPorSede = $this->calcularBloquesPorSede($reportesIds, $sedesEfectivasIds);

        // 8. Composición Demográfica
        $demografia = $this->calcularDemografia($reportesIds);

        // 9. Tendencia temporal
        $tendencia = $this->calcularTendenciaTemporal($reportesIds);

        // 10. Alertas de calidad de datos
        $alertas = $this->detectarAlertasCalidad($fechaInicio, $fechaFin, $sedesEfectivasIds, $reportesIds);

        return [
            'filtros_aplicados' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
                'sedes_id' => $sedesEfectivasIds,
                'reuniones_id' => $reunionesFiltro,
                'tipos_reunion_id' => $tiposFiltro,
            ],
            'kpis' => $kpis,
            'tabla_reuniones' => $tablaReuniones,
            'bloques_sede' => $bloquesPorSede,
            'demografia' => $demografia,
            'tendencia' => $tendencia,
            'alertas' => $alertas,
        ];
    }

    /**
     * Resuelve las sedes permitidas según permisos Spatie y jerarquía de roles.
     */
    public function obtenerSedesAutorizadasIds(User $usuario, $rolActivo): array
    {
        // 1. Si tiene permiso para ver todas las sedes y no tiene una sede fija en su rol
        if ($rolActivo && $rolActivo->lista_sedes_sede_id) {
            return [$rolActivo->lista_sedes_sede_id];
        }

        if ($rolActivo && $rolActivo->hasPermissionTo('sedes.lista_sedes_todas')) {
            return Sede::pluck('id')->toArray();
        }

        // 2. Si tiene alcance solo ministerio o sedes encargadas
        $sedesEncargadas = $usuario->sedesEncargadas('array');
        if (! empty($sedesEncargadas)) {
            return $sedesEncargadas;
        }

        // 3. Fallback: sede del usuario
        return $usuario->sede_id ? [$usuario->sede_id] : [];
    }

    /**
     * Calcula los KPIs consolidados del bloque general.
     */
    protected function calcularKpisGenerales($reportesQuery, array $reportesIds): array
    {
        $totalReportes = count($reportesIds);
        if ($totalReportes === 0) {
            return [
                'total_reportes' => 0,
                'asistencias_acumuladas' => 0,
                'asistencias_miembros' => 0,
                'invitados_totales' => 0,
                'personas_unicas' => 0,
                'promedio_asistencias' => 0,
                'cobertura_congregacion' => 0,
                'poblacion_elegible_acumulada' => 0,
                'aforo_total' => 0,
                'ocupacion_aforo' => 0,
            ];
        }

        // Suma de asistencias de miembros registrados
        $asistenciasMiembros = DB::table('asistencia_reuniones')
            ->whereIn('reporte_reunion_id', $reportesIds)
            ->count();

        // Personas únicas registradas en el período
        $personasUnicas = DB::table('asistencia_reuniones')
            ->whereIn('reporte_reunion_id', $reportesIds)
            ->distinct('user_id')
            ->count('user_id');

        // Invitados confirmados
        $invitadosReservas = DB::table('reservas_reuniones')
            ->whereIn('reporte_reunion_id', $reportesIds)
            ->where('invitado', true)
            ->where('registrada', true)
            ->count();

        // Fallback de invitados por contador del reporte si no hay reservas de invitados
        $invitadosReportes = (clone $reportesQuery)->sum('reporte_reuniones.invitados') ?? 0;
        $invitadosTotales = max($invitadosReservas, (int) $invitadosReportes);

        $asistenciasAcumuladas = $asistenciasMiembros + $invitadosTotales;
        $promedioPorReporte = round($asistenciasAcumuladas / max($totalReportes, 1), 1);

        // Cobertura congregacional ponderada (solo miembros sobre población elegible)
        $poblacionElegibleAcumulada = (int) ((clone $reportesQuery)->sum('reporte_reuniones.poblacion_elegible_historica') ?? 0);
        $cobertura = $poblacionElegibleAcumulada > 0
            ? round(($asistenciasMiembros / $poblacionElegibleAcumulada) * 100, 1)
            : 0;

        // Aforo
        $aforoTotal = (int) ((clone $reportesQuery)->whereNotNull('reporte_reuniones.aforo')->sum('reporte_reuniones.aforo') ?? 0);
        $ocupacionAforo = $aforoTotal > 0
            ? round(($asistenciasAcumuladas / $aforoTotal) * 100, 1)
            : 0;

        return [
            'total_reportes' => $totalReportes,
            'asistencias_acumuladas' => $asistenciasAcumuladas,
            'asistencias_miembros' => $asistenciasMiembros,
            'invitados_totales' => $invitadosTotales,
            'personas_unicas' => $personasUnicas,
            'promedio_asistencias' => $promedioPorReporte,
            'cobertura_congregacion' => $cobertura,
            'poblacion_elegible_acumulada' => $poblacionElegibleAcumulada,
            'aforo_total' => $aforoTotal,
            'ocupacion_aforo' => $ocupacionAforo,
        ];
    }

    /**
     * Tabla comparativa fila a fila por cada reunión.
     */
    protected function calcularTablaPorReunion(array $reportesIds, array $sedesAutorizadasIds): array
    {
        if (empty($reportesIds)) {
            return [];
        }

        $reunionesData = ReporteReunion::query()
            ->join('reuniones', 'reuniones.id', '=', 'reporte_reuniones.reunion_id')
            ->leftJoin('sedes', 'sedes.id', '=', 'reuniones.sede_id')
            ->leftJoin('tipo_servicios_reporte_reunion as ts', 'ts.id', '=', 'reuniones.tipo_servicio_reporte_reunion_id')
            ->whereIn('reporte_reuniones.id', $reportesIds)
            ->select(
                'reuniones.id as reunion_id',
                'reuniones.nombre as reunion_nombre',
                'sedes.id as sede_id',
                'sedes.nombre as sede_nombre',
                'ts.nombre as tipo_servicio_nombre',
                DB::raw('COUNT(reporte_reuniones.id) as total_reportes'),
                DB::raw('COALESCE(SUM(reporte_reuniones.poblacion_elegible_historica), 0) as poblacion_elegible_total'),
                DB::raw('COALESCE(SUM(reporte_reuniones.invitados), 0) as invitados_reporte')
            )
            ->groupBy('reuniones.id', 'reuniones.nombre', 'sedes.id', 'sedes.nombre', 'ts.nombre')
            ->orderBy('sedes.nombre')
            ->orderBy('reuniones.nombre')
            ->get();

        $resultado = [];

        foreach ($reunionesData as $row) {
            // Reportes de esta reunión específica
            $reunionReportesIds = ReporteReunion::whereIn('id', $reportesIds)
                ->where('reunion_id', $row->reunion_id)
                ->pluck('id')
                ->toArray();

            $miembros = DB::table('asistencia_reuniones')
                ->whereIn('reporte_reunion_id', $reunionReportesIds)
                ->count();

            $unicas = DB::table('asistencia_reuniones')
                ->whereIn('reporte_reunion_id', $reunionReportesIds)
                ->distinct('user_id')
                ->count('user_id');

            $invitados = (int) $row->invitados_reporte;
            $totalAsistencias = $miembros + $invitados;
            $promedio = $row->total_reportes > 0 ? round($totalAsistencias / $row->total_reportes, 1) : 0;

            $cobertura = $row->poblacion_elegible_total > 0
                ? round(($miembros / $row->poblacion_elegible_total) * 100, 1)
                : 0;

            $resultado[] = [
                'reunion_id' => $row->reunion_id,
                'reunion_nombre' => $row->reunion_nombre,
                'sede_id' => $row->sede_id,
                'sede_nombre' => $row->sede_nombre ?? 'Sin sede asignada',
                'tipo_servicio' => $row->tipo_servicio_nombre ?? 'General',
                'reportes_count' => (int) $row->total_reportes,
                'asistencias_totales' => $totalAsistencias,
                'asistencias_miembros' => $miembros,
                'invitados' => $invitados,
                'personas_unicas' => $unicas,
                'promedio_reporte' => $promedio,
                'cobertura' => $cobertura,
                'poblacion_elegible' => (int) $row->poblacion_elegible_total,
            ];
        }

        return $resultado;
    }

    /**
     * Desglose agrupado por cada Sede Organizadora.
     */
    protected function calcularBloquesPorSede(array $reportesIds, array $sedesAutorizadasIds): array
    {
        if (empty($reportesIds)) {
            return [];
        }

        $sedes = Sede::whereIn('id', $sedesAutorizadasIds)->orderBy('nombre')->get();
        $bloques = [];

        foreach ($sedes as $sede) {
            // Reportes organizados por esta sede
            $sedeReportesIds = ReporteReunion::join('reuniones', 'reuniones.id', '=', 'reporte_reuniones.reunion_id')
                ->whereIn('reporte_reuniones.id', $reportesIds)
                ->where('reuniones.sede_id', $sede->id)
                ->pluck('reporte_reuniones.id')
                ->toArray();

            if (empty($sedeReportesIds)) {
                continue;
            }

            $querySede = ReporteReunion::whereIn('id', $sedeReportesIds);
            $kpisSede = $this->calcularKpisGenerales($querySede, $sedeReportesIds);
            $tablaReunionesSede = $this->calcularTablaPorReunion($sedeReportesIds, [$sede->id]);

            $bloques[] = [
                'sede_id' => $sede->id,
                'sede_nombre' => $sede->nombre,
                'kpis' => $kpisSede,
                'reuniones' => $tablaReunionesSede,
            ];
        }

        return $bloques;
    }

    /**
     * Calcula la distribución por Tipo de Usuario y por Género.
     */
    protected function calcularDemografia(array $reportesIds): array
    {
        if (empty($reportesIds)) {
            return [
                'por_tipo_usuario' => [],
                'por_genero' => [],
            ];
        }

        // 1. Por Tipo de Usuario
        $porTipo = DB::table('asistencia_reuniones')
            ->join('users', 'users.id', '=', 'asistencia_reuniones.user_id')
            ->leftJoin('tipo_usuarios', 'tipo_usuarios.id', '=', 'users.tipo_usuario_id')
            ->whereIn('asistencia_reuniones.reporte_reunion_id', $reportesIds)
            ->select(
                DB::raw("COALESCE(tipo_usuarios.nombre, 'Sin clasificar') as tipo_nombre"),
                DB::raw('COUNT(asistencia_reuniones.id) as total')
            )
            ->groupBy('tipo_usuarios.nombre')
            ->orderByDesc('total')
            ->get();

        $totalMiembros = DB::table('asistencia_reuniones')->whereIn('reporte_reunion_id', $reportesIds)->count();

        $demografiaTipo = [];
        foreach ($porTipo as $item) {
            $pct = $totalMiembros > 0 ? round(($item->total / $totalMiembros) * 100, 1) : 0;
            $demografiaTipo[] = [
                'etiqueta' => $item->tipo_nombre,
                'total' => (int) $item->total,
                'porcentaje' => $pct,
            ];
        }

        // 2. Por Género
        $porGenero = DB::table('asistencia_reuniones')
            ->join('users', 'users.id', '=', 'asistencia_reuniones.user_id')
            ->whereIn('asistencia_reuniones.reporte_reunion_id', $reportesIds)
            ->select(
                'users.genero',
                DB::raw('COUNT(asistencia_reuniones.id) as total')
            )
            ->groupBy('users.genero')
            ->get();

        $demografiaGenero = [];
        foreach ($porGenero as $g) {
            $label = match ($g->genero) {
                'M', 'm', 'Masculino', 'masculino', 0 => 'Hombres',
                'F', 'f', 'Femenino', 'femenino', 1 => 'Mujeres',
                default => 'Sin especificar',
            };

            $pct = $totalMiembros > 0 ? round(($g->total / $totalMiembros) * 100, 1) : 0;
            $demografiaGenero[] = [
                'etiqueta' => $label,
                'total' => (int) $g->total,
                'porcentaje' => $pct,
            ];
        }

        return [
            'por_tipo_usuario' => $demografiaTipo,
            'por_genero' => $demografiaGenero,
            'total_analizado' => $totalMiembros,
        ];
    }

    /**
     * Serie cronológica de asistencia por fecha.
     */
    protected function calcularTendenciaTemporal(array $reportesIds): array
    {
        if (empty($reportesIds)) {
            return [];
        }

        return ReporteReunion::query()
            ->join('reuniones', 'reuniones.id', '=', 'reporte_reuniones.reunion_id')
            ->whereIn('reporte_reuniones.id', $reportesIds)
            ->select(
                'reporte_reuniones.id',
                'reporte_reuniones.fecha',
                'reuniones.nombre as reunion_nombre',
                'reporte_reuniones.cantidad_asistencias as total',
                'reporte_reuniones.invitados'
            )
            ->orderBy('reporte_reuniones.fecha')
            ->get()
            ->map(function ($r) {
                return [
                    'fecha' => $r->fecha,
                    'reunion' => $r->reunion_nombre,
                    'asistencias' => (int) $r->total,
                    'invitados' => (int) $r->invitados,
                ];
            })
            ->toArray();
    }

    /**
     * Alertas sobre discrepancias o inconsistencias en los datos para el usuario.
     */
    protected function detectarAlertasCalidad(string $fechaInicio, string $fechaFin, array $sedesIds, array $reportesIds): array
    {
        $alertas = [];

        // 1. Reportes en el rango que no están finalizados
        $noFinalizados = ReporteReunion::join('reuniones', 'reuniones.id', '=', 'reporte_reuniones.reunion_id')
            ->whereBetween('reporte_reuniones.fecha', [$fechaInicio, $fechaFin])
            ->whereIn('reuniones.sede_id', $sedesIds)
            ->where('reporte_reuniones.estado', '!=', ReporteReunion::ESTADO_FINALIZADO)
            ->count();

        if ($noFinalizados > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "Hay {$noFinalizados} reporte(s) en este período que aún no están finalizados y por ello no se incluyen en las estadísticas oficiales.",
            ];
        }

        // 2. Reportes finalizados sin ninguna asistencia registrada
        if (! empty($reportesIds)) {
            $sinAsistencia = ReporteReunion::whereIn('id', $reportesIds)
                ->where('cantidad_asistencias', 0)
                ->count();

            if ($sinAsistencia > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'mensaje' => "Se identificaron {$sinAsistencia} reporte(s) finalizados con 0 asistencias registradas.",
                ];
            }
        }

        return $alertas;
    }

    /**
     * Estructura predeterminada en caso de no existir datos.
     */
    protected function estructuraVacia(string $fechaInicio, string $fechaFin, string $estado): array
    {
        return [
            'filtros_aplicados' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
                'sedes_id' => [],
                'reuniones_id' => [],
                'tipos_reunion_id' => [],
            ],
            'kpis' => [
                'total_reportes' => 0,
                'asistencias_acumuladas' => 0,
                'asistencias_miembros' => 0,
                'invitados_totales' => 0,
                'personas_unicas' => 0,
                'promedio_asistencias' => 0,
                'cobertura_congregacion' => 0,
                'poblacion_elegible_acumulada' => 0,
                'aforo_total' => 0,
                'ocupacion_aforo' => 0,
            ],
            'tabla_reuniones' => [],
            'bloques_sede' => [],
            'demografia' => [
                'por_tipo_usuario' => [],
                'por_genero' => [],
                'total_analizado' => 0,
            ],
            'tendencia' => [],
            'alertas' => [],
        ];
    }
}
