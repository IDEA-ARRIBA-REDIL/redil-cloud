<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Escuela;
use App\Models\Sede;
use App\Models\TipoAula;
use App\Models\Aula;
use App\Models\CorteEscuela;
use App\Models\Materia;
use App\Models\HorarioBase;
use App\Models\TipoItem;
use App\Models\ItemPlantilla;
use App\Models\Periodo;
use App\Models\CortePeriodo;
use App\Models\MateriaPeriodo;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\User;
use App\Models\Matricula;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico; // Alias común en el proyecto

class EscuelaConsolidacionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('>> INICIANDO SEEDER ESCUELAS CONSOLIDACIÓN <<');

        // ---------------------------------------------------------
        // 1. INFRAESTRUCTURA (Sede y Aulas)
        // ---------------------------------------------------------
        $sede = Sede::find(1);
        if (!$sede) {
            $sede = Sede::firstOrCreate(['id' => 1, 'nombre' => 'Sede Principal (Default)', 'tipo_sede_id' => 1, 'grupo_id' => 1, 'default' => true]);
        }

        // TipoAula: Sector TRUE
        $tipoAulaSector = TipoAula::updateOrCreate(
            ['nombre' => 'Consolidación Sector'],
            ['sector' => true]
        );

        // TipoAula: Sector FALSE
        $tipoAulaGeneral = TipoAula::updateOrCreate(
            ['nombre' => 'Consolidación General'],
            ['sector' => false]
        );

        // Aulas
        $aulaSector = Aula::firstOrCreate(
            ['nombre' => 'Aula CHL Sector', 'sede_id' => $sede->id],
            ['tipo_aula_id' => $tipoAulaSector->id, 'activo' => true ]
        );

        $aulaGeneral = Aula::firstOrCreate(
            ['nombre' => 'Aula CHL General', 'sede_id' => $sede->id],
            ['tipo_aula_id' => $tipoAulaGeneral->id, 'activo' => true]
        );

        $tipoItem = TipoItem::firstOrCreate(['nombre' => 'Item Estándar Consolidación']);

        // ---------------------------------------------------------
        // 2. ESCUELAS Y ESTRUCTURA ACADÉMICA
        // ---------------------------------------------------------
        $escuelasData = [
            ['nombre' => 'CHL', 'descripcion' => 'Escuela de Consolidación CHL'],
            ['nombre' => 'CHL WARRIORS', 'descripcion' => 'Escuela de Consolidación CHL WARRIORS']
        ];

        // Guardamos referencias para matrículas posteriores
        $escuelasMap = [];

        foreach ($escuelasData as $data) {
            $this->command->info("Procesando escuela: {$data['nombre']}");

            $escuela = Escuela::firstOrCreate([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'tipo_matricula' => 'materias_independientes',
                'diploma_id' => 1,
                'habilitada_consilidacion' => true,
            ]);

            $escuelasMap[$data['nombre']] = $escuela;

            // Cortes
            $cortesConfig = [
                ['nombre' => 'Corte 1', 'orden' => 1, 'porcentaje' => 35],
                ['nombre' => 'Corte 2', 'orden' => 2, 'porcentaje' => 35],
                ['nombre' => 'Corte 3', 'orden' => 3, 'porcentaje' => 30],
            ];

            $cortesEscuela = collect();
            foreach ($cortesConfig as $cConfig) {
                $cortesEscuela->push(CorteEscuela::firstOrCreate([
                    'escuela_id' => $escuela->id,
                    'nombre' => $cConfig['nombre'],
                    'orden' => $cConfig['orden'],
                    'porcentaje' => $cConfig['porcentaje']
                ]));
            }

            // Materias
            $nombresMaterias = [
                'Fundamentos de Fe',
                'Vida de Oración',
                'Evangelismo Práctico',
                'Mayordomía Bíblica',
                'Liderazgo Servidor',
                'Doctrina Básica'
            ];

            $materias = collect();
            foreach ($nombresMaterias as $nom) {
                $materias->push(Materia::firstOrCreate([
                    'escuela_id' => $escuela->id,
                    'nombre' => $nom,
                    'asistencias_minimas' => 8,
                    'habilitar_asistencias' => true,
                    'habilitar_calificaciones' => true
                ]));
            }

            // Prerrequisitos
            for ($i = 1; $i < $materias->count(); $i++) {
                 $materias[$i]->prerrequisitosMaterias()->syncWithoutDetaching($materias[$i - 1]->id);
            }

            // Items Plantilla & Horarios Base
            foreach ($materias as $materia) {
                foreach ($cortesEscuela as $corte) {
                    ItemPlantilla::firstOrCreate(['materia_id' => $materia->id, 'corte_escuela_id' => $corte->id, 'nombre' => 'Parcial Teórico', 'tipo_item_id' => $tipoItem->id, 'porcentaje_sugerido' => 50]);
                    ItemPlantilla::firstOrCreate(['materia_id' => $materia->id, 'corte_escuela_id' => $corte->id, 'nombre' => 'Trabajo Práctico', 'tipo_item_id' => $tipoItem->id, 'porcentaje_sugerido' => 50]);
                }

                HorarioBase::firstOrCreate(['materia_id' => $materia->id, 'dia' => 1, 'aula_id' => $aulaSector->id, 'hora_inicio' => '19:00', 'hora_fin' => '21:00', 'capacidad' => 40]);
                HorarioBase::firstOrCreate(['materia_id' => $materia->id, 'dia' => 2, 'aula_id' => $aulaGeneral->id, 'hora_inicio' => '19:00', 'hora_fin' => '21:00', 'capacidad' => 40]);
            }

            // Periodo 2026-1
            $fechaInicio = Carbon::create(2026, 1, 1);
            $fechaFin = Carbon::create(2026, 5, 30);

            $periodo = Periodo::firstOrCreate([
                'escuela_id' => $escuela->id,
                'nombre' => "Periodo 2026-1 ({$escuela->nombre})",
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => true,
                'sistema_calificaciones_id' => 1,
                'fecha_inicio_matricula' => $fechaInicio->copy()->subMonth(),
                'fecha_fin_matricula' => $fechaInicio->copy()->addDays(15),
            ]);

            // Oferta Académica
            foreach ($cortesEscuela as $ce) {
                CortePeriodo::firstOrCreate([
                    'periodo_id' => $periodo->id,
                    'corte_escuela_id' => $ce->id,
                    'porcentaje' => $ce->porcentaje
                ]);
            }

            foreach ($materias as $materia) {
                $materiaPeriodo = MateriaPeriodo::firstOrCreate([
                    'materia_id' => $materia->id,
                    'periodo_id' => $periodo->id
                ]);

                $horariosBase = HorarioBase::where('materia_id', $materia->id)->get();
                foreach ($horariosBase as $hb) {
                    HorarioMateriaPeriodo::firstOrCreate([
                        'materia_periodo_id' => $materiaPeriodo->id,
                        'horario_base_id' => $hb->id
                    ]);
                }
            }
        }

        // ---------------------------------------------------------
        // 3. MATRICULAS ESPECÍFICAS
        // ---------------------------------------------------------
        $this->command->info('>> GENERANDO MATRÍCULAS ESPECÍFICAS POR ID <<');

        $enrollmentConfigs = [
            [
                'user_id' => 11,
                'escuela_nombre' => 'CHL',
                'sector_required' => true,
                'fecha_matricula' => '2026-01-11',
                'bloqueado' => false,
                'fecha_bloqueo' => null
            ],
            [
                'user_id' => 10,
                'escuela_nombre' => 'CHL',
                'sector_required' => false,
                'fecha_matricula' => '2026-01-11',
                'bloqueado' => false,
                'fecha_bloqueo' => null
            ],
            [
                'user_id' => 9,
                'escuela_nombre' => 'CHL',
                'sector_required' => true,
                'fecha_matricula' => '2025-12-11',
                'bloqueado' => false,
                'fecha_bloqueo' => null
            ],
            [
                'user_id' => 8,
                'escuela_nombre' => 'CHL',
                'sector_required' => true,
                'fecha_matricula' => '2026-01-11',
                'bloqueado' => true,
                'fecha_bloqueo' => '2026-01-13' // Asumiendo formato Y-m-d
            ],
            [
                'user_id' => 7,
                'escuela_nombre' => 'CHL WARRIORS',
                'sector_required' => true,
                'fecha_matricula' => '2026-01-11',
                'bloqueado' => false,
                'fecha_bloqueo' => null
            ]
        ];

        foreach ($enrollmentConfigs as $config) {
            $userId = $config['user_id'];
            $this->command->info("Matriculando User ID: {$userId} en {$config['escuela_nombre']}");

            // Usuario ya existe, usamos userId directo
            $this->command->info("  Procesando ID: {$userId}");

            // Obtener Escuela y Periodo
            if (!isset($escuelasMap[$config['escuela_nombre']])) {
                $this->command->error("Escuela {$config['escuela_nombre']} no encontrada en mapa.");
                continue;
            }
            $escuela = $escuelasMap[$config['escuela_nombre']];

            // Buscar el periodo creado para esta escuela (asumimos el único creado en este seeder)
            // O buscamos por fechas si hubiera conflicto, pero aquí es fresco.
            $periodo = Periodo::where('escuela_id', $escuela->id)
                ->where('nombre', 'like', '%2026-1%')
                ->first();

            if (!$periodo) {
                $this->command->error("Periodo no encontrado para escuela {$escuela->nombre}");
                continue;
            }

            // Matricular en TODAS las materias del periodo que cumplan con el tipo de aula
            $materiaPeriodos = MateriaPeriodo::where('periodo_id', $periodo->id)->get();

            foreach ($materiaPeriodos as $mp) {
                // Buscar Horario que coincida con sector preference
                $horarioTarget = HorarioMateriaPeriodo::where('materia_periodo_id', $mp->id)
                    ->whereHas('horarioBase.aula.tipo', function($q) use ($config) {
                        $q->where('sector', $config['sector_required']);
                    })
                    ->first();

                if (!$horarioTarget) {
                    $sectorStr = $config['sector_required'] ? 'Sector' : 'General';
                    $this->command->warn("  No se encontró horario {$sectorStr} para materia {$mp->materia->nombre}. Saltando.");
                    continue;
                }

                // Crear Matrícula
                $matricula = Matricula::firstOrCreate([
                    'user_id' => $userId,
                    'periodo_id' => $periodo->id,
                    'horario_materia_periodo_id' => $horarioTarget->id,
                    'fecha_matricula' => Carbon::parse($config['fecha_matricula']),
                    'bloqueado' => $config['bloqueado'],
                    'fecha_bloqueo' => $config['fecha_bloqueo'] ? Carbon::parse($config['fecha_bloqueo']) : null,
                    'escuela_id' => $escuela->id,
                    'estado_pago_matricula' => 'finalizada', // Asumo pagada/finalizada para pruebas
                    'sede_id' => 1, // Sede creada
                ]);

                // Crear Estado Académico (Vinculación académica)
                EstadoAcademico::firstOrCreate([
                    'matricula_id' => $matricula->id,
                    'user_id' => $userId,
                    'horario_materia_periodo_id' => $horarioTarget->id,
                    'periodo_id' => $periodo->id
                ]);

                // Solo una matrícula por usuario como solicitado
                break;
            }
            $this->command->info("  Usuario {$userId} matriculado exitosamente.");
        }

        $this->command->info('<< SEEDER FINALIZADO CORRECTAMENTE >>');
    }
}
