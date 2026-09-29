<div class="calificacion-grilla">
    <style>
        .calificacion-grilla .grilla-toolbar {
            background: linear-gradient(135deg, rgba(115, 103, 240, .08), rgba(40, 199, 111, .05));
            border: 1px solid rgba(115, 103, 240, .14);
        }

        .calificacion-grilla .grilla-buscador {
            max-width: 560px;
        }

        .calificacion-grilla .grilla-contenedor {
            max-height: 72vh;
            overflow: auto;
            scrollbar-gutter: stable;
        }

        .calificacion-grilla .grilla-tabla {
            border-collapse: separate;
            border-spacing: 0;
            min-width: max-content;
            width: 100%;
        }

        .calificacion-grilla .grilla-tabla th,
        .calificacion-grilla .grilla-tabla td {
            border-bottom: 1px solid #ebe9f1;
            border-right: 1px solid #ebe9f1;
        }

        .calificacion-grilla .grilla-tabla thead th {
            background: #f8f7fa;
            box-shadow: 0 2px 5px rgba(47, 43, 61, .08);
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .calificacion-grilla .columna-alumno {
            background: #fff;
            left: 0;
            min-width: 290px;
            position: sticky;
            width: 290px;
            z-index: 10;
        }

        .calificacion-grilla thead .columna-alumno {
            background: #f8f7fa;
            z-index: 30;
        }

        .calificacion-grilla .encabezado-item {
            min-width: 150px;
            width: 150px;
        }

        .calificacion-grilla .nombre-item {
            display: block;
            max-width: 135px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .calificacion-grilla .celda-nota {
            min-width: 150px;
            padding: .75rem;
            text-align: center;
            vertical-align: middle;
        }

        .calificacion-grilla .entrada-nota {
            border-radius: .5rem;
            font-weight: 600;
            margin: auto;
            max-width: 96px;
        }

        .calificacion-grilla .nota-bloqueada {
            align-items: center;
            background: rgba(234, 84, 85, .08);
            border: 1px solid rgba(234, 84, 85, .18);
            border-radius: .5rem;
            display: inline-flex;
            gap: .4rem;
            justify-content: center;
            min-height: 38px;
            min-width: 78px;
        }

        .calificacion-grilla .grilla-tabla tbody tr:hover td:not(.columna-alumno) {
            background: rgba(115, 103, 240, .035);
        }

        .calificacion-grilla input[type='number']::-webkit-inner-spin-button,
        .calificacion-grilla input[type='number']::-webkit-outer-spin-button {
            margin-left: 5px;
            opacity: 1;
        }

        @media (max-width: 767.98px) {
            .calificacion-grilla .columna-alumno {
                min-width: 235px;
                width: 235px;
            }

            .calificacion-grilla .grilla-contenedor {
                max-height: 68vh;
            }
        }
    </style>

    <div class="card grilla-toolbar shadow-none mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h5 class="mb-1">Registro rápido de calificaciones</h5>
                    <p class="text-muted mb-0">Busca un alumno y edita sus notas directamente en la grilla.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill bg-label-primary">{{ $alumnosFiltrados->count() }} de {{ $alumnosConEstado->count() }} alumnos</span>
                    <span class="badge rounded-pill bg-label-info">{{ $items->count() }} {{ $items->count() === 1 ? 'ítem' : 'ítems' }}</span>
                    <span class="badge rounded-pill bg-label-secondary">Escala: {{ $configuracion->nota_minima ?? 0 }} a {{ $configuracion->nota_maxima ?? 5 }}</span>
                </div>
            </div>

            <div class="grilla-buscador">
                <label for="busquedaAlumnoGrilla" class="form-label fw-medium">Buscar alumno</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="search" id="busquedaAlumnoGrilla" class="form-control"
                        wire:model.live.debounce.300ms="busquedaAlumno"
                        placeholder="Nombre, apellido, identificación o correo..." autocomplete="off">
                    <span class="input-group-text" wire:loading wire:target="busquedaAlumno">
                        <span class="spinner-border spinner-border-sm text-primary" role="status" aria-label="Buscando"></span>
                    </span>
                    @if ($busquedaAlumno !== '')
                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('busquedaAlumno', '')" aria-label="Limpiar búsqueda">
                            <i class="ti ti-x"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($alumnosConEstado->isEmpty())
        <div class="alert alert-warning text-center" role="alert">
            <i class="ti ti-users-off fs-2 d-block mb-2"></i>
            <h5 class="alert-heading">No hay estudiantes matriculados</h5>
            <p class="mb-0">Por el momento no se encuentra ningún estudiante matriculado en esta clase.</p>
        </div>
    @elseif ($alumnosFiltrados->isEmpty())
        <div class="alert alert-info text-center" role="status">
            <i class="ti ti-search-off fs-2 d-block mb-2"></i>
            <h5 class="alert-heading">No encontramos alumnos</h5>
            <p class="mb-0">Prueba con otro nombre, apellido, identificación o correo.</p>
        </div>
    @elseif ($items->isEmpty())
        <div class="alert alert-info text-center" role="alert">
            <i class="ti ti-clipboard-off fs-2 d-block mb-2"></i>
            <h5 class="alert-heading">No hay ítems para calificar</h5>
            <p class="mb-0">Crea o habilita los ítems de evaluación de este horario para usar la grilla.</p>
        </div>
    @else
        <div class="card border shadow-sm overflow-hidden">
            <div class="grilla-contenedor">
                <table class="table grilla-tabla align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="columna-alumno p-3 text-uppercase small">Alumno</th>
                            @foreach ($items as $item)
                                <th class="encabezado-item p-2 text-center" wire:key="encabezado-item-{{ $item->id }}"
                                    title="{{ $item->nombre }} · {{ $item->cortePeriodo->corteEscuela->nombre ?? 'Corte' }}">
                                    <span class="badge rounded-pill bg-label-primary mb-2">
                                        {{ $item->cortePeriodo->corteEscuela->nombre ?? 'Corte' }} · {{ round($item->porcentaje, 0) }}%
                                    </span>
                                    <strong class="nombre-item mx-auto text-body">{{ $item->nombre }}</strong>
                                    <small class="d-block text-muted fw-normal mt-1">
                                        {{ $item->fecha_inicio?->format('d/m/Y') ?? 'Sin inicio' }} – {{ $item->fecha_fin?->format('d/m/Y') ?? 'Sin fin' }}
                                    </small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alumnosFiltrados as $estado)
                            @php
                                $matricula = $estado->matricula;
                                $user = $estado->user;
                                $bloqueado = $matricula->bloqueado ?? false;
                                $trasladado = $matricula->trasladado ?? false;
                                $nombreCompleto = $user ? trim(implode(' ', array_filter([$user->primer_apellido, $user->segundo_apellido, $user->primer_nombre, $user->segundo_nombre]))) : 'Alumno no disponible';
                            @endphp

                            @if ($user)
                                <tr wire:key="fila-calificacion-{{ $user->id }}">
                                    <td class="columna-alumno p-3">
                                        <div class="d-flex align-items-center gap-2">
                                            @if (! $user->foto || in_array($user->foto, ['default-m.png', 'default-f.png']))
                                                <span class="avatar-initial rounded-circle bg-label-info fw-bold flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                    {{ $user->inicialesNombre() }}
                                                </span>
                                            @else
                                                <img src="{{ $user->foto_url }}" alt="Foto de {{ $nombreCompleto }}"
                                                    class="rounded-circle flex-shrink-0" style="width: 38px; height: 38px; object-fit: cover;">
                                            @endif
                                            <div class="min-w-0">
                                                <strong class="d-block text-body text-truncate" style="max-width: 205px;" title="{{ $nombreCompleto }}">{{ $nombreCompleto }}</strong>
                                                <small class="d-block text-muted text-truncate" style="max-width: 205px;">
                                                    {{ $user->identificacion ?: 'Sin identificación' }}
                                                    @if ($user->email) · {{ $user->email }} @endif
                                                </small>
                                                @if ($bloqueado)
                                                    <span class="badge rounded-pill bg-label-danger mt-1">Bloqueado</span>
                                                @elseif ($trasladado)
                                                    <span class="badge rounded-pill bg-label-warning mt-1">Trasladado</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    @foreach ($items as $item)
                                        <td class="celda-nota" wire:key="nota-{{ $user->id }}-{{ $item->id }}">
                                            @if ($bloqueado)
                                                <span class="badge rounded-pill bg-label-danger"><i class="ti ti-lock me-1"></i>Bloqueado</span>
                                            @elseif ($trasladado)
                                                <span class="badge rounded-pill bg-label-warning"><i class="ti ti-transfer me-1"></i>Trasladado</span>
                                            @else
                                                @php
                                                    $fechaFinItem = $item->fecha_fin ? \Carbon\Carbon::parse($item->fecha_fin)->endOfDay() : null;
                                                    $fechaFinCorte = $item->cortePeriodo->fecha_fin ? \Carbon\Carbon::parse($item->cortePeriodo->fecha_fin)->endOfDay() : null;
                                                    $limite = $fechaFinCorte ?? $fechaFinItem;
                                                    $vencido = $limite && $fechaActual->gt($limite);
                                                    $editable = $puedeCalificarSinFecha || ! $vencido;
                                                @endphp

                                                @if (! $editable)
                                                    <div class="nota-bloqueada" title="Plazo vencido">
                                                        <strong>{{ $notas[$user->id][$item->id] ?? '—' }}</strong>
                                                        <i class="ti ti-lock text-danger"></i>
                                                    </div>
                                                @else
                                                    <input type="number" step="0.1"
                                                        min="{{ $configuracion->nota_minima ?? 0 }}"
                                                        max="{{ $configuracion->nota_maxima ?? 5 }}"
                                                        class="form-control form-control-sm text-center entrada-nota"
                                                        wire:model.live.debounce.500ms="notas.{{ $user->id }}.{{ $item->id }}"
                                                        aria-label="Nota de {{ $nombreCompleto }} en {{ $item->nombre }}"
                                                        placeholder="—">
                                                    @error("notas.{$user->id}.{$item->id}")
                                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                                    @enderror
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
