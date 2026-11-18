<?php

namespace App\Livewire\Dashboard;

use App\Models\AccionDashboard;
use App\Models\Role;
use App\Models\TipoUsuario;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class GestionarAccionesDashboard extends Component
{
    use WithFileUploads;

    public ?int $accionId = null;

    public string $nombre = '';

    public string $url = '';

    public bool $abrirNuevaPestana = false;

    public string $tipoVisual = 'icono';

    public string $iconoClase = 'ti ti-apps';

    public string $iconoColor = '#2F5D50';

    public $archivoPng = null;

    public ?string $imagenExistente = null;

    public ?string $imagenExistenteUrl = null;

    public bool $visibleTodos = true;

    /** @var array<int, int|string> */
    public array $tiposUsuariosSeleccionados = [];

    /** @var array<int, int|string> */
    public array $rolesSeleccionados = [];

    public int $orden = 1;

    public bool $activo = true;

    public string $busqueda = '';

    public string $busquedaIcono = '';

    /**
     * Catálogo de íconos propios de la plantilla (Tabler Icons) usados en REDIL Cloud.
     *
     * @return array<int, array{clase: string, etiqueta: string}>
     */
    public function getCatalogoIconosProperty(): array
    {
        $iconos = [
            ['clase' => 'ti ti-school', 'etiqueta' => 'Academia / Escuela'],
            ['clase' => 'ti ti-users-group', 'etiqueta' => 'Grupos'],
            ['clase' => 'ti ti-users', 'etiqueta' => 'Personas'],
            ['clase' => 'ti ti-building-church', 'etiqueta' => 'Iglesia / Reuniones'],
            ['clase' => 'ti ti-pray', 'etiqueta' => 'Tiempo con Dios'],
            ['clase' => 'ti ti-bible', 'etiqueta' => 'Biblia / Versículos'],
            ['clase' => 'ti ti-book', 'etiqueta' => 'Plan Lector / Libro'],
            ['clase' => 'ti ti-heart-handshake', 'etiqueta' => 'Consolidación'],
            ['clase' => 'ti ti-messages', 'etiqueta' => 'Consejería / Chat'],
            ['clase' => 'ti ti-home-heart', 'etiqueta' => 'Familias'],
            ['clase' => 'ti ti-circle-dashed-check', 'etiqueta' => 'Rueda de la vida'],
            ['clase' => 'ti ti-notes', 'etiqueta' => 'Peticiones'],
            ['clase' => 'ti ti-baby-carriage', 'etiqueta' => 'Iglesia Infantil'],
            ['clase' => 'ti ti-device-laptop', 'etiqueta' => 'Cursos LMS'],
            ['clase' => 'ti ti-calendar-star', 'etiqueta' => 'Actividades / Eventos'],
            ['clase' => 'ti ti-calendar-event', 'etiqueta' => 'Calendario'],
            ['clase' => 'ti ti-building-store', 'etiqueta' => 'Puntos de pago'],
            ['clase' => 'ti ti-cash-banknote', 'etiqueta' => 'Finanzas'],
            ['clase' => 'ti ti-coin', 'etiqueta' => 'Ofrendas / Donaciones'],
            ['clase' => 'ti ti-credit-card', 'etiqueta' => 'Pagos'],
            ['clase' => 'ti ti-shopping-bag', 'etiqueta' => 'Tienda / Canjes'],
            ['clase' => 'ti ti-award', 'etiqueta' => 'Gamificación / Insignia'],
            ['clase' => 'ti ti-trophy', 'etiqueta' => 'Hitos / Logros'],
            ['clase' => 'ti ti-album', 'etiqueta' => 'Línea de vida'],
            ['clase' => 'ti ti-cards', 'etiqueta' => 'Publicaciones'],
            ['clase' => 'ti ti-blockquote', 'etiqueta' => 'Temas'],
            ['clase' => 'ti ti-report', 'etiqueta' => 'Informes'],
            ['clase' => 'ti ti-chart-bar', 'etiqueta' => 'Estadísticas'],
            ['clase' => 'ti ti-building', 'etiqueta' => 'Sedes'],
            ['clase' => 'ti ti-map-pin', 'etiqueta' => 'Ubicación / Mapa'],
            ['clase' => 'ti ti-video', 'etiqueta' => 'Videos'],
            ['clase' => 'ti ti-music', 'etiqueta' => 'Música / Alabanza'],
            ['clase' => 'ti ti-bell-ringing', 'etiqueta' => 'Notificaciones'],
            ['clase' => 'ti ti-cake', 'etiqueta' => 'Cumpleaños'],
            ['clase' => 'ti ti-flame', 'etiqueta' => 'Racha / Fuego'],
            ['clase' => 'ti ti-star', 'etiqueta' => 'Destacado / Estrella'],
            ['clase' => 'ti ti-world', 'etiqueta' => 'Sitio Web'],
            ['clase' => 'ti ti-link', 'etiqueta' => 'Enlace externo'],
            ['clase' => 'ti ti-brand-whatsapp', 'etiqueta' => 'WhatsApp'],
            ['clase' => 'ti ti-brand-youtube', 'etiqueta' => 'YouTube'],
            ['clase' => 'ti ti-brand-instagram', 'etiqueta' => 'Instagram'],
            ['clase' => 'ti ti-File-text', 'etiqueta' => 'Formulario / Documento'],
            ['clase' => 'ti ti-speakerphone', 'etiqueta' => 'Anuncios'],
            ['clase' => 'ti ti-lifebuoy', 'etiqueta' => 'Ayuda / Soporte'],
        ];

        $termino = mb_strtolower(trim($this->busquedaIcono), 'UTF-8');
        if ($termino === '') {
            return $iconos;
        }

        return array_values(array_filter($iconos, function (array $item) use ($termino): bool {
            return str_contains(mb_strtolower($item['etiqueta'], 'UTF-8'), $termino)
                || str_contains(mb_strtolower($item['clase'], 'UTF-8'), $termino);
        }));
    }

    /**
     * Selecciona un ícono del catálogo interactivo de la plantilla.
     */
    public function seleccionarIconoPlantilla(string $clase): void
    {
        $this->iconoClase = $clase;
    }

    /**
     * Abre el offcanvas para crear o editar una acción del dashboard.
     */
    public function abrirModalAccion(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetFormulario();

        if ($id) {
            $accion = AccionDashboard::with(['tiposUsuarios:id', 'roles:id'])->findOrFail($id);
            $this->accionId = $accion->id;
            $this->nombre = $accion->nombre;
            $this->url = $accion->url;
            $this->abrirNuevaPestana = (bool) $accion->abrir_nueva_pestana;
            $this->tipoVisual = $accion->tipo_visual ?: 'icono';
            $this->iconoClase = $accion->icono_clase ?: 'ti ti-apps';
            $this->iconoColor = $accion->icono_color ?: '#2F5D50';
            $this->imagenExistente = $accion->imagen;
            $this->imagenExistenteUrl = $accion->imagen_url;
            $this->visibleTodos = (bool) $accion->visible_todos;
            $this->tiposUsuariosSeleccionados = $accion->tiposUsuarios->pluck('id')->map(fn ($v) => (string) $v)->all();
            $this->rolesSeleccionados = $accion->roles->pluck('id')->map(fn ($v) => (string) $v)->all();
            $this->orden = (int) $accion->orden;
            $this->activo = (bool) $accion->activo;
        } else {
            $maxOrden = (int) AccionDashboard::max('orden');
            $this->orden = $maxOrden + 1;
        }

        $this->dispatch('abrirOffcanvasAccionDashboard', [
            'tiposUsuarios' => $this->tiposUsuariosSeleccionados,
            'roles' => $this->rolesSeleccionados,
        ]);
    }

    /**
     * Guarda una nueva acción o actualiza una existente validando todos sus parámetros.
     */
    public function guardarAccion(): void
    {
        $reglas = [
            'nombre' => 'required|string|max:80',
            'url' => 'required|string|max:500',
            'tipoVisual' => 'required|in:icono,imagen',
            'orden' => 'required|integer|min:0',
            'visibleTodos' => 'boolean',
        ];

        $mensajes = [
            'nombre.required' => 'El título del elemento es obligatorio.',
            'nombre.max' => 'El título no debe superar los 80 caracteres.',
            'url.required' => 'El enlace (link) de redirección es obligatorio.',
            'iconoClase.required' => 'Debes elegir un ícono de la plantilla.',
            'archivoPng.required' => 'Debes subir un archivo en formato PNG.',
            'archivoPng.mimes' => 'La imagen debe ser estrictamente un archivo en formato PNG (.png).',
            'archivoPng.max' => 'El archivo PNG no debe superar los 2 MB.',
        ];

        if ($this->tipoVisual === 'icono') {
            $reglas['iconoClase'] = 'required|string|max:100';
            $reglas['iconoColor'] = 'required|string|max:30';
        } else {
            if (! $this->accionId && ! $this->imagenExistente) {
                $reglas['archivoPng'] = 'required|file|mimes:png|max:2048';
            } elseif ($this->archivoPng) {
                $reglas['archivoPng'] = 'nullable|file|mimes:png|max:2048';
            }
        }

        $this->validate($reglas, $mensajes);

        // 1. Validar que si no es visible para todos, tenga al menos un Tipo de Usuario o Rol seleccionado
        if (! $this->visibleTodos && empty($this->tiposUsuariosSeleccionados) && empty($this->rolesSeleccionados)) {
            $this->addError('segmentacion', 'Selecciona al menos un Tipo de Usuario o un Rol que pueda ver este elemento, o marca "Visible para todos".');

            return;
        }

        // 2. Preparar datos base del registro
        $datos = [
            'nombre' => trim($this->nombre),
            'url' => trim($this->url),
            'abrir_nueva_pestana' => $this->abrirNuevaPestana,
            'tipo_visual' => $this->tipoVisual,
            'icono_clase' => $this->iconoClase ?: 'ti ti-apps',
            'icono_color' => $this->iconoColor ?: '#2F5D50',
            'visible_todos' => $this->visibleTodos,
            'orden' => (int) $this->orden,
            'activo' => $this->activo,
        ];

        // 3. Procesar subida de imagen PNG en el disco 'public' del tenant
        $directorio = 'img/acciones-dashboard';
        $imagenesGlobalesSistema = ['Funcionalidad-escuelas.png', 'Funcionalidad-grupos.png', 'Funcionalidad-cumpleaños.png'];

        if ($this->tipoVisual === 'imagen' && $this->archivoPng) {
            $nombreArchivo = Str::slug($this->nombre).'-'.time().'.'.$this->archivoPng->getClientOriginalExtension();
            $this->archivoPng->storeAs($directorio, $nombreArchivo, 'public');
            $datos['imagen'] = $nombreArchivo;
        }

        if ($this->accionId) {
            $accion = AccionDashboard::findOrFail($this->accionId);

            // 4. Eliminar archivo PNG anterior del tenant si fue reemplazado por otro
            if (
                isset($datos['imagen']) &&
                $accion->imagen &&
                ! in_array($accion->imagen, $imagenesGlobalesSistema, true) &&
                Storage::disk('public')->exists($directorio.'/'.$accion->imagen)
            ) {
                Storage::disk('public')->delete($directorio.'/'.$accion->imagen);
            }

            $accion->update($datos);
            $mensaje = 'El acceso del dashboard fue actualizado correctamente.';
        } else {
            $accion = AccionDashboard::create($datos);
            $mensaje = 'El nuevo acceso del dashboard fue creado correctamente.';
        }

        // 5. Sincronizar restricciones por Tipo de Usuario y Rol
        if ($this->visibleTodos) {
            $accion->tiposUsuarios()->sync([]);
            $accion->roles()->sync([]);
        } else {
            $tiposIds = array_values(array_filter(array_map('intval', $this->tiposUsuariosSeleccionados)));
            $rolesIds = array_values(array_filter(array_map('intval', $this->rolesSeleccionados)));
            $accion->tiposUsuarios()->sync($tiposIds);
            $accion->roles()->sync($rolesIds);
        }

        $this->resetFormulario();
        $this->dispatch('cerrarOffcanvasAccionDashboard');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Guardado!',
            'texto' => $mensaje,
        ]);
    }

    /**
     * Alterna el estado visible/oculto de una acción directamente desde la tabla.
     */
    public function toggleActivo(int $id): void
    {
        $accion = AccionDashboard::findOrFail($id);
        $accion->update(['activo' => ! $accion->activo]);
    }

    /**
     * Sube una posición en el orden de visualización.
     */
    public function subirOrden(int $id): void
    {
        $accion = AccionDashboard::findOrFail($id);
        $anterior = AccionDashboard::where('orden', '<', $accion->orden)
            ->orderBy('orden', 'desc')
            ->first();

        if ($anterior) {
            $ordenTemp = $accion->orden;
            $accion->update(['orden' => $anterior->orden]);
            $anterior->update(['orden' => $ordenTemp]);
        } elseif ($accion->orden > 1) {
            $accion->update(['orden' => $accion->orden - 1]);
        }
    }

    /**
     * Baja una posición en el orden de visualización.
     */
    public function bajarOrden(int $id): void
    {
        $accion = AccionDashboard::findOrFail($id);
        $siguiente = AccionDashboard::where('orden', '>', $accion->orden)
            ->orderBy('orden', 'asc')
            ->first();

        if ($siguiente) {
            $ordenTemp = $accion->orden;
            $accion->update(['orden' => $siguiente->orden]);
            $siguiente->update(['orden' => $ordenTemp]);
        } else {
            $accion->update(['orden' => $accion->orden + 1]);
        }
    }

    /**
     * Elimina una acción del dashboard y su imagen asociada en el tenant si aplica.
     */
    public function eliminarAccion(int $id): void
    {
        $accion = AccionDashboard::findOrFail($id);
        $imagenesGlobalesSistema = ['Funcionalidad-escuelas.png', 'Funcionalidad-grupos.png', 'Funcionalidad-cumpleaños.png'];

        if (
            $accion->imagen &&
            ! in_array($accion->imagen, $imagenesGlobalesSistema, true) &&
            Storage::disk('public')->exists('img/acciones-dashboard/'.$accion->imagen)
        ) {
            Storage::disk('public')->delete('img/acciones-dashboard/'.$accion->imagen);
        }

        $accion->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'El enlace ha sido eliminado del dashboard.',
        ]);
    }

    /**
     * Restablece las propiedades del formulario a sus valores por defecto.
     */
    public function resetFormulario(): void
    {
        $this->accionId = null;
        $this->nombre = '';
        $this->url = '';
        $this->abrirNuevaPestana = false;
        $this->tipoVisual = 'icono';
        $this->iconoClase = 'ti ti-apps';
        $this->iconoColor = '#2F5D50';
        $this->archivoPng = null;
        $this->imagenExistente = null;
        $this->imagenExistenteUrl = null;
        $this->visibleTodos = true;
        $this->tiposUsuariosSeleccionados = [];
        $this->rolesSeleccionados = [];
        $this->orden = 1;
        $this->activo = true;
        $this->busquedaIcono = '';
    }

    public function render(): View
    {
        $acciones = AccionDashboard::with(['tiposUsuarios:id,nombre', 'roles:id,name'])
            ->when(trim($this->busqueda) !== '', function ($query) {
                $termino = '%'.trim($this->busqueda).'%';
                $query->where(function ($q) use ($termino) {
                    $q->where('nombre', 'like', $termino)
                        ->orWhere('url', 'like', $termino);
                });
            })
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')->orderBy('nombre', 'asc')->get(['id', 'nombre']);
        $roles = Role::orderBy('name', 'asc')->get(['id', 'name']);

        return view('livewire.dashboard.gestionar-acciones-dashboard', [
            'acciones' => $acciones,
            'tiposUsuarios' => $tiposUsuarios,
            'roles' => $roles,
            'catalogoIconos' => $this->catalogoIconos,
        ]);
    }
}
