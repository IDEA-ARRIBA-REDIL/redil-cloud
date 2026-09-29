<?php

namespace App\Services;

use App\Models\AlumnoRespuestaItem;
use App\Models\Calificaciones;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\MateriaAprobadaUsuario;
use App\Models\MateriaPeriodo;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\ReporteAsistenciaAlumnos;
use Illuminate\Support\Collection;

class ResumenPeriodoService
{
    /**
     * Construye una lectura del periodo sin consolidar notas ni ejecutar efectos de aprobación.
     *
     * @return array<string, mixed>
     */
    public function obtener(Periodo $periodo): array
    {
        $periodo->loadMissing(['escuela', 'nivelesPeriodo.nivelEscuela', 'materiasPeriodo.materia', 'materiasPeriodo.nivel', 'materiasPeriodo.horariosMateriaPeriodo']);
        $periodo->materiasPeriodo->loadMissing(['horariosMateriaPeriodo.horarioBase' => fn ($consulta) => $consulta->withTrashed()->with(['aula' => fn ($aulas) => $aulas->withTrashed()->with('sede')])]);
        $materias = $periodo->materiasPeriodo->keyBy('id');
        $estadosCierre = app(GestionCierreMateriaService::class)->estados($materias->keys()->all());
        $horarios = $materias->flatMap->horariosMateriaPeriodo->keyBy('id');
        $matriculas = $periodo->matriculas()->with('user:id,genero')
            ->whereNotIn('estado_pago_matricula', ['anulada', 'rechazada'])
            ->get(['id', 'user_id', 'periodo_id', 'horario_materia_periodo_id', 'bloqueado']);
        $notaMinima = Calificaciones::query()
            ->where('sistema_calificacion_id', $periodo->sistema_calificaciones_id)
            ->where('aprobado', true)->min('nota_minima');

        $items = ItemCorteMateriaPeriodo::query()
            ->whereIn('horario_materia_periodo_id', $horarios->keys())
            ->whereIn('materia_periodo_id', $materias->modelKeys())
            ->whereHas('cortePeriodo', fn ($consulta) => $consulta->where('periodo_id', $periodo->id))
            ->with('cortePeriodo:id,porcentaje')->get()
            ->filter(fn (ItemCorteMateriaPeriodo $item): bool => (int) $horarios[$item->horario_materia_periodo_id]->materia_periodo_id === (int) $item->materia_periodo_id);
        $itemsPorHorario = $items->groupBy('horario_materia_periodo_id');

        $notas = AlumnoRespuestaItem::query()
            ->join('item_corte_materia_periodo as item', 'item.id', '=', 'alumno_respuesta_items.item_corte_materia_periodo_id')
            ->join('cortes_periodo as corte', 'corte.id', '=', 'item.corte_periodo_id')
            ->whereIn('item.id', $items->modelKeys())
            ->select('alumno_respuesta_items.user_id', 'item.horario_materia_periodo_id')
            ->selectRaw('SUM(alumno_respuesta_items.nota_obtenida * item.porcentaje / 100.0 * corte.porcentaje / 100.0) as nota')
            ->selectRaw('COUNT(alumno_respuesta_items.nota_obtenida) as calificadas')
            ->groupBy('alumno_respuesta_items.user_id', 'item.horario_materia_periodo_id')->get()
            ->keyBy(fn ($nota): string => $nota->user_id.'-'.$nota->horario_materia_periodo_id);
        $asistencias = ReporteAsistenciaAlumnos::query()
            ->join('reportes_asistencia_clase as clase', 'clase.id', '=', 'reportes_asistencia_alumnos.reporte_asistencia_clase_id')
            ->whereIn('clase.horario_materia_periodo_id', $horarios->keys())
            ->select('reportes_asistencia_alumnos.user_id', 'clase.horario_materia_periodo_id')
            ->selectRaw('SUM(CASE WHEN asistio THEN 1 ELSE 0 END) as presentes')
            ->selectRaw('COUNT(asistio) as registros')
            ->groupBy('reportes_asistencia_alumnos.user_id', 'clase.horario_materia_periodo_id')->get()
            ->keyBy(fn ($asistencia): string => $asistencia->user_id.'-'.$asistencia->horario_materia_periodo_id);
        $resultados = MateriaAprobadaUsuario::query()->where('periodo_id', $periodo->id)
            ->orderBy('id')->get()->keyBy(fn ($resultado): string => $resultado->user_id.'-'.$resultado->materia_periodo_id);

        $filas = collect();
        foreach ($matriculas->groupBy(fn (Matricula $matricula): string => $matricula->user_id.'-'.($horarios->get($matricula->horario_materia_periodo_id)?->materia_periodo_id ?? 'sin-materia')) as $grupo) {
            $matricula = $grupo->first();
            $horario = $horarios->get($matricula->horario_materia_periodo_id);
            $materia = $materias->get($horario?->materia_periodo_id);
            $clave = $matricula->user_id.'-'.$matricula->horario_materia_periodo_id;
            $itemsHorario = $itemsPorHorario->get($matricula->horario_materia_periodo_id, collect());
            $pesoConfigurado = $itemsHorario->sum(fn ($item): float => (float) $item->porcentaje * (float) $item->cortePeriodo->porcentaje / 100);
            $variosHorarios = $grupo->pluck('horario_materia_periodo_id')->unique()->count() > 1;
            $nota = (float) ($notas->get($clave)?->nota ?? 0);
            $presentes = (int) ($asistencias->get($clave)?->presentes ?? 0);
            $bloqueada = $grupo->contains('bloqueado', true);
            $cerrada = ! $periodo->estado || (bool) $materia?->finalizado;
            $resultado = $resultados->get($matricula->user_id.'-'.$materia?->id);
            $motivos = [];
            $estado = 'pendiente';

            if ($cerrada) {
                if ($resultado && in_array($resultado->aprobado, [0, 1], true)) {
                    $estado = $resultado->esAprobado() ? 'aprobado' : 'riesgo';
                    $motivos = array_filter(array_map('trim', explode(',', $resultado->motivo_reprobacion ?? '')));
                    $nota = $resultado->nota_final === null ? null : (float) $resultado->nota_final;
                } else {
                    $motivos[] = 'SIN_RESULTADO_FINAL';
                    $nota = null;
                }
            } elseif (! $materia || $variosHorarios) {
                $motivos[] = 'MATRICULA_INCONSISTENTE';
            } else {
                [$estado, $motivos] = $this->proyectar($materia, $notaMinima === null ? null : (float) $notaMinima, $nota, $presentes, $bloqueada, $itemsHorario->count(), $pesoConfigurado);
            }

            $filas->push([
                'usuarioId' => $matricula->user_id,
                'genero' => match ((string) $matricula->user?->genero) {
                    '0' => 'Masculino', '1' => 'Femenino', default => 'Sin registrar',
                },
                'materiaId' => $materia?->id,
                'nivelId' => $materia?->nivel_id,
                'estado' => $estado,
                'motivos' => $motivos,
                'bloqueada' => $bloqueada,
                'cerrada' => $cerrada,
                'nota' => $materia?->habilitar_calificaciones && ! $variosHorarios ? $nota : null,
                'presentes' => $materia?->habilitar_asistencias && ! $variosHorarios ? $presentes : 0,
                'registros' => $materia?->habilitar_asistencias && ! $variosHorarios ? (int) ($asistencias->get($clave)?->registros ?? 0) : 0,
                'controlAsistencia' => (bool) $materia?->habilitar_asistencias && ! $variosHorarios,
                'evaluaciones' => $materia?->habilitar_calificaciones && ! $variosHorarios ? $itemsHorario->count() : 0,
                'calificadas' => $materia?->habilitar_calificaciones && ! $variosHorarios ? (int) ($notas->get($clave)?->calificadas ?? 0) : 0,
            ]);
        }

        $filasPorMateria = $filas->groupBy('materiaId');
        $detalleMaterias = $materias->map(fn (MateriaPeriodo $materia): array => [
            'id' => $materia->id,
            'nombre' => $materia->materia?->nombre ?? 'Materia no disponible',
            'nivel' => $materia->nivel?->nombre ?? 'Sin nivel',
            'nivelId' => $materia->nivel_id,
            'cerrada' => ! $periodo->estado || $materia->finalizado,
            'finalizada' => $materia->finalizado,
            'estadoCierre' => $estadosCierre[$materia->id] ?? null,
            'sedes' => $materia->horariosMateriaPeriodo->map(fn ($horario) => $horario->horarioBase?->aula?->sede)
                ->filter()->unique('id')->sortBy('nombre')->map(fn ($sede): array => ['id' => $sede->id, 'nombre' => $sede->nombre])->values()->all(),
            'horarios' => $materia->horariosMateriaPeriodo->count(),
            'notaMinima' => $materia->habilitar_calificaciones ? $notaMinima : null,
            'evaluaNotas' => $materia->habilitar_calificaciones,
            'asistenciasMinimas' => $materia->habilitar_asistencias ? $materia->asistencias_minimas : null,
            'resumen' => $this->resumir($filasPorMateria->get($materia->id, collect())),
        ])->sortBy('nombre')->values();
        $niveles = $periodo->nivelesPeriodo->mapWithKeys(fn ($nivel): array => [$nivel->nivel_escuela_id => $nivel->nivelEscuela?->nombre ?? 'Nivel no disponible']);
        foreach ($materias as $materia) {
            $niveles->put($materia->nivel_id ?? 'sin-nivel', $materia->nivel?->nombre ?? 'Sin nivel / materias independientes');
        }
        $filasPorNivel = $filas->groupBy(fn (array $fila): int|string => $fila['nivelId'] ?? 'sin-nivel');

        return [
            'general' => $this->resumir($filas),
            'matriculas' => $matriculas->count(),
            'excluidas' => $periodo->matriculas()->withTrashed()->count() - $matriculas->count(),
            'bloqueadas' => $matriculas->where('bloqueado', true)->count(),
            'materias' => $detalleMaterias,
            'horarios' => $horarios->count(),
            'niveles' => $niveles->map(fn (string $nombre, int|string $id): array => [
                'nombre' => $nombre,
                'resumen' => $this->resumir($filasPorNivel->get($id, collect())),
            ])->values(),
            'actualizado' => now(),
        ];
    }

