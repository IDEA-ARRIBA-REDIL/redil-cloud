<?php

namespace App\Livewire\Consejeria;

use App\Models\CitaConsejeria;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class MisCitas extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $citas = CitaConsejeria::where('user_id', Auth::id())
            ->with(['consejero.usuario', 'tipoConsejeria'])
            ->orderBy('fecha_hora_inicio', 'desc')
            ->paginate(10);

        return view('livewire.consejeria.mis-citas', [
            'citas' => $citas
        ]);
    }
}
