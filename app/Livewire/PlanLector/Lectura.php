<?php

namespace App\Livewire\PlanLector;

use App\Models\PlanLector;
use App\Models\PlanLectorDia;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

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

    public $modoPreview = false;

    public $mostrandoExitoDia = false;

    public $diaCompletadoNumero = 1;

    public ?int $diaActualRealId = null;

    public function getDiaActualRealId()
    {
        foreach ($this->dias as $dia) {
            if (! in_array($dia->id, $this->completados)) {
                return $dia->id;
            }
        }

        return $this->dias->isNotEmpty() ? $this->dias->last()->id : null;
    }

    public function mount(PlanLector $plan, $dia_inicial_id = null)
    {
        $usuario = auth()->user();

        // Verificar si el usuario está inscrito
        $inscripcion = DB::table('plan_lector_users')
            ->where('plan_lector_id', $plan->id)
            ->where('user_id', $usuario->id)
            ->first();

        $this->plan = $plan;
        $this->dias = $plan->dias()->orderBy('dia')->get();

        if (! $inscripcion) {
            $this->modoPreview = true;
            if ($this->dias->isNotEmpty()) {
                $this->diaActualId = $this->dias->first()->id;
            }

            return;
        }

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
            // Si todos están completados, cargar el primer día en modo repaso (sin activar finalizado)
            $siguienteDia = $this->dias->first(fn ($d) => ! in_array($d->id, $this->completados));

            if (! $siguienteDia) {
                $this->diaActualId = $this->dias->isNotEmpty() ? $this->dias->first()->id : null;
                $this->finalizado = false;
            } else {
                $this->diaActualId = $siguienteDia->id;
            }
        }

        if ($inscripcion->calificacion_usuario) {
            $this->calificacion = $inscripcion->calificacion_usuario;
        }

        $this->planCompletado = ($inscripcion->estado ?? '') === 'completado';
        $this->diaActualRealId = $this->getDiaActualRealId();
    }

    public function comenzarPlan()
    {
        $usuario = auth()->user();

        // Evitar que el usuario se inscriba si no cumple con las restricciones (Validación del backend)
        $puedeVer = PlanLector::forUser($usuario)->where('planes_lectores.id', $this->plan->id)->exists();

        if (! $puedeVer || ! $this->plan->estado) {
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Error', msnTexto: 'No tienes permitido acceder a este plan lector en estos momentos.');

            return;
        }

        // Crear la relación si no existe
        if (! $usuario->planesLectoresInscritos()->where('plan_lector_id', $this->plan->id)->exists()) {
            $usuario->planesLectoresInscritos()->attach($this->plan->id, [
                'estado' => 'inscrito',
                'fecha_inscripcion' => now(),
                'porcentaje_progreso' => 0,
            ]);
        }

        // Cambiar el estado para mostrar la interfaz de lectura normal
        $this->modoPreview = false;

        // Cargar datos normales de lectura
        $this->completados = [];

        if ($this->dias->isNotEmpty()) {
            $this->diaActualId = $this->dias->first()->id;
        }

        $this->diaActualRealId = $this->getDiaActualRealId();
        $this->dispatch('update-swiper');
    }

    public function retrocederDia()
    {
        $actualIndex = $this->dias->search(fn ($d) => $d->id == $this->diaActualId);

        if ($actualIndex !== false && $actualIndex > 0) {
            // Retroceder al día anterior
            $this->diaActualId = $this->dias[$actualIndex - 1]->id;
            $this->dispatch('dia-cambiado', diaId: $this->diaActualId);
        } else {
            // Si es el primer día, salir al inicio
            return redirect()->route('planes-lectores.inicio');
        }
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
        $actualIndex = $this->dias->search(fn ($d) => $d->id == $diaId);

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

        $diaActualNumero = $this->dias->firstWhere('id', $this->diaActualId)->dia ?? 1;

        // Registrar completado
        DB::table('plan_lector_dia_users')->updateOrInsert(
            ['plan_lector_dia_id' => $this->diaActualId, 'user_id' => $usuario->id],
            ['fecha_completado' => now()]
        );

        if (! in_array($this->diaActualId, $this->completados)) {
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
                'estado' => ($porcentaje >= 100) ? 'completado' : 'inscrito',
            ]);

        $this->diaActualRealId = $this->getDiaActualRealId();

        if ($porcentaje >= 100) {
            // Es el último día
            $this->finalizado = true;
            $this->mostrandoExito = true;
        } else {
            // Completó un día intermedio, mostramos pantalla de éxito del día
            $this->diaCompletadoNumero = $diaActualNumero;
            $this->mostrandoExitoDia = true;
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
            'diaActual' => $diaActual,
        ]);
    }
}
