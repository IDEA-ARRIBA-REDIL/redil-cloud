<?php

namespace App\Livewire\Taquilla;

use Livewire\Component;
use App\Models\User;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Caja;
use App\Models\Sede; // Importación agregada
use App\Models\Moneda;
use App\Models\TipoPago;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Compra;
use App\Models\Pago;
use App\Models\Matricula;
use App\Models\Inscripcion;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;
use App\Models\ActividadCarritoCompra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Mail\InscripcionConfirmacionMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Livewire\Attributes\Validate; // Para validación

class ProcesarMatriculaEscuela extends Component
{
    // --- PROPIEDADES RECIBIDAS (PROPS) ---
    /**
     * El usuario que paga (Padre/Comprador).
     * Recibido desde 'procesar-venta.blade.php'
     *
     */
    public User $comprador;

    /**
     * El usuario que asiste (Hijo/Inscrito).
     * RENOMBRADO: 'usuario' ahora es 'inscrito' para mayor claridad.
     *
     */
    public User $inscrito;

    public $prueba;
    public Actividad $actividad;
    public Caja $cajaActiva;
    public ActividadCategoria $categoria; // La materia/categoría seleccionada
    public Moneda $moneda;

    // --- DATOS DEL "FORMULARIO" ---
    public $precioTotal = 0;

    // 1. Lógica de Sede/Horario (de EscuelasCarrito)
    public $sedes = [];
    public $tiposAula = [];
    public $horarios = [];

    #[Validate('required', message: 'Debes seleccionar una sede.')]
    public $sedeSeleccionada = null;
    public $tipoAulaSeleccionado = null;
    #[Validate('required', message: 'Debes seleccionar un horario.')]
    public $horarioSeleccionado = null;

    // 2. Lógica de Campos Adicionales (de la maqueta)
    public $camposAdicionales = []; // Array para wire:model
    public $camposAdicionalesModelo; // Para cargar la colección

    // 3. Lógica de Pagos Divididos (de la maqueta)
    public $tiposPagoDisponibles = [];
    public $pagos = []; // Array de pagos añadidos (ej: [['tipo_pago_id' => 1, 'valor' => 50000], ...])
    public $valorRestante = 0;

    // Propiedades para añadir un nuevo pago
    public $nuevoPagoValor;
    public $nuevoPagoTipoId;
    public $nuevoPagoVoucher = '';

    /**
     * Almacena la lista de sedes para el envío de material.
     */
    public $sedesDelPeriodo = []; // El nombre se mantiene por tu solicitud

    /**
     * Almacena el ID de la sede seleccionada para el material.
     * La validación coincide con tu esquema de BD (nullable)
     * pero la marcaremos como 'required' en la vista para este flujo.
     */
    #[Validate('required', message: 'Debes seleccionar una sede para el material.')]
    public $materialSedeId = null;
    // ===============================


