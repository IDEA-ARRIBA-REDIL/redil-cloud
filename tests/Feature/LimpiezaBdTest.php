<?php

namespace Tests\Feature;

use App\Services\EjecutorLimpiezaBd;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use RuntimeException;
use Tests\TestCase;

class LimpiezaBdTest extends TestCase
{
    private ?Connection $conexion = null;

    private EjecutorLimpiezaBd $ejecutor;

    private array $plan;

    private string $hash;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('MEJORA_BD_LIMPIEZA_DATABASE') !== EjecutorLimpiezaBd::BASE) {
            $this->markTestSkipped('Requiere autorización explícita para el clon local del ejecutor.');
        }
        $config = config('database.connections.pgsql');
        $this->assertContains($config['host'], ['127.0.0.1', 'localhost']);
        unset($config['url'], $config['read'], $config['write']);
        $config['host'] = '127.0.0.1';
        $config['database'] = EjecutorLimpiezaBd::BASE;
        $config['search_path'] = EjecutorLimpiezaBd::ESQUEMA;
        config(['database.connections.limpieza_bd_test' => $config]);
        $this->conexion = app(DatabaseManager::class)->connection('limpieza_bd_test');
        $this->ejecutor = new EjecutorLimpiezaBd($this->conexion);
        $this->ejecutor->identidad();
        $ruta = getenv('MEJORA_BD_LIMPIEZA_PLAN') ?: storage_path('app/private/mejora-bd-20261028/ejecutor-local/manifiesto.json');
        $this->plan = $this->ejecutor->leerJson($ruta);
        $this->hash = hash_file('sha256', $ruta);
        $this->conexion->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->conexion !== null) {
            $this->conexion->rollBack(0);
            app(DatabaseManager::class)->purge('limpieza_bd_test');
        }
        parent::tearDown();
    }

    private function estadoBitacora(): ?string
    {
        if ($this->conexion->selectOne("SELECT to_regclass('mejora_bd_control.ejecuciones') AS tabla")->tabla === null) {
            return null;
        }

        return $this->conexion->selectOne('SELECT estado FROM mejora_bd_control.ejecuciones WHERE plan_sha256 = ?', [$this->hash])?->estado;
    }

    public function test_simulacion_revierte_datos_restricciones_y_bitacora(): void
    {
        $resultado = $this->ejecutor->ejecutar($this->plan, $this->hash);
        $this->assertSame('simulado_aplicado', $resultado['resultado']);
        $this->assertNull($this->estadoBitacora());
        $this->assertSame('verificado_antes', $this->ejecutor->verificar($this->plan)['resultado']);
    }

    public function test_ida_reversion_y_repeticiones_preservan_las_413_tablas_y_secuencias(): void
    {
        $this->assertSame('aplicado', $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false)['resultado']);
        $this->assertSame('aplicado', $this->estadoBitacora());
        $this->assertSame(278260, $this->ejecutor->verificar($this->plan)['filas']);
        $this->assertSame('ya_aplicado', $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false)['resultado']);
        $this->assertSame('simulado_revertido', $this->ejecutor->ejecutar($this->plan, $this->hash, revertir: true)['resultado']);
        $this->assertSame('aplicado', $this->estadoBitacora());
        $this->assertSame('verificado_despues', $this->ejecutor->verificar($this->plan)['resultado']);
        $this->assertSame('revertido', $this->ejecutor->ejecutar($this->plan, $this->hash, revertir: true, simular: false)['resultado']);
        $this->assertSame('revertido', $this->estadoBitacora());
        $this->assertSame(278543, $this->ejecutor->verificar($this->plan)['filas']);
        $this->assertSame('ya_revertido', $this->ejecutor->ejecutar($this->plan, $this->hash, revertir: true, simular: false)['resultado']);
    }

    public function test_no_revierte_sin_aplicacion_confirmada(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No existe una aplicación confirmada');
        $this->ejecutor->ejecutar($this->plan, $this->hash, revertir: true, simular: false);
    }

    public function test_fila_modificada_impide_la_limpieza_sin_sobrescribirla(): void
    {
        $this->conexion->update('UPDATE migracion_2025.pagos SET valor = valor + 1 WHERE id = 137');
        $valor = $this->conexion->selectOne('SELECT valor::text FROM migracion_2025.pagos WHERE id = 137')->valor;
        try {
            $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false);
            $this->fail('Debió detenerse por deriva.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Deriva de datos', $error->getMessage());
            $this->assertSame($valor, $this->conexion->selectOne('SELECT valor::text FROM migracion_2025.pagos WHERE id = 137')->valor);
            $this->assertNull($this->estadoBitacora());
        }
    }

    public function test_dependencia_nueva_no_se_borra_por_ampliacion_del_alcance(): void
    {
        $this->conexion->statement("INSERT INTO migracion_2025.historial_modificacion_pagos SELECT (jsonb_populate_record(NULL::migracion_2025.historial_modificacion_pagos, to_jsonb(t) || '{\"id\":99999999}'::jsonb)).* FROM migracion_2025.historial_modificacion_pagos t LIMIT 1");
        try {
            $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false);
            $this->fail('Debió rechazar la nueva dependencia.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Deriva de datos', $error->getMessage());
            $this->assertSame(1, $this->conexion->selectOne('SELECT count(*)::integer AS n FROM migracion_2025.historial_modificacion_pagos WHERE id=99999999')->n);
            $this->assertNull($this->estadoBitacora());
        }
    }

    public function test_reversion_con_movimiento_posterior_se_detiene(): void
    {
        $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false);
        $this->conexion->update('UPDATE migracion_2025.pagos SET valor = valor + 1 WHERE id = 137');
        try {
            $this->ejecutor->ejecutar($this->plan, $this->hash, revertir: true, simular: false);
            $this->fail('Debió conservar el movimiento posterior.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Deriva de datos', $error->getMessage());
            $this->assertSame('aplicado', $this->estadoBitacora());
            $this->assertSame(0, $this->conexion->selectOne('SELECT count(*)::integer AS n FROM migracion_2025.pagos WHERE id=135')->n);
        }
    }

    public function test_error_despues_de_actualizar_revierte_toda_la_limpieza(): void
    {
        $fallar = true;
        $this->conexion->beforeExecuting(function (string $consulta) use (&$fallar): void {
            if ($fallar && str_starts_with($consulta, 'DELETE FROM "migracion_2025"')) {
                $fallar = false;
                throw new RuntimeException('Fallo inyectado después de las actualizaciones.');
            }
        });
        try {
            $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false);
            $this->fail('Debió revertir la transacción.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Fallo inyectado', $error->getMessage());
            $this->assertNull($this->estadoBitacora());
            $this->assertSame('verificado_antes', $this->ejecutor->verificar($this->plan)['resultado']);
        }
    }

    public function test_error_durante_reversion_no_deja_filas_reinsertadas_ni_fk_retiradas(): void
    {
        $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false);
        $fallar = true;
        $this->conexion->beforeExecuting(function (string $consulta) use (&$fallar): void {
            if ($fallar && str_starts_with($consulta, 'UPDATE "migracion_2025"')) {
                $fallar = false;
                throw new RuntimeException('Fallo inyectado después de reinsertar.');
            }
        });
        try {
            $this->ejecutor->ejecutar($this->plan, $this->hash, revertir: true, simular: false);
            $this->fail('Debió revertir la recuperación incompleta.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Fallo inyectado', $error->getMessage());
            $this->assertSame('aplicado', $this->estadoBitacora());
            $this->assertSame('verificado_despues', $this->ejecutor->verificar($this->plan)['resultado']);
        }
    }

    public function test_cambio_de_catalogo_impide_aplicar(): void
    {
        $this->conexion->statement('ALTER TABLE migracion_2025.pagos ADD CONSTRAINT prueba_deriva CHECK (id > 0)');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Deriva de catálogo');
        $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false);
    }

    public function test_diferencia_en_secuencias_no_se_corrige_reduciendo_contadores(): void
    {
        $secuencia = array_key_first($this->plan['secuencias']);
        $this->plan['secuencias'][$secuencia]['last_value'] = '-999';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Las secuencias cambiaron');
        $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false);
    }

    public function test_search_path_public_es_rechazado(): void
    {
        $this->conexion->statement('SET LOCAL search_path TO public');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Destino rechazado');
        $this->ejecutor->ejecutar($this->plan, $this->hash, simular: false);
    }

    public function test_sha_incorrecto_es_rechazado(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SHA-256 distinto');
        $this->ejecutor->verificarArchivo(__FILE__, str_repeat('0', 64));
    }
}
