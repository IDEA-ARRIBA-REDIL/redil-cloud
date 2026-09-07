<table>
    <thead>
        <tr>
            <th colspan="{{ 3 + $items->count() + ($modo === 'materias' ? 4 : 3) }}" style="font-size: 14px; font-weight: bold; text-align: center; background-color: #007788; color: #ffffff;">
                CONSOLIDADO ACADÉMICO: {{ mb_strtoupper($escuelaNombre) }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + $items->count() + ($modo === 'materias' ? 4 : 3) }}" style="font-size: 11px; text-align: center; color: #555555;">
                Filtro aplicado: {{ $filtroTipoMaterias === 'obligatorias' ? 'Solo materias/niveles obligatorios' : ($filtroTipoMaterias === 'opcionales' ? 'Solo materias/niveles opcionales' : 'Todas las materias/niveles') }} | Estado: {{ $filtroEstadoUsuario === 'activos' ? 'Solo activos' : ($filtroEstadoUsuario === 'dados_de_baja' ? 'Solo dados de baja' : 'Activos + Dados de baja') }} | Fecha de emisión: {{ now()->format('d/m/Y H:i') }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center; background-color: #f2f2f2; border: 1px solid #cccccc;">No.</th>
            <th style="font-weight: bold; text-align: left; background-color: #f2f2f2; border: 1px solid #cccccc;">Apellidos</th>
            <th style="font-weight: bold; text-align: left; background-color: #f2f2f2; border: 1px solid #cccccc;">Nombres</th>
            @foreach ($items as $item)
                <th style="font-weight: bold; text-align: center; background-color: #e9ecef; border: 1px solid #cccccc;">
                    {{ $item->nombre }}
                    @if ($modo === 'materias' && !empty($item->creditos))
                        ({{ $item->creditos }} cr)
                    @endif
                    @if ($item->caracter_obligatorio)
                        *
                    @endif
                </th>
            @endforeach
            <th style="font-weight: bold; text-align: center; background-color: #d1e7dd; color: #0f5132; border: 1px solid #cccccc;">Total Cursadas</th>
            @if ($modo === 'materias')
                <th style="font-weight: bold; text-align: center; background-color: #e2d9f3; color: #512da8; border: 1px solid #cccccc;">Créditos Aprobados</th>
            @endif
            <th style="font-weight: bold; text-align: center; background-color: #cff4fc; color: #055160; border: 1px solid #cccccc;">Promedio</th>
            <th style="font-weight: bold; text-align: center; background-color: #fff3cd; color: #664d03; border: 1px solid #cccccc;">% Avance</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($estudiantes as $index => $est)
            <tr>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #cccccc;">{{ $est['apellidos'] }}{{ !empty($est['dado_de_baja']) ? ' (Dado de baja)' : '' }}</td>
                <td style="border: 1px solid #cccccc;">{{ $est['nombres'] }}</td>
                @foreach ($items as $item)
                    @php
                        $det = $est['detalle_items'][$item->id] ?? null;
                    @endphp
                    @if ($det && $det['estado'] === 1)
                        <td style="text-align: center; background-color: #c6efce; color: #006100; font-weight: bold; border: 1px solid #cccccc;">
                            {{ $det['nota'] ?? 'Aprobado' }}
                        </td>
                    @elseif ($det && $det['estado'] === 2)
                        <td style="text-align: center; background-color: #ffeb9c; color: #9c6500; border: 1px solid #cccccc;">
                            En proceso
                        </td>
                    @elseif ($det && $det['estado'] === 0)
                        <td style="text-align: center; background-color: #ffc7ce; color: #9c0006; border: 1px solid #cccccc;">
                            {{ $det['nota'] ?? 'Reprobado' }}
                        </td>
                    @else
                        <td style="text-align: center; background-color: #f8d7da; color: #b02a37; border: 1px solid #cccccc;">
                            -
                        </td>
                    @endif
                @endforeach
                <td style="text-align: center; font-weight: bold; background-color: #f9f9f9; border: 1px solid #cccccc;">{{ $est['aprobadas'] }}/{{ $est['total_evaluados'] ?? $items->count() }}</td>
                @if ($modo === 'materias')
                    <td style="text-align: center; font-weight: bold; background-color: #f9f9f9; color: #512da8; border: 1px solid #cccccc;">{{ $est['creditos_aprobados'] ?? 0 }}</td>
                @endif
                <td style="text-align: center; font-weight: bold; background-color: #f9f9f9; border: 1px solid #cccccc;">{{ $est['promedio'] !== null ? number_format($est['promedio'], 1) : '-' }}</td>
                <td style="text-align: center; font-weight: bold; background-color: #f9f9f9; border: 1px solid #cccccc;">{{ number_format($est['porcentaje_avance'], 1) }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>
