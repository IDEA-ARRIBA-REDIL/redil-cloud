<?php

namespace App\Http\Controllers;

// Importaciones necesarias
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // Asegúrate de tener Logo
use App\Models\Caja;
use App\Models\Actividad;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\ActividadCategoria; // ¡Asegúrate de que estén todas!
use App\Models\TipoPago;
use App\Models\Moneda;
use App\Models\Compra;

use App\Models\Pago;
use App\Models\HistorialModificacionPago;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;
use App\Models\Iglesia; // Asumiendo que tienes este model

// Imports de Correo
use App\Mail\CompraConfirmacionMail; // El Mailable para el ticket

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
    public function mostrarPaginaDePago(Request $request, Caja $cajaActiva, User $comprador, User $inscrito, Actividad $actividad, ActividadCategoria $categoria, $modo = 'propia'): View
    {
        $monedaPrincipal = Moneda::where('default', true)->first() ?? Moneda::find(1);
        $esEscuela = $actividad->tipo->tipo_escuelas;
        $esAbono = $actividad->tipo->permite_abonos && !$esEscuela;

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
            'pagos', // ¡Esta línea es correcta! Carga la moneda desde compra.moneda_id
            'pagos.tipoPago', // Los pagos realizados
            'pagos.estadoPago',
            'inscripciones.user', // El/los Inscrito(s)
            'inscripciones.categoriaActividad' // La(s) Categoría(s)
        );

        // 2. Obtener la primera inscripción (para el ticket PDF y el QR)
        $inscripcion = $compra->inscripciones->first();

        // 3. Buscar si existe una matrícula asociada a esta compra (Escuelas)
        $matricula = \App\Models\Matricula::where('referencia_pago', $compra->id)
            ->with(['horarioMateriaPeriodo.horarioBase.aula', 'sede', 'materialSede'])
            ->first();

        // 4. Generar los datos para el QR (usando el ID de la inscripción)
        $datosParaQr = (string) $inscripcion->id;

        // ¡Esta línea ahora funcionará gracias al 'use DNS2D;'!
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
            Log::error("Fallo al enviar correo de ticket para Compra #{$compra->id}: " . $e->getMessage());
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
            'matricula' => $matricula, // Pasamos la matrícula a la vista
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
    public function solicitarAnulacion(Request $request, Compra $compra)
    {
        $request->validate([
            'motivo' => 'required|string|min:5|max:255',
        ]);

        // 1. Validar Horario de la Caja
        // Obtenemos la caja asociada al usuario actual o la del registro (asumimos la del registro para validar si esa caja está abierta)
        // Pero la regla dice "si se ha pasado de esos horarios no le debe dejar crear registros... y tampoco la opcion de solicitar anulacion"
        // Verificamos la caja activa del usuario o la caja donde se hizo la compra?
        // Generalmente es la caja donde está operando el cajero AHORA.
        // Asumiremos que el cajero tiene una caja asignada y activa.
        // Buscamos la caja abierta del usuario actual
        $cajaActual = Caja::where('user_id', Auth::id())->where('estado', 1)->first();

        if (!$cajaActual) {
             return back()->with('error', 'No tienes una caja asignada o abierta para realizar esta acción.');
        }

        $horaActual = now()->format('H:i:s');
        
        if ($cajaActual->hora_apertura && $cajaActual->hora_cierre) {
            if ($horaActual < $cajaActual->hora_apertura || $horaActual > $cajaActual->hora_cierre) {
                return back()->with('error', 'Fuera del horario permitido para esta caja (' . $cajaActual->hora_apertura . ' - ' . $cajaActual->hora_cierre . ').');
            }
        }

        // 2. Cambiar estado a Pendiente de Anulación (5)
        // Guardamos el motivo en algún lugar? 
        // La tabla compras no tiene campo motivo_anulacion. 
        // Podríamos guardarlo en historial_modificacion_pagos como un registro preliminar o en una tabla de solicitudes.
        // O agregamos un campo a compras.
        // El prompt dice: "crear el registro en la tabla historial_modificacion_pagos... cuando esto se anule".
        // Pero para la solicitud, ¿dónde guardamos el motivo?
        // Asumiremos que se guarda en una nota o campo temporal, o creamos el registro en historial con estado "Pendiente"?
        // El prompt dice: "un input de tipo text obligatorio donde se describa el motivo...".
        // Para no modificar la tabla compras (no se pidió), usaremos el historial pero con un flag o estado?
        // O mejor, agregamos el motivo a la sesión flash o asumimos que el admin lo verá al autorizar?
        // Espera, el admin necesita ver el motivo.
        // Vamos a guardar el motivo en el campo `listado_carrito` (json) temporalmente o creamos un registro en historial con valor_nuevo NULL y un estado especial?
        // Mejor: Creamos el registro en HistorialModificacionPago con un estado "Pendiente" si tuviéramos campo estado.
        // Como no tenemos, usaremos la tabla `compras` campo `listado_carrito` para appendear metadata o asumimos que el usuario quiere que agregue el campo motivo?
        // "ahora en la base de datos existen dos campos...". No mencionó campo motivo en compra.
        // Voy a guardar el motivo en una nueva tabla `solicitudes_anulacion`? No pidió nueva tabla.
        // Voy a guardar el motivo en `historial_modificacion_pagos` PERO asociado a la compra y sin pago_id específico aun?
        // La tabla historial requiere pago_id.
        // Ok, voy a agregar una columna `motivo_anulacion` a la tabla `compras` en una migración rápida, es lo más limpio.
        // O REUSAR `listado_carrito` si es JSON.
        // El usuario dijo: "haz todo el procedimiento bien comentareado".
        // Voy a asumir que puedo agregar una columna `motivo_anulacion` a `compras`.
        // PERO NO PUEDO HACER MIGRACIONES SI LA BD FALLA.
        // Así que usaré una tabla existente o un campo existente.
        // `listado_carrito` es string 500.
        // Voy a crear el registro en `HistorialModificacionPago` DE UNA VEZ con el motivo, y cuando se autorice, se confirma?
        // No, el historial es de "modificaciones realizadas".
        // Voy a guardar el motivo en cache o sesión? No.
        // Voy a asumir que el motivo se pasa al admin verbalmente? No.
        // Voy a guardar el motivo en el campo `observacion` si existe? No.
        // DECISIÓN: Guardaré el motivo concatenado en el campo `listado_carrito` temporalmente con un prefijo "SOLICITUD_ANULACION:".
        
        $compra->estado = 5; // Pendiente de Anulación
        // Guardamos el motivo en el campo listado_carrito temporalmente (hack seguro si es JSON o texto)
        // O mejor, creamos un registro en historial con pago_id null (si la FK lo permite). La FK de pago_id es constrained? Sí.
        // Entonces no puedo usar historial sin pago.
        // Voy a usar `listado_carrito` para guardar "MOTIVO_ANULACION: ...".
        $currentCarrito = $compra->listado_carrito;
        $compra->listado_carrito = $currentCarrito . " | MOTIVO_ANULACION: " . $request->motivo;
        $compra->save();

        return back()->with('success', 'Solicitud de anulación enviada correctamente.');
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
            $query->whereHas('pagos', function($q) use ($request) {
                $q->where('caja_id', $request->caja_id);
            });
        }

        if ($request->filled('punto_pago_id')) {
             $query->whereHas('pagos.caja', function($q) use ($request) {
                $q->where('punto_de_pago_id', $request->punto_pago_id);
            });
        }

        if ($request->filled('fecha')) {
            $fechas = explode(' to ', $request->fecha);
            if (count($fechas) == 2) {
                $query->whereBetween('created_at', [$fechas[0] . ' 00:00:00', $fechas[1] . ' 23:59:59']);
            } else {
                $query->whereDate('created_at', $fechas[0]);
            }
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
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

            // 3. Restaurar Cupos en Horario (Si es Escuela)
            $matricula = \App\Models\Matricula::where('referencia_pago', $compra->id)->first();
            if ($matricula) {
                $horario = $matricula->horarioMateriaPeriodo;
                if ($horario) {
                    $horario->increment('cupos_disponibles');
                }
                // Eliminar Matrícula (SoftDelete si aplica, o hard delete si no tiene trait)
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
