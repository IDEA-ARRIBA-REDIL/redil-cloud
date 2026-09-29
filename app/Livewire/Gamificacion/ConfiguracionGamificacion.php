<?php

namespace App\Livewire\Gamificacion;

use App\Models\Insignia;
use App\Models\ReglaGamificacion;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConfiguracionGamificacion extends Component
{
    use WithFileUploads;

    // Control de Pestañas ('reglas' | 'insignias')
    public string $tabActivo = 'reglas';

    // Propiedades Formulario Regla
    public ?int $reglaId = null;

    public string $accion_codigo = '';

    public string $nombre = '';

    public string $frecuencia = 'cada_vez';

    public $meta_cantidad = 1;

    public $puntos_premio = 0;

    public ?int $insignia_id = null;

    // Propiedades Formulario Insignia
    public ?int $insigniaId = null;

    public string $insigniaNombre = '';

    public string $insigniaDescripcion = '';

    public string $tipo_diseno = 'icono'; // 'icono' | 'imagen'

    public string $icono_clase = 'ti ti-award';

    public string $icono_color = '#166534';

    public $imagen = null;

    public ?string $imagen_recortada = null;

    public ?string $imagen_existente = null;

    public $orden = 0;

    // Buscador
    public string $busquedaInsignias = '';

    // Filtros para Reglas
    public string $filtroAccion = '';

    public string $filtroFrecuencia = '';

    public string $filtroInsignia = '';

    /**
     * Limpia los filtros aplicados a las reglas.
     */
    public function limpiarFiltrosReglas(): void
    {
        $this->filtroAccion = '';
        $this->filtroFrecuencia = '';
        $this->filtroInsignia = '';
    }

    /**
     * Catálogo de triggers/acciones del sistema.
     */
    public function getTriggersDisponiblesProperty(): array
    {
        return [
            'rueda_de_la_vida' => 'Llenar Rueda de la Vida',
            'tiempo_con_dios' => 'Tiempo con Dios',
            'completar_perfil' => 'Completar Perfil',
            'asistencia_grupo' => 'Asistencia a Grupo',
            'peticion_oracion' => 'Registrar Petición de Oración',
            'aprobar_curso' => 'Aprobar Curso / Escuela',
            'asistencia_reunion' => 'Asistir a Culto / Reunión',
        ];
    }

    /**
     * Cambia la pestaña activa.
     */
    public function cambiarTab(string $tab): void
    {
        $this->tabActivo = $tab;
    }

    // =========================================================================
    // GESTIÓN DE REGLAS DE GAMIFICACIÓN
    // =========================================================================

    /**
     * Abre el modal/offcanvas para crear una nueva regla o editar una existente.
     */
    public function abrirModalRegla(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetFormularioRegla();

        if ($id) {
            $regla = ReglaGamificacion::findOrFail($id);
            $this->reglaId = $regla->id;
            $this->accion_codigo = $regla->accion_codigo;
            $this->nombre = $regla->nombre;
            $this->frecuencia = $regla->frecuencia;
            $this->meta_cantidad = $regla->meta_cantidad ?? 1;
            $this->puntos_premio = $regla->puntos_premio ?? 0;
            $this->insignia_id = $regla->insignia_id;
        }

        $this->dispatch('abrirOffcanvasRegla');
    }

    /**
     * Guarda una regla nueva o actualiza la existente.
     */
    public function guardarRegla(): void
    {
        $this->validate([
            'accion_codigo' => 'required|string',
            'nombre' => 'required|string|max:255',
            'frecuencia' => 'required|in:cada_vez,meta,unica_vez',
            'meta_cantidad' => 'required_if:frecuencia,meta|nullable|integer|min:1',
            'puntos_premio' => 'nullable|integer|min:0',
            'insignia_id' => 'nullable|exists:insignias,id',
        ], [
            'accion_codigo.required' => 'Debes seleccionar una acción a premiar.',
            'nombre.required' => 'La descripción interna de la regla es obligatoria.',
            'frecuencia.required' => 'Debes seleccionar una frecuencia de recompensa.',
            'meta_cantidad.required_if' => 'Indica cuántas veces debe repetir la acción.',
            'puntos_premio.integer' => 'Los puntos deben ser un número.',
            'puntos_premio.min' => 'Los puntos no pueden ser negativos.',
        ]);

        $datos = [
            'accion_codigo' => $this->accion_codigo,
            'nombre' => $this->nombre,
            'frecuencia' => $this->frecuencia,
            'meta_cantidad' => $this->frecuencia === 'meta' ? (int) ($this->meta_cantidad ?: 1) : 1,
            'puntos_premio' => (int) ($this->puntos_premio ?: 0),
            'insignia_id' => $this->insignia_id ?: null,
        ];

        if ($this->reglaId) {
            $regla = ReglaGamificacion::findOrFail($this->reglaId);
            $regla->update($datos);
            $mensaje = 'Regla de acción actualizada con éxito.';
        } else {
            ReglaGamificacion::create($datos);
            $mensaje = 'Regla de acción creada con éxito.';
        }

        $this->resetFormularioRegla();
        $this->dispatch('cerrarOffcanvasRegla');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Completado!',
            'texto' => $mensaje,
        ]);
    }

    /**
     * Elimina una regla de gamificación.
     */
    public function eliminarRegla(int $id): void
    {
        $regla = ReglaGamificacion::findOrFail($id);
        $regla->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'La regla ha sido eliminada correctamente.',
        ]);
    }

    /**
     * Resetea los campos del formulario de regla.
     */
    public function resetFormularioRegla(): void
    {
        $this->reglaId = null;
        $this->accion_codigo = '';
        $this->nombre = '';
        $this->frecuencia = 'cada_vez';
        $this->meta_cantidad = 1;
        $this->puntos_premio = 0;
        $this->insignia_id = null;
    }

    // =========================================================================
    // GESTIÓN DEL CATÁLOGO DE INSIGNIAS
    // =========================================================================

    /**
     * Abre el modal/offcanvas para crear una nueva insignia o editar una existente.
     */
    public function abrirModalInsignia(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetFormularioInsignia();

        if ($id) {
            $insignia = Insignia::findOrFail($id);
            $this->insigniaId = $insignia->id;
            $this->insigniaNombre = $insignia->nombre;
            $this->insigniaDescripcion = $insignia->descripcion ?? '';
            $this->icono_clase = $insignia->icono_clase ?: 'ti ti-award';
            $this->icono_color = $insignia->icono_color ?: '#166534';
            $this->imagen_existente = $insignia->getRawOriginal('imagen_url');
            $this->tipo_diseno = $this->imagen_existente ? 'imagen' : 'icono';
            $this->orden = $insignia->orden ?? 0;
        }

        $this->dispatch('abrirOffcanvasInsignia', [
            'imagenUrl' => $this->imagen_existente ? tenant_asset('img/insignias/'.$this->imagen_existente) : null,
            'placeholderUrl' => Storage::disk('global_media')->url('placeholder.jpg'),
        ]);
    }

    /**
     * Guarda una insignia nueva o actualiza la existente.
     */
    public function guardarInsignia(): void
    {
        $reglas = [
            'insigniaNombre' => 'required|string|max:255',
            'insigniaDescripcion' => 'nullable|string|max:1000',
            'tipo_diseno' => 'required|in:icono,imagen',
        ];

        if ($this->tipo_diseno === 'icono') {
            $reglas['icono_clase'] = 'required|string|max:100';
            $reglas['icono_color'] = 'required|string|max:30';
        } else {
            if (! $this->insigniaId && ! $this->imagen_recortada && ! $this->imagen && ! $this->imagen_existente) {
                $reglas['imagen_recortada'] = 'required';
            }
        }

        $this->validate($reglas, [
            'insigniaNombre.required' => 'El nombre de la insignia es obligatorio.',
            'icono_clase.required' => 'Ingresa la clase del ícono.',
            'imagen_recortada.required' => 'Debes seleccionar y recortar una imagen para la insignia.',
            'imagen.required' => 'Debes seleccionar una imagen para la insignia.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
        ]);

        $datos = [
            'nombre' => $this->insigniaNombre,
            'descripcion' => $this->insigniaDescripcion,
            'orden' => $this->orden ?: 0,
        ];

        if ($this->tipo_diseno === 'icono') {
            $datos['icono_clase'] = $this->icono_clase;
            $datos['icono_color'] = $this->icono_color;
            $datos['imagen_url'] = null;
        } else {
            $datos['icono_clase'] = null;
            $datos['icono_color'] = null;

            if ($this->imagen_recortada) {
                // Decodificar Base64 de CropperJS
                $imagenPartes = explode(';base64,', $this->imagen_recortada);
                $imagenBase64 = base64_decode(count($imagenPartes) > 1 ? $imagenPartes[1] : $imagenPartes[0]);
                $nombreArchivo = 'insignia-'.time().'.png';
                Storage::disk('public')->put('img/insignias/'.$nombreArchivo, $imagenBase64);
                $datos['imagen_url'] = $nombreArchivo;
            } elseif ($this->imagen) {
                $nombreArchivo = 'insignia-'.time().'.'.$this->imagen->getClientOriginalExtension();
                $this->imagen->storeAs('img/insignias', $nombreArchivo, 'public');
                $datos['imagen_url'] = $nombreArchivo;
            }
        }

        if ($this->insigniaId) {
            $insignia = Insignia::findOrFail($this->insigniaId);

            // Eliminar imagen anterior si se sube una nueva o se cambia a ícono
            if (($this->imagen_recortada || $this->imagen || $this->tipo_diseno === 'icono') && $insignia->getRawOriginal('imagen_url')) {
                Storage::disk('public')->delete('img/insignias/'.$insignia->getRawOriginal('imagen_url'));
            }

            $insignia->update($datos);
            $mensaje = 'Insignia actualizada con éxito.';
        } else {
            Insignia::create($datos);
            $mensaje = 'Insignia creada con éxito.';
        }

        $this->resetFormularioInsignia();
        $this->dispatch('cerrarOffcanvasInsignia');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Completado!',
            'texto' => $mensaje,
        ]);
    }

    /**
     * Elimina una insignia del catálogo.
     */
    public function eliminarInsignia(int $id): void
    {
        $insignia = Insignia::findOrFail($id);
        $insignia->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminada!',
            'texto' => 'La insignia ha sido eliminada del catálogo.',
        ]);
    }

    /**
     * Resetea los campos del formulario de insignia.
     */
    public function resetFormularioInsignia(): void
    {
        $this->insigniaId = null;
        $this->insigniaNombre = '';
        $this->insigniaDescripcion = '';
        $this->tipo_diseno = 'icono';
        $this->icono_clase = 'ti ti-award';
        $this->icono_color = '#166534';
        $this->imagen = null;
        $this->imagen_recortada = null;
        $this->imagen_existente = null;
        $this->orden = 0;
    }

    /**
     * Normaliza un texto eliminando acentos y convirtiéndolo a minúsculas.
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
        $todasInsignias = Insignia::orderBy('orden', 'asc')->get();

        // Consulta de Reglas con filtros
        $queryReglas = ReglaGamificacion::with('insignia');

        if (! empty($this->filtroAccion)) {
            $queryReglas->where('accion_codigo', $this->filtroAccion);
        }

        if (! empty($this->filtroFrecuencia)) {
            $queryReglas->where('frecuencia', $this->filtroFrecuencia);
        }

        if (! empty($this->filtroInsignia)) {
            if ($this->filtroInsignia === 'con_insignia') {
                $queryReglas->whereNotNull('insignia_id');
            } elseif ($this->filtroInsignia === 'sin_insignia') {
                $queryReglas->whereNull('insignia_id');
            } else {
                $queryReglas->where('insignia_id', $this->filtroInsignia);
            }
        }

        $reglas = $queryReglas->latest()->get();

        // Consulta de Insignias con búsqueda
        $insignias = $todasInsignias;
        if (! empty(trim($this->busquedaInsignias))) {
            $termino = $this->normalizarTexto($this->busquedaInsignias);
            $insignias = $todasInsignias->filter(function ($insignia) use ($termino) {
                $nombre = $this->normalizarTexto($insignia->nombre ?? '');
                $descripcion = $this->normalizarTexto($insignia->descripcion ?? '');

                return str_contains($nombre, $termino) || str_contains($descripcion, $termino);
            });
        }

        return view('livewire.gamificacion.configuracion-gamificacion', [
            'reglas' => $reglas,
            'insignias' => $insignias,
            'todasInsignias' => $todasInsignias,
            'triggers' => $this->triggersDisponibles,
        ]);
    }
}
