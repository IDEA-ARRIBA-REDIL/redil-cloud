<?php

namespace App\Livewire\Carrito;

use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\ActividadCarritoCompra;
use Illuminate\Support\Facades\Mail;
use App\Mail\InscripcionConfirmacionMail;
use App\Models\ActividadCampoAdicionalCompra;
use App\Models\Inscripcion;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\Moneda;
use App\Models\Pago;
use App\Models\User;
use App\Models\RespuestaElementoFormulario;
use App\Models\ElementoFormularioActividad;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads; // Importamos para manejar archivos e imágenes

class Carrito extends Component
{
    use WithFileUploads;

    // Propiedades del componente
    public Actividad $actividad;
    public ?Compra $compraActual = null;

    // --- PROPIEDADES DE NAVEGACIÓN ENTRE PASOS ---
    public $pasoActual = 1;
    public $totalPasos = 1;
    public $elementosFormulario = []; // Almacenará los elementos del formulario de la actividad

    // --- PROPIEDADES PARA INVITADOS ---
    public $invitados = [];
    public $limiteInvitados = 0;
    public $cantidadInvitados = 0;

    // --- PROPIEDADES DEL CARRITO ---
    public $carrito = [];
    public $categoriasCompraPermitidas = [];
    public $cantidades = [];
    public $monedaSeleccionada;
    public ?Moneda $monedaActual = null;

    // --- PROPIEDADES DEL FORMULARIO INTEGRADO ---
    public $respuestas = []; // Almacenará las respuestas: [elemento_id => valor]
    public $archivosBorrados = []; // Para trackear archivos que el usuario decida eliminar

    // --- PROPIEDADES ADICIONALES Y DE USUARIO ---
    public ?User $usuario = null;
    public $configuracion;
    public $fechaHoy;
    public $relacionesFamiliares = [];
    public $parienteSeleccionado;
    public $destinatario;
    public $camposAdicionales = [];
    public $camposAdicionalesActividad = [];
    public $camposAdicionalesHtml;

    // Datos para inscripción de invitados/no logueados
    public $nombreComprador;
    public $identificacionComprador;
    public $EmailComprador;
    public $telefonoComprador;


