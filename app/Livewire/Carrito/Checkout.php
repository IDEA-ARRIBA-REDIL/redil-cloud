<?php

namespace App\Livewire\Carrito;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
// Componentes de Livewire

use App\Services\ZonaPagoService;

use App\Models\Pago;     // Asegúrate que esté importado

use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Livewire\Component;
use Carbon\Carbon;

use App\Models\AbonoCategoria;
use App\Models\ActividadCategoriaMoneda;
use App\Models\EstadoPago;

use App\Models\Actividad;
use App\Models\Configuracion;
use App\Models\ActividadCategoria;
use App\Models\ActividadCarritoCompra;
use App\Models\ActividadCampoAdicionalCompra;
use App\Models\Moneda;
use App\Models\User;
use App\Models\Compra;
use App\Models\TipoPago;

class Checkout extends Component
{

    public $actividad;
    public $carrito = [];
    public $totalCompra;
    public $compra;
    public $usuarioCompra;
    public  $tiposPagoActividad;
    public $mostrarPaymentTabs;

    public $nombreComprador;
    public $identificacionComprador;
    public $EmailComprador;
    public $telefonoComprador;
    public $configuracion;
    public $moneda;
    public $carritoActual;
    public $categoriaSeleccionada;
    public $fechaHoy;
    public $carritosAbono;

    // esto es para la accion del boton de pagar
    public $tipoPagoSeleccionado;
    public $estadoPagoSeleccionado;
    public $mensajito;
    public $valorAPagarAhora = 0;


    public $pagosAnteriores = [];
    public $valorTotalCategoria = 0;

    // Términos y condiciones
    public $aceptarTerminos = false;


    public function mount(Actividad $actividad, Compra $compra)
    {
        $this->actividad = $actividad;
        $this->compra = $compra;
        $this->configuracion = Configuracion::find(1);
        $this->moneda = Moneda::find($this->compra->moneda_id);
        $this->mostrarPaymentTabs = true;
        $this->tiposPagoActividad = $this->actividad->tiposPago()->with('estadosPago')->get();
        $this->tipoPagoSeleccionado = $this->tiposPagoActividad->first()?->id;

        if (auth()->check()) {
            $this->usuarioCompra = User::find($compra->user_id);
        }

        // --- INICIO DE LA CORRECCIÓN DE LÓGICA ---
        if ($actividad->tipo->permite_abonos) {
            // 1. Obtenemos el item del carrito temporal que se va a pagar AHORA.
            $abonoActual = $compra->carritos()->latest()->first();
            $this->valorAPagarAhora = $abonoActual ? $abonoActual->precio : 0;

            // 2. Obtenemos el historial de pagos YA REALIZADOS (excluyendo el que se acaba de crear).
            $this->pagosAnteriores = Pago::where('compra_id', $compra->id)
                ->where('id', '!=', $abonoActual?->pago_id)
                ->get();

            // 3. Obtenemos el valor total de la categoría para mostrarlo en el resumen.
            if ($abonoActual) {
                $categoria = ActividadCategoria::find($abonoActual->actividad_categoria_id);
                $this->valorTotalCategoria = $categoria->monedas()
                    ->where('moneda_id', $this->compra->moneda_id)
                    ->first()?->pivot->valor ?? 0;
            }

            // Pasamos el item actual a la propiedad que usa la vista para el resumen
            $this->carritosAbono = $compra->carritos()->where('fecha', Carbon::now()->format('Y-m-d'))->get();
        } else {
            // Para compras normales, el valor a pagar es el total de la compra.
            $this->valorAPagarAhora = $this->compra->valor;
        }

        // Inicializar con el primer tipo de pago (si existe)
        if ($this->tiposPagoActividad->isNotEmpty()) {
            $this->tipoPagoSeleccionado = $this->tiposPagoActividad->first()->id;
        }
    }



    protected function rules()
    {
        // Las reglas solo se aplican si el usuario es un invitado.
        if (!auth()->check()) {
            return [
                'nombreComprador' => 'required|string|min:3',
                'identificacionComprador' => 'required|string|min:5',
                'EmailComprador' => 'required|email',
                'telefonoComprador' => 'required|string|min:7',
                'telefonoComprador' => 'required|string|min:7',
                'aceptarTerminos' => $this->actividad->terminos_y_condiciones ? 'accepted' : 'nullable',
            ];
        }

        // Si el usuario está autenticado, validamos solo los términos si existen
        return [
             'aceptarTerminos' => $this->actividad->terminos_y_condiciones ? 'accepted' : 'nullable',
        ];
    }

    protected function messages()
    {
        return [
            'nombreComprador.required' => 'El nombre completo es obligatorio.',
            'identificacionComprador.required' => 'El número de identificación es obligatorio.',
            'EmailComprador.required' => 'El correo electrónico es obligatorio.',
            'EmailComprador.email' => 'El formato del correo no es válido.',
            'telefonoComprador.required' => 'El número de teléfono es obligatorio.',
            'telefonoComprador.required' => 'El número de teléfono es obligatorio.',
            'aceptarTerminos.accepted' => 'Debes aceptar los términos y condiciones para continuar.',
        ];
    }

