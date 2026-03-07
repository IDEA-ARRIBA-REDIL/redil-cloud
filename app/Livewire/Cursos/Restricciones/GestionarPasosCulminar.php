<?php

namespace App\Livewire\Cursos\Restricciones;

use Livewire\Component;
use App\Models\Curso;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;

class GestionarPasosCulminar extends Component
{
    public Curso $curso;
    public $pasoSeleccionado = '';
    public $estadoSeleccionado = '';

    public $pasos = [];
    public $estados = [];

    public function mount(Curso $curso)
    {
        $this->curso = $curso;
        $this->cargarDatos();
    }

    private function cargarDatos()
    {
        $this->pasos = PasoCrecimiento::orderBy('nombre')->get();
        $this->estados = EstadoPasoCrecimientoUsuario::orderBy('nombre')->get();
    }

    public function agregarPaso()
    {
        $this->validate([
            'pasoSeleccionado' => 'required|exists:pasos_crecimiento,id',
            'estadoSeleccionado' => 'required|exists:estados_pasos_crecimiento_usuario,id',
        ], [
            'pasoSeleccionado.required' => 'Debes seleccionar un paso.',
            'estadoSeleccionado.required' => 'Debes seleccionar un estado.',
        ]);

        // Verificar existencia
        $existente = $this->curso->pasosCulminar()
            ->where('paso_crecimiento_id', $this->pasoSeleccionado)
            ->exists();

        if ($existente) {
             $this->dispatch('msn', [
                'msn' => 'Este paso ya está configurado para culminar.',
                'icon' => 'warning'
            ]);
            return;
        }

        $maxIndice = $this->curso->pasosCulminar()->max('indice') ?? 0;

        $this->curso->pasosCulminar()->attach($this->pasoSeleccionado, [
            'estado_paso_crecimiento_usuario_id' => $this->estadoSeleccionado,
            'estado' => $this->estadoSeleccionado,
            'indice' => $maxIndice + 1,
        ]);

        $this->reset(['pasoSeleccionado', 'estadoSeleccionado']);

         $this->dispatch('msn', [
            'msn' => 'Paso a culminar agregado.',
            'icon' => 'success'
        ]);
    }

    public function eliminarPaso($pasoId)
    {
        $this->curso->pasosCulminar()->detach($pasoId);

        // Re-index
        $pasos = $this->curso->pasosCulminar()->orderBy('pivot_indice')->get();
        foreach ($pasos as $index => $paso) {
            $this->curso->pasosCulminar()->updateExistingPivot($paso->id, [
                'indice' => $index + 1
            ]);
        }

         $this->dispatch('msn', [
            'msn' => 'Paso eliminado.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        $pasosCulminar = $this->curso->pasosCulminar()
            ->orderBy('curso_paso_culminar.indice')
            ->get();

        return view('livewire.cursos.restricciones.gestionar-pasos-culminar', [
            'pasosCulminar' => $pasosCulminar
        ]);
    }
}
