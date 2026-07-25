<?php

namespace App\Livewire\Carrito;

use App\Models\Actividad;
// Componentes de Livewire

use App\Models\ActividadCategoria; // Servicio canónico unificado
use App\Models\Compra;     // Asegúrate que esté importado
use App\Models\Configuracion;
use App\Models\EstadoPago;
use App\Models\Moneda;
use App\Models\Pago;
use App\Models\User;
use App\Services\ZonaPagosService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Checkout extends Component
{
    public $actividad;

    public $carrito = [];

    public $totalCompra;

    public $compra;

    public $usuarioCompra;

    public $tiposPagoActividad;

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

    // Estado para nueva ventana y monitoreo en tiempo real de ZonaPagos
    public bool $esperandoPagoZonaPagos = false;

    public bool $tienePagoEnProcesoZonaPagos = false;

    public ?int $pagoIdEsperando = null;

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

        // Detectar si la compra ya tiene un pago en proceso activo con ZonaPagos
        $pagoPendienteZona = Pago::where('compra_id', $compra->id)
            ->whereHas('tipoPago', fn ($q) => $q->where('key_reservada', 'zona'))
            ->whereHas('estadoPago', fn ($q) => $q->where('estado_pendiente', true))
            ->latest('id')
            ->first();

        if ($pagoPendienteZona) {
            $this->esperandoPagoZonaPagos = true;
            $this->pagoIdEsperando = $pagoPendienteZona->id;
            $this->tienePagoEnProcesoZonaPagos = true;
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
        if (! auth()->check()) {
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
                'aceptarTerminos' => 'accepted',
            ]);
        }

        // Validación de datos del comprador para invitados (si no está autenticado)
        if (! auth()->check()) {
            $this->validate();
        }

        $tipoPago = \App\Models\TipoPago::find($this->tipoPagoSeleccionado);

        if (! $tipoPago) {
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
        if (! $this->compra) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se encuentra una compra válida para procesar.']);

            return;
        }

        if (empty($this->estadoPagoSeleccionado)) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Atención', 'mensaje' => 'Debes seleccionar un estado para registrar el pago.']);

            return;
        }

        // Actualizar datos comprador
        if (auth()->check()) {
            $this->nombreComprador = $this->usuarioCompra->nombre(3) ?: 'Usuario Redil';
            $this->identificacionComprador = $this->usuarioCompra->identificacion ?: '000000000';
            $this->EmailComprador = $this->usuarioCompra->email ?: 'sin-email@redil.com';
            $this->telefonoComprador = $this->usuarioCompra->telefono_movil ?: '111111111';
        }

        $this->compra->update([
            'nombre_completo_comprador' => $this->nombreComprador,
            'identificacion_comprador' => $this->identificacionComprador,
            'email_comprador' => $this->EmailComprador,
            'telefono_comprador' => $this->telefonoComprador,
        ]);

        $pagoParaProcesar = Pago::where('compra_id', $this->compra->id)->latest()->first();

        if (! $pagoParaProcesar) {
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
        if (! $this->compra) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se encuentra una compra válida para procesar.']);

            return;
        }

        // Se actualizan los datos del comprador en la compra
        if (auth()->check()) {
            $this->nombreComprador = $this->usuarioCompra->nombre(3) ?: 'Usuario Redil';
            $this->identificacionComprador = $this->usuarioCompra->identificacion ?: '000000000';
            $this->EmailComprador = $this->usuarioCompra->email ?: 'sin-email@redil.com';
            $this->telefonoComprador = $this->usuarioCompra->telefono_movil ?: '111111111';
        }

        $this->compra->update([
            'nombre_completo_comprador' => $this->nombreComprador,
            'identificacion_comprador' => $this->identificacionComprador,
            'email_comprador' => $this->EmailComprador,
            'telefono_comprador' => $this->telefonoComprador,
        ]);

        // Separamos nombre y apellido para enviarlos por separado a ZonaPagos.
        // El servicio también hace esta separación como fallback, pero es mejor
        // hacerlo aquí donde tenemos acceso al modelo de usuario completo.
        $nombreCompleto = trim($this->nombreComprador);
        $partes = explode(' ', $nombreCompleto, 2);
        $nombre = $partes[0];
        $apellido = $partes[1] ?? '.';

        // Se preparan los datos para el servicio de pagos
        $datosComprador = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'identificacion' => $this->identificacionComprador,
            'email' => $this->EmailComprador,
            'telefono' => $this->telefonoComprador,
        ];

        // Se busca el último pago pendiente asociado a esta compra
        $pagoParaProcesar = Pago::where('compra_id', $this->compra->id)->latest()->first();

        if (! $pagoParaProcesar) {
            $this->dispatch('mostrarMensaje', ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'El registro de pago asociado es inválido.']);

            return;
        }

        // --- INICIO DE LA CORRECCIÓN ---
        // Se actualiza el registro de Pago con la selección del usuario ANTES de enviarlo a la pasarela.

        // 1. Buscamos el estado inicial por defecto para el método de pago seleccionado a través de su relación.
        $estadoInicial = $tipoPago->estadosPago()->where('estado_inicial_defecto', true)->first();

        if (! $estadoInicial) {
            $this->dispatch('mostrarMensaje', [
                'tipo' => 'error',
                'titulo' => 'Error de Configuración',
                'mensaje' => "El método de pago <b>'{$tipoPago->nombre}'</b> (ID: {$this->tipoPagoSeleccionado}) no tiene un <b>estado inicial por defecto</b> configurado en la base de datos.<br><small>Por favor, verifica la tabla 'estados_pago'.</small>",
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
        $zonaPagosService = new ZonaPagosService;
        $resultado = $zonaPagosService->iniciarPago($pagoParaProcesar, $datosComprador, $tipoCompra);

        if ($resultado['success']) {
            $pagoParaProcesar->update([
                'payment_url' => $resultado['payment_url'],
                'gateway_response' => $resultado['gateway_response'],
            ]);

            $this->esperandoPagoZonaPagos = true;
            $this->pagoIdEsperando = $pagoParaProcesar->id;

            // Despacha evento al navegador para abrir la pasarela en una nueva pestaña (_blank)
            $this->dispatch('abrirPasarelaZonaPagos', url: $resultado['payment_url']);
        } else {
            // Si el servicio falla, se muestra el error detallado
            $mensajeError = $resultado['message'] ?? 'No se pudo iniciar el proceso de pago.';
            $detalleError = isset($resultado['response']) ? json_encode($resultado['response'], JSON_PRETTY_PRINT) : 'Sin respuesta adicional.';

            Log::error('Error ZonaPagos: '.$mensajeError, ['response' => $resultado['response'] ?? []]);

            $this->dispatch('mostrarMensaje', [
                'tipo' => 'error',
                'titulo' => 'Error de Pasarela',
                'mensaje' => "<b>{$mensajeError}</b><br><br><p class='text-start small'>Detalle técnico:<br><code style='font-size: 10px;'>".e($detalleError).'</code></p>',
            ]);
        }
    }

    /**
     * Consulta automáticamente el estado del pago en tiempo real cuando el usuario
     * está en la pantalla de espera mientras completa la transacción en la otra pestaña.
     */
    public function consultarEstadoPagoAuto()
    {
        if (! $this->pagoIdEsperando) {
            return;
        }

        $pago = Pago::with('estadoPago', 'compra.inscripciones', 'tipoPago')->find($this->pagoIdEsperando);

        if (! $pago) {
            return;
        }

        // 1. Verificar si la BD ya fue actualizada (vía Callback GET o Sonda)
        if ($pago->estadoPago && ! $pago->estadoPago->estado_pendiente) {
            return $this->finalizarYRedirigirAPerfil($pago);
        }

        // 2. Si continúa pendiente en BD, consultar proactivamente a la API de ZonaPagos
        $zonaPagosService = new ZonaPagosService;
        $resultado = $zonaPagosService->verificarPago($pago);

        if ($resultado['success']) {
            $strResPago = $resultado['data']['str_res_pago'] ?? '';
            $datosTransaccion = $zonaPagosService->parsearRespuestaVerificacion($strResPago);
            $codigoEstadoExterno = $datosTransaccion['int_estado_pago'] ?? null;

            if ($codigoEstadoExterno) {
                $nuevoEstado = EstadoPago::where('id_codigo_externo', $codigoEstadoExterno)
                    ->where('tipo_pago_id', $pago->tipo_pago_id)
                    ->first();

                if ($nuevoEstado && ! $nuevoEstado->estado_pendiente) {
                    $referenciaPago = $datosTransaccion['int_n_pago'] ?: $datosTransaccion['int_ped_numero'] ?: null;

                    $pago->update([
                        'estado_pago_id' => $nuevoEstado->id,
                        'referencia_pago' => $referenciaPago,
                        'gateway_response' => $resultado['data'],
                    ]);

                    if ($nuevoEstado->estado_final_inscripcion) {
                        $compra = $pago->compra;
                        if ($compra) {
                            $compra->update(['estado' => 3]);
                            $tipoCompra = strtoupper(trim($datosTransaccion['str_campo1'] ?? ''));
                            if ($tipoCompra === 'ESCUELAS' && $pago->matricula) {
                                $pago->matricula->update(['estado_pago_matricula' => 'pagada']);
                            } elseif ($compra->inscripciones->isNotEmpty()) {
                                $compra->inscripciones()->update(['estado' => true]);
                            }
                        }
                    } elseif ($nuevoEstado->estado_anulado_inscripcion || (! $nuevoEstado->estado_pendiente && ! $nuevoEstado->estado_final_inscripcion)) {
                        // Si el pago fue RECHAZADO, ANULADO o CANCELADO → Liberar matrícula borrador, horario y cupos
                        Matricula::limpiarMatriculasDePagoFallido($pago);
                    }

                    return $this->finalizarYRedirigirAPerfil($pago);
                }
            }
        }
    }

    /**
     * Finaliza la espera y redirige la ventana principal al perfil de la actividad.
     */
    private function finalizarYRedirigirAPerfil(Pago $pago)
    {
        $this->esperandoPagoZonaPagos = false;
        $estado = $pago->estadoPago;

        if ($estado && $estado->estado_final_inscripcion) {
            session()->flash('success', '¡Tu pago se procesó exitosamente! Gracias por tu registro.');
        } else {
            session()->flash('warning', 'El proceso de pago finalizó con el estado: '.($estado->nombre ?? 'Sin estado'));
        }

        return redirect()->route('actividades.perfil', $this->actividad);
    }

    public function render()
    {
        return view('livewire.carrito.checkout');
    }
}
