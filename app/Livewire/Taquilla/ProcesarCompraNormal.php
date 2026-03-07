<?php

namespace App\Livewire\Taquilla;

use Livewire\Component;
use App\Models\User;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Caja;
use App\Models\Moneda;
use App\Models\TipoPago;
use App\Models\Compra;
use App\Models\Pago;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Mail\InscripcionConfirmacionMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Livewire\Attributes\Validate; // Para validación

class ProcesarCompraNormal extends Component
{
    // --- PROPIEDADES RECIBIDAS (PROPS) ---
    // Estas propiedades son pasadas desde la vista anfitriona 'procesar-venta.blade.php'

    /**
     * El usuario que paga (Padre/Comprador).
     * @var User
     */
    public User $comprador;

    /**
     * El usuario que asiste (Hijo/Inscrito).
     * @var User
     */
    public User $inscrito;

    public Actividad $actividad;
    public Caja $cajaActiva;
    public ActividadCategoria $categoria;
    public Moneda $moneda;

    // --- DATOS DEL FORMULARIO ---
    public $precioTotal = 0;

    // 1. Lógica de Campos Adicionales
    //
    public $camposAdicionales = []; // Array para wire:model
    public $camposAdicionalesModelo; // Para cargar la colección

    // 2. Lógica de Pagos Divididos
    //
    public $tiposPagoDisponibles = [];
    public $pagos = []; // Array de pagos añadidos
    public $valorRestante = 0;

    // Propiedades para añadir un nuevo pago
    public $nuevoPagoValor;
    public $nuevoPagoTipoId;
    public $nuevoPagoVoucher = '';

    // --- NUEVAS PROPIEDADES PARA COMPRAS MÚLTIPLES ---
    public int $cantidad = 1;
    public float $precioUnitario = 0;

    /**
     * MÉTODO MOUNT (Constructor)
     * Carga todos los datos iniciales.
     */
    public function mount()
    {
        // 1. Calcular precio y cargar métodos de pago
        $precioPivot = $this->categoria->monedas()->where('moneda_id', $this->moneda->id)->first();
        $this->precioUnitario = $precioPivot->pivot->valor ?? 0;
        $this->precioTotal = $this->precioUnitario * $this->cantidad;
        $this->valorRestante = $this->precioTotal;

        $this->tiposPagoDisponibles = TipoPago::where('habilitado_punto_pago', true)
            ->where('activo', true)
            ->get();
        $this->nuevoPagoTipoId = $this->tiposPagoDisponibles->first()->id ?? null;

        // 2. Cargar lógica de Campos Adicionales
        $this->camposAdicionalesModelo = $this->actividad->camposAdicionales;
    }

    public function updatedCantidad($value)
    {
        // Validar límites básicos antes de recalcular
        if ($value < 1) $this->cantidad = 1;
        
        // Validar límite por categoría
        if ($this->cantidad > ($this->categoria->limite_compras ?? 1)) {
            $this->cantidad = $this->categoria->limite_compras ?? 1;
            $this->dispatch('notificacion', tipo: 'warning', mensaje: 'La cantidad máxima permitida para esta categoría es ' . $this->cantidad);
        }

        // Recalcular montos
        $this->precioTotal = $this->precioUnitario * $this->cantidad;
        $this->actualizarRestante();
    }

    public function incrementar()
    {
        $this->cantidad++;
        $this->updatedCantidad($this->cantidad);
    }

    public function decrementar()
    {
        $this->cantidad--;
        $this->updatedCantidad($this->cantidad);
    }

    // ===================================================================
    // LÓGICA DE PAGOS DIVIDIDOS (Con Voucher)
    //
    // ===================================================================

