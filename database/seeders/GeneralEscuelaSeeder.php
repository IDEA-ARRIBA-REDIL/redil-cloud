<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

// --- Modelos ---
use App\Models\Escuela;
use App\Models\Sede;
use App\Models\TipoAula;
use App\Models\Aula;
use App\Models\CorteEscuela;
use App\Models\Materia;
use App\Models\HorarioBase;
use App\Models\TipoItem;
use App\Models\ItemPlantilla;
use App\Models\SistemaCalificacion;
use App\Models\Periodo;
use App\Models\CortePeriodo;
use App\Models\MateriaPeriodo;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\Maestro;
use App\Models\User;
use App\Models\Matricula;
use App\Models\MateriaAprobadaUsuario;
use App\Models\ReporteAsistenciaClase;
use App\Models\ReporteAsistenciaAlumnos;
use App\Models\AlumnoRespuestaItem;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Moneda;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;

class GeneralEscuelaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('>> INICIANDO SEEDER GENERAL DE ESCUELA <<');
        $this->command->info('FASE 1: Creando estructura académica base...');
        $configuracionBase = $this->_crearEstructuraAcademicaBase();
        if (empty($configuracionBase)) {
            $this->command->error('No se pudo crear la estructura base. Deteniendo el seeder.');
            return;
        }
        $this->command->info('FASE 1: Estructura base creada con éxito.');
        $this->command->info('FASE 2: Creando escenarios de períodos para pruebas...');
        $this->_crearYPopulatePeriodo($configuracionBase, 'SEMESTRE CONCLUIDO (2024)', Carbon::createFromDate(2024, 1, 15), false, 3);
        $periodoEnProgresoData = $this->_crearYPopulatePeriodo($configuracionBase, 'SEMESTRE EN PROGRESO (2026)', Carbon::now()->startOfMonth(), true, 2);
        $this->command->info('FASE 2: Escenarios de períodos creados con éxito.');
        $this->command->info('FASE 3: Creando actividad de matrícula para el período en progreso...');
        if ($periodoEnProgresoData) {
            $this->_crearActividadDeMatricula($periodoEnProgresoData['periodo'], $periodoEnProgresoData['materiaPeriodos']);
            $this->command->info('FASE 3: Actividad de matrícula creada exitosamente.');
        } else {
            $this->command->warn('FASE 3: No se pudo crear la actividad de matrícula.');
        }
        $this->command->info('>> SEEDER GENERAL DE ESCUELA FINALIZADO EXITOSAMENTE <<');
    }

    private function _crearEstructuraAcademicaBase(): array
    {
        $this->command->info('-- Asegurando datos fundamentales (Sedes, Aulas, etc.)...');
        $sede1 = Sede::firstOrCreate(['id' => 1], ['nombre' => 'Sede Principal (IMDVET)', 'tipo_sede_id' => 1, 'grupo_id' => 1, 'default' => true]);
        $sede2 = Sede::firstOrCreate(['id' => 2], ['nombre' => 'Sede Anexa (IMDVET)', 'tipo_sede_id' => 1, 'grupo_id' => 1, 'default' => false]);
        $tipoAulaPresencial = TipoAula::firstOrCreate(['nombre' => 'Presencial']);
        $sistemaCalificacion = SistemaCalificacion::firstOrCreate(['nombre' => 'Sistema Estándar IMDVET'], ['es_numerico' => true]);
        $tipoItemGeneral = TipoItem::firstOrCreate(['nombre' => 'Actividad General Seeder']);
        Moneda::firstOrCreate(['id' => 1], ['nombre' => 'Peso Colombiano', 'simbolo' => 'COP']);
        $aulasData = [
            ['nombre' => 'Salón Manantial 1 (IMDVET)', 'sede_id' => $sede1->id, 'tipo_aula_id' => $tipoAulaPresencial->id, 'activo' => true],
            ['nombre' => 'Salón Manantial 2 (IMDVET)', 'sede_id' => $sede1->id, 'tipo_aula_id' => $tipoAulaPresencial->id, 'activo' => true],
            ['nombre' => 'Salón Vida Eterna 1 (IMDVET)', 'sede_id' => $sede2->id, 'tipo_aula_id' => $tipoAulaPresencial->id, 'activo' => true],
        ];
        $aulasCreadas = collect($aulasData)->map(fn($data) => Aula::firstOrCreate(['nombre' => $data['nombre']], $data));
        $this->command->info('-- Creando Escuela, Materias y Cortes...');
        $escuela = Escuela::firstOrCreate(['nombre' => 'IGLESIA MANANTIAL DE VIDA ETERNA'], ['descripcion' => 'Escuela de formación integral.', 'tipo_matricula' => 'materias_independientes']);
        $cortesData = [
            ['escuela_id' => $escuela->id, 'nombre' => 'Corte 1', 'orden' => 1, 'porcentaje' => 35],
            ['escuela_id' => $escuela->id, 'nombre' => 'Corte 2', 'orden' => 2, 'porcentaje' => 35],
            ['escuela_id' => $escuela->id, 'nombre' => 'Corte 3', 'orden' => 3, 'porcentaje' => 30],
        ];

        // --- CORRECCIÓN ---
        // Se renombra la variable a snake_case para mantener consistencia.
        $cortes_escuela = collect($cortesData)->map(fn($data) => CorteEscuela::firstOrCreate(['nombre' => $data['nombre'], 'escuela_id' => $escuela->id], $data));

        $materiasData = ['Mentor Espiritual', 'Carácter y Servicio', 'Familia', 'Corazones Activos', 'Cosmovisión Bíblica', 'Espíritu Santo'];
        $materias = collect($materiasData)->map(fn($nombre) => Materia::firstOrCreate(['nombre' => $nombre, 'escuela_id' => $escuela->id], [
            'asistencias_minimas' => 8,
            'habilitar_asistencias' => true,
            'habilitar_calificaciones' => true,
        ]));
        $this->command->info("-- Asignando prerrequisitos secuenciales...");
        for ($i = 1; $i < $materias->count(); $i++) {
            $materias[$i]->prerrequisitosMaterias()->syncWithoutDetaching($materias[$i - 1]->id);
        }
        $this->command->info('-- Creando plantillas de Horarios e Ítems de evaluación...');
        foreach ($materias as $materia) {
            for ($i = 0; $i < 3; $i++) {
                HorarioBase::firstOrCreate(['materia_id' => $materia->id, 'dia' => ($i + 1)], ['aula_id' => $aulasCreadas->random()->id, 'hora_inicio' => '07:00', 'hora_fin' => '09:00', 'capacidad' => 20]);
            }
            // --- CORRECCIÓN ---
            // Se usa la variable renombrada.
            foreach ($cortes_escuela as $corte) {
                ItemPlantilla::firstOrCreate(['materia_id' => $materia->id, 'corte_escuela_id' => $corte->id, 'nombre' => 'Reporte Lectura'], ['tipo_item_id' => $tipoItemGeneral->id, 'porcentaje_sugerido' => 30]);
                ItemPlantilla::firstOrCreate(['materia_id' => $materia->id, 'corte_escuela_id' => $corte->id, 'nombre' => 'Compromiso Práctico'], ['tipo_item_id' => $tipoItemGeneral->id, 'porcentaje_sugerido' => 40]);
                ItemPlantilla::firstOrCreate(['materia_id' => $materia->id, 'corte_escuela_id' => $corte->id, 'nombre' => 'Asistencia y Participación'], ['tipo_item_id' => $tipoItemGeneral->id, 'porcentaje_sugerido' => 30]);
            }
        }
        $this->command->info('-- Obteniendo Maestros y Alumnos de prueba...');
        $maestroUsers = User::findMany([3, 4]);
        if ($maestroUsers->isEmpty()) {
            $this->command->error('No se encontraron usuarios (IDs 3,4) para maestros.');
            return [];
        }
        $maestros = $maestroUsers->map(fn($user) => Maestro::firstOrCreate(['user_id' => $user->id]));
        $alumnos = User::whereIn('id', range(5, 10))->get();
        if ($alumnos->count() < 2) {
            $this->command->error('No se encontraron suficientes usuarios (IDs 5-10) para alumnos.');
            return [];
        }

        // --- CORRECCIÓN ---
        // La función compact ahora usa el nombre de variable correcto.
        return compact('escuela', 'materias', 'cortes_escuela', 'maestros', 'alumnos', 'tipoItemGeneral');
    }

    private function _crearYPopulatePeriodo(array $configBase, string $nombrePeriodo, Carbon $fechaReferencia, bool $esActivo, int $limiteCortesAPoblar): ?array
    {
        $periodo = Periodo::firstOrCreate(
            ['escuela_id' => $configBase['escuela']->id, 'nombre' => $nombrePeriodo],
            ['fecha_inicio' => $fechaReferencia, 'fecha_fin' => $fechaReferencia->copy()->addMonths(5), 'estado' => $esActivo, 'sistema_calificaciones_id' => 3, 'fecha_inicio_matricula' => $fechaReferencia->copy()->subMonth(), 'fecha_fin_matricula' => $fechaReferencia->copy()->subDay()]
        );
        $periodo->sedes()->syncWithoutDetaching([1, 2]);
        $materiaPeriodos = collect($configBase['materias'])->map(fn($materia) => MateriaPeriodo::firstOrCreate(['materia_id' => $materia->id, 'periodo_id' => $periodo->id]));

        // --- CORRECCIÓN ---
        // Aquí se usa la clave correcta 'cortes_escuela' (snake_case)
        $cortesPeriodo = collect($configBase['cortes_escuela'])->map(fn($corteEsc) => CortePeriodo::firstOrCreate(['periodo_id' => $periodo->id, 'corte_escuela_id' => $corteEsc->id], ['porcentaje' => $corteEsc->porcentaje]));

        $materiaDePrueba = $materiaPeriodos->first();
        $horarioBase = HorarioBase::where('materia_id', $materiaDePrueba->materia_id)->first();
        if (!$horarioBase) {
            $this->command->error("No se encontró HorarioBase para la materia {$materiaDePrueba->materia->nombre}");
            return null;
        }

        $horario = HorarioMateriaPeriodo::firstOrCreate(['materia_periodo_id' => $materiaDePrueba->id, 'horario_base_id' => $horarioBase->id]);

        if($horario->wasRecentlyCreated) {
            $horario->maestros()->attach($configBase['maestros']->first()->id);
        }

        $matriculas = collect();
        foreach ($configBase['alumnos'] as $alumno) {
            $mat = Matricula::firstOrCreate(
                ['user_id' => $alumno->id, 'horario_materia_periodo_id' => $horario->id],
                ['periodo_id' => $periodo->id, 'estado_pago_matricula' => 'finalizada', 'fecha_matricula' => $periodo->fecha_inicio_matricula]
            );
            EstadoAcademico::firstOrCreate(
                ['matricula_id' => $mat->id, 'user_id' => $alumno->id],
                ['horario_materia_periodo_id' => $horario->id, 'periodo_id' => $periodo->id]
            );
            $matriculas->push($mat);
        }

        foreach ($cortesPeriodo as $corteIndex => $corte) {
            if ($corteIndex + 1 > $limiteCortesAPoblar) continue;

            $itemsPlantilla = ItemPlantilla::where('materia_id', $materiaDePrueba->materia_id)->where('corte_escuela_id', $corte->corte_escuela_id)->get();
            foreach ($itemsPlantilla as $plantilla) {
                $item = ItemCorteMateriaPeriodo::firstOrCreate(
                    ['corte_periodo_id' => $corte->id, 'horario_materia_periodo_id' => $horario->id, 'item_plantilla_id' => $plantilla->id],
                    ['materia_periodo_id' => $materiaDePrueba->id, 'nombre' => $plantilla->nombre, 'porcentaje' => $plantilla->porcentaje_sugerido]
                );
                foreach ($matriculas as $mat) {
                    AlumnoRespuestaItem::firstOrCreate(
                        ['user_id' => $mat->user_id, 'item_corte_materia_periodo_id' => $item->id],
                        ['nota_obtenida' => rand(30, 50) / 10, 'calificador_user_id' => $configBase['maestros']->first()->user_id]
                    );
                }
            }

            for ($i = 1; $i <= 4; $i++) {
                $reporteClase = ReporteAsistenciaClase::firstOrCreate(
                    ['horario_materia_periodo_id' => $horario->id, 'fecha_clase_reportada' => $periodo->fecha_inicio->addWeeks($corteIndex)->addDays($i)->format('Y-m-d')]
                );
                foreach ($matriculas as $mat) {
                    ReporteAsistenciaAlumnos::firstOrCreate(
                        ['reporte_asistencia_clase_id' => $reporteClase->id, 'user_id' => $mat->user_id],
                        ['asistio' => true]
                    );
                }
            }
        }

        if (!$esActivo) {
            $this->command->info("-- Finalizando datos para período concluido: '{$nombrePeriodo}'");
            foreach ($matriculas as $mat) {
                MateriaAprobadaUsuario::firstOrCreate(
                    [
                        'user_id' => $mat->user_id,
                        'materia_id' => $materiaDePrueba->materia_id,
                        'periodo_id' => $periodo->id,
                    ],
                    [
                        'materia_periodo_id' => $materiaDePrueba->id,
                        'aprobado' => true,
                        'nota_final' => 4.25,
                        'total_asistencias' => 12,
                    ]
                );
            }
        }

        return compact('periodo', 'materiaPeriodos');
    }

    private function _crearActividadDeMatricula(Periodo $periodo, $materiaPeriodos)
    {
        // FECHAS SOLICITADAS:
        // Fecha Visualización: 01 Enero 2026
        // Fecha Cierre: 28 Febrero 2026
        // Fecha Inicio: 01 Marzo 2026
        // Fecha Fin: 31 Marzo 2026
        // Tambien: mostrar_en_proximas_actividades => true, punto_de_pago => true
        // moneda_id = 1, tipo_pago_id = 1

        $fechaVisualizacion = Carbon::create(2026, 1, 1);
        $fechaCierre = Carbon::create(2026, 2, 28);
        $fechaInicio = Carbon::create(2026, 3, 1);
        $fechaFin = Carbon::create(2026, 3, 31);

        $actividad = Actividad::firstOrCreate(
            ['nombre' => "Matrículas {$periodo->nombre}", 'periodo_id' => $periodo->id],
            [
                'tipo_actividad_id' => 4,
                'punto_de_pago' => true,
                'fecha_inicio' => $fechaInicio,
                'fecha_finalizacion' => $fechaFin,
                'fecha_visualizacion' => $fechaVisualizacion,
                'fecha_cierre' => $fechaCierre,
                'activa' => true,
                'totalmente_publica' => false,
                'restriccion_por_categoria' => true,
                'mostrar_en_proximas_actividades' => true
            ]
        );

        // Actualizamos para asegurar valores
        $actividad->update([
            'fecha_inicio' => $fechaInicio,
            'fecha_finalizacion' => $fechaFin,
            'fecha_visualizacion' => $fechaVisualizacion,
            'fecha_cierre' => $fechaCierre,
            'punto_de_pago' => true,
            'mostrar_en_proximas_actividades' => true,
            'activa' => true,
        ]);

        // LOGICA DE TIPO PAGO Y MONEDA (Para esta actividad específica)
        // El usuario pidió: moneda_id = 1, tipo_pago_id = 1
        $actividad->monedas()->sync([1]); // Moneda 1
        $actividad->tiposPago()->sync([1]); // Tipo Pago 1

        $this->command->info("---- Creando categorías para la actividad '{$actividad->nombre}'...");
        $moneda = Moneda::find(1);
        foreach ($materiaPeriodos as $mp) {
            $categoria = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $actividad->id, 'nombre' => $mp->materia->nombre],
                ['aforo' => 999, 'es_gratuita' => false, 'materia_periodo_id' => $mp->id]
            );
            if ($moneda && $categoria->wasRecentlyCreated) {
                // Modificado para usar moneda 1 y valor 50000
                $categoria->monedas()->syncWithoutDetaching([1 => ['valor' => 50000]]);
            }
        }
    }
}
