<?php

namespace App\Services;

use Illuminate\Database\Connection;
use RuntimeException;
use Throwable;

/** Ejecuta exclusivamente el ensayo local; el manifiesto y la bitácora contienen datos privados. */
class EjecutorLimpiezaBd
{
    public const BASE = 'redil_mejora_bd_ejecutor_20261028';

    public const ESQUEMA = 'migracion_2025';

    private const RESTRICCIONES = [
        'mbd_encargados_grupo_grupo_id_fk', 'mbd_ingresos_ofrenda_id_fk',
        'mbd_integrantes_grupo_grupo_id_fk', 'mbd_pago_estado_tipo_fk',
        'mbd_pagos_compra_id_fk', 'mbd_pagos_registro_caja_id_fk', 'mbd_reporte_grupos_grupo_id_fk',
    ];

    public function __construct(private Connection $conexion) {}

    public function identidad(): array
    {
        $identidad = (array) $this->conexion->selectOne("SELECT current_database() AS base, current_schema() AS esquema, host(inet_server_addr()) AS servidor, inet_server_port() AS puerto, current_user AS usuario, current_setting('server_version_num') AS version, current_setting('search_path') AS search_path");
        if ($identidad['base'] !== self::BASE || $identidad['esquema'] !== self::ESQUEMA || $identidad['servidor'] !== '127.0.0.1' || (int) $identidad['puerto'] !== 5432 || ! in_array($identidad['search_path'], [self::ESQUEMA, '"'.self::ESQUEMA.'"'], true)) {
            throw new RuntimeException('Destino rechazado: solo se permite la base local independiente de ensayo.');
        }

        return $identidad;
    }

    /** Construye imágenes combinadas a partir de los tres lotes previamente autorizados. */
    public function preparar(string $fuentes, string $respaldo, string $hashRespaldo): array
    {
        $this->verificarArchivo($respaldo, $hashRespaldo);
        $filas = [];
        $orden = [];
        $origenes = [];
        foreach ([['conciliacion-local', 'plan-conciliacion.json'], ['depuracion-local', 'plan.json'], ['huerfanos-local', 'plan.json']] as [$directorio, $archivo]) {
            $rutaPlan = $fuentes.'/'.$directorio.'/'.$archivo;
            $rutaBitacora = $fuentes.'/'.$directorio.'/bitacora-apply.json';
            $plan = $this->leerJson($rutaPlan);
            $bitacora = $this->leerJson($rutaBitacora);
            $this->verificarArchivo($rutaPlan, $bitacora['plan_sha256']);
            if ($plan['database'] !== 'redil_mejora_bd_ensayo_20261028' || $plan['schema'] !== self::ESQUEMA || $bitacora['modo'] !== 'apply') {
                throw new RuntimeException('Origen de evidencia inesperado.');
            }
            $origenes[] = ['plan' => $rutaPlan, 'sha256' => hash_file('sha256', $rutaPlan), 'bitacora' => $rutaBitacora, 'bitacora_sha256' => hash_file('sha256', $rutaBitacora)];
            $imagenes = [];
            foreach ($bitacora['filas'] as $registro) {
                $imagenes[$registro['tabla']][$registro['fila']['id']][$registro['fase']] = $registro['fila'];
            }
            $cambios = $plan['patches'] ?? $plan['actualizar'];
            foreach ($plan['eliminar'] ?? [] as $tabla => $ids) {
                foreach ($ids as $id) {
                    $cambios[] = ['tabla' => $tabla, 'id' => $id, 'eliminar' => true];
                }
            }
            foreach ($cambios as $cambio) {
                $tabla = $cambio['tabla'];
                $id = $cambio['id'];
                $antes = $imagenes[$tabla][$id]['antes'] ?? throw new RuntimeException('Falta imagen anterior.');
                $despues = isset($cambio['eliminar']) ? null : ($imagenes[$tabla][$id]['despues'] ?? throw new RuntimeException('Falta imagen posterior.'));
                if (isset($filas[$tabla][$id])) {
                    if ($filas[$tabla][$id]['despues'] != $antes) {
                        throw new RuntimeException('Las imágenes de los lotes no encadenan.');
                    }
                    $filas[$tabla][$id]['despues'] = $despues;
                } else {
                    $filas[$tabla][$id] = ['antes' => $antes, 'despues' => $despues];
                }
            }
            $orden = array_values(array_unique(array_merge($orden, $plan['orden_eliminacion'] ?? [])));
        }
        ksort($filas);
        $resumen = ['eliminar' => 0, 'actualizar' => 0];
        foreach ($filas as &$registros) {
            ksort($registros);
            foreach ($registros as $registro) {
                $resumen[$registro['despues'] === null ? 'eliminar' : 'actualizar']++;
            }
        }
        unset($registros);
        if ($resumen !== ['eliminar' => 283, 'actualizar' => 90] || count($orden) !== 14) {
            throw new RuntimeException('El alcance no coincide con los lotes autorizados.');
        }

        return $this->conexion->transaction(function () use ($filas, $orden, $resumen, $origenes, $respaldo, $hashRespaldo): array {
            $identidad = $this->identidad();
            $this->bloquear();
            $this->comprobarImagenes($filas, 'antes');
            $catalogo = $this->catalogo();
            $restricciones = array_values(array_filter($catalogo['restricciones'], fn (array $fila): bool => ! $fila['validada']));
            $nombres = array_column($restricciones, 'nombre');
            sort($nombres);
            if ($nombres !== self::RESTRICCIONES) {
                throw new RuntimeException('Las restricciones pendientes no coinciden con las siete revisadas.');
            }
            $posterior = $catalogo;
            foreach ($posterior['restricciones'] as &$restriccion) {
                if (in_array($restriccion['nombre'], self::RESTRICCIONES, true)) {
                    $restriccion['validada'] = true;
                    $restriccion['definicion'] = preg_replace('/ NOT VALID$/', '', $restriccion['definicion']);
                }
            }
            unset($restriccion);

            return ['formato' => 1, 'alcance' => 'ensayo-local-exclusivo', 'identidad' => $identidad,
                'creado_utc' => gmdate('c'), 'ejecutor_sha256' => hash_file('sha256', __FILE__),
                'respaldo' => ['ruta' => $respaldo, 'sha256' => $hashRespaldo], 'fuentes' => $origenes,
                'filas' => $filas, 'orden_eliminacion' => $orden, 'resumen' => $resumen,
                'datos_antes' => $this->huellas(), 'datos_despues' => $this->huellas($filas),
                'catalogo_antes' => $catalogo, 'catalogo_despues' => $posterior,
                'secuencias' => $this->secuencias(), 'restricciones_reversion' => $restricciones];
        });
    }

