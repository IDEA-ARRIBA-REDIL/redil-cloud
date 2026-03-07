<?php

namespace App\Livewire\Escuelas\Niveles;

use Livewire\Component;
use App\Models\NivelAgrupacion;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;
use Illuminate\Support\Collection;

class GestionarPasosCulminadosNivel extends Component
{
    public $nivel;
    public $pasosCulminados;
    public $pasoSeleccionado;
    public $estadoSeleccionado;

    // Draft Mode Properties
    public $draftMode = false;
    public $draftItems = [];

    protected $listeners = ['eliminarNivelPasoCulminado'];

    public function mount(NivelAgrupacion $nivel)
    {
        $this->nivel = $nivel;
        $this->draftMode = !$nivel->exists;

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        if (!$this->draftMode) {
            $this->pasosCulminados = $this->nivel->pasosCrecimiento()
                ->wherePivot('al_iniciar', 0)
                ->orderBy('nivel_paso_crecimiento.indice')
                ->get();
        } else {
            $this->pasosCulminados = collect();
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
                    $this->dispatch('msn', msnTexto: 'Este paso ya está agregado al culminar', msnIcono: 'warning');
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
            $exists = $this->nivel->pasosCrecimiento()
                ->where('paso_crecimiento_id', $this->pasoSeleccionado)
                ->wherePivot('al_iniciar', 0)
                ->exists();

            if ($exists) {
                $this->dispatch('msn', msnTexto: 'Este paso ya está agregado al culminar', msnIcono: 'warning');
                return;
            }

            $maxIndice = $this->nivel->pasosCrecimiento()->wherePivot('al_iniciar', 0)->max('indice') ?? 0;

            $this->nivel->pasosCrecimiento()->attach($this->pasoSeleccionado, [
                'estado_paso_crecimiento_usuario_id' => $this->estadoSeleccionado,
                'estado' => $this->estadoSeleccionado,
                'al_iniciar' => 0,
                'indice' => $maxIndice + 1,
            ]);

            $this->cargarDatos();
        }

        $this->reset(['pasoSeleccionado', 'estadoSeleccionado']);
        $this->dispatch('msn', msnTitulo: 'Agregado', msnTexto: 'Paso al culminar agregado correctamente.', msnIcono: 'success');
    }

    public function confirmarEliminacionNivelPasoCulminado($pasoId)
    {
        $this->dispatch('confirmarEliminacion',
            id: $pasoId,
            titulo: '¿Eliminar paso al culminar?',
            texto: 'Este paso dejará de asignarse al cerrar el grado.',
            metodo: 'eliminarNivelPasoCulminado'
        );
    }

    public function eliminarNivelPasoCulminado($pasoId)
    {
        $this->eliminarPaso($pasoId);
    }

    public function eliminarPaso($pasoId)
    {
        if ($this->draftMode) {
             $this->draftItems = array_filter($this->draftItems, function($item) use ($pasoId) {
                 return $item['temp_id'] != $pasoId;
             });
        } else {
            $this->nivel->pasosCrecimiento()->wherePivot('al_iniciar', 0)->detach($pasoId);
            $this->cargarDatos();
        }

        $this->dispatch('msn', msnTitulo: 'Eliminado', msnTexto: 'Paso al culminar eliminado correctamente.', msnIcono: 'success');
    }

    public function render()
    {
        return view('livewire.escuelas.niveles.gestionar-pasos-culminados-nivel', [
            'pasosDisponibles' => PasoCrecimiento::orderBy('nombre')->get(),
            'estadosDisponibles' => EstadoPasoCrecimientoUsuario::orderBy('nombre')->get(),
            'pasosCulminados' => $this->pasosCulminados,
            'draftItems' => $this->draftItems
        ]);
    }
}
