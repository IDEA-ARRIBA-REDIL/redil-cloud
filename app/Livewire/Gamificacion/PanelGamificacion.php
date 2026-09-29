<?php

namespace App\Livewire\Gamificacion;

use App\Models\Insignia;
use App\Models\InsigniaUser;
use App\Models\ProductoTienda;
use App\Models\ReglaGamificacion;
use App\Models\SolicitudCanje;
use App\Models\TransaccionPuntos;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PanelGamificacion extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $tabActivo = 'insignias';

    protected $queryString = [
        'tabActivo' => ['except' => 'insignias', 'as' => 'tab'],
    ];

    public string $filtroMision = 'todas'; // 'todas' | 'diarias' | 'pendientes' | 'completadas'

    // Sub-pestañas de Historial ('movimientos' | 'canjes')
    public string $subtabHistorial = 'movimientos';

    public ?string $historialFechaInicio = null;

    public ?string $historialFechaFin = null;

    public function mount(): void
    {
        if (request()->has('tab') && in_array(request()->get('tab'), ['insignias', 'misiones', 'tienda', 'historial'])) {
            $this->tabActivo = request()->get('tab');
        }

        // Rango de fechas por defecto: últimos 90 días
        $this->historialFechaInicio = now()->subDays(90)->format('Y-m-d');
        $this->historialFechaFin = now()->format('Y-m-d');
    }

    /**
     * Cambia la pestaña activa.
     */
    public function cambiarTab(string $tab): void
    {
        if (in_array($tab, ['insignias', 'misiones', 'tienda', 'historial'])) {
            $this->tabActivo = $tab;
        }
    }

    /**
     * Cambia la sub-pestaña de Historial.
     */
    public function cambiarSubtabHistorial(string $subtab): void
    {
        if (in_array($subtab, ['movimientos', 'canjes'])) {
            $this->subtabHistorial = $subtab;
            $this->resetPage();
        }
    }

    /**
     * Limpia el filtro de fechas del historial restableciendo a los últimos 90 días.
     */
    public function limpiarFiltroFechasHistorial(): void
    {
        $this->historialFechaInicio = now()->subDays(90)->format('Y-m-d');
        $this->historialFechaFin = now()->format('Y-m-d');
        $this->resetPage();
        $this->dispatch('resetFlatpickrHistorial', [
            'fechaInicio' => $this->historialFechaInicio,
            'fechaFin' => $this->historialFechaFin,
        ]);
    }

    /**
     * Filtra el listado de misiones.
     */
    public function filtrarMisiones(string $filtro): void
    {
        if (in_array($filtro, ['todas', 'diarias', 'pendientes', 'completadas'])) {
            $this->filtroMision = $filtro;
        }
    }

    /**
     * Prepara y solicita confirmación para canjear un producto.
     */
    public function solicitarCanje(int $productoId): void
    {
        $usuario = Auth::user();
        $producto = ProductoTienda::forUser($usuario)->find($productoId);

        if (! $producto) {
            $this->dispatch('msn', [
                'icono' => 'error',
                'titulo' => 'Producto no disponible',
                'texto' => 'Este producto ya no se encuentra disponible para tu perfil.',
            ]);

            return;
        }

        // Validar si el usuario tiene puntos suficientes
        if ($usuario->puntos < $producto->costo_puntos) {
            $faltantes = $producto->costo_puntos - $usuario->puntos;
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'Puntos insuficientes',
                'texto' => 'Te faltan '.number_format($faltantes).' puntos para poder canjear este producto.',
            ]);

            return;
        }

        // Validar stock si es físico
        if ($producto->tipo === 'fisico' && $producto->stock !== null && $producto->stock <= 0) {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'Producto agotado',
                'texto' => 'Lo sentimos, las unidades disponibles de este artículo se han agotado.',
            ]);

            return;
        }

        // Validar límite por usuario
        if ($producto->limite_por_usuario !== null) {
            $conteoCanjes = SolicitudCanje::where('user_id', $usuario->id)
                ->where('producto_id', $producto->id)
                ->where('estado', '!=', 'rechazado')
                ->count();

            if ($conteoCanjes >= $producto->limite_por_usuario) {
                $this->dispatch('msn', [
                    'icono' => 'info',
                    'titulo' => 'Límite alcanzado',
                    'texto' => "Ya has alcanzado el límite máximo de ({$producto->limite_por_usuario}) canje(s) permitido(s) para este producto.",
                ]);

                return;
            }
        }

        // Disparar evento a JavaScript con SweetAlert2 para confirmar
        $this->dispatch('confirmarCanjeSwal', [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'costo' => number_format($producto->costo_puntos),
            'puntosRestantes' => number_format($usuario->puntos - $producto->costo_puntos),
            'tipo' => $producto->tipo,
            'instrucciones' => $producto->instrucciones_canje ?: '',
            'imagenUrl' => $producto->imagen_url,
            'esDigital' => $producto->tipo === 'digital',
        ]);
    }

    /**
     * Ejecuta la transacción de canje de puntos de manera atómica y segura.
     */
    public function ejecutarCanje(int $productoId): void
    {
        $userId = Auth::id();

        try {
            $resultado = DB::transaction(function () use ($userId, $productoId) {
                // Bloquear registro del usuario para evitar concurrencia / doble gasto
                $usuario = User::where('id', $userId)->lockForUpdate()->firstOrFail();

                // Bloquear producto
                $producto = ProductoTienda::where('id', $productoId)->lockForUpdate()->firstOrFail();

                // Re-verificar puntos
                if ($usuario->puntos < $producto->costo_puntos) {
                    throw new \Exception('No cuentas con los puntos suficientes para completar este canje.');
                }

                // Re-verificar stock
                if ($producto->tipo === 'fisico' && $producto->stock !== null && $producto->stock <= 0) {
                    throw new \Exception('El producto se ha agotado.');
                }

                // Re-verificar límite por usuario
                if ($producto->limite_por_usuario !== null) {
                    $conteoCanjes = SolicitudCanje::where('user_id', $usuario->id)
                        ->where('producto_id', $producto->id)
                        ->where('estado', '!=', 'rechazado')
                        ->count();

                    if ($conteoCanjes >= $producto->limite_por_usuario) {
                        throw new \Exception('Has alcanzado el límite de canjes permitidos para este artículo.');
                    }
                }

                // 1. Deducir puntos del usuario
                $usuario->puntos -= $producto->costo_puntos;
                $usuario->save();

                // 2. Deducir stock si es físico con inventario controlado
                if ($producto->tipo === 'fisico' && $producto->stock !== null) {
                    $producto->decrement('stock', 1);
                }

                // 3. Generar código único de canje
                $codigoCanje = 'CNJ-'.strtoupper(Str::random(6));
                while (SolicitudCanje::where('codigo_canje', $codigoCanje)->exists()) {
                    $codigoCanje = 'CNJ-'.strtoupper(Str::random(6));
                }

                // 4. Crear registro en transacciones_puntos
                TransaccionPuntos::create([
                    'user_id' => $usuario->id,
                    'monto' => -$producto->costo_puntos,
                    'motivo' => "Canje de producto: {$producto->nombre} (#{$codigoCanje})",
                    'creado_por_user_id' => $usuario->id,
                ]);

                // 5. Crear solicitud de canje
                $esDigital = $producto->tipo === 'digital';
                $solicitud = SolicitudCanje::create([
                    'codigo_canje' => $codigoCanje,
                    'user_id' => $usuario->id,
                    'producto_id' => $producto->id,
                    'puntos_gastados' => $producto->costo_puntos,
                    'estado' => $esDigital ? 'aprobado' : 'pendiente',
                    'procesado_por_user_id' => null,
                    'procesado_el' => $esDigital ? now() : null,
                ]);

                return [
                    'solicitud' => $solicitud,
                    'producto' => $producto,
                    'codigo' => $codigoCanje,
                    'esDigital' => $esDigital,
                    'enlaceDigital' => $producto->enlace_digital,
                    'instrucciones' => $producto->instrucciones_canje,
                ];
            });

            // Notificación exitosa
            $this->dispatch('canjeRealizadoExitosamente', [
                'codigo' => $resultado['codigo'],
                'nombre' => $resultado['producto']->nombre,
                'esDigital' => $resultado['esDigital'],
                'enlaceDigital' => $resultado['enlaceDigital'],
                'instrucciones' => $resultado['instrucciones'] ?: 'Acércate al punto de información o coordinación para reclamar tu producto presentando tu código de canje.',
            ]);

        } catch (\Throwable $th) {
            $this->dispatch('msn', [
                'icono' => 'error',
                'titulo' => 'Error al procesar el canje',
                'texto' => $th->getMessage() ?: 'No fue posible completar la solicitud. Por favor intenta de nuevo.',
            ]);
        }
    }

    public function render(): View
    {
        $usuario = Auth::user();

        // 1. Mis Insignias y Progresos
        $insignias = Insignia::orderBy('orden', 'asc')->get();
        $progresos = InsigniaUser::where('user_id', $usuario->id)
            ->get()
            ->keyBy('insignia_id');
        $reglasMeta = ReglaGamificacion::where('frecuencia', 'meta')
            ->whereNotNull('insignia_id')
            ->get()
            ->keyBy('insignia_id');

        // 2. Productos disponibles para el usuario según restricciones
        $productos = ProductoTienda::forUser($usuario)
            ->orderBy('orden', 'asc')
            ->get();

        // Obtener historial de canjes del usuario para verificar límites por producto
        $canjesPorProducto = SolicitudCanje::where('user_id', $usuario->id)
            ->where('estado', '!=', 'rechazado')
            ->select('producto_id', DB::raw('count(*) as total'))
            ->groupBy('producto_id')
            ->pluck('total', 'producto_id')
            ->all();

        // 3. Historial de Canjes del usuario (con filtro de fecha si aplica)
        $querySolicitudes = SolicitudCanje::with('producto')
            ->where('user_id', $usuario->id);

        if (! empty($this->historialFechaInicio) && ! empty($this->historialFechaFin)) {
            $querySolicitudes->whereBetween('created_at', [
                $this->historialFechaInicio.' 00:00:00',
                $this->historialFechaFin.' 23:59:59',
            ]);
        }

        $misSolicitudes = $querySolicitudes->latest()->paginate(15, ['*'], 'page_canjes');

        // 4. Historial de Transacciones de puntos (Paginadas y filtradas por fecha a 90 días por defecto)
        $queryTransacciones = TransaccionPuntos::where('user_id', $usuario->id);

        if (! empty($this->historialFechaInicio) && ! empty($this->historialFechaFin)) {
            $queryTransacciones->whereBetween('created_at', [
                $this->historialFechaInicio.' 00:00:00',
                $this->historialFechaFin.' 23:59:59',
            ]);
        }

        $misTransacciones = $queryTransacciones->latest()->paginate(15, ['*'], 'page_movimientos');

        // 5. Misiones activas configuradas en el sistema
        $reglas = ReglaGamificacion::with('insignia')->get();
        $hoy = \Carbon\Carbon::today();

        // Conteo de transacciones de hoy por motivo para reglas 'cada_vez'
        $transaccionesHoy = TransaccionPuntos::where('user_id', $usuario->id)
            ->whereDate('created_at', $hoy)
            ->get();

        // Todas las transacciones del usuario para verificar misiones 'unica_vez'
        $todasTransacciones = TransaccionPuntos::where('user_id', $usuario->id)->get();

        $mapaRutas = [
            'tiempo_con_dios' => [
                'titulo' => 'Tiempo con Dios',
                'icono' => 'ti-book-2',
                'color' => '#166534',
                'bgColor' => '#dcfce7',
                'ruta' => \Illuminate\Support\Facades\Route::has('tiempoConDios.nuevo') ? route('tiempoConDios.nuevo') : '#',
                'btnTexto' => 'Hacer devocional',
            ],
            'rueda_de_la_vida' => [
                'titulo' => 'Rueda de la Vida',
                'icono' => 'ti-chart-radar',
                'color' => '#0284c7',
                'bgColor' => '#e0f2fe',
                'ruta' => \Illuminate\Support\Facades\Route::has('ruedaDeLaVida.nueva') ? route('ruedaDeLaVida.nueva') : '#',
                'btnTexto' => 'Evaluar mi rueda',
            ],
            'completar_perfil' => [
                'titulo' => 'Completar Perfil',
                'icono' => 'ti-user-circle',
                'color' => '#7c3aed',
                'bgColor' => '#ede9fe',
                'ruta' => \Illuminate\Support\Facades\Route::has('usuario.perfil') ? route('usuario.perfil', $usuario->id) : '#',
                'btnTexto' => 'Actualizar perfil',
            ],
            'peticion_oracion' => [
                'titulo' => 'Petición de Oración',
                'icono' => 'ti-heart-handshake',
                'color' => '#dc2626',
                'bgColor' => '#fee2e2',
                'ruta' => \Illuminate\Support\Facades\Route::has('peticion.nueva') ? route('peticion.nueva') : '#',
                'btnTexto' => 'Enviar petición',
            ],
            'asistencia_grupo' => [
                'titulo' => 'Asistencia a Grupo',
                'icono' => 'ti-users-group',
                'color' => '#0d9488',
                'bgColor' => '#ccfbf1',
                'ruta' => \Illuminate\Support\Facades\Route::has('grupo.lista') ? route('grupo.lista') : '#',
                'btnTexto' => 'Ver mis grupos',
            ],
            'aprobar_curso' => [
                'titulo' => 'Cursos y Escuelas',
                'icono' => 'ti-school',
                'color' => '#d97706',
                'bgColor' => '#fef3c7',
                'ruta' => \Illuminate\Support\Facades\Route::has('cursos.campus') ? route('cursos.campus') : (\Illuminate\Support\Facades\Route::has('cursos.gestionar') ? route('cursos.gestionar') : '#'),
                'btnTexto' => 'Ir a los cursos',
            ],
            'asistencia_reunion' => [
                'titulo' => 'Asistencia a Reunión',
                'icono' => 'ti-building-church',
                'color' => '#ea580c',
                'bgColor' => '#ffedd5',
                'ruta' => '#',
                'btnTexto' => 'Asistir',
            ],
        ];

        $misiones = $reglas->map(function ($regla) use ($progresos, $transaccionesHoy, $todasTransacciones, $mapaRutas) {
            $accion = $regla->accion_codigo;
            $metaData = $mapaRutas[$accion] ?? [
                'titulo' => $regla->nombre,
                'icono' => 'ti-target-arrow',
                'color' => '#166534',
                'bgColor' => '#dcfce7',
                'ruta' => '#',
                'btnTexto' => 'Realizar misión',
            ];

            $estado = 'disponible'; // 'disponible' | 'en_progreso' | 'completada_hoy' | 'completada'
            $progresoActual = 0;
            $metaCantidad = $regla->meta_cantidad ?: 1;
            $porcentaje = 0;

            if ($regla->frecuencia === 'meta') {
                $progreso = $regla->insignia_id ? $progresos->get($regla->insignia_id) : null;
                if ($progreso && $progreso->completada) {
                    $estado = 'completada';
                    $progresoActual = $metaCantidad;
                    $porcentaje = 100;
                } elseif ($progreso && $progreso->progreso_actual > 0) {
                    $estado = 'en_progreso';
                    $progresoActual = $progreso->progreso_actual;
                    $porcentaje = min(100, round(($progresoActual / max(1, $metaCantidad)) * 100));
                } else {
                    $estado = 'disponible';
                    $progresoActual = 0;
                    $porcentaje = 0;
                }
            } elseif ($regla->frecuencia === 'unica_vez') {
                $yaCompletada = $todasTransacciones->contains(function ($tx) use ($regla) {
                    return str_contains($tx->motivo, $regla->nombre);
                });

                if ($yaCompletada) {
                    $estado = 'completada';
                    $porcentaje = 100;
                } else {
                    $estado = 'disponible';
                    $porcentaje = 0;
                }
            } else { // 'cada_vez'
                $ejecucionesHoy = $transaccionesHoy->filter(function ($tx) use ($regla) {
                    return str_contains($tx->motivo, $regla->nombre);
                })->count();

                if ($regla->limite_diario && $ejecucionesHoy >= $regla->limite_diario) {
                    $estado = 'completada_hoy';
                    $porcentaje = 100;
                } else {
                    $estado = 'disponible';
                    $porcentaje = 0;
                }
            }

            return [
                'id' => $regla->id,
                'nombre' => $regla->nombre,
                'accion_codigo' => $regla->accion_codigo,
                'frecuencia' => $regla->frecuencia,
                'puntos_premio' => $regla->puntos_premio,
                'insignia' => $regla->insignia,
                'limite_diario' => $regla->limite_diario,
                'meta_cantidad' => $metaCantidad,
                'progreso_actual' => $progresoActual,
                'porcentaje' => $porcentaje,
                'estado' => $estado,
                'metaData' => $metaData,
            ];
        });

        // Filtrar según pestaña de misiones seleccionada
        $misionesFiltradas = $misiones->filter(function ($mision) {
            if ($this->filtroMision === 'diarias') {
                return $mision['frecuencia'] === 'cada_vez';
            }
            if ($this->filtroMision === 'pendientes') {
                return in_array($mision['estado'], ['disponible', 'en_progreso']);
            }
            if ($this->filtroMision === 'completadas') {
                return in_array($mision['estado'], ['completada', 'completada_hoy']);
            }

            return true;
        });

        return view('livewire.gamificacion.panel-gamificacion', [
            'usuario' => $usuario,
            'insignias' => $insignias,
            'progresos' => $progresos,
            'reglasMeta' => $reglasMeta,
            'productos' => $productos,
            'canjesPorProducto' => $canjesPorProducto,
            'misSolicitudes' => $misSolicitudes,
            'misTransacciones' => $misTransacciones,
            'misiones' => $misionesFiltradas,
            'totalMisiones' => $misiones->count(),
            'totalPendientes' => $misiones->whereIn('estado', ['disponible', 'en_progreso'])->count(),
            'totalCompletadas' => $misiones->whereIn('estado', ['completada', 'completada_hoy'])->count(),
            'subtabHistorial' => $this->subtabHistorial,
            'historialFechaInicio' => $this->historialFechaInicio,
            'historialFechaFin' => $this->historialFechaFin,
        ]);
    }
}