    /**
     * Añade un pago al array de pagos divididos.
     */
    public function anadirPago()
    {
        $valor = floatval($this->nuevoPagoValor);

        // Validaciones de valor
        if ($valor <= 0) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'El valor debe ser mayor a cero.');
            return;
        }
        if ($valor > $this->valorRestante) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'El valor no puede ser mayor que el restante.');
            return;
        }

        $tipoPago = $this->tiposPagoDisponibles->find($this->nuevoPagoTipoId);

        // Validación de Voucher
        if ($tipoPago && $tipoPago->codigo_datafono && empty($this->nuevoPagoVoucher)) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: "El método de pago '{$tipoPago->nombre}' requiere un código de voucher.");
            $this->addError('nuevoPagoVoucher', 'El código es obligatorio para este método de pago.');
            return;
        }
        $this->resetErrorBag('nuevoPagoVoucher');

        // Añadimos el pago al array
        $this->pagos[] = [
            'tipo_pago_id' => $this->nuevoPagoTipoId,
            'nombre' => $tipoPago->nombre,
            'valor' => $valor,
            'codigo_vaucher' => ($tipoPago && $tipoPago->codigo_datafono) ? $this->nuevoPagoVoucher : null,
        ];

        // Recalculamos y reseteamos
        $this->actualizarRestante();
        $this->nuevoPagoValor = '';
        $this->nuevoPagoVoucher = '';
    }

    /**
     * Quita un pago del array de pagos divididos.
     */
    public function quitarPago($index)
    {
        unset($this->pagos[$index]);
        $this->pagos = array_values($this->pagos); // Re-indexar
        $this->actualizarRestante();
    }

    /**
     * Recalcula el valor restante.
     */
    private function actualizarRestante()
    {
        $totalPagado = collect($this->pagos)->sum('valor');
        $this->valorRestante = $this->precioTotal - $totalPagado;
    }

    // ===================================================================
    // ¡LÓGICA FINAL DE LA TRANSACCIÓN!
    // ===================================================================

    public function confirmarCompra()
    {
        // 1. Validar Campos Adicionales
        $this->validarCamposAdicionales();

        // 2. Validar Pago
        if ($this->precioTotal > 0 && $this->valorRestante > 0) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Aún falta dinero por pagar.');
            return;
        }
        // Si no es gratis, debe tener al menos un pago
        if ($this->precioTotal > 0 && count($this->pagos) == 0) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Debe añadir al menos un método de pago.');
            return;
        }

        // 2.1 Validar Límite de Dinero en Caja (Nuevo Requisito)
        foreach ($this->pagos as $pagoInfo) {
            $tipoPago = TipoPago::find($pagoInfo['tipo_pago_id']);
            
            if ($tipoPago && $tipoPago->tiene_limite_dinero_acumulado) {
                // Verificar si la caja tiene un límite configurado
                if ($this->cajaActiva->limite_dinero_acumulado > 0) {
                    
                    // CAMBIO: Solo bloqueamos si el dinero ACTUAL YA es mayor o igual al límite.
                    // Si está en 80.000 y el límite es 100.000, dejamos pasar los 50.000 (quedará en 130.000).
                    // La próxima vez, como estará en 130.000, entrará en este IF y bloqueará.
                    if ($this->cajaActiva->dinero_acumulado >= $this->cajaActiva->limite_dinero_acumulado) {
                        $this->dispatch('notificacion', 
                            tipo: 'error', 
                            titulo: '¡Caja Llena!',
                            mensaje: "La caja ya ha alcanzado su tope de dinero para {$tipoPago->nombre}. Debe solicitar una recolección antes de recibir más pagos de este tipo."
                        );
                        return;
                    }
                }
            }
        }

        // (Placeholder para la Sugerencia 1 - Registro de Sesión de Caja)
        $registroCajaId = null;

        // 3. INICIO DE LA TRANSACCIÓN
        DB::beginTransaction();
        try {
            // 4. (Sugerencia 2) Validar y bloquear cupos
            //
            $categoria = ActividadCategoria::where('id', $this->categoria->id)
                ->lockForUpdate()
                ->first();

            if ($categoria->aforo > 0 && $categoria->aforo_ocupado >= $categoria->aforo) {
                $this->dispatch('notificacion', tipo: 'error', mensaje: 'Los cupos se agotaron.');
                DB::rollBack();
                return;
            }

            // 5. Crear la Compra (a nombre del COMPRADOR)
            $compra = Compra::create([
                'user_id' => $this->comprador->id,
                'actividad_id' => $this->actividad->id,
                'moneda_id' => $this->moneda->id,
                'fecha' => now(),
                'valor' => $this->precioTotal,
                'estado' => 1, // 1 = Pagada
                'nombre_completo_comprador' => $this->comprador->nombre(3),
                'identificacion_comprador' => $this->comprador->identificacion,
                'telefono_comprador' => $this->comprador->telefono_movil,
                'email_comprador' => $this->comprador->email,
                'metodo_pago_id' => 0 
            ]);

            // 6. Determinar número de registros de inscripción a crear
            $numeroInscripciones = 1; // Por defecto 1 (si es unica_inscripcion)
            
            if ($this->actividad->tipo->multiples_inscripciones) {
                $numeroInscripciones = $this->cantidad;
            }

            $inscripionPrincipal = null;

            // 7. Crear las Inscripciones (a nombre del INSCRITO)
            for ($i = 0; $i < $numeroInscripciones; $i++) {
                $ins = Inscripcion::create([
                    'user_id' => $this->inscrito->id,
                    'actividad_categoria_id' => $this->categoria->id,
                    'compra_id' => $compra->id,
                    'fecha' => now(),
                    'estado' => $this->actividad->estado_inscripcion_defecto,
                    'nombre_inscrito' => $this->inscrito->nombre(4),
                    'json_campos_adicionales' => json_encode($this->camposAdicionales),
                ]);

                if ($i === 0) $inscripionPrincipal = $ins;
            }

            // 7. Crear los registros de Pagos (divididos)
            //
            foreach ($this->pagos as $pagoInfo) {
                Pago::create([
                    'compra_id' => $compra->id,
                    'tipo_pago_id' => $pagoInfo['tipo_pago_id'],
                    'estado_pago_id' => 3, // Asumimos 3 = Aprobado
                    'moneda_id' => $this->moneda->id,
                    'valor' => $pagoInfo['valor'],
                    'fecha' => now(),
                    'actividad_categoria_id' => $this->categoria->id,
                    'registro_caja_id' => $this->cajaActiva->id,
                    'codigo_vaucher' => $pagoInfo['codigo_vaucher'] ?? null,
                ]);

                // Actualizar acumulado en caja si aplica
                $tipoPago = TipoPago::find($pagoInfo['tipo_pago_id']);
                if ($tipoPago && $tipoPago->tiene_limite_dinero_acumulado) {
                    $this->cajaActiva->refresh();
                    $this->cajaActiva->dinero_acumulado = ($this->cajaActiva->dinero_acumulado ?? 0) + $pagoInfo['valor'];
                    $this->cajaActiva->save();
                }
            }

            // 8. Actualizar Cupos (Descontamos TODA la cantidad comprada)
            $categoria->aforo_ocupado = ($categoria->aforo_ocupado ?? 0) + $this->cantidad;
            $categoria->save();

            // 9. ¡ÉXITO!
            DB::commit();

            // 10. Enviar correo de confirmación (Al menos de la inscripción principal)
            if ($inscripionPrincipal) {
                $this->_enviarCorreoDeConfirmacion($inscripionPrincipal);
            }

            // 11. Notificar al cajero
            $this->dispatch('notificacion', tipo: 'success', mensaje: '¡Compra procesada con éxito!');

            // 12. Redirigir vista de exito
            return redirect()->route('taquilla.compraFinalizada', $compra);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar compra en taquilla: ' . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Error al procesar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Valida los campos adicionales requeridos.
     *
     */
    private function validarCamposAdicionales()
    {
        $reglas = [];
        $mensajes = [];

        foreach ($this->camposAdicionalesModelo as $campo) {
            if ($campo->obligatorio) {
                $reglas['camposAdicionales.' . $campo->id] = 'required';
                $mensajes['camposAdicionales.' . $campo->id . '.required'] = "El campo '{$campo->nombre}' es obligatorio.";
            }
        }

        if (!empty($reglas)) {
            $this->validate($reglas, $mensajes);
        }
    }

    /**
     * Envía el correo de confirmación.
     *
     */
    private function _enviarCorreoDeConfirmacion(Inscripcion $inscripcion)
    {
        try {
            $inscripcion->load('categoriaActividad.actividad', 'compra', 'user');
            $actividad = $inscripcion->categoriaActividad->actividad;
            $emailDestinatario = $inscripcion->user->email ?? $this->comprador->email;
            if (filter_var($emailDestinatario, FILTER_VALIDATE_EMAIL)) {
                Mail::to($emailDestinatario)->send(new InscripcionConfirmacionMail($inscripcion, $actividad));
            }
        } catch (Exception $e) {
            Log::error("Fallo al enviar correo para inscripción #{$inscripcion->id}: " . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'warning', mensaje: 'Compra registrada, pero falló el envío de correo.');
        }
    }

    /**
     * Renderiza la vista.
     */
    public function render()
    {
        return view('livewire.taquilla.procesar-compra-normal');
    }
}
