<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use App\Models\Curso;
use App\Models\Moneda;
use App\Models\TipoPago;
use App\Models\Role;
use App\Models\PasoCrecimiento;
use App\Models\TareaConsolidacion;
use Illuminate\Support\Str;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class CrearCurso extends Component
{
    use WithFileUploads;

    // Propiedades del Modelo Curso
    public $nombre;
    public $slug;
    public $descripcion_corta;
    public $descripcion_larga;
    public $imagen_portada; // Ahora será el archivo temporal
    public $video_preview_url;
    public $categoria_id; // Keeping for compatibility or specific category logic
    public $nivel_dificultad = 'Todas';
    public $es_obligatorio = false;
    public $estado = 'Borrador';
    public $orden_destacado = 0;

    // Advanced Configuration
    public $cupos_totales;
    public $dias_acceso_limitado;
    public $duracion_estimada_dias = 0;
    public $fecha_inicio;
    public $carrera_id;
    public $categorias_seleccionadas = [];

    // Precios y Pagos
    public $es_gratuito = false;
    public $precio = 0;
    public $precio_comparacion;
    public $moneda_id;
    public $tipos_pago_seleccionados = [];

    // Restricciones
    public $roles_seleccionados = [];

    // Listas para Selects
    public $monedas = [];
    public $tiposPagoList = []; // All types
    public $tiposPagoFiltrados = []; // Filtered types
    public $rolesList = [];
    public $carrerasList = [];
    public $categoriasList = [];

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:cursos,slug',
        'descripcion_corta' => 'nullable|string',
        'descripcion_larga' => 'nullable|string',
        'nivel_dificultad' => 'required|in:Principiante,Intermedio,Avanzado,Todas',
        'estado' => 'required|in:Borrador,Publicado,Inactivo',
        'precio' => 'required|numeric|min:0',
        'es_gratuito' => 'boolean',
        'moneda_id' => 'nullable|exists:monedas,id',
        'imagen_portada' => 'nullable|image|max:2048', // 2MB Max
        'video_preview_url' => 'nullable|url',
        'carrera_id' => 'nullable|exists:carreras,id',
        'cupos_totales' => 'nullable|integer|min:1',
        'dias_acceso_limitado' => 'nullable|integer|min:1',
        'duracion_estimada_dias' => 'nullable|integer|min:0',
        'categorias_seleccionadas' => 'array',
    ];

    public function mount()
    {
        $this->monedas = Moneda::all();
        $this->tiposPagoList = TipoPago::where('activo', true)->get();
        $this->rolesList = Role::all();
        $this->carrerasList = \App\Models\Carrera::where('estado', 'Activo')->get();
        $this->categoriasList = \App\Models\CategoriaCurso::all();

        $this->filtrarTiposPago();
    }

    public function updatedNombre($value)
    {
        $this->slug = Str::slug($value);
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

    public function updatedMonedaId()
    {
        $this->tipos_pago_seleccionados = []; // Reset selection when currency changes
        $this->filtrarTiposPago();
        $this->dispatch('initSelect2'); // Trigger re-init because options change
    }

    public function filtrarTiposPago()
    {
        if ($this->moneda_id) {
            $this->tiposPagoFiltrados = $this->tiposPagoList->filter(function ($tp) {
                // Return if unica_moneda_id matches or is null (if applicable, user said "solo cargar los donde unica_moneda_id sea... selected")
                // User said: "solo debe cargar los tipospago donde unica_moneda_id sea el de la moneda seleccionada"
                return $tp->unica_moneda_id == $this->moneda_id;
            });
        } else {
             // If no currency selected, maybe show none or all?
             // Logic: "ahora yo puedo elegir moneda usd y elegir un metodo de pago con moneda en pesos" -> suggests strict filtering.
             // If no currency, maybe only generic ones or none. Let's show all for now if no currency selected to allow user to pick currency first?
             // Or better, clear the list if no currency is selected to force selection.
             // User Request: "dependera de la moneda seleccionada"
             $this->tiposPagoFiltrados = collect([]);
        }
    }

    public function save()
    {
        $this->validate();

        $rutaImagen = null;
        if ($this->imagen_portada) {
            $configuracion = \App\Models\Configuracion::find(1);
            $directorio = $configuracion->ruta_almacenamiento . '/img/cursos/portadas';

            // Generar nombre personalizado: slug_portada_timestamp.ext
            $nombreArchivo = $this->slug . '-portada-' . time() . '.' . $this->imagen_portada->getClientOriginalExtension();

            // Guardar imagen y obtener solo el nombre para la BD
            $this->imagen_portada->storeAs($directorio, $nombreArchivo, 'public');
            $rutaImagen = $nombreArchivo;
        }

        // Normalize YouTube URL
        $videoUrl = $this->video_preview_url;
        if ($videoUrl) {
            $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
            if (preg_match($pattern, $videoUrl, $matches)) {
                $videoUrl = 'https://www.youtube.com/embed/' . $matches[1];
            }
        }

        $curso = Curso::create([
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
        if (!empty($this->roles_seleccionados)) {
            $curso->rolesRestringidos()->sync($this->roles_seleccionados);
        }

        if (!empty($this->tipos_pago_seleccionados)) {
            $curso->tiposPago()->sync($this->tipos_pago_seleccionados);
        }

        if (!empty($this->categorias_seleccionadas)) {
             $curso->categorias()->sync($this->categorias_seleccionadas);
        }

        return redirect()->route('cursos.gestionar')->with('success', 'Curso creado exitosamente.');
    }

    public function render()
    {
        return view('livewire.cursos.crear-curso');
    }
}
