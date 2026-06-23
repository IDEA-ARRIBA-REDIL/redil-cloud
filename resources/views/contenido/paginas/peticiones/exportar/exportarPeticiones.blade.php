<style>
table, th, td {
border: 1px solid blue;
}
</style>

<table>
    <thead>
      <tr>
          @foreach($camposPeticiones as $campo)
            <th><b>{{ $campo->nombre }}</b></th>
          @endforeach
        </tr>
    </thead>
    <tbody>

    @foreach($peticiones as $peticion)
      <tr>
        @if($camposPeticiones->where('value', 'nombre_solicitante')->count() > 0)
        <td>{{ $peticion->nombre_solicitante }}</td>
        @endif

        @if($camposPeticiones->where('value', 'email_solicitante')->count() > 0)
        <td>{{ $peticion->email_solicitante }}</td>
        @endif

        @if($camposPeticiones->where('value', 'telefono_solicitante')->count() > 0)
        <td>{{ $peticion->telefono_solicitante }}</td>
        @endif

        @if($camposPeticiones->where('value', 'genero_solicitante')->count() > 0)
        <td>{{ $peticion->genero_solicitante }}</td>
        @endif

        @if($camposPeticiones->where('value', 'pais_id')->count() > 0)
        <td>{{ $peticion->paisNombre }}</td>
        @endif

        @if($camposPeticiones->where('value', 'autor_creacion_id')->count() > 0)
        <td>{{ $peticion->usuarioCreacion }}</td>
        @endif

        @if($camposPeticiones->where('value', 'asignado_a')->count() > 0)
        <td>{{ $peticion->asignado_a }}</td>
        @endif

        @if($camposPeticiones->where('value', 'tipo_peticion_id')->count() > 0)
        <td>{{ $peticion->tipoPeticion->nombre ?? 'Sin especificar' }}</td>
        @endif

        @if($camposPeticiones->where('value', 'estado')->count() > 0)
        <td>{{ $peticion->estado }}</td>
        @endif

        @if($camposPeticiones->where('value', 'descripcion')->count() > 0)
        <td>{{ $peticion->descripcion }}</td>
        @endif

        @if($camposPeticiones->where('value', 'fecha')->count() > 0)
        <td>{{ $peticion->fecha }}</td>
        @endif
      </tr>
    @endforeach
    </tbody>
</table>
