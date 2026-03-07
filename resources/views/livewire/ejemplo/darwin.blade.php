<div>
    
  
    <div>
   
    </div>
    <div>
        <input wire:model.live.debounce.250ms="buscar">
        <ul>

            @foreach($temas as $tema)
                <li>
                    {{$tema->titulo}} 
                </li>
            @endforeach
        </ul>
    </div>

    <div>
        <input wire:model.live="nombre">
        <input wire:model.live="apellido">
        <div wire:ignore>
            {{$nombre}}
            
        </div>
        {{$apellido}}
    </div>


</div>
