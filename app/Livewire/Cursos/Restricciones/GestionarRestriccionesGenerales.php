<?php

namespace App\Livewire\Cursos\Restricciones;

use Livewire\Component;
use App\Models\Curso;
use App\Models\Sede;
use App\Models\RangoEdad;
use App\Models\EstadoCivil;
use App\Models\TipoServicioGrupo;

class GestionarRestriccionesGenerales extends Component
{
    public Curso $curso;

    // Campos directos
    public $genero;
    public $vinculacion_grupo;
    public $actividad_grupo;
    public $excluyente;

    // Campos relaciones (Array IDs)
    public $sedesSeleccionadas = [];
    public $rangosEdadSeleccionados = [];
    public $estadosCivilesSeleccionados = [];
    public $tipoServiciosSeleccionados = [];

    // Catalogos
    public $sedes;
    public $rangosEdad;
    public $estadosCiviles;
    public $tipoServicios;

    public function mount(Curso $curso)
    {
        $this->curso = $curso;

        // Cargar valores iniciales desde el modelo
        $this->genero = $curso->genero ?? 3;
        $this->vinculacion_grupo = $curso->vinculacion_grupo ?? 3;
        $this->actividad_grupo = $curso->actividad_grupo ?? 3;
        $this->excluyente = $curso->excluyente;

        // Cargar relaciones
        $this->sedesSeleccionadas = $curso->sedes->pluck('id')->toArray();
        $this->rangosEdadSeleccionados = $curso->rangosEdad->pluck('id')->toArray();
        $this->estadosCivilesSeleccionados = $curso->estadosCiviles->pluck('id')->toArray();


        // Cargar catálogos
        $this->sedes = Sede::orderBy('nombre')->get();
        $this->rangosEdad = RangoEdad::orderBy('nombre')->get();
        $this->estadosCiviles = EstadoCivil::orderBy('nombre')->get();
        $this->tipoServicios = TipoServicioGrupo::orderBy('nombre')->get();
    }

    public function guardar()
    {
        // Validacion
        $this->validate([
            'genero' => 'required|integer|in:1,2,3',
            'vinculacion_grupo' => 'required|integer|in:1,2,3',
            'actividad_grupo' => 'required|integer|in:1,2,3',
            'excluyente' => 'required|boolean',
            'sedesSeleccionadas' => 'nullable|array',
            'rangosEdadSeleccionados' => 'nullable|array',
            'estadosCivilesSeleccionados' => 'nullable|array',
            'tipoServiciosSeleccionados' => 'nullable|array',
        ]);

        // Guardar Campos Directos
        $this->curso->update([
            'genero' => $this->genero,
            'vinculacion_grupo' => $this->vinculacion_grupo,
            'actividad_grupo' => $this->actividad_grupo,
            'excluyente' => $this->excluyente,
        ]);

        // Sincronizar Relaciones
        $this->curso->sedes()->sync($this->sedesSeleccionadas);
        $this->curso->rangosEdad()->sync($this->rangosEdadSeleccionados);
        $this->curso->estadosCiviles()->sync($this->estadosCivilesSeleccionados);


        $this->dispatch('msn', [
            'msn' => 'Restricciones generales actualizadas correctamente.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.cursos.restricciones.gestionar-restricciones-generales');
    }
}
