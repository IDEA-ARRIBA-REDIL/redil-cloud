<?php

namespace App\Livewire\Matricula;

use Livewire\Component;
use App\Models\TrasladoMatriculaLog;
use App\Models\HorarioMateriaPeriodo;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class GestionarSolicitudesTraslado extends Component
{
    use WithPagination;

    public $filtroNombre = '';

    protected $listeners = ['aprobarSolicitud', 'rechazarSolicitud', 'recargarPagina' => '$refresh'];

    public function updatingFiltroNombre()
    {
        $this->resetPage();
    }

    public function aprobarSolicitud($solicitudId)
    {
        $solicitud = TrasladoMatriculaLog::with('matricula', 'horarioOrigen', 'horarioDestino')->find($solicitudId);

        if (!$solicitud || $solicitud->estado !== TrasladoMatriculaLog::ESTADO_PENDIENTE) {
            $this->dispatch('swal:error', ['title'=>'Error', 'text'=>'Solicitud no válida o ya procesada.']);
            return;
        }

        $horarioDestino = HorarioMateriaPeriodo::find($solicitud->destino_horario_id);

        // Validar cupos nuevamente al momento de aprobar
        if ($horarioDestino->cupos_disponibles < 1) {
            $this->dispatch('swal:error', ['title'=>'Sin Cupos', 'text'=>'Ya no hay cupos disponibles en el horario de destino.']);
            return;
        }

        try {
            DB::transaction(function () use ($solicitud, $horarioDestino) {
                // 1. Ejecutar el traslado real
                $matricula = $solicitud->matricula;
                $horarioOrigen = $solicitud->horarioOrigen;

                // Ajustar cupos
                $horarioOrigen->increment('cupos_disponibles');
                $horarioDestino->decrement('cupos_disponibles');

                // Actualizar Matrícula
                $matricula->update(['horario_materia_periodo_id' => $horarioDestino->id]);

                // Actualizar Estado Académico (Notas/Asistencia) para que apunte al nuevo curso
                $matricula->estadoAcademicoClase()->update(['horario_materia_periodo_id' => $horarioDestino->id]);

            // 2. Actualizar Estado Solicitud
                $solicitud->update([
                    'estado' => TrasladoMatriculaLog::ESTADO_APROBADO
                ]);
            });

            // Enviar correo de notificación
            if ($solicitud->user && $solicitud->user->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($solicitud->user->email)->send(new \App\Mail\TrasladoAprobado($solicitud));
                } catch (\Exception $e) {
                    // Log error or ignore to not break the flow
                    \Illuminate\Support\Facades\Log::error("Error enviando correo de traslado aprobado: " . $e->getMessage());
                }
            }

            $this->dispatch('swal:success', ['title'=>'Aprobada', 'text'=>'Traslado ejecutado correctamente.']);
        } catch (\Exception $e) {
            $this->dispatch('swal:error', ['title'=>'Error', 'text'=>$e->getMessage()]);
        }
    }

    public function rechazarSolicitud($solicitudId, $motivo)
    {
        $solicitud = TrasladoMatriculaLog::find($solicitudId);
         if (!$solicitud || $solicitud->estado !== TrasladoMatriculaLog::ESTADO_PENDIENTE) {
             return;
         }

         $solicitud->update([
             'estado' => TrasladoMatriculaLog::ESTADO_RECHAZADO,
             'motivo_rechazo' => $motivo
         ]);

         // Enviar correo de notificación
         if ($solicitud->user && $solicitud->user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($solicitud->user->email)->send(new \App\Mail\TrasladoRechazado($solicitud));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error enviando correo de traslado rechazado: " . $e->getMessage());
            }
        }

         $this->dispatch('swal:success', ['title'=>'Rechazada', 'text'=>'Solicitud rechazada.']);
    }

    public function render()
    {
        $solicitudes = TrasladoMatriculaLog::with(['user', 'matricula.horarioMateriaPeriodo.materiaPeriodo.materia', 'horarioOrigen.horarioBase', 'horarioDestino.horarioBase'])
            ->where('estado', TrasladoMatriculaLog::ESTADO_PENDIENTE)
            ->whereHas('user', function($q){
                $q->Where('primer_nombre', 'like', '%'.$this->filtroNombre.'%')
                  ->orWhere('primer_apellido', 'like', '%'.$this->filtroNombre.'%');
            })
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('livewire.matricula.gestionar-solicitudes-traslado', [
            'solicitudes' => $solicitudes
        ]);
    }
}
