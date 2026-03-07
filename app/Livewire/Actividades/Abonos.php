<?php

namespace App\Livewire\Actividades;

use App\Models\Abono;
use App\Models\AbonoCategoria;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Moneda;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;

class Abonos extends Component
{
    public $actividad;
    public $abonosActividad = [];
    public $categoriasActividad = [];
    public $esGratuita = false;

    // Propiedades para el modal NUEVO
    public $fecha_inicio_abono;
    public $fecha_fin_abono;
    public $valoresMonedasNuevo = [];
    public $valoresFaltantes = [];

    // Propiedades para el modal EDITAR
    public $abonoActividadEditar;
    public $fechaInicioAbonoEditar;
    public $fechaFinAbonoEditar;
    public $valoresMonedasEditar = [];
    public $abonoCategoriaIdsEditar = [];

    // Propiedad para el cálculo de restantes en el panel izquierdo
    public $abonosExistentesPorCategoria = [];

    // Propiedad para el cálculo de restantes en el panel izquierdo


    public function mount()
    {
        // 1. Determinar si la actividad es gratuita (sin cambios)
        $costoTotal = 0;
        foreach ($this->actividad->categorias as $categoria) {
            $costoTotal += $categoria->monedas()->sum('valor');
        }
        $this->esGratuita = ($costoTotal == 0);

        // 2. Cargar datos básicos de la actividad (sin cambios)
        $this->categoriasActividad = $this->actividad->categorias;
        $categoriasIds = $this->categoriasActividad->pluck('id')->toArray();

        // 3. Cargar los abonos de la actividad de forma eficiente (sin cambios en la consulta inicial)
        $this->abonosActividad = Abono::whereHas('abonoCategorias', function ($query) use ($categoriasIds) {
            $query->whereIn('actividad_categoria_id', $categoriasIds);
        })
            ->with([
                'abonoCategorias.categoria', // Simplificado para cargar la relación completa
                'abonoCategorias.moneda'
            ])
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        // --- INICIO DE LA CORRECCIÓN ---
        // 4. Filtrar la relación anidada para excluir abonos de otras actividades
        $this->abonosActividad->each(function ($abono) {
            // Reemplazamos la relación 'abonoCategorias' con una versión filtrada
            $abono->setRelation('abonoCategorias', $abono->abonoCategorias->filter(function ($abonoCategoria) {
                // Mantenemos la cuota solo si la categoría a la que pertenece
                // tiene el mismo ID de actividad que la actividad actual.
                return $abonoCategoria->categoria->actividad_id == $this->actividad->id;
            }));
        });
        // --- FIN DE LA CORRECCIÓN ---


        // 5. Resetear y calcular los valores restantes (sin cambios)
        $this->abonosExistentesPorCategoria = [];
        $this->valoresFaltantes = [];

        foreach ($this->categoriasActividad as $categoria) {
            foreach ($categoria->monedas as $moneda) {
                $valorMaximo = $moneda->pivot->valor ?? 0;

                // Usamos la relación filtrada para sumar correctamente
                $totalAbonado = 0;
                foreach ($this->abonosActividad as $abono) {
                    $totalAbonado += $abono->abonoCategorias
                        ->where('actividad_categoria_id', $categoria->id)
                        ->where('moneda_id', $moneda->id)
                        ->sum('valor');
                }

                $valorResultado = $valorMaximo - $totalAbonado;

                $this->abonosExistentesPorCategoria[$categoria->id][$moneda->id] = [
                    'total_abonado' => $totalAbonado,
                    'restante' => $valorResultado,
                ];

                if ($valorResultado > 0) {
                    $this->valoresFaltantes[] = [
                        'categoria' => $categoria->nombre,
                        'moneda' => $moneda->nombre_corto,
                        'valor' => $valorResultado
                    ];
                }
            }
        }
    }

    public function nuevoAbono()
    {
        // Esta lógica ya funciona bien, la dejamos como está.
        $this->validate([
            'fecha_inicio_abono' => 'required|date',
            'fecha_fin_abono' => 'required|date|after_or_equal:fecha_inicio_abono',
        ]);
        // Aquí iría tu lógica de validación de fechas y valores...

        // Crear abono
        $nuevoAbono = Abono::create([
            'fecha_inicio' => $this->fecha_inicio_abono,
            'fecha_fin' => $this->fecha_fin_abono,
        ]);

        // Guardar abonos por categoría
        foreach ($this->valoresMonedasNuevo as $categoriaId => $monedas) {
            foreach ($monedas as $monedaId => $valor) {
                if ($valor !== null && $valor !== '') {
                    AbonoCategoria::create([
                        'abono_id' => $nuevoAbono->id,
                        'actividad_categoria_id' => $categoriaId,
                        'valor' => $valor,
                        'moneda_id' => $monedaId,
                    ]);
                }
            }
        }

        $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Muy bien!', msnTexto: 'El abono se creó con éxito.');
        $this->dispatch('cerrarModal', nombreModal: 'modalNuevoAbono');
        $this->reset(['fecha_inicio_abono', 'fecha_fin_abono', 'valoresMonedasNuevo']);
        $this->mount();
    }

