<?php

namespace App\Livewire\Ejemplo;

use App\Models\Tema;
use Livewire\Component;

class Darwin extends Component
{
    public $buscar="";
    public $nombre="";
    public $apellido="";
    
    public function render()
    {
        
         if($this->buscar == "")
         {
            $temas=Tema::get();
         }else{
            
            $temas=Tema::where('titulo','like', "%$this->buscar%")->get();
         }
        return view('livewire.ejemplo.darwin',[
            'temas'=>$temas
        ]);

    }

   


}
