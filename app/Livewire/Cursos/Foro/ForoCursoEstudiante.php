<?php

namespace App\Livewire\Cursos\Foro;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Curso;
use App\Models\CursoItem;
use App\Models\CursoForoHilo;
use App\Models\CursoForoRespuesta;
use Illuminate\Support\Facades\Auth;

class ForoCursoEstudiante extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $cursoId;
    public $curso;

    // Filtros
    public $search = '';
    public $filtroMisPreguntas = false;

    // Control de Vistas
    // Vistas posibles: 'lista', 'crear', 'detalle_hilo'
    public $vistaActual = 'lista';

    // Hilo seleccionado para ver detalles
    public $hiloSeleccionadoId = null;

    // Formulario Nueva Pregunta
    public $nuevaPreguntaTitulo = '';
    public $nuevaPreguntaCuerpo = '';
    public $moduloItemAsociadoId = ''; // Opcional: asociar a una clase

    // Formulario Respuesta
    public $nuevaRespuestaCuerpo = '';

    public function mount($cursoId)
    {
        $this->cursoId = $cursoId;
        $this->curso = Curso::with('modulos.items')->findOrFail($cursoId);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroMisPreguntas()
    {
        $this->resetPage();
    }

    // --- ACCIONES DE VISTA ---

    public function volverALista()
    {
        $this->vistaActual = 'lista';
        $this->hiloSeleccionadoId = null;
        $this->resetFormularios();
    }

    public function abrirFormularioNuevaPregunta()
    {
        $this->vistaActual = 'crear';
        $this->resetFormularios();
    }

    public function verHilo($id)
    {
        $this->hiloSeleccionadoId = $id;
        $this->vistaActual = 'detalle_hilo';
        $this->nuevaRespuestaCuerpo = ''; // Limpiar la caja de respuesta
    }

    private function resetFormularios()
    {
        $this->nuevaPreguntaTitulo = '';
        $this->nuevaPreguntaCuerpo = '';
        $this->moduloItemAsociadoId = '';
        $this->nuevaRespuestaCuerpo = '';
        $this->resetValidation();
    }

    // --- LOGICA DE CREACION ---

    public function guardarNuevaPregunta()
    {
        $this->validate([
            'nuevaPreguntaTitulo' => 'required|max:255',
            'nuevaPreguntaCuerpo' => 'required|min:10',
        ], [
            'nuevaPreguntaTitulo.required' => 'El resumen/título es obligatorio.',
            'nuevaPreguntaCuerpo.required' => 'Debes explicar tu duda con al menos 10 caracteres.',
        ]);

        if ($this->moduloItemAsociadoId) {
            $item = CursoItem::with('tipo')->find($this->moduloItemAsociadoId);
            if ($item && $item->tipo && in_array($item->tipo->codigo, ['evaluacion', 'quiz', 'final'])) {
                $this->addError('moduloItemAsociadoId', 'No puedes realizar preguntas públicas asociadas a las evaluaciones para proteger las respuestas.');
                return;
            }
        }

        CursoForoHilo::create([
            'curso_id' => $this->cursoId,
            'user_id' => Auth::id(),
            'curso_item_id' => $this->moduloItemAsociadoId ?: null,
            'titulo' => $this->nuevaPreguntaTitulo,
            'cuerpo' => $this->nuevaPreguntaCuerpo,
            'estado' => 'pendiente',
        ]);

        session()->flash('success', 'Tu pregunta ha sido publicada con éxito. El equipo o algún compañero responderá pronto.');
        $this->volverALista();
    }

    public function guardarRespuesta()
    {
        // Solo permitir responder si hay un cuerpo
        $this->validate([
            'nuevaRespuestaCuerpo' => 'required|min:2',
        ], [
            'nuevaRespuestaCuerpo.required' => 'No puedes enviar una respuesta vacía.',
        ]);

        $hilo = CursoForoHilo::findOrFail($this->hiloSeleccionadoId);

        // Evitar respuestas si está cerrado
        if ($hilo->estado === 'cerrado') {
            session()->flash('error', 'Esta conversación ya se encuentra cerrada.');
            return;
        }

        CursoForoRespuesta::create([
            'hilo_id' => $this->hiloSeleccionadoId,
            'user_id' => Auth::id(),
            'cuerpo' => $this->nuevaRespuestaCuerpo,
            'es_respuesta_oficial' => false, // Es estudiante, no es oficial
        ]);

        // Si el estado estaba en resuelto, pero el alumno de la pregunta original replica, vuelve a pendiente?
        // Lógica de negocio a debatir, por ahora lo dejamos tal cual o lo pasamos a pendiente.
        if ($hilo->estado === 'resuelto' && $hilo->user_id === Auth::id()) {
            $hilo->update(['estado' => 'pendiente']);
        }

        $this->nuevaRespuestaCuerpo = ''; // Limpiar textarea
        session()->flash('success', 'Respuesta publicada.');
    }

    // --- RENDERIZADO ---

    public function render()
    {
        $hilos = collect();
        $hiloDetalle = null;

        if ($this->vistaActual === 'lista') {
            $query = CursoForoHilo::where('curso_id', $this->cursoId)
                ->with(['user', 'item', 'respuestas']);

            // Filtro Mis Preguntas
            if ($this->filtroMisPreguntas) {
                $query->where('user_id', Auth::id());
            }

            // Búsqueda de texto
            if (!empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('titulo', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('cuerpo', 'LIKE', '%' . $this->search . '%');
                });
            }

            $hilos = $query->orderBy('created_at', 'desc')->paginate(10);
        } elseif ($this->vistaActual === 'detalle_hilo') {
            // Cargar el hilo específico con sus respuestas ordenadas
            $hiloDetalle = CursoForoHilo::with(['user', 'item', 'respuestas.user'])
                ->findOrFail($this->hiloSeleccionadoId);
        }

        return view('livewire.cursos.foro.foro-curso-estudiante', [
            'hilos' => $hilos,
            'hiloDetalle' => $hiloDetalle,
        ]);
    }
}