    // =================================================================
    // =========== LÓGICA DE EDICIÓN (AQUÍ ESTÁ LA SOLUCIÓN) ===========
    // =================================================================

    public function abrirModalActualizarAbono($abonoId)
    {
        // 1. Cargar el abono con sus relaciones de forma eficiente
        $this->abonoActividadEditar = Abono::with('abonoCategorias')->findOrFail($abonoId);

        // 2. Asignar las fechas
        $this->fechaInicioAbonoEditar = Carbon::parse($this->abonoActividadEditar->fecha_fin)->format('Y-m-d');
        $this->fechaFinAbonoEditar = Carbon::parse($this->abonoActividadEditar->fecha_inicio)->format('Y-m-d');

        // 3. Resetear los arrays para evitar datos de ediciones anteriores
        $this->valoresMonedasEditar = [];
        $this->abonoCategoriaIdsEditar = [];

        // 4. Llenar los arrays con la estructura que la vista necesita
        foreach ($this->abonoActividadEditar->abonoCategorias as $abonoCategoria) {
            $categoriaId = $abonoCategoria->actividad_categoria_id;
            $monedaId = $abonoCategoria->moneda_id;

            // Llenamos el array para los inputs del formulario (wire:model)
            $this->valoresMonedasEditar[$categoriaId][$monedaId] = $abonoCategoria->valor;

            // Llenamos el array de mapeo para saber qué ID de registro actualizar después
            $this->abonoCategoriaIdsEditar[$categoriaId][$monedaId] = $abonoCategoria->id;
        }

        // 5. Abrir el modal
        $this->dispatch('abrirModal', nombreModal: 'modalEditarAbono');
    }

    public function actualizarAbono()
    {
        $this->validate([
            'fechaInicioAbonoEditar' => 'required|date',
            'fechaFinAbonoEditar' => 'required|date|after_or_equal:fechaInicioAbonoEditar',
        ]);

        // 1. Actualizar las fechas del Abono principal
        $this->abonoActividadEditar->update([
            'fecha_inicio' => $this->fechaInicioAbonoEditar,
            'fecha_fin' => $this->fechaFinAbonoEditar,
        ]);

        // 2. Iterar sobre la estructura anidada de valores que viene del formulario
        foreach ($this->valoresMonedasEditar as $categoriaId => $monedas) {
            foreach ($monedas as $monedaId => $valor) {

                // 3. Usar el array de mapeo para obtener el ID del registro a actualizar
                $abonoCategoriaId = $this->abonoCategoriaIdsEditar[$categoriaId][$monedaId] ?? null;

                if ($abonoCategoriaId) {
                    $abonoCategoria = AbonoCategoria::find($abonoCategoriaId);

                    // Aquí puedes añadir tu lógica de validación de valores si lo necesitas
                    // Por ejemplo: validar que el nuevo $valor no exceda el máximo permitido

                    // 4. Actualizar el valor
                    $abonoCategoria->update(['valor' => $valor]);
                }
            }
        }

        $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Muy bien!', msnTexto: 'El abono se ha editado con éxito.');
        $this->dispatch('cerrarModal', nombreModal: 'modalEditarAbono');
        $this->mount(); // Refrescar los datos de la vista
        $this->dispatch('valoresFaltantesActualizados', valores: $this->valoresFaltantes);
    }


    // =================================================================
    // ======================= LÓGICA DE ELIMINACIÓN =====================
    // =================================================================

    public function confirmarEliminarAbonoCategoria($abonoId)
    {
        // El nombre es confuso, pero recibe un ID de Abono, no de AbonoCategoria
        $this->dispatch('confirmarEliminarAbonoCategoria', abonoCategoriaId: $abonoId);
    }

    public function eliminarAbonoCategoria($abonoId)
    {
        // Renombramos la variable para mayor claridad
        if ($this->actividad->aforo_ocupado > 0) {
            $this->dispatch('msn', msnIcono: 'warning', msnTitulo: '¡Acción no permitida!', msnTexto: 'Este abono no se puede eliminar porque ya existen inscripciones asociadas.');
            return;
        }

        $abono = Abono::find($abonoId);
        if ($abono) {
            AbonoCategoria::where('abono_id', $abono->id)->delete(); // Eliminar registros hijos primero
            $abono->delete(); // Luego eliminar el padre

            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Eliminado!', msnTexto: 'El abono fue eliminado con éxito.');
            $this->mount(); // Refrescar
        }
    }

    public function render()
    {
        return view('livewire.actividades.abonos');
    }
}
