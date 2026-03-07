<?php

namespace App\Livewire\Escuelas;

use App\Models\ItemPlantilla;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\MateriaAprobadaUsuario;
use App\Models\CortePeriodo; // Necesario para buscar los cortes del periodo
use App\Models\Materia;
use App\Jobs\FinalizarMateriaJob; // <-- Importaremos el nuevo Job
use App\Models\Periodo;
use App\Models\Configuracion;
use App\Models\MateriaPeriodo as ModeloMateriaPeriodo; // Alias para evitar conflicto de nombres
use App\Models\HorarioBase; // Asegúrate de importar HorarioBase
use App\Models\HorarioMateriaPeriodo as ModeloHorarioMateriaPeriodo; // Asegúrate de importar HorarioMateriaPeriodo
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Importar DB para transacciones
use Livewire\Attributes\On; // <-- ¡Asegúrate de importar On!
use Illuminate\Support\Facades\Auth;

class MateriaPeriodo extends Component
{
    public Periodo $periodo;
    public $materiasDelPeriodo; // Colección de App\Models\MateriaPeriodo
    public $configuracion;

    // Para el modal de añadir materias
    public $mostrarModalAnadirMaterias = false;
    public $materiasEscuelaDisponibles = []; // Colección de App\Models\Materia de la escuela
    public $materiasSeleccionadasParaAnadir = []; // Array de IDs de Materia seleccionadas
    public $incluirHorariosBase = "0"; // "0" para No (default), "1" para Sí.

    protected function rules() // Modificado para incluir la nueva regla
    {
        return [
            'materiasSeleccionadasParaAnadir' => 'required|array|min:1',
            'materiasSeleccionadasParaAnadir.*' => 'exists:materias,id', // Valida que cada ID exista en la tabla materias
            'incluirHorariosBase' => 'required|in:0,1', // Validar la opción del radio button
        ];
    }

    protected function messages() // Modificado para incluir el nuevo mensaje
    {
        return [
            'materiasSeleccionadasParaAnadir.required' => 'Debes seleccionar al menos una materia para añadir.',
            'materiasSeleccionadasParaAnadir.min' => 'Debes seleccionar al menos una materia para añadir.',
            'materiasSeleccionadasParaAnadir.*.exists' => 'Una de las materias seleccionadas no es válida.',
            'incluirHorariosBase.required' => 'Debes seleccionar si deseas incluir los horarios base.',
            'incluirHorariosBase.in' => 'La opción para incluir horarios base no es válida.',
        ];
    }

    /**
     * Se ejecuta cuando el componente es inicializado.
     * Recibe el modelo Periodo para el cual se gestionarán las materias.
     */
    public function mount(Periodo $periodo)
    {
        $this->periodo = $periodo;
        // Asumiendo que Configuracion::find(1) es correcto para tu lógica.
        // Si no existe, podría dar error. Considera un manejo más robusto si es necesario.
        $this->configuracion = Configuracion::find(1);
        $this->cargarMateriasDelPeriodo();
    }

    #[On('finalizarMateriaConfirmado')]
    public function finalizarMateria(int $materiaPeriodoId)
    {


        try {
            $materiaPeriodo = ModeloMateriaPeriodo::findOrFail($materiaPeriodoId);
            $user = Auth::user();
            FinalizarMateriaJob::dispatch($materiaPeriodo, $user);
            session()->flash('mensaje_exito', "El proceso de finalización para '{$materiaPeriodo->materia->nombre}' ha comenzado.");
            // 1. Cambiamos el estado de la materia a "no finalizado"
            $materiaPeriodo->finalizado = true;
            $materiaPeriodo->save();
        } catch (\Exception $e) {
            Log::error("Error al despachar FinalizarMateriaJob: " . $e->getMessage());
            session()->flash('mensaje_error', 'Ocurrió un error al iniciar el proceso.');
        }
        $this->mount($this->periodo);
    }

    public function confirmarFinalizacion(int $materiaPeriodoId)
    {
        $materia = ModeloMateriaPeriodo::find($materiaPeriodoId);
        $this->dispatch('mostrar-confirmacion-finalizar', [
            'id' => $materiaPeriodoId,
            'nombre' => $materia->materia->nombre,
        ]);
    }

    public function confirmarReactivacion(int $materiaPeriodoId)
    {
        $materia = ModeloMateriaPeriodo::find($materiaPeriodoId);
        // Emite un evento al frontend para que el script de SweetAlert lo intercepte
        $this->dispatch('mostrar-confirmacion-reactivar', [
            'id' => $materiaPeriodoId,
            'nombre' => $materia->materia->nombre,
        ]);
    }

