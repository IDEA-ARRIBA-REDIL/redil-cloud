<div class="row" style="margin:30px">
    @if($alumnosConEstado->count() > 0)

        <style>
            .table-container {
                overflow-x: auto;
                max-width: 100%;
                /* Altura máxima para permitir scroll vertical si hay muchos alumnos */
                max-height: 80vh;
            }
            .fixed_header {
                width: 100%;
                border-collapse: separate; /* Changed for border spacing if needed */
                border-spacing: 0;
            }
            .fixed_header th, .fixed_header td {
                border: 1px solid #ddd;
            }

            /* Sticky Header (Encabezados de columna) */
            .pinnedth {
                position: sticky;
                top: 0;
                z-index: 100; /* Z-index alto para estar sobre el contenido */
                background-color: #f2f2f2 !important; /* Fondo sólido para tapar al hacer scroll */
                box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
            }

            /* Sticky First Column (Nombre Alumno) */
            .pinnedtd {
                position: sticky;
                left: 0;
                z-index: 50; /* Menor que el encabezado pero mayor que el contenido normal */
                background-color: #fff !important; /* White background */
                box-shadow: 4px 0 5px -2px rgba(0,0,0,0.1); /* Subtle shadow for depth */
                border-right: 2px solid #d9d9d9 !important; /* Clear separation */
            }

            /* Intersección */
            .fixed_header th:first-child {
                position: sticky;
                left: 0;
                top: 0;
                z-index: 150; /* El más alto de todos para la esquina */
                background-color: #f2f2f2 !important;
                border-right: 2px solid #d9d9d9;
            }

            /* Spin Buttons Bigger */
            input[type=number]::-webkit-inner-spin-button,
            input[type=number]::-webkit-outer-spin-button {
                opacity: 1;
                transform: scale(1.3);
                margin-left: 5px;
            }
        </style>

        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="table-container">
                    <table class="fixed_header table table-bordered mb-0">

                    <thead>
                        <tr>
                            <th style="min-width:250px; padding:10px; background:#eee; text-align:center" class="pinnedth letra mayusculas text-center">
                                ALUMNO
                            </th>
                            @foreach($items as $item)
                                <th style="min-width:130px; border: solid 1px #ddd; text-align:center; padding: 5px;"
                                    title="{{ ucwords(strtolower($item->nombre)) }} - {{ $item->cortePeriodo->corteEscuela->nombre ?? 'Corte' }}"
                                    class="pinnedth letra text-dark text-center mayusculas">

                                    <div style="font-size:11px; color:#666; margin-bottom: 2px;">
                                        {{ $item->cortePeriodo->corteEscuela->nombre ?? 'Corte' }} ({{ round($item->porcentaje, 0) }}%)
                                    </div>
                                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;">
                                        {{ ucwords(strtolower($item->nombre)) }}
                                    </div>

                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alumnosConEstado as $estado)
                            @php
                                $matricula = $estado->matricula;
                                $user = $estado->user;
                                $bloqueado = $matricula->bloqueado ?? false;
                                $trasladado = $matricula->trasladado ?? false;
                            @endphp

                            {{-- FILA DE ALUMNO --}}
                            <tr>
                                <td style="background:#fff; padding: 5px;" class="pinnedtd letra">
                                    <div class="d-flex align-items-center">
                                        @if($user->foto)
                                            <img style="border-radius:50%; width: 35px; height: 35px; object-fit:cover; margin-right: 10px;"
                                                 src="{{ asset('storage/fotos/' . $user->foto) }}" alt="Avatar"
                                                 onerror="this.src='{{ asset('assets/img/avatars/1.png') }}'">
                                        @else
                                             <img style="border-radius:50%; width: 35px; height: 35px; object-fit:cover; margin-right: 10px;"
                                                 src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar">
                                        @endif
                                        <div>
                                            <p style="font-size:13px; margin:0;" class="letra text-dark fw-semibold">
                                                {{ $user->primer_apellido }} {{ $user->segundo_apellido }}
                                            </p>
                                            <p style="font-size:13px; margin:0;" class="letra text-dark fw-semibold">
                                                {{ $user->primer_nombre }} {{ $user->segundo_nombre }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                @foreach($items as $item)
                                    <td style="text-align:center; vertical-align:middle; padding: 8px;">
                                        @if($bloqueado)
                                            <span class="badge badge-danger">BLOQUEADO</span>
                                        @elseif($trasladado)
                                            <span class="badge badge-warning">TRASLADADO</span>
                                        @else
                                            {{-- Lógica de Campo de Calificación --}}

                                            {{-- Verificamos si podemos editar --}}
                                            @php
                                                $fechaFinItem = $item->fecha_fin ? \Carbon\Carbon::parse($item->fecha_fin)->endOfDay() : null;
                                                $fechaFinCorte = $item->cortePeriodo->fecha_fin ? \Carbon\Carbon::parse($item->cortePeriodo->fecha_fin)->endOfDay() : null;
                                                $limite = $fechaFinCorte ?? $fechaFinItem;
                                                $vencido = $limite && $fechaActual->gt($limite);
                                                $editable = $puedeCalificarSinFecha || !$vencido;
                                            @endphp

                                            @if(!$editable)
                                                <div style="background-color: #ffe6e6; height: 100%; width: 100%; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Plazo vencido">
                                                    <span>{{ $notas[$user->id][$item->id] ?? '-' }}</span>
                                                    <i class="fas fa-lock fa-xs ml-1 text-muted"></i>
                                                </div>
                                            @else
                                                <input
                                                    type="number"
                                                    step="0.1"
                                                    min="{{ $configuracion->nota_minima ?? 0 }}"
                                                    max="{{ $configuracion->nota_maxima ?? 5 }}"
                                                    class="form-control form-control-sm text-center"
                                                    style="border: 1px solid #d9dee3; border-radius: 4px; height: 38px; width: 100%; font-weight: bold;"
                                                    wire:model.live.debounce.500ms="notas.{{ $user->id }}.{{ $item->id }}"
                                                    placeholder="-"
                                                >
                                                @error("notas.{$user->id}.{$item->id}")
                                                    <span class="text-danger" style="font-size: 9px; display:block;">{{ $message }}</span>
                                                @enderror
                                            @endif

                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        <div class="col-12 text-center mt-5">
            <div class="alert alert-warning" role="alert">
                <h4 class="alert-heading"><i class="fas fa-users fa-2x mb-2"></i><br>No hay estudiantes matriculados</h4>
                <p>Por el momento no se encuentra ningún estudiante matriculado en esta clase.</p>
            </div>
        </div>
    @endif
</div>

<script>
    // Script simple para feedback visual si Livewire no se encarga de todo o para notificaciones
    document.addEventListener('notaGuardada', event => {
        // Podrías resaltar la celda momentáneamente, pero Livewire ya actualiza el DOM
        // console.log('Nota guardada', event.detail);
    });
</script>
