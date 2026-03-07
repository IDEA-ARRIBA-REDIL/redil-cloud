<div>
    <form wire:submit.prevent="guardarYFinalizar">
        <div class="card">
            <div class="card-body">
                {{-- INICIO: Campo de Búsqueda (Copiado y adaptado de la otra vista) --}}
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-2 col-md-3 col-sm-12">
                        <label for="busquedaAlumnoInput" class="form-label mb-0">Buscar Alumno:</label>
                    </div>
                    <div class="col-lg-10 col-md-9 col-sm-12">
                        <input type="text" class="form-control" id="busquedaAlumnoInput"
                            wire:model.live.debounce.300ms="busqueda"
                            placeholder="Buscar por nombre o identificación...">
                    </div>
                </div>
                {{-- FIN: Campo de Búsqueda --}}


                {{-- ENCABEZADO - Visible solo en pantallas grandes (lg y superiores) --}}
                <div class="row d-none d-lg-flex fw-semibold text-black border-bottom pb-2 mb-3">
                    <div class="col-lg-4">ALUMNO</div>
                    <div class="col-lg-3">MOTIVO INASISTENCIA</div>
                    <div class="col-lg-2">OBSERVACIÓN</div>
                    <div class="col-lg-3 text-center">¿ASISTIÓ?</div>
                </div>

                {{-- CAMBIO: Usamos @forelse y la nueva variable $alumnosParaLaVista --}}
                @forelse ($alumnosParaLaVista as $alumno)
                    @if ($alumno)
                        <div class="card shadow-sm mb-3" wire:key="alumno-card-{{ $alumno->id }}">
                            <div class="card-body p-3">
                                <div class="row g-3 align-items-center">
                                    {{-- COLUMNA 1: Información del Alumno --}}
                                    <div class="col-12 col-lg-4">
                                        {{-- ... (contenido de la columna del alumno sin cambios) ... --}}
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="avatar-initial p-2 rounded-circle bg-label-secondary">
                                                    @php
                                                        $nombreCompleto = trim(
                                                            ($alumno->primer_nombre ?? '') .
                                                                ' ' .
                                                                ($alumno->primer_apellido ?? ''),
                                                        );
                                                        $iniciales =
                                                            mb_substr($alumno->primer_nombre ?? '', 0, 1) .
                                                            mb_substr($alumno->primer_apellido ?? '', 0, 1);
                                                    @endphp
                                                    {{ $iniciales ?: 'N/A' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-medium  text-black d-block">{{ $nombreCompleto }}</span>
                                                <small class="text-muted">ID:
                                                    {{ $alumno->identificacion ?? 'N/A' }}</small>
                                                <br>
                                                @if (isset($asistencias[$alumno->id]['auto_asistencia']) && $asistencias[$alumno->id]['auto_asistencia'] == true)
                                                    <span class="badge btn-success rounded-pill mt-2">Auto
                                                        asistencia</span>
                                                @else
                                                    <span class="badge btn-info rounded-pill mt-2">Manual</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    {{-- COLUMNA 2: Motivo de Inasistencia --}}
                                    <div class="col-12 col-lg-3">
                                        {{-- ... (contenido de la columna de motivo sin cambios) ... --}}
                                        <label class="form-label d-lg-none">Motivo Inasistencia:</label>
                                        <select class="form-select form-select-sm"
                                            wire:model.live="asistencias.{{ $alumno->id }}.motivo_inasistencia_id"
                                            @if (isset($asistencias[$alumno->id]['asistio']) && $asistencias[$alumno->id]['asistio'] === true) disabled @endif>
                                            <option value="">Seleccionar...</option>
                                            @foreach ($motivosInasistencia as $motivo)
                                                <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('asistencias.' . $alumno->id . '.motivo_inasistencia_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- COLUMNA 3: Botón de Observación --}}
                                    <div class="col-6 col-lg-3 ps-5">
                                        {{-- ... (contenido de la columna de observación sin cambios) ... --}}
                                        <label class="form-label d-lg-none">Observación:</label>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-pill w-80"
                                            wire:click="abrirModalObservacion({{ $alumno->id }})">
                                            <i class="mdi mdi-comment-edit-outline"></i>
                                            <span class="d-md-inline ms-1">Observaciones</span>
                                        </button>
                                    </div>
                                    {{-- COLUMNA 4: Interruptor de Asistencia --}}
                                    <div class="col-6 col-lg-2 text-start">
                                        {{-- ... (contenido de la columna de asistencia sin cambios) ... --}}
                                        <label class="form-label d-lg-none">¿Asistió?:</label>
                                        <div class="d-flex justify-content-start ps-5">
                                            <label class="switch switch-lg">
                                                <input type="checkbox" class="switch-input"
                                                    wire:model.live="asistencias.{{ $alumno->id }}.asistio" />
                                                <span class="switch-toggle-slider"><span
                                                        class="switch-on">SI</span><span
                                                        class="switch-off">NO</span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    {{-- Mensaje para cuando no hay resultados de búsqueda --}}
                    <div class="text-center p-4">
                        <i class="mdi mdi-account-search-outline mdi-48px text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron alumnos</h5>
                        <p class="text-muted mb-0">
                            No hay alumnos que coincidan con tu búsqueda: "{{ $busqueda }}"
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="card-footer text-start">
                <a href="{{ route('maestros.reporteAsistencia', ['maestro' => $maestro->id, 'horarioAsignado' => $horarioAsignado->id]) }}"
                    class="btn btn-outline-secondary me-3 mb-4 rounded-pill">Volver</a>
                <button type="submit" class="btn btn-primary mb-4 rounded-pill">
                    <i class="mdi mdi-check-all me-1"></i> Finalizar y Guardar Todo
                </button>
            </div>
        </div>
    </form>

    {{-- Modal para la observación (sin cambios) --}}
    @if ($mostrarModalObservacion)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Observación para: {{ $nombreAlumnoModal }}</h5>
                        <button type="button" class="btn-close" wire:click="cerrarModalObservacion"></button>
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control" rows="5" wire:model="observacionActual"
                            placeholder="Escribe una observación detallada..."></textarea>
                        @error('observacionActual')
                            <span class="text-danger small mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary rounded-pill mb-5"
                            wire:click="cerrarModalObservacion">Cancelar</button>
                        <button type="button" class="btn btn-primary rounded-pill mb-5"
                            wire:click="guardarObservacion">Guardar Observación</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
