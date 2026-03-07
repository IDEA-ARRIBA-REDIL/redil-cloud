<?php

namespace App\Livewire\Carrito;

use Livewire\Component;
use App\Models\AbonoCategoria;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Carbon\Carbon;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\ActividadCarritoCompra;
use App\Models\ActividadCampoAdicionalCompra;
use App\Models\ActividadCategoriaMoneda;
use App\Models\CategoriaActividadCompra;
use App\Models\Moneda;
use App\Models\User;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\Pago;
use App\Models\Inscripcion;
use App\Models\RespuestaElementoFormulario;
use App\Models\ElementoFormularioActividad;
use Livewire\WithFileUploads;

class AbonoCarrito extends Component
{
    use WithFileUploads;

    // Propiedades de navegación y formulario
    public $pasoActual = 1;
    public $totalPasos = 1;
    public $elementosFormulario = [];
    public $respuestas = [];
    // Propiedades públicas del componente
    public $categoriasCompraPermitidas = []; // Categorías de compra permitidas
    public $categoriasCompraPermitidasIds = []; // IDs de categorías permitidas
    public $cantidadItemsActuales = 0; // Contador de items en el carrito
    public $actividad; // Actividad actual
    public $primeraVez; // Bandera para identificar si es la primera vez que se carga el componente
    public $monedaSeleccionada = 1; // Moneda seleccionada para la compra
    public $monedaActual; // Objeto de la moneda actual
    public $carrito = []; // Items en el carrito
    public $relacionesFamiliares = []; // Relaciones familiares del usuario
    public $parientesHabilitados = []; // Parientes habilitados para comprar
    public $cantidad = 1; // Cantidad por defecto para múltiples compras
    public $cantidades = []; // Cantidades por categoría
    public $camposAdicionalesActividad = []; // Campos adicionales de la actividad
    public $camposAdicionalesActividadGuardados = []; // Campos adicionales guardados
    public $usuario; // Usuario actual
    public $parienteSeleccionado; // Pariente seleccionado
    public $camposAdicionalesHtml; // HTML generado para campos adicionales
    public $totalCompra; // Total de la compra
    public $destinatario; // Destinatario de la compra
    public $compraActual; // Compra actual (si existe)
    public $camposAdicionales = []; // Respuestas de campos adicionales
    public $destinatarioSeleccionado; // Destinatario seleccionado
    public $configuracion; // Configuración del sistema
    public $mensajeAbono; // Mensaje relacionado con abonos
    public $valorAbono; // Valor del abono
    public $pagosAbonoCompra; // Pagos relacionados con abonos
    public $mostrarInputAbono; // Bandera para mostrar el input de abono
    public $valorMinimoAbonoParaCategoria = 0; // Valor mínimo del abono para la categoría
    public $valorAbonoRetorno; // Valor de retorno del abono
    public $valorMinimoAbonoParaCategoriaRetorno = 0; // Valor mínimo de retorno del abono
    public $valorTotalAbonado = 0; // Total abonado
    public $categoriaAbonoSeleccionada; // Categoría de abono seleccionada
    public $pagosCompraUsuario; // Pagos del usuario
    public $mensajito; // Mensaje adicional
    public $pagoActual; // Pago actual
    public $valorPagosCompra; // Valor total de los pagos
    public $carritoActual; // Carrito actual
    public $compraNueva; // Nueva compra
    public $actualizar = false; // Bandera para actualizar
    public $pagoCompraActual; // Pago de la compra actual
    public $monedaNueva; // Nueva moneda seleccionada
    public $fechaHoy; // Fecha actual
    public $abonosFinalizados = false; // Bandera para abonos finalizados
    public $pagosAnteriores = [];


    // ANOTACIÓN 18 DE MARZO: ES NECESARIO CUANDO LA TAQUILLA ESTE LISTA QUE SE HAGAN LAS VALIDACIONES DE LOS PAGOS QUE TENGAN ESTADOS FINALIZADOS TRUE,
    /// Y QUE LOS ESTADOS PAGOS SEAN LOS CORRECTOS, SI TIENE VARIOS ABONOS PERO NO ESTAN FINALIZADOS OK, Y SE COMPROBO QUE LOS PAGOS FUERON FINALIZADOS ENTONCES NO SE DEBEN LISTAR
    /// ESTA VALIDACIÓN ESTA PENDIENTE Y DEBE HACERSE EN TODOS LOS PROCESOS DE ABONOS.

    /**
     * Método mount: Se ejecuta al inicializar el componente.
     *
     * @param Actividad $actividad Actividad actual.
     * @param mixed $compraActual Compra existente (si aplica).
     * @param bool $primeraVez Indica si es la primera carga del componente.
     */




    public function mount(Actividad $actividad, $compraActual, $primeraVez)
    {
        $this->primeraVez = $primeraVez;
        $this->actividad = $actividad;
        $this->configuracion = Configuracion::first();
        $this->fechaHoy = Carbon::now()->format('Y-m-d');
        $this->compraActual = $compraActual;

        $this->cargarCategoriasPermitidas();


        // --- INICIO DE LA CORRECCIÓN ---
        // Se añade la validación para redirigir si no hay categorías permitidas.
        // Esta comprobación se hace después de cargar las categorías.
        if ($this->categoriasCompraPermitidas->isEmpty()) {
            // Se define el mensaje de error.
            $errorMessage = 'No cumples con los requisitos para acceder a ninguna de las categorías de esta actividad. Por favor, comunícate con soporte o con tu pastor.';

            // Se redirige al usuario de vuelta a la página de perfil de la actividad con el mensaje.
            return redirect()->route('actividades.perfil', ['actividad' => $this->actividad->id])->with('error', $errorMessage);
        }
        // --- FIN DE LA CORRECCIÓN ---
        $this->cargarRelacionesFamiliares();

        if (isset($compraActual->id)) {

            $this->pagosAnteriores = Pago::where('compra_id', $compraActual->id)->get();
            $this->cargarCarritoDesdeCompra($compraActual);

            // --- INICIO DE LA CORRECCIÓN DEFINITIVA ---
            if (!$this->primeraVez) {


                // 1. Buscamos el último pago realizado para esta compra.
                $ultimoPago = Pago::where('compra_id', $compraActual->id)->latest()->first();
                $this->primeraVez = $ultimoPago;
                // 2. Si existe un pago y tiene una categoría asociada, ¡esa es la que buscamos!
                if ($ultimoPago && $ultimoPago->actividad_categoria_id) {
                    // 3. Establecemos la categoría "bloqueada" y precargamos su información.
                    $this->categoriaAbonoSeleccionada = $ultimoPago->actividad_categoria_id;
                    $this->actualizarCategoriaAbonoSeleccionada();
                } else {
                    // Si no hay pagos, o los pagos no tienen categoría, no seleccionamos nada.
                    // Esto mantiene el comportamiento esperado para compras sin pagos previos.
                    \Log::info("Compra {$compraActual->id} cargada sin pagos previos con categoría.");
                }
            }
            // --- FIN DE LA CORRECCIÓN DEFINITIVA ---

        } else {

            $this->primeraVez = 'asi no ted';
            $this->compraActual = new Compra;
            $this->carrito = [];
        }

        $this->inicializarMoneda();
        $this->cargarCamposAdicionales();

        // --- INICIALIZACIÓN DE FORMULARIO DINÁMICO ---
        $this->elementosFormulario = $this->actividad->elementos()
            ->where('visible', true)
            ->orderBy('orden')
            ->get();

        $tienePreguntas = $this->elementosFormulario->where('tipo_elemento_id', '!=', 1)->count() > 0;
        $this->totalPasos = $tienePreguntas ? 2 : 1;

        if (isset($compraActual->id)) {
            $this->cargarRespuestasExistentes();
        }
    }

