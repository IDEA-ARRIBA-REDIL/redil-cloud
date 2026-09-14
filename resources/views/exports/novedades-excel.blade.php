<table>
    <thead>
        <tr>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">ID</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Fecha Registro</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Estado</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Actividad</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Tipo Actividad</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Materia / Escuela Deseada</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Tipo de Novedad</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Nombre Completo</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Identificación</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Teléfono</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Correo Electrónico</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Asunto</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Descripción del Error</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Respuesta Enviada</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Respondido Por</th>
            <th style="background-color: #0099d9; color: #ffffff; font-weight: bold;">Fecha Respuesta</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($novedades as $nov)
            <tr>
                <td>{{ $nov->id }}</td>
                <td>{{ $nov->created_at ? $nov->created_at->format('Y-m-d H:i') : '' }}</td>
                <td>{{ $nov->estado_nombre }}</td>
                <td>{{ $nov->actividad->nombre ?? 'N/A' }}</td>
                <td>{{ $nov->actividad->tipo->nombre ?? 'N/A' }}</td>
                <td>{{ $nov->materia->nombre ?? ($nov->materia_nombre ?? 'N/A') }}</td>
                <td>{{ $nov->tipoNovedad->nombre ?? 'N/A' }}</td>
                <td>{{ $nov->nombre }}</td>
                <td>{{ $nov->identificacion }}</td>
                <td>{{ $nov->telefono }}</td>
                <td>{{ $nov->email }}</td>
                <td>{{ $nov->asunto }}</td>
                <td>{{ $nov->descripcion }}</td>
                <td>{{ $nov->respuesta ?? 'Sin respuesta aún' }}</td>
                <td>{{ $nov->respondidoPor ? ($nov->respondidoPor->primer_nombre . ' ' . $nov->respondidoPor->primer_apellido) : 'N/A' }}</td>
                <td>{{ $nov->fecha_respuesta ? $nov->fecha_respuesta->format('Y-m-d H:i') : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
