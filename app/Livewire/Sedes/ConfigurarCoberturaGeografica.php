<?php

namespace App\Livewire\Sedes;

use App\Helpers\Helpers;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Sede;
use Livewire\Component;

class ConfigurarCoberturaGeografica extends Component
{
    public Sede $sede;

    public bool $apareceEnTodosLosCasos = true;

    public string $modoPaises = 'incluir';

    /** @var array<int, int> */
    public array $paisesSeleccionados = [];

    public string $busquedaPais = '';

    public string $modoMunicipios = 'ninguno';

    /** @var array<int, int> */
    public array $municipiosSeleccionados = [];

    public string $busquedaMunicipio = '';

    public string $modoLocalidades = 'ninguno';

    /** @var array<int, int> */
    public array $localidadesSeleccionadas = [];

    public string $busquedaLocalidad = '';

    public function mount(Sede $sede): void
    {
        $this->sede = $sede->load(['paises:id,nombre', 'municipios:id,nombre,pais_id', 'localidades:id,nombre,municipio_id']);

        $this->apareceEnTodosLosCasos = (bool) ($this->sede->aparece_en_todos_los_casos ?? true);
        $this->modoPaises = $this->sede->modo_paises ?: 'incluir';
        $this->paisesSeleccionados = $this->sede->paises->pluck('id')->map(fn ($id): int => (int) $id)->values()->all();

        $this->modoMunicipios = $this->sede->modo_municipios ?: 'ninguno';
        $this->municipiosSeleccionados = $this->sede->municipios->pluck('id')->map(fn ($id): int => (int) $id)->values()->all();

        $this->modoLocalidades = $this->sede->modo_localidades ?: 'ninguno';
        $this->localidadesSeleccionadas = $this->sede->localidades->pluck('id')->map(fn ($id): int => (int) $id)->values()->all();
    }

    public function updatedModoPaises(string $valor): void
    {
        if ($valor === 'excluir') {
            $this->modoMunicipios = 'ninguno';
            $this->municipiosSeleccionados = [];
            $this->modoLocalidades = 'ninguno';
            $this->localidadesSeleccionadas = [];
            $this->busquedaMunicipio = '';
            $this->busquedaLocalidad = '';
        }
    }

    public function updatedModoMunicipios(string $valor): void
    {
        if ($valor !== 'incluir') {
            $this->modoLocalidades = 'ninguno';
            $this->localidadesSeleccionadas = [];
            $this->busquedaLocalidad = '';
        }
        if ($valor === 'ninguno') {
            $this->municipiosSeleccionados = [];
            $this->busquedaMunicipio = '';
        }
    }

    public function updatedModoLocalidades(string $valor): void
    {
        if ($valor === 'ninguno') {
            $this->localidadesSeleccionadas = [];
            $this->busquedaLocalidad = '';
        }
    }

    public function agregarPais(int $paisId): void
    {
        if (! in_array($paisId, $this->paisesSeleccionados, true)) {
            $this->paisesSeleccionados[] = $paisId;
        }
        $this->busquedaPais = '';
    }

    public function quitarPais(int $paisId): void
    {
        $this->paisesSeleccionados = array_values(array_filter(
            $this->paisesSeleccionados,
            fn (int $id): bool => $id !== $paisId
        ));

        if ($this->paisesSeleccionados === []) {
            $this->municipiosSeleccionados = [];
            $this->localidadesSeleccionadas = [];
            $this->modoMunicipios = 'ninguno';
            $this->modoLocalidades = 'ninguno';

            return;
        }

        $municipiosValidos = Municipio::whereIn('id', $this->municipiosSeleccionados)
            ->whereIn('pais_id', $this->paisesSeleccionados)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $this->municipiosSeleccionados = array_values($municipiosValidos);

        $localidadesValidas = Localidad::whereIn('id', $this->localidadesSeleccionadas)
            ->whereIn('municipio_id', $this->municipiosSeleccionados)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $this->localidadesSeleccionadas = array_values($localidadesValidas);
    }

    public function agregarMunicipio(int $municipioId): void
    {
        if (! in_array($municipioId, $this->municipiosSeleccionados, true)) {
            $this->municipiosSeleccionados[] = $municipioId;
        }
        $this->busquedaMunicipio = '';
    }

