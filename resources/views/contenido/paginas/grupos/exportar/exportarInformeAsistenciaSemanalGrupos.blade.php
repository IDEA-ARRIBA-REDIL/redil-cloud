{{-- resources/views/exports/informe-asistencia.blade.php --}}

{{-- Este archivo NO usa @extends ni @section. Es HTML puro para las tablas. --}}

@if($resumen)
    {{-- TABLA RESUMEN --}}
    <table>
        <thead>
            <tr>
                {{-- La fusión de celdas se hará en la clase Export --}}
                <th>MES</th>
                @foreach ($encabezadosAgrupados as $data)
                    <th colspan="{{ $data['colspan'] }}">{{ $data['mes'] }}</th>
                @endforeach
                <th rowspan="2">PROMEDIOS</th>
            </tr>
            <tr>
                <th>FECHAS</th>
                @foreach ($encabezados as $encabezado)
                    <th>{{ $encabezado['dias'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{-- Filas de Clasificaciones --}}
            @foreach ($clasificacionesSeleccionadas as $clasificacion)
                <tr>
                    <td>Total {{ $clasificacion->nombre }}</td>
                    @foreach ($encabezados as $encabezado)
                        <td>{{ $resumen['clasificaciones'][$clasificacion->id][$encabezado['semana']] ?? 0 }}</td>
                    @endforeach
                    <td>{{ round($resumen['promedios']['clasificaciones'][$clasificacion->id] ?? 0) }}</td>
                </tr>
            @endforeach

            {{-- Fila de Asistencias --}}
            <tr>
                <td>Total Asistencias</td>
                @foreach ($encabezados as $encabezado)
                    <td>{{ $resumen['asistencias'][$encabezado['semana']] ?? 0 }}</td>
                @endforeach
                <td>{{ round($resumen['promedios']['asistencias'] ?? 0) }}</td>
            </tr>
            {{-- Fila de Inasistencias --}}
            <tr>
                <td>Total Inasistencias</td>
                @foreach ($encabezados as $encabezado)
                    <td>{{ $resumen['inasistencias'][$encabezado['semana']] ?? 0 }}</td>
                @endforeach
                <td>{{ round($resumen['promedios']['inasistencias'] ?? 0) }}</td>
            </tr>
            {{-- Fila de Nro. Reportes --}}
            <tr>
                <td>Total Nro. Reportes</td>
                @foreach ($encabezados as $encabezado)
                    <td>{{ $resumen['reportes'][$encabezado['semana']] ?? 0 }}</td>
                @endforeach
                <td>{{ $resumen['promedios']['reportes']['real'] ?? 0 }} / {{ $resumen['promedios']['reportes']['esperado'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Espacio entre tablas --}}
    <table>
        <tr><td></td></tr>
        <tr><td></td></tr>
    </table>
@endif


@if($dataPivoteada)
    {{-- TABLA GENERAL --}}
    <table>
        <thead>
             <tr>
                <th>MES</th>
                @foreach ($encabezadosAgrupados as $data)
                    <th colspan="{{ $data['colspan'] }}">{{ $data['mes'] }}</th>
                @endforeach
                <th rowspan="2">PROMEDIOS</th>
            </tr>
            <tr>
                <th>FECHAS</th>
                @foreach ($encabezados as $encabezado)
                    <th>{{ $encabezado['dias'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($dataPivoteada as $nombreGrupo => $datosGrupo)
                {{-- Info del grupo (se fusionará en la clase Export) --}}
                 <tr>
                    <th colspan="{{ $totalColumnas }}">
                        @php
                            // Preparamos la cadena de texto para los encargados
                            $encargadosInfo = [];
                            if (!empty($datosGrupo['encargados'])) {
                                foreach ($datosGrupo['encargados'] as $encargado) {
                                    // Añadimos "Nombre (Tipo de Usuario)" a un array
                                    $encargadosInfo[] = $encargado->encargado_nombre . ' (' . $encargado->tipo_usuario_nombre . ')';
                                }
                            }
                            // Unimos los nombres con un separador
                            $textoEncargados = implode(' | ', $encargadosInfo);

                            // Construimos el texto final para la celda.
                            // Excel no interpreta bien los saltos de línea HTML, por eso lo concatenamos todo.
                            $textoCelda = $nombreGrupo . ' (' . $datosGrupo['tipo_grupo'] . ')';
                            if (!empty($textoEncargados)) {
                                $textoCelda .= ' - Encargados: ' . $textoEncargados;
                            }
                        @endphp
                        {{ $textoCelda }}
                    </th>
                </tr>

                {{-- Filas de clasificaciones --}}
                @foreach ($clasificacionesSeleccionadas as $clasificacion)
                <tr>
                    <td>{{ $clasificacion->nombre }}</td>
                    @foreach ($encabezados as $semana)
                        <td>
                            @if ($semana['semana'] < $datosGrupo['semana_apertura'])
                                -
                            @else
                                {{ $datosGrupo['datos_semanales'][$semana['semana']]['clasificaciones'][$clasificacion->id] ?? 0 }}
                            @endif
                        </td>
                    @endforeach
                    <td>{{ round($datosGrupo['promedios']['clasificaciones'][$clasificacion->id] ?? 0) }}</td>
                </tr>
                @endforeach

                {{-- Asistencias, Inasistencias, Reportes --}}
                <tr>
                    <td>Asistencias</td>
                     @foreach ($encabezados as $semana)
                        <td>
                            @if ($semana['semana'] < $datosGrupo['semana_apertura'])
                                -
                            @else
                                {{ $datosGrupo['datos_semanales'][$semana['semana']]['asistencias'] ?? 0 }}
                            @endif
                        </td>
                    @endforeach
                    <td>{{ round($datosGrupo['promedios']['asistencias'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>Inasistencias</td>
                     @foreach ($encabezados as $semana)
                        <td>
                             @if ($semana['semana'] < $datosGrupo['semana_apertura'])
                                -
                            @else
                                {{ $datosGrupo['datos_semanales'][$semana['semana']]['inasistencias'] ?? 0 }}
                            @endif
                        </td>
                    @endforeach
                    <td>{{ round($datosGrupo['promedios']['inasistencias'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>Nro. Reportes</td>
                     @foreach ($encabezados as $semana)
                        <td>
                            @if ($semana['semana'] < $datosGrupo['semana_apertura'])
                                -
                            @else
                                {{ $datosGrupo['datos_semanales'][$semana['semana']]['reportes'] ?? 0 }}
                            @endif
                        </td>
                    @endforeach
                    <td>{{ $datosGrupo['promedios']['reportes']['real'] ?? 0 }} / {{ $datosGrupo['promedios']['reportes']['esperado'] ?? 0 }}</td>
                </tr>

                {{-- Fila de espacio entre grupos --}}
                <tr><td></td></tr>

            @empty
                <tr>
                    <td colspan="{{ count($encabezados) + 2 }}">No se encontraron datos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif
