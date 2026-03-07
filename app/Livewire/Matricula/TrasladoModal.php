<?php

namespace App\Livewire\Matricula;

use Livewire\Component;
use App\Models\Matricula;
use App\Models\Sede;
use App\Models\User;
use App\Models\HorarioMateriaPeriodo;
use App\Models\TrasladoMatriculaLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class TrasladoModal extends Component
{
    public $showModal = false;
    public ?Matricula $matriculaActual = null;

    // Propiedades para los nuevos selectores
    public $sedesDisponibles = [];
    public $horariosDisponibles = [];
    public $sedeId;
    public $horarioDestinoId;
    public $horarioDestino;
    public $user;

    #[On('abrirModalTraslado')]
    public function openModal($matriculaId)
    {
        $this->reset(['sedeId', 'horarioDestinoId', 'sedesDisponibles', 'horariosDisponibles']);

        $this->matriculaActual = Matricula::with('horarioMateriaPeriodo.materiaPeriodo.materia')->find($matriculaId);
        
        if ($this->matriculaActual) {
            // Cargamos las sedes que tienen horarios para la misma materia en el mismo periodo.
            $this->sedesDisponibles = Sede::whereHas('aulas.horariosBase.horariosMateriaPeriodo', function($query) {
                $query->where('id', '!=', $this->matriculaActual->horario_materia_periodo_id) // Excluir el horario actual
                      ->whereHas('materiaPeriodo', function($q_mp) {
                            $q_mp->where('periodo_id', $this->matriculaActual->periodo_id)
                                 ->where('materia_id', $this->matriculaActual->horarioMateriaPeriodo->materiaPeriodo->materia_id);
                      });
            })->distinct()->orderBy('nombre')->get();
        }

        $this->showModal = true;
         $this->user=User::find(Auth::id());

        
    }

    // Se ejecuta cuando el usuario selecciona una sede.
    public function updatedSedeId($value)
    {
        $this->reset(['horarioDestinoId', 'horariosDisponibles']);
        if ($value) {
            // Buscamos los horarios disponibles en la sede seleccionada, para la misma materia y periodo,
            // excluyendo el horario en el que ya está matriculado.
            $this->horariosDisponibles = HorarioMateriaPeriodo::with(['horarioBase.aula', 'maestros.user'])
                ->where('id', '!=', $this->matriculaActual->horario_materia_periodo_id)
                ->whereHas('materiaPeriodo', function($query) {
                    $query->where('materia_id', $this->matriculaActual->horarioMateriaPeriodo->materiaPeriodo->materia_id)
                          ->where('periodo_id', $this->matriculaActual->periodo_id);
                })
                ->whereHas('horarioBase.aula', function($query) use ($value) {
                    $query->where('sede_id', $value);
                })
                ->get();
                
        }
    }

   public function trasladar()
    {   
       
        
        $this->validate(['horarioDestinoId' => 'required'], ['horarioDestinoId.required' => 'Debe seleccionar un nuevo horario.']);

        $horarioOrigen = $this->matriculaActual->horarioMateriaPeriodo;
        $horarioDestino = HorarioMateriaPeriodo::find($this->horarioDestinoId);

        if ($horarioDestino->cupos_disponibles < 1) {
            $this->dispatch('swal:error', [[
                'title' => 'Error de Cupos', 
                'text' => 'No hay cupos disponibles en el horario de destino.'
            ]]);
            return;
        }

        try {
            DB::transaction(function () use ($horarioOrigen, $horarioDestino) {
                // Lógica de traslado...
                $horarioOrigen->increment('cupos_disponibles');
                $horarioDestino->decrement('cupos_disponibles');

                $this->matriculaActual->update(['horario_materia_periodo_id' => $this->horarioDestinoId]);
                $this->matriculaActual->estadoAcademicoClase()->update(['horario_materia_periodo_id' => $this->horarioDestinoId]);

                TrasladoMatriculaLog::create([
                    'matricula_id' => $this->matriculaActual->id,
                    'origen_horario_id' => $horarioOrigen->id,
                    'destino_horario_id' => $horarioDestino->id,
                    'user_id' =>$this->user->id ,
                ]);
            });
        } catch (\Exception $e) {
            // --- INICIO DE LA MODIFICACIÓN PARA DEPURACIÓN ---

            // 1. Obtenemos el mensaje de error específico de la excepción.
            $errorReal = $e->getMessage();

            // 2. (Recomendado) Guardamos el error completo en el log de Laravel
            //    para poder revisarlo en el servidor (storage/logs/laravel.log).
            Log::error("Error en traslado de matrícula: " . $errorReal);

            // 3. Enviamos el mensaje de error real a la alerta para poder verlo en pantalla.
            $this->dispatch('swal:error', [
                'title' => '¡Ocurrió un Error de Base de Datos!',
                'text' => 'Error técnico: ' . $errorReal
            ]);
            
            // --- FIN DE LA MODIFICACIÓN ---
            return;
        }

        // Feedback de éxito y recarga
        $this->closeModal();
        $this->dispatch('swal:success', [
            'title' => '¡Traslado Exitoso!', 
            'text' => 'El estudiante ha sido trasladado de horario correctamente.'
        ]);
        $this->dispatch('recargarPagina');
        
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.matricula.traslado-modal');
    }
}