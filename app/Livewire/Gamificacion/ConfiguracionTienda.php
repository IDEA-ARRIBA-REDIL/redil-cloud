<?php

namespace App\Livewire\Gamificacion;

use App\Models\EstadoCivil;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\EstadoTareaConsolidacion;
use App\Models\PasoCrecimiento;
use App\Models\ProductoTienda;
use App\Models\RangoEdad;
use App\Models\Sede;
use App\Models\SolicitudCanje;
use App\Models\TareaConsolidacion;
use App\Models\TipoUsuario;
use App\Models\TransaccionPuntos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConfiguracionTienda extends Component
{
    use WithFileUploads;

    // Control de Pestañas ('solicitudes' | 'catalogo')
    public string $tabActivo = 'solicitudes';

    // Propiedades Formulario Producto
    public ?int $productoId = null;
    public string $nombre = '';
    public string $descripcion = '';
    public string $tipo = 'fisico'; // 'fisico' | 'digital'
    public $costo_puntos = 0;
    public $stock = null;
    public $limite_por_usuario = null;
    public string $instrucciones_canje = '';
    public string $enlace_digital = '';
    public $imagen = null;
    public ?string $imagen_recortada = null;
    public ?string $imagen_existente = null;
    public $orden = 0;

    // =========================================================================
    // PROPIEDADES DE RESTRICCIONES / VISIBILIDAD
    // =========================================================================
    public bool $visible_todos = true;
    public int $genero = 3; // 1: Masculino, 2: Femenino, 3: Ambos
    public array $sedesSeleccionadas = [];
    public array $estadosCivilesSeleccionados = [];
    public array $rangosEdadSeleccionados = [];
    public array $tiposUsuarioSeleccionados = [];
    public array $procesosRequisito = [];
    public array $tareasRequisito = [];

    // Buscadores y Filtros
    public string $busquedaProductos = '';
    public string $filtroTipoProducto = ''; // '' | 'fisico' | 'digital'

    public string $busquedaSolicitudes = '';
    public string $filtroEstadoSolicitud = ''; // '' | 'pendiente' | 'aprobado' | 'entregado' | 'rechazado'
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    /**
     * Inicializa los filtros por defecto (últimos 30 días).
     */
    public function mount(): void
    {
        $this->fechaInicio = now()->subDays(30)->format('Y-m-d');
        $this->fechaFin = now()->format('Y-m-d');
    }

    /**
     * Cambia la pestaña activa.
     */
    public function cambiarTab(string $tab): void
    {
        $this->tabActivo = $tab;
    }

    /**
     * Cambia el tipo de producto en el formulario ('fisico' o 'digital').
     */
    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }

    /**
     * Agrega una fila de paso de crecimiento requisito.
     */
    public function agregarPasoRequisito(): void
    {
        $this->procesosRequisito[] = [
            'paso_crecimiento_id' => '',
            'estado_paso_crecimiento_usuario_id' => '',
        ];
    }

    /**
     * Elimina una fila de paso de crecimiento requisito.
     */
    public function eliminarPasoRequisito(int $index): void
    {
        unset($this->procesosRequisito[$index]);
        $this->procesosRequisito = array_values($this->procesosRequisito);
    }

    /**
     * Agrega una fila de tarea de consolidación requisito.
     */
    public function agregarTareaRequisito(): void
    {
        $this->tareasRequisito[] = [
            'tarea_consolidacion_id' => '',
            'estado_tarea_consolidacion_id' => '',
        ];
    }

    /**
     * Elimina una fila de tarea de consolidación requisito.
     */
    public function eliminarTareaRequisito(int $index): void
    {
        unset($this->tareasRequisito[$index]);
        $this->tareasRequisito = array_values($this->tareasRequisito);
    }

    /**
     * Limpia los filtros de la lista de solicitudes.
     */
    public function limpiarFiltrosSolicitudes(): void
    {
        $this->busquedaSolicitudes = '';
        $this->filtroEstadoSolicitud = '';
        $this->fechaInicio = now()->subDays(30)->format('Y-m-d');
        $this->fechaFin = now()->format('Y-m-d');

        $this->dispatch('resetFlatpickr', [
            'inicio' => $this->fechaInicio,
            'fin' => $this->fechaFin,
        ]);
    }

    /**
     * Limpia los filtros del catálogo de productos.
     */
    public function limpiarFiltrosProductos(): void
    {
        $this->busquedaProductos = '';
        $this->filtroTipoProducto = '';
    }

    // =========================================================================
    // GESTIÓN DE PRODUCTOS (CATÁLOGO)
    // =========================================================================

    /**
     * Abre el modal/offcanvas para crear un nuevo producto o editar uno existente.
     */
    public function abrirModalProducto(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetFormularioProducto();

        if ($id) {
            $producto = ProductoTienda::with([
                'sedes', 'estadosCiviles', 'rangosEdad', 'tiposUsuarios', 'procesosRequisito', 'tareasRequisito'
            ])->findOrFail($id);

            $this->productoId = $producto->id;
            $this->nombre = $producto->nombre;
            $this->descripcion = $producto->descripcion ?? '';
            $this->tipo = $producto->tipo ?: 'fisico';
            $this->costo_puntos = $producto->costo_puntos ?? 0;
            $this->stock = $producto->stock;
            $this->limite_por_usuario = $producto->limite_por_usuario;
            $this->instrucciones_canje = $producto->instrucciones_canje ?? '';
            $this->enlace_digital = $producto->enlace_digital ?? '';
            $this->imagen_existente = $producto->imagen_ruta;
            $this->orden = $producto->orden ?? 0;

            // Cargar restricciones de visibilidad
            $this->visible_todos = (bool) ($producto->visible_todos ?? true);
            $this->genero = (int) ($producto->genero ?? 3);
            $this->sedesSeleccionadas = $producto->sedes->pluck('id')->map(fn($item) => (string) $item)->toArray();
            $this->estadosCivilesSeleccionados = $producto->estadosCiviles->pluck('id')->map(fn($item) => (string) $item)->toArray();
            $this->rangosEdadSeleccionados = $producto->rangosEdad->pluck('id')->map(fn($item) => (string) $item)->toArray();
            $this->tiposUsuarioSeleccionados = $producto->tiposUsuarios->pluck('id')->map(fn($item) => (string) $item)->toArray();

            $this->procesosRequisito = $producto->procesosRequisito->map(fn($p) => [
                'paso_crecimiento_id' => (string) $p->id,
                'estado_paso_crecimiento_usuario_id' => (string) $p->pivot->estado_paso_crecimiento_usuario_id,
            ])->toArray();

            $this->tareasRequisito = $producto->tareasRequisito->map(fn($t) => [
                'tarea_consolidacion_id' => (string) $t->id,
                'estado_tarea_consolidacion_id' => (string) $t->pivot->estado_tarea_consolidacion_id,
            ])->toArray();
        }

        $this->dispatch('sincronizarSelect2Restricciones', [
            'sedes' => $this->sedesSeleccionadas,
            'estados' => $this->estadosCivilesSeleccionados,
            'rangos' => $this->rangosEdadSeleccionados,
            'tipos' => $this->tiposUsuarioSeleccionados,
        ]);

        $this->dispatch('abrirOffcanvasProducto', [
            'imagenUrl' => $this->imagen_existente ? tenant_asset('img/tienda/' . $this->imagen_existente) : null,
            'placeholderUrl' => Storage::disk('global_media')->url('placeholder.jpg'),
        ]);
    }

    /**
     * Guarda un producto nuevo o actualiza el existente y sus restricciones.
     */
    public function guardarProducto(): void
    {
        $reglas = [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:fisico,digital',
            'costo_puntos' => 'nullable|integer|min:0',
            'descripcion' => 'nullable|string|max:2000',
            'instrucciones_canje' => 'nullable|string|max:2000',
            'stock' => 'nullable|integer|min:0',
            'limite_por_usuario' => 'nullable|integer|min:1',
            'enlace_digital' => $this->tipo === 'digital' ? 'nullable|string|max:1000' : 'nullable',
            'imagen' => 'nullable|image|max:2048',
            'visible_todos' => 'boolean',
            'genero' => 'integer|in:1,2,3',
        ];

        $this->validate($reglas, [
            'nombre.required' => 'El nombre del artículo es obligatorio.',
            'tipo.required' => 'Debes seleccionar el tipo de producto.',
            'costo_puntos.integer' => 'El costo en puntos debe ser un número entero.',
            'costo_puntos.min' => 'El costo en puntos no puede ser negativo.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.max' => 'La imagen no debe superar los 2MB.',
        ]);

        $datos = [
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'costo_puntos' => (int) ($this->costo_puntos ?: 0),
            'descripcion' => $this->descripcion ?: null,
            'instrucciones_canje' => $this->instrucciones_canje ?: null,
            'stock' => ($this->tipo === 'fisico' && $this->stock !== '' && $this->stock !== null) ? (int) $this->stock : null,
            'limite_por_usuario' => ($this->limite_por_usuario !== '' && $this->limite_por_usuario !== null) ? (int) $this->limite_por_usuario : null,
            'enlace_digital' => ($this->tipo === 'digital' && $this->enlace_digital) ? trim($this->enlace_digital) : null,
            'orden' => $this->orden ?: 0,
            'visible_todos' => $this->visible_todos,
            'genero' => $this->visible_todos ? 3 : (int) $this->genero,
        ];

        // Procesar subida de imagen recortada (CropperJS) o subida estándar
        if ($this->imagen_recortada) {
            $imagenPartes = explode(';base64,', $this->imagen_recortada);
            $imagenBase64 = base64_decode(count($imagenPartes) > 1 ? $imagenPartes[1] : $imagenPartes[0]);
            $nombreArchivo = 'producto-' . time() . '.png';
            Storage::disk('public')->put('img/tienda/' . $nombreArchivo, $imagenBase64);
            $datos['imagen_ruta'] = $nombreArchivo;
        } elseif ($this->imagen) {
            $nombreArchivo = 'producto-' . time() . '.' . $this->imagen->getClientOriginalExtension();
            $this->imagen->storeAs('img/tienda', $nombreArchivo, 'public');
            $datos['imagen_ruta'] = $nombreArchivo;
        }

        if ($this->productoId) {
            $producto = ProductoTienda::findOrFail($this->productoId);

            // Eliminar imagen anterior si se sube una nueva
            if (($this->imagen_recortada || $this->imagen) && $producto->imagen_ruta) {
                Storage::disk('public')->delete('img/tienda/' . $producto->imagen_ruta);
            }

            $producto->update($datos);
            $mensaje = 'Producto actualizado correctamente.';
        } else {
            $producto = ProductoTienda::create($datos);
            $mensaje = 'Producto creado correctamente.';
        }

        // =====================================================================
        // SINCRONIZAR RESTRICCIONES DE VISIBILIDAD
        // =====================================================================
        if (!$this->visible_todos) {
            $producto->sedes()->sync($this->sedesSeleccionadas);
            $producto->estadosCiviles()->sync($this->estadosCivilesSeleccionados);
            $producto->rangosEdad()->sync($this->rangosEdadSeleccionados);
            $producto->tiposUsuarios()->sync($this->tiposUsuarioSeleccionados);

            // Sincronizar pasos de crecimiento requisito
            $pasosSync = [];
            foreach ($this->procesosRequisito as $idx => $paso) {
                if (!empty($paso['paso_crecimiento_id']) && !empty($paso['estado_paso_crecimiento_usuario_id'])) {
                    $pasosSync[$paso['paso_crecimiento_id']] = [
                        'estado_paso_crecimiento_usuario_id' => $paso['estado_paso_crecimiento_usuario_id'],
                        'indice' => $idx,
                    ];
                }
            }
            $producto->procesosRequisito()->sync($pasosSync);

            // Sincronizar tareas de consolidación requisito
            $tareasSync = [];
            foreach ($this->tareasRequisito as $idx => $tarea) {
                if (!empty($tarea['tarea_consolidacion_id']) && !empty($tarea['estado_tarea_consolidacion_id'])) {
                    $tareasSync[$tarea['tarea_consolidacion_id']] = [
                        'estado_tarea_consolidacion_id' => $tarea['estado_tarea_consolidacion_id'],
                        'indice' => $idx,
                    ];
                }
            }
            $producto->tareasRequisito()->sync($tareasSync);
        } else {
            // Si es visible para todos, desvincular restricciones
            $producto->sedes()->detach();
            $producto->estadosCiviles()->detach();
            $producto->rangosEdad()->detach();
            $producto->tiposUsuarios()->detach();
            $producto->procesosRequisito()->detach();
            $producto->tareasRequisito()->detach();
        }

        $this->resetFormularioProducto();
        $this->dispatch('cerrarOffcanvasProducto');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Completado!',
            'texto' => $mensaje,
        ]);
    }

    /**
     * Elimina un producto del catálogo (SoftDelete).
     */
    public function eliminarProducto(int $id): void
    {
        $producto = ProductoTienda::findOrFail($id);
        $producto->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'El producto ha sido eliminado del catálogo.',
        ]);
    }

    /**
     * Resetea los campos del formulario de producto.
     */
    public function resetFormularioProducto(): void
    {
        $this->productoId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->tipo = 'fisico';
        $this->costo_puntos = 0;
        $this->stock = null;
        $this->limite_por_usuario = null;
        $this->instrucciones_canje = '';
        $this->enlace_digital = '';
        $this->imagen = null;
        $this->imagen_recortada = null;
        $this->imagen_existente = null;
        $this->orden = 0;

        // Reset restricciones
        $this->visible_todos = true;
        $this->genero = 3;
        $this->sedesSeleccionadas = [];
        $this->estadosCivilesSeleccionados = [];
        $this->rangosEdadSeleccionados = [];
        $this->tiposUsuarioSeleccionados = [];
        $this->procesosRequisito = [];
        $this->tareasRequisito = [];

        $this->dispatch('sincronizarSelect2Restricciones', [
            'sedes' => [],
            'estados' => [],
            'rangos' => [],
            'tipos' => [],
        ]);
    }

    // =========================================================================
    // GESTIÓN DE SOLICITUDES DE CANJE
    // =========================================================================

    /**
     * Aprueba o marca como entregada una solicitud de canje.
     */
    public function aprobarSolicitud(int $id): void
    {
        $solicitud = SolicitudCanje::with('producto')->findOrFail($id);

        if ($solicitud->estado !== 'pendiente') {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'Atención',
                'texto' => 'Esta solicitud ya ha sido procesada previamente.',
            ]);
            return;
        }

        $solicitud->update([
            'estado' => 'aprobado',
            'procesado_por_user_id' => Auth::id(),
            'procesado_el' => now(),
        ]);

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Aprobada!',
            'texto' => 'La solicitud de canje ha sido aprobada con éxito.',
        ]);
    }

    /**
     * Rechaza una solicitud de canje, devuelve los puntos al usuario y reingresa el stock físico si aplica.
     */
    public function rechazarSolicitud(int $id, ?string $motivo = null): void
    {
        try {
            DB::transaction(function () use ($id, $motivo) {
                $solicitud = SolicitudCanje::with(['user', 'producto'])->lockForUpdate()->findOrFail($id);

                if ($solicitud->estado !== 'pendiente') {
                    throw new \Exception('Esta solicitud ya ha sido procesada previamente.');
                }

                // 1. Reembolsar puntos al usuario
                if ($solicitud->user && $solicitud->puntos_gastados > 0) {
                    $solicitud->user->increment('puntos', $solicitud->puntos_gastados);

                    // Registrar la transacción de reembolso si existe la tabla
                    if (class_exists(TransaccionPuntos::class)) {
                        TransaccionPuntos::create([
                            'user_id' => $solicitud->user_id,
                            'monto' => $solicitud->puntos_gastados,
                            'motivo' => 'Reembolso por canje rechazado: ' . ($solicitud->producto->nombre ?? 'Producto') . " (#{$solicitud->codigo_canje})",
                            'creado_por_user_id' => Auth::id(),
                        ]);
                    }
                }

                // 2. Reingresar stock si es producto físico con control de inventario
                if ($solicitud->producto && $solicitud->producto->tipo === 'fisico' && $solicitud->producto->stock !== null) {
                    $solicitud->producto->increment('stock', 1);
                }

                // 3. Marcar solicitud como rechazada
                $solicitud->update([
                    'estado' => 'rechazado',
                    'notas_admin' => $motivo ?: $solicitud->notas_admin,
                    'procesado_por_user_id' => Auth::id(),
                    'procesado_el' => now(),
                ]);
            });

            $this->dispatch('msn', [
                'icono' => 'info',
                'titulo' => 'Solicitud Rechazada',
                'texto' => 'Se ha rechazado la solicitud, los puntos han sido reembolsados y el stock ha sido reingresado.',
            ]);
        } catch (\Throwable $th) {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'Atención',
                'texto' => $th->getMessage() ?: 'No fue posible procesar el rechazo de la solicitud.',
            ]);
        }
    }

    /**
     * Normaliza un texto eliminando acentos y convirtiéndolo a minúsculas para búsquedas.
     */
    protected function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ü' => 'u', 'Ü' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
        ];

        return strtr($texto, $acentos);
    }

    public function render()
    {
        // Conteo de solicitudes pendientes para el badge de la pestaña
        $pendientesCount = SolicitudCanje::where('estado', 'pendiente')->count();

        // ---------------------------------------------------------------------
        // CONSULTA DE PRODUCTOS (CATÁLOGO)
        // ---------------------------------------------------------------------
        $queryProductos = ProductoTienda::query();

        if (!empty($this->filtroTipoProducto)) {
            $queryProductos->where('tipo', $this->filtroTipoProducto);
        }

        $todosProductos = $queryProductos->orderBy('orden', 'asc')->latest()->get();

        $productos = $todosProductos;
        if (!empty(trim($this->busquedaProductos))) {
            $termino = $this->normalizarTexto($this->busquedaProductos);
            $productos = $todosProductos->filter(function ($prod) use ($termino) {
                $nombre = $this->normalizarTexto($prod->nombre ?? '');
                $desc = $this->normalizarTexto($prod->descripcion ?? '');
                return str_contains($nombre, $termino) || str_contains($desc, $termino);
            });
        }

        // ---------------------------------------------------------------------
        // CONSULTA DE SOLICITUDES DE CANJE
        // ---------------------------------------------------------------------
        $querySolicitudes = SolicitudCanje::with(['user', 'producto', 'procesadoPor'])->latest();

        if (!empty($this->filtroEstadoSolicitud)) {
            $querySolicitudes->where('estado', $this->filtroEstadoSolicitud);
        }

        if (!empty($this->fechaInicio)) {
            $querySolicitudes->whereDate('created_at', '>=', $this->fechaInicio);
        }

        if (!empty($this->fechaFin)) {
            $querySolicitudes->whereDate('created_at', '<=', $this->fechaFin);
        }

        $todasSolicitudes = $querySolicitudes->get();

        $solicitudes = $todasSolicitudes;
        if (!empty(trim($this->busquedaSolicitudes))) {
            $terminoSolicitud = $this->normalizarTexto($this->busquedaSolicitudes);
            $solicitudes = $todasSolicitudes->filter(function ($sol) use ($terminoSolicitud) {
                $codigo = $this->normalizarTexto($sol->codigo_canje ?? '');
                $usuarioNombre = $this->normalizarTexto($sol->user ? $sol->user->nombre(3) : '');
                $usuarioEmail = $this->normalizarTexto($sol->user->email ?? '');
                $productoNombre = $this->normalizarTexto($sol->producto->nombre ?? '');

                return str_contains($codigo, $terminoSolicitud)
                    || str_contains($usuarioNombre, $terminoSolicitud)
                    || str_contains($usuarioEmail, $terminoSolicitud)
                    || str_contains($productoNombre, $terminoSolicitud);
            });
        }

        // ---------------------------------------------------------------------
        // CATÁLOGOS PARA RESTRICCIONES
        // ---------------------------------------------------------------------
        $sedes = Sede::orderBy('nombre', 'asc')->get();
        $estadosCiviles = EstadoCivil::orderBy('nombre', 'asc')->get();
        $rangosEdad = RangoEdad::orderBy('edad_minima', 'asc')->get();
        $tiposUsuario = TipoUsuario::orderBy('nombre', 'asc')->get();
        $pasosCrecimiento = PasoCrecimiento::orderBy('nombre', 'asc')->get();
        $estadosPasos = EstadoPasoCrecimientoUsuario::orderBy('nombre', 'asc')->get();
        $tareasConsolidacion = TareaConsolidacion::orderBy('nombre', 'asc')->get();
        $estadosTareas = EstadoTareaConsolidacion::orderBy('nombre', 'asc')->get();

        return view('livewire.gamificacion.configuracion-tienda', [
            'productos' => $productos,
            'solicitudes' => $solicitudes,
            'pendientesCount' => $pendientesCount,
            'sedes' => $sedes,
            'estadosCiviles' => $estadosCiviles,
            'rangosEdad' => $rangosEdad,
            'tiposUsuario' => $tiposUsuario,
            'pasosCrecimiento' => $pasosCrecimiento,
            'estadosPasos' => $estadosPasos,
            'tareasConsolidacion' => $tareasConsolidacion,
            'estadosTareas' => $estadosTareas,
        ]);
    }
}
