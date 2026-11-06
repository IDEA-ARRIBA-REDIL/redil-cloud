<?php

namespace App\Console\Commands;

use App\Services\EjecutorLimpiezaBd;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use RuntimeException;
use Throwable;

class EjecutarLimpiezaBd extends Command
{
    protected $signature = 'bd:limpieza-local {accion=simular : preparar, simular, aplicar, simular-reversion, revertir o verificar}
        {--plan= : Ruta privada del manifiesto; preparar exige un archivo nuevo}
        {--sha256= : SHA-256 del manifiesto revisado}
        {--fuentes= : Directorio privado con los tres lotes autorizados}
        {--respaldo= : Dump previo a la conciliación, restaurado en el clon}
        {--respaldo-sha256= : SHA-256 del dump verificado}';

    protected $description = 'Ensaya limpieza y reversión con manifiesto cerrado; solo admite el clon local independiente.';

    public function handle(DatabaseManager $bases): int
    {
        umask(0077);
        try {
            $accion = $this->argument('accion');
            if (! in_array($accion, ['preparar', 'simular', 'aplicar', 'simular-reversion', 'revertir', 'verificar'], true)) {
                throw new RuntimeException('Acción desconocida.');
            }
            $ruta = $this->rutaPrivada((string) $this->option('plan'));
            $config = config('database.connections.pgsql');
            if (! in_array($config['host'] ?? null, ['127.0.0.1', 'localhost'], true) || (int) ($config['port'] ?? 5432) !== 5432) {
                throw new RuntimeException('Configuración rechazada: requiere PostgreSQL local en puerto 5432.');
            }
            unset($config['url'], $config['read'], $config['write']);
            $config['host'] = '127.0.0.1';
            $config['database'] = EjecutorLimpiezaBd::BASE;
            $config['search_path'] = EjecutorLimpiezaBd::ESQUEMA;
            config(['database.connections.mejora_bd_ejecutor' => $config]);
            $bases->purge('mejora_bd_ejecutor');
            $ejecutor = new EjecutorLimpiezaBd($bases->connection('mejora_bd_ejecutor'));
            if ($accion === 'preparar') {
                $plan = $ejecutor->preparar(
                    $this->rutaPrivada((string) $this->option('fuentes')),
                    $this->rutaPrivada((string) $this->option('respaldo')),
                    (string) $this->option('respaldo-sha256'),
                );
                $this->guardarNuevo($ruta, $plan);
                $resultado = ['resultado' => 'preparado', 'plan_sha256' => hash_file('sha256', $ruta), 'resumen' => $plan['resumen']];
            } else {
                $hash = (string) $this->option('sha256');
                $ejecutor->verificarArchivo($ruta, $hash);
                $plan = $ejecutor->leerJson($ruta);
                $resultado = $accion === 'verificar'
                    ? $ejecutor->verificar($plan)
                    : $ejecutor->ejecutar($plan, $hash, in_array($accion, ['revertir', 'simular-reversion'], true), ! in_array($accion, ['aplicar', 'revertir'], true));
            }
            $this->line(json_encode($resultado, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
            try {
                $this->guardarNuevo(dirname($ruta).'/resultado-'.gmdate('Ymd-His').'-'.$accion.'-'.bin2hex(random_bytes(4)).'.json', $resultado);
            } catch (Throwable) {
                $this->warn('Resultado mostrado, pero no se pudo guardar su copia en disco. Consulte la bitácora atómica mejora_bd_control.ejecuciones antes de repetir.');
            }

            return self::SUCCESS;
        } catch (QueryException) {
            $this->error('Operación SQL interrumpida. No se muestran filas privadas. Consulte el estado del manifiesto antes de repetir; un fallo de conexión durante COMMIT requiere comprobar la bitácora.');

            return self::FAILURE;
        } catch (Throwable $error) {
            $this->error($error instanceof RuntimeException ? $error->getMessage() : 'No se pudo procesar el manifiesto. Ningún error autoriza ampliar el alcance.');

            return self::FAILURE;
        } finally {
            $bases->purge('mejora_bd_ejecutor');
        }
    }

    private function rutaPrivada(string $ruta): string
    {
        $raiz = realpath(storage_path('app/private'));
        $real = realpath($ruta);
        if ($real === false && is_dir(dirname($ruta))) {
            $real = realpath(dirname($ruta)).'/'.basename($ruta);
        }
        if (! $raiz || ! $real || ! str_starts_with($real, $raiz.'/')) {
            throw new RuntimeException('Las evidencias deben estar dentro de storage/app/private.');
        }

        return $real;
    }

    private function guardarNuevo(string $ruta, array $datos): void
    {
        $texto = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $archivo = fopen($ruta, 'xb');
        if ($archivo === false) {
            throw new RuntimeException('El archivo de salida ya existe o no se puede crear.');
        }
        try {
            if (fwrite($archivo, $texto) !== strlen($texto) || ! fflush($archivo) || ! fsync($archivo)) {
                throw new RuntimeException('No se pudo guardar toda la evidencia.');
            }
        } finally {
            fclose($archivo);
        }
    }
}
