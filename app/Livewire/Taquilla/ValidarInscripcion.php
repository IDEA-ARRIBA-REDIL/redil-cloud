<?php

namespace App\Livewire\Taquilla;

use Livewire\Component;
use App\Models\User;
use App\Models\Actividad;
use App\Models\Moneda; // Necesitaremos la moneda
use App\Models\Caja;
use Exception; // Para manejo de errores
// --- ¡NUEVAS IMPORTACIONES PARA LA TRANSACCIÓN! ---
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ActividadCategoria; //
use App\Models\Compra;             //
use App\Models\Inscripcion;        //
use Carbon\Carbon;                // Para la fecha

// Necesarias para el correo
use Illuminate\Support\Facades\Mail;
use App\Mail\InscripcionConfirmacionMail; //

class ValidarInscripcion extends Component
{
    // ===================================================================
    // PROPIEDADES RECIBIDAS (PROPS)
    // ===================================================================

    /**
     * El usuario que está pagando (El Padre/Comprador).
     * @var User
     */
    public User $comprador;

    /**
     * El usuario que será inscrito (El Hijo o el mismo Padre).
     * @var User
     */
    public User $usuario;

    /**
     * La actividad que se está validando.
     * @var Actividad
     */
    public Actividad $actividad;

    /**
     * La caja activa desde donde opera el cajero.
     * @var Caja
     */
    public Caja $cajaActiva;

    // ===================================================================
    // ESTADO INTERNO DEL COMPONENTE
    // ===================================================================

    public $validacionExitosa = false;
    public $mensajeError = '';
    public $categoriasDisponibles = [];
    public $monedaPrincipal;

    /**
     * MÉTODO MOUNT (Constructor)
     * Se ejecuta una vez al cargar el componente.
     * Carga la moneda y ejecuta la validación principal.
     */
    public function mount()
    {
        try {
            // Carga la moneda por defecto (o la primera)
            $this->monedaPrincipal = Moneda::where('default', true)->first() ?? Moneda::find(1);

            // Ejecuta la lógica de validación principal
            $this->ejecutarValidacion();
        } catch (Exception $e) {
            $this->validacionExitosa = false;
            $this->mensajeError = 'Ocurrió un error inesperado al validar: ' . $e->getMessage();
            Log::error('Error en Taquilla.ValidarInscripcion.mount: ' . $e->getMessage());
        }
    }

    /**
     * LÓGICA DE VALIDACIÓN PRINCIPAL
     * Determina qué categorías puede o no inscribir el usuario.
     */
    public function ejecutarValidacion()
    {
        $this->validacionExitosa = false;
        $this->mensajeError = '';
        $this->categoriasDisponibles = [];

        // CASO 1: Es una actividad de tipo Escuela
        //
        if ($this->actividad->tipo && $this->actividad->tipo->tipo_escuelas) {
            $this->validacionExitosa = true;
            // Llama al método 'validarCategoriasEscuelaParaTaquilla' que creamos en el modelo Actividad.
            // Este método valida prerrequisitos (Aprobados y En Progreso).
            //
            $this->categoriasDisponibles = $this->actividad->validarCategoriasEscuelaParaTaquilla($this->usuario);

            // CASO 2: Es una actividad normal con restricciones por Categoría
        } elseif ($this->actividad->restriccion_por_categoria) {
            //
            $resultado = $this->actividad->verificarDisponibilidadCategorias($this->usuario->id);
            if ($resultado['success']) {
                $this->validacionExitosa = true;
                // Mapea al formato de objeto estándar que espera la vista
                $this->categoriasDisponibles = collect($resultado['categorias'])->map(fn($cat) => (object)[
                    'categoria' => $cat,
                    'estado' => 'DISPONIBLE',
                    'motivos' => []
                ]);
            } else {
                $this->validacionExitosa = false;
                $this->mensajeError = $resultado['message'];
            }

            // CASO 3: Es una actividad normal con restricciones Generales
        } else {
            //
            $resultado = $this->actividad->validarAsistenciaActividad($this->usuario->id, $this->actividad->id);
            if ($resultado) {
                $this->validacionExitosa = true;
                // Mapea al formato de objeto estándar
                $this->categoriasDisponibles = $this->actividad->categorias->map(fn($cat) => (object)[
                    'categoria' => $cat,
                    'estado' => 'DISPONIBLE',
                    'motivos' => []
                ]);
            } else {
                $this->validacionExitosa = false;
                $this->mensajeError = 'El usuario no cumple con los requisitos generales.';
            }
        }

        // --- COMPROBACIÓN FINAL: ¿YA ESTÁ INSCRITO? ---
        // Si la validación de requisitos fue exitosa, revisamos si ya tiene un tiquete.
        if ($this->validacionExitosa) {
            $categoriaIds = $this->actividad->categorias()->pluck('id');

            //
            $inscripcionesExistentesIds = Inscripcion::where('user_id', $this->usuario->id)
                ->whereIn('actividad_categoria_id', $categoriaIds)
                ->pluck('actividad_categoria_id');

            // Sobrescribimos el estado si encontramos una inscripción
            $this->categoriasDisponibles = $this->categoriasDisponibles->map(function ($item) use ($inscripcionesExistentesIds) {
                if ($inscripcionesExistentesIds->contains($item->categoria->id)) {
                    $item->estado = 'INSCRITO'; //
                }
                return $item;
            });
        }
    }

