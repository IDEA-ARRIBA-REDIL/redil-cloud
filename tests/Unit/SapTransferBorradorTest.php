<?php

namespace Tests\Unit;

use App\Services\SapTransferBorrador;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class SapTransferBorradorTest extends TestCase
{
    public function test_reproduce_el_total_y_columnas_de_la_muestra_pse_sin_repetir_lineas(): void
    {
        $movimientos = new Collection;
        foreach ([306579, 306600, 306532] as $id) {
            $movimientos->push((object) ['id' => $id, 'origen' => 'actividad', 'fecha' => '2026-11-03', 'valor' => 40000, 'int_id_forma_pago' => 29, 'punto_pago_id' => null, 'moneda_id' => 1, 'identificacion' => (string) $id, 'cuenta_sap' => '41709511', 'sucursal_sap' => 'INC001', 'centro_costo_sap' => 'MNO30001', 'referencia' => (string) $id, 'concepto' => 'PAGOS DE DERECHO DE GRADO 2026']);
        }

        $resultado = (new SapTransferBorrador)->generar($movimientos);
        $encabezado = array_map(fn (string $linea): array => str_getcsv($linea, "\t"), explode("\n", trim($resultado['encabezado'])));
        $detalle = array_map(fn (string $linea): array => str_getcsv($linea, "\t"), explode("\n", trim($resultado['detalle'])));

        $this->assertCount(3, $encabezado);
        $this->assertSame('120000.00', $encabezado[2][9]);
        $this->assertSame('11050513', $encabezado[2][7]);
        $this->assertCount(5, $detalle);
        $this->assertSame(['1', '2', '3'], array_column(array_slice($detalle, 2), 1));
        $this->assertEqualsCanonicalizing(['306579', '306600', '306532'], array_column(array_slice($detalle, 2), 11));
        $this->assertEquals(120000, array_sum(array_column(array_slice($detalle, 2), 3)));
        $this->assertSame([], $resultado['faltantes']);
    }

    public function test_informa_los_codigos_faltantes_sin_inventarlos(): void
    {
        $movimiento = (object) ['id' => 1, 'origen' => 'ofrenda', 'fecha' => '2026-11-03', 'valor' => 10000, 'int_id_forma_pago' => null, 'punto_pago_id' => null, 'moneda_id' => 1, 'identificacion' => '', 'cuenta_sap' => null, 'sucursal_sap' => null, 'centro_costo_sap' => null, 'referencia' => null, 'concepto' => 'Acción de gracias'];

        $resultado = (new SapTransferBorrador)->generar(new Collection([$movimiento]));

        $this->assertSame(1, $resultado['faltantes']['AccountCode']);
        $this->assertSame(1, $resultado['faltantes']['ProfitCenter']);
        $this->assertArrayNotHasKey('canal sin equivalencia confirmada', $resultado['faltantes']);
        $this->assertStringContainsString('Acción de gracias', $resultado['detalle']);
        $this->assertTrue(mb_check_encoding($resultado['detalle'], 'UTF-8'));
    }

    public function test_tarjeta_conserva_la_fecha_de_transferencia_del_ejemplo_de_vision(): void
    {
        $movimiento = (object) ['id' => 1, 'origen' => 'actividad', 'fecha' => '2026-11-04', 'valor' => 20000, 'int_id_forma_pago' => 32, 'punto_pago_id' => null, 'moneda_id' => 1, 'identificacion' => '123', 'cuenta_sap' => '41709511', 'sucursal_sap' => 'INC001', 'centro_costo_sap' => 'MNO30001', 'referencia' => 'T-1', 'concepto' => 'Evento'];

        $resultado = (new SapTransferBorrador)->generar(new Collection([$movimiento]));
        $encabezado = array_map(fn (string $linea): array => str_getcsv($linea, "\t"), explode("\n", trim($resultado['encabezado'])));

        $this->assertSame('20261104', $encabezado[2][8]);
    }

    public function test_efectivo_usa_el_id_interno_del_pago_como_transaccion(): void
    {
        $movimiento = (object) ['id' => 223, 'origen' => 'actividad', 'fecha' => '2026-11-03', 'valor' => 30000, 'int_id_forma_pago' => null, 'punto_pago_id' => 4, 'moneda_id' => 1, 'identificacion' => '123', 'cuenta_sap' => '41709511', 'sucursal_sap' => 'INC001', 'centro_costo_sap' => 'MNO30001', 'referencia' => null, 'metodo_pago' => 'Efectivo (PDP)', 'concepto' => 'Evento'];

        $resultado = (new SapTransferBorrador)->generar(new Collection([$movimiento]));
        $detalle = array_map(fn (string $linea): array => str_getcsv($linea, "\t"), explode("\n", trim($resultado['detalle'])));

        $this->assertSame('223', $detalle[2][11]);
        $this->assertArrayNotHasKey('U_MAN_TRANSACCION', $resultado['faltantes']);
    }

    public function test_detecta_cuentas_sap_de_ejemplo_sin_cambiarlas(): void
    {
        $movimiento = (object) ['id' => 1, 'origen' => 'ofrenda', 'fecha' => '2026-11-03', 'valor' => 10000, 'int_id_forma_pago' => 29, 'punto_pago_id' => null, 'moneda_id' => 1, 'identificacion' => '123', 'cuenta_sap' => 'SAP001', 'sucursal_sap' => 'INC001', 'centro_costo_sap' => 'MNO30001', 'referencia' => 'T-1', 'concepto' => 'Diezmo'];

        $resultado = (new SapTransferBorrador)->generar(new Collection([$movimiento]));

        $this->assertSame(1, $resultado['faltantes']['AccountCode con formato distinto a Visión']);
        $this->assertStringContainsString('SAP001', $resultado['detalle']);
    }
}
