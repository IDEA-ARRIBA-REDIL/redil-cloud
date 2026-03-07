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
use App\Models\AbonoCategoria; //
use App\Models\ActividadCategoriaMoneda; //
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Mail\InscripcionConfirmacionMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Livewire\Attributes\Validate;

class ProcesarAbono extends Component
{
    // --- PROPIEDADES RECIBIDAS (PROPS) ---
    public User $usuario;
    public Actividad $actividad;
    public Caja $cajaActiva;
    public ActividadCategoria $categoria;
    public Moneda $moneda;

    // ===================================================================
    // ¡PROPIEDADES CORREGIDAS!
    // ===================================================================
    /**
     * El usuario que paga (Padre/Comprador).
     * Recibido desde 'procesar-venta.blade.php'
     */
    public User $comprador;

    /**
     * El usuario que asiste (Hijo/Inscrito).
     * Recibido como 'usuario' desde 'procesar-venta.blade.php'
     */
    public User $inscrito;

    // --- Lógica de Abonos (Adaptada de AbonoCarrito.php) ---
    public ?Compra $compraActual = null;
    public $primeraVez = true; // ¿Es el primer pago de este usuario para esta actividad?
    public $valorTotalCategoria = 0;
    public $totalYaPagado = 0;
    public $valorMinimoAbono = 0; // El mínimo requerido HOY
    public $valorMaximoAbono = 0; // El total restante
    public $mensajeAbono = ''; // Mensaje de ayuda (ej. "Fecha límite: ...")
    public $abonosFinalizados = false;

    // --- Lógica de Campos Adicionales ---
    public $camposAdicionales = [];
    public $camposAdicionalesModelo;

    // --- Lógica de Pagos Divididos ---
    public $tiposPagoDisponibles = [];
    public $pagos = [];
    public $valorRestante = 0; // El restante de *esta* transacción
    public $nuevoPagoValor;
    public $nuevoPagoTipoId;
    public $nuevoPagoVoucher = '';

    /**
     * MÉTODO MOUNT (Constructor)
     * Carga todos los datos iniciales y calcula los montos del abono.
     */
    /**
     * MÉTODO MOUNT (Constructor)
     * ¡CORREGIDO!
     */
    public function mount(User $comprador, User $usuario, Actividad $actividad, Caja $cajaActiva, ActividadCategoria $categoria, Moneda $moneda)
    {
        // 1. Asignar propiedades
        $this->comprador = $comprador;
        $this->inscrito = $usuario; // Asigna el 'usuario' entrante a nuestra propiedad 'inscrito'
        $this->actividad = $actividad;
        $this->cajaActiva = $cajaActiva;
        $this->categoria = $categoria;
        $this->moneda = $moneda;

        // 2. Cargar datos para el pago
        $this->tiposPagoDisponibles = TipoPago::where('habilitado_punto_pago', true)
            ->where('activo', true)
            ->get();
        $this->nuevoPagoTipoId = $this->tiposPagoDisponibles->first()->id ?? null;
        $this->camposAdicionalesModelo = $this->actividad->camposAdicionales; //

        // 3. Buscar si ya existe una Compra (¡USANDO AL COMPRADOR!)
        //
        $this->compraActual = Compra::where('user_id', $this->comprador->id) // <-- ¡CORREGIDO!
            ->where('actividad_id', $this->actividad->id)
            ->first();
        $this->primeraVez = is_null($this->compraActual);

        // 4. Calcular los montos (Esta lógica ya considera los abonos)
        $this->calcularLimitesAbono(); //

        // 5. Establecer el valor restante para el pago dividido
        $this->valorRestante = $this->valorMaximoAbono;
    }

