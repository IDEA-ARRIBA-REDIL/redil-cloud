<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use App\Models\CarritoCursoUser;
use Illuminate\Support\Facades\Auth;

class CarritoCompras extends Component
{
    public $carrito;
    public $items = [];
    public $total = 0;

    public function mount()
    {
        $this->cargarCarrito();
    }

    public function cargarCarrito()
    {
        if (Auth::check()) {
            $this->carrito = CarritoCursoUser::where('user_id', Auth::id())
                                ->where('estado', 'pendiente')
                                ->first();

            if ($this->carrito) {
                // items es un array => JSON field en DB
                $this->items = is_array($this->carrito->items) ? $this->carrito->items : [];
                $this->total = $this->carrito->total;
            } else {
                $this->items = [];
                $this->total = 0;
            }
        }
    }

    public function eliminarItem($cursoId)
    {
        if (!$this->carrito) return;

        // Filtrar array removiendo el curso
        $nuevosItems = array_filter($this->items, function($item) use ($cursoId) {
            return $item['curso_id'] != $cursoId;
        });

        // Reindexar el array para evitar problemas json
        $nuevosItems = array_values($nuevosItems);

        // Recalcular Total
        $nuevoTotal = array_reduce($nuevosItems, function($carry, $item) {
            return $carry + $item['precio'];
        }, 0);

        // Guardar la actualización
        $this->carrito->update([
            'items' => $nuevosItems,
            'total' => $nuevoTotal
        ]);

        $this->cargarCarrito();

        $this->dispatch('notificar', [
            'tipo' => 'success',
            'mensaje' => 'Curso eliminado del carrito.'
        ]);
    }

    public function render()
    {
        return view('livewire.cursos.carrito-compras');
    }
}