    /** El registro de ejecución se confirma atómicamente con el DML y las restricciones. */
    public function ejecutar(array $plan, string $hash, bool $revertir = false, bool $simular = true): array
    {
        $inicio = microtime(true);
        $this->validarPlan($plan);
        $nivel = $this->conexion->transactionLevel();
        $this->conexion->beginTransaction();
        try {
            $this->bloquear();
            $this->conexion->statement('CREATE SCHEMA IF NOT EXISTS mejora_bd_control');
            $this->conexion->statement("CREATE TABLE IF NOT EXISTS mejora_bd_control.ejecuciones (plan_sha256 text PRIMARY KEY, estado text NOT NULL CHECK (estado IN ('aplicado','revertido')), plan jsonb NOT NULL, eventos jsonb NOT NULL)");
            $registro = $this->conexion->selectOne('SELECT estado, plan = ?::jsonb AS coincide FROM mejora_bd_control.ejecuciones WHERE plan_sha256 = ? FOR UPDATE', [$this->json($plan), $hash]);
            if ($registro && ! $registro->coincide) {
                throw new RuntimeException('La bitácora corresponde a un manifiesto distinto.');
            }
            if ($revertir && ! $registro) {
                throw new RuntimeException('No existe una aplicación confirmada de este manifiesto.');
            }
            $estado = $revertir ? 'revertido' : 'aplicado';
            $destino = $revertir ? 'antes' : 'despues';
            if ($registro && $registro->estado === $estado) {
                $this->comprobarEstado($plan, $destino);
                $this->conexion->rollBack($nivel);

                return ['resultado' => 'ya_'.$estado, 'plan_sha256' => $hash, 'segundos' => round(microtime(true) - $inicio, 3)];
            }
            $this->comprobarEstado($plan, $revertir ? 'despues' : 'antes');
            if ($revertir) {
                $this->revertirFilas($plan);
            } else {
                $this->aplicarFilas($plan);
            }
            $this->comprobarEstado($plan, $destino);
            if (microtime(true) - $inicio > 120) {
                throw new RuntimeException('El lote excedió el máximo local de 120 segundos; se revierte.');
            }
            $evento = $this->json([['accion' => $estado, 'fecha_utc' => gmdate('c'), 'usuario' => $plan['identidad']['usuario']]]);
            $this->conexion->statement('INSERT INTO mejora_bd_control.ejecuciones (plan_sha256,estado,plan,eventos) VALUES (?, ?, ?::jsonb, ?::jsonb) ON CONFLICT (plan_sha256) DO UPDATE SET estado = EXCLUDED.estado, eventos = ejecuciones.eventos || EXCLUDED.eventos', [$hash, $estado, $this->json($plan), $evento]);
            if ($simular) {
                $this->conexion->rollBack($nivel);
            } else {
                $this->conexion->commit();
            }

            return ['resultado' => $simular ? 'simulado_'.$estado : $estado, 'plan_sha256' => $hash, 'resumen' => $plan['resumen'], 'tablas_verificadas' => count($plan['datos_antes']), 'segundos' => round(microtime(true) - $inicio, 3)];
        } catch (Throwable $error) {
            $this->conexion->rollBack($nivel);
            throw $error;
        }
    }

