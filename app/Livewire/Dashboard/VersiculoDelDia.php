<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\VersiculoDiario;
use App\Models\Configuracion;
use Carbon\Carbon;

class VersiculoDelDia extends Component
{
    public $versiculo;
    public $configuracion;
    public $claseColumnas;

    public function mount($claseColumnas = 'col-12 col-md-4')
    {
        $this->claseColumnas = $claseColumnas;
        $this->configuracion = Configuracion::first();
        
        // Obtener el versículo de hoy
        $this->versiculo = VersiculoDiario::whereDate('fecha_publicacion', Carbon::today())->first();
    }

    public function toggleLike()
    {
        if (!auth()->check() || !$this->versiculo) {
            return;
        }

        $this->versiculo->usuariosQueDieronLike()->toggle(auth()->id());
        
        // Refrescar el modelo para actualizar el contador de likes en la vista
        $this->versiculo->refresh();
    }

    public function render()
    {
        $plainText = "";
        $fullTextModal = "";
        
        if ($this->versiculo) {
            $dataVersiculos = $this->versiculo->texto_versiculo;
            
            // Si por alguna razón es un string, lo decodificamos
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
                $fullTextModal = $this->versiculo->cita_referencia;
            }

            $plainText = trim(strip_tags($plainText));
        }

        return view('livewire.dashboard.versiculo-del-dia', [
            'plainText' => $plainText,
            'fullTextModal' => $fullTextModal
        ]);
    }
}