    public function rules()
    {
        $rules = [];

        // Aplica estas reglas SOLO si el usuario no ha iniciado sesión Y la actividad lo permite.
        if (!Auth::check() && !$this->actividad->tipo->requiere_inicio_sesion) {
            $rules['nombreComprador'] = 'required|string|min:3';
            $rules['identificacionComprador'] = 'required|string|min:5';
            $rules['EmailComprador'] = 'required|email';
            $rules['telefonoComprador'] = 'required|numeric|digits_between:7,15';
        }

        return $rules;
    }
    public function messages()
    {
        return [
            'nombreComprador.required' => 'El nombre completo es obligatorio.',
            'identificacionComprador.required' => 'El número de identificación es obligatorio.',
            'EmailComprador.required' => 'El correo electrónico es obligatorio.',
            'EmailComprador.email' => 'El formato del correo no es válido.',
            'telefonoComprador.required' => 'El número de teléfono es obligatorio.',
            'telefonoComprador.numeric' => 'El teléfono solo debe contener números.',
            'telefonoComprador.digits_between' => 'El teléfono debe tener entre 7 y 15 dígitos.',
        ];
    }
    /**
     * MÉTODO EDITADO
     * Ahora establece el 'parienteSeleccionado' por defecto al usuario autenticado.
     */
    public function mount(Actividad $actividad, ?int $compraId = null)
    {
        $this->actividad = $actividad;
        $this->configuracion = Configuracion::first();
        $this->fechaHoy = Carbon::now()->format('Y-m-d');

        if ($compraId) {
            $this->compraActual = Compra::find($compraId);
        }

        if (Auth::check()) {
            $this->usuario = Auth::user();
            $this->parienteSeleccionado = $this->usuario->id;
        }

        $this->cargarCategoriasPermitidas();
        $this->cargarRelacionesFamiliares();
        $this->cargarCamposAdicionales();

        // --- LÓGICA DE FORMULARIO INTEGRADO ---
        $this->elementosFormulario = $this->actividad->elementos()
            ->where('visible', true)
            ->orderBy('orden')
            ->get();

        // Determinamos si hay pasos adicionales (tipo_elemento 1 es encabezado, no cuenta como pregunta)
        $tienePreguntas = $this->elementosFormulario->where('tipo_elemento_id', '!=', 1)->count() > 0;
        $this->totalPasos = $tienePreguntas ? 2 : 1;

        if ($this->compraActual) {
            $this->monedaSeleccionada = $this->compraActual->moneda_id;
            $this->destinatario = $this->compraActual->destinatario_id;
            $this->parienteSeleccionado = $this->compraActual->pariente_usuario_id ?? $this->usuario?->id;
            $this->cargarCarritoDesdeCompra();
            $this->cargarRespuestasExistentes();
        }

        $this->inicializarMoneda();
        $this->generarCamposAdicionalesHtml();

        // --- LÓGICA DE PRECARGA AUTOMÁTICA ---

        // FLUJO 1: Actividades GRATUITAS (No requieren pago ni necesariamente sesión)
        // Si es única compra, única inscripción, gratuita Y solo hay una categoría, agregamos automáticamente.
        if (
            $this->actividad->tipo->unica_compra && 
            $this->actividad->tipo->unica_inscripcion && 
            $this->actividad->tipo->es_gratuita && 
            $this->categoriasCompraPermitidas->count() === 1 && // Solo si hay una opción clara
            empty($this->carrito)
        ) {
            $categoriaParaAgregar = $this->categoriasCompraPermitidas->first();
            if ($categoriaParaAgregar) {
                $this->agregarAlCarrito($categoriaParaAgregar->id);
            }
        }

        // FLUJO 2: Actividades de PAGO con SESIÓN REQUERIDA (NUEVO)
        // Si la actividad requiere inicio de sesión, es de pago, el usuario está logueado 
        // y es de compra/inscripción única, cargamos automáticamente la categoría con sus datos.
        if (
            Auth::check() &&                                   // Usuario logueado
            $this->actividad->tipo->requiere_inicio_sesion &&  // Requiere sesión
            !$this->actividad->tipo->es_gratuita &&            // NO es gratuita (es de pago)
            $this->actividad->tipo->unica_compra &&            // Es compra única
            $this->actividad->tipo->unica_inscripcion &&       // Es inscripción única
            $this->categoriasCompraPermitidas->count() === 1 && // NUEVO: Solo si hay UNA categoría disponible
            empty($this->carrito)                             // El carrito está actualmente vacío
        ) {
            // Obtenemos la primera categoría que el usuario tenga permitido comprar (validada académicamente) 
            $categoriaParaCargar = $this->categoriasCompraPermitidas->first();
            
            if ($categoriaParaCargar) {
                // Seleccionamos automáticamente al mismo usuario logueado como pariente/destinatario por defecto
                $this->parienteSeleccionado = $this->usuario->id;
                
                // Agregamos al carrito para que ya aparezca con el valor de la categoría moneda
                $this->agregarAlCarrito($categoriaParaCargar->id);
            }
        }
    }

