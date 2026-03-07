<style>
table, th, td {
border: 1px solid blue;
}
</style>

<table>
    <thead>
      <tr>
          @foreach($camposPeticiones as $campo)
            <th><b>{{ $campo->nombre}}</b></th>
          @endforeach
          {!! auth()->user()->cabezalTh(1,$arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra) !!}
        </tr>

        <tr>
          @foreach($camposPeticiones as $campo)
            <th></th>
          @endforeach
          {!! auth()->user()->cabezalTh(2,$arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra) !!}
        </tr>
    </thead>
    <tbody>

    @foreach($peticiones as $peticion)
      <tr>
        @if($camposPeticiones->where('value', 'tipo_peticion_id')->count() > 0)
        <td>{{$peticion->tipoPeticion->nombre}}</td>
        @endif

        @if($camposPeticiones->where('value', 'estado')->count() > 0)
        <td>{{$peticion->estado}}</td>
        @endif

        @if($camposPeticiones->where('value', 'descripcion')->count() > 0)
        <td>{{$peticion->descripcion}}</td>
        @endif

        @if($camposPeticiones->where('value', 'respuesta')->count() > 0)
        <td>{{$peticion->respuesta}}</td>
        @endif

        @if($camposPeticiones->where('value', 'fecha')->count() > 0)
        <td>{{$peticion->fecha}}</td>
        @endif

        @if($camposPeticiones->where('value', 'autor_creacion_id')->count() > 0)
        <td>{{$peticion->usuarioCreacion}}</td>
        @endif

        @if($camposPeticiones->where('value', 'pais_id')->count() > 0)
        <td>{{$peticion->paisNombre}}</td>
        @endif

        {!!
          $peticion->usuario()->withTrashed()->first()->dataTd(
          $arrayCamposInfoPersonal,
          $arrayPasosCrecimiento,
          $arrayDatosCongregacionales,
          $arrayCamposExtra
        ) !!}

      </tr>
    @endforeach
    </tbody>
</table>