    public function quitarMunicipio(int $municipioId): void
    {
        $this->municipiosSeleccionados = array_values(array_filter(
            $this->municipiosSeleccionados,
            fn (int $id): bool => $id !== $municipioId
        ));

        if ($this->municipiosSeleccionados === []) {
            $this->localidadesSeleccionadas = [];
            $this->modoLocalidades = 'ninguno';

            return;
        }

        $localidadesValidas = Localidad::whereIn('id', $this->localidadesSeleccionadas)
            ->whereIn('municipio_id', $this->municipiosSeleccionados)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $this->localidadesSeleccionadas = array_values($localidadesValidas);
    }

    public function agregarLocalidad(int $localidadId): void
    {
        if (! in_array($localidadId, $this->localidadesSeleccionadas, true)) {
            $this->localidadesSeleccionadas[] = $localidadId;
        }
        $this->busquedaLocalidad = '';
    }

    public function quitarLocalidad(int $localidadId): void
    {
        $this->localidadesSeleccionadas = array_values(array_filter(
            $this->localidadesSeleccionadas,
            fn (int $id): bool => $id !== $localidadId
        ));
    }

    public function getPaisesSeleccionadosItemsProperty()
    {
        if ($this->paisesSeleccionados === []) {
            return collect();
        }

        return Pais::whereIn('id', $this->paisesSeleccionados)
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre']);
    }

    public function getMunicipiosSeleccionadosItemsProperty()
    {
        if ($this->municipiosSeleccionados === []) {
            return collect();
        }

        return Municipio::whereIn('id', $this->municipiosSeleccionados)
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre', 'pais_id', 'tiene_localidades']);
    }

    public function getLocalidadesSeleccionadasItemsProperty()
    {
        if ($this->localidadesSeleccionadas === []) {
            return collect();
        }

        return Localidad::with('municipio:id,nombre')
            ->whereIn('id', $this->localidadesSeleccionadas)
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre', 'municipio_id']);
    }

    public function getPaisesTienenMunicipiosProperty(): bool
    {
        if ($this->modoPaises !== 'incluir' || $this->paisesSeleccionados === []) {
            return false;
        }

        return Municipio::whereIn('pais_id', $this->paisesSeleccionados)->exists();
    }

    public function getMunicipiosTienenLocalidadesProperty(): bool
    {
        if ($this->modoPaises !== 'incluir' || $this->modoMunicipios !== 'incluir' || $this->municipiosSeleccionados === []) {
            return false;
        }

        return Localidad::whereIn('municipio_id', $this->municipiosSeleccionados)->exists();
    }

    public function guardar(): void
    {
        $this->resetErrorBag();

        if ($this->apareceEnTodosLosCasos) {
            $this->sede->update([
                'aparece_en_todos_los_casos' => true,
                'modo_paises' => 'incluir',
                'modo_municipios' => 'ninguno',
                'modo_localidades' => 'ninguno',
            ]);

            $this->sede->paises()->sync([]);
            $this->sede->municipios()->sync([]);
            $this->sede->localidades()->sync([]);

            session()->flash('success', 'La cobertura geográfica de la sede <b>'.$this->sede->nombre.'</b> fue actualizada (aparece en todos los casos).');

            return;
        }

        if ($this->paisesSeleccionados === []) {
            $this->addError('paisesSeleccionados', 'Debes seleccionar al menos un país.');

            return;
        }

        if ($this->modoPaises === 'incluir' && $this->modoMunicipios !== 'ninguno' && $this->municipiosSeleccionados === []) {
            $this->addError('municipiosSeleccionados', 'Debes seleccionar al menos una ciudad o elegir "No exigir ciudad".');

            return;
        }

        if (
            $this->modoPaises === 'incluir'
            && $this->modoMunicipios === 'incluir'
            && $this->modoLocalidades !== 'ninguno'
            && $this->localidadesSeleccionadas === []
        ) {
            $this->addError('localidadesSeleccionadas', 'Debes seleccionar al menos una localidad o elegir "No exigir localidad".');

            return;
        }

        $modoMunicipiosFinal = $this->modoPaises === 'incluir' ? $this->modoMunicipios : 'ninguno';
        $municipiosFinal = $modoMunicipiosFinal !== 'ninguno' ? $this->municipiosSeleccionados : [];

        $modoLocalidadesFinal = ($this->modoPaises === 'incluir' && $modoMunicipiosFinal === 'incluir' && $this->municipiosTienenLocalidades)
            ? $this->modoLocalidades
            : 'ninguno';
        $localidadesFinal = $modoLocalidadesFinal !== 'ninguno' ? $this->localidadesSeleccionadas : [];

        $this->sede->update([
            'aparece_en_todos_los_casos' => false,
            'modo_paises' => $this->modoPaises,
            'modo_municipios' => $modoMunicipiosFinal,
            'modo_localidades' => $modoLocalidadesFinal,
        ]);

        $this->sede->paises()->sync($this->paisesSeleccionados);
        $this->sede->municipios()->sync($municipiosFinal);
        $this->sede->localidades()->sync($localidadesFinal);

        session()->flash('success', 'La cobertura geográfica de la sede <b>'.$this->sede->nombre.'</b> fue guardada con éxito.');
    }

