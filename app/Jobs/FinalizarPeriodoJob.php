<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Periodo;
use App\Services\ServicioValidacionPeriodo;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Mail;
use App\Mail\PeriodoFinalizadoMail;

class FinalizarPeriodoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutos, ahora cada job es más corto

    protected $periodo;
    protected $paginaActual;
    protected $alumnosPorPagina;

    /**
     * @param Periodo $periodo El periodo a procesar.
     * @param int $paginaActual El lote actual de alumnos a procesar.
     * @param int $alumnosPorPagina El tamaño de cada lote.
     */
    public function __construct(Periodo $periodo, int $paginaActual = 1, int $alumnosPorPagina = 200)
    {
        $this->periodo = $periodo;
        $this->paginaActual = $paginaActual;
        $this->alumnosPorPagina = $alumnosPorPagina;
    }

    public function handle(ServicioValidacionPeriodo $servicioValidacion): void
    {
        Log::info("Iniciando Job de Finalización para Periodo ID: {$this->periodo->id} - Lote/Página: {$this->paginaActual}");

        try {
            // Le pedimos al servicio que procese solo un lote de alumnos
            $alumnosProcesados = $servicioValidacion->procesarLoteDeAlumnos($this->periodo, $this->paginaActual, $this->alumnosPorPagina);
            // ===== ¡AQUÍ SE LLAMA LA NUEVA FUNCIÓN! =====
            // Una vez que no hay más alumnos que procesar, cerramos los componentes.
            //$servicioValidacion->finalizarComponentesDelPeriodo($this->periodo);


            // Si se procesaron alumnos en este lote, significa que podría haber más
            if ($alumnosProcesados > 0) {
                // Despachamos el siguiente lote
                Log::info("Lote {$this->paginaActual} completado. Despachando siguiente lote...");
                self::dispatch($this->periodo, $this->paginaActual + 1, $this->alumnosPorPagina);
            } else {
                // Si no se procesaron alumnos, significa que hemos terminado.
                Log::info("No se encontraron más alumnos. Proceso de finalización COMPLETO para el Periodo ID: {$this->periodo->id}");
                Log::info("Job: El periodo ID {$this->periodo->id} ha sido finalizado completamente.");

                Mail::to('idea.arriba@gmail.com')->send(new PeriodoFinalizadoMail($this->periodo));
                Log::info("Correo de notificación de finalización enviado a idea.arriba@gmail.com.");
            }
        } catch (Throwable $e) {
            Log::error("El Job de Finalización FALLÓ para el Periodo ID: {$this->periodo->id} en el Lote: {$this->paginaActual}. Error: {$e->getMessage()}");
            $this->fail($e);
        }
    }
}
