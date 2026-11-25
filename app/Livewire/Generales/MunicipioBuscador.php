<?php

namespace App\Livewire\Generales;

use App\Helpers\Helpers;
use App\Models\Municipio;
use Livewire\Attributes\On;
use Livewire\Component;

class MunicipioBuscador extends Component
{
    public string $municipioId = '';

    public string $paisId = '';

    public bool $tieneCampoPais = false;

    public string $busqueda = '';

    public bool $verInputBusqueda = true;

    public ?Municipio $municipioSeleccionado = null;

    public bool $mostrarError = false;

    public string $msnError = '';

    public bool $mostrar = true;

    public string $class = '';

    public string $label = '';

    public string $nameId = '';

    public string $placeholder = '';

    public mixed $usuario = null;

    public string $informacionDeApoyo = '';

    public function mount(): void
    {
        if ($this->tieneCampoPais && $this->paisId !== '' && ! Municipio::where('pais_id', $this->paisId)->exists()) {
            $this->mostrar = false;

            return;
        }

        $oldMunicipio = old($this->nameId);

        if ($oldMunicipio) {
            $this->seleccionarMunicipio($oldMunicipio, false);
        } elseif ($this->usuario && $this->usuario->municipio_id) {
            $this->seleccionarMunicipio($this->usuario->municipio_id, false);
        } elseif ($this->usuario && $this->usuario->localidad && $this->usuario->localidad->municipio_id) {
            $this->seleccionarMunicipio($this->usuario->localidad->municipio_id, false);
        }
    }

    public function getEstaDeshabilitadoProperty(): bool
    {
        return $this->tieneCampoPais && $this->paisId === '';
    }

    public function seleccionarMunicipio(int|string|null $municipioId, bool $interactivo = true): void
    {
        $this->municipioSeleccionado = $municipioId
            ? Municipio::withCount('localidades')->find($municipioId)
            : null;
        $this->municipioId = $this->municipioSeleccionado ? (string) $this->municipioSeleccionado->id : '';
        $this->verInputBusqueda = ! (bool) $this->municipioSeleccionado;
        $this->mostrarError = false;
        $this->msnError = '';

        if ($interactivo) {
            $tieneLocalidades = $this->municipioSeleccionado
                ? ((bool) $this->municipioSeleccionado->tiene_localidades || $this->municipioSeleccionado->localidades_count > 0)
                : false;

            $this->dispatch(
                'municipio-seleccionado',
                municipioId: $this->municipioId,
                paisId: $this->municipioSeleccionado ? (string) $this->municipioSeleccionado->pais_id : '',
                tieneLocalidades: $tieneLocalidades,
                interactivo: true
            );
        }
    }

    public function quitarSeleccion(bool $interactivo = true): void
    {
        $this->municipioSeleccionado = null;
        $this->municipioId = '';
        $this->verInputBusqueda = true;
        $this->busqueda = '';

        if ($interactivo) {
            $this->dispatch('municipio-limpiado', interactivo: true);
        }
    }

    #[On('pais-seleccionado')]
    public function alSeleccionarPais(int|string|null $paisId = '', bool $tieneMunicipios = true, bool $interactivo = true): void
    {
        $paisAnterior = $this->paisId;
        $nuevoPaisId = (string) ($paisId ?? '');
        $this->paisId = $nuevoPaisId;

        if (! $tieneMunicipios) {
            $this->quitarSeleccion(false);
            $this->mostrar = false;

            return;
        }

        if ($interactivo && $paisAnterior !== $nuevoPaisId) {
            $this->quitarSeleccion(false);
        }

        $this->mostrar = true;
    }

    #[On('pais-limpiado')]
    public function alLimpiarPais(bool $interactivo = true): void
    {
        $this->paisId = '';
        $this->mostrar = true;
        $this->quitarSeleccion(false);
    }

    #[On('mostrarMensajeError')]
    public function mostrarMensajeError(bool $mostrarError, string $msnError): void
    {
        $this->mostrarError = $mostrarError;
        $this->msnError = $msnError;
    }

    public function render()
    {
        $municipios = null;
        $busquedaLimpia = trim($this->busqueda);

        if (! $this->estaDeshabilitado && $busquedaLimpia !== '' && mb_strlen($busquedaLimpia) >= 2) {
            $buscar = htmlspecialchars($busquedaLimpia);
            $buscar = Helpers::sanearStringConEspacios($buscar);
            $buscar = str_replace(["'"], '', $buscar);

            $query = Municipio::query()
                ->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE ?", ["%{$buscar}%"]);

            if ($this->paisId !== '') {
                $query->where('pais_id', $this->paisId);
            }

            $municipios = $query
                ->orderBy('nombre', 'asc')
                ->take(15)
                ->select('id', 'nombre', 'pais_id', 'tiene_localidades')
                ->get();
        }

        return view('livewire.generales.municipio-buscador', ['municipios' => $municipios]);
    }
}