    public function render()
    {
        $resultadosPaises = collect();
        $busquedaPais = trim($this->busquedaPais);
        if (! $this->apareceEnTodosLosCasos && $busquedaPais !== '' && mb_strlen($busquedaPais) >= 2) {
            $buscar = str_replace(["'"], '', Helpers::sanearStringConEspacios(htmlspecialchars($busquedaPais)));
            $resultadosPaises = Pais::query()
                ->whereNotIn('id', $this->paisesSeleccionados)
                ->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE ?", ["%{$buscar}%"])
                ->orderBy('nombre', 'asc')
                ->take(15)
                ->get(['id', 'nombre']);
        }

        $resultadosMunicipios = collect();
        $busquedaMunicipio = trim($this->busquedaMunicipio);
        if (
            ! $this->apareceEnTodosLosCasos
            && $this->modoPaises === 'incluir'
            && $this->modoMunicipios !== 'ninguno'
            && $this->paisesSeleccionados !== []
            && $busquedaMunicipio !== ''
            && mb_strlen($busquedaMunicipio) >= 2
        ) {
            $buscar = str_replace(["'"], '', Helpers::sanearStringConEspacios(htmlspecialchars($busquedaMunicipio)));
            $resultadosMunicipios = Municipio::query()
                ->whereIn('pais_id', $this->paisesSeleccionados)
                ->whereNotIn('id', $this->municipiosSeleccionados)
                ->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE ?", ["%{$buscar}%"])
                ->orderBy('nombre', 'asc')
                ->take(15)
                ->get(['id', 'nombre', 'pais_id']);
        }

        $resultadosLocalidades = collect();
        $busquedaLocalidad = trim($this->busquedaLocalidad);
        if (
            ! $this->apareceEnTodosLosCasos
            && $this->modoPaises === 'incluir'
            && $this->modoMunicipios === 'incluir'
            && $this->modoLocalidades !== 'ninguno'
            && $this->municipiosSeleccionados !== []
            && $busquedaLocalidad !== ''
            && mb_strlen($busquedaLocalidad) >= 2
        ) {
            $buscar = str_replace(["'"], '', Helpers::sanearStringConEspacios(htmlspecialchars($busquedaLocalidad)));
            $resultadosLocalidades = Localidad::with('municipio:id,nombre')
                ->whereIn('municipio_id', $this->municipiosSeleccionados)
                ->whereNotIn('id', $this->localidadesSeleccionadas)
                ->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE ?", ["%{$buscar}%"])
                ->orderBy('nombre', 'asc')
                ->take(15)
                ->get(['id', 'nombre', 'municipio_id']);
        }

        return view('livewire.sedes.configurar-cobertura-geografica', [
            'resultadosPaises' => $resultadosPaises,
            'resultadosMunicipios' => $resultadosMunicipios,
            'resultadosLocalidades' => $resultadosLocalidades,
        ]);
    }
}
