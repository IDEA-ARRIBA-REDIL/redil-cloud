<?php

namespace App\Console\Commands;

use App\Jobs\NotificarLiderReporteJob;
use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\ReporteGrupo;
use App\Models\SemanaDeshabilitada;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use stdClass;
use Illuminate\Support\Facades\Mail;
use App\Mail\DefaultMail;

class ReportesNotificarPendientesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reportes:notificar-pendientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica a los encargados de grupos sobre reportes pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $configuracion = Configuracion::first();

        if(!$configuracion->dia_corte_reportes_grupos) {
            $this->info('Corte no configurado (día u hora nulos).');
            return 0;
        }

        // Paso 0: Validación de Horario Programado
        $diaProgramado = $configuracion->dia_recordatorio_para_reporte_grupos;
        $horaProgramada = $configuracion->hora_recordatorio_para_reporte_grupos;

        if (is_null($diaProgramado) || is_null($horaProgramada)) {
            $this->info('Recordatorio no configurado (día u hora nulos).');
            return 0;
        }

        $now = Carbon::now();      
        $diaActualRedil = $now->dayOfWeek + 1; // Convertir 0-6 (Dom-Sab) a 1-7 (Dom-Sab)
        
        if ($diaActualRedil != $diaProgramado) {
            $this->info("No es el día programado. Hoy: $diaActualRedil, Programado: $diaProgramado.");
            return 0;
        }

        // Validar Hora (HH:MM)
        // Formato BD: HH:MM:SS. Comparamos los primeros 5 caracteres (HH:MM).
        $horaActualStr = $now->format('H:i');
        $horaProgramadaStr = substr($horaProgramada, 0, 5);

        if ($horaActualStr !== $horaProgramadaStr) {
            $this->info("No es la hora programada. Actual: $horaActualStr, Programada: $horaProgramadaStr.");
            return 0;
        }

        // Paso 1: Validación de Semana Habilitada
        $anio = $now->year;
        $semana = $now->weekOfYear;

        $semanaDeshabilitada = SemanaDeshabilitada::where('anio', $anio)
            ->where('numero_semana', $semana)
            ->exists();

        if ($semanaDeshabilitada) {
            $this->info('La semana actual está deshabilitada para reportes.');
            return 0;
        }
              
        // Paso 2: Definir Rango de Fechas (Ventana de Auditoría)
        $diaCorte = $configuracion->dia_corte_reportes_grupos; // 1=Domingo...7=Sábado

        // Calcular fecha corte.
        // Se alinea ahora con la lógica de Grupo.php que usa semanas Lunes-Domingo.
        // "primerDiaSemana" (Lunes).
        $inicioSemana = $now->copy()->startOfWeek(Carbon::MONDAY);

        // El rango de la semana es de Lunes a Domingo.
        $fechaInicioSemana = $inicioSemana->format('Y-m-d');
        $fechaFinSemana = $inicioSemana->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // Paso 3: Consulta de Grupos
        $grupos = Grupo::with(['encargados', 'tipoGrupo'])
            //->where('id', 4)
            ->where('dado_baja', false)
            ->whereHas('tipoGrupo', function ($q) {
                $q->where('seguimiento_actividad', true);
            })
            ->whereDoesntHave('reportes', function ($q) use ($fechaInicioSemana, $fechaFinSemana) {
                $q->whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
                  ->where('finalizado', true);
            })
            ->chunk(100, function ($gruposChunk) use ($fechaInicioSemana, $fechaFinSemana) {
                
                $notificacionesPorLider = [];

                foreach ($gruposChunk as $grupo) {
                    
                    // Verificar estado específico
                    $reporteSinFinalizar = $grupo->reportes()
                        ->whereBetween('fecha', [$fechaInicioSemana, $fechaFinSemana])
                        ->where('finalizado', false)
                        ->first();

                    $estado = $reporteSinFinalizar ? 'Sin finalizar' : 'Sin reporte';
                    
                    foreach ($grupo->encargados as $encargado) {
                        if (!isset($notificacionesPorLider[$encargado->id])) {
                            $notificacionesPorLider[$encargado->id] = [
                                'user' => $encargado,
                                'grupos' => []
                            ];
                        }
                        
                        $notificacionesPorLider[$encargado->id]['grupos'][] = [
                            'nombre' => $grupo->nombre,
                            'estado' => $estado
                        ];
                    }
                }

                // Paso 5: Disparo
                foreach ($notificacionesPorLider as $data) {
                    NotificarLiderReporteJob::dispatch($data['user'], $data['grupos']);
                }
            });

        $this->info('Proceso de notificación de reportes pendientes completado.');

        return 0;
    }
}
