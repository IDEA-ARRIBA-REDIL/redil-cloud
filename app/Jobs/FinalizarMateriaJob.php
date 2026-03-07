<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MateriaPeriodo;
use App\Models\User;
use App\Services\ServicioValidacionMateriaPeriodo;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Mail;
use App\Mail\MateriaFinalizadaMail; // <-- CORRECCIÓN: Usamos el nuevo Mailable

class FinalizarMateriaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    protected MateriaPeriodo $materiaPeriodo;
    protected User $initiatingUser;
    protected int $paginaActual;
    protected int $alumnosPorPagina;

    public function __construct(MateriaPeriodo $materiaPeriodo, User $initiatingUser, int $paginaActual = 1, int $alumnosPorPagina = 200)
    {
        $this->materiaPeriodo = $materiaPeriodo;
        $this->initiatingUser = $initiatingUser;
        $this->paginaActual = $paginaActual;
        $this->alumnosPorPagina = $alumnosPorPagina;
    }

    public function handle(ServicioValidacionMateriaPeriodo $servicioValidacion): void
    {


        Log::info("Iniciando Job 4444 de Finalización para MateriaPeriodo ID: {$this->materiaPeriodo->id} - Lote: {$this->paginaActual}");

        try {
            $alumnosProcesados = $servicioValidacion->procesarLoteDeAlumnosPorMateria($this->materiaPeriodo, $this->paginaActual, $this->alumnosPorPagina);

            if ($alumnosProcesados > 0) {
                Log::info("Lote {$this->paginaActual} completado. Despachando siguiente lote...");
                self::dispatch($this->materiaPeriodo, $this->initiatingUser, $this->paginaActual + 1, $this->alumnosPorPagina);
            } else {
                Log::info("Proceso de finalización COMPLETO  333 para MateriaPeriodo ID: {$this->materiaPeriodo->id}");

                $adminEmail = 'idea.arriba@gmail.com';
                if ($adminEmail) {
                    // --- CORRECCIÓN: Llamamos al nuevo Mailable y le pasamos el objeto MateriaPeriodo ---
                    Mail::to($adminEmail)->send(new MateriaFinalizadaMail($this->materiaPeriodo));
                    Log::info("Correo de notificación de finalización de materia enviado a {$adminEmail}.");
                }
            }
        } catch (Throwable $e) {
            Log::error("Job FinalizarMateriaJob FALLÓ para MateriaPeriodo ID: {$this->materiaPeriodo->id}. Error: {$e->getMessage()}");
            $this->fail($e);
        }
    }
}
