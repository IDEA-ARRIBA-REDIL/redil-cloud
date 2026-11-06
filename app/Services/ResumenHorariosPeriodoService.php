<?php

namespace App\Services;

use App\Models\HorarioMateriaPeriodo;
use App\Models\Periodo;

class ResumenHorariosPeriodoService
{
    /**
     * Obtiene el consolidado y desglose de horarios para un periodo específico.
     *
     * @return array<string, mixed>
     */
    public function obtener(Periodo $periodo): array
    {
        $horarios = HorarioMateriaPeriodo::query()
            ->whereHas('materiaPeriodo', fn ($q) => $q->where('periodo_id', $periodo->id))
            ->with([
                'materiaPeriodo.materia',
                'horarioBase.aula.sede',
                'horarioBase.aula.tipo',
                'maestros.user',
            ])
            ->withCount([
                'matriculasDeAlumnos as matriculas_activas_count' => fn ($q) => $q->where('periodo_id', $periodo->id)
                    ->whereNotIn('estado_pago_matricula', ['anulada', 'rechazada']),
            ])
            ->get();

        $diasNombres = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            0 => 'Domingo',
            7 => 'Domingo',
        ];

        $conteoPorDia = [
            'Lunes' => 0,
            'Martes' => 0,
            'Miércoles' => 0,
            'Jueves' => 0,
            'Viernes' => 0,
            'Sábado' => 0,
            'Domingo' => 0,
        ];

        $materiasGlobal = [];
        $sedesGlobal = [];
        $totalInscritos = 0;
        $capacidadTotal = 0;
        $horariosHabilitados = 0;
        $horariosInactivos = 0;

        foreach ($horarios as $hmp) {
            $habilitado = (bool) $hmp->habilitado;
            if ($habilitado) {
                $horariosHabilitados++;
            } else {
                $horariosInactivos++;
            }

            $inscritosHorario = (int) ($hmp->matriculas_activas_count ?? 0);
            $totalInscritos += $inscritosHorario;

            $capacidadHorario = (int) ($hmp->horarioBase?->capacidad ?? 0);
            $capacidadTotal += $capacidadHorario;

            $diaNum = $hmp->horarioBase?->dia !== null ? (int) $hmp->horarioBase->dia : null;
            $nombreDia = $diaNum !== null && isset($diasNombres[$diaNum]) ? $diasNombres[$diaNum] : 'Sin definir';
            if (isset($conteoPorDia[$nombreDia])) {
                $conteoPorDia[$nombreDia]++;
            }

            $materia = $hmp->materiaPeriodo?->materia;
            $materiaId = $materia?->id ?? 0;
            $materiaNombre = $materia?->nombre ?? 'Sin materia';

            if (! isset($materiasGlobal[$materiaId])) {
                $materiasGlobal[$materiaId] = [
                    'id' => $materiaId,
                    'nombre' => $materiaNombre,
                    'nivel' => $materia?->nivel ?? 'General',
                    'totalHorarios' => 0,
                    'totalInscritos' => 0,
                    'capacidadTotal' => 0,
                    'sedes' => [],
                ];
            }
            $materiasGlobal[$materiaId]['totalHorarios']++;
            $materiasGlobal[$materiaId]['totalInscritos'] += $inscritosHorario;
            $materiasGlobal[$materiaId]['capacidadTotal'] += $capacidadHorario;

            $sede = $hmp->horarioBase?->aula?->sede;
            $sedeId = $sede?->id ?? 0;
            $sedeNombre = $sede?->nombre ?? 'Sin sede asignada';

            if (! in_array($sedeNombre, $materiasGlobal[$materiaId]['sedes'], true)) {
                $materiasGlobal[$materiaId]['sedes'][] = $sedeNombre;
            }

            if (! isset($sedesGlobal[$sedeId])) {
                $sedesGlobal[$sedeId] = [
                    'id' => $sedeId,
                    'nombre' => $sedeNombre,
                    'totalHorarios' => 0,
                    'totalInscritos' => 0,
                    'capacidadTotal' => 0,
                    'dias' => [
                        'Lunes' => 0,
                        'Martes' => 0,
                        'Miércoles' => 0,
                        'Jueves' => 0,
                        'Viernes' => 0,
                        'Sábado' => 0,
                        'Domingo' => 0,
                    ],
                    'materias' => [],
                ];
            }
            $sedesGlobal[$sedeId]['totalHorarios']++;
            $sedesGlobal[$sedeId]['totalInscritos'] += $inscritosHorario;
            $sedesGlobal[$sedeId]['capacidadTotal'] += $capacidadHorario;
            if (isset($sedesGlobal[$sedeId]['dias'][$nombreDia])) {
                $sedesGlobal[$sedeId]['dias'][$nombreDia]++;
            }

            if (! isset($sedesGlobal[$sedeId]['materias'][$materiaId])) {
                $sedesGlobal[$sedeId]['materias'][$materiaId] = [
                    'id' => $materiaId,
                    'nombre' => $materiaNombre,
                    'nivel' => $materia?->nivel ?? 'General',
                    'totalHorarios' => 0,
                    'totalInscritos' => 0,
                    'capacidadTotal' => 0,
                    'horarios' => [],
                ];
            }
            $sedesGlobal[$sedeId]['materias'][$materiaId]['totalHorarios']++;
            $sedesGlobal[$sedeId]['materias'][$materiaId]['totalInscritos'] += $inscritosHorario;
            $sedesGlobal[$sedeId]['materias'][$materiaId]['capacidadTotal'] += $capacidadHorario;

            $maestrosList = [];
            if ($hmp->maestros) {
                foreach ($hmp->maestros as $mProf) {
                    $u = $mProf->user;
                    $maestrosList[] = [
                        'id' => $mProf->id,
                        'nombre' => $u ? trim($u->primer_nombre.' '.$u->segundo_nombre.' '.$u->primer_apellido.' '.$u->segundo_apellido) : 'Maestro sin usuario',
                        'telefono' => $u?->telefono_movil ?? '',
                        'email' => $u?->email ?? '',
                    ];
                }
            }

            $sedesGlobal[$sedeId]['materias'][$materiaId]['horarios'][] = [
                'id' => $hmp->id,
                'aula' => $hmp->horarioBase?->aula?->nombre ?? 'Sin aula',
                'tipoAula' => $hmp->horarioBase?->aula?->tipo?->nombre ?? 'General',
                'dia' => $nombreDia,
                'diaNum' => $diaNum,
                'horaInicio' => $hmp->horarioBase?->hora_inicio_formato ?? '',
                'horaFin' => $hmp->horarioBase?->hora_fin_formato ?? '',
                'horarioTexto' => ($hmp->horarioBase?->hora_inicio_formato ?? '').' - '.($hmp->horarioBase?->hora_fin_formato ?? ''),
                'capacidad' => $capacidadHorario,
                'inscritos' => $inscritosHorario,
                'habilitado' => $habilitado,
                'maestros' => $maestrosList,
            ];
        }

