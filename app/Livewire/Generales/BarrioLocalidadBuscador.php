<?php

namespace App\Livewire\Generales;

use App\Helpers\Helpers;
use App\Models\Localidad;
use App\Models\Municipio;
use Livewire\Attributes\On;
use Livewire\Component;

class BarrioLocalidadBuscador extends Component
{
    public $localidadId = '';

    public string $paisId = '';

    public string $municipioId = '';

    public bool $tieneCampoPais = false;

    public bool $tieneCampoMunicipio = false;

    public bool $paisTieneMunicipios = true;

    public string $informacionDeApoyo = '';

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

    public function mount(): void
    {
        if ($this->tieneCampoPais && $this->paisId !== '' && ! Municipio::where('pais_id', $this->paisId)->exists()) {
            $this->paisTieneMunicipios = false;
            $this->mostrar = false;

            return;
        }

        if ($this->tieneCampoMunicipio && $this->municipioId !== '') {
            $municipio = Municipio::withCount('localidades')->find($this->municipioId);
            $tieneLocalidades = $municipio && ((bool) $municipio->tiene_localidades || $municipio->localidades_count > 0);
            if (! $tieneLocalidades) {
                $this->mostrar = false;

                return;
            }
        }

        $oldUbicacion = old($this->nameId);

        if ($oldUbicacion) {
            $this->seleccionarUbicacion($oldUbicacion, 'Localidad', false);
        } elseif ($this->usuario && $this->usuario->localidad && $this->usuario->localidad->id) {
            $this->seleccionarUbicacion($this->usuario->localidad->id, 'Localidad', false);
        }
    }

    public function getEstaDeshabilitadoProperty(): bool
    {
        if ($this->tieneCampoMunicipio) {
            return $this->municipioId === '';
        }

        if ($this->tieneCampoPais) {
            return $this->paisId === '';
        }

        return false;
    }

    #[On('pais-seleccionado')]
    public function alSeleccionarPais(int|string|null $paisId = '', bool $tieneMunicipios = true, bool $interactivo = true): void
    {
        $nuevoPaisId = (string) ($paisId ?? '');
        $this->paisTieneMunicipios = $tieneMunicipios;

        if ($interactivo && $this->paisId !== $nuevoPaisId) {
            if ($this->tieneCampoMunicipio) {
                $this->municipioId = '';
            }
            $this->quitarSeleccion(false);
        }

        $this->paisId = $nuevoPaisId;

        if (! $this->paisTieneMunicipios) {
            $this->quitarSeleccion(false);
            $this->mostrar = false;
        } else {
            $this->mostrar = true;
        }
    }

    #[On('pais-limpiado')]
    public function alLimpiarPais(bool $interactivo = true): void
    {
        $this->paisId = '';
        $this->paisTieneMunicipios = true;
        if ($this->tieneCampoMunicipio) {
            $this->municipioId = '';
        }
        $this->mostrar = true;
        $this->quitarSeleccion(false);
    }

    #[On('municipio-seleccionado')]
    public function alSeleccionarMunicipio(int|string|null $municipioId = '', bool $tieneLocalidades = true, bool $interactivo = true): void
    {
        $nuevoMunicipioId = (string) ($municipioId ?? '');

        if ($interactivo && $this->municipioId !== $nuevoMunicipioId) {
            $this->quitarSeleccion(false);
        }

        $this->municipioId = $nuevoMunicipioId;

        if (! $this->paisTieneMunicipios || ! $tieneLocalidades) {
            $this->quitarSeleccion(false);
            $this->mostrar = false;
        } else {
            $this->mostrar = true;
        }
    }

    #[On('municipio-limpiado')]
    public function alLimpiarMunicipio(bool $interactivo = true): void
    {
        $this->municipioId = '';
        $this->mostrar = $this->paisTieneMunicipios;
        $this->quitarSeleccion(false);
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
        $this->ubicacionSeleccionada = Localidad::with('municipio:id,pais_id')->find($ubicacionId);
        $this->tipoUbicacionSeleccionada = 'Localidad';
        $this->localidadId = $this->ubicacionSeleccionada ? $this->ubicacionSeleccionada->id : '';

        $this->verInputBusqueda = false;
        $this->verListaBusqueda = false;

        if ($interactivo) {
            $this->dispatch(
                'ubicacion-seleccionada',
                localidadId: $this->localidadId,
                municipioId: $this->ubicacionSeleccionada?->municipio_id ? (string) $this->ubicacionSeleccionada->municipio_id : '',
                paisId: $this->ubicacionSeleccionada?->municipio?->pais_id ? (string) $this->ubicacionSeleccionada->municipio->pais_id : '',
                interactivo: true
            );
        }
    }

    public function quitarSeleccion(bool $interactivo = true)
    {
        $this->ubicacionSeleccionada = null;
        $this->tipoUbicacionSeleccionada = null;
        $this->localidadId = '';
        $this->verInputBusqueda = true;
        $this->verListaBusqueda = true;
        $this->busqueda = '';

        if ($interactivo) {
            $this->dispatch('ubicacion-limpiada', interactivo: true);
        }
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
        $busquedaLimpia = trim((string) $this->busqueda);

        if (! $this->estaDeshabilitado && $this->mostrar && $busquedaLimpia !== '' && strlen($busquedaLimpia) >= 3) {
            $buscar = htmlspecialchars($busquedaLimpia);
            $buscar = Helpers::sanearStringConEspacios($buscar);
            $buscar = str_replace(["'"], '', $buscar);

            // Búsqueda exclusiva de localidades filtrada por municipio o país si aplica
            $query = Localidad::with('municipio:id,nombre')
                ->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE ?", ["%{$buscar}%"]);

            if ($this->municipioId !== '') {
                $query->where('municipio_id', $this->municipioId);
            } elseif ($this->paisId !== '') {
                $query->whereHas('municipio', function ($q): void {
                    $q->where('pais_id', $this->paisId);
                });
            }

            $localidades = $query
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