    /**
     * ¡NUEVO MÉTODO HELPER!
     * Calcula los montos de abono (lógica de AbonoCarrito.php).
     */
    public function calcularLimitesAbono()
    {
        $fechaActual = Carbon::now();
        $categoriaId = $this->categoria->id;
        $monedaId = $this->moneda->id;

        // 1. Obtener el VALOR TOTAL de la categoría
        //
        $this->valorTotalCategoria = ActividadCategoriaMoneda::where('actividad_categoria_id', $categoriaId)
            ->where('moneda_id', $monedaId)
            ->value('valor');

        // 2. Obtener el TOTAL YA PAGADO por el usuario
        $this->totalYaPagado = 0;
        if (!$this->primeraVez) {
            //
            $this->totalYaPagado = Pago::where('compra_id', $this->compraActual->id)
                ->where('moneda_id', $monedaId)
                // (OPCIONAL: filtrar por estado de pago aprobado)
                ->sum('valor');
        }

        // 3. Calcular el VALOR MÍNIMO REQUERIDO HOY
        //
        $valorMinimoRequerido = 0;
        $fechaLimiteAbonoActual = null;
        $abonosDefinidos = AbonoCategoria::where('actividad_categoria_id', $categoriaId)
            ->where('moneda_id', $monedaId)
            ->with('abono') // Carga la relación para ver las fechas
            ->get();

        foreach ($abonosDefinidos as $abonoCat) {
            if ($fechaActual >= $abonoCat->abono->fecha_inicio) {
                // Si la fecha de inicio del abono ya pasó, se suma
                $valorMinimoRequerido += $abonoCat->valor;
                $fechaLimiteAbonoActual = $abonoCat->abono->fecha_fin;
            }
        }

        // 4. Calcular los valores finales
        // El mínimo a pagar hoy es lo requerido MENOS lo que ya pagó
        $this->valorMinimoAbono = max(0, $valorMinimoRequerido - $this->totalYaPagado);

        // El máximo a pagar es el total MENOS lo que ya pagó
        $this->valorMaximoAbono = $this->valorTotalCategoria - $this->totalYaPagado;

        // 5. Establecer el valor sugerido en el input
        $this->nuevoPagoValor = $this->valorMinimoAbono > 0 ? $this->valorMinimoAbono : null;

        // 6. Generar mensaje de ayuda
        if ($this->valorMaximoAbono <= 0) {
            $this->abonosFinalizados = true;
            $this->mensajeAbono = '¡Felicitaciones! Ya has completado todos los pagos para esta actividad.';
        } elseif ($fechaLimiteAbonoActual) {
            $this->mensajeAbono = 'Fecha límite para este abono: ' . Carbon::parse($fechaLimiteAbonoActual)->format('d/m/Y');
        }
    }

    // ===================================================================
    // LÓGICA DE PAGOS DIVIDIDOS (Idéntica a ProcesarMatriculaEscuela)
    //
    // ===================================================================