    public function redirigirAtras()
    {
        // Lógica específica para Escuelas
        if ($this->actividad->tipo->tipo_escuelas) {
            if ($this->configuracion->envio_material) {
                return redirect()->route('carrito.destinatario', ['actividad' => $this->actividad]);
            } elseif ($this->actividad->elementos->count() > 0) {
                return redirect()->route('carrito.formulario', ['compra' => $this->compra, 'actividad' => $this->actividad]);
            } else {
                return redirect()->route('carrito.escuelasCarrito', ['actividad' => $this->actividad, 'primeraVez' => 0, 'compra' => $this->compra->id]);
            }
        }

        // Lógica estándar para otras actividades
        if ($this->actividad->elementos->count() > 0) {
            return redirect()->route('carrito.formulario', ['compra' => $this->compra, 'actividad' => $this->actividad]);
        } else {
            return redirect()->route('carrito.carrito', ['compra' => $this->compra, 'actividad' => $this->actividad, 'primeraVez' => 0]);
        }
    }

    public function redirigirAtrasAbono()
    {


        if ($this->actividad->elementos->count() > 0) {
            return redirect()->route('carrito.formulario', ['compra' => $this->compra, 'actividad' => $this->actividad]);
        } else {
            return redirect()->route('carrito.abonoCarrito', ['compra' => $this->compra, 'actividad' => $this->actividad, 'primeraVez' => 0]);
        }
    }

