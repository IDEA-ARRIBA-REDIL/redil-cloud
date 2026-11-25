<?php

namespace App\Livewire\Generales;

use App\Helpers\Helpers;
use App\Models\Municipio;
use App\Models\Pais;
use Livewire\Attributes\On;
use Livewire\Component;

class PaisBuscador extends Component
{
    public string $paisId = '';

    public string $busqueda = '';

    public bool $verInputBusqueda = true;

    public ?Pais $paisSeleccionado = null;

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
        $oldPais = old($this->nameId);

        if ($oldPais) {
            $this->seleccionarPais($oldPais, false);
        } elseif ($this->usuario && $this->usuario->pais_id) {
            $this->seleccionarPais($this->usuario->pais_id, false);
        }
    }

    public function seleccionarPais(int|string|null $paisId, bool $interactivo = true): void
    {
        $this->paisSeleccionado = $paisId ? Pais::find($paisId) : null;
        $this->paisId = $this->paisSeleccionado ? (string) $this->paisSeleccionado->id : '';
        $this->verInputBusqueda = ! (bool) $this->paisSeleccionado;
        $this->mostrarError = false;
        $this->msnError = '';

        if ($interactivo) {
            $tieneMunicipios = $this->paisSeleccionado
                ? Municipio::where('pais_id', $this->paisSeleccionado->id)->exists()
                : false;

            $this->dispatch(
                'pais-seleccionado',
                paisId: $this->paisId,
                tieneMunicipios: $tieneMunicipios,
                interactivo: true
            );
        }
    }

    public function quitarSeleccion(): void
    {
        $this->paisSeleccionado = null;
        $this->paisId = '';
        $this->verInputBusqueda = true;
        $this->busqueda = '';

        $this->dispatch('pais-limpiado', interactivo: true);
    }

    #[On('mostrarMensajeError')]
    public function mostrarMensajeError(bool $mostrarError, string $msnError): void
    {
        $this->mostrarError = $mostrarError;
        $this->msnError = $msnError;
    }

    public function render()
    {
        $paises = null;
        $busquedaLimpia = trim($this->busqueda);

        if ($busquedaLimpia !== '' && mb_strlen($busquedaLimpia) >= 2) {
            $buscar = htmlspecialchars($busquedaLimpia);
            $buscar = Helpers::sanearStringConEspacios($buscar);
            $buscar = str_replace(["'"], '', $buscar);

            $paises = Pais::query()
                ->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE ?", ["%{$buscar}%"])
                ->orderBy('nombre', 'asc')
                ->take(15)
                ->select('id', 'nombre')
                ->get();
        }

        return view('livewire.generales.pais-buscador', ['paises' => $paises]);
    }
}