    #[On('reactivarMateriaConfirmado')]
    public function reactivarMateria(int $materiaPeriodoId)
    {
        try {
            $materiaPeriodo = ModeloMateriaPeriodo::findOrFail($materiaPeriodoId);

            // 1. Cambiamos el estado de la materia a "no finalizado"
            $materiaPeriodo->finalizado = false;
            $materiaPeriodo->save();

            // 2. (Opcional pero MUY RECOMENDADO) Eliminamos los resultados finales
            //    que se habían calculado previamente para esta materia.
            //    Esto asegura que cuando se vuelva a finalizar, se genere un cálculo limpio.
            MateriaAprobadaUsuario::where('materia_periodo_id', $materiaPeriodoId)->delete();

            session()->flash('mensaje_exito', "La materia '{$materiaPeriodo->materia->nombre}' ha sido reactivada. Los resultados finales anteriores han sido eliminados para permitir un nuevo cálculo.");

            // Refrescamos la lista de materias para que la vista se actualice
            $this->cargarMateriasDelPeriodo();
        } catch (\Exception $e) {
            Log::error("Error al reactivar MateriaPeriodo ID {$materiaPeriodoId}: " . $e->getMessage());
            session()->flash('mensaje_error', 'Ocurrió un error al reactivar la materia.');
        }
    }



    /**
     * Carga las materias que ya están asociadas a este período.
     */
    public function cargarMateriasDelPeriodo()
    {
        $this->materiasDelPeriodo = ModeloMateriaPeriodo::where('periodo_id', $this->periodo->id)
            ->with('materia') // Cargar la relación con la materia base para mostrar su nombre
            ->get();
    }

    /**
     * Prepara y abre el modal para añadir materias.
     * Carga las materias de la escuela que aún no están en el período actual.
     */
    public function abrirModalAnadirMaterias()
    {
        $materiasYaEnPeriodoIds = ModeloMateriaPeriodo::where('periodo_id', $this->periodo->id)
            ->pluck('materia_id')
            ->toArray();

        $this->materiasEscuelaDisponibles = Materia::where('escuela_id', $this->periodo->escuela_id)
            ->whereNotIn('id', $materiasYaEnPeriodoIds) // Excluir las que ya están
            ->orderBy('nombre')
            ->get();

        $this->materiasSeleccionadasParaAnadir = []; // Resetear selección previa
        $this->incluirHorariosBase = "0"; // Valor por defecto al abrir el modal
        $this->resetErrorBag(); // Limpiar errores de validación previos
        $this->mostrarModalAnadirMaterias = true;
    }

    /**
     * Cierra el modal para añadir materias.
     */
    public function cerrarModalAnadirMaterias()
    {
        $this->mostrarModalAnadirMaterias = false;
        $this->materiasSeleccionadasParaAnadir = [];
        $this->incluirHorariosBase = "0";
        $this->resetErrorBag();
    }

