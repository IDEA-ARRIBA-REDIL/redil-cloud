<?php

namespace App\Livewire\Escuelas\NivelesEscuelas;

use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\NivelEscuela;
use App\Models\PasoCrecimiento;
use Livewire\Component;

class GestionarPasosRequisito extends Component
{
    public NivelEscuela $nivel;

    public $pasoSeleccionado = '';

    public $estadoSeleccionado = '';

    public $pasos = [];

    public $estados = [];

    public $pasosRequisito = [];

    public $draftMode = false;

    public $draftItems = [];

    public function mount(NivelEscuela $nivel)
    {
        $this->nivel = $nivel;
        $this->draftMode = ! $nivel->exists;
        $this->cargarDatos();
    }

    private function cargarDatos()
    {
        $this->pasos = PasoCrecimiento::orderBy('nombre')->get();
        $this->estados = EstadoPasoCrecimientoUsuario::orderBy('nombre')->get();

        if (! $this->draftMode) {
            $this->pasosRequisito = $this->nivel->procesosPrerrequisito()
                ->orderBy('nivel_proceso_prerrequisito.indice')
                ->get();
        } else {
            $this->pasosRequisito = collect();
        }
    }

    public function agregarPaso()
    {

        $existente = $this->nivel->procesosPrerrequisito()
            ->where('paso_crecimiento_id', $this->pasoSeleccionado)
            ->exists();

        if ($existente) {
            $this->dispatch('msn', msnTexto: 'Este paso ya está agregado como requisito', msnIcono: 'warning');

            return;
        }

        if ($this->draftMode) {
            $pasoModel = PasoCrecimiento::find($this->pasoSeleccionado);
            $estadoModel = EstadoPasoCrecimientoUsuario::find($this->estadoSeleccionado);

            foreach ($this->draftItems as $item) {
                if ($item['paso_id'] == $this->pasoSeleccionado && $item['estado_id'] == $this->estadoSeleccionado) {
                    $this->dispatch('msn', msnTexto: 'Este paso ya está agregado como requisito', msnIcono: 'warning');

                    return;
                }
            }

            $this->draftItems[] = [
                'paso_id' => $this->pasoSeleccionado,
                'paso_nombre' => $pasoModel->nombre,
                'estado_id' => $this->estadoSeleccionado,
                'estado_nombre' => $estadoModel->nombre,
                'estado_color' => $estadoModel->color ?? 'primary',
                'temp_id' => uniqid(),
            ];

        } else {
            $maxIndice = $this->nivel->procesosPrerrequisito()->max('indice') ?? 0;

            \App\Models\NivelProcesoPrerrequisito::create([
                'nivel_id' => $this->nivel->id,
                'paso_crecimiento_id' => $this->pasoSeleccionado,
                'estado_paso_crecimiento_usuario_id' => $this->estadoSeleccionado,
                'estado_proceso' => $this->estadoSeleccionado,
                'indice' => $maxIndice + 1,
            ]);
        }

        $this->reset(['pasoSeleccionado', 'estadoSeleccionado']);
        $this->cargarDatos();
        $this->dispatch('msn', msnTexto: 'Paso requisito agregado correctamente', msnIcono: 'success');
    }

    public function eliminarPaso($pasoId)
    {
        if ($this->draftMode) {
            $this->draftItems = array_filter($this->draftItems, function ($item) use ($pasoId) {
                return $item['temp_id'] != $pasoId;
            });
        } else {
            $this->nivel->procesosPrerrequisito()->detach($pasoId);

            $pasos = $this->nivel->procesosPrerrequisito()->orderBy('indice')->get();
            foreach ($pasos as $index => $paso) {
                $this->nivel->procesosPrerrequisito()->updateExistingPivot($paso->id, [
                    'indice' => $index + 1,
                ]);
            }
            $this->cargarDatos();
        }

        $this->dispatch('msn', msnTexto: 'Paso eliminado correctamente', msnIcono: 'success');
    }

    public function actualizarOrden($ordenes)
    {
        foreach ($ordenes as $orden) {
            $this->nivel->procesosPrerrequisito()->updateExistingPivot($orden['id'], [
                'indice' => $orden['orden'],
            ]);
        }

        $this->cargarDatos();
        $this->dispatch('msn', msnTexto: 'Orden actualizado correctamente', msnIcono: 'success');
    }

    public function render()
    {
        return view('livewire.escuelas.niveles-escuelas.gestionar-pasos-requisito', [
            'draftItems' => $this->draftItems,
        ]);
    }
}
