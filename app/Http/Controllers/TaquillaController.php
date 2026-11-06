<?php

namespace App\Http\Controllers;

// Importaciones necesarias
use App\Mail\CompraConfirmacionMail;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\HistorialModificacionPago; // Asegúrate de tener Logo
use App\Models\Iglesia;
use App\Models\Moneda;
use App\Models\Pago;
use App\Models\TipoPago;
use App\Models\User; // ¡Asegúrate de que estén todas!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View; // Asumiendo que tienes este model
// Imports de Correo
use Milon\Barcode\Facades\DNS2DFacade as DNS2D; // El Mailable para el ticket

class TaquillaController extends Controller
{
    /**
     * Muestra las cajas asignadas al usuario.
     * (Sin cambios)
     */
    public function misCajas(): View
    {
        $usuario = Auth::user();
        $cajasAsignadas = $usuario->cajasAsignadas()
            ->with('puntoDePago.sede')
            ->where('estado', true)
            ->get();

        return view('contenido.paginas.taquillas.mis-cajas', [ // Ruta de vista corregida
            'configuracion' => Configuracion::find(1),
            'cajasAsignadas' => $cajasAsignadas,
        ]);
    }

    /**
     * ¡MÉTODO CORREGIDO!
     * Define todas las variables que la vista 'gestionar.blade.php' necesita.
     */
    public function operar(Request $request, Caja $cajaActiva): View
    {
        // 1. Cargar datos para los filtros (Actividades)
        $actividadesDisponibles = Actividad::where('activa', true)
            ->where('punto_de_pago', true) //
            ->orderBy('nombre')
            ->get();

        // 2. Procesar los IDs de la URL
        $compradorId = $request->input('user_id', null); //
        $actividadId = $request->input('actividad_id', null);
        $inscritoId = $request->input('inscrito_id', $compradorId);

        /**
         * Verificamos si el formulario fue enviado explícitamente.
         * (Como solicitaste, esta es la lógica clave)
         */
        $verificacionEnviada = $request->has('verificar'); //

        // 3. Inicializar variables
        $comprador = null;
        $parientes = collect();
        $usuarioAValidar = null;
        $actividadSeleccionada = null;

        // 4. Cargar al Comprador y sus Parientes (esto se hace siempre)
        if ($compradorId) {
            $comprador = User::find($compradorId);
            if ($comprador) {
                $parientes = $comprador->parientesDelUsuario()->get(); //
            }
        }

        // 5. Cargar la Actividad y el Inscrito (Solo si se ENVIÓ el formulario)
        if ($verificacionEnviada) {
            if ($actividadId) {
                $actividadSeleccionada = $actividadesDisponibles->find($actividadId);
            }
            if ($inscritoId) {
                $usuarioAValidar = User::find($inscritoId);
            }
        }

        // 6. Devolver la vista principal
        //
        return view('contenido.paginas.taquillas.gestionar', [
            'configuracion' => Configuracion::find(1),
            'actividadesDisponibles' => $actividadesDisponibles,
            'cajaActiva' => $cajaActiva,

            // Variables para la lógica de parientes
            'comprador' => $comprador,
            'parientes' => $parientes,

            // Variables para "form sticky"
            'compradorIdActual' => $compradorId,
            'inscritoIdActual' => $inscritoId,
            'actividadIdActual' => $actividadId,

            // Variable de control de flujo
            'verificacionEnviada' => $verificacionEnviada,

            // Modelos para el componente Livewire
            'usuarioAValidar' => $usuarioAValidar,
            'actividadSeleccionada' => $actividadSeleccionada,
        ]);
    }

    /**
     * Muestra la página de pago.
     * (¡MODIFICADO! Asegúrate de que los nombres de variables coincidan)
     */
    public function mostrarPaginaDePago(Request $request, Caja $cajaActiva, User $comprador, User $inscrito, Actividad $actividad, ActividadCategoria $categoria, $modo = 'propia'): View|\Illuminate\Http\RedirectResponse
    {
        // 1. Validar que la caja esté activa y dentro de su horario de atención
        if (! $cajaActiva->puedeOperar()) {
            $mensaje = $cajaActiva->obtenerMensajeFueraDeHorario() ?? 'La caja no se encuentra habilitada para operar en este momento.';

            return redirect()->route('taquilla.operar', $cajaActiva)->with('error', $mensaje);
        }

        $monedaPrincipal = Moneda::where('default', true)->first() ?? Moneda::find(1);
        $esEscuela = $actividad->tipo->tipo_escuelas;
        $esAbono = $actividad->tipo->permite_abonos && ! $esEscuela;

        //
        return view('contenido.paginas.taquillas.procesar-venta', [
            'configuracion' => Configuracion::find(1),
            'comprador' => $comprador, // El que paga
            'inscrito' => $inscrito,   // El que se inscribe
            'actividad' => $actividad,
            'categoria' => $categoria,
            'cajaActiva' => $cajaActiva,
            'moneda' => $monedaPrincipal,
            'esEscuela' => $esEscuela,
            'esAbono' => $esAbono,
        ]);
    }