    private function cargarRespuestasExistentes()
    {
        if (!$this->compraActual || !isset($this->compraActual->id)) return;

        $respuestasGuardadas = RespuestaElementoFormulario::where('compra_id', $this->compraActual->id)->get();
        foreach ($respuestasGuardadas as $resp) {
            $tipoClase = $resp->elemento->tipoElemento->getRawOriginal('clase') ?? $resp->elemento->tipoElemento->clase;
            switch ($tipoClase) {
                case 'corta': $valor = $resp->respuesta_texto_corto; break;
                case 'larga': $valor = $resp->respuesta_texto_largo; break;
                case 'si_no': $valor = $resp->respuesta_si_no; break;
                case 'unica_respuesta': $valor = $resp->respuesta_unica; break;
                case 'multiple_respuesta': $valor = explode(',', $resp->respuesta_multiple); break;
                case 'fecha': $valor = $resp->respuesta_fecha; break;
                case 'numero': $valor = $resp->respuesta_numero; break;
                case 'moneda': $valor = $resp->respuesta_moneda; break;
                case 'archivo': $valor = $resp->url_archivo; break;
                case 'imagen': $valor = $resp->url_foto; break;
                default: $valor = null;
            }
            $this->respuestas[$resp->elemento_formulario_actividad_id] = $valor;
        }
    }

    private function cargarCarritoDesdeCompra($compraActual)
    {
        if ($this->primeraVez) {
            $this->carrito = [];
        } else {
            $this->pagoActual = Pago::where('compra_id', $compraActual->id)
                ->where('moneda_id', $this->compraActual->moneda_id)
                ->where('fecha', Carbon::now()->format('Y-m-d'))
                ->first();

            $this->carrito = $compraActual->carritos->mapWithKeys(function ($item) {
                return [
                    $item->actividad_categoria_id => [
                        'id' => $item->actividad_categoria_id,
                        'nombre' => $item->categoria->nombre,
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        // CORRECCIÓN 1: Usar el operador nullsafe (?->) para evitar error si no se encuentra la moneda.
                        'moneda' => $item->categoria->monedas()->wherePivot('moneda_id', $this->monedaSeleccionada)->first()?->nombre_corto ?? 'Gratis',
                        'compra_id' => $this->compraActual->id,
                        // CORRECCIÓN 2: Verificar si $this->pagoActual existe antes de acceder a su id.
                        'pago_id' => $this->pagoActual ? $this->pagoActual->id : null,
                        'actividad_carrito_compra_id' => $item->id
                    ]
                ];
            })->toArray();
        }

        $this->destinatario = $compraActual->destinatario_id;
        $this->camposAdicionalesActividadGuardados = $compraActual->camposAdicionales;
        $this->camposAdicionales = $compraActual->camposAdicionales->pluck('respuesta', 'campo_adicional_id')->toArray();
    }

    private function cargarRelacionesFamiliares()
    {
        if (auth()->check()) {
            $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
            $this->usuario = User::find($rolActivo->pivot->model_id);

            $this->relacionesFamiliares = $this->usuario
                ->parientesDelUsuario()
                ->leftJoin('tipos_parentesco', 'parientes_usuarios.tipo_pariente_id', '=', 'tipos_parentesco.id')
                ->select(
                    'users.id',
                    'users.foto',
                    'users.identificacion',
                    'users.primer_nombre',
                    'users.segundo_nombre',
                    'users.primer_apellido',
                    'users.segundo_apellido',
                    'users.tipo_identificacion_id',
                    'tipos_parentesco.nombre as nombre_parentesco',
                    'tipos_parentesco.nombre_masculino',
                    'tipos_parentesco.nombre_femenino',
                    'parientes_usuarios.es_el_responsable',
                    'parientes_usuarios.id'
                )
                ->get();

            foreach ($this->relacionesFamiliares as $pariente) {
                if ($this->actividad->restriccion_por_categoria) {
                    $disponibles = $this->actividad->categoriasDisponiblesParaUsuario($pariente->pivot->pariente_user_id);
                    $parienteHabilitado = $disponibles->isNotEmpty();
                } else {
                    $parienteHabilitado = $this->actividad->validarAsistenciaActividad($this->actividad->id, $pariente->pivot->pariente_user_id);
                }
                
                if ($parienteHabilitado) {
                    array_push($this->parientesHabilitados, $pariente->pivot->pariente_user_id);
                }
            }

            $this->relacionesFamiliares = $this->usuario
                ->parientesDelUsuario()
                ->leftJoin('tipos_parentesco', 'parientes_usuarios.tipo_pariente_id', '=', 'tipos_parentesco.id')
                ->whereIn('users.id', $this->parientesHabilitados)
                ->select(
                    'users.id',
                    'users.foto',
                    'users.identificacion',
                    'users.primer_nombre',
                    'users.segundo_nombre',
                    'users.primer_apellido',
                    'users.segundo_apellido',
                    'users.tipo_identificacion_id',
                    'tipos_parentesco.nombre as nombre_parentesco',
                    'tipos_parentesco.nombre_masculino',
                    'tipos_parentesco.nombre_femenino',
                    'parientes_usuarios.es_el_responsable',
                    'parientes_usuarios.id'
                )
                ->get();
        }
    }

