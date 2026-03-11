<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\VersiculoDiario;
use App\Models\Configuracion;
use Carbon\Carbon;
use Livewire\Attributes\Renderless;

class VersiculoDelDia extends Component
{
    public $versiculoId;
    public $configuracion;
    public $claseColumnas;


    public function mount($claseColumnas = 'col-12 col-md-4')
    {
        $this->claseColumnas = $claseColumnas;
        $this->configuracion = Configuracion::first();

        $versiculo = VersiculoDiario::whereDate('fecha_publicacion', Carbon::today())->first();
        $this->versiculoId = $versiculo ? $versiculo->id : null;
    }

    public function toggleLike($id)
    {
        if (!auth()->check()) {
            return;
        }

        $versiculo = VersiculoDiario::find($id);
        if ($versiculo) {
            $versiculo->usuariosQueDieronLike()->toggle(auth()->id());
        }
    }

    public function render()
    {
        $versiculo = $this->versiculoId ? VersiculoDiario::with('usuariosQueDieronLike')->find($this->versiculoId) : null;
        $plainText = "";
        $fullTextModal = "";

        if ($versiculo) {
            $dataVersiculos = $versiculo->texto_versiculo;

            if (is_string($dataVersiculos)) {
                $dataVersiculos = json_decode($dataVersiculos, true);
            }

            if(isset($dataVersiculos) && is_array($dataVersiculos)) {
                foreach($dataVersiculos as $selection) {
                    $versiculosArray = isset($selection['versiculos']) ? $selection['versiculos'] : [];
                    foreach($versiculosArray as $v) {
                        $plainText .= (isset($v['texto']) ? $v['texto'] : '') . " ";
                        $num = isset($v['numero']) ? $v['numero'] : '';
                        $texto = isset($v['texto']) ? $v['texto'] : '';
                        $fullTextModal .= "<strong>".$num."</strong> " . $texto . "<br><br>";
                    }
                }
            }

            if (empty($fullTextModal)) {
                $fullTextModal = $versiculo->cita_referencia;
            }

            $plainText = trim(strip_tags($plainText));
        }

        return view('livewire.dashboard.versiculo-del-dia', [
            'versiculo' => $versiculo,
            'plainText' => $plainText,
            'fullTextModal' => $fullTextModal
        ]);
    }
}