    /**
     * ¡NUEVO MÉTODO!
     * Muestra la página de éxito/recibo después de una transacción
     * y envía el correo de confirmación con el ticket PDF.
     */
    public function compraFinalizada(Request $request, Compra $compra): View
    {
        // 1. Cargar todas las relaciones necesarias para la vista y el email
        $compra->load(
            'user', // El Comprador
            'actividad',
            'actividad.tipo', // ¡NUEVO! Necesario para saber si permite abonos
            'moneda',
            'pagos', // Carga los pagos realizados
            'pagos.tipoPago',
            'pagos.estadoPago',
            'destinatario',
            'inscripciones.user', // El/los Inscrito(s)
            'inscripciones.categoriaActividad' // La(s) Categoría(s)
        );

        // 2. Obtener la primera inscripción (para el ticket PDF y el QR si aplica)
        $inscripcion = $compra->inscripciones->first();

        // 3. Buscar si existen matrículas asociadas a esta compra (Escuelas)
        $pagoIds = $compra->pagos->pluck('id')->filter()->all();
        $referencias = array_merge(
            [$compra->id, (string) $compra->id],
            $pagoIds,
            array_map('strval', $pagoIds)
        );

        $matriculas = \App\Models\Matricula::where(function ($query) use ($compra, $referencias, $pagoIds) {
            $query->where('compra_id', $compra->id)
                ->orWhereIn('referencia_pago', $referencias);
            if (! empty($pagoIds)) {
                $query->orWhereIn('pago_id', $pagoIds);
            }
        })
            ->with([
                'horarioMateriaPeriodo.horarioBase.aula.sede',
                'horarioMateriaPeriodo.horarioBase.aula.tipo',
                'horarioMateriaPeriodo.horarioBase.materia',
                'horarioMateriaPeriodo.materiaPeriodo.materia',
                'sede',
                'materialSede',
                'escuela',
                'user',
            ])
            ->latest('id')
            ->get();

        // Fallback: Si no está vinculada por id/referencia directa, buscar por usuario y fecha cercana
        if ($matriculas->isEmpty() && $compra->user_id) {
            $matriculas = \App\Models\Matricula::where('user_id', $compra->user_id)
                ->whereBetween('created_at', [
                    $compra->created_at->copy()->subMinutes(30),
                    $compra->created_at->copy()->addMinutes(30),
                ])
                ->with([
                    'horarioMateriaPeriodo.horarioBase.aula.sede',
                    'horarioMateriaPeriodo.horarioBase.aula.tipo',
                    'horarioMateriaPeriodo.horarioBase.materia',
                    'horarioMateriaPeriodo.materiaPeriodo.materia',
                    'sede',
                    'materialSede',
                    'escuela',
                    'user',
                ])
                ->latest('id')
                ->get();
        }

        $matricula = $matriculas->first();

        // 4. Generar los datos para el QR de forma segura
        $datosParaQr = (string) ($inscripcion?->id ?? $matricula?->id ?? $compra->id);

        $qrCode = DNS2D::getBarcodePNG($datosParaQr, 'QRCODE');

        // 5. Enviar el Correo de Confirmación (con el Ticket PDF)
        try {
            if (filter_var($compra->email_comprador, FILTER_VALIDATE_EMAIL)) {

                $pagoReferencia = $compra->pagos->first();

                Mail::to($compra->email_comprador)->send(new CompraConfirmacionMail(
                    $compra,
                    $pagoReferencia,
                    $inscripcion,
                    $compra->actividad,
                    $matricula // Pasamos la matrícula (puede ser null)
                ));
            }
        } catch (\Exception $e) {
            Log::error("Fallo al enviar correo de ticket para Compra #{$compra->id}: ".$e->getMessage());
            // No detenemos al usuario, la compra fue exitosa, solo falló el email.
        }

        // 6. Cargar datos de configuración para la vista
        $configuracion = Configuracion::find(1);
        $iglesia = Iglesia::find(1);
        $colorEncabezado = '#000000';
        $titulo = '¡Inscripción exitosa!';
        $mensaje = 'Tu transacción ha sido procesada correctamente.';

        // 7. Devolver la vista con todos los datos
        return view('contenido.paginas.taquillas.compra-finalizada', [
            'configuracion' => $configuracion,
            'iglesia' => $iglesia,
            'compra' => $compra,
            'colorEncabezado' => $colorEncabezado,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'qrCodeBase64' => $qrCode, // Pasamos el QR a la vista
            'matricula' => $matricula, // Pasamos la matrícula principal
            'matriculas' => $matriculas, // Pasamos la colección completa de matrículas
        ]);
    }

