<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Escuela;
use App\Models\User;
use App\Models\Maestro;
use App\Models\Periodo;
use App\Models\MateriaPeriodo;
use App\Models\CortePeriodo;
use App\Models\HorarioBase;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemPlantilla;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\Matricula;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;

class SingleStudentSetupSeeder extends Seeder
{

    // --- PARÁMETROS DE CONFIGURACIÓN ---
    const PERIODO_NOMBRE = 'PERIODO DE PRUEBA PARA ALUMNO';
    const ESCUELA_ID = 3;
    const ALUMNO_USER_ID = 6;
    const MAESTRO_USER_ID = 6;

    /**
     * Punto de entrada para ejecutar el seeder.
     */


    public function run(): void
    {
        $this->command->info('>> INICIANDO SEEDER DE ESCENARIO PARA ALUMNO ÚNICO <<');

        // 1. Limpia el escenario de prueba anterior para empezar de cero.
        $this->_cleanup();

        // 2. Construye el nuevo escenario.
        $this->_setupScenario();

        $this->command->info('>> SEEDER DE ESCENARIO PARA ALUMNO ÚNICO FINALIZADO EXITOSAMENTE <<');
    }

    /**
     * Busca y elimina el periodo de prueba creado por este seeder en ejecuciones anteriores.
     */
    private function _cleanup(): void
    {
        $this->command->info('-- Buscando y limpiando escenario de prueba anterior...');
        $periodoAnterior = Periodo::where('nombre', self::PERIODO_NOMBRE)->first();

        if ($periodoAnterior) {
            $periodoAnterior->delete(); // Gracias a onDelete('cascade'), esto limpia la mayoría de los datos.
            $this->command->info('   > Escenario anterior eliminado.');
        } else {
            $this->command->info('   > No se encontraron datos anteriores. Todo limpio.');
        }
    }

    /**
     * Construye todo el escenario de prueba.
     */
    private function _setupScenario(): void
    {
        $this->command->info('-- Construyendo nuevo escenario de prueba...');

        // 1. Verificar que los modelos base existan
        try {
            $escuela = Escuela::findOrFail(self::ESCUELA_ID);
            $alumno = User::findOrFail(self::ALUMNO_USER_ID);
            $maestroUser = User::findOrFail(self::MAESTRO_USER_ID);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->command->error('Error: No se encontró la Escuela, el Alumno o el Maestro especificado. Asegúrate de que los IDs existan.');
            return;
        }

        // 2. Crear el nuevo Periodo
        $periodo = Periodo::firstOrCreate([
            'escuela_id' => $escuela->id,
            'nombre' => self::PERIODO_NOMBRE,
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonths(5),
            'estado' => true, // Periodo activo
            'sistema_calificaciones_id' => 3, // Asumiendo un sistema de calificación
        ]);
        $this->command->info("   > Periodo '{$periodo->nombre}' creado con ID: {$periodo->id}");

        // 3. Crear instancias de Materias y Cortes para el nuevo Periodo
        $materiaPeriodos = collect();
        foreach ($escuela->materias as $materia) {
            $materiaPeriodos->push(MateriaPeriodo::firstOrCreate(['materia_id' => $materia->id, 'periodo_id' => $periodo->id]));
        }

        $cortesPeriodo = collect();
        foreach ($escuela->cortesEscuela as $corteEscuela) {
            $cortesPeriodo->push(CortePeriodo::firstOrCreate(['periodo_id' => $periodo->id, 'corte_escuela_id' => $corteEscuela->id, 'porcentaje' => $corteEscuela->porcentaje]));
        }

        // 4. Seleccionar una materia, crear un horario y asignar el maestro
        $materiaDePrueba = $materiaPeriodos->first(); // Tomamos la primera materia como ejemplo
        $horarioBase = HorarioBase::where('materia_id', $materiaDePrueba->materia_id)->first();
        if (!$horarioBase) {
            $this->command->error("Error: La materia '{$materiaDePrueba->materia->nombre}' no tiene un Horario Base configurado.");
            return;
        }

        $horario = HorarioMateriaPeriodo::firstOrCreate(['materia_periodo_id' => $materiaDePrueba->id, 'horario_base_id' => $horarioBase->id]);
        $maestro = Maestro::firstOrCreate(['user_id' => $maestroUser->id]);
        $horario->maestros()->attach($maestro->id);
        $this->command->info("   > Horario creado para la materia '{$materiaDePrueba->materia->nombre}' y maestro asignado.");

        // 5. Matricular al alumno
        $matricula = Matricula::firstOrCreate(['user_id' => $alumno->id, 'horario_materia_periodo_id' => $horario->id, 'periodo_id' => $periodo->id, 'estado_pago_matricula' => 'finalizada', 'fecha_matricula' => now()]);
        EstadoAcademico::firstOrCreate(['matricula_id' => $matricula->id, 'user_id' => $alumno->id, 'horario_materia_periodo_id' => $horario->id, 'periodo_id' => $periodo->id]);
        $this->command->info("   > Alumno (ID: {$alumno->id}) matriculado en el horario.");

        // 6. Crear los Items Calificables (las tareas) para este horario
        $itemCount = 0;
        foreach ($cortesPeriodo as $corte) {
            $plantillas = ItemPlantilla::where('materia_id', $materiaDePrueba->materia_id)
                ->where('corte_escuela_id', $corte->corte_escuela_id)->get();

            foreach ($plantillas as $plantilla) {
                ItemCorteMateriaPeriodo::firstOrCreate([
                    'corte_periodo_id' => $corte->id,
                    'horario_materia_periodo_id' => $horario->id,
                    'materia_periodo_id' => $materiaDePrueba->id,
                    'item_plantilla_id' => $plantilla->id,
                    'nombre' => $plantilla->nombre,
                    'porcentaje' => $plantilla->porcentaje_sugerido,
                ]);
                $itemCount++;
            }
        }
        $this->command->info("   > Se crearon {$itemCount} ítems calificables. El escenario está listo.");
    }
}