    /**
     * Carga las respuestas si ya existe una compra iniciada.
     */
    private function cargarRespuestasExistentes()
    {
        if (!$this->compraActual) return;

        $respuestasGuardadas = RespuestaElementoFormulario::where('compra_id', $this->compraActual->id)->get();
        foreach ($respuestasGuardadas as $resp) {
            $tipoClase = $resp->elemento->tipoElemento->clase;
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

    /**
     * Navegación al siguiente paso o procesamiento final.
     */
    public function siguientePaso()
    {
        if ($this->pasoActual == 1) {
            // Validaciones básicas de Step 1
            if (empty($this->carrito)) {
                $this->dispatch('mostrarMensaje', ['titulo' => 'Carrito vacío', 'mensaje' => 'Debes agregar al menos un ítem.', 'tipo' => 'error']);
                return;
            }

            if (!Auth::check()) {
                $this->validate(); // Valida datos del comprador si es invitado
            }

            if ($this->totalPasos > 1) {
                $this->pasoActual = 2;
                return;
            }
        }

        // Si ya estamos en el último paso (o no hay formulario), procesamos todo.
        $this->procesarRegistro();
    }

    public function volverPaso()
    {
        if ($this->pasoActual > 1) {
            $this->pasoActual--;
        }
    }


    /**
     * Carga el array $carrito desde los registros de la base de datos.
     */
    private function cargarCarritoDesdeCompra()
    {
        $this->carrito = [];
        if ($this->compraActual) {
            foreach ($this->compraActual->carritos as $item) {
                $this->carrito[$item->actividad_categoria_id] = [
                    'id' => $item->actividad_categoria_id,
                    'nombre' => $item->categoria->nombre,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                ];
            }
        }
    }

    /**
     * Carga las relaciones familiares del usuario autenticado.
     */
    private function cargarRelacionesFamiliares()
    {
        if ($this->usuario) {
            $this->relacionesFamiliares = $this->usuario->parientesDelUsuario()->get();
        }
    }

    private function cargarCategoriasPermitidas()
    {
        if ($this->actividad->tipo->requiere_inicio_sesion && $this->usuario) {
            // Unificamos categorías permitidas: las del usuario + las de sus parientes relacionados
            $categoriasUsuario = $this->actividad->categoriasDisponiblesParaUsuario($this->usuario->id);
            
            // También revisamos qué categorías están disponibles para sus parientes
            $parientes = $this->usuario->parientesDelUsuario()->get();
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

    /**
     * Inicializa la moneda de la transacción.
     */
    private function inicializarMoneda()
    {
        if (empty($this->monedaSeleccionada)) {
            $this->monedaSeleccionada = $this->actividad->monedas()->first()->id ?? null;
        }
        $this->monedaActual = Moneda::find($this->monedaSeleccionada);
    }

    /**
     * Carga los campos adicionales de la actividad.
     */
    private function cargarCamposAdicionales()
    {
        $this->camposAdicionalesActividad = $this->actividad->camposAdicionales;
    }

    /**
     * Genera el HTML para los campos adicionales.
     */
    public function generarCamposAdicionalesHtml()
    {
        $html = '<div class="card shadow border-top-0 border-1c p-5 rounded"> 
                     <div class="card-header p-0">
                     <h5 class="fw-semibold">Campos adicionales</h5>
                     </div>
                     <div class="card-body p-0"> 
                     <div class="row">';

        foreach ($this->camposAdicionalesActividad as $campo) {
            $respuestaCampo = $this->compraActual?->camposAdicionales->firstWhere('campo_adicional_id', $campo->id)?->respuesta ?? '';
            $html .= '<div class="form-group col-sm-12 col-md-6 mb-2">';
            $html .= '<label class="form-label">' . $campo->nombre . '</label>';
            $html .= '<input placeholder="Ingresa la información" wire:model="camposAdicionales.' . $campo->id . '" type="text" class="form-control" value="' . $respuestaCampo . '">';
            $html .= '</div>';
        }

        $html .= '</div></div></div>';
        $this->camposAdicionalesHtml = $html;
    }

    /**
     * Incrementa la cantidad de un ítem.
     */
    public function incrementCantidad($categoriaId)
    {
        // Si la actividad es de única compra o única inscripción, bloqueamos el incremento a 1.
        if ($this->actividad->tipo->unica_compra || $this->actividad->tipo->unica_inscripcion) {
            $this->cantidades[$categoriaId] = 1;
            return;
        }

        $categoria = ActividadCategoria::find($categoriaId);
        if (!isset($this->cantidades[$categoriaId])) {
            $this->cantidades[$categoriaId] = 1;
        }
        if ($categoria && $this->cantidades[$categoriaId] < $categoria->limite_compras) {
            $this->cantidades[$categoriaId]++;
        }
    }

    /**
     * Decrementa la cantidad de un ítem.
     */
    public function decrementCantidad($categoriaId)
    {
        // Si la actividad es de única compra o única inscripción, bloqueamos en 1.
        if ($this->actividad->tipo->unica_compra || $this->actividad->tipo->unica_inscripcion) {
            $this->cantidades[$categoriaId] = 1;
            return;
        }

        if (isset($this->cantidades[$categoriaId]) && $this->cantidades[$categoriaId] > 1) {
            $this->cantidades[$categoriaId]--;
        }
    }

    /**
     * Agrega una categoría al carrito temporal.
     */
    public function agregarAlCarrito($categoriaId)
    {

        $categoria = ActividadCategoria::find($categoriaId);
        if (!$categoria) return;

        // --- INICIO DE LA LÓGICA EDITADA ---
        $cantidadDeseada = $this->cantidades[$categoriaId] ?? 1;

        // --- INICIO DE LA LÓGICA CORREGIDA ---
        $this->cantidadInvitados = max(0, intval($this->cantidadInvitados));
        $cantidadDeseada = 1 + $this->cantidadInvitados;
        // --- FIN DE LA LÓGICA CORREGIDA ---

        // --- INICIO DE LA CORRECCIÓN DE LÓGICA ---
        if ($this->actividad->tipo->unica_compra && $this->actividad->tiene_invitados) {
            // CASO 1: Compra única CON invitados
            $this->cantidadInvitados = max(0, intval($this->cantidadInvitados));
            $cantidadDeseada = 1 + $this->cantidadInvitados;
        } elseif (!$this->actividad->tipo->unica_compra) {
            // CASO 2: Compra MÚLTIPLE (con botones +/-)
            $cantidadDeseada = $this->cantidades[$categoriaId] ?? 1;
            if ($cantidadDeseada > $categoria->limite_compras) {
                $this->dispatch('mostrarMensaje', ['mensaje' => "No puedes agregar más de {$categoria->limite_compras} items de la categoría '{$categoria->nombre}'.", 'tipo' => 'error']);
                return;
            }
        }
        // CASO 3: Compra única SIN invitados, ya está cubierto por el $cantidadDeseada = 1;
        // --- FIN DE LA CORRECCIÓN DE LÓGICA ---

        $monedaCategoria = $categoria->monedas()->where('moneda_id', $this->monedaSeleccionada)->first();
        $precio = $monedaCategoria?->pivot->valor ?? 0;

        $this->carrito[$categoriaId] = [
            'id' => $categoria->id,
            'nombre' => $categoria->nombre,
            'cantidad' => $cantidadDeseada,
            'precio' => $precio,
        ];
    }
    public function updatedCantidadInvitados($value)
    {
        // Nos aseguramos de que el valor sea un entero no negativo.
        $this->cantidadInvitados = max(0, intval($value));

        // Si la actividad permite invitados y ya hay un item en el carrito (modo unica_compra)
        if ($this->actividad->tiene_invitados && !empty($this->carrito)) {
            // Obtenemos la clave de la primera (y única) categoría en el carrito.
            $categoriaId = array_key_first($this->carrito);

            // Actualizamos la cantidad en el carrito para que sea 1 (principal) + N (invitados).
            if (isset($this->carrito[$categoriaId])) {
                $this->carrito[$categoriaId]['cantidad'] = 1 + $this->cantidadInvitados;
            }
        }
    }

    /**
     * --- MÉTODO DE ELIMINACIÓN RECONSTRUIDO ---
     * Elimina toda la transacción y resetea el estado.
     */
    public function eliminarDelCarrito($categoriaId)
    {
        if (!$this->compraActual) {
            // Si no hay compra, solo se quita del carrito visual
            if (isset($this->carrito[$categoriaId])) {
                unset($this->carrito[$categoriaId]);
            }
            return;
        }

        DB::beginTransaction();
        try {
            // Revertir el aforo ocupado
            foreach ($this->compraActual->carritos as $item) {
                ActividadCategoria::find($item->actividad_categoria_id)->decrement('aforo_ocupado', $item->cantidad);
            }

            // Eliminar registros asociados
            $this->compraActual->pagos()->delete();
            $this->compraActual->carritos()->delete();
            $this->compraActual->camposAdicionales()->delete();
            $this->compraActual->delete();

            // Resetear el estado del componente
            $this->compraActual = null;
            $this->carrito = [];

            DB::commit();

            $this->dispatch('mostrarMensaje', ['mensaje' => "La compra ha sido cancelada. Puedes empezar de nuevo.", 'tipo' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar compra completa: ' . $e->getMessage());
            $this->dispatch('mostrarMensaje', ['titulo' => 'Error', 'mensaje' => 'No se pudo cancelar la compra.', 'tipo' => 'error']);
        }
    }
    /**
     * MÉTODO RECONSTRUIDO: Lógica unificada con Transacción.
     * Procesa la compra, la inscripción y las respuestas del formulario.
     */
    public function procesarRegistro()
    {
        // 1. VALIDACIONES INICIALES
        if (empty($this->carrito)) return;

        $this->resetErrorBag();

        // Validar campos de contacto si no está logueado
        if (!Auth::check()) {
            $this->validate();
        }

        // Validar campos requeridos del formulario si existen
        $erroresEncontrados = false;
        foreach ($this->elementosFormulario as $elemento) {
            if ($elemento->required && $elemento->tipo_elemento_id != 1) {
                if (!isset($this->respuestas[$elemento->id]) || empty($this->respuestas[$elemento->id])) {
                    $this->addError('respuestas.' . $elemento->id, "El campo '{$elemento->titulo}' es obligatorio.");
                    $erroresEncontrados = true;
                }
            }
        }

        if ($erroresEncontrados) {
            $this->dispatch('mostrarMensaje', ['titulo' => 'Campos Pendientes', 'mensaje' => "Por favor, completa los campos obligatorios del formulario.", 'tipo' => 'error']);
            return;
        }

        DB::beginTransaction();

        try {
            // 2. VALIDACIÓN DE AFORO Y CÁLCULO TOTAL
            foreach ($this->carrito as $categoriaId => $item) {
                $cat = ActividadCategoria::find($categoriaId);
                $disponible = $cat->aforo - $cat->aforo_ocupado;
                if ($item['cantidad'] > $disponible) {
                    throw new \Exception("No hay cupos suficientes para '{$cat->nombre}'.");
                }
                $cat->increment('aforo_ocupado', $item['cantidad']);
            }
            $totalCompra = $this->calcularTotal();

            // 3. CREACIÓN DE LA COMPRA
            $datosCompra = [
                'actividad_id' => $this->actividad->id,
                'moneda_id' => $this->monedaSeleccionada ?: 1,
                'fecha' => $this->fechaHoy,
                'valor' => $totalCompra,
                'estado' => 1,
                'metodo_pago_id' => 1,
                'destinatario_id' => $this->destinatario ?? null,
            ];

            if (Auth::check()) {
                $datosCompra['user_id'] = $this->usuario->id;
                $datosCompra['pariente_usuario_id'] = ($this->usuario->id == $this->parienteSeleccionado) ? null : $this->parienteSeleccionado;
                $datosCompra['nombre_completo_comprador'] = $this->usuario->nombre(3);
                $datosCompra['identificacion_comprador'] = $this->usuario->identificacion;
                $datosCompra['telefono_comprador'] = $this->usuario->telefono_movil ?: '0000000';
                $datosCompra['email_comprador'] = $this->usuario->email;
            } else {
                $datosCompra['nombre_completo_comprador'] = $this->nombreComprador;
                $datosCompra['identificacion_comprador'] = $this->identificacionComprador;
                $datosCompra['telefono_comprador'] = $this->telefonoComprador;
                $datosCompra['email_comprador'] = $this->EmailComprador;
            }

            $compra = Compra::create($datosCompra);

            // 4. CREACIÓN DEL PAGO (si aplica)
            $pago = null;
            if ($totalCompra > 0) {
                $pago = Pago::create([
                    'compra_id' => $compra->id,
                    'moneda_id' => $compra->moneda_id,
                    'valor' => $totalCompra,
                    'fecha' => $this->fechaHoy,
                ]);
            }

            // 5. CARRILLO COMPRA E INSCRIPCIÓN PRINCIPAL
            $inscripcionPrincipal = null;
            foreach ($this->carrito as $item) {
                ActividadCarritoCompra::create([
                    'actividad_id' => $this->actividad->id,
                    'compra_id' => $compra->id,
                    'actividad_categoria_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'user_id' => $this->usuario?->id,
                    'pago_id' => $pago?->id,
                    'fecha' => $this->fechaHoy
                ]);

                if (!$inscripcionPrincipal) {
                    $inscripcionPrincipal = Inscripcion::create([
                        'user_id' => $this->parienteSeleccionado,
                        'actividad_categoria_id' => $item['id'],
                        'compra_id' => $compra->id,
                        'fecha' => $this->fechaHoy,
                        'estado' => $this->actividad->estado_inscripcion_defecto,
                        'nombre_inscrito' => $datosCompra['nombre_completo_comprador'],
                        'email' => $datosCompra['email_comprador'],
                        'limite_invitados' => $this->cantidadInvitados
                    ]);
                }
            }

            // 6. GUARDADO DE RESPUESTAS DEL FORMULARIO
            foreach ($this->respuestas as $elementoId => $valor) {
                $elemento = ElementoFormularioActividad::find($elementoId);
                if (!$elemento) continue;

                $respuesta = RespuestaElementoFormulario::updateOrCreate([
                    'compra_id' => $compra->id,
                    'elemento_formulario_actividad_id' => $elementoId,
                ], [
                    'inscripcion_id' => $inscripcionPrincipal->id,
                    'user_id' => $this->parienteSeleccionado ?: ($this->usuario->id ?? null)
                ]);

                switch ($elemento->tipoElemento->clase) {
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

            // 7. CAMPOS ADICIONALES (Sistema antiguo)
            foreach ($this->camposAdicionales as $campoId => $respuestaTexto) {
                if (!empty($respuestaTexto)) {
                    ActividadCampoAdicionalCompra::create([
                        'compra_id' => $compra->id,
                        'campo_adicional_id' => $campoId,
                        'respuesta' => $respuestaTexto
                    ]);
                }
            }

            DB::commit();

            // 8. ENVÍO DE CORREO (Solo si es gratis, sino el controller lo hace tras el pago)
            if ($totalCompra <= 0 && $inscripcionPrincipal) {
                $this->_enviarCorreoDeConfirmacion($inscripcionPrincipal);
            }

            // 9. REDIRECCIÓN
            if ($totalCompra > 0) {
                return redirect()->route('carrito.checkout', ['compra' => $compra, 'actividad' => $this->actividad]);
            } else {
                return redirect()->route('carrito.inscripcionFinalizada', ['inscripcion' => $inscripcionPrincipal, 'actividad' => $this->actividad]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en procesarRegistro: ' . $e->getMessage());
            $this->dispatch('mostrarMensaje', ['titulo' => 'Error', 'mensaje' => 'No se pudo completar el registro: ' . $e->getMessage(), 'tipo' => 'error']);
        }
    }

    /**
     * Elimina una respuesta de archivo/imagen del componente (y futuro de BD si aplica).
     */
    public function eliminarRespuesta($elementoId)
    {
        if (isset($this->respuestas[$elementoId])) {
            unset($this->respuestas[$elementoId]);
        }
    }

    /**
     * Prepara y envía el correo de confirmación de inscripción con el ticket PDF adjunto.
     * Se envuelve en un try-catch para no interrumpir el flujo del usuario si el envío falla.
     *
     * @param Inscripcion $inscripcion La inscripción recién creada.
     */
    private function _enviarCorreoDeConfirmacion(Inscripcion $inscripcion)
    {
        try {
            // Aseguramos que las relaciones necesarias estén cargadas para evitar errores.
            $inscripcion->load('categoriaActividad.actividad', 'compra', 'user');
            $actividad = $inscripcion->categoriaActividad->actividad;

            // Determinamos cuál es el email del destinatario principal.
            $emailDestinatario = $inscripcion->user->email ?? $inscripcion->compra->email_comprador;

            // Verificamos que tengamos un email válido antes de intentar enviar.
            if (filter_var($emailDestinatario, FILTER_VALIDATE_EMAIL)) {
                Mail::to($emailDestinatario)->send(new InscripcionConfirmacionMail($inscripcion, $actividad));
            } else {
                Log::warning("No se pudo enviar correo para inscripción #{$inscripcion->id} por email inválido: " . $emailDestinatario);
            }
        } catch (\Exception $e) {
            // Si el envío de correo falla por cualquier motivo (ej. config del servidor),
            // lo registramos en el log pero no detenemos la ejecución.
            // El usuario completará su inscripción igualmente.
            Log::error("Fallo al enviar correo de confirmación para inscripción #{$inscripcion->id}: " . $e->getMessage());
        }
    }




    public function updatedMonedaSeleccionada($value)
    {
        $this->carrito = [];
        $this->monedaActual = Moneda::find($value);
        $this->dispatch('mostrarMensaje', [
            'titulo' => 'Moneda actualizada',
            'mensaje' => "El carrito se ha vaciado. Por favor, agrega los items de nuevo con los precios en {$this->monedaActual->nombre}.",
            'tipo' => 'info'
        ]);
    }

    public function calcularTotal()
    {
        return collect($this->carrito)->sum(function ($item) {
            return $item['precio'] * $item['cantidad'];
        });
    }

    public function redirigirSiguiente()
    {
        if ($this->actividad->elementos->count() > 0) {
            return redirect()->route('carrito.formulario', ['compra' => $this->compraActual, 'actividad' => $this->actividad]);
        } else {
            return redirect()->route('carrito.checkout', ['compra' => $this->compraActual, 'actividad' => $this->actividad]);
        }
    }

    public function render()
    {
        $this->generarCamposAdicionalesHtml();
        return view('livewire.carrito.carrito', [
            'total' => $this->calcularTotal(),
        ]);
    }
}