    /**
     * Muestra el historial de transacciones de una caja específica.
     */
    public function historialTransacciones(Request $request, Caja $cajaActiva): View
    {
        return view('contenido.paginas.taquillas.historial', [
            'configuracion' => Configuracion::find(1),
            'cajaActiva' => $cajaActiva,
        ]);
    }

    /**
     * Muestra el historial de modificaciones (Auditoría).
     */
    public function historialModificaciones(): View
    {
        return view('contenido.paginas.taquillas.historial-modificaciones');
    }

    /**
     * Solicita la anulación de una compra.
     * Estado 5: Pendiente de Anulación.
     */
    /**
     * Registra una solicitud de anulación para una compra.
     * Estado 5: Pendiente de Anulación.
     */
    public function solicitarAnulacion(Request $request, Compra $compra)
    {
        $request->validate([
            'motivo' => 'required|string|min:5|max:255',
            'caja_id' => 'nullable|integer|exists:cajas,id',
        ]);

        // 1. Identificar la caja activa específica enviada desde la vista o asociada al pago
        $cajaId = $request->input('caja_id');

        if ($cajaId) {
            $cajaActual = Caja::find($cajaId);
        } else {
            $registroCajaId = $compra->pagos()->first()?->registro_caja_id;
            $cajaActual = $registroCajaId
                ? Caja::find($registroCajaId)
                : Caja::where('user_id', Auth::id())->where('estado', true)->first();
        }

        if (! $cajaActual || ! $cajaActual->estado) {
            return back()->with('error', 'No se identificó una caja activa u abierta para realizar esta acción.');
        }

        // 2. Validar si la CAJA ESPECÍFICA permite modificar/anular registros
        if (! $cajaActual->permite_modificar_registros) {
            return back()->with('error', "La caja '{$cajaActual->nombre}' no tiene habilitado el permiso para modificar o anular registros.");
        }

        // 3. Validar Horario de Operación de la Caja especificada
        if (! $cajaActual->estaEnHorario()) {
            return back()->with('error', "Operación no permitida: La caja '{$cajaActual->nombre}' se encuentra fuera de su horario de atención ({$cajaActual->horario_atencion}).");
        }

        // 4. Validar estado actual de la compra
        if (in_array($compra->estado, [4, 5, 6])) {
            return back()->with('error', 'La compra ya se encuentra anulada o con una solicitud de anulación en curso.');
        }

        // 5. Cambiar estado a Pendiente de Anulación (5) y registrar motivo
        $compra->estado = 5;
        $currentCarrito = $compra->listado_carrito ?? '';
        $compra->listado_carrito = trim($currentCarrito).' | MOTIVO_ANULACION: '.$request->motivo;
        $compra->save();

        return back()->with('success', "Solicitud de anulación enviada correctamente a la administración desde '{$cajaActual->nombre}'.");
    }