    /**
     * Replica los criterios del cierre, manteniendo como pendientes las configuraciones incompletas.
     *
     * @return array{0: string, 1: array<int, string>}
     */
    private function proyectar(MateriaPeriodo $materia, ?float $notaMinima, float $nota, int $asistencias, bool $bloqueada, int $cantidadItems, float $pesoConfigurado): array
    {
        if ($bloqueada) {
            return ['riesgo', ['MATRICULA_BLOQUEADA']];
        }
        if ($materia->habilitar_calificaciones && ($notaMinima === null || $cantidadItems === 0 || abs($pesoConfigurado - 100) > 0.01)) {
            return ['pendiente', ['CRITERIOS_INCOMPLETOS']];
        }
        if ($materia->habilitar_asistencias && $materia->asistencias_minimas === null) {
            return ['pendiente', ['CRITERIOS_INCOMPLETOS']];
        }
        $motivos = [];
        if ($materia->habilitar_calificaciones && $nota < $notaMinima) {
            $motivos[] = 'NOTA_INSUFICIENTE';
        }
        if ($materia->habilitar_asistencias && $asistencias < $materia->asistencias_minimas) {
            $motivos[] = 'ASISTENCIA_INSUFICIENTE';
        }

        return [$motivos === [] ? 'aprobado' : 'riesgo', $motivos];
    }

