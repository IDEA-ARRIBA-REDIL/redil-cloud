@php
    $totalCols = 7;
    if ($modo === 'materias') {
        $totalCols++;
    }
    if ($modo === 'materias' && !empty($itemActivo?->habilitar_asistencias)) {
        $totalCols++;
    }
@endphp
<table>
    <thead>
        <tr>
            <th colspan="{{ $totalCols }}" style="font-size: 14px; font-weight: bold; text-align: center; background-color: #007788; color: #ffffff;">
                INFORME ESTADO: {{ mb_strtoupper($itemNombre) }} ({{ mb_strtoupper($escuelaNombre) }})
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalCols }}" style="font-size: 11px; text-align: center; color: #555555;">
                {{ $modo === 'materias' ? 'Materia' : 'Nivel' }}: {{ $itemNombre }} | 
                Rango: {{ !empty($rangoFechas) ? $rangoFechas : 'Histórico completo' }} | 
                Estado Académico: {{ $filtroEstadoAcademico === 'aprobados' ? 'Solo Aprobados' : ($filtroEstadoAcademico === 'en_curso' ? 'Solo En Proceso' : ($filtroEstadoAcademico === 'reprobados' ? 'Solo Reprobados' : 'Todos')) }} | 
                Registro: {{ $filtroTipoRegistro === 'regular' ? 'Solo Cursados' : ($filtroTipoRegistro === 'homologacion' ? 'Solo Homologados' : 'Todos') }} | 
                Estudiantes: {{ $filtroEstadoUsuario === 'activos' ? 'Solo activos' : ($filtroEstadoUsuario === 'dados_de_baja' ? 'Solo dados de baja' : 'Activos + Dados de baja') }} | 
                Fecha de emisión: {{ now()->format('d/m/Y H:i') }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center; background-color: #f2f2f2; border: 1px solid #cccccc;">No.</th>
            <th style="font-weight: bold; text-align: left; background-color: #f2f2f2; border: 1px solid #cccccc;">Identificación</th>
            <th style="font-weight: bold; text-align: left; background-color: #f2f2f2; border: 1px solid #cccccc;">Apellidos</th>
            <th style="font-weight: bold; text-align: left; background-color: #f2f2f2; border: 1px solid #cccccc;">Nombres</th>
            <th style="font-weight: bold; text-align: left; background-color: #f2f2f2; border: 1px solid #cccccc;">Correo Electrónico</th>
            <th style="font-weight: bold; text-align: center; background-color: #f2f2f2; border: 1px solid #cccccc;">Estado</th>
            <th style="font-weight: bold; text-align: center; background-color: #f2f2f2; border: 1px solid #cccccc;">Tipo</th>
            <th style="font-weight: bold; text-align: center; background-color: #cff4fc; color: #055160; border: 1px solid #cccccc;">Calificación</th>
            @if ($modo === 'materias')
                <th style="font-weight: bold; text-align: center; background-color: #e2d9f3; color: #512da8; border: 1px solid #cccccc;">Créditos</th>
            @endif
            @if ($modo === 'materias' && !empty($itemActivo?->habilitar_asistencias))
                <th style="font-weight: bold; text-align: center; background-color: #f2f2f2; border: 1px solid #cccccc;">Asistencias</th>
            @endif
            <th style="font-weight: bold; text-align: center; background-color: #f2f2f2; border: 1px solid #cccccc;">Fecha Registro</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($estudiantes as $index => $est)
            <tr>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $index + 1 }}</td>
                <td style="text-align: left; border: 1px solid #cccccc;">{{ $est['identificacion'] ?: 'S/D' }}</td>
                <td style="border: 1px solid #cccccc;">{{ $est['apellidos'] }}{{ !empty($est['dado_de_baja']) ? ' (Dado de baja)' : '' }}</td>
                <td style="border: 1px solid #cccccc;">{{ $est['nombres'] }}</td>
                <td style="border: 1px solid #cccccc;">{{ $est['email'] }}</td>
                <td style="text-align: center; border: 1px solid #cccccc; {{ $est['estado'] === 1 ? 'background-color: #c6efce; color: #006100; font-weight: bold;' : ($est['estado'] === 2 ? 'background-color: #ffeb9c; color: #9c6500;' : 'background-color: #ffc7ce; color: #9c0006;') }}">
                    {{ $est['estado'] === 1 ? 'Aprobado' : ($est['estado'] === 2 ? 'En proceso' : 'Reprobado') }}
                </td>
                <td style="text-align: center; border: 1px solid #cccccc;">
                    {{ $est['es_homologacion'] ? 'Homologado' : 'Cursado' }}
                </td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #cccccc;">
                    {{ $est['nota'] !== null ? $est['nota'] : '-' }}
                </td>
                @if ($modo === 'materias')
                    <td style="text-align: center; font-weight: bold; color: #512da8; border: 1px solid #cccccc;">
                        {{ $est['estado'] === 1 ? ($est['creditos'] ?? 0) : 0 }}
                    </td>
                @endif
                @if ($modo === 'materias' && !empty($itemActivo?->habilitar_asistencias))
                    <td style="text-align: center; border: 1px solid #cccccc;">
                        {{ $est['asistencias'] ?? 0 }}
                    </td>
                @endif
                <td style="text-align: center; border: 1px solid #cccccc;">
                    {{ $est['fecha_registro'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