    /**
     * Lista las solicitudes de anulación para administradores.
     */
    public function listarSolicitudesAnulacion(Request $request)
    {
        // Filtros
        $query = Compra::where('estado', 5)
            ->with(['user', 'pagos.caja.usuario', 'actividad']);

        if ($request->filled('caja_id')) {
            // Filtrar por caja del primer pago
            $query->whereHas('pagos', function ($q) use ($request) {
                $q->where('caja_id', $request->caja_id);
            });
        }

        if ($request->filled('punto_pago_id')) {
            $query->whereHas('pagos.caja', function ($q) use ($request) {
                $q->where('punto_de_pago_id', $request->punto_pago_id);
            });
        }

        if ($request->filled('fecha')) {
            $fechas = explode(' to ', $request->fecha);
            if (count($fechas) == 2) {
                $query->whereBetween('created_at', [$fechas[0].' 00:00:00', $fechas[1].' 23:59:59']);
            } else {
                $query->whereDate('created_at', $fechas[0]);
            }
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('identificacion_comprador', 'like', "%$busqueda%")
                    ->orWhere('nombre_completo_comprador', 'like', "%$busqueda%");
            });
        }

        $solicitudes = $query->paginate(10);
        $cajas = Caja::all();
        $puntosPago = \App\Models\PuntoDePago::all(); // Asumiendo modelo

        return view('contenido.paginas.taquillas.solicitudes-anulacion', compact('solicitudes', 'cajas', 'puntosPago'));
    }

    /**
     * Autoriza la anulación de una compra.
     * Estado 6: Anulada.
     */
    /**
     * Autoriza la anulación de una compra.
     * Elimina registros y devuelve cupos.
     */
    public function autorizarAnulacion(Request $request, Compra $compra)
    {

        DB::transaction(function () use ($compra, $request) {
            // 1. Cargar relaciones necesarias
            $compra->load('inscripciones', 'pagos', 'actividad');

            // 2. Restaurar Cupos en Categoría
            foreach ($compra->inscripciones as $inscripcion) {
                $categoria = ActividadCategoria::find($inscripcion->actividad_categoria_id);
                if ($categoria) {
                    $categoria->decrement('aforo_ocupado');
                }
            }

            // 3. Restaurar Cupos en Horario (Si es Escuela) y eliminar registros académicos
            $matriculas = \App\Models\Matricula::where('referencia_pago', $compra->id)->get();
            foreach ($matriculas as $matricula) {
                $horario = $matricula->horarioMateriaPeriodo;
                if ($horario) {
                    $horario->increment('cupos_disponibles');
                }
                // Eliminar estado académico asociado explícitamente y luego la matrícula (soft delete)
                \App\Models\MatriculaHorarioMateriaPeriodo::where('matricula_id', $matricula->id)->delete();
                $matricula->delete();
            }

            // 4. Registrar en Historial y Eliminar Pagos
            foreach ($compra->pagos as $pago) {
                // Crear registro en historial
                HistorialModificacionPago::create([
                    'asesor_id' => auth()->id(),
                    'caja_id' => $pago->registro_caja_id, // Asumiendo que Pago tiene registro_caja_id
                    'punto_de_pago_id' => $pago->caja->punto_de_pago_id ?? null, // Obtener del modelo Caja
                    'compra_id' => $compra->id,
                    'pago_id' => $pago->id,
                    'usuario_afectado_id' => $compra->user_id,
                    'actividad_id' => $compra->actividad_id,
                    'categoria_actividad_id' => $pago->actividad_categoria_id,
                    'tipo_pago_id' => $pago->tipo_pago_id,
                    'valor' => $pago->valor,
                    'motivo' => $request->motivo_anulacion, // Obtener motivo del request si existe
                ]);

                // Descontar del acumulado si el tipo de pago tiene límite habilitado
                $tipoPago = TipoPago::find($pago->tipo_pago_id);
                if ($tipoPago && $tipoPago->tiene_limite_dinero_acumulado) {
                    $caja = Caja::find($pago->registro_caja_id);
                    if ($caja && $caja->dinero_acumulado >= $pago->valor) {
                        $caja->decrement('dinero_acumulado', $pago->valor);
                    }
                }

                // Eliminar Pago (SoftDelete)
                $pago->delete();
            }

            // 5. Eliminar Inscripciones (SoftDelete)
            $compra->inscripciones()->delete();

            // 6. Eliminar Compra (SoftDelete)
            $compra->delete();
        });

        return back()->with('success', 'Compra anulada, registros eliminados y cupos restaurados correctamente.');
    }

    /**
     * Rechaza la anulación de una compra.
     * Devuelve el estado a Pagado (1).
     */
    public function rechazarAnulacion(Request $request, Compra $compra)
    {
        // 1. Restaurar estado a Pagado
        $compra->estado = 1;

        // 2. Limpiar motivo del carrito (opcional, pero limpio)
        if (str_contains($compra->listado_carrito, ' | MOTIVO_ANULACION:')) {
            $parts = explode(' | MOTIVO_ANULACION:', $compra->listado_carrito);
            $compra->listado_carrito = $parts[0];
        }

        $compra->save();

        return back()->with('success', 'Solicitud de anulación rechazada. La compra ha vuelto a su estado original.');
    }
}
