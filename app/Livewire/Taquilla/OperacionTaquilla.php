<?php

namespace App\Livewire\Taquilla;

use Livewire\Component;
use App\Models\Caja;
use App\Models\Actividad;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\Moneda;
use App\Models\Inscripcion;
use App\Models\ActividadCategoria;
use App\Models\Compra;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\InscripcionConfirmacionMail;
use Carbon\Carbon;
use Livewire\Attributes\On; // ¡Importante! Para el listener

class OperacionTaquilla extends Component
{
    // --- Modelos Principales (Recibidos) ---
    public Caja $cajaActiva;

    // --- Estado del Comprador (Paso 1) ---
    public ?User $comprador = null; // El que paga (el padre)
    public $compradorIdActual;
    public $parientes = []; // Lista de parientes del comprador

    // --- Estado del Formulario de Búsqueda (Paso 2) ---
    public $actividadesDisponibles = [];
    public $actividadIdActual;
    public $esInscripcionPropia = true; //
    public $inscritoIdActual;

    // --- Estado de Validación (Paso 3) ---
    public $verificacionEnviada = false; // Controla si se muestra el bloque de resultados
    public $validacionExitosa = false;
    public $mensajeError = '';
    public $categoriasDisponibles = []; // Los "cards" de categorías
    public ?User $usuarioAValidar = null; // El que se inscribe (el hijo)
    public ?Actividad $actividadSeleccionada = null;
    public $monedaPrincipal;

    /**
     * Reglas para el botón "Verificar requisitos".
     */
    protected function rules()
    {
        return [
            'actividadIdActual' => 'required',
        ];
    }
    protected $messages = [
        'actividadIdActual.required' => 'Debes seleccionar una actividad.',
    ];

    /**
     * MÉTODO MOUNT (Constructor)
     * Carga los datos iniciales.
     */
    public function mount(Caja $cajaActiva)
    {
        $this->cajaActiva = $cajaActiva;

        // Cargar datos que no cambian
        $this->actividadesDisponibles = Actividad::where('activa', true)
            ->where('punto_de_pago', true) //
            ->orderBy('nombre')
            ->get();

        $this->monedaPrincipal = Moneda::where('default', true)->first() ?? Moneda::find(1);
    }

    /**
     * ¡ACCIÓN CLAVE 1!
     * Escucha el evento 'usuario-seleccionado' del componente 'usuarios-para-busqueda'.
     * Carga los parientes INMEDIATAMENTE.
     *
     */
    #[On('usuario-seleccionado')]
    public function cargarParientes($id)
    {
        $this->compradorIdActual = $id;
        $this->comprador = User::find($id);

        if ($this->comprador) {
            //
            $this->parientes = $this->comprador->parientesDelUsuario()->get();
        } else {
            $this->parientes = collect();
        }

        // Reseteamos el estado al seleccionar un nuevo comprador
        $this->esInscripcionPropia = true;
        $this->inscritoIdActual = $this->compradorIdActual;
        $this->verificacionEnviada = false; // Oculta la validación anterior
    }

    /**
     * ¡ACCIÓN CLAVE 2!
     * Se ejecuta cuando se presiona el botón "Verificar requisitos".
     * Fusiona la lógica de 'GestionTaquilla' y 'ValidarInscripcion'.
     */
    public function verificarRequisitos()
    {
        $this->validate(); // Valida las 'rules()'

        // 1. Ocultar resultados anteriores
        $this->verificacionEnviada = false;
        $this->validacionExitosa = false;

        // 2. Determinar QUIÉN se va a inscribir
        if ($this->esInscripcionPropia) {
            $this->inscritoIdActual = $this->compradorIdActual;
        }

        if (empty($this->inscritoIdActual)) {
            $this->addError('inscritoIdActual', 'Debes seleccionar un familiar a inscribir.');
            return;
        }

        // 3. Cargar los modelos para la validación
        $this->usuarioAValidar = User::find($this->inscritoIdActual);
        $this->actividadSeleccionada = $this->actividadesDisponibles->find($this->actividadIdActual);

        // 4. Ejecutar la lógica de validación (copiada de ValidarInscripcion.php)
        //
        try {
            $this->ejecutarValidacionInterna();
        } catch (Exception $e) {
            $this->validacionExitosa = false;
            $this->mensajeError = 'Ocurrió un error inesperado al validar: ' . $e->getMessage();
            Log::error('Error en Taquilla.OperacionTaquilla.verificarRequisitos: ' . $e->getMessage());
        }

        // 5. Mostrar el bloque de resultados (éxito o error)
        $this->verificacionEnviada = true;
    }

    /**
     * ¡ACCIÓN CLAVE 3!
     * Lógica de "Limpiar"
     */
    public function limpiar()
    {
        $this->reset(['comprador', 'usuarioAValidar', 'actividadSeleccionada', 'parientes', 'compradorIdActual', 'actividadIdActual', 'inscritoIdActual', 'esInscripcionPropia', 'verificacionEnviada']);
        // Evento para resetear el buscador de usuarios
        $this->dispatch('resetear-buscador-usuario');
        // Evento para resetear el Select2 de actividad
        $this->dispatch('resetear-select-actividad');
    }