    /**
     * Cada estudiante cuenta una vez: riesgo en alguna materia prevalece sobre pendientes.
     * Aprobar el conjunto inscrito no acredita promoción de nivel ni graduación.
     *
     * @param  Collection<int, array<string, mixed>>  $filas
     * @return array<string, mixed>
     */
    private function resumir(Collection $filas): array
    {
        $estudiantes = $filas->groupBy('usuarioId')->map(function (Collection $materias): array {
            return [
                'genero' => $materias->first()['genero'],
                'estado' => $materias->contains('estado', 'riesgo') ? 'riesgo' : ($materias->contains('estado', 'pendiente') ? 'pendiente' : 'aprobado'),
            ];
        });
        $generos = collect(['Femenino', 'Masculino', 'Sin registrar'])->mapWithKeys(fn (string $genero): array => [$genero => $estudiantes->where('genero', $genero)->count()])->all();
        $registros = $filas->sum('registros');
        $presentes = $filas->sum('presentes');
        $evaluaciones = $filas->sum('evaluaciones');
        $filasConAsistencia = $filas->where('controlAsistencia', true)->count();

        return [
            'estudiantes' => $estudiantes->count(),
            'generos' => $generos,
            'aprobados' => $estudiantes->where('estado', 'aprobado')->count(),
            'riesgo' => $estudiantes->where('estado', 'riesgo')->count(),
            'pendientes' => $estudiantes->where('estado', 'pendiente')->count(),
            'porcentajeAprobacion' => $estudiantes->isEmpty() ? null : round(100 * $estudiantes->where('estado', 'aprobado')->count() / $estudiantes->count(), 1),
            'notaPromedio' => $filas->whereNotNull('nota')->avg('nota'),
            'asistencia' => $registros > 0 ? round(100 * $presentes / $registros, 1) : null,
            'promedioAsistencias' => $registros > 0 && $filasConAsistencia > 0 ? round($presentes / $filasConAsistencia, 1) : null,
            'registrosAsistencia' => $registros,
            'presentes' => $presentes,
            'ausentes' => max(0, $registros - $presentes),
            'evaluaciones' => $evaluaciones,
            'calificadas' => $filas->sum('calificadas'),
            'avanceCalificacion' => $evaluaciones > 0 ? round(100 * $filas->sum('calificadas') / $evaluaciones, 1) : null,
            'motivos' => $filas->flatMap(fn (array $fila): array => $fila['motivos'])->countBy()->all(),
        ];
    }
}
