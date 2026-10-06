<?php

namespace App\Jobs;

use App\Enums\EstadoInformeCola;
use App\Exports\MegaInformeExport;
use App\Mail\MegaInformeGeneradoMail;
use App\Models\InformeEnCola;
use App\Services\MegaInformeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class GenerarMegaInformeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tiempo máximo de ejecución del job (10 minutos).
     */
    public int $timeout = 600;

    /**
     * Número máximo de intentos.
     */
    public int $tries = 2;

    public function __construct(public readonly int $informeEnColaId) {}

    public function handle(MegaInformeService $service): void
    {
        $inicio = microtime(true);
        $informeEnCola = InformeEnCola::find($this->informeEnColaId);

        if (! $informeEnCola) {
            Log::warning("GenerarMegaInformeJob: No se encontró el registro InformeEnCola ID: {$this->informeEnColaId}");
            return;
        }

        try {
            // 1. Marcar como procesando
            $informeEnCola->estado = EstadoInformeCola::Procesando;
            $informeEnCola->save();

            Log::info("GenerarMegaInformeJob: Iniciando procesamiento de InformeEnCola ID: {$informeEnCola->id}");

            // 2. Ejecutar procesamiento analítico
            $resultado = $service->procesar($informeEnCola);

            // 3. Generar y guardar el archivo Excel en storage privado
            $nombreArchivo = $resultado['nombreArchivo'];
            $relPath = 'informes/' . $nombreArchivo . '.xlsx';

            // Asegurar directorio en storage
            Storage::disk('local')->makeDirectory('informes');

            Excel::store(
                new MegaInformeExport($resultado['tablaHtml'], $resultado['informe']),
                $relPath,
                'local'
            );

            $fullPath = Storage::disk('local')->path($relPath);
            $fin = microtime(true);
            $duracionSegundos = (int) round($fin - $inicio);

            // 4. Actualizar registro en base de datos
            $informeEnCola->nombre_archivo = $nombreArchivo;
            $informeEnCola->estado = EstadoInformeCola::Completado;
            $informeEnCola->tiempo_ejecucion_segundos = $duracionSegundos;
            $informeEnCola->error_message = null;
            $informeEnCola->save();

            Log::info("GenerarMegaInformeJob: Generación completada con éxito para InformeEnCola ID: {$informeEnCola->id} en {$duracionSegundos}s");

            // 5. Enviar notificación por correo si tiene email configurado
            if (! empty($informeEnCola->email)) {
                Mail::to($informeEnCola->email)->send(
                    new MegaInformeGeneradoMail($informeEnCola, $resultado['informe'], $fullPath)
                );
                Log::info("GenerarMegaInformeJob: Correo enviado a {$informeEnCola->email}");
            }
        } catch (Throwable $e) {
            $fin = microtime(true);
            $duracionSegundos = (int) round($fin - $inicio);

            $informeEnCola->estado = EstadoInformeCola::Fallido;
            $informeEnCola->error_message = $e->getMessage();
            $informeEnCola->tiempo_ejecucion_segundos = $duracionSegundos;
            $informeEnCola->save();

            Log::error("GenerarMegaInformeJob: Error al procesar InformeEnCola ID {$informeEnCola->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->fail($e);
        }
    }
}