    private function cargarCategoriasPermitidas()
    {
        if ($this->actividad->tipo->requiere_inicio_sesion && auth()->check()) {
            // Unificamos categorías permitidas: las del usuario + las de sus parientes relacionados
            $categoriasUsuario = $this->actividad->categoriasDisponiblesParaUsuario(auth()->user()->id);
            
            // También revisamos qué categorías están disponibles para sus parientes
            $parientes = auth()->user()->parientesDelUsuario()->get();
            $categoriasParientesIds = collect();
            
            foreach($parientes as $pariente) {
                $disponibles = $this->actividad->categoriasDisponiblesParaUsuario($pariente->id);
                $categoriasParientesIds = $categoriasParientesIds->merge($disponibles->pluck('id'));
            }

            $todosLosIds = $categoriasUsuario->pluck('id')->merge($categoriasParientesIds)->unique();
            
            $this->categoriasCompraPermitidas = $this->actividad->categorias()
                ->whereIn('id', $todosLosIds)
                ->get();

        } else {
            $this->categoriasCompraPermitidas = $this->actividad->categorias;
        }

        foreach ($this->actividad->categorias as $categoria) {
            $this->cantidades[$categoria->id] = 1;
        }
    }

    private function inicializarMoneda()
    {
        if (empty($this->monedaSeleccionada)) {
            $this->monedaSeleccionada = $this->actividad->monedas()->first()->id ?? 0;
        }
        $this->monedaActual = Moneda::find($this->monedaSeleccionada) ?? new Moneda();
    }

    private function cargarCamposAdicionales()
    {
        if (count($this->actividad->camposAdicionales) > 0) {
            $this->camposAdicionalesActividad = $this->actividad->camposAdicionales;
        }
        $this->camposAdicionalesHtml = $this->obtenerCamposAdicionalesHtml();
    }

    public function obtenerCamposAdicionalesHtml()
    {
        $html = '<div class="card shadow border-top-0 border-1c p-5 rounded"> 
                     <div class="card-header p-0">
                     <h5 class="fw-semibold">Campos adicionales actividades</h5>
                     </div>
                     <div class="card-body p-0"> 
                     <div class="row">';
        foreach ($this->camposAdicionalesActividad as $campo) {
            $respuestaCampo = null;
            foreach ($this->camposAdicionalesActividadGuardados as $campoRes) {
                if ($campoRes->campo_adicional_id == $campo->id) {
                    $respuestaCampo = $campoRes->respuesta;
                }
            }
            $html .= '<div wire:ignore class="form-group col-sm-12 col-md-6 mb-2">';
            $html .= '<label class="form-label">' . $campo->nombre . '</label>';
            $html .= '<input placeholder="ingresa la información" wire:model="camposAdicionales.' . $campo->id . '" name="campoAd-' . $campo->id . '" type="text" class="form-control" value="' . $respuestaCampo . '">';
            $html .= '</div>';
        }
        $html .= '</div></div></div>';
        return $html;
    }

    public function eliminarDelCarritoAbono($categoriaId)
    {
        // 1. Validar que tengamos una compra activa y una categoría para buscar.
        if (!$this->compraActual || !$categoriaId) {
            $this->dispatch('mostrarMensaje', ['mensaje' => 'No se pudo encontrar la compra para eliminar el item.', 'tipo' => 'error']);
            return;
        }

        // 2. Iniciar una transacción de base de datos para seguridad.
        DB::beginTransaction();

        try {
            // 3. Buscar el item temporal en la base de datos (el más reciente, por si acaso).
            $carritoItem = ActividadCarritoCompra::where('compra_id', $this->compraActual->id)
                ->where('actividad_categoria_id', $categoriaId)
                ->latest()
                ->first();

            // 4. Si el registro del carrito existe, proceder con la eliminación.
            if ($carritoItem) {
                $pagoIdParaEliminar = $carritoItem->pago_id;

                // --- INICIO DE LA CORRECCIÓN CLAVE ---
                // 5. Eliminar el registro del PAGO asociado, si tiene un ID.
                if ($pagoIdParaEliminar) {
                    Pago::find($pagoIdParaEliminar)->delete();
                }
                // --- FIN DE LA CORRECCIÓN CLAVE ---

                // 6. Eliminar el registro del CARRITO temporal.
                $carritoItem->delete();
            }

            // 7. Quitar el item del array `$carrito` del componente para actualizar la vista.
            if (isset($this->carrito[$categoriaId])) {
                unset($this->carrito[$categoriaId]);
            }

            // 8. Si todo sale bien, confirmar los cambios en la base de datos.
            DB::commit();

            // 9. Mostrar mensaje de éxito al usuario.
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "El abono ha sido quitado del carrito y el pago asociado ha sido revertido.",
                'tipo' => 'success'
            ]);

