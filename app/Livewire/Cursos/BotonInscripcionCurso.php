<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use App\Models\Curso;
use App\Models\CursoUser;
use App\Models\CarritoCursoUser;
use Illuminate\Support\Facades\Auth;

class BotonInscripcionCurso extends Component
{
    public Curso $curso;
    public $validacion = [];
    public $cargando = false;
    public $enCarrito = false;
    public $cantidadCarrito = 0;

    public function mount(Curso $curso)
    {
        $this->curso = $curso;
        $this->verificarRequisitos();
        $this->verificarCarrito();
    }

    public function verificarCarrito()
    {
        if (Auth::check()) {
            $carrito = CarritoCursoUser::where('user_id', Auth::id())->where('estado', 'pendiente')->first();
            if ($carrito && is_array($carrito->items)) {
                $this->cantidadCarrito = count($carrito->items);
                $this->enCarrito = collect($carrito->items)->contains('curso_id', $this->curso->id);
            }
        }
    }

    public function verificarRequisitos()
    {
        if (Auth::check()) {
            /** @var \App\Models\User $usuario */
            $usuario = Auth::user();
            $this->validacion = $this->curso->validarRequisitosUsuarioCurso($usuario);
        } else {
            // Si no está logueado, asumimos temporalmente que bloqueamos u obligamos login
            $this->validacion = [
                'cumple' => false,
                'codigo' => 'NO_AUTH',
                'razones' => ['Debes iniciar sesión para inscribirte.']
            ];
        }
    }

    public function inscribirGratis()
    {
        $this->cargando = true;

        if (!$this->validacion['cumple'] || !$this->curso->es_gratuito) {
            $this->cargando = false;
            return;
        }

        // Crear la inscripción directa
        CursoUser::create([
            'curso_id' => $this->curso->id,
            'user_id' => Auth::id(),
            'estado' => 'activo',
            'fecha_vencimiento_acceso' => $this->curso->dias_acceso_limitado ? now()->addDays($this->curso->dias_acceso_limitado) : null,
        ]);

        $this->cargando = false;

        // Redirigir al usuario al aula (por ahora a previsualizar de nuevo o listado, ajusta segun ruta final del curso)
        return redirect()->route('cursos.gestionar')->with('success', '¡Inscripción exitosa! Bienvenido al curso.');
    }

    public function agregarAlCarrito()
    {
        $this->cargando = true;

        if (!$this->validacion['cumple'] || $this->curso->es_gratuito) {
            $this->cargando = false;
            return;
        }

        $usuario = Auth::user();

        // Buscar carrito pendiente o crearlo
        $carrito = CarritoCursoUser::firstOrCreate(
            ['user_id' => $usuario->id, 'estado' => 'pendiente'],
            ['items' => [], 'total' => 0]
        );

        $carrito->agregarCurso($this->curso, $this->curso->precio);

        $this->cargando = false;
        $this->enCarrito = true;
        $this->cantidadCarrito = count($carrito->fresh()->items ?? []);

        $this->dispatch('notificar', [
            'tipo' => 'success',
            'mensaje' => 'Curso añadido al carrito de compras.'
        ]);
    }

    public function comprarDirecto()
    {
        $this->cargando = true;

        if (!$this->validacion['cumple'] || $this->curso->es_gratuito) {
            $this->cargando = false;
            return;
        }

        // Aquí redirigiremos a la nueva vista interactiva de CheckoutCursos
        // Pasando el curso_id que quiere comprar directamente
        return redirect()->to('/cursos/checkout?curso_id=' . $this->curso->id);
    }

    public function render()
    {
        return view('livewire.cursos.boton-inscripcion-curso');
    }
}
