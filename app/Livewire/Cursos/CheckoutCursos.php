<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use App\Models\CarritoCursoUser;
use App\Models\Curso;
use App\Models\TipoPago;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\ZonaPagoService;
use App\Mail\CompraCursoConfirmacionMail;
use Illuminate\Support\Facades\Mail;

class CheckoutCursos extends Component
{
    public $carrito;
    public $usuarioCompra;
    public $tiposPagoDisponibles = [];
    public $tipoPagoSeleccionado;

    // Datos del comprador
    public $nombreComprador;
    public $identificacionComprador;
    public $EmailComprador;
    public $telefonoComprador;

    // Control de UI
    public $cargando = false;

    // Si viene directo de "Comprar Ahora", pasamos un curso específico
    // en mount() en vez de usar el carrito completo.

    public function mount()
    {
        $this->usuarioCompra = Auth::user();

        // 1. Verificar si viene de un "Comprar Ahora" leyendo la URL
        $cursoIdDirecto = request()->query('curso_id');

        if ($cursoIdDirecto) {
            $curso = Curso::find($cursoIdDirecto);
            if ($curso && !$curso->es_gratuito) {
                // Generamos un carrito virtual on-the-fly para la vista
                $this->carrito = new CarritoCursoUser([
                    'items' => [
                        [
                            'curso_id' => $curso->id,
                            'nombre'   => $curso->nombre,
                            'precio'   => $curso->precio
                        ]
                    ],
                    'total' => $curso->precio,
                    'estado' => 'virtual'
                ]);

                // Los tipos de pago permitidos se rigen por los de ESTE curso.
                $this->tiposPagoDisponibles = $curso->tiposPago()->with('estadosPago')->get();
            }
        } else {
            // 2. Viene del carrito general de LMS
            $this->carrito = CarritoCursoUser::where('user_id', $this->usuarioCompra->id)
                ->where('estado', 'pendiente')
                ->first();

            if ($this->carrito && !empty($this->carrito->items)) {
                $cursoIds = collect($this->carrito->items)->pluck('curso_id')->toArray();
                $cursos = Curso::whereIn('id', $cursoIds)->with('tiposPago.estadosPago')->get();

                $todosTipos = collect();
                foreach ($cursos as $curso) {
                    $todosTipos = $todosTipos->merge($curso->tiposPago);
                }

                $this->tiposPagoDisponibles = $todosTipos->unique('id')->values();
            } else {
                $this->tiposPagoDisponibles = collect();
            }
        }

        if (!$this->carrito || empty($this->carrito->items)) {
            return redirect()->route('cursos.gestionar')->with('error', 'Tu carrito de cursos está vacío.');
        }

        if ($this->tiposPagoDisponibles->isNotEmpty()) {
            $this->tipoPagoSeleccionado = $this->tiposPagoDisponibles->first()->id;
        }

        // Poblamos datos usuario
        $this->nombreComprador = $this->usuarioCompra->nombre(3);
        $this->identificacionComprador = $this->usuarioCompra->identificacion;
        $this->EmailComprador = $this->usuarioCompra->email;
        $this->telefonoComprador = $this->usuarioCompra->telefono_movil;
    }

    protected function rules()
    {
        return [
            'nombreComprador' => 'required|string|min:3',
            'identificacionComprador' => 'required|string|min:5',
            'EmailComprador' => 'required|email',
            'telefonoComprador' => 'required|string|min:7',
            'tipoPagoSeleccionado' => 'required'
        ];
    }

    public function procesarPago()
    {
        $this->validate();
        $this->cargando = true;

        $tipoPago = TipoPago::find($this->tipoPagoSeleccionado);
        if (!$tipoPago) {
            $this->addError('tipoPagoSeleccionado', 'El método de pago es inválido.');
            $this->cargando = false;
            return;
        }

        // Aquí se crearía el registro intermediario `OrdenCurso` (si lo deseas) o simplemente procesar con ZonaPagos
        // Para acortar este ejemplo MVP y hacer la logica independiente, usaremos logica de "Pago Correcto"
        // y simularemos la creación.

        if ($tipoPago->id == 5) {
            $this->procesarPagoEfectivoPDP($tipoPago);
            return;
        }

        // Pasarela
        if ($tipoPago->key_reservada == 'zona') {
            $this->procesarZonaPagos($tipoPago);
        } else {
            // Simulador Exitoso por Defecto para metodos mockeados
            $this->procesarPagoEfectivoPDP($tipoPago);
        }
    }

    private function procesarPagoEfectivoPDP($tipoPago)
    {
        // El pago manual inscribe inmediatamente al estudiante en todos los cursos del carrito
        foreach ($this->carrito->items as $item) {
            $curso = Curso::find($item['curso_id']);
            if ($curso) {
                \App\Models\CursoUser::updateOrCreate(
                    ['curso_id' => $curso->id, 'user_id' => $this->usuarioCompra->id],
                    [
                        'estado' => 'activo',
                        'fecha_vencimiento_acceso' => $curso->dias_acceso_limitado ? now()->addDays($curso->dias_acceso_limitado) : null
                    ]
                );
            }
        }

        if ($this->carrito->id) {
            $this->carrito->update([
                'estado' => 'completado',
                // Podríamos guardar un JSON con los datos del comprador si lo necesitamos a futuro
            ]);
        }

        // Enviar Correo de Confirmación
        try {
            Mail::to($this->EmailComprador)->send(new CompraCursoConfirmacionMail($this->carrito, $this->nombreComprador, $this->identificacionComprador, $this->telefonoComprador));
        } catch (\Exception $e) {
            Log::error('Error enviando correo de confirmación de curso: ' . $e->getMessage());
        }

        $this->cargando = false;

        return redirect()->route('cursos.compraFinalizada', ['carrito' => $this->carrito->id]);
    }

    private function procesarZonaPagos($tipoPago)
    {
        // En una implementación real, aquí inicias la transacción a Zona Pagos
        // Y el webhook de retorno (Route::post('/webhook/zonapagos')) sería el encargado
        // de hacer el \App\Models\CursoUser::create() una vez reciba {estado: exitoso}.

        $this->cargando = false;
        $this->addError('tipoPagoSeleccionado', 'Pasarela ZonaPagos no implementada completamente en Checkout Cursos Mockup.');
        return;
    }

    public function render()
    {
        return view('livewire.cursos.checkout-cursos');
    }
}