    /**
     * ¡ACCIÓN CLAVE 4!
     * Lógica de inscripción gratuita (copiada de ValidarInscripcion.php)
     *
     */
    public function procesarInscripcionGratuita($categoriaId)
    {
        // 1. Validación de duplicados
        $inscripcionExistente = Inscripcion::where('user_id', $this->usuarioAValidar->id)
            ->where('actividad_categoria_id', $categoriaId)
            ->exists();
        if ($inscripcionExistente) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Este usuario ya tiene una inscripción en esta categoría.');
            return;
        }

        // 2. Transacción
        DB::beginTransaction();
        try {
            // 3. Validar Aforo (con bloqueo)
            $categoria = ActividadCategoria::where('id', $categoriaId)->lockForUpdate()->first();
            if ($categoria->aforo > 0 && $categoria->aforo_ocupado >= $categoria->aforo) {
                DB::rollBack();
                $this->dispatch('notificacion', tipo: 'error', mensaje: 'El cupo para esta categoría se agotó.');
                return;
            }

            // 4. Crear Compra (a nombre del COMPRADOR)
            $compra = Compra::create([
                'user_id' => $this->comprador->id,
                'moneda_id' => $this->monedaPrincipal->id,
                'fecha' => Carbon::now(),
                'valor' => 0,
                'estado' => 3, // Pagada (gratis)
                'nombre_completo_comprador' => $this->comprador->nombre(4),
                'identificacion_comprador' => $this->comprador->identificacion,
                'telefono_comprador' => $this->comprador->telefono_movil,
                'email_comprador' => $this->comprador->email,
                'actividad_id' => $this->actividadSeleccionada->id
            ]);

            // 5. Crear Inscripción (a nombre del INSCRITO)
            $inscripcion = Inscripcion::create([
                'user_id' => $this->usuarioAValidar->id,
                'actividad_categoria_id' => $categoria->id,
                'compra_id' => $compra->id,
                'fecha' => Carbon::now(),
                'estado' => $this->actividadSeleccionada->estado_inscripcion_defecto,
                'nombre_inscrito' => $this->usuarioAValidar->nombre(4)
            ]);

            $categoria->aforo_ocupado = ($categoria->aforo_ocupado ?? 0) + 1;
            $categoria->save();
            DB::commit();

            // 6. Enviar Correo
            $this->_enviarCorreoDeConfirmacion($inscripcion);
            $this->dispatch('notificacion', tipo: 'success', mensaje: '¡Inscripción gratuita registrada con éxito!');

            // 7. Refrescar la validación para mostrar "Ya inscrito"
            $this->ejecutarValidacionInterna();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar inscripción gratuita: ' . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Error al procesar la inscripción.');
        }
    }

    // ===================================================================
    // MÉTODOS HELPER (PRIVADOS)
    // ===================================================================

    /**
     * HELPER 1: Lógica de validación interna.
     * (Copiada de ValidarInscripcion.php)
     */
    private function ejecutarValidacionInterna()
    {
        $this->validacionExitosa = false;
        $this->mensajeError = '';
        $this->categoriasDisponibles = [];

        // CASO 1: Escuela
        if ($this->actividadSeleccionada->tipo && $this->actividadSeleccionada->tipo->tipo_escuelas) {
            $this->validacionExitosa = true;
            $this->categoriasDisponibles = $this->actividadSeleccionada->validarCategoriasEscuelaParaTaquilla($this->usuarioAValidar);

            // CASO 2: Normal (Restricción por Categoría)
        } elseif ($this->actividadSeleccionada->restriccion_por_categoria) {
            $resultado = $this->actividadSeleccionada->verificarDisponibilidadCategorias($this->usuarioAValidar->id);
            if ($resultado['success']) {
                $this->validacionExitosa = true;
                $this->categoriasDisponibles = collect($resultado['categorias'])->map(fn($cat) => (object)[
                    'categoria' => $cat,
                    'estado' => 'DISPONIBLE',
                    'motivos' => []
                ]);
            } else {
                $this->validacionExitosa = false;
                $this->mensajeError = $resultado['message'];
            }

            // CASO 3: Normal (Restricción General)
        } else {
            $resultado = $this->actividadSeleccionada->validarUsuarioEnCategoria($this->usuarioAValidar, null);
            if ($resultado->estado === 'DISPONIBLE') {
                $this->validacionExitosa = true;
                $this->categoriasDisponibles = $this->actividadSeleccionada->categorias->map(fn($cat) => (object)[
                    'categoria' => $cat,
                    'estado' => 'DISPONIBLE',
                    'motivos' => []
                ]);
            } else {
                $this->validacionExitosa = false;
                $this->mensajeError = implode(". ", $resultado->motivos);
                // Si la actividad misma está bloqueada, bloqueamos todas sus categorías visualmente
                $this->categoriasDisponibles = $this->actividadSeleccionada->categorias->map(fn($cat) => (object)[
                    'categoria' => $cat,
                    'estado' => 'BLOQUEADA',
                    'motivos' => $resultado->motivos
                ]);
            }
        }

        // -----------------------------------------------------------------
        // ¡INICIO DE LA MODIFICACIÓN!
        // COMPROBACIÓN FINAL: ¿YA INSCRITO? (Lógica mejorada)
        // -----------------------------------------------------------------
        if ($this->validacionExitosa) {

            // --- RAMA NUEVA: LÓGICA DE ABONOS ---
            if ($this->actividadSeleccionada->tipo->permite_abonos && !$this->actividadSeleccionada->tipo->tipo_escuelas) {

                // 1. Buscamos si el COMPRADOR ya tiene una Compra para esta actividad
                $compraExistente = Compra::where('user_id', $this->comprador->id)
                    ->where('actividad_id', $this->actividadSeleccionada->id)
                    ->with(['inscripciones', 'pagos']) // Cargamos relaciones clave
                    ->first();

                if ($compraExistente) {
                    // 2. El Comprador SÍ tiene una compra. Verificamos si el INSCRITO actual está en ella.
                    $inscripcionAbono = $compraExistente->inscripciones
                        ->firstWhere('user_id', $this->usuarioAValidar->id);

                    $categoriaInscritaId = $inscripcionAbono ? $inscripcionAbono->actividad_categoria_id : null;

                    if ($categoriaInscritaId) {
                        // 3. ¡SÍ! El inscrito tiene un abono iniciado. Calculamos su estado.
                        $totalPagado = $compraExistente->pagos->sum('valor');
                        $valorTotal = $compraExistente->valor;
                        $debeAun = $totalPagado < $valorTotal;

                        // 4. Mapeamos las categorías con la nueva lógica
                        $this->categoriasDisponibles = $this->categoriasDisponibles->map(function ($item) use ($categoriaInscritaId, $debeAun, $totalPagado, $valorTotal) {

                            if ($item->categoria->id == $categoriaInscritaId) {
                                // Esta es la categoría en la que están inscritos.
                                if ($debeAun) {
                                    $item->estado = 'ABONO_PENDIENTE'; // ¡Nuevo estado!
                                    $item->totalPagado = $totalPagado;
                                    $item->valorTotal = $valorTotal;
                                } else {
                                    $item->estado = 'INSCRITO'; // Ya pagó todo
                                }
                            } else {
                                // Esta NO es su categoría. La bloqueamos.
                                $item->estado = 'BLOQUEADA';
                                $item->motivos = ['Ya tienes un abono iniciado en otra categoría.'];
                            }
                            return $item;
                        });
                    }
                    // Si $categoriaInscritaId es null, significa que el Comprador tiene una compra
                    // pero para OTRO inscrito. Dejamos el estado 'DISPONIBLE' para este nuevo inscrito.
                    // Esto es correcto.
                }
                // Si no hay $compraExistente, la lógica de 'DISPONIBLE' original se mantiene.

                // --- RAMA ANTIGUA: LÓGICA NORMAL (GRATUITAS, ESCUELAS) ---
            } else {
                $categoriaIds = $this->actividadSeleccionada->categorias()->pluck('id');
                $inscripcionesExistentesIds = Inscripcion::where('user_id', $this->usuarioAValidar->id)
                    ->whereIn('actividad_categoria_id', $categoriaIds)
                    ->pluck('actividad_categoria_id');

                $this->categoriasDisponibles = $this->categoriasDisponibles->map(function ($item) use ($inscripcionesExistentesIds) {
                    if ($inscripcionesExistentesIds->contains($item->categoria->id)) {
                        $item->estado = 'INSCRITO';
                    }
                    return $item;
                });
            }
        }
        // -----------------------------------------------------------------
        // ¡FIN DE LA MODIFICACIÓN!
        // -----------------------------------------------------------------
    }

    /**
     * HELPER 2: Envío de correo.
     * (Copiado de ValidarInscripcion.php)
     */
    private function _enviarCorreoDeConfirmacion(Inscripcion $inscripcion)
    {
        try {
            $inscripcion->load('categoriaActividad.actividad', 'compra', 'user');
            $actividad = $inscripcion->categoriaActividad->actividad;
            $emailDestinatario = $inscripcion->user->email ?? $inscripcion->compra->email_comprador;

            if (filter_var($emailDestinatario, FILTER_VALIDATE_EMAIL)) {
                Mail::to($emailDestinatario)->send(new InscripcionConfirmacionMail($inscripcion, $actividad));
            } else {
                Log::warning("No se pudo enviar correo para inscripción #{$inscripcion->id} por email inválido: " . $emailDestinatario);
            }
        } catch (Exception $e) {
            Log::error("Fallo al enviar correo de confirmación para inscripción #{$inscripcion->id}: " . $e->getMessage());
            $this->dispatch('notificacion', titulo: 'Fallo envio de correo',   tipo: 'warning', mensaje: 'Inscripción registrada, pero falló el envío de correo.');
        }
    }

    /**
     * RENDER
     * Carga la vista y el layout.
     */
    public function render()
    {
        return view('livewire.taquilla.operacion-taquilla', [
            'configuracion' => Configuracion::find(1),
        ])->layout('layouts.layoutMaster'); // ¡Le decimos que use tu layout!
    }
}
