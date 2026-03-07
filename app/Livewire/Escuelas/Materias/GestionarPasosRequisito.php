<?php

namespace App\Livewire\Escuelas\Materias;

use App\Models\Materia;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;
use Livewire\Component;

class GestionarPasosRequisito extends Component
{
    public Materia $materia;
    public $pasoSeleccionado = '';
    public $estadoSeleccionado = '';

    public $pasos = [];
    public $estados = [];
    public $pasosRequisito = [];

    public $draftMode = false;
    public $draftItems = [];

    public function mount(Materia $materia)
    {
        $this->materia = $materia;
        $this->draftMode = !$materia->exists;
        $this->cargarDatos();
    }

    private function cargarDatos()
    {
        $this->pasos = PasoCrecimiento::orderBy('nombre')->get();
        $this->estados = EstadoPasoCrecimientoUsuario::orderBy('nombre')->get();

        if (!$this->draftMode) {
            $this->pasosRequisito = $this->materia->procesosPrerrequisito()
                ->orderBy('materia_proceso_prerrequisito.indice')
                ->get();
        } else {
            $this->pasosRequisito = collect();
        }
    }

    public function agregarPaso()
    {
        $this->validate([
            'pasoSeleccionado' => 'required|exists:pasos_crecimiento,id',
            'estadoSeleccionado' => 'required|exists:estados_pasos_crecimiento_usuario,id',
        ], [
            'pasoSeleccionado.required' => 'Debes seleccionar un paso',
            'pasoSeleccionado.exists' => 'El paso seleccionado no existe',
            'estadoSeleccionado.required' => 'Debes seleccionar un estado',
            'estadoSeleccionado.exists' => 'El estado seleccionado no existe',
        ]);

        // Verificar si ya existe esta combinación
        $existente = $this->materia->procesosPrerrequisito()
            ->where('paso_crecimiento_id', $this->pasoSeleccionado)
            ->exists();

        if ($existente) {
            $this->dispatch('msn', msnTexto: 'Este paso ya está agregado como requisito', msnIcono: 'warning');
            return;
        }

        // Obtener el máximo índice
        if ($this->draftMode) {
            // DRAFT MODE: Agregar al array
            $pasoModel = PasoCrecimiento::find($this->pasoSeleccionado);
            $estadoModel = EstadoPasoCrecimientoUsuario::find($this->estadoSeleccionado);

            // Verificar duplicados en draft
            foreach($this->draftItems as $item) {
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
                'temp_id' => uniqid()
            ];

        } else {
            // DB MODE
            $maxIndice = $this->materia->procesosPrerrequisito()->max('indice') ?? 0;

            // Agregar el paso con el nuevo campo FK y el legacy
            $this->materia->procesosPrerrequisito()->attach($this->pasoSeleccionado, [
                'estado_paso_crecimiento_usuario_id' => $this->estadoSeleccionado,
                'estado_proceso' => $this->estadoSeleccionado, // Legacy field name in materia_proceso_prerrequisito
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
             // DRAFT MODE: Eliminar del array por temp_id o index
             // Asumiendo pasoId es el temp_id
             $this->draftItems = array_filter($this->draftItems, function($item) use ($pasoId) {
                 return $item['temp_id'] != $pasoId;
             });
        } else {
            // DB MODE
            $this->materia->procesosPrerrequisito()->detach($pasoId);

            // Reordenar índices
            $pasos = $this->materia->procesosPrerrequisito()->orderBy('indice')->get();
            foreach ($pasos as $index => $paso) {
                $this->materia->procesosPrerrequisito()->updateExistingPivot($paso->id, [
                    'indice' => $index + 1
                ]);
            }
            $this->cargarDatos();
        }

        $this->dispatch('msn', msnTexto: 'Paso eliminado correctamente', msnIcono: 'success');
    }

    public function actualizarOrden($ordenes)
    {
        foreach ($ordenes as $orden) {
            $this->materia->procesosPrerrequisito()->updateExistingPivot($orden['id'], [
                'indice' => $orden['orden']
            ]);
        }

        $this->cargarDatos();
        $this->dispatch('msn', msnTexto: 'Orden actualizado correctamente', msnIcono: 'success');
    }

    public function render()
    {
        return view('livewire.escuelas.materias.gestionar-pasos-requisito', [
            'draftItems' => $this->draftItems
        ]);
    }
}
