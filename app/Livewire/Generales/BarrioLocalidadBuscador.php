<?php

namespace App\Livewire\Generales;

use App\Helpers\Helpers;
use App\Models\Barrio;
use App\Models\Localidad;
use Livewire\Attributes\On;
use Livewire\Component;

class BarrioLocalidadBuscador extends Component
{
    public $localidadId = '';

    public $barrioId = '';

    public $busqueda = '';

    public $verListaBusqueda = false;

    public $verInputBusqueda = true;

    public $ubicacionSeleccionada = null;

    public $tipoUbicacionSeleccionada = null;

    public $mostrarError = false;

    public $msnError = '';

    public $mostrar = true;

    public $class = '';

    public $label = '';

    public $nameId = '';

    public $placeholder = '';

    public $usuario = null;

    public function mount()
    {
        $oldUbicacion = old($this->nameId);

        if ($oldUbicacion) {
            $this->seleccionarUbicacion($oldUbicacion, 'Localidad', false);
        } else {
            if ($this->usuario) {
                if ($this->usuario->localidad && $this->usuario->localidad->id) {
                    $this->seleccionarUbicacion($this->usuario->localidad->id, 'Localidad', false);
                } elseif ($this->usuario->barrio && $this->usuario->barrio->localidad_id) {
                    // Fallback para usuarios antiguos que solo tenían barrio vinculado a una localidad
                    $this->seleccionarUbicacion($this->usuario->barrio->localidad_id, 'Localidad', false);
                }
            }
        }
    }

    public function desplegarListaBusqueda()
    {
        if (! $this->ubicacionSeleccionada) {
            $this->verListaBusqueda = true;
        }
    }

    public function ocultarListaBusqueda()
    {
        if (! $this->ubicacionSeleccionada) {
            $this->verListaBusqueda = false;
        }
    }

    public function seleccionarUbicacion($ubicacionId, $tipo = 'Localidad', $interactivo = true)
    {
        $this->ubicacionSeleccionada = Localidad::find($ubicacionId);
        $this->tipoUbicacionSeleccionada = 'Localidad';
        $this->localidadId = $this->ubicacionSeleccionada ? $this->ubicacionSeleccionada->id : '';
        $this->barrioId = '';

        $this->verInputBusqueda = false;
        $this->verListaBusqueda = false;

        $this->dispatch('ubicacion-seleccionada', localidadId: $this->localidadId, interactivo: $interactivo);
    }

    public function quitarSeleccion()
    {
        $this->ubicacionSeleccionada = null;
        $this->tipoUbicacionSeleccionada = null;
        $this->localidadId = '';
        $this->barrioId = '';
        $this->verInputBusqueda = true;
        $this->verListaBusqueda = true;
        $this->busqueda = '';

        $this->dispatch('ubicacion-limpiada', interactivo: true);
    }

    #[On('mostrarBuscadorUbicacion')]
    public function mostrarBuscadorUbicacion($cambiarPor)
    {
        $this->mostrar = $cambiarPor;
        $this->quitarSeleccion();
    }

    #[On('mostrarMensajeError')]
    public function mostrarMensajeError($mostrarError, $msnError)
    {
        $this->mostrarError = $mostrarError;
        $this->msnError = $msnError;
    }

    public function render()
    {
        $ubicaciones = null;
        if ($this->busqueda && strlen($this->busqueda) >= 3) {
            $buscar = htmlspecialchars($this->busqueda);
            $buscar = Helpers::sanearStringConEspacios($buscar);
            $buscar = str_replace(["'"], '', $buscar);

            // Búsqueda exclusiva de localidades
            $localidades = Localidad::whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$buscar%'")
                ->orderBy('nombre', 'asc')
                ->take(15)
                ->select('id', 'nombre', 'municipio_id')
                ->get();

            $localidades->map(function ($localidad): void {
                $localidad->tipo = 'Localidad';
                $localidad->nombreMunicipio = $localidad->municipio ? $localidad->municipio->nombre : '';
            });

            $ubicaciones = $localidades;
        }

        return view('livewire.generales.barrio-localidad-buscador', ['ubicaciones' => $ubicaciones]);
    }
}