    public function procesarPago()
    {
        // Validar términos y condiciones si aplica
        if ($this->actividad->terminos_y_condiciones) {
             $this->validate([
                'aceptarTerminos' => 'accepted'
             ]);
        }

        // **VALIDACIÓN DE DATOS DEL COMPRADOR PARA INVITADOS**
        // $this->validate();

        $tipoPago = \App\Models\TipoPago::find($this->tipoPagoSeleccionado);

        if (!$tipoPago) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'Método de pago no válido.']);
            return;
        }

        if ($tipoPago->id == 5) {
            $this->procesarPagoEfectivoPDP($tipoPago);
            return;
        }

        switch ($tipoPago->key_reservada) {
            case 'zona':
                $this->procesarPagoZonaPagos($tipoPago);
                break;
            default:
                $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'Procesador para este método de pago no implementado.']);
                break;
        }
    }

    public function procesarPagoEfectivoPDP($tipoPago)
    {
        if (!$this->compra) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se encuentra una compra válida para procesar.']);
            return;
        }

        if (empty($this->estadoPagoSeleccionado)) {
             $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Atención', 'mensaje' => 'Debes seleccionar un estado para registrar el pago.']);
             return;
        }

        // Actualizar datos comprador
        if (auth()->check()) {
            $this->nombreComprador = $this->usuarioCompra->nombre(3);
            $this->identificacionComprador = $this->usuarioCompra->identificacion;
            $this->EmailComprador = $this->usuarioCompra->email;
            $this->telefonoComprador = $this->usuarioCompra->telefono_movil;
        }

        $this->compra->update([
            'nombre_completo_comprador' => $this->nombreComprador,
            'identificacion_comprador' => $this->identificacionComprador,
            'email_comprador' => $this->EmailComprador,
            'telefono_comprador' => $this->telefonoComprador,
        ]);

        $pagoParaProcesar = Pago::where('compra_id', $this->compra->id)->latest()->first();

        if (!$pagoParaProcesar) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'El registro de pago asociado es inválido.']);
            return;
        }

        // Actualizar pago con el estado seleccionado manualmente
        $pagoParaProcesar->update([
            'tipo_pago_id' => $this->tipoPagoSeleccionado,
            'estado_pago_id' => $this->estadoPagoSeleccionado,
            'fecha' => now(),
        ]);

        // Si es escuelas, actualizar matrícula
        if ($this->actividad->tipo->tipo_escuelas) {
            \App\Models\Matricula::where('referencia_pago', $pagoParaProcesar->id)
                ->update(['tipo_pago_id' => $this->tipoPagoSeleccionado]);
        }

        // Verificar el estado para actualizar la compra
        $estado = \App\Models\EstadoPago::find($this->estadoPagoSeleccionado);

        // Actualizar el estado de la compra basado en flags del EstadoPago
        if ($estado->estado_final_inscripcion) {
            $this->compra->update(['estado' => 3]); // 3 = PAGADA / FINALIZADA
        } elseif ($estado->estado_anulado_inscripcion) {
            $this->compra->update(['estado' => 4]); // 4 = ANULADA / ERROR
        } elseif ($estado->estado_pendiente) {
            $this->compra->update(['estado' => 1]); // 1 = PENDIENTE / INICIADA
        }

        // Redirigir a la vista de compra finalizada
        return redirect()->route('carrito.compraFinalizada', ['pago' => $pagoParaProcesar->id]);
    }


    public function procesarPagoZonaPagos($tipoPago)
    {
        if (!$this->compra) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se encuentra una compra válida para procesar.']);
            return;
        }

        // Se actualizan los datos del comprador en la compra
        if (auth()->check()) {
            $this->nombreComprador = $this->usuarioCompra->nombre(3);
            $this->identificacionComprador = $this->usuarioCompra->identificacion;
            $this->EmailComprador = $this->usuarioCompra->email;
            $this->telefonoComprador = $this->usuarioCompra->telefono_movil;
        }
        $this->compra->update([
            'nombre_completo_comprador' => $this->nombreComprador,
            'identificacion_comprador' => $this->identificacionComprador,
            'email_comprador' => $this->EmailComprador,
            'telefono_comprador' => $this->telefonoComprador,
        ]);

        // Se preparan los datos para el servicio de pagos
        $datosComprador = [
            'nombre' => $this->nombreComprador,
            'apellido' => '',
            'identificacion' => $this->identificacionComprador,
            'email' => $this->EmailComprador,
            'telefono' => $this->telefonoComprador
        ];

        // Se busca el último pago pendiente asociado a esta compra
        $pagoParaProcesar = Pago::where('compra_id', $this->compra->id)->latest()->first();

        if (!$pagoParaProcesar) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'El registro de pago asociado es inválido.']);
            return;
        }

        // --- INICIO DE LA CORRECCIÓN ---
        // Se actualiza el registro de Pago con la selección del usuario ANTES de enviarlo a la pasarela.

        // 1. Buscamos el estado inicial por defecto para el método de pago seleccionado a través de su relación.
        $estadoInicial = $tipoPago->estadosPago()->where('estado_inicial_defecto', true)->first();

        if (!$estadoInicial) {
            $this->dispatch('mostrarMensaje', [
                'tipo' => 'error',
                'titulo' => 'Error de Configuración',
                'mensaje' => "El método de pago <b>'{$tipoPago->nombre}'</b> (ID: {$this->tipoPagoSeleccionado}) no tiene un <b>estado inicial por defecto</b> configurado en la base de datos.<br><small>Por favor, verifica la tabla 'estados_pago'.</small>"
            ]);
            return;
        }

        // 2. Actualizamos el pago con el tipo de pago y el estado inicial correspondiente.
        $pagoParaProcesar->update([
            'tipo_pago_id' => $this->tipoPagoSeleccionado,
            'estado_pago_id' => $estadoInicial->id,
        ]);

        // Si es una actividad tipo escuela, asignamos el tipo de pago a la matrícula asociada
        if ($this->actividad->tipo->tipo_escuelas) {
            \App\Models\Matricula::where('referencia_pago', $pagoParaProcesar->id)
                ->update(['tipo_pago_id' => $this->tipoPagoSeleccionado]);
        }
        // --- FIN DE LA CORRECCIÓN ---

        // Se determina el tipo de compra para enviarlo en el campo opcional
        $tipoCompra = 'COMPRA GENERAL';
        if ($this->actividad->tipo->permite_abonos) {
            $tipoCompra = 'ABONO';
        } elseif ($this->actividad->tipo->tipo_escuelas) {
            $tipoCompra = 'ESCUELAS';
        }

        // Se llama al servicio de pagos con el registro de Pago ya actualizado
        $zonaPagosService = new ZonaPagoService();
        $resultado = $zonaPagosService->iniciarPago($pagoParaProcesar, $datosComprador, $tipoCompra);

        if ($resultado['success']) {
            // Si el servicio responde con éxito, se actualiza el pago con la URL y se redirige
            $pagoParaProcesar->update([
                'payment_url' => $resultado['payment_url'],
                'gateway_response' => $resultado['gateway_response']
            ]);
            return redirect()->to($resultado['payment_url']);
        } else {
            // Si el servicio falla, se muestra el error detallado
            $mensajeError = $resultado['message'] ?? 'No se pudo iniciar el proceso de pago.';
            $detalleError = isset($resultado['response']) ? json_encode($resultado['response'], JSON_PRETTY_PRINT) : 'Sin respuesta adicional.';

            Log::error('Error ZonaPagos: ' . $mensajeError, ['response' => $resultado['response'] ?? []]);

            $this->dispatch('mostrarMensaje', [
                'tipo' => 'error',
                'titulo' => 'Error de Pasarela',
                'mensaje' => "<b>{$mensajeError}</b><br><br><p class='text-start small'>Detalle técnico:<br><code style='font-size: 10px;'>" . e($detalleError) . "</code></p>"
            ]);
        }
    }


    public function render()
    {
        return view('livewire.carrito.checkout');
    }
}
