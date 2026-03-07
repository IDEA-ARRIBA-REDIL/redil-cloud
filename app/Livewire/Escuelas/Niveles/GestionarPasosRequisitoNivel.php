<?php

namespace App\Livewire\Escuelas\Niveles;

use Livewire\Component;
use App\Models\NivelAgrupacion;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;
use Illuminate\Support\Collection;

class GestionarPasosRequisitoNivel extends Component
{
    public $nivel;
    public $pasosRequisito;
    public $pasoSeleccionado;
    public $estadoSeleccionado;

    // Draft Mode Properties
    public $draftMode = false;
    public $draftItems = [];

    protected $listeners = ['eliminarNivelPasoRequisito'];

    public function mount(NivelAgrupacion $nivel)
    {
        $this->nivel = $nivel;
        $this->draftMode = !$nivel->exists;

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        if (!$this->draftMode) {
            $this->pasosRequisito = $this->nivel->procesosPrerrequisito()
                ->orderBy('nivel_proceso_prerrequisito.indice')
                ->get();
        } else {
            $this->pasosRequisito = collect();
        }
    }

    public function agregarPaso()
    {
        $this->validate([
            'pasoSeleccionado' => 'required',
            'estadoSeleccionado' => 'required'
        ]);

        if ($this->draftMode) {
            $pasoModel = PasoCrecimiento::find($this->pasoSeleccionado);
            $estadoModel = EstadoPasoCrecimientoUsuario::find($this->estadoSeleccionado);

            foreach($this->draftItems as $item) {
                if ($item['paso_id'] == $this->pasoSeleccionado && $item['estado_id'] == $this->estadoSeleccionado) {
                    $this->dispatch('msn', msnTexto: 'Este requisito ya está agregado', msnIcono: 'warning');
                    return;
                }
            }

            $this->draftItems[] = [
                'paso_id' => $this->pasoSeleccionado,
                'paso_nombre' => $pasoModel->nombre,
                'estado_id' => $this->estadoSeleccionado,
                'estado_nombre' => $estadoModel->nombre,
                'estado_color' => $estadoModel->color ?? 'primary',
                'temp_id' => uniqid()
            ];

        } else {
             $exists = $this->nivel->procesosPrerrequisito()
                ->where('paso_crecimiento_id', $this->pasoSeleccionado)
                ->wherePivot('estado_paso_crecimiento_usuario_id', $this->estadoSeleccionado)
                ->exists();

            if ($exists) {
                $this->dispatch('msn', msnTexto: 'Este requisito ya está agregado', msnIcono: 'warning');
                return;
            }

            $maxIndice = $this->nivel->procesosPrerrequisito()->max('indice') ?? 0;

            $this->nivel->procesosPrerrequisito()->attach($this->pasoSeleccionado, [
                'estado_paso_crecimiento_usuario_id' => $this->estadoSeleccionado,
                'estado_proceso' => $this->estadoSeleccionado, // Legacy
                'indice' => $maxIndice + 1,
            ]);

            $this->cargarDatos();
        }

        $this->reset(['pasoSeleccionado', 'estadoSeleccionado']);
        $this->dispatch('msn', msnTitulo: 'Agregado', msnTexto: 'Requisito agregado correctamente.', msnIcono: 'success');
    }

    public function confirmarEliminacionNivelPasoRequisito($pasoId)
    {
        $this->dispatch('confirmarEliminacion',
            id: $pasoId,
            titulo: '¿Eliminar requisito?',
            texto: 'Este paso dejará de ser un requisito para el grado.',
            metodo: 'eliminarNivelPasoRequisito'
        );
    }

    public function eliminarNivelPasoRequisito($pasoId)
    {
        if ($this->draftMode) {
             $this->draftItems = array_filter($this->draftItems, function($item) use ($pasoId) {
                 return $item['temp_id'] != $pasoId;
             });
        } else {
            // DB removal logic needs pivot discrimination if multiple same steps allowed (usually not for prerequisite)
            // Assuming unique pair step-state or just step for removal?
            // Usually we pass the pivot ID or logic to detach specific.
            // Here using detach by ID for simplicity as standard belongsToMany
            $this->nivel->procesosPrerrequisito()->detach($pasoId);
            $this->cargarDatos();
        }

        $this->dispatch('msn', msnTitulo: 'Eliminado', msnTexto: 'Requisito eliminado correctamente.', msnIcono: 'success');
    }

    public function render()
    {
        return view('livewire.escuelas.niveles.gestionar-pasos-requisito-nivel', [
            'pasosDisponibles' => PasoCrecimiento::orderBy('nombre')->get(),
            'estadosDisponibles' => EstadoPasoCrecimientoUsuario::orderBy('nombre')->get(),
            'pasosRequisito' => $this->pasosRequisito,
            'draftItems' => $this->draftItems
        ]);
    }
}