    public function verificar(array $plan): array
    {
        $this->validarPlan($plan);

        return $this->conexion->transaction(function () use ($plan): array {
            $this->bloquear();
            $huellas = $this->huellas();
            $fase = $huellas === $plan['datos_antes'] ? 'antes' : 'despues';
            $this->comprobarEstado($plan, $fase);

            return ['resultado' => 'verificado_'.$fase, 'tablas' => count($huellas), 'filas' => array_sum(array_column($huellas, 'filas'))];
        });
    }

    public function leerJson(string $ruta): array
    {
        $contenido = file_get_contents($ruta);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer la evidencia.');
        }

        return json_decode($contenido, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
    }

    public function verificarArchivo(string $ruta, string $hash): void
    {
        if (! preg_match('/^[a-f0-9]{64}$/D', $hash) || ! is_file($ruta) || ! hash_equals($hash, hash_file('sha256', $ruta))) {
            throw new RuntimeException('Archivo ausente o SHA-256 distinto del aprobado.');
        }
    }

    private function validarPlan(array $plan): void
    {
        if (($plan['formato'] ?? null) !== 1 || ($plan['alcance'] ?? null) !== 'ensayo-local-exclusivo' || $plan['identidad'] !== $this->identidad()) {
            throw new RuntimeException('Manifiesto incompatible con la identidad local.');
        }
        $this->verificarArchivo(__FILE__, $plan['ejecutor_sha256']);
        $this->verificarArchivo($plan['respaldo']['ruta'], $plan['respaldo']['sha256']);
        foreach ($plan['fuentes'] as $fuente) {
            $this->verificarArchivo($fuente['plan'], $fuente['sha256']);
            $this->verificarArchivo($fuente['bitacora'], $fuente['bitacora_sha256']);
        }
    }

    /** Bloqueo conservador del clon: rechaza toda deriva, incluidas relaciones sin FK. */
    private function bloquear(): void
    {
        $this->identidad();
        $this->conexion->statement("SET LOCAL lock_timeout = '5s'");
        $this->conexion->statement("SET LOCAL statement_timeout = '60s'");
        $this->conexion->statement("SET LOCAL idle_in_transaction_session_timeout = '60s'");
        $this->conexion->selectOne("SELECT pg_advisory_xact_lock(hashtext('mejora-bd-ejecutor-local'))");
        $tablas = $this->tablas();
        if (count($tablas) !== 413) {
            throw new RuntimeException('El inventario de tablas del clon cambió.');
        }
        $this->conexion->statement('LOCK TABLE '.implode(', ', array_map($this->tabla(...), $tablas)).' IN SHARE ROW EXCLUSIVE MODE');
    }

    private function tablas(): array
    {
        return array_column($this->conexion->select('SELECT tablename FROM pg_tables WHERE schemaname = ? ORDER BY tablename COLLATE "C"', [self::ESQUEMA]), 'tablename');
    }

    private function tabla(string $nombre): string
    {
        return $this->identificador(self::ESQUEMA).'.'.$this->identificador($nombre);
    }