    /**
     * MÉTODO MOUNT (Constructor)
     * ¡MODIFICADO para cargar campos adicionales!

     * Livewire 3 maneja esto automáticamente si los nombres coinciden.
     * Aquí renombramos 'usuario' (que es el inscrito) a 'inscrito'.
     */
    public function mount(User $comprador, User $usuario, Actividad $actividad, Caja $cajaActiva, ActividadCategoria $categoria, Moneda $moneda)
    {
        $this->comprador = $comprador;
        $this->inscrito = $usuario; // Asigna el 'usuario' entrante a la propiedad 'inscrito'
        $this->actividad = $actividad;
        $this->cajaActiva = $cajaActiva;
        $this->categoria = $categoria;
        $this->moneda = $moneda;

        // --- Lógica de Mount (SIN CAMBIOS) ---
        $precioPivot = $this->categoria->monedas()->where('moneda_id', $this->moneda->id)->first();
        $this->precioTotal = $precioPivot->pivot->valor ?? 0;
        $this->valorRestante = $this->precioTotal;

        $this->tiposPagoDisponibles = TipoPago::where('habilitado_punto_pago', true)->get();
        $this->nuevoPagoTipoId = $this->tiposPagoDisponibles->first()->id ?? null;

        $materiaPeriodoId = $this->categoria->materia_periodo_id;
        $this->sedes = HorarioMateriaPeriodo::getSedesForMateriaPeriodo($materiaPeriodoId)
            ->map(fn($sede) => ['id' => $sede->id, 'nombre' => $sede->nombre])
            ->all();

        $this->camposAdicionalesModelo = $this->actividad->camposAdicionales;

        $periodo = $this->categoria->materiaPeriodo->periodo;
        if ($periodo && method_exists($periodo, 'sedes')) {
            $this->sedesDelPeriodo = $periodo->sedes()->get();
        } else {
            Log::warning('Modelo Periodo (ID: ' . $periodo->id . ') no tiene relación "sedes". Cargando todas las sedes.');
            $this->sedesDelPeriodo = Sede::orderBy('nombre')->get();
        }
        $this->materialSedeId = $this->sedesDelPeriodo->firstWhere('id', 2)?->id ?? $this->sedesDelPeriodo->first()?->id;
    }
    // ===================================================================
    // LÓGICA DE DROPDOWNS DEPENDIENTES (Sede -> TipoAula -> Horario)
    // Extraída de EscuelasCarrito.php
    // ===================================================================

    public function updatedSedeSeleccionada($sedeId)
    {
        $this->reset(['tipoAulaSeleccionado', 'horarioSeleccionado', 'tiposAula', 'horarios']);
        if (!$sedeId) {
            $this->tiposAula = [];
            return;
        }

        // Carga los tipos de aula (Virtual, Presencial)
        $this->tiposAula = HorarioMateriaPeriodo::query()
            ->where('materia_periodo_id', $this->categoria->materia_periodo_id)
            ->whereHas('horarioBase.aula', fn($q) => $q->where('sede_id', $sedeId))
            ->with('horarioBase.aula.tipo')
            ->get()
            ->pluck('horarioBase.aula.tipo')
            ->unique('id')
            ->map(fn($tipo) => ['id' => $tipo->id, 'nombre' => $tipo->nombre])
            ->values()->all();
    }

    public function updatedTipoAulaSeleccionado($tipoId)
    {
        $this->reset('horarioSeleccionado');

        // Si el tipoId se resetea (ej. al cambiar de sede), limpiamos los horarios
        if (empty($tipoId)) {
            $this->horarios = [];
            return;
        }

        // Carga los horarios finales disponibles (tu misma lógica)
        //



        $this->horarios = HorarioMateriaPeriodo::query()
            ->where('materia_periodo_id', $this->categoria->materia_periodo_id)
            ->where('cupos_disponibles', '>', 0)
            ->whereHas('horarioBase.aula', function ($q) use ($tipoId) {
                $q->where('sede_id', $this->sedeSeleccionada)
                    ->where('tipo_aula_id', $tipoId);
            })
            ->with(['horarioBase.aula', 'maestros.user'])
            ->get()
            ->map(fn($h) => $this->formatHorarioForAlpine($h))
            ->all();
    }

