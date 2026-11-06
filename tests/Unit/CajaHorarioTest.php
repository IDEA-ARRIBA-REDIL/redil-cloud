<?php

namespace Tests\Unit;

use App\Models\Caja;
use Tests\TestCase;

class CajaHorarioTest extends TestCase
{
    /**
     * Prueba que sin horario configurado la caja siempre está en horario.
     */
    public function test_caja_sin_horario_configurado_siempre_esta_en_horario(): void
    {
        $caja = new Caja([
            'nombre' => 'Caja 1',
            'estado' => true,
            'hora_apertura' => null,
            'hora_cierre' => null,
        ]);

        $this->assertTrue($caja->estaEnHorario());
        $this->assertTrue($caja->puedeOperar());
        $this->assertNull($caja->horario_atencion);
        $this->assertNull($caja->obtenerMensajeFueraDeHorario());
    }

    /**
     * Prueba el horario estándar (ej: 07:00 a 15:00).
     */
    public function test_caja_con_horario_estandar(): void
    {
        $caja = new Caja([
            'nombre' => 'Taquilla 111',
            'estado' => true,
            'hora_apertura' => '07:00:00',
            'hora_cierre' => '15:00:00',
        ]);

        $this->assertSame('7:00 AM - 3:00 PM', $caja->horario_atencion);

        // Dentro del horario (ej: 10:30 AM)
        $this->assertTrue($caja->estaEnHorario('10:30:00'));
        $this->assertTrue($caja->puedeOperar('10:30:00'));
        $this->assertNull($caja->obtenerMensajeFueraDeHorario('10:30:00'));

        // Justo al abrir y al cerrar
        $this->assertTrue($caja->estaEnHorario('07:00:00'));
        $this->assertTrue($caja->estaEnHorario('15:00:00'));

        // Caso del usuario: 1:00 AM (01:00:00) debe estar FUERA de horario
        $this->assertFalse($caja->estaEnHorario('01:00:00'));
        $this->assertFalse($caja->puedeOperar('01:00:00'));
        $this->assertStringContainsString('fuera de su horario de atención', (string) $caja->obtenerMensajeFueraDeHorario('01:00:00'));

        // Fuera de horario en la tarde (ej: 16:00 / 4:00 PM)
        $this->assertFalse($caja->estaEnHorario('16:00:00'));
        $this->assertFalse($caja->puedeOperar('16:00:00'));
    }

    /**
     * Prueba el horario nocturno que cruza la medianoche (ej: 22:00 a 06:00).
     */
    public function test_caja_con_horario_nocturno(): void
    {
        $caja = new Caja([
            'nombre' => 'Taquilla Nocturna',
            'estado' => true,
            'hora_apertura' => '22:00:00',
            'hora_cierre' => '06:00:00',
        ]);

        $this->assertTrue($caja->estaEnHorario('23:30:00'));
        $this->assertTrue($caja->estaEnHorario('01:30:00'));
        $this->assertTrue($caja->estaEnHorario('06:00:00'));

        $this->assertFalse($caja->estaEnHorario('12:00:00'));
        $this->assertFalse($caja->estaEnHorario('21:59:59'));
    }

    /**
     * Prueba que una caja inactiva no pueda operar aunque esté en horario.
     */
    public function test_caja_inactiva_no_puede_operar(): void
    {
        $caja = new Caja([
            'nombre' => 'Caja Inactiva',
            'estado' => false,
            'hora_apertura' => '07:00:00',
            'hora_cierre' => '15:00:00',
        ]);

        $this->assertTrue($caja->estaEnHorario('10:00:00'));
        $this->assertFalse($caja->puedeOperar('10:00:00'));
        $this->assertStringContainsString('inactiva o cerrada', (string) $caja->obtenerMensajeFueraDeHorario('10:00:00'));
    }
}