    private function identificador(string $nombre): string
    {
        if (! preg_match('/^[a-z_][a-z0-9_]*$/D', $nombre)) {
            throw new RuntimeException('Identificador inválido en el manifiesto.');
        }

        return '"'.$nombre.'"';
    }

    private function json(array $valor): string
    {
        return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function huellas(array $cambios = []): array
    {
        $resultado = [];
        foreach ($this->tablas() as $nombre) {
            $tabla = $this->tabla($nombre);
            $expresion = 'to_jsonb(t)';
            $excluir = [];
            $casos = [];
            $parametros = [];
            foreach ($cambios[$nombre] ?? [] as $id => $fila) {
                if ($fila['despues'] === null) {
                    $excluir[] = (int) $id;
                } else {
                    $casos[] = 'WHEN t.id = ? THEN to_jsonb(jsonb_populate_record(NULL::'.$tabla.', ?::jsonb))';
                    array_push($parametros, $id, $this->json($fila['despues']));
                }
            }
            if ($casos) {
                $expresion = 'CASE '.implode(' ', $casos).' ELSE to_jsonb(t) END';
            }
            $filtro = $excluir ? ' WHERE id NOT IN ('.implode(',', $excluir).')' : '';
            $resultado[$nombre] = (array) $this->conexion->selectOne("SELECT count(*)::integer AS filas, md5(coalesce(string_agg(fila::text, E'\n' ORDER BY fila::text COLLATE \"C\"), '')) AS huella FROM (SELECT ".$expresion.' AS fila FROM '.$tabla.' t'.$filtro.') s', $parametros);
        }

        return $resultado;
    }

    private function catalogo(): array
    {
        $consultas = [
            'columnas' => 'SELECT table_name, column_name, ordinal_position, data_type, udt_name, is_nullable, column_default, character_maximum_length, numeric_precision, numeric_scale FROM information_schema.columns WHERE table_schema = ? ORDER BY table_name COLLATE "C", ordinal_position',
            'restricciones' => 'SELECT c.relname AS tabla, conname AS nombre, contype AS tipo, convalidated AS validada, pg_get_constraintdef(p.oid) AS definicion FROM pg_constraint p JOIN pg_class c ON c.oid=p.conrelid WHERE connamespace = ?::regnamespace ORDER BY c.relname COLLATE "C", conname COLLATE "C"',
            'indices' => 'SELECT tablename, indexname, indexdef FROM pg_indexes WHERE schemaname = ? ORDER BY indexname COLLATE "C"',
            'triggers' => 'SELECT c.relname AS tabla, t.tgname, t.tgenabled, pg_get_triggerdef(t.oid) AS definicion FROM pg_trigger t JOIN pg_class c ON c.oid=t.tgrelid WHERE c.relnamespace=?::regnamespace AND NOT t.tgisinternal ORDER BY c.relname COLLATE "C", t.tgname COLLATE "C"',
        ];
        $resultado = [];
        foreach ($consultas as $nombre => $sql) {
            $resultado[$nombre] = array_map(fn (object $fila): array => (array) $fila, $this->conexion->select($sql, [self::ESQUEMA]));
        }

        return $resultado;
    }

    private function secuencias(): array
    {
        $resultado = [];
        foreach ($this->conexion->select('SELECT sequencename FROM pg_sequences WHERE schemaname = ? ORDER BY sequencename COLLATE "C"', [self::ESQUEMA]) as $secuencia) {
            $resultado[$secuencia->sequencename] = (array) $this->conexion->selectOne('SELECT last_value::text, is_called FROM '.$this->tabla($secuencia->sequencename));
        }

        return $resultado;
    }

    private function comprobarEstado(array $plan, string $fase): void
    {
        if ($this->huellas() !== $plan['datos_'.$fase]) {
            throw new RuntimeException('Deriva de datos: detener; no ampliar ni sobrescribir el manifiesto.');
        }
        if ($this->catalogo() !== $plan['catalogo_'.$fase]) {
            throw new RuntimeException('Deriva de catálogo: columnas, restricciones, índices o triggers distintos.');
        }
        if ($this->secuencias() !== $plan['secuencias']) {
            throw new RuntimeException('Las secuencias cambiaron; no se reducirán ni reiniciarán.');
        }
    }

    private function comprobarImagenes(array $filas, string $fase): void
    {
        foreach ($filas as $nombre => $registros) {
            $tabla = $this->tabla($nombre);
            $columnas = array_column($this->conexion->select('SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ?', [self::ESQUEMA, $nombre]), 'column_name');
            sort($columnas);
            foreach ($registros as $id => $registro) {
                $claves = array_keys($registro[$fase]);
                sort($claves);
                if ($columnas !== $claves || (int) $registro[$fase]['id'] !== (int) $id) {
                    throw new RuntimeException('Imagen incompleta o ID distinto de la evidencia.');
                }
                $coincide = $this->conexion->selectOne('SELECT count(*)::integer AS cantidad FROM '.$tabla.' t WHERE id = ? AND to_jsonb(t) = to_jsonb(jsonb_populate_record(NULL::'.$tabla.', ?::jsonb))', [$id, $this->json($registro[$fase])]);
                if ($coincide->cantidad !== 1) {
                    throw new RuntimeException('Fila anterior distinta de la evidencia: '.$nombre.' / '.$id);
                }
            }
        }
    }

    private function actualizar(string $nombre, int $id, array $imagen): void
    {
        $tabla = $this->tabla($nombre);
        $columnas = array_values(array_diff(array_keys($imagen), ['id']));
        $asignaciones = array_map(fn (string $columna): string => $this->identificador($columna).' = s.'.$this->identificador($columna), $columnas);
        $cantidad = $this->conexion->affectingStatement('UPDATE '.$tabla.' t SET '.implode(', ', $asignaciones).' FROM jsonb_populate_record(NULL::'.$tabla.', ?::jsonb) s WHERE t.id = ?', [$this->json($imagen), $id]);
        if ($cantidad !== 1) {
            throw new RuntimeException('Cantidad actualizada inesperada.');
        }
    }

    private function aplicarFilas(array $plan): void
    {
        foreach ($plan['filas'] as $tabla => $registros) {
            foreach ($registros as $id => $fila) {
                if ($fila['despues'] !== null) {
                    $this->actualizar($tabla, $id, $fila['despues']);
                }
            }
        }
        foreach ($plan['orden_eliminacion'] as $tabla) {
            foreach ($plan['filas'][$tabla] as $id => $fila) {
                if ($fila['despues'] === null && $this->conexion->delete('DELETE FROM '.$this->tabla($tabla).' WHERE id = ?', [$id]) !== 1) {
                    throw new RuntimeException('Cantidad eliminada inesperada.');
                }
            }
        }
        foreach ($plan['restricciones_reversion'] as $restriccion) {
            $this->conexion->statement('ALTER TABLE '.$this->tabla($restriccion['tabla']).' VALIDATE CONSTRAINT '.$this->identificador($restriccion['nombre']));
        }
    }

    private function revertirFilas(array $plan): void
    {
        foreach ($plan['restricciones_reversion'] as $restriccion) {
            if (! in_array($restriccion['nombre'], self::RESTRICCIONES, true)) {
                throw new RuntimeException('Restricción no autorizada para reversión.');
            }
            $this->conexion->statement('ALTER TABLE '.$this->tabla($restriccion['tabla']).' DROP CONSTRAINT '.$this->identificador($restriccion['nombre']));
        }
        foreach (array_reverse($plan['orden_eliminacion']) as $nombre) {
            $tabla = $this->tabla($nombre);
            foreach ($plan['filas'][$nombre] as $fila) {
                if ($fila['despues'] === null) {
                    $cantidad = $this->conexion->affectingStatement('INSERT INTO '.$tabla.' SELECT * FROM jsonb_populate_record(NULL::'.$tabla.', ?::jsonb)', [$this->json($fila['antes'])]);
                    if ($cantidad !== 1) {
                        throw new RuntimeException('Cantidad restaurada inesperada.');
                    }
                }
            }
        }
        foreach ($plan['filas'] as $tabla => $registros) {
            foreach ($registros as $id => $fila) {
                if ($fila['despues'] !== null) {
                    $this->actualizar($tabla, $id, $fila['antes']);
                }
            }
        }
        foreach ($plan['restricciones_reversion'] as $restriccion) {
            $this->conexion->statement('ALTER TABLE '.$this->tabla($restriccion['tabla']).' ADD CONSTRAINT '.$this->identificador($restriccion['nombre']).' '.$restriccion['definicion']);
        }
    }
}
