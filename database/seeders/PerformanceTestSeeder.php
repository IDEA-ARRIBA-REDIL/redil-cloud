<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Importa todos los modelos necesarios
use App\Models\Escuela;
use App\Models\Materia;
use App\Models\CorteEscuela;
use App\Models\User;
use App\Models\Periodo;
use App\Models\MateriaPeriodo;
use App\Models\CortePeriodo;
use App\Models\HorarioBase;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemPlantilla;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\ReporteAsistenciaClase;
use App\Models\AlumnoRespuestaItem;
use App\Models\Matricula;
use App\Models\MatriculaHorarioMateriaPeriodo;
use App\Models\Sede;
use App\Models\Aula;

class PerformanceTestSeeder extends Seeder
{
    const NUM_ALUMNOS = 50;
    const ASISTENCIAS_MINIMAS = 8;
    const TOTAL_CLASES_POR_MATERIA = 10;
    const PERIODO_TEST_NOMBRE = 'PERIODO DE PRUEBA DE RENDIMIENTO (Distribuido)';
  /*
    public function run(): void
    {
        $this->command->info('>> INICIANDO SEEDER DE PRUEBA DE RENDIMIENTO (DISTRIBUIDO) <<');
        $this->command->info('FASE 0: Limpiando datos de prueba anteriores...');
        $this->_cleanup();
        $this->command->info('FASE 0: Limpieza completada.');
        $this->command->info('FASE 1: Verificando y preparando datos base...');
        $baseData = $this->_prepareBaseData();
        if (!$baseData) {
            return;
        }
        $this->command->info('FASE 1: Datos base listos.');
        $this->command->info('FASE 2: Creando el periodo de prueba con ' . self::NUM_ALUMNOS . ' alumnos distribuidos en ' . $baseData['sedes']->count() . ' sedes...');
        $this->_createPerformancePeriod($baseData);
        $this->command->info('FASE 2: Periodo de prueba creado y poblado exitosamente.');
        $this->command->info('>> SEEDER DE RENDIMIENTO FINALIZADO <<');
    }

    private function _cleanup(): void
    {
        $periodoAnterior = Periodo::where('nombre', 'like', 'PERIODO DE PRUEBA DE RENDIMIENTO%')->first();
        if ($periodoAnterior) {
            $this->command->warn("--- Encontrado periodo de prueba anterior (ID: {$periodoAnterior->id}). Eliminando...");
            $periodoAnterior->delete();
            $this->command->info("--- Periodo de prueba anterior eliminado.");
        } else {
            $this->command->info("--- No se encontraron datos de prueba anteriores. Todo limpio.");
        }
    }

    private function _prepareBaseData(): ?array
    {
        // ----> CORRECCIÓN CLAVE: Buscamos la escuela por ID=3 o fallback <----
        $escuela = Escuela::find(3) ?? Escuela::first();
        if (!$escuela) {
            $this->command->error('No se encontró ninguna escuela en la base de datos.');
            return null;
        }

        $sedes = Sede::all();
        if ($sedes->isEmpty()) {
            $this->command->error('No se encontraron sedes en la base de datos. Por favor, crea al menos una sede.');
            return null;
        }

        $materias = Materia::where('escuela_id', $escuela->id)->limit(6)->get();
        $cortesEscuela = CorteEscuela::where('escuela_id', $escuela->id)->get();
        $itemPlantillas = ItemPlantilla::whereIn('materia_id', $materias->pluck('id'))->get();

        if ($materias->count() < 1 || $cortesEscuela->isEmpty()) {
            $this->command->error('La estructura de materias o cortes para la escuela es insuficiente.');
            return null;
        }

        $this->command->info('-- Verificando usuarios de prueba...');
        $existingUsersCount = User::where('email', 'like', 'student.test.%')->count();
        if ($existingUsersCount < self::NUM_ALUMNOS) {
            $this->command->warn('--- No se encontraron suficientes usuarios de prueba. Creando...');
            User::factory()->count(self::NUM_ALUMNOS - $existingUsersCount)->create();
            $this->command->info('--- ' . (self::NUM_ALUMNOS - $existingUsersCount) . ' nuevos usuarios de prueba creados.');
        } else {
            $this->command->info('--- Los ' . self::NUM_ALUMNOS . ' usuarios de prueba ya existen o hay más de los necesarios.');
        }
        $alumnos = User::where('email', 'like', 'student.test.%')->take(self::NUM_ALUMNOS)->get();

        return [
            'escuela' => $escuela,
            'sedes' => $sedes,
            'materias' => $materias,
            'cortes_escuela' => $cortesEscuela,
            'alumnos' => $alumnos->shuffle(),
            'item_plantillas' => $itemPlantillas->groupBy(['materia_id', 'corte_escuela_id']),
        ];
    }

    private function _createPerformancePeriod(array $baseData): void
    {
        $periodo = Periodo::firstOrCreate(['escuela_id' => $baseData['escuela']->id, 'nombre' => self::PERIODO_TEST_NOMBRE, 'fecha_inicio' => now()->subYear(), 'fecha_fin' => now()->subYear()->addMonths(5), 'estado' => true, 'sistema_calificaciones_id' => 3]);
        $periodo->sedes()->attach($baseData['sedes']->pluck('id'));
        $this->command->info("-- Periodo '{$periodo->nombre}' creado y asociado a todas las sedes.");

        $materiaPeriodos = collect();
        foreach ($baseData['materias'] as $materia) {
            $materiaPeriodos->push(MateriaPeriodo::firstOrCreate(['materia_id' => $materia->id, 'periodo_id' => $periodo->id, 'asistencias_minimas' => self::ASISTENCIAS_MINIMAS]));
        }
        $cortesPeriodo = collect();
        foreach ($baseData['cortes_escuela'] as $corteEsc) {
            $cortesPeriodo->push(CortePeriodo::firstOrCreate(['periodo_id' => $periodo->id, 'corte_escuela_id' => $corteEsc->id, 'porcentaje' => $corteEsc->porcentaje]));
        }

        $alumnos = $baseData['alumnos'];
        // Para verificar rendimiento, asignaremos todos los alumnos a todas las materias pero en diferentes horarios
        // Dividiremos los alumnos en 2 grupos para los 2 horarios

        $grupoMañana = $alumnos->splice(0, (int)($alumnos->count() / 2));
        $grupoTarde = $alumnos; // El resto

        $matriculasParaInsertar = [];
        $estadoAcademicoParaInsertar = [];
        $respuestasParaInsertar = [];
        $asistenciasAlumnosParaInsertar = [];
        $matriculasParaDesertar = [];

        $this->command->getOutput()->progressStart($materiaPeriodos->count());

        $sede = $baseData['sedes']->first(); // Usamos una sede principal para simplificar, o aleatorio
        $aula = Aula::firstOrCreate(['sede_id' => $sede->id, 'nombre' => "Aula Demo Performance"], ['tipo_aula_id' => 1]);

        foreach ($materiaPeriodos as $mp) {
            // --- Creación de 2 Horarios ---
            $horarios = [];

            // Horario 1: Mañana
            $horarioBase1 = HorarioBase::firstOrCreate(
                ['materia_id' => $mp->materia_id, 'aula_id' => $aula->id, 'dia' => 1], // Lunes
                ['hora_inicio' => '08:00:00', 'hora_fin' => '10:00:00']
            );
            $horario1 = HorarioMateriaPeriodo::firstOrCreate(['materia_periodo_id' => $mp->id, 'horario_base_id' => $horarioBase1->id]);
            $horarios[] = ['horario' => $horario1, 'grupo' => $grupoMañana];

            // Horario 2: Tarde
            $horarioBase2 = HorarioBase::firstOrCreate(
                ['materia_id' => $mp->materia_id, 'aula_id' => $aula->id, 'dia' => 3], // Miercoles
                ['hora_inicio' => '14:00:00', 'hora_fin' => '16:00:00']
            );
            $horario2 = HorarioMateriaPeriodo::firstOrCreate(['materia_periodo_id' => $mp->id, 'horario_base_id' => $horarioBase2->id]);
            $horarios[] = ['horario' => $horario2, 'grupo' => $grupoTarde];

            // Procesar cada horario
            foreach ($horarios as $hData) {
                $horario = $hData['horario'];
                $alumnosGrupo = $hData['grupo'];

                // Matricular alumnos del grupo en este horario
                $nuevasMatriculas = [];
                foreach ($alumnosGrupo as $alumno) {
                    // Verificar si ya tiene matricula en el periodo (la matricula es por periodo, pero aqui la asociamos al horario especifico en estado academico)
                    // NOTA: El modelo Matricula suele ser único por Periodo/Usuario en muchos sistemas, pero aquí parece permitir multiples o se usa para el cobro.
                    // Asumiremos una matricula por periodo y multiples estados academicos si fuera el caso, pero el seeder original creaba una matricula por horario (linea 154 original).
                    // Mantendremos el patrón: Una Matricula por alumno/horario para simplificar este test de rendimiento especifico si el sistema lo permite,
                    // O si el sistema valida unique user_id + periodo_id, deberíamos buscar la matricula existente.
                    // Para seguridad en este seed: firstOrCreate la matricula del periodo y luego el estado academico.

                    $matricula = Matricula::firstOrCreate([
                        'user_id' => $alumno->id,
                        'periodo_id' => $periodo->id
                    ], [
                        'horario_materia_periodo_id' => $horario->id, // El "principal"
                        'fecha_matricula' => $periodo->fecha_inicio,
                        'sede_id' => $sede->id,
                        'estado_pago_matricula' => 'finalizada'
                    ]);

                    // Estado Academico (es lo que liga a la materia especifica)
                    $estadoAcademicoParaInsertar[] = [
                        'matricula_id' => $matricula->id,
                        'user_id' => $alumno->id,
                        'horario_materia_periodo_id' => $horario->id,
                        'periodo_id' => $periodo->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    $nuevasMatriculas[] = $matricula;

                    // Bloqueo / Deserción
                    $perfil = $this->_getPerfilAleatorio();
                    if ($perfil === 'critico') {
                        $matriculasParaDesertar[] = [
                            'id' => $matricula->id,
                            'fecha_bloqueo' => $periodo->fecha_inicio->addWeeks(rand(1, 8))
                        ];
                    }
                }

                // Crear Items (Notas)
                $itemsDeEsteHorario = [];
                foreach ($cortesPeriodo as $corteP) {
                    $plantillas = $baseData['item_plantillas'][$mp->materia_id][$corteP->corte_escuela_id] ?? collect();
                    foreach ($plantillas as $plantilla) {
                        $itemsDeEsteHorario[] = ItemCorteMateriaPeriodo::firstOrCreate([
                            'corte_periodo_id' => $corteP->id,
                            'horario_materia_periodo_id' => $horario->id,
                            'materia_periodo_id' => $mp->id,
                            'item_plantilla_id' => $plantilla->id,
                            'nombre' => $plantilla->nombre,
                            'porcentaje' => $plantilla->porcentaje_sugerido
                        ]);
                    }
                }

                // Crear Clases (Asistencias)
                $clasesDeEsteHorario = [];
                for ($i = 0; $i < self::TOTAL_CLASES_POR_MATERIA; $i++) {
                   $clasesDeEsteHorario[] = ReporteAsistenciaClase::firstOrCreate([
                       'horario_materia_periodo_id' => $horario->id,
                       'fecha_clase_reportada' => $periodo->fecha_inicio->addWeeks($i)
                   ]);
                }

                // Rellenar Notas y Asistencias
                foreach ($nuevasMatriculas as $mat) {
                    $perfil = $this->_getPerfilAleatorio(); // Recalcular perfil por materia para variar, o mantenerlo fijo si se desea consistencia

                    // Notas
                    foreach ($itemsDeEsteHorario as $item) {
                        $respuestasParaInsertar[] = [
                            'user_id' => $mat->user_id,
                            'item_corte_materia_periodo_id' => $item->id,
                            'nota_obtenida' => $this->_generarNota($perfil),
                            'calificador_user_id' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }

                    // Asistencias
                    $asistenciasAConceder = $this->_generarAsistencias($perfil);
                    foreach ($clasesDeEsteHorario as $index => $clase) {
                        $asistenciasAlumnosParaInsertar[] = [
                            'reporte_asistencia_clase_id' => $clase->id,
                            'user_id' => $mat->user_id,
                            'asistio' => $index < $asistenciasAConceder,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }

            } // Fin foreach horarios

            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();

        $this->command->info("\n-- Insertando datos masivamente en la base de datos...");
        foreach (array_chunk($estadoAcademicoParaInsertar, 500) as $chunk) {
            DB::table('matricula_horario_materia_periodo')->insert($chunk);
        }
        foreach (array_chunk($respuestasParaInsertar, 500) as $chunk) {
            DB::table('alumno_respuesta_items')->insert($chunk);
        }
        foreach (array_chunk($asistenciasAlumnosParaInsertar, 500) as $chunk) {
            DB::table('reportes_asistencia_alumnos')->insert($chunk);
        }

        $this->command->info('-- Aplicando deserciones...');
        foreach ($matriculasParaDesertar as $desercion) {
            Matricula::where('id', $desercion['id'])->update(['bloqueado' => true, 'fecha_bloqueo' => $desercion['fecha_bloqueo']]);
        }

        $this->command->info('-- Inserción masiva completada.');
    }

    private function _getPerfilAleatorio(): string
    {
        $rand = mt_rand(1, 100);
        if ($rand <= 60) return 'ejemplar';
        if ($rand <= 80) return 'dificultades';
        if ($rand <= 95) return 'ausente';
        return 'critico';
    }

    private function _generarNota(string $perfil): float
    {
        return match ($perfil) {
            'ejemplar' => mt_rand(350, 500) / 100,
            'dificultades' => mt_rand(150, 299) / 100,
            'ausente' => mt_rand(320, 480) / 100,
            'critico' => mt_rand(100, 250) / 100,
        };
    }

    private function _generarAsistencias(string $perfil): int
    {
        return match ($perfil) {
            'ejemplar' => mt_rand(self::ASISTENCIAS_MINIMAS, self::TOTAL_CLASES_POR_MATERIA),
            'dificultades' => mt_rand(self::ASISTENCIAS_MINIMAS, self::TOTAL_CLASES_POR_MATERIA - 1),
            'ausente' => mt_rand(3, self::ASISTENCIAS_MINIMAS - 1),
            'critico' => mt_rand(0, self::ASISTENCIAS_MINIMAS - 2),
        };
    }
        */
}