    /**
     * Añade las materias seleccionadas del modal al período actual.
     * Crea nuevos registros en la tabla 'materia_periodo'.
     * Opcionalmente, duplica los HorarioBase como HorarioMateriaPeriodo.
     */
    public function anadirMateriasSeleccionadas()
    {
        $this->validate();

        $materiasOriginales = Materia::whereIn('id', $this->materiasSeleccionadasParaAnadir)->get();
        $materiasAnadidasConExito = 0;
        $horariosAnadidosConExito = 0;
        $itemsCorteAnadidosConExito = 0; // Nuevo contador
        $materiasYaExistentes = 0;

        DB::beginTransaction();

        try {
            // Cargar los cortes del período actual una sola vez para optimizar
            $cortesDelPeriodoActual = CortePeriodo::where('periodo_id', $this->periodo->id)
                ->get()
                ->keyBy('corte_escuela_id'); // Facilita la búsqueda

            foreach ($materiasOriginales as $materiaOriginal) {
                $existente = ModeloMateriaPeriodo::where('materia_id', $materiaOriginal->id)
                    ->where('periodo_id', $this->periodo->id)
                    ->first();

                if ($existente) {
                    $materiasYaExistentes++;
                    continue;
                }

                $materiaPeriodo = new ModeloMateriaPeriodo();
                $materiaPeriodo->materia_id = $materiaOriginal->id;
                $materiaPeriodo->periodo_id = $this->periodo->id;
                $materiaPeriodo->descripcion = $materiaOriginal->descripcion;
                $materiaPeriodo->habilitar_calificaciones = $materiaOriginal->habilitar_calificaciones;
                $materiaPeriodo->habilitar_asistencias = $materiaOriginal->habilitar_asistencias;
                $materiaPeriodo->asistencias_minimas = $materiaOriginal->asistencias_minimas;
                $materiaPeriodo->habilitar_alerta_inasistencias = $materiaOriginal->habilitar_alerta_inasistencias; // Asegúrate que este campo existe en $materiaOriginal o que el nombre es correcto
                $materiaPeriodo->habilitar_traslado = $materiaOriginal->habilitar_traslado;
                $materiaPeriodo->cantidad_inasistencias_alerta = $materiaOriginal->asistencias_minima_alerta; // Asegúrate que este campo existe en $materiaOriginal o que el nombre es correcto

                $materiaPeriodo->auto_matricula = $this->configuracion->auto_matricula_materia_periodo ?? false;
                $materiaPeriodo->estado_auto_matricula = $this->configuracion->estado_auto_matricula_materia_periodo ?? 2;
                $materiaPeriodo->finalizado = false;
                $materiaPeriodo->save();
                $materiasAnadidasConExito++;

                if ($this->incluirHorariosBase === "1") {
                    $horariosBaseDeMateria = HorarioBase::where('materia_id', $materiaOriginal->id)
                        ->where('activo', true)
                        ->get();

                    // Cargar las plantillas de ítems para esta materia original una vez
                    $itemPlantillasDeMateria = ItemPlantilla::where('materia_id', $materiaOriginal->id)->get();

                    foreach ($horariosBaseDeMateria as $hb) {
                        $horarioMPExistente = ModeloHorarioMateriaPeriodo::where('materia_periodo_id', $materiaPeriodo->id)
                            ->where('horario_base_id', $hb->id)
                            ->first();
                        if ($horarioMPExistente) {
                            continue;
                        }

                        $horarioMateriaPeriodo = new ModeloHorarioMateriaPeriodo();
                        $horarioMateriaPeriodo->materia_periodo_id = $materiaPeriodo->id;
                        $horarioMateriaPeriodo->horario_base_id = $hb->id;
                        $horarioMateriaPeriodo->habilitado = true;
                        $horarioMateriaPeriodo->capacidad = $hb->capacidad;
                        $horarioMateriaPeriodo->capacidad_limite = $hb->capacidad_limite;
                        $horarioMateriaPeriodo->ampliar_cupos_limite = false;
                        $horarioMateriaPeriodo->cupos_disponibles = $hb->capacidad;
                        $horarioMateriaPeriodo->save();
                        $horariosAnadidosConExito++;

                        // --- INICIO: Nueva lógica para crear ItemCorteMateriaPeriodo ---
                        if (!$itemPlantillasDeMateria->isEmpty()) {
                            foreach ($itemPlantillasDeMateria as $itemPlantilla) {
                                // Encontrar el CortePeriodo destino usando el corte_escuela_id de la plantilla
                                $cortePeriodoDestino = $cortesDelPeriodoActual->get($itemPlantilla->corte_escuela_id);

                                if ($cortePeriodoDestino) {
                                    // Instanciar el nuevo ItemCorteMateriaPeriodo
                                    $nuevoItemCorte = new ItemCorteMateriaPeriodo();

                                    // Asignar las propiedades
                                    $nuevoItemCorte->materia_periodo_id = $materiaPeriodo->id;
                                    $nuevoItemCorte->corte_periodo_id = $cortePeriodoDestino->id;
                                    $nuevoItemCorte->item_plantilla_id = $itemPlantilla->id;
                                    $nuevoItemCorte->tipo_item_id = $itemPlantilla->tipo_item_id; // Asume que ItemPlantilla tiene tipo_item_id
                                    $nuevoItemCorte->horario_materia_periodo_id = $horarioMateriaPeriodo->id; // Vínculo al horario actual
                                    $nuevoItemCorte->nombre = $itemPlantilla->nombre;
                                    $nuevoItemCorte->contenido = $itemPlantilla->contenido;
                                    $nuevoItemCorte->visible = $itemPlantilla->visible_predeterminado; // Usar el valor predeterminado de la plantilla
                                    $nuevoItemCorte->fecha_inicio = $cortePeriodoDestino->fecha_inicio; // Tomado del CortePeriodo
                                    $nuevoItemCorte->fecha_fin = $cortePeriodoDestino->fecha_fin;       // Tomado del CortePeriodo
                                    $nuevoItemCorte->habilitar_entregable = $itemPlantilla->entregable_predeterminado; // Usar el valor predeterminado
                                    $nuevoItemCorte->porcentaje = $itemPlantilla->porcentaje_sugerido; // Usar el valor sugerido
                                    $nuevoItemCorte->orden = $itemPlantilla->orden;

                                    // Guardar el nuevo ítem en la base de datos
                                    $nuevoItemCorte->save();

                                    $itemsCorteAnadidosConExito++;
                                } else {
                                    Log::warning("Al añadir materia '{$materiaOriginal->nombre}' al período '{$this->periodo->nombre}', no se encontró CortePeriodo para ItemPlantilla ID: {$itemPlantilla->id} (asociado a corte_escuela_id: {$itemPlantilla->corte_escuela_id}) en Periodo ID: {$this->periodo->id}. El ítem no fue creado para el horario ID: {$horarioMateriaPeriodo->id}.");
                                }
                            }
                        }
                        // --- FIN: Nueva lógica para crear ItemCorteMateriaPeriodo ---
                    }
                }
            }

            DB::commit();

            $mensajeExitoTotal = '';
            if ($materiasAnadidasConExito > 0) {
                $mensajeExitoTotal .= $materiasAnadidasConExito . ' materia(s) añadida(s) al período. ';
            }
            if ($this->incluirHorariosBase === "1" && $horariosAnadidosConExito > 0) {
                $mensajeExitoTotal .= $horariosAnadidosConExito . ' horario(s) base vinculado(s). ';
            }
            if ($this->incluirHorariosBase === "1" && $itemsCorteAnadidosConExito > 0) { // Mensaje para los ítems
                $mensajeExitoTotal .= $itemsCorteAnadidosConExito . ' ítem(s) de evaluación creados para los horarios. ';
            }

            if (!empty(trim($mensajeExitoTotal))) {
                session()->flash('mensaje_exito', trim($mensajeExitoTotal));
            }

            if ($materiasYaExistentes > 0) {
                session()->flash('mensaje_info', $materiasYaExistentes . ' materia(s) seleccionada(s) ya existía(n) y no se duplicaron.');
            }

            if ($materiasAnadidasConExito == 0 && $horariosAnadidosConExito == 0 && $itemsCorteAnadidosConExito == 0 && $materiasYaExistentes == 0 && count($this->materiasSeleccionadasParaAnadir) > 0) {
                session()->flash('mensaje_error', 'No se pudo añadir ninguna de las materias seleccionadas (posiblemente ya existían o hubo un error general).');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al añadir materias/horarios/ítems al período {$this->periodo->id}: " . $e->getMessage() . " en la línea " . $e->getLine() . " del archivo " . $e->getFile());
            session()->flash('mensaje_error', 'Ocurrió un error muy grave al procesar la solicitud. Por favor, contacta al administrador. Detalles técnicos: ' . $e->getMessage());
        }

        $this->cerrarModalAnadirMaterias();
        $this->cargarMateriasDelPeriodo();
    }

    /**
     * Paso 1: Verifica si la materia se puede eliminar y pide confirmación.
     */
    public function confirmarEliminacion(int $materiaPeriodoId)
    {
        // Buscamos la materia y usamos 'whereDoesntHave' para verificar la condición.
        // Esta es una forma muy eficiente de comprobar si existen registros relacionados.
        $sePuedeEliminar = ModeloMateriaPeriodo::where('id', $materiaPeriodoId)
            ->whereDoesntHave('horariosMateriaPeriodo.matriculasDeAlumnos') // La magia está aquí
            ->exists();

        $materia = ModeloMateriaPeriodo::find($materiaPeriodoId);

        if ($sePuedeEliminar) {
            // Si se puede eliminar, emitimos un evento al frontend para mostrar el SweetAlert de confirmación.
            $this->dispatch('mostrar-confirmacion-eliminar', [
                'id' => $materiaPeriodoId,
                'nombre' => $materia->materia->nombre,
            ]);
        } else {
            // Si NO se puede eliminar, emitimos un evento de error.
            $this->dispatch('mostrar-error', [
                'texto' => "La materia '{$materia->materia->nombre}' no se puede eliminar porque tiene alumnos matriculados en al menos uno de sus horarios."
            ]);
        }
    }

    /**
     * Paso 2: Ejecuta la eliminación después de la confirmación del usuario.
     */
    #[On('eliminarMateriaConfirmado')]
    public function eliminarMateria(int $materiaPeriodoId)
    {
        try {
            $materiaPeriodo = ModeloMateriaPeriodo::findOrFail($materiaPeriodoId);
            $nombreMateria = $materiaPeriodo->materia->nombre;

            $materiaPeriodo->delete();

            session()->flash('mensaje_exito', "La materia '{$nombreMateria}' se ha eliminado correctamente del periodo.");

            // Refrescamos la lista de materias para que desaparezca de la vista.
            $this->cargarMateriasDelPeriodo();
        } catch (\Exception $e) {
            Log::error("Error al eliminar MateriaPeriodo ID {$materiaPeriodoId}: " . $e->getMessage());
            session()->flash('mensaje_error', 'Ocurrió un error al eliminar la materia.');
        }
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        return view('livewire.escuelas.materia-periodo');
    }
}
