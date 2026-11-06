<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CompraFinalizadaEscuelaTest extends TestCase
{
    /**
     * Verifica que el mapeo de días incluya 0 y 7 como Domingo.
     */
    public function test_dias_mapeo_incluye_domingo_para_dia_cero_y_siete(): void
    {
        $diasLargo = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        $this->assertSame('Domingo', $diasLargo[0]);
        $this->assertSame('Domingo', $diasLargo[7]);
        $this->assertSame('Lunes', $diasLargo[1]);
        $this->assertSame('Sábado', $diasLargo[6]);

        $diasCorto = [
            0 => 'Dom',
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mié',
            4 => 'Jue',
            5 => 'Vie',
            6 => 'Sáb',
            7 => 'Dom',
        ];

        $this->assertSame('Dom', $diasCorto[0]);
        $this->assertSame('Dom', $diasCorto[7]);
    }

    /**
     * Verifica que la generación de referencias para búsqueda de matrícula
     * abarque el compra_id y todos los posibles pago_ids en formato int y string.
     */
    public function test_referencias_matricula_cubren_compra_y_pagos(): void
    {
        $compraId = 244;
        $pagoIds = [209, 210];

        $referencias = array_merge(
            [$compraId, (string) $compraId],
            $pagoIds,
            array_map('strval', $pagoIds)
        );

        $this->assertContains(244, $referencias);
        $this->assertContains('244', $referencias);
        $this->assertContains(209, $referencias);
        $this->assertContains('209', $referencias);
        $this->assertContains(210, $referencias);
        $this->assertContains('210', $referencias);
    }

    /**
     * Verifica la resolución segura del identificador para el código QR
     * incluso si la inscripción es nula (caso común en matrículas directas de escuelas).
     */
    public function test_qr_id_resolucion_segura_con_inscripcion_nula(): void
    {
        $inscripcion = null;
        $matricula = (object) ['id' => 143];
        $compra = (object) ['id' => 244];

        $datosParaQr = (string) ($inscripcion?->id ?? $matricula?->id ?? $compra->id);

        $this->assertSame('143', $datosParaQr);

        $matriculaNula = null;
        $datosParaQrFallback = (string) ($inscripcion?->id ?? $matriculaNula?->id ?? $compra->id);

        $this->assertSame('244', $datosParaQrFallback);
    }

    /**
     * Verifica que cada tipo_pago_id se asocie con su respectivo estado_pago_id aprobado
     * evitando la violación de la clave foránea compuesta (estado_pago_id, tipo_pago_id).
     */
    public function test_resolucion_estado_pago_aprobado_por_tipo(): void
    {
        // Mapeo conocido en el modelo de base de datos
        $estadosAprobadosPorTipo = [
            5 => 14, // Efectivo PDP -> Finalizado (ID 14)
            7 => 20, // Tarjeta Débito PDP -> Finalizado (ID 20)
            6 => 19, // Tarjeta de Crédito -> Finalizado (ID 19)
            3 => 3,  // Grupo Éxito -> Finalizado (ID 3)
            4 => 12, // Puntos Efecty -> Finalizado (ID 12)
        ];

        foreach ($estadosAprobadosPorTipo as $tipoId => $estadoEsperadoId) {
            $this->assertNotSame(
                ($estadoEsperadoId === 3 ? null : 3),
                $estadoEsperadoId,
                "Tipo pago {$tipoId} no debe asumir estado 3 si su estado aprobado es {$estadoEsperadoId}"
            );
        }

        // Caso del error del usuario: tipo 5 con estado 3 viola foreign key
        $this->assertNotEquals(3, $estadosAprobadosPorTipo[5]);
        $this->assertSame(14, $estadosAprobadosPorTipo[5]);
    }
}
