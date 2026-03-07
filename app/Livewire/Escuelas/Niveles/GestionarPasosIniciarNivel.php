<?php

namespace App\Livewire\Escuelas\Niveles;

use Livewire\Component;
use App\Models\NivelAgrupacion;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;
use Illuminate\Support\Collection;

class GestionarPasosIniciarNivel extends Component
{
    public $nivel;
    public $pasosIniciar;
    public $pasoSeleccionado;
    public $estadoSeleccionado;

    // Draft Mode Properties
    public $draftMode = false;
    public $draftItems = [];

    protected $listeners = ['eliminarNivelPasoIniciar'];

    public function mount(NivelAgrupacion $nivel)
    {
        $this->nivel = $nivel;
        $this->draftMode = !$nivel->exists;

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        if (!$this->draftMode) {
            $this->pasosIniciar = $this->nivel->pasosCrecimiento()
                ->wherePivot('al_iniciar', 1)
                ->orderBy('nivel_paso_crecimiento.indice')
                ->get();
        } else {
            $this->pasosIniciar = collect();
        }
    }

    public function agregarPaso()
    {
        $this->validate([
            'pasoSeleccionado' => 'required',
            'estadoSeleccionado' => 'required'
        ]);

        if ($this->draftMode) {
            // DRAFT MODE: Agregar al array
            $pasoModel = PasoCrecimiento::find($this->pasoSeleccionado);
            $estadoModel = EstadoPasoCrecimientoUsuario::find($this->estadoSeleccionado);

            // Verificar duplicados en draft
            foreach($this->draftItems as $item) {
                if ($item['paso_id'] == $this->pasoSeleccionado && $item['estado_id'] == $this->estadoSeleccionado) {
                    $this->dispatch('msn', msnTexto: 'Este paso ya está agregado al iniciar', msnIcono: 'warning');
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
            $exists = $this->nivel->pasosCrecimiento()
                ->where('paso_crecimiento_id', $this->pasoSeleccionado)
                ->wherePivot('al_iniciar', 1)
                ->exists();

            if ($exists) {
                $this->dispatch('msn', msnTexto: 'Este paso ya está agregado al iniciar', msnIcono: 'warning');
                return;
            }

            $maxIndice = $this->nivel->pasosCrecimiento()->wherePivot('al_iniciar', 1)->max('indice') ?? 0;

            $this->nivel->pasosCrecimiento()->attach($this->pasoSeleccionado, [
                'estado_paso_crecimiento_usuario_id' => $this->estadoSeleccionado,
                'estado' => $this->estadoSeleccionado, // Legacy
                'al_iniciar' => 1,
                'indice' => $maxIndice + 1,
            ]);

            $this->cargarDatos();
        }

        $this->reset(['pasoSeleccionado', 'estadoSeleccionado']);
        $this->dispatch('msn', msnTitulo: 'Agregado', msnTexto: 'Paso al iniciar agregado correctamente.', msnIcono: 'success');
    }

    public function confirmarEliminacionNivelPasoIniciar($pasoId)
    {
        $this->dispatch('confirmarEliminacion',
            id: $pasoId,
            titulo: '¿Eliminar paso al iniciar?',
            texto: 'Este paso dejará de asignarse al iniciar el grado.',
            metodo: 'eliminarNivelPasoIniciar'
        );
    }

    public function eliminarNivelPasoIniciar($pasoId) // En draft mode, pasoId es temp_id
    {
        $this->eliminarPaso($pasoId);
    }

    public function eliminarPaso($pasoId)
    {
        if ($this->draftMode) {
             // DRAFT MODE: Eliminar del array por temp_id
             $this->draftItems = array_filter($this->draftItems, function($item) use ($pasoId) {
                 return $item['temp_id'] != $pasoId;
             });
        } else {
            // DB MODE
            $this->nivel->pasosCrecimiento()->wherePivot('al_iniciar', 1)->detach($pasoId);

            // Reordenar
            $pasos = $this->nivel->pasosCrecimiento()->wherePivot('al_iniciar', 1)->orderBy('indice')->get();
            foreach ($pasos as $index => $paso) {
                 $this->nivel->pasosCrecimiento()->wherePivot('al_iniciar', 1)->updateExistingPivot($paso->id, [
                    'indice' => $index + 1
                ]);
            }
            $this->cargarDatos();
        }

        $this->dispatch('msn',
            msnTitulo: 'Eliminado',
            msnTexto: 'Paso al iniciar eliminado correctamente.',
            msnIcono: 'success'
        );
    }

    public function render()
    {
        return view('livewire.escuelas.niveles.gestionar-pasos-iniciar-nivel', [
            'pasosDisponibles' => PasoCrecimiento::orderBy('nombre')->get(),
            'estadosDisponibles' => EstadoPasoCrecimientoUsuario::orderBy('nombre')->get(),
            'pasosIniciar' => $this->pasosIniciar,
            'draftItems' => $this->draftItems
        ]);
    }
}
