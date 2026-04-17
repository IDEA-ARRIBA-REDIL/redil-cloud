<?php

namespace App\Livewire\PlanLector;

use Livewire\Component;
use App\Models\PlanLector;
use App\Models\PlanLectorDia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Lectura extends Component
{
    public $plan;
    public $dias;
    public $diaActualId;
    public $completados = [];
    public $finalizado = false;
    public $calificacion = 0;
    
    // Estado de la UI
    public $mostrandoExito = false;
    public $planCompletado = false;

    public function mount(PlanLector $plan, $dia_inicial_id = null)
    {
        $usuario = auth()->user();
        
        // Verificar si el usuario está inscrito
        $inscripcion = DB::table('plan_lector_users')
            ->where('plan_lector_id', $plan->id)
            ->where('user_id', $usuario->id)
            ->first();
            
        if (!$inscripcion) {
            return redirect()->route('planes-lectores.inicio');
        }

        $this->plan = $plan;
        $this->dias = $plan->dias()->orderBy('dia')->get();
        
        // Obtener IDs de días completados
        $this->completados = DB::table('plan_lector_dia_users')
            ->where('user_id', $usuario->id)
            ->whereIn('plan_lector_dia_id', $this->dias->pluck('id'))
            ->pluck('plan_lector_dia_id')
            ->toArray();

        // Determinar qué día cargar
        if ($dia_inicial_id) {
            $this->diaActualId = $dia_inicial_id;
        } else {
            // Buscar el primer día no completado
            $siguienteDia = $this->dias->first(fn($d) => !in_array($d->id, $this->completados));
            
            // Si todos están completados, cargar el último día o mostrar pantalla final
            if (!$siguienteDia) {
                $this->diaActualId = $this->dias->last()->id;
                $this->finalizado = true;
            } else {
                $this->diaActualId = $siguienteDia->id;
            }
        }

        if ($inscripcion->calificacion_usuario) {
            $this->calificacion = $inscripcion->calificacion_usuario;
        }

        $this->planCompletado = ($inscripcion->estado ?? '') === 'completado';
    }

    public function seleccionarDia($diaId)
    {
        // Si el plan ya está finalizado, permitimos navegación libre
        $progresoCompletado = DB::table('plan_lector_users')
            ->where('plan_lector_id', $this->plan->id)
            ->where('user_id', auth()->id())
            ->where('estado', 'completado')
            ->exists();

        if ($progresoCompletado) {
            $this->diaActualId = $diaId;
            $this->finalizado = false;
            return;
        }

        // Verificar si el día es accesible (es el primero, ya está completado, o el anterior está completado)
        $actualIndex = $this->dias->search(fn($d) => $d->id == $diaId);
        
        if ($actualIndex === 0) {
            $this->diaActualId = $diaId;
        } elseif ($actualIndex > 0) {
            $diaAnteriorId = $this->dias[$actualIndex - 1]->id;
            if (in_array($diaAnteriorId, $this->completados)) {
                $this->diaActualId = $diaId;
            } else {
                session()->flash('warning', 'Debes completar los días anteriores para acceder a este contenido.');
                return;
            }
        }

        $this->finalizado = false;
    }

    public function marcarComoLeido()
    {
        $usuario = auth()->user();
        
        // Registrar completado
        DB::table('plan_lector_dia_users')->updateOrInsert(
            ['plan_lector_dia_id' => $this->diaActualId, 'user_id' => $usuario->id],
            ['fecha_completado' => now()]
        );

        if (!in_array($this->diaActualId, $this->completados)) {
            $this->completados[] = $this->diaActualId;
        }

        // Actualizar progreso total
        $totalDias = count($this->dias);
        $completadosCount = count($this->completados);
        $porcentaje = ($totalDias > 0) ? round(($completadosCount / $totalDias) * 100) : 100;

        DB::table('plan_lector_users')
            ->where('plan_lector_id', $this->plan->id)
            ->where('user_id', $usuario->id)
            ->update([
                'porcentaje_progreso' => $porcentaje,
                'estado' => ($porcentaje >= 100) ? 'completado' : 'inscrito'
            ]);

        // Buscar siguiente día
        $actualIndex = $this->dias->search(fn($d) => $d->id == $this->diaActualId);
        
        if ($actualIndex !== false && $actualIndex < count($this->dias) - 1) {
            $this->diaActualId = $this->dias[$actualIndex + 1]->id;
            $this->dispatch('dia-cambiado', diaId: $this->diaActualId);
        } else {
            // Es el último día
            $this->finalizado = true;
            $this->mostrandoExito = true;
        }
    }

    public function guardarCalificacion($estrellas)
    {
        $this->calificacion = $estrellas;
        
        DB::table('plan_lector_users')
            ->where('plan_lector_id', $this->plan->id)
            ->where('user_id', auth()->id())
            ->update(['calificacion_usuario' => $estrellas]);
            
        // Opcional: Recalcular promedio del plan (podría hacerse vía event/job)
        $this->recalcularPromedioPlan();
        
        session()->flash('success', '¡Gracias por calificar este plan!');
    }


    protected function recalcularPromedioPlan()
    {
        $this->plan->recalcularPromedio();
    }

    public function finalizar()
    {
        return redirect()->route('planes-lectores.inicio');
    }

    public function render()
    {
        $diaActual = PlanLectorDia::with('contenidos.tipoContenido')->find($this->diaActualId);
        
        return view('livewire.plan-lector.lectura', [
            'diaActual' => $diaActual
        ]);
    }
}
