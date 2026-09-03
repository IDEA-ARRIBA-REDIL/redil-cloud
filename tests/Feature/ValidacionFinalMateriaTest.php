<?php

namespace Tests\Feature;

use App\Services\ServicioValidacionMateriaPeriodo;
use App\Services\ServicioValidacionPeriodo;
use Tests\TestCase;

class ValidacionFinalMateriaTest extends TestCase
{
    public function test_a_blocked_enrollment_cannot_pass_in_either_finalization_flow(): void
    {
        $resultado = (object) [
            'matricula_bloqueada' => true,
            'habilitar_calificaciones' => false,
            'habilitar_asistencias' => false,
            'nota_final_calculada' => 5.0,
            'total_asistencias' => 20,
            'asistencias_minimas' => 0,
        ];

        $this->assertFalse($this->determinarEstadoFinal(new ServicioValidacionPeriodo, $resultado)['aprobado']);
        $this->assertFalse($this->determinarEstadoFinal(new ServicioValidacionMateriaPeriodo, $resultado)['aprobado']);
    }

    /**
     * @param  ServicioValidacionPeriodo|ServicioValidacionMateriaPeriodo  $servicio
     * @return array{aprobado: bool, motivo: ?string}
     */
    private function determinarEstadoFinal(object $servicio, object $resultado): array
    {
        $metodo = new \ReflectionMethod($servicio, 'determinarEstadoFinal');

        return $metodo->invoke($servicio, $resultado, 3.5);
    }
}