    public function anadirPago()
    {
        $valor = floatval($this->nuevoPagoValor);

        if ($valor <= 0) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'El valor debe ser mayor a cero.');
            return;
        }

        // --- NUEVA RESTRICCIÓN: VALORES CERRADOS ---
        if ($this->actividad->pagos_abonos_con_valores_cerrados) {
            if ($valor != $this->valorMinimoAbono) {
                $this->dispatch('notificacion', 
                    tipo: 'error', 
                    mensaje: 'Esta actividad solo permite pagos con el valor exacto del abono ($' . number_format($this->valorMinimoAbono) . ').'
                );
                return;
            }
        }

        // ¡CAMBIO! Validamos contra el máximo posible (total restante)
        if ($valor > $this->valorMaximoAbono) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'El valor no puede ser mayor que el total restante (' . $this->valorMaximoAbono . ').');
            return;
        }
        if ($valor > $this->valorRestante) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'El valor no puede ser mayor que el restante de esta transacción.');
            return;
        }

        $tipoPago = $this->tiposPagoDisponibles->find($this->nuevoPagoTipoId);

        if ($tipoPago && $tipoPago->codigo_datafono && empty($this->nuevoPagoVoucher)) {
            $this->addError('nuevoPagoVoucher', 'El código es obligatorio.');
            return;
        }
        $this->resetErrorBag('nuevoPagoVoucher');

        $this->pagos[] = [
            'tipo_pago_id' => $this->nuevoPagoTipoId,
            'nombre' => $tipoPago->nombre,
            'valor' => $valor,
            'codigo_vaucher' => ($tipoPago && $tipoPago->codigo_datafono) ? $this->nuevoPagoVoucher : null,
        ];

        $this->actualizarRestante();
        $this->nuevoPagoValor = '';
        $this->nuevoPagoVoucher = '';
    }

    public function quitarPago($index)
    {
        unset($this->pagos[$index]);
        $this->pagos = array_values($this->pagos);
        $this->actualizarRestante();
    }

    private function actualizarRestante()
    {
        $totalPagadoEstaTransaccion = collect($this->pagos)->sum('valor');
        // El restante es el MÁXIMO (total) menos lo que está pagando AHORA
        $this->valorRestante = $this->valorMaximoAbono - $totalPagadoEstaTransaccion;
    }

    // ===================================================================
    // ¡LÓGICA FINAL DE LA TRANSACCIÓN!
    // ===================================================================

    public function confirmarAbono()
    {
        // 1. Validar Campos Adicionales (solo si es la primera vez)
        if ($this->primeraVez) {
            $this->validarCamposAdicionales();
        }

        // 2. Validar Pago
        $totalPagadoEstaTransaccion = collect($this->pagos)->sum('valor');

        if ($totalPagadoEstaTransaccion <= 0) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Debe añadir al menos un pago.');
            return;
        }
        // Validamos que el pago de HOY cumpla el MÍNIMO requerido
        if ($totalPagadoEstaTransaccion < $this->valorMinimoAbono) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'El pago debe ser de al menos $' . number_format($this->valorMinimoAbono));
            return;
        }

        $registroCajaId = null; // Placeholder

        // 2.1 Validar Límite de Dinero en Caja (Nuevo Requisito)
        foreach ($this->pagos as $pagoInfo) {
            $tipoPago = TipoPago::find($pagoInfo['tipo_pago_id']);
            
            if ($tipoPago && $tipoPago->tiene_limite_dinero_acumulado) {
                // Verificar si la caja tiene un límite configurado
                if ($this->cajaActiva->limite_dinero_acumulado > 0) {
                    
                    // CAMBIO: Validamos si YA se alcanzó el límite, no si se va a superar.
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

        // 3. INICIO DE LA TRANSACCIÓN
        DB::beginTransaction();
        try {
            $categoria = ActividadCategoria::where('id', $this->categoria->id)
                ->lockForUpdate()
                ->first();

            // 4. Crear/Obtener Compra e Inscripción (SOLO SI ES PRIMERA VEZ)
            $compra = $this->compraActual; // Nulo si es primera vez
            $inscripcion = null;

            if ($this->primeraVez) {
                // Validar Aforo (solo la primera vez)
                if ($categoria->aforo > 0 && $categoria->aforo_ocupado >= $categoria->aforo) {
                    $this->dispatch('notificacion', tipo: 'error', titulo: '¡Límite Superado!', mensaje: 'Los cupos se agotaron.');
                    DB::rollBack();
                    return;
                }

                // ===============================================
                // ¡INICIO DE LA CORRECCIÓN CRÍTICA!
                // ===============================================

                // Crear Compra (Usa solo $this->comprador)
                $compra = Compra::create([
                    'user_id' => $this->comprador->id,
                    'actividad_id' => $this->actividad->id,
                    'moneda_id' => $this->moneda->id,
                    'fecha' => now(),
                    'valor' => $this->valorTotalCategoria, // El valor total de la deuda
                    'estado' => 2, // 2 = Pendiente
                    'nombre_completo_comprador' => $this->comprador->nombre(3),
                    'identificacion_comprador' => $this->comprador->identificacion,
                    'telefono_comprador' => $this->comprador->telefono_movil,
                    'email_comprador' => $this->comprador->email,
                    'metodo_pago_id' => 0
                ]);

                // Crear Inscripción (Usa solo $this->inscrito)
                $inscripcion = Inscripcion::create([
                    'user_id' => $this->inscrito->id,
                    'actividad_categoria_id' => $this->categoria->id,
                    'compra_id' => $compra->id,
                    'fecha' => now(),
                    'estado' => $this->actividad->estado_inscripcion_defecto,
                    'json_campos_adicionales' => json_encode($this->camposAdicionales),
                    'nombre_inscrito' => $this->inscrito->nombre(4),
                ]);

                // ===============================================
                // ¡FIN DE LA CORRECCIÓN CRÍTICA!
                // ===============================================

                // Incrementar Aforo (solo la primera vez)
                // Incrementar Aforo (solo la primera vez)
                $categoria->aforo_ocupado = ($categoria->aforo_ocupado ?? 0) + 1;
                $categoria->save();
            }

            // 5. Crear los registros de Pagos (divididos)
            foreach ($this->pagos as $pagoInfo) {
                Pago::create([
                    'compra_id' => $compra->id,
                    'tipo_pago_id' => $pagoInfo['tipo_pago_id'],
                    'estado_pago_id' => 3, // Aprobado
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

            // 6. Actualizar estado de la Compra si se completó el pago
            $nuevoTotalPagado = $this->totalYaPagado + $totalPagadoEstaTransaccion;
            if ($nuevoTotalPagado >= $this->valorTotalCategoria) {
                $compra->update(['estado' => 1]); // 1 = Pagada
            }

            // 7. ¡ÉXITO!
            DB::commit();

            // 8. Enviar correo (solo la primera vez)
            if ($this->primeraVez && $inscripcion) {
                $this->_enviarCorreoDeConfirmacion($inscripcion);
            }

            // 9. Notificar y Redirigir
            $this->dispatch('notificacion', tipo: 'success', mensaje: '¡Abono procesado con éxito!');
            return redirect()->route('taquilla.compraFinalizada', $compra);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar abono en taquilla: ' . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Error al procesar el abono: ' . $e->getMessage());
        }
    }

    /**
     * ¡NUEVO HELPER!
     * Valida los campos adicionales requeridos.
     */
    private function validarCamposAdicionales()
    {
        $reglas = [];
        $mensajes = [];
        foreach ($this->camposAdicionalesModelo as $campo) {
            if ($campo->obligatorio) { //
                $reglas['camposAdicionales.' . $campo->id] = 'required';
                $mensajes['camposAdicionales.' . $campo->id . '.required'] = "El campo '{$campo->nombre}' es obligatorio.";
            }
        }
        if (!empty($reglas)) {
            $this->validate($reglas, $mensajes);
        }
    }

    /**
     * ¡NUEVO HELPER!
     * Envía el correo de confirmación.
     */
    private function _enviarCorreoDeConfirmacion(Inscripcion $inscripcion)
    {
        try {
            $inscripcion->load('categoriaActividad.actividad', 'compra.user', 'user');
            $actividad = $inscripcion->categoriaActividad->actividad;
            // El email del INSCRITO si existe, si no, el del COMPRADOR
            $emailDestinatario = $this->inscrito->email ?? $this->comprador->email;

            if (filter_var($emailDestinatario, FILTER_VALIDATE_EMAIL)) {
                Mail::to($emailDestinatario)->send(new InscripcionConfirmacionMail($inscripcion, $actividad));
            }
        } catch (Exception $e) {
            Log::error("Fallo al enviar correo para inscripción #{$inscripcion->id}: " . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'warning', mensaje: 'Abono registrado, pero falló el envío de correo.');
        }
    }

    public function render()
    {
        return view('livewire.taquilla.procesar-abono');
    }
}
