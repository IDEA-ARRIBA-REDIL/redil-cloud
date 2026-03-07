<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use App\Models\Curso;
use App\Models\Moneda;
use App\Models\TipoPago;
use App\Models\Role;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EditarCurso extends Component
{
    use WithFileUploads;

    public $curso; // Instancia del modelo

    // Propiedades del Modelo Curso
    public $nombre;
    public $slug;
    public $descripcion_corta;
    public $descripcion_larga;
    public $imagen_portada; // Nueva imagen (upload)
    public $imagen_actual; // URL o path de la imagen actual
    public $video_preview_url;
    public $categoria_id;
    public $nivel_dificultad;
    public $es_obligatorio;
    public $estado;
    public $orden_destacado;

    // Advanced Configuration
    public $cupos_totales;
    public $dias_acceso_limitado;
    public $duracion_estimada_dias;
    public $fecha_inicio;
    public $carrera_id;
    public $categorias_seleccionadas = [];

    // Precios y Pagos
    public $es_gratuito;
    public $precio;
    public $precio_comparacion;
    public $moneda_id;
    public $tipos_pago_seleccionados = [];

    // Restricciones
    public $roles_seleccionados = [];

    // Listas para Selects
    public $monedas = [];
    public $tiposPagoList = [];
    public $tiposPagoFiltrados = [];
    public $rolesList = [];
    public $carrerasList = [];
    public $categoriasList = [];

    public function mount(Curso $curso)
    {
        $this->curso = $curso;

        // Cargar datos básicos
        $this->nombre = $curso->nombre;
        $this->slug = $curso->slug;
        $this->descripcion_corta = $curso->descripcion_corta;
        $this->descripcion_larga = $curso->descripcion_larga;
        $this->imagen_actual = $curso->imagen_portada;
        $this->video_preview_url = $curso->video_preview_url;
        $this->categoria_id = $curso->categoria_id;
        $this->nivel_dificultad = $curso->nivel_dificultad;
        $this->es_obligatorio = $curso->es_obligatorio;
        $this->estado = $curso->estado;
        $this->orden_destacado = $curso->orden_destacado;
        $this->cupos_totales = $curso->cupos_totales;
        $this->dias_acceso_limitado = $curso->dias_acceso_limitado;
        $this->duracion_estimada_dias = $curso->duracion_estimada_dias;
        $this->fecha_inicio = $curso->fecha_inicio;
        $this->carrera_id = $curso->carrera_id;

        // Cargar precios
        $this->es_gratuito = (bool) $curso->es_gratuito;
        $this->precio = $curso->precio;
        $this->precio_comparacion = $curso->precio_comparacion;
        $this->moneda_id = $curso->moneda_id;

        // Cargar listas
        $this->monedas = Moneda::all();
        $this->tiposPagoList = TipoPago::all();
        $this->rolesList = Role::all();
        $this->carrerasList = \App\Models\Carrera::where('estado', 'Activo')->get();
        $this->categoriasList = \App\Models\CategoriaCurso::all();

        // Cargar relaciones
        $this->tipos_pago_seleccionados = $curso->tiposPago->pluck('id')->toArray();
        $this->categorias_seleccionadas = $curso->categorias->pluck('id')->toArray();
        $this->roles_seleccionados = $curso->rolesRestringidos->pluck('id')->toArray();

        // Inicializar filtros
        $this->filtrarTiposPago();
    }

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('cursos', 'slug')->ignore($this->curso->id)],
            'descripcion_corta' => 'nullable|string',
            'descripcion_larga' => 'nullable|string',
            'nivel_dificultad' => 'required|in:Principiante,Intermedio,Avanzado,Todas',
            'estado' => 'required|in:Borrador,Publicado,Inactivo',
            'precio' => 'required|numeric|min:0',
            'es_gratuito' => 'boolean',
            'moneda_id' => 'nullable|exists:monedas,id',
            'imagen_portada' => 'nullable|image|max:2048',
            'video_preview_url' => 'nullable|url',
            'carrera_id' => 'nullable|exists:carreras,id',
            'cupos_totales' => 'nullable|integer|min:1',
            'dias_acceso_limitado' => 'nullable|integer|min:1',
            'duracion_estimada_dias' => 'nullable|integer|min:0',
            'categorias_seleccionadas' => 'array',
        ];
    }

    public function updatedNombre($value)
    {
        // Solo actualizar slug si está vacío o si el usuario no lo ha modificado manualmente (opcional, aquí lo forzamos a seguir el nombre)
        // O mejor, dejemos que slug sea editable pero se prellene.
        // Si queremos que se actualice al editar nombre:
        $this->slug = Str::slug($value);
    }

    public function updatedMonedaId()
    {
        $this->tipos_pago_seleccionados = [];
        $this->filtrarTiposPago();
        $this->dispatch('initSelect2');
    }

    public function updatedEsGratuito()
    {
        if ($this->es_gratuito) {
            $this->precio = 0;
            $this->precio_comparacion = null;
            $this->moneda_id = null;
            $this->tipos_pago_seleccionados = [];
        }
    }

    public function filtrarTiposPago()
    {
        if ($this->moneda_id) {
            $this->tiposPagoFiltrados = $this->tiposPagoList->filter(function ($tp) {
                return $tp->unica_moneda_id == $this->moneda_id;
            });
        } else {
            // Si es edición, tal vez queramos mostrar los que ya tiene seleccionados o todos?
            // Mantener lógica de crear: si no hay moneda, vacio.
            $this->tiposPagoFiltrados = collect([]);
        }
    }

    public function update()
    {
        $this->validate();

        $rutaImagen = $this->imagen_actual;

        if ($this->imagen_portada) {
            $configuracion = \App\Models\Configuracion::find(1);
            $directorio = $configuracion->ruta_almacenamiento . '/img/cursos/portadas';

            // Generar nombre personalizado: ID_portada_timestamp.ext
            $nombreImagen = $this->curso->id . '-portada-' . time() . '.' . $this->imagen_portada->getClientOriginalExtension();

            // Guardar imagen con el nombre personalizado
            $this->imagen_portada->storeAs($directorio, $nombreImagen, 'public');

            // Guardar SOLO el nombre del archivo en la BD
            $rutaImagen = $nombreImagen;

            // Opcional: Eliminar imagen anterior si existe y es diferente
            // if ($this->imagen_actual && Storage::disk('public')->exists($directorio . '/' . $this->imagen_actual)) {
            //    Storage::disk('public')->delete($directorio . '/' . $this->imagen_actual);
            // }
        }

        // Normalize YouTube URL
        $videoUrl = $this->video_preview_url;
        if ($videoUrl) {
            $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
            if (preg_match($pattern, $videoUrl, $matches)) {
                $videoUrl = 'https://www.youtube.com/embed/' . $matches[1];
            }
        }

        $this->curso->update([
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'descripcion_corta' => $this->descripcion_corta,
            'descripcion_larga' => $this->descripcion_larga,
            'imagen_portada' => $rutaImagen,
            'video_preview_url' => $videoUrl,
            'categoria_id' => $this->categoria_id,
            'carrera_id' => $this->carrera_id,
            'nivel_dificultad' => $this->nivel_dificultad,
            'es_obligatorio' => $this->es_obligatorio,
            'estado' => $this->estado,
            'orden_destacado' => $this->orden_destacado,
            'cupos_totales' => $this->cupos_totales,
            'dias_acceso_limitado' => $this->dias_acceso_limitado,
            'duracion_estimada_dias' => $this->duracion_estimada_dias,
            'fecha_inicio' => $this->fecha_inicio,
            'es_gratuito' => $this->es_gratuito,
            'precio' => $this->precio,
            'precio_comparacion' => $this->precio_comparacion,
            'moneda_id' => $this->moneda_id,
        ]);

        // Sync relaciones
        $this->curso->rolesRestringidos()->sync($this->roles_seleccionados);
        $this->curso->tiposPago()->sync($this->tipos_pago_seleccionados);
        $this->curso->categorias()->sync($this->categorias_seleccionadas);

        return redirect()->route('cursos.gestionar')->with('success', 'Curso actualizado exitosamente.');
    }

    public function render()
    {
        return view('livewire.cursos.editar-curso');
    }
}
