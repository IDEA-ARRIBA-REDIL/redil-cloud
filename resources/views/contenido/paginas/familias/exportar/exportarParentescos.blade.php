<style>
    table, th, td {
    border: 1px solid blue;
    }
    </style>
    
    <table>
        <thead>
            <tr>
                @foreach($camposRelacionesUsuarios as $campo)
                <th><b>{{ $campo->nombre}}</b></th>
                 @endforeach
              {!! auth()->user()->cabezalTh(1,$arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra) !!}
            </tr>
    
            <tr>
              @foreach($camposRelacionesUsuarios as $campo)
                <th></th>
              @endforeach
              {!! auth()->user()->cabezalTh(2,$arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra) !!}
            </tr>
        </thead>
        <tbody>
    
        @foreach($parientes as $pariente)
        <tr>
            @if($camposRelacionesUsuarios->where('value', 'pariente_user_id')->count() > 0)
            <td>{{$pariente->pariente->nombre(3)}} </td>
            @endif

            @if($camposRelacionesUsuarios->where('value', 'es_el_responsable')->count() > 0)

            <?php
            $responsabilidad = 1;
            if($pariente->es_el_responsable == true)
              $responsabilidad = 2;
            elseif($pariente->es_el_responsable == false)
              $responsabilidad = 3;
                    
            ?>
            <td>
                @if($responsabilidad ==1)
                Ninguna
                @elseif($responsabilidad ==2)
                SI
                @else
                NO 
                @endif
                
            </td>
            @endif

            @if($camposRelacionesUsuarios->where('value', 'es_el_responsable')->count() > 0)
            <td>
                @if($pariente->genero == 0 )
                {{$pariente->nombre_masculino}}
                @else
                {{$pariente->femenino}}
                @endif
            </td>

            {!!
                $pariente->usuario()->withTrashed()->first()->dataTd(
                $arrayCamposInfoPersonal,
                $arrayPasosCrecimiento,
                $arrayDatosCongregacionales,
                $arrayCamposExtra
              ) !!}
            @endif
        </tr>
        
        @endforeach
    
          
        </tbody>
    </table>
    