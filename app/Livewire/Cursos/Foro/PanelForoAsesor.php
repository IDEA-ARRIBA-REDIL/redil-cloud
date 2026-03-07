<?php

namespace App\Livewire\Cursos\Foro;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CursoForoHilo;
use App\Models\CursoForoRespuesta;
use App\Models\Configuracion;
use App\Models\Curso;
use Illuminate\Support\Facades\Auth;

class PanelForoAsesor extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Filtros
    public $filtroEstado = 'todos'; // pendiente, resuelto, cerrado, todos
    public $filtroCursoId = '';
    public $searchTitle = '';

    // Hilo Activo en Offcanvas
    public $hiloActivoId = null;
    public $hiloActivo = null;

    public $configuracion = '';

    // Formulario de respuesta
    public $respuestaAsesorCuerpo = '';

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroCursoId()
    {
        $this->resetPage();
    }

    public function updatingSearchTitle()
    {
        $this->resetPage();
    }

    // --- ACCIONES SOBRE EL HILO ---

    public function abrirHilo($id)
    {
        $this->hiloActivoId = $id;
        $this->cargarHilo();
        $this->respuestaAsesorCuerpo = '';
        $this->resetValidation();
        // Disparar evento para abrir Offcanvas en UI
        $this->dispatch('abrir-offcanvas-foro');
    }

    public function cargarHilo()
    {
        if ($this->hiloActivoId) {
            $this->hiloActivo = CursoForoHilo::with(['user', 'item', 'curso', 'respuestas.user'])->find($this->hiloActivoId);
        }
    }

    public function enviarRespuestaOficial()
    {
        $this->validate([
            'respuestaAsesorCuerpo' => 'required|min:2',
        ], [
            'respuestaAsesorCuerpo.required' => 'Debes escribir una respuesta.',
        ]);

        if ($this->hiloActivo && $this->hiloActivo->estado !== 'cerrado') {
            CursoForoRespuesta::create([
                'hilo_id' => $this->hiloActivoId,
                'user_id' => Auth::id(),
                'cuerpo' => $this->respuestaAsesorCuerpo,
                'es_respuesta_oficial' => true, // Importante: Marca como oficial
            ]);

            $this->respuestaAsesorCuerpo = '';
            $this->cargarHilo(); // Recarga las respuestas
            session()->flash('successHilo', 'Respuesta oficial enviada con éxito.');
        }
    }

    public function cambiarEstadoHilo($nuevoEstado)
    {
        if (in_array($nuevoEstado, ['pendiente', 'resuelto', 'cerrado']) && $this->hiloActivo) {
            $this->hiloActivo->update(['estado' => $nuevoEstado]);
            $this->cargarHilo();
            session()->flash('successHilo', 'Estado del hilo cambiado a ' . ucfirst($nuevoEstado));
        }
    }

    public function render()
    {
        $configuracion = Configuracion::first();

        // Obtener el rol global activo
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $query = CursoForoHilo::with(['curso', 'user', 'item', 'respuestas'])
            ->orderBy('created_at', 'desc');

        $verTodas = $rolActivo && $rolActivo->hasPermissionTo('cursos.listar_todas_conversaciones');
        $verSoloAsignadas = $rolActivo && $rolActivo->hasPermissionTo('cursos.conversaciones_cursos_asignados');

        if ($verTodas) {
            // Ver todas -> sin restricciones preventivas
        } elseif ($verSoloAsignadas) {
            // Evaluamos si, de acuerdo a la BD local, tiene rol en el equipo del curso
            // y además su `TipoCargoCurso` posee `puede_responder_preguntas == true`
            $query->whereHas('curso', function ($qCurso) {
                $qCurso->whereHas('equipo', function ($qEquipo) {
                    $qEquipo->where('usuario_id', auth()->id())
                            ->where('activo', true)
                            ->whereHas('tipoCargo', function ($qCargo) {
                                $qCargo->where('puede_responder_preguntas', true);
                            });
                });
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        // Aplicar filtros
        if ($this->filtroEstado !== 'todos') {
            $query->where('estado', $this->filtroEstado);
        }

        if (!empty($this->filtroCursoId)) {
            $query->where('curso_id', $this->filtroCursoId);
        }

        if (!empty($this->searchTitle)) {
            $query->where(function ($q) {
                $q->where('titulo', 'LIKE', '%' . $this->searchTitle . '%')
                    ->orWhere('cuerpo', 'LIKE', '%' . $this->searchTitle . '%');
            });
        }

        $hilosList = $query->paginate(15);

        $cursosFiltro = Curso::select('id', 'nombre')->orderBy('nombre')->get();

        return view('livewire.cursos.foro.panel-foro-asesor', [
            'hilosList' => $hilosList,
            'cursosFiltro' => $cursosFiltro,
        ]);
    }
}