        uasort($materiasGlobal, fn ($a, $b) => $b['totalHorarios'] <=> $a['totalHorarios']);

        uasort($sedesGlobal, function ($a, $b) {
            if ($a['totalHorarios'] !== $b['totalHorarios']) {
                return $b['totalHorarios'] <=> $a['totalHorarios'];
            }

            return strcasecmp($a['nombre'], $b['nombre']);
        });

        $totalHorarios = $horarios->count();

        foreach ($sedesGlobal as &$sData) {
            uasort($sData['materias'], fn ($ma, $mb) => $mb['totalHorarios'] <=> $ma['totalHorarios']);
            $sData['materias'] = array_values($sData['materias']);
            $sData['porcentajeOcupacion'] = $sData['capacidadTotal'] > 0
                ? round(($sData['totalInscritos'] * 100) / $sData['capacidadTotal'], 1)
                : 0;
            $sData['porcentajeDelTotal'] = $totalHorarios > 0
                ? round(($sData['totalHorarios'] * 100) / $totalHorarios, 1)
                : 0;

            // Gráficos proporcionales específicos de esta sede
            $sData['chartMaterias'] = [
                'labels' => array_column($sData['materias'], 'nombre'),
                'series' => array_column($sData['materias'], 'totalHorarios'),
            ];
            $sData['chartDias'] = [
                'labels' => array_keys($sData['dias']),
                'series' => array_values($sData['dias']),
            ];
        }
        unset($sData);

        $porcentajeOcupacionGeneral = $capacidadTotal > 0
            ? round(($totalInscritos * 100) / $capacidadTotal, 1)
            : 0;

        $materiasChartLabels = array_column(array_values($materiasGlobal), 'nombre');
        $materiasChartSeries = array_column(array_values($materiasGlobal), 'totalHorarios');
        $materiasChartInscritos = array_column(array_values($materiasGlobal), 'totalInscritos');

        $sedesChartLabels = array_column(array_values($sedesGlobal), 'nombre');
        $sedesChartSeries = array_column(array_values($sedesGlobal), 'totalHorarios');
        $sedesChartInscritos = array_column(array_values($sedesGlobal), 'totalInscritos');
        $sedesChartPorcentajes = array_column(array_values($sedesGlobal), 'porcentajeDelTotal');

        return [
            'periodo' => $periodo,
            'actualizado' => now(),
            'general' => [
                'totalHorarios' => $totalHorarios,
                'horariosHabilitados' => $horariosHabilitados,
                'horariosInactivos' => $horariosInactivos,
                'totalMaterias' => count($materiasGlobal),
                'totalSedes' => count($sedesGlobal),
                'totalInscritos' => $totalInscritos,
                'capacidadTotal' => $capacidadTotal,
                'porcentajeOcupacion' => $porcentajeOcupacionGeneral,
            ],
            'charts' => [
                'materias' => [
                    'labels' => $materiasChartLabels,
                    'series' => $materiasChartSeries,
                    'inscritos' => $materiasChartInscritos,
                ],
                'sedes' => [
                    'labels' => $sedesChartLabels,
                    'series' => $sedesChartSeries,
                    'porcentajes' => $sedesChartPorcentajes,
                    'inscritos' => $sedesChartInscritos,
                ],
                'dias' => [
                    'labels' => array_keys($conteoPorDia),
                    'series' => array_values($conteoPorDia),
                ],
            ],
            'sedes' => array_values($sedesGlobal),
            'materias' => array_values($materiasGlobal),
        ];
    }
}
