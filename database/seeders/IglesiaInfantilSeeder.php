<?php

namespace Database\Seeders;

use App\Models\EstacionSalonInfantil;
use App\Models\ReporteReunion;
use App\Models\Reunion;
use App\Models\SalonInfantil;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IglesiaInfantilSeeder extends Seeder
{
    /**
     * Crea datos de prueba para el módulo de Iglesia Infantil:
     * - 2 estaciones globales (General, Cambio de Pañales)
     * - 4 salones con sus estaciones asignadas
     * - 1 ReporteReunion de prueba con iglesia infantil habilitada
     */
    public function run(): void
    {
        // =====================================================================
        // 1. ESTACIONES
        // =====================================================================

        $estacionGeneral = EstacionSalonInfantil::firstOrCreate(
            ['nombre' => 'General'],
            ['descripcion' => 'Área general del salón para niños que no requieren cuidado especial.']
        );

        $estacionPañales = EstacionSalonInfantil::firstOrCreate(
            ['nombre' => 'Cambio de Pañales'],
            ['descripcion' => 'Estación especializada para cambio de pañales y cuidado de bebés.']
        );

        // =====================================================================
        // 2. SALONES
        // =====================================================================

        $salones = [
            [
                'nombre' => 'Bebés (0-2 años)',
                'descripcion' => 'Salón para bebés de 0 a 2 años. Requiere personal especializado.',
                'activo' => true,
                'estaciones' => [$estacionGeneral->id, $estacionPañales->id],
            ],
            [
                'nombre' => 'Párvulos (2-4 años)',
                'descripcion' => 'Salón para niños de 2 a 4 años con actividades lúdicas.',
                'activo' => true,
                'estaciones' => [$estacionGeneral->id, $estacionPañales->id],
            ],
            [
                'nombre' => 'Preescolar (4-6 años)',
                'descripcion' => 'Salón para niños de 4 a 6 años con enseñanza bíblica básica.',
                'activo' => true,
                'estaciones' => [$estacionGeneral->id],
            ],
            [
                'nombre' => 'Primaria (6-10 años)',
                'descripcion' => 'Salón para niños de 6 a 10 años con dinámica interactiva.',
                'activo' => true,
                'estaciones' => [$estacionGeneral->id],
            ],
        ];

        foreach ($salones as $datos) {
            $estacionIds = $datos['estaciones'];
            unset($datos['estaciones']);

            $salon = SalonInfantil::firstOrCreate(
                ['nombre' => $datos['nombre']],
                $datos
            );

            // Asignar estaciones al salón (sin duplicados)
            $salon->estaciones()->syncWithoutDetaching($estacionIds);
        }

        // =====================================================================
        // 3. REPORTE DE REUNIÓN DE PRUEBA
        // =====================================================================

        // Busca la primera reunión disponible en el sistema para asociar el reporte
        $reunion = Reunion::withTrashed()->first();

        if (! $reunion) {
            $this->command->warn('⚠️  No se encontró ninguna Reunión en la base de datos. Crea al menos una reunión antes de correr este seeder.');

            return;
        }

        $fechaPrueba = Carbon::now()->addDays(3)->toDateString(); // 3 días hacia adelante

        $reporte = ReporteReunion::firstOrCreate(
            [
                'reunion_id' => $reunion->id,
                'fecha' => $fechaPrueba,
            ],
            [
                'hora' => $reunion->hora ?? '10:00:00',
                'predicador' => 1,
                'predicador_diezmos' => 0,
                'predicador_invitado' => null,
                'predicador_diezmos_invitado' => 0,
                'observaciones' => 'Reporte de prueba generado por IglesiaInfantilSeeder.',
                'invitados' => 0,
                'cantidad_asistencias' => 0,
                'total_ofrendas' => 0,
                'autor_creacion' => 1,
                'conteo_preliminar' => 0,
                'habilitar_reserva' => false,
                'dias_plazo_reserva' => 0,
                'aforo' => 0,
                'aforo_ocupado' => 0,
                'habilitar_reserva_invitados' => false,
                'cantidad_maxima_reserva_invitados' => 0,
                'solo_reservados_pueden_asistir' => false,
                'habilitar_preregistro_iglesia_infantil' => true, // ← HABILITADO
                'visualizaciones' => 0,
            ]
        );

        // =====================================================================
        // 4. RESUMEN
        // =====================================================================

        $this->command->info('✅ IglesiaInfantilSeeder completado:');
        $this->command->info("   → Estación: {$estacionGeneral->nombre} (ID: {$estacionGeneral->id})");
        $this->command->info("   → Estación: {$estacionPañales->nombre} (ID: {$estacionPañales->id})");
        $this->command->info('   → 4 salones creados/verificados con estaciones asignadas');
        $this->command->info("   → ReporteReunion de prueba: Reunión '{$reunion->nombre}' — Fecha: {$fechaPrueba} (ID: {$reporte->id})");
        $this->command->info('   → habilitar_preregistro_iglesia_infantil = TRUE ✔');
    }
}