    // Helper para formatear el texto del horario
    protected function formatHorarioForAlpine($horario): array
    {
        $dias = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];
        $dia = $dias[$horario->horarioBase->dia] ?? 'N/D';
        $ini = Carbon::parse($horario->horarioBase->hora_inicio)->format('h:i A');
        $fin = Carbon::parse($horario->horarioBase->hora_fin)->format('h:i A');
        $aula = $horario->horarioBase->aula->nombre ?? 'N/D';
        $maestro = $horario->maestros->first()?->user->nombre(2) ?? 'Por asignar';
        $label = "{$dia} | {$ini} - {$fin} | Aula: {$aula} | Maestro: {$maestro} | Cupos: {$horario->cupos_disponibles}";
        return ['id' => $horario->id, 'label' => $label];
    }

    // ===================================================================
    // LÓGICA DE PAGOS DIVIDIDOS (De la maqueta)
    // ===================================================================

    /**
     * Añade un pago al array de pagos divididos.
     * ¡MODIFICADO para incluir el código de voucher!
     */
    public function anadirPago()
    {
        $valor = floatval($this->nuevoPagoValor);

        // Validaciones de valor (sin cambios)
        if ($valor <= 0) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'El valor debe ser mayor a cero.');
            return;
        }
        if ($valor > $this->valorRestante) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'El valor no puede ser mayor que el restante.');
            return;
        }

        // ===================================================================
        // ¡INICIO DE LA NUEVA LÓGICA DE VOUCHER!
        // ===================================================================

        // 1. Buscamos el objeto TipoPago para chequear su propiedad 'codigo_datafono'
        $tipoPago = $this->tiposPagoDisponibles->find($this->nuevoPagoTipoId);

        // 2. Validamos si el voucher es requerido y está vacío
        //
        if ($tipoPago && $tipoPago->codigo_datafono && empty($this->nuevoPagoVoucher)) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: "El método de pago '{$tipoPago->nombre}' requiere un código de voucher.");
            // Usamos 'addError' para mostrar el error debajo del campo
            $this->addError('nuevoPagoVoucher', 'El código es obligatorio para este método de pago.');
            return;
        }
        $this->resetErrorBag('nuevoPagoVoucher'); // Limpiamos el error si pasa

        // 3. Añadimos el pago al array, incluyendo el voucher (sea null o un valor)
        $this->pagos[] = [
            'tipo_pago_id' => $this->nuevoPagoTipoId,
            'nombre' => $tipoPago->nombre,
            'valor' => $valor,
            'codigo_vaucher' => ($tipoPago && $tipoPago->codigo_datafono) ? $this->nuevoPagoVoucher : null,
        ];

        // ===================================================================
        // ¡FIN DE LA NUEVA LÓGICA DE VOUCHER!
        // ===================================================================

        // Recalculamos y reseteamos los inputs
        $this->actualizarRestante();
        $this->nuevoPagoValor = '';
        $this->nuevoPagoVoucher = ''; // ¡Importante resetear el voucher también!
    }

    public function quitarPago($index)
    {
        unset($this->pagos[$index]);
        $this->pagos = array_values($this->pagos);
        $this->actualizarRestante();
    }

    private function actualizarRestante()
    {
        $totalPagado = collect($this->pagos)->sum('valor');
        $this->valorRestante = $this->precioTotal - $totalPagado;
    }

    /**
     * ¡LÓGICA FINAL DE LA TRANSACCIÓN (CORREGIDA)!
     * Ahora usa $this->comprador y $this->inscrito
     */
    public function confirmarMatricula()
    {
        // 1. Validaciones
        $this->validate();
        $this->validarCamposAdicionales();

        // 2. Validación de Pago
        if ($this->precioTotal > 0 && $this->valorRestante > 0) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Aún falta dinero por pagar.');
            return;
        }
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

                    // CAMBIO: Validamos si YA se alcanzó el límite.
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

        $registroCajaId = null; // Placeholder

        // 3. INICIO DE LA TRANSACCIÓN
        DB::beginTransaction();
        try {
            $horario = HorarioMateriaPeriodo::with('materiaPeriodo.periodo')->findOrFail($this->horarioSeleccionado);
            $periodo = $horario->materiaPeriodo->periodo;
            $horario->lockForUpdate();
            if ($horario->cupos_disponibles <= 0) {
                DB::rollBack();
                $this->dispatch('notificacion', tipo: 'error', mensaje: 'Lo sentimos, los cupos para este horario se han agotado.');
                return;
            }

            // 5. Crear la Compra (¡A nombre del COMPRADOR!)
            $compra = Compra::create([
                'user_id' => $this->comprador->id, // <-- CORREGIDO
                'actividad_id' => $this->actividad->id,
                'moneda_id' => $this->moneda->id,
                'fecha' => now(),
                'valor' => $this->precioTotal,
                'estado' => 1,
                'nombre_completo_comprador' => $this->comprador->nombre(3),
                'identificacion_comprador' => $this->comprador->identificacion,
                'telefono_comprador' => $this->comprador->telefono_movil,
                'email_comprador' => $this->comprador->email,
                'metodo_pago_id' => 0
            ]);

            // 6. Crear la Inscripción (¡A nombre del INSCRITO!)
            $inscripcion = Inscripcion::create([
                'user_id' => $this->inscrito->id, // <-- CORREGIDO
                'actividad_categoria_id' => $this->categoria->id,
                'compra_id' => $compra->id,
                'fecha' => now(),
                'estado' => $this->actividad->estado_inscripcion_defecto,
                'nombre_inscrito' => $this->inscrito->nombre(4),
                'json_campos_adicionales' => json_encode($this->camposAdicionales),
            ]);

            // 7. Crear la Matrícula (¡A nombre del INSCRITO!)
            $matricula = Matricula::create([
                'user_id' => $this->inscrito->id, // <-- CORREGIDO
                'periodo_id' => $periodo->id,
                'horario_materia_periodo_id' => $horario->id,
                'referencia_pago' => $compra->id,
                'estado_pago_id' => 3, // 3 = Pagado
                'fecha_matricula' => now()->toDateString(),
                'valor_a_pagar' => $this->precioTotal,
                'valor_pagado' => $this->precioTotal,
                'fecha_pago' => now(),
                'tipo_pago_id' => $this->pagos[0]['tipo_pago_id'] ?? null,
                'sede_id' => $this->sedeSeleccionada,
                'material_sede_id' => $this->materialSedeId, //
                'escuela_id' => $periodo->escuela_id,
            ]);

            // 8. Crear el Estado Académico (¡A nombre del INSCRITO!)
            EstadoAcademico::create([
                'user_id' => $this->inscrito->id, // <-- CORREGIDO
                'horario_materia_periodo_id' => $horario->id,
                'matricula_id' => $matricula->id,
                'periodo_id' => $periodo->id,
                'estado_aprobacion' => 'cursando',
            ]);

            // 9. Crear los registros de Pagos (divididos)
            foreach ($this->pagos as $pagoInfo) {
                Pago::create([
                    'compra_id' => $compra->id,
                    'tipo_pago_id' => $pagoInfo['tipo_pago_id'],
                    'estado_pago_id' => 3,
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


            // 10. Actualizar Cupos
            $horario->decrement('cupos_disponibles');

            // 11. ¡ÉXITO!
            DB::commit();

            // 12. Enviar correo de confirmación
            $this->_enviarCorreoDeConfirmacion($inscripcion);

            // 13. Notificar al cajero
            $this->dispatch('notificacion', tipo: 'success', mensaje: '¡Matrícula procesada con éxito!');

            // 14. Redirigir de vuelta a la búsqueda (¡RUTA CORREGIDA!)
            return redirect()->route('taquilla.compraFinalizada', $compra);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar matrícula en taquilla: ' . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Error al procesar la matrícula: ' . $e->getMessage());
        }
    }

    // ===================================================================
    // MÉTODOS HELPER (Añadidos/Actualizados)
    // ===================================================================

    /**
     * ¡NUEVO HELPER!
     * Valida los campos adicionales requeridos.
     *
     */
    private function validarCamposAdicionales()
    {
        $reglas = [];
        $mensajes = [];

        foreach ($this->camposAdicionalesModelo as $campo) {
            //
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
     * ¡HELPER MODIFICADO!
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
            $this->dispatch('notificacion', tipo: 'warning', mensaje: 'Matrícula registrada, pero falló el envío de correo.');
        }
    }
    // (Método render (SIN CAMBIOS))
    public function render()
    {
        return view('livewire.taquilla.procesar-matricula-escuela');
    }
}
