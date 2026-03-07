<?php

namespace App\Livewire\Cursos\Restricciones;

use Livewire\Component;
use App\Models\Curso;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;

class GestionarPasosRequisito extends Component
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
        // Assuming EstadoPasoCrecimientoUsuario exists and has a name. If not, I might need to check its structure.
        // Based on reference, it seems to exist.
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
        $existente = $this->curso->pasosRequisito()
            ->where('paso_crecimiento_id', $this->pasoSeleccionado)
            ->wherePivot('estado_paso_crecimiento_usuario_id', $this->estadoSeleccionado)
            ->exists();

        if ($existente) {
             $this->dispatch('msn', [
                'msn' => 'Este paso con ese estado ya está agregado.',
                'icon' => 'warning'
            ]);
            return;
        }

        $maxIndice = $this->curso->pasosRequisito()->max('indice') ?? 0;

        $this->curso->pasosRequisito()->attach($this->pasoSeleccionado, [
            'estado_paso_crecimiento_usuario_id' => $this->estadoSeleccionado,
            'estado' => $this->estadoSeleccionado,
            'indice' => $maxIndice + 1,
        ]);

        $this->reset(['pasoSeleccionado', 'estadoSeleccionado']);

         $this->dispatch('msn', [
            'msn' => 'Requisito de paso agregado.',
            'icon' => 'success'
        ]);

        // Refresh handled by livewire re-render
    }

    public function eliminarPaso($pasoId)
    {
        $this->curso->pasosRequisito()->detach($pasoId);

        // Re-index
        $pasos = $this->curso->pasosRequisito()->orderBy('pivot_indice')->get();
        foreach ($pasos as $index => $paso) {
            $this->curso->pasosRequisito()->updateExistingPivot($paso->id, [
                'indice' => $index + 1
            ]);
        }

         $this->dispatch('msn', [
            'msn' => 'Requisito eliminado.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        // Need to load pivotal data properly
        $pasosRequisito = $this->curso->pasosRequisito()
            ->orderBy('curso_paso_requisito.indice')
            ->get();

        // We might need to manually map the "estado" name if relation is not set up in PasoCrecimiento model for pivot.
        // But let's assume we can fetch it or just show the ID for now if relation is complex,
        // however, better to fetch the state object.
        // The relationship in Curso model uses ->withPivot.
        // Ideally we would want to get the state name.
        // Let's iterate and find the state name from $this->estados to avoid N+1 if not eager loaded.

        return view('livewire.cursos.restricciones.gestionar-pasos-requisito', [
            'pasosRequisito' => $pasosRequisito
        ]);
    }
}
