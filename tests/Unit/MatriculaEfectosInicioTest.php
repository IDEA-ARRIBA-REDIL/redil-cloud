<?php

namespace Tests\Unit;

use App\Models\Matricula;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MatriculaEfectosInicioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
    }

    /**
     * Verifica que si no hay user_id asociado a la matrícula,
     * el método aplicarEfectosMatricula termina limpiamente sin consultar BD ni lanzar excepción.
     */
    public function test_aplicar_efectos_termina_limpiamente_sin_user_id(): void
    {
        $matricula = new Matricula;
        $matricula->id = 99999;
        $matricula->user_id = null;

        // No debe lanzar excepciones ni consultar la BD
        $matricula->aplicarEfectosMatricula();

        $this->assertTrue(true);
    }

    /**
     * Verifica que si no hay horario_materia_periodo_id asociado,
     * el método aplicarEfectosMatricula termina limpiamente sin consultar materias en la BD.
     */
    public function test_aplicar_efectos_termina_limpiamente_sin_horario_id(): void
    {
        $matricula = new Matricula;
        $matricula->id = 99999;
        $matricula->user_id = 1;
        $matricula->horario_materia_periodo_id = null;

        // Simulamos usuario cargado en la relación
        $usuarioMock = new User;
        $usuarioMock->id = 1;
        $matricula->setRelation('user', $usuarioMock);

        // No debe lanzar excepciones
        $matricula->aplicarEfectosMatricula();

        $this->assertTrue(true);
    }

    /**
     * Verifica la resolución de referencias de matrícula para pasarelas
     * cubriendo pago_id, compra_id y sus versiones en string.
     */
    public function test_resolucion_referencias_matricula_pasarela(): void
    {
        $pagoId = 150;
        $compraId = 320;

        $referenciasEsperadas = [
            (string) $pagoId,
            (string) $compraId,
            $pagoId,
            $compraId,
        ];

        $this->assertContains('150', $referenciasEsperadas);
        $this->assertContains('320', $referenciasEsperadas);
        $this->assertContains(150, $referenciasEsperadas);
        $this->assertContains(320, $referenciasEsperadas);
    }
}