            // 10. Recalcular la información de abonos para actualizar los mensajes en la vista.
            $this->actualizarCategoriaAbonoSeleccionada();
        } catch (\Exception $e) {
            // 11. En caso de cualquier error, revertir todos los cambios.
            DB::rollBack();
            Log::error("Error al eliminar abono y pago: " . $e->getMessage());
            $this->dispatch('mostrarMensaje', ['mensaje' => 'Ocurrió un error al intentar eliminar el abono.', 'tipo' => 'error']);
        }
    }



    public function eliminarDelCarritoAbonoRetorno($categoriaId, $carritoActual)
    {

        if (isset($this->pagoActual->id)) {
            // 1. Eliminar el pago específico
            $this->pagoActual->valor = 0; // Eliminar físicamente el registro
            $this->pagoActual->save();
            // 2. Recalcular total abonado sin este pago
            $totalAbonado = Pago::where('compra_id', $this->compraActual->id)
                ->where('moneda_id', $this->monedaSeleccionada)
                ->sum('valor');

            // 3. Obtener el valor total de la categoría
            $categoria = ActividadCategoria::find($this->categoriaAbonoSeleccionada);
            $valorTotal = $categoria->monedas()
                ->wherePivot('moneda_id', $this->monedaSeleccionada)
                ->first()->pivot->valor;

            // 4. Calcular nuevo mínimo (lo que falta por pagar)
            $this->valorMinimoAbonoParaCategoriaRetorno = $valorTotal - $totalAbonado;
            $this->valorAbonoRetorno = $this->valorMinimoAbonoParaCategoriaRetorno;

            // 5. Actualizar mensaje
            $moneda = Moneda::find($this->monedaSeleccionada);
            $this->mensajeAbono = "Valor restante: "
                . Number::currency($this->valorMinimoAbonoParaCategoriaRetorno)
                . $moneda->nombre_corto;
        }


        if (isset($this->carrito[$categoriaId])) {
            unset($this->carrito[$categoriaId]);


            // Puedes mostrar un mensaje al usuario si lo deseas
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "Abono eliminado del carrito.",
                'tipo' => 'success'
            ]);
        }

        /*
            
            $this->valorMinimoAbonoParaCategoriaRetorno= $valorMinimo;
            $this->valorAbonoRetorno= $valorMinimo;
            // $this->valorMinimoAbonoParaCategoria= $pagosCompra

          
            
          if (isset($this->carrito[$categoriaId])) {
            unset($this->carrito[$categoriaId]);
        

            // Puedes mostrar un mensaje al usuario si lo deseas
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "Abono eliminado del carrito.",
                'tipo' => 'success'
            ]);
        }
        */
    }
    private function calcularLimitesAbono($categoriaId)
    {
        $categoria = ActividadCategoria::find($categoriaId);

        // 1. Valor total de la categoría
        $valorTotal = $categoria->monedas()
            ->wherePivot('moneda_id', $this->monedaSeleccionada)
            ->first()->pivot->valor;

        // 2. Total abonado (excluyendo el pago eliminado)
        $totalAbonado = Pago::where('compra_id', $this->compraActual->id)
            ->where('moneda_id', $this->monedaSeleccionada)
            ->sum('valor');

        return [
            'min' => $valorTotal - $totalAbonado, // Restante es el mínimo requerido
            'max' => $valorTotal - $totalAbonado // Máximo igual al restante
        ];
    }

    public function agregarAlCarritoAbono($categoriaId)
    {
        $categoria = ActividadCategoria::find($categoriaId);

        // Validar que el valor del abono no sea menor al mínimo permitido
        if ($this->actividad->pagos_abonos_con_valores_cerrados) {
            if ($this->valorAbono != $this->valorMinimoAbonoParaCategoria) {
                $this->dispatch('mostrarMensaje', [
                    'mensaje' => "Para esta actividad el abono debe ser exactamente: " . Number::currency($this->valorMinimoAbonoParaCategoria),
                    'tipo' => 'error'
                ]);
                return;
            }
        } elseif ($this->valorAbono < $this->valorMinimoAbonoParaCategoria) {
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "El valor del abono no puede ser menor a " . Number::currency($this->valorMinimoAbonoParaCategoria),
                'tipo' => 'error'
            ]);
            return;
        }

        // Validar si la categoría ya existe en el carrito (opcional)
        if (isset($this->carrito[$categoriaId])) {
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "Ya has agregado un abono para la categoría '{$categoria->nombre}' al carrito.",
                'tipo' => 'warning'
            ]);
            return;
        }

        // Obtener el valor total de la categoría en la moneda seleccionada
        $valorTotalCategoria = $categoria->monedas()
            ->wherePivot('moneda_id', $this->monedaSeleccionada)
            ->first()->pivot->valor;

        // Calcular el máximo permitido
        $maximoPermitido = $valorTotalCategoria; // Por defecto, el máximo es el valor total de la categoría

        // Si hay abonos previos, ajustar el máximo permitido
        $totalAbonado = Pago::where('compra_id', $this->compraActual->id)
            ->where('moneda_id', $this->monedaSeleccionada)
            ->sum('valor');

        if ($totalAbonado > 0) {
            $maximoPermitido = $valorTotalCategoria - $totalAbonado;
        }

        // Validar que el valor del abono no supere el máximo permitido
        if ($this->valorAbono > $maximoPermitido) {
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "El valor del abono no puede superar: " . Number::currency($maximoPermitido),
                'tipo' => 'warning'
            ]);
            return;
        }

        // Obtener la moneda y su nombre_corto
        $monedaCategoria = $categoria->monedas()
            ->wherePivot('moneda_id', $this->monedaSeleccionada)
            ->first();

        $nombreMoneda = $monedaCategoria->nombre_corto ?? 'Gratis';

        // Agregar al carrito
        $this->carrito[$categoriaId] = [
            'id' => $categoria->id,
            'nombre' => $categoria->nombre . ' (Abono)', // Indicar que es un abono
            'cantidad' => 1, // La cantidad para abonos siempre es 1
            'precio' => $this->valorAbono, // Usar el valor del input
            'moneda' => $nombreMoneda
        ];

        // Actualizar el total abonado
        $this->valorPagosCompra = Pago::where('compra_id', $this->compraActual->id)
            ->where('moneda_id', $this->monedaSeleccionada)
            ->sum('valor');

        $this->valorPagosCompra += $this->valorAbono;

        // Mostrar mensaje de éxito
        $this->mensajeAbono = 'Tu carrito fue agregado exitosamente.';
        $this->dispatch('mostrarMensaje', [
            'mensaje' => "Abono agregado al carrito.",
            'tipo' => 'success'
        ]);
    }

    public function agregarAlCarritoAbonoRetorno($categoriaId)
    {
        $categoria = ActividadCategoria::find($categoriaId);

        $limites = $this->calcularLimitesAbono($categoriaId);

        // Validar que el valor del abono no sea menor al mínimo permitido
        if ($this->valorAbonoRetorno != $limites['min']) {
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "Debes abonar exactamente: " . Number::currency($limites['min']),
                'tipo' => 'error'
            ]);
            return;
        }


        // Validar que el valor del abono no sea menor al mínimo permitido
        if ($this->valorAbonoRetorno < $this->valorMinimoAbonoParaCategoriaRetorno) {
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "El valor del abono no puede ser menor a " . $this->valorMinimoAbonoParaCategoriaRetorno,
                'tipo' => 'error'
            ]);
            return;
        }

        // Validar si la categoría ya existe en el carrito (opcional)
        if (isset($this->carrito[$categoriaId])) {
            $this->dispatch('mostrarMensaje', [
                'mensaje' => "Ya has agregado un abono para la categoría '{$categoria->nombre}' al carrito.",
                'tipo' => 'warning'
            ]);
            return;
        }

        // Obtener la moneda y su nombre_corto
        $monedaCompra = Moneda::find($this->monedaSeleccionada);




        // Agregar al carrito

        $this->carrito[$categoriaId] =
            [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre . ' (Abono)', // Indicar que es un abono
                'cantidad' => 1, // La cantidad para abonos siempre es 1
                'precio' => $this->valorAbonoRetorno, // Usar el valor del input
                'moneda' => $monedaCompra->nombre_corto
            ];
        $this->carritoActual = json_encode($this->carrito);

        //$this->mensajito=$this->carrito[$categoriaId];

        // Resetear el valor del abono (opcional)

        $this->valorPagosCompra = Pago::where('compra_id', $this->compraActual->id)->where('moneda_id', $monedaCompra->id)->sum('valor');

        $this->valorPagosCompra = ($this->valorPagosCompra + $this->valorAbonoRetorno) - ($categoria->abonos()->where('moneda_id', '=', $this->compraActual->moneda_id)->sum('valor'));

        $this->actualizar = true;
        if ($this->valorPagosCompra != 0) {
        } else {
            $this->mensajeAbono = 'Se completo la totalidad de los abonos.';
        }

        // Puedes mostrar un mensaje al usuario si lo deseas
        $this->dispatch('mostrarMensaje', [
            'mensaje' => "Abono agregado al carrito.",
            'tipo' => 'success'
        ]);
    }

    public function actualizarCategoriaAbonoSeleccionada()
    {
        $fechaActual = Carbon::now();
        $this->mensajeAbono = '';
        $this->valorMinimoAbonoParaCategoria = 0;
        $this->valorMinimoAbonoParaCategoriaRetorno = 0;

        // Obtener la categoría seleccionada
        $categoriaSeleccionada = ActividadCategoria::find($this->categoriaAbonoSeleccionada);
        if (!$categoriaSeleccionada) {
            $this->mensajeAbono = '<p class="mb-1">Categoría no encontrada.</p>';
            return;
        }

        // Obtener el valor total de la categoría en la moneda seleccionada
        $valorTotalCategoria = ActividadCategoriaMoneda::where('actividad_categoria_id', $categoriaSeleccionada->id)
            ->where('moneda_id', $this->monedaSeleccionada)
            ->value('valor');

        // Obtener todos los abonos de la categoría en la moneda seleccionada
        $abonosCategoria = AbonoCategoria::where('actividad_categoria_id', $categoriaSeleccionada->id)
            ->where('moneda_id', $this->monedaSeleccionada)
            ->with('abono')
            ->get();

        // Calcular el valor mínimo (suma de abonos vigentes o pasados)
        $valorMinimo = 0;
        $fechaLimiteAbonoActual = '';

        foreach ($abonosCategoria as $abonoCat) {
            // Verificar si la fecha de inicio del abono ya pasó
            if ($fechaActual >= $abonoCat->abono->fecha_inicio) {
                // Si la fecha de inicio ya pasó, sumar el valor del abono
                $valorMinimo += $abonoCat->valor;
                $fechaLimiteAbonoActual = $abonoCat->abono->fecha_fin;
            }
        }

        // Obtener el total abonado por el usuario en esta categoría
        $totalAbonado = Pago::where('compra_id', $this->compraActual->id)
            ->where('moneda_id', $this->monedaSeleccionada)
            ->sum('valor');

        // Calcular el mínimo y máximo permitido para ambos casos
        $this->valorMinimoAbonoParaCategoria = max(0, $valorMinimo - $totalAbonado);
        $this->valorMinimoAbonoParaCategoriaRetorno = $this->valorMinimoAbonoParaCategoria;

        // Asignar valores a las variables de abono
        $this->valorAbono = $this->valorMinimoAbonoParaCategoria;
        $this->valorAbonoRetorno = $this->valorMinimoAbonoParaCategoriaRetorno;

        // Obtener la moneda seleccionada
        $monedaSeleccionadaActual = $this->monedaNueva ?? Moneda::find($this->monedaSeleccionada);

        // Construir el mensaje
        $valorAbonoMoneda = Number::currency($this->valorMinimoAbonoParaCategoria);

        $this->mensajeAbono .= '<p class="mb-1">Fecha límite para este abono: ' . $fechaLimiteAbonoActual . '</p>';

        // Si el usuario tiene abonos registrados, mostrarlos
        if ($totalAbonado > 0) {
            $pagosUsuario = Pago::where('compra_id', $this->compraActual->id)
                ->where('moneda_id', $this->monedaSeleccionada)
                ->get();

            foreach ($pagosUsuario as $pago) {
                $valorPagoMoneda = Number::currency($pago->valor);
                $this->mensajeAbono .= '<p class="mb-1">Tienes un abono registrado por valor de:<b> ' . $valorPagoMoneda . $monedaSeleccionadaActual->nombre_corto . '  </b> en la fecha ' . $pago->fecha . '</p>';
            }
        }

        $this->mensajeAbono .= '<p class="mb-1">Valor mínimo de abono: ' . $valorAbonoMoneda . $monedaSeleccionadaActual->nombre_corto . '</p>';
    }

    public function actualizarCategoriaAbonoSeleccionadaViejito()
    {
        $fechaActual = Carbon::now();
        $mensajeAbono = '';

        $categoriaActivdadSeleccionada = ActividadCategoria::find($this->categoriaAbonoSeleccionada);
        $fechaLiminiteAbonoActual = '';
        $this->mensajeAbono = '';
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $this->usuario = User::find($rolActivo->pivot->model_id);

        $this->valorMinimoAbonoParaCategoriaRetorno = 0;

        //REGISTROS DE COMPRAS Y PAGOS DE USUARIO 
        $comprasRegistradasUsuario = $this->usuario->compras()->where('actividad_id', $this->actividad->id)->pluck('id')->toArray();
        $categoriaActividadCompras = CategoriaActividadCompra::where('actividad_categoria_id', $categoriaActivdadSeleccionada->id)->whereIn('compra_id', $comprasRegistradasUsuario)->pluck('compra_id')->toArray();
        $this->pagosCompraUsuario = Pago::whereIn('compra_id', $categoriaActividadCompras)->get();


        //// PRIMERO SE REVISA SI TIENE UN PAGO O COMPRA ASOCIADO


        if ($this->pagosCompraUsuario->count() > 0) {
            // Obtener los abonos de la categoría seleccionada
            $abonosCategoria = AbonoCategoria::where('actividad_categoria_id', $categoriaId)
                ->with('abono') // Cargar la relación con el modelo Abono
                ->where('moneda_id', $this->monedaSeleccionada)
                ->get();

            // Calcular el valor total de la categoría
            $valorTotalCategoria = ActividadCategoriaMoneda::where('actividad_categoria_id', $categoriaId)
                ->where('moneda_id', $this->monedaSeleccionada) // Moneda seleccionada
                ->value('valor');

            /// solo para calcular el valor del abono categoria
            foreach ($abonosCategoria as $abonoCat) {
                // Si la fecha actual es menor o igual a la fecha fin del abono
                if ($fechaActual >= ($abonoCat->abono->fecha_fin)) {
                    $this->valorMinimoAbonoParaCategoria += $abonoCat->valor; // Sumar el valor del abono
                } else {
                    break; // Salir del bucle si la fecha fin ya pasó
                }
            }

            $this->valorAbono = $this->valorMinimoAbonoParaCategoria;
            $this->mensajeAbono .= '<p>Se elimino el carrito de compra </p>';
        } else {
            // Obtener los abonos de la categoría seleccionada
            $abonosCategoria = AbonoCategoria::where('actividad_categoria_id', $categoriaActivdadSeleccionada->id)
                ->with('abono') // Cargar la relación con el modelo Abono
                ->where('moneda_id', $this->monedaSeleccionada)
                ->get();

            // Calcular el valor total de la categoría
            $valorTotalCategoria = ActividadCategoriaMoneda::where('actividad_categoria_id', $categoriaActivdadSeleccionada->id)
                ->where('moneda_id', $this->monedaSeleccionada) // Moneda seleccionada
                ->value('valor');

            /// solo para calcular el valor del abono categoria
            foreach ($abonosCategoria as $abonoCat) {
                // Si la fecha actual es menor o igual a la fecha fin del abono
                if ($fechaActual >= ($abonoCat->abono->fecha_fin)) {
                    $this->valorMinimoAbonoParaCategoriaRetorno += $abonoCat->valor; // Sumar el valor del abono
                    $fechaLiminiteAbonoActual = $abonoCat->abono->fecha_fin;
                } else {

                    break; // Salir del bucle si la fecha fin ya pasó
                }
            }
            $this->mensajito = $this->monedaNueva;
            if (isset($this->monedaNueva->id)) {
                $monedaSeleccionadaActual = $this->monedaNueva;
            } else {

                $monedaSeleccionadaActual = Moneda::find($this->monedaSeleccionada);
            }


            $this->valorAbonoRetorno = $this->valorMinimoAbonoParaCategoriaRetorno;
            $this->valorAbono = $this->valorMinimoAbonoParaCategoriaRetorno;
            $this->valorMinimoAbonoParaCategoria = $this->valorMinimoAbonoParaCategoriaRetorno;

            $valorAbonoMoneda = Number::currency($this->valorMinimoAbonoParaCategoria);

            $this->mensajeAbono .= '<p class="mb-1"> No tienes abonos o pagos registrados</p>';
            $this->mensajeAbono .= '<p class="mb-1"> Tú valor mínimo de abono es de ' . $valorAbonoMoneda . $monedaSeleccionadaActual->nombre_corto . '</p>';
            $this->mensajeAbono .= '<p class="mb-1"> Fecha limite para este abono: ' . $fechaLiminiteAbonoActual;
        }



        /*
       
        // Obtener el valor mínimo de la moneda
        $valorMinimoMoneda = Moneda::find($this->monedaSeleccionada)->valor_minimo;

        // Validaciones adicionales
        if ($this->valorTotalAbonado < $this->valorMinimoAbono) {
            // Si el total abonado es menor al mínimo del abono actual, 
            // el mínimo será el valor mínimo de la moneda
            $this->valorMinimoAbono = $valorMinimoMoneda;
            $this->mensajeAbono.='Información El valor mínimo del abono es '. $valorMinimoMoneda;
        } else {
            // Si el total abonado es mayor o igual al mínimo del abono actual,
            // el mínimo será el valor mínimo de la moneda y el máximo la diferencia
            $valorMaximoAbono = $valorTotalCategoria - $this->valorTotalAbonado;
            $this->mensajeAbono.='Información El valor mínimo del abono es ' . $valorMinimoMoneda . ' y el máximo es ' . $valorMaximoAbono;
        }

        // Asignar los valores a las variables públicas
        $this->valorAbonosPasados = $this->valorAbonosPasados;
        $this->valorMinimoAbono = $this->valorMinimoAbono;
        */
    }



    public function crearAbono()
    {
        // 1. Validaciones iniciales
        if (empty($this->carrito)) {
            $this->dispatch('mostrarMensaje', ['titulo' => 'Carrito vacío', 'mensaje' => 'Debes agregar un abono al carrito para continuar.', 'tipo' => 'error']);
            return;
        }
        if (count($this->camposAdicionalesActividad) > 0) {
            foreach ($this->camposAdicionalesActividad as $campo) {
                if (empty($this->camposAdicionales[$campo->id])) {
                    $this->dispatch('mostrarMensaje', ['mensaje' => "Por favor, completa el campo: {$campo->nombre}", 'tipo' => 'error']);
                    return;
                }
            }
        }

        // Se inicia una transacción para garantizar la integridad de los datos
        DB::beginTransaction();

        try {
            $compraParaElPago = $this->compraActual;

            // 2. Lógica condicional de Aforo
            // Si no existe una compra previa, es el primer pago y se debe validar y reservar el cupo.
            if (!isset($compraParaElPago->id)) {

                // --- Lógica de Aforo: Se ejecuta SÓLO para compras nuevas ---
                foreach ($this->carrito as $categoriaId => $item) {
                    $categoria = ActividadCategoria::find($categoriaId);

                    // Se calcula el aforo disponible real
                    $aforoDisponible = $categoria->aforo - $categoria->aforo_ocupado;

                    if (!$categoria || $item['cantidad'] > $aforoDisponible) {
                        $mensajeError = $aforoDisponible > 0 ? "Solo quedan {$aforoDisponible} cupos para la categoría: {$categoria->nombre}" : "No hay suficientes cupos para la categoría: {$categoria->nombre}";
                        $this->dispatch('mostrarMensaje', ['mensaje' => $mensajeError, 'tipo' => 'error']);
                        DB::rollBack(); // Se revierte la transacción si no hay cupo
                        return;
                    }

                    // Si hay cupo, se descuenta del aforo incrementando los ocupados
                    $categoria->aforo_ocupado += $item['cantidad'];
                    $categoria->save();
                }
                // --- Fin de la Lógica de Aforo ---

                // Si el aforo es correcto, se crea el nuevo registro de Compra
                if (auth()->check()) {
                    $valorTotalCategoria = ActividadCategoria::find(collect($this->carrito)->first()['id'])
                        ->monedas()
                        ->where('moneda_id', $this->monedaSeleccionada)
                        ->first()->pivot->valor;

                    $compraParaElPago = Compra::create([
                        'user_id' => auth()->id(),
                        'actividad_id' => $this->actividad->id,
                        'moneda_id' => $this->monedaSeleccionada,
                        'fecha' => $this->fechaHoy,
                        'valor' => $valorTotalCategoria, // Se guarda el valor total de la categoría, no del abono
                        'estado' => 1, // Iniciada
                        'metodo_pago_id' => 1,
                        'nombre_completo_comprador' => $this->usuario->primer_nombre . ' ' . $this->usuario->primer_apellido,
                        'identificacion_comprador' => $this->usuario->identificacion,
                        'telefono_comprador' => $this->usuario->telefono_movil,
                        'email_comprador' => $this->usuario->email,
                        'pariente_usuario_id' => ($this->usuario->id == $this->parienteSeleccionado) ? null : $this->parienteSeleccionado,
                        'destinatario_id' => $this->destinatario ?? null,
                    ]);
                    $this->compraActual = $compraParaElPago;

                    // --- CREACIÓN DE LA INSCRIPCIÓN ---
                    Inscripcion::create([
                        'user_id' => $this->parienteSeleccionado ?? auth()->id(),
                        'actividad_categoria_id' => $categoriaId,
                        'compra_id' => $compraParaElPago->id,
                        'fecha' => $this->fechaHoy,
                        'estado' => $this->actividad->estado_inscripcion_defecto ?? 0,
                        'nombre_inscrito' => $compraParaElPago->nombre_completo_comprador,
                        'email' => $compraParaElPago->email_comprador,
                        'limite_invitados' => 0
                    ]);
                } else {
                    throw new \Exception("El usuario debe estar autenticado para crear una compra de abono.");
                }
            }

            // 3. Creación o Actualización del Pago (EVITAR DUPLICADOS)
            $itemDelCarrito = collect($this->carrito)->first();
            $categoriaIdDelCarrito = $itemDelCarrito['id'] ?? null;
            $valorDelAbono = $itemDelCarrito['precio'] ?? 0;

            $pago = Pago::updateOrCreate([
                'compra_id' => $compraParaElPago->id,
                'estado_pago_id' => 1, // 'Iniciado'
                'fecha' => $this->fechaHoy,
            ], [
                'actividad_categoria_id' => $categoriaIdDelCarrito,
                'moneda_id' => $this->monedaSeleccionada,
                'valor' => $valorDelAbono,
            ]);

            // 4. Se crea o actualiza el registro temporal del carrito (EVITAR DUPLICADOS)
            ActividadCarritoCompra::updateOrCreate([
                'compra_id' => $compraParaElPago->id,
                'user_id' => auth()->id(),
            ], [
                'actividad_id' => $this->actividad->id,
                'actividad_categoria_id' => $categoriaIdDelCarrito,
                'cantidad' => 1,
                'precio' => $valorDelAbono,
                'pago_id' => $pago->id,
                'fecha' => $this->fechaHoy
            ]);

            // 5. Lógica de Campos Adicionales y Redirección
            if (count($this->camposAdicionales) > 0) {
                foreach ($this->camposAdicionales as $campoId => $respuesta) {
                    ActividadCampoAdicionalCompra::create([
                        'actividad_id' => $this->actividad->id,
                        'compra_id' => $compraParaElPago->id,
                        'campo_adicional_id' => $campoId,
                        'respuesta' => $respuesta,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            DB::commit(); // Se confirman todos los cambios si no hubo errores

            // --- Lógica de Navegación de Pasos ---
            if ($this->pasoActual == 1 && $this->totalPasos > 1) {
                $this->pasoActual = 2;
                $this->dispatch('mostrarMensaje', ['mensaje' => 'Abono agregado. Por favor completa el formulario.', 'tipo' => 'success']);
                return;
            }

            // Si no hay formulario o ya estamos en el paso final, guardamos respuestas y redirigimos
            $this->finalizarProcesoAbono($compraParaElPago);

        } catch (\Exception $e) {
            DB::rollBack(); // Se revierten los cambios en caso de cualquier error
            Log::error('Error al crear abono: ' . $e->getMessage());
            $this->dispatch('mostrarMensaje', [
                'titulo' => '¡Error Inesperado!', 
                'mensaje' => 'Error técnico: ' . $e->getMessage(), 
                'tipo' => 'error'
            ]);
        }
    }

    public function finalizarProcesoAbono($compra = null)
    {
        $compra = $compra ?? $this->compraActual;

        if (!$compra || !isset($compra->id)) {
            $this->dispatch('mostrarMensaje', ['mensaje' => 'No se encontró la compra para finalizar.', 'tipo' => 'error']);
            return;
        }

        DB::beginTransaction();
        try {
            // --- VALIDACIÓN DE CAMPOS OBLIGATORIOS ---
            foreach ($this->elementosFormulario as $elemento) {
                if ($elemento->required && $elemento->tipo_elemento_id != 1) {
                    $valor = $this->respuestas[$elemento->id] ?? null;
                    if (empty($valor)) {
                        $this->dispatch('mostrarMensaje', [
                            'mensaje' => "El campo \"{$elemento->titulo}\" es obligatorio.",
                            'tipo' => 'error'
                        ]);
                        DB::rollBack();
                        return;
                    }
                }
            }

            // Guardar respuestas del formulario
            $inscripcion = Inscripcion::where('compra_id', $compra->id)->first();

            foreach ($this->respuestas as $elementoId => $valor) {
                if (empty($valor)) continue;

                $elemento = ElementoFormularioActividad::find($elementoId);
                if (!$elemento) continue;

                $respuesta = RespuestaElementoFormulario::updateOrCreate([
                    'compra_id' => $compra->id,
                    'elemento_formulario_actividad_id' => $elementoId,
                ], [
                    'inscripcion_id' => $inscripcion?->id,
                    'user_id' => $compra->pariente_usuario_id ?: $compra->user_id
                ]);

                switch ($elemento->tipoElemento->getRawOriginal('clase') ?? $elemento->tipoElemento->clase) {
                    case 'corta': $respuesta->respuesta_texto_corto = $valor; break;
                    case 'larga': $respuesta->respuesta_texto_largo = $valor; break;
                    case 'si_no': $respuesta->respuesta_si_no = $valor; break;
                    case 'unica_respuesta': $respuesta->respuesta_unica = $valor; break;
                    case 'multiple_respuesta': $respuesta->respuesta_multiple = is_array($valor) ? implode(",", $valor) : $valor; break;
                    case 'fecha': $respuesta->respuesta_fecha = $valor; break;
                    case 'numero': $respuesta->respuesta_numero = $valor; break;
                    case 'moneda': $respuesta->respuesta_moneda = $valor; break;
                    case 'archivo':
                        if ($valor instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                            $directorio = $this->configuracion->ruta_almacenamiento . '/archivos/actividades/';
                            $nombreArchivo = time() . '_' . preg_replace('/[^A-Za-z0-9.\-\_]/', '', $valor->getClientOriginalName());
                            $valor->storeAs($directorio, $nombreArchivo, 'public');
                            $respuesta->url_archivo = $nombreArchivo;
                        }
                        break;
                    case 'imagen':
                        if ($valor instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                            $directorio = $this->configuracion->ruta_almacenamiento . '/img/respuestas-formulario/';
                            $nombreFoto = 'img_' . time() . '_' . $valor->getClientOriginalName();
                            $valor->storeAs($directorio, $nombreFoto, 'public');
                            $respuesta->url_foto = $nombreFoto;
                        }
                        break;
                }
                $respuesta->save();
            }

            DB::commit();

            // Redirección final al checkout
            return redirect()->route('carrito.checkout', ['compra' => $compra->id, 'actividad' => $this->actividad]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al finalizar abono: ' . $e->getMessage());
            $this->dispatch('mostrarMensaje', [
                'mensaje' => 'Error al guardar el formulario: ' . $e->getMessage(), 
                'tipo' => 'error'
            ]);
        }
    }

    public function volverPaso()
    {
        if ($this->pasoActual > 1) {
            $this->pasoActual--;
        }
    }

    public function eliminarRespuesta($elementoId)
    {
        if (isset($this->respuestas[$elementoId])) {
            unset($this->respuestas[$elementoId]);
        }
    }

    /// cuando se hace un cambio de moneda hay que ejecutar esto porque debe eliminar lo del carrito y calcular de nuevo las categorias
    public function updatedMonedaSeleccionada($value)
    {
        $this->carrito = [];

        // Obtener el nombre de la moneda seleccionada
        $this->monedaNueva = $this->actividad->monedas->find($value);
        $this->monedaActual = $this->monedaNueva;
        $this->actualizarCategoriaAbonoSeleccionada();

        if ($this->monedaNueva) {
            // Dispara una notificación usando SweetAlert2 o el sistema que prefieras
            $this->dispatch('mostrarMensajeMoneda', [
                'titulo' => 'Cambio de moneda',
                'mensaje' => ' Has cambiado la moneda a:' . $this->monedaNueva->nombre,
                'tipo' => 'success'
            ]);

            // Aquí puedes agregar cualquier otra lógica necesaria
            // Por ejemplo, recalcular precios, actualizar otros componentes, etc.
        }
    }



    // esto me ayuda recorriendo los items del carrito para poder saber cuanto suma todo, es mas facil para al final en el resumen poner un valor, en vez de tener que calcular todo de nuevo
    public function calcularTotal()
    {
        return collect($this->carrito)->sum(function ($item) {

            return $item['precio'] * $item['cantidad'];
        });
    }

    public function actualizarCompra(Compra $compraActualizada, Pago $pagoActual)
    {

        try {

            // Validar que haya items en el carrito
            if (empty($this->carrito)) {
                $this->dispatch('mostrarMensaje', [
                    'titulo' => 'Opps algo sucedió',
                    'mensaje' => 'El carrito está vacío',
                    'tipo' => 'error'
                ]);
                return;
            }

            // Validar campos adicionales
            if (count($this->camposAdicionalesActividad) > 0) {
                foreach ($this->camposAdicionalesActividad as $campo) {
                    if (empty($this->camposAdicionales[$campo->id])) {
                        $this->dispatch('mostrarMensaje', [
                            'mensaje' => "Por favor, completa el campo: {$campo->nombre}",
                            'tipo' => 'error'
                        ]);
                        return; // Detener el proceso si falta algún campo
                    }
                }
            }

            // Bloqueo optimista: obtener el aforo actual
            $aforoActividad = $this->actividad->aforo;
            $aforosCategorias = $this->actividad->categorias->pluck('aforo', 'id');
            $cantidadTotal = collect($this->carrito)->sum('cantidad');
            $this->totalCompra = $this->calcularTotal();

            // Validación de aforo (similar a crearCompra)
            foreach ($this->carrito as $categoriaId => $item) {
                $categoria = ActividadCategoria::find($categoriaId);
                if (!$categoria) {
                    continue;
                }

                if ($categoria->aforo == 0) {
                    $this->dispatch('mostrarMensaje', [
                        'mensaje' => "Lo sentimos, no hay cupos disponibles para la categoría: {$categoria->nombre}",
                        'tipo' => 'error'
                    ]);
                    return;
                }

                if ($item['cantidad'] > $categoria->aforo) {
                    $this->dispatch('mostrarMensaje', [
                        'mensaje' => "Solo quedan {$categoria->aforo_ocupado} cupos disponibles para la categoría: {$categoria->nombre}",
                        'tipo' => 'error'
                    ]);
                    return;
                } else {
                    // Disminuir el aforo de la categoría (bloqueo optimista)
                    $categoria->aforo = $aforosCategorias[$categoria->id] - $item['cantidad'];
                    $categoria->save();
                }
            }
            //...

            // Actualizar la compra principal
            $compraActualizada->moneda_id = $this->monedaSeleccionada;
            $compraActualizada->valor =  $this->totalCompra;
            //... otros campos que se puedan actualizar...
            $compraActualizada->save();

            // Actualizar los items del carrito
            // Primero, eliminar los items existentes
            $compraActualizada->carritos()->delete();
            foreach ($this->carrito as $item) {
                ActividadCarritoCompra::create([
                    'actividad_id' => $this->actividad->id,
                    'actividad_categoria_id' => $item['id'],
                    'compra_id' => $compraActualizada->id,
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'user_id' => $this->usuario ? $this->usuario->id : null,
                    'pago_id' => $pagoActual->id,
                    'fecha' => $this->fechaHoy
                ]);
                $pagoActual->valor = $item['precio'];
                $pagoActual->save();
            }



            // Actualizar los campos adicionales
            // Primero, eliminar los campos existentes
            $compraActualizada->camposAdicionales()->delete();
            if (count($this->camposAdicionales) > 0) {
                foreach ($this->camposAdicionales as $campoId => $respuesta) {
                    ActividadCampoAdicionalCompra::create([
                        'actividad_id' => $this->actividad->id,
                        'compra_id' => $compraActualizada->id,
                        'campo_adicional_id' => $campoId,
                        'respuesta' => $respuesta,
                        'user_id' => $this->usuario ? $this->usuario->id : null,
                    ]);
                }
            }

            // Redireccionar al siguiente paso (similar a crearCompra)
            //...

            $this->dispatch('mostrarMensaje', [
                'titulo' => 'Carrito de compras actualizado',
                'mensaje' => 'Se actualizaron los cambios de tu carrito de compra',
                'tipo' => 'success'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar errores de la base de datos
            DB::rollBack();
            Log::error('Error de base de datos al crear la compra: ' . $e->getMessage());

            $this->dispatch('mostrarMensaje', [
                'titulo' => 'Opps algo sucedió',
                'mensaje' => 'Hubo un error al guardar la compra en la base de datos.' . $e->getMessage() . ' Por favor, intenta nuevamente.',
                'tipo' => 'error'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Capturar errores de validación
            DB::rollBack();
            Log::error('Error de validación al crear la compra: ' . $e->getMessage());

            $this->dispatch('mostrarMensaje', [
                'titulo' => 'Opps algo sucedió',
                'mensaje' => 'Error de validación: ' . $e->validator->errors()->first(),
                'tipo' => 'error'
            ]);
        } catch (\Exception $e) {
            // Capturar cualquier otra excepción
            DB::rollBack();
            Log::error('Error al crear la compra: ' . $e->getMessage());

            $this->dispatch('mostrarMensaje', [
                'titulo' => 'Opps algo sucedió',
                'mensaje' => 'Error: Creando carrito de compras' . $e->getMessage(), // Mostrar el mensaje de error real
                'tipo' => 'error'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.carrito.abono-carrito', [
            'total' => $this->calcularTotal(),
            'camposAdicionalesHtml' => $this->camposAdicionalesHtml,
        ]);
    }
}
