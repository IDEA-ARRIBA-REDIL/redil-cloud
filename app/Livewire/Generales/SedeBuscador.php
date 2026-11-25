<?php

namespace App\Livewire\Generales;

use App\Helpers\Helpers;
use App\Models\FormularioUsuario;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Sede;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class SedeBuscador extends Component
{
    public string $sedeId = '';

    public ?int $formularioId = null;

    public string $paisId = '';

    public string $municipioId = '';

    public string $localidadId = '';

    public bool $tieneCampoPais = false;

    public bool $tieneCampoMunicipio = false;

    public bool $tieneCampoLocalidad = false;

    public bool $paisTieneMunicipios = true;

    public bool $municipioTieneLocalidades = true;

    public bool $estaDeshabilitado = false;

    public string $placeholderDeshabilitado = '';

    public string $busqueda = '';

    public bool $verInputBusqueda = true;

    public ?Sede $sedeSeleccionada = null;

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
        if ($this->municipioId === '' && $this->localidadId !== '') {
            $this->municipioId = (string) (Localidad::find($this->localidadId)?->municipio_id ?? '');
        }

        if ($this->paisId === '' && $this->municipioId !== '') {
            $this->paisId = (string) (Municipio::find($this->municipioId)?->pais_id ?? '');
        }

        if ($this->paisId !== '') {
            $this->paisTieneMunicipios = Municipio::where('pais_id', $this->paisId)->exists();
        }

        if ($this->municipioId !== '') {
            $mun = Municipio::withCount('localidades')->find($this->municipioId);
            $this->municipioTieneLocalidades = (bool) ($mun && ((bool) $mun->tiene_localidades || $mun->localidades_count > 0));
        }

        $sedeInicial = old($this->nameId, $this->usuario?->sede_id);
        $sedePreferidaId = $sedeInicial ? (int) $sedeInicial : null;

        $this->evaluarEstadoSedes(false, $sedePreferidaId);
    }

    public function seleccionarSede(int|string|null $sedeId): void
    {
        $this->sedeSeleccionada = $sedeId ? Sede::find($sedeId) : null;
        $this->sedeId = $this->sedeSeleccionada ? (string) $this->sedeSeleccionada->id : '';
        $this->verInputBusqueda = ! (bool) $this->sedeSeleccionada;
        $this->mostrarError = false;
        $this->msnError = '';
        $this->busqueda = '';
    }

    public function quitarSeleccion(): void
    {
        $this->sedeSeleccionada = null;
        $this->sedeId = '';
        $this->verInputBusqueda = true;
        $this->busqueda = '';
    }

    #[On('pais-seleccionado')]
    public function alSeleccionarPais(int|string|null $paisId = '', bool $tieneMunicipios = true, bool $interactivo = true): void
    {
        $nuevoPaisId = (string) ($paisId ?? '');
        $cambioPais = $this->paisId !== $nuevoPaisId;

        $this->paisId = $nuevoPaisId;
        $this->paisTieneMunicipios = $tieneMunicipios;

        if ($interactivo && $cambioPais) {
            $this->municipioId = '';
            $this->municipioTieneLocalidades = true;
            $this->localidadId = '';
        }

        $this->evaluarEstadoSedes($interactivo && $cambioPais);
    }

    #[On('pais-limpiado')]
    public function alLimpiarPais(bool $interactivo = true): void
    {
        $this->paisId = '';
        $this->paisTieneMunicipios = true;
        $this->municipioId = '';
        $this->municipioTieneLocalidades = true;
        $this->localidadId = '';

        $this->evaluarEstadoSedes($interactivo);
    }

    #[On('municipio-seleccionado')]
    public function alSeleccionarMunicipio(
        int|string|null $municipioId = '',
        int|string|null $paisId = '',
        bool $tieneLocalidades = true,
        bool $interactivo = true
    ): void {
        $nuevoMunicipioId = (string) ($municipioId ?? '');
        $cambioMunicipio = $this->municipioId !== $nuevoMunicipioId;

        $this->municipioId = $nuevoMunicipioId;
        $this->municipioTieneLocalidades = $tieneLocalidades;

        if ($paisId !== null && (string) $paisId !== '') {
            $this->paisId = (string) $paisId;
        }

        if ($interactivo && $cambioMunicipio) {
            $this->localidadId = '';
        }

        $this->evaluarEstadoSedes($interactivo && $cambioMunicipio);
    }

    #[On('municipio-limpiado')]
    public function alLimpiarMunicipio(bool $interactivo = true): void
    {
        $this->municipioId = '';
        $this->municipioTieneLocalidades = true;
        $this->localidadId = '';

        $this->evaluarEstadoSedes($interactivo);
    }

    #[On('ubicacion-seleccionada')]
    public function alSeleccionarUbicacion(
        int|string|null $localidadId = '',
        int|string|null $municipioId = '',
        int|string|null $paisId = '',
        bool $interactivo = true
    ): void {
        $nuevaLocalidadId = (string) ($localidadId ?? '');
        $cambioLocalidad = $this->localidadId !== $nuevaLocalidadId;

        $this->localidadId = $nuevaLocalidadId;

        if ($municipioId !== null && (string) $municipioId !== '') {
            $this->municipioId = (string) $municipioId;
        }
        if ($paisId !== null && (string) $paisId !== '') {
            $this->paisId = (string) $paisId;
        }

        $this->evaluarEstadoSedes($interactivo && $cambioLocalidad);
    }

    #[On('ubicacion-limpiada')]
    public function alLimpiarUbicacion(bool $interactivo = true): void
    {
        $this->localidadId = '';
        $this->evaluarEstadoSedes($interactivo);
    }

    #[On('mostrarMensajeError')]
    public function mostrarMensajeError(bool $mostrarError, string $msnError): void
    {
        $this->mostrarError = $mostrarError;
        $this->msnError = $msnError;
    }

    protected function obtenerSedesBase(): Collection
    {
        $sedeActualUsuarioId = $this->usuario?->sede_id ? (int) $this->usuario->sede_id : null;

        if ($this->formularioId) {
            $formulario = FormularioUsuario::find($this->formularioId);
            if ($formulario) {
                return $formulario->sedesDisponibles($sedeActualUsuarioId);
            }
        }

        return Sede::with(['paises:id', 'municipios:id', 'localidades:id'])
            ->orderBy('nombre', 'asc')
            ->get();
    }

    protected function obtenerSedesCoincidentes(): Collection
    {
        $sedesBase = $this->obtenerSedesBase();
        $haySedeConReglas = $sedesBase->contains(
            fn (Sede $sede): bool => ! (bool) ($sede->aparece_en_todos_los_casos ?? true)
        );
        $tieneAlgunCampoGeo = $this->tieneCampoPais || $this->tieneCampoMunicipio || $this->tieneCampoLocalidad;

        if (! $haySedeConReglas || ! $tieneAlgunCampoGeo) {
            return $sedesBase;
        }

        $paisIdInt = $this->paisId !== '' ? (int) $this->paisId : null;
        $municipioIdInt = $this->municipioId !== '' ? (int) $this->municipioId : null;
        $localidadIdInt = $this->localidadId !== '' ? (int) $this->localidadId : null;

        return $sedesBase
            ->filter(fn (Sede $sede): bool => $sede->cumpleCoberturaGeografica($paisIdInt, $municipioIdInt, $localidadIdInt))
            ->values();
    }

    protected function ubicacionIncompleta(): bool
    {
        if ($this->tieneCampoPais && $this->paisId === '') {
            return true;
        }
        if ($this->tieneCampoMunicipio && $this->paisTieneMunicipios && $this->municipioId === '') {
            return true;
        }
        if ($this->tieneCampoLocalidad && $this->paisTieneMunicipios && $this->municipioTieneLocalidades && $this->localidadId === '') {
            return true;
        }

        return false;
    }

    protected function evaluarEstadoSedes(bool $limpiarSeleccion = false, ?int $sedePreferidaId = null): void
    {
        $coincidentes = $this->obtenerSedesCoincidentes();

        if ($coincidentes->isEmpty()) {
            $this->quitarSeleccion();
            $this->estaDeshabilitado = true;
            $this->placeholderDeshabilitado = $this->ubicacionIncompleta()
                ? 'Primero selecciona tu ubicación'
                : 'No hay sedes disponibles';

            return;
        }

        $this->estaDeshabilitado = false;
        $this->placeholderDeshabilitado = '';

        if ($coincidentes->count() === 1) {
            $unicaSede = $coincidentes->first();
            $this->seleccionarSede($unicaSede->id);

            return;
        }

        $idObjetivo = $sedePreferidaId ?? (! $limpiarSeleccion && $this->sedeId !== '' ? (int) $this->sedeId : null);

        if ($idObjetivo && $coincidentes->contains('id', $idObjetivo)) {
            $this->seleccionarSede($idObjetivo);
        } else {
            $this->quitarSeleccion();
        }
    }

    public function render()
    {
        $sedes = collect();

        if (! $this->estaDeshabilitado && $this->mostrar) {
            $coincidentes = $this->obtenerSedesCoincidentes();
            $busquedaLimpia = trim($this->busqueda);

            if ($busquedaLimpia !== '') {
                $buscar = mb_strtolower(Helpers::sanearStringConEspacios($busquedaLimpia));

                $coincidentes = $coincidentes->filter(function (Sede $sede) use ($buscar): bool {
                    $nombre = mb_strtolower(Helpers::sanearStringConEspacios((string) $sede->nombre));
                    $direccion = mb_strtolower(Helpers::sanearStringConEspacios((string) ($sede->direccion ?? '')));

                    return str_contains($nombre, $buscar) || str_contains($direccion, $buscar);
                });
            }

            $sedes = $coincidentes->take(5)->values();
        }

        return view('livewire.generales.sede-buscador', ['sedes' => $sedes]);
    }
}
