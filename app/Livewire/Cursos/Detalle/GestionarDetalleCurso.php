<?php

namespace App\Livewire\Cursos\Detalle;

use Livewire\Component;
use App\Models\Curso;
use App\Models\CursoAprendizaje;

class GestionarDetalleCurso extends Component
{
    public Curso $curso;

    // Array para manejar los aprendizajes dinámicamente
    // Cada item será un array ['id' => null, 'texto' => '', 'orden' => 0]
    public $aprendizajes = [];

    public function mount(Curso $curso)
    {
        $this->curso = $curso;
        $this->aprendizajes = []; // Reiniciar el array para evitar duplicados al recargar

        // Cargar aprendizajes existentes ordenados
        foreach ($curso->aprendizajes()->orderBy('orden')->get() as $apr) {
            $this->aprendizajes[] = [
                'id' => $apr->id,
                'texto' => $apr->texto,
                'orden' => $apr->orden
            ];
        }

        // Si no hay ninguno, agregar uno vacío por defecto (opcional, aquí no lo hago para dejar limpio)
    }

    public function agregarAprendizaje()
    {
        $this->aprendizajes[] = [
            'id' => null, // Nuevo, sin ID
            'texto' => '',
            'orden' => count($this->aprendizajes) + 1
        ];
    }

    public function eliminarAprendizaje($index)
    {
        $item = $this->aprendizajes[$index];

        // Si tiene ID, eliminar de la BD inmediatamente o marcar para borrar al guardar?
        // Estrategia: Borrado inmediato si el usuario confirma suele ser mejor UX en listas complejas,
        // pero para formularios de "Guardar todo al final", lo mejor es solo quitar del array y borrar en save().
        // Sin embargo, Livewire array manipulation is tricky.
        // Haremos borrado en BD en el método 'guardar' comparando IDs, o borrado directo si se prefiere.
        // Simplificaremos: Si tiene ID, lo eliminamos de BD ahora mismo para evitar complejidad de tracking.

        if (!empty($item['id'])) {
            CursoAprendizaje::find($item['id'])?->delete();
        }

        unset($this->aprendizajes[$index]);
        $this->aprendizajes = array_values($this->aprendizajes); // Reindexar array
    }

    public function actualizarOrden($list)
    {
        // $list proviene del plugin sortable, trae indices nuevos
        // Estructura esperada de $list: [['value' => index_original_o_id, 'order' => new_order], ...]
        // Simplificación: reordenamos el array local en base a los indices recibidos

        // Asumiendo que Livewire sortable devuelve el orden de los "keys" o "ids"
        // Implementación manual mas robusta con SortableJS envía el nuevo array de IDs.

        // Estrategia Livewire simple: El frontend actualiza el array via model, y si usamos sortable js,
        // al soltar el elemento, emitimos un evento con el nuevo orden de indices.

        // Aquí solo recibiremos el array reordenado de IDs/Indices si se usa plugin.
        // Dejaremos que el 'guardar' maneje el orden basado en la posición en el array $aprendizajes.
    }


    #[Livewire\Attributes\On('reorder-items')]
    public function reordenar($orderedIds)
    {
        // $orderedIds es un array de índices antiguos en el nuevo orden
        // Reconstruimos el array $aprendizajes en base a este nuevo orden

        $nuevoArray = [];
        foreach ($orderedIds as $oldIndex) {
            if (isset($this->aprendizajes[$oldIndex])) {
                $nuevoArray[] = $this->aprendizajes[$oldIndex];
            }
        }

        $this->aprendizajes = $nuevoArray;
    }

    // Simplificación: Guardar todo
    public function guardar()
    {
        $this->validate([
            'aprendizajes.*.texto' => 'required|string|max:120',
        ]);

    // Guardar Aprendizajes
        // Estrategia: Borrar todos y recrear? No, perdemos IDs.
        // Upsert? Sí.

        // 1. Obtener IDs actuales en BD
        $idsEnBD = $this->curso->aprendizajes()->pluck('id')->toArray();

        // 2. IDs enviados en el formulario
        $idsEnFormulario = array_column($this->aprendizajes, 'id');
        $idsEnFormulario = array_filter($idsEnFormulario); // Quitar nulos

        // 3. Eliminar los que ya no están
        $idsAEliminar = array_diff($idsEnBD, $idsEnFormulario);
        CursoAprendizaje::destroy($idsAEliminar);

        // 4. Guardar/Actualizar
        foreach ($this->aprendizajes as $index => $item) {
            if (!empty($item['id'])) {
                CursoAprendizaje::where('id', $item['id'])->update([
                    'texto' => $item['texto'],
                    'orden' => $index + 1 // El orden es el indice en el array + 1
                ]);
            } else {
                CursoAprendizaje::create([
                    'curso_id' => $this->curso->id,
                    'texto' => $item['texto'],
                    'orden' => $index + 1
                ]);
            }
        }

        // Recargar para tener los IDs nuevos
        $this->mount($this->curso);

        $this->dispatch('msn', [
            'msn' => 'Detalles del curso guardados correctamente.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.cursos.detalle.gestionar-detalle-curso');
    }
}