    /**
     * Procesa una inscripción GRATUITA (Valor = 0).
     *
     */
    public function procesarInscripcionGratuita($categoriaId)
    {
        // 1. Validación de duplicados
        $inscripcionExistente = Inscripcion::where('user_id', $this->usuario->id)
            ->where('actividad_categoria_id', $categoriaId)
            ->exists();

        if ($inscripcionExistente) {
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Este usuario ya tiene una inscripción en esta categoría.');
            return;
        }

        // 2. Iniciar Transacción
        DB::beginTransaction();
        try {
            // 3. Validar Aforo (con bloqueo)
            //
            $categoria = ActividadCategoria::where('id', $categoriaId)->lockForUpdate()->first();
            if ($categoria->aforo > 0 && $categoria->aforo_ocupado >= $categoria->aforo) {
                DB::rollBack();
                $this->dispatch('notificacion', tipo: 'error', mensaje: 'El cupo para esta categoría se agotó.');
                return;
            }

            // 4. Crear la Compra (a nombre del COMPRADOR/PADRE)
            //
            $compra = Compra::create([
                'user_id' => $this->comprador->id, // ¡ID del Comprador!
                'moneda_id' => $this->monedaPrincipal->id,
                'fecha' => Carbon::now(),
                'valor' => 0,
                'estado' => 3, // Pagada (gratis)
                'nombre_completo_comprador' => $this->comprador->nombre(4),
                'identificacion_comprador' => $this->comprador->identificacion,
                'telefono_comprador' => $this->comprador->telefono_movil,
                'email_comprador' => $this->comprador->email,
                'actividad_id' => $this->actividad->id
            ]);

            // 5. Crear la Inscripción (a nombre del INSCRITO/HIJO)
            //
            $inscripcion = Inscripcion::create([
                'user_id' => $this->usuario->id, // ¡ID del Inscrito!
                'actividad_categoria_id' => $categoria->id,
                'compra_id' => $compra->id,
                'fecha' => Carbon::now(),
                'estado' => $this->actividad->estado_inscripcion_defecto,
                'nombre_inscrito' => $this->usuario->nombre(4) // Nombre del Inscrito
            ]);

            // 6. Incrementar Aforo
            $categoria->increment('aforo_ocupado');

            // 7. Confirmar Transacción
            DB::commit();

            // 8. Enviar Correo de Confirmación
            $this->_enviarCorreoDeConfirmacion($inscripcion);

            // 9. Notificar al Cajero
            $this->dispatch('notificacion', tipo: 'success', mensaje: '¡Inscripción gratuita registrada con éxito!');

            // 10. Refrescar la vista para mostrar el estado "Ya inscrito"
            $this->ejecutarValidacion();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar inscripción gratuita: ' . $e->getMessage());
            $this->dispatch('notificacion', tipo: 'error', mensaje: 'Error al procesar la inscripción.');
        }
    }

    /**
     * Helper privado para enviar el correo de confirmación.
     *
     */
    private function _enviarCorreoDeConfirmacion(Inscripcion $inscripcion)
    {
        try {
            $inscripcion->load('categoriaActividad.actividad', 'compra', 'user');
            $actividad = $inscripcion->categoriaActividad->actividad;

            // Determina el email (del inscrito si lo tiene, si no, del comprador)
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
     * Método 'render'
     * Dibuja la vista del componente Livewire.
     */
    public function render()
    {
        return view('livewire.taquilla.validar-inscripcion');
    }
}
