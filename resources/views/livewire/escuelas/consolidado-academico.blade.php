<div class="row g-4">

    {{-- ========================================================================= --}}
    {{-- 1. BARRA SUPERIOR DE FILTROS Y CONTROLES                                  --}}
    {{-- ========================================================================= --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="row g-3 align-items-end">

                    {{-- Selector de Escuela --}}
                    <div class="col-12 col-md-4 col-lg-4">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-building-skyscraper me-1 text-primary"></i> Escuela:
                        </label>
                        <select wire:model.live="escuelaSeleccionadaId" class="form-select form-select-sm">
                            <option value="">-- Selecciona una escuela --</option>
                            @foreach ($escuelas as $esc)
                                <option value="{{ $esc->id }}">{{ $esc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Obligatorias vs Opcionales --}}
                    <div class="col-12 col-md-3 col-lg-3">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-filter me-1 text-primary"></i> Mostrar el pensum:
                        </label>
                        <select wire:model="filtroTipoMaterias" class="form-select form-select-sm">
                            <option value="obligatorias">Pensum obligatorio</option>
                            <option value="todas">Todo el pensum</option>
                            <option value="opcionales">Pensum opcional</option>
                        </select>
                    </div>

                    {{-- Selector de Estado de Estudiantes (Activos vs Dados de baja) --}}
                    <div class="col-12 col-md-3 col-lg-3">
                        <label class="form-label text-black fw-semibold small mb-1">
                            <i class="ti ti-user-check me-1 text-primary"></i> Estudiantes:
                        </label>
                        <select wire:model="filtroEstadoUsuario" class="form-select form-select-sm">
                            <option value="activos">Solo activos</option>
                            <option value="todos">Todos (Activos + Dados de baja)</option>
                            <option value="dados_de_baja">Solo dados de baja</option>
                        </select>
                    </div>

                    {{-- Botón de Filtrado / Consulta --}}
                    <div class="col-12 col-md-2 col-lg-2 text-md-end mt-md-4">
                        <button wire:click="consultar" type="button" class="btn btn-sm btn-primary rounded-pill w-100" wire:loading.attr="disabled" {{ empty($escuelaSeleccionadaId) ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="consultar">
                                <i class="ti ti-filter me-1"></i> Filtrar
                            </span>
                            <span wire:loading wire:target="consultar">
                                <i class="ti ti-loader rotate me-1"></i> Consultando...
                            </span>
                        </button>
                    </div>

                </div>
                
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. TARJETAS KPI (ESTILO INDICADORES DE USUARIOS)                          --}}
    {{-- ========================================================================= --}}
    @if ($consultado && !empty($escuelaId))
        <div class="col-12">
            <div class="row g-2">
                {{-- Total Estudiantes --}}
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-info rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-users fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Total Estudiantes</p>
                                <h5 class="mb-0 fw-bold text-dark">{{ $metricas['total'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Completaron Pensum (100%) --}}
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-success rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-certificate fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Completaron (100%)</p>
                                <h5 class="mb-0 fw-bold text-dark">{{ $metricas['completados'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- En Curso (1% a 99%) --}}
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-warning rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-trending-up fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">En proceso (1% - 99%)</p>
                                <h5 class="mb-0 fw-bold text-dark">{{ $metricas['en_curso'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Promedio Calificación General --}}
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md bg-info rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-award fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Promedio Escuela</p>
                                <h5 class="mb-0 fw-bold text-dark">{{ $metricas['promedio_general'] > 0 ? number_format($metricas['promedio_general'], 1) : '-' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($consultado && !empty($escuelaId) && $estudiantesPaginados->isNotEmpty())
    <div class="d-flex align-items-center justify-content-end gap-2">
        <button wire:click="exportarExcel" type="button" class="btn btn-sm btn-outline-success rounded-pill" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="exportarExcel">
                <i class="ti ti-file-spreadsheet me-1"></i> Exportar a excel
            </span>
            <span wire:loading wire:target="exportarExcel">
                <i class="ti ti-loader rotate me-1"></i> Generando...
            </span>
        </button>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 3. MATRIZ DE CONSOLIDADO ACADÉMICO (SÁBANA DE NOTAS)                     --}}
    {{-- ========================================================================= --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="ti ti-table me-1"></i> Matriz académica: {{ $escuelaActual?->nombre ?? 'Sin seleccionar' }}
                    </h5>
                    @if ($consultado && !empty($escuelaId))
                        <small class="text-black">
                            Evaluando {{ $itemsEvaluados->count() }} {{ $modo === 'materias' ? 'materias' : 'niveles' }} | Mostrando <b>{{ $estudiantesPaginados->total() }}</b> estudiantes con historial
                        </small>
                    @endif
                </div>

                {{-- Acciones Derecha: Botón Exportar + Selector de cantidad por página --}}
                <div class="d-flex align-items-center gap-2">                   
                    <label class="small text-black mb-0 ms-1">Mostrar:</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                </div>
            </div>

            @if ($consultado && !empty($escuelaId))
                <div class="row m-3 align-items-center">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="ti ti-search text-muted"></i>
                            </span>
                            <input type="text" wire:model.live.debounce.350ms="buscar" class="form-control form-control-sm border-start-0" placeholder="Buscar por nombre, apellido o cédula..." />
                            @if (!empty($buscar))
                                <button wire:click="$set('buscar', '')" class="btn btn-sm " type="button">
                                    <i class="ti ti-x"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-8 text-md-end">
                        
                    </div>
                </div>
            @endif

            @if (! $consultado || empty($escuelaId))
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl bg-label-primary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-school fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-black mb-1">Selecciona una escuela y filtra el consolidado</h5>
                    <p class="text-black mb-0">Elige una escuela en el selector superior y haz clic en <strong>Filtrar Consolidado</strong> para visualizar la matriz académica.</p>
                </div>
            @elseif ($itemsEvaluados->isEmpty())
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl bg-label-warning mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-books-off fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-black mb-1">No hay {{ $modo === 'materias' ? 'materias' : 'niveles' }} configurados</h5>
                    <p class="text-black mb-0">No se encontraron ítems bajo el filtro seleccionado ({{ $filtroTipoMaterias }}).</p>
                </div>
            @elseif ($estudiantesPaginados->isEmpty())
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl bg-label-info mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-user-x fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-black mb-1">No se encontraron estudiantes</h5>
                    <p class="text-black mb-0">Ningún estudiante coincide con los filtros aplicados en esta escuela.</p>
                </div>
            @else
                
               

                <div class="table-responsive" style="max-height: 560px; overflow-y: auto;">
                    <table class="table table-hover table-bordered align-middle mb-0" style="font-size: 0.8125rem;">
                        <thead class="table-light sticky-top shadow-sm" style="z-index: 10;">
                            <tr>
                                <th class="text-center bg-light" style="width: 50px;">No.</th>
                                <th class="bg-light" style="min-width: 110px;">Identificación</th>
                                <th class="bg-light" style="min-width: 220px;">Estudiante</th>

                                {{-- Columnas dinámicas de Materias o Niveles --}}
                                @foreach ($itemsEvaluados as $item)
                                    <th class="text-center text-truncate" style="min-width: 125px; max-width: 160px; background-color: #f1f5f9;" title="{{ $item->nombre }} ({{ $item->caracter_obligatorio ? 'Obligatoria' : 'Opcional' }})">
                                        <div class="fw-bold text-black text-truncate">{{ $item->nombre }}</div>
                                        <div class="d-flex align-items-center justify-content-center gap-1 mt-1">
                                            @if ($item->caracter_obligatorio)
                                                <span class="badge bg-label-danger rounded-pill" style="font-size: 0.65rem;">Obligatoria</span>
                                            @else
                                                <span class="badge bg-label-secondary rounded-pill" style="font-size: 0.65rem;">Opcional</span>
                                            @endif
                                        </div>
                                    </th>
                                @endforeach

                                {{-- Columnas de Resumen --}}
                                <th class="text-center" style="min-width: 95px; background-color: #d1e7dd; color: #0f5132;">Cursadas</th>
                                @if ($modo === 'materias')
                                    <th class="text-center" style="min-width: 85px; background-color: #e2d9f3; color: #512da8;">Créditos aprobados</th>
                                @endif
                                <th class="text-center" style="min-width: 85px; background-color: #cff4fc; color: #055160;">Promedio</th>
                                <th class="text-center" style="min-width: 130px; background-color: #fff3cd; color: #664d03;">% Avance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantesPaginados as $index => $est)
                                <tr>
                                    {{-- No. Correlativo --}}
                                    <td class="text-center fw-bold text-black">
                                        {{ ($estudiantesPaginados->currentPage() - 1) * $estudiantesPaginados->perPage() + $index + 1 }}
                                    </td>

                                    {{-- Identificación --}}
                                    <td class="fw-semibold text-black">
                                        {{ $est['identificacion'] ?: 'No identificado' }}
                                    </td>

                                    {{-- Estudiante --}}
                                    <td>
                                        <div class="text-black small">{{ $est['nombres'] }}</div>
                                        <div class="text-black small d-flex align-items-center gap-1">
                                            <span>{{ $est['apellidos'] }}</span>
                                            @if (!empty($est['dado_de_baja']))
                                                <span class="badge bg-label-danger rounded-pill" style="font-size: 0.6rem;" title="Usuario dado de baja">Dado de baja</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Celdas de cada Materia / Nivel --}}
                                    @foreach ($itemsEvaluados as $item)
                                        @php
                                            $det = $est['detalle_items'][$item->id] ?? null;
                                        @endphp
                                        <td class="text-center p-1" style="{{ $det && $det['estado'] === 1 ? 'background-color: rgba(40, 199, 111, 0.12);' : ($det && $det['estado'] === 2 ? 'background-color: rgba(255, 171, 0, 0.12);' : ($det && $det['estado'] === 0 ? 'background-color: rgba(234, 84, 85, 0.12);' : 'background-color: rgba(248, 215, 218, 0.35);')) }}">
                                            @if ($det && $det['estado'] === 1)
                                                <span class="badge text-white bg-success rounded-pill fw-bold py-1 px-2" style="font-size: 0.75rem;" title="Aprobado {{ $det['nota'] ? '- Nota: '.$det['nota'] : '' }} {{ $det['es_homologacion'] ? '(Homologada)' : '' }}">
                                                    {{ $det['nota'] ?? 'Aprobado' }}
                                                    @if ($det['es_homologacion'])
                                                        <small class="opacity-75 ms-1" title="Homologada">H</small>
                                                    @endif
                                                </span>
                                            @elseif ($det && $det['estado'] === 2)
                                                <span class="badge text-white bg-warning rounded-pill py-1 px-2" style="font-size: 0.72rem;">
                                                    En proceso
                                                </span>
                                            @elseif ($det && $det['estado'] === 0)
                                                <span class="badge text-white bg-danger rounded-pill py-1 px-2" style="font-size: 0.72rem;">
                                                    {{ $det['nota'] ?? 'Reprobado' }}
                                                </span>
                                            @else
                                                <span class="text-danger fw-semibold opacity-75">-</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    {{-- Resumen: Cursadas (ej. 1/6) --}}
                                    <td class="text-center fw-bold text-success ">
                                        {{ $est['aprobadas'] }}/{{ $est['total_evaluados'] ?? $itemsEvaluados->count() }}
                                    </td>

                                    {{-- Resumen: Créditos Aprobados --}}
                                    @if ($modo === 'materias')
                                        <td class="text-center fw-bold " style="color: #512da8;">
                                            {{ $est['creditos_aprobados'] ?? 0 }}
                                        </td>
                                    @endif

                                    {{-- Resumen: Promedio --}}
                                    <td class="text-center fw-bold ">
                                        {{ $est['promedio'] !== null ? number_format($est['promedio'], 1) : '-' }}
                                    </td>

                                    {{-- Resumen: % Avance --}}
                                    <td class="text-center ">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <div class="progress w-100" style="height: 6px;">
                                                <div class="progress-bar {{ $est['porcentaje_avance'] >= 100 ? 'bg-success' : ($est['porcentaje_avance'] > 50 ? 'bg-primary' : 'bg-warning') }}" role="progressbar" style="width: {{ $est['porcentaje_avance'] }}%"></div>
                                            </div>
                                            <span class="small fw-bold {{ $est['porcentaje_avance'] >= 100 ? 'text-success' : 'text-dark' }}" style="min-width: 45px; font-size: 0.75rem;">
                                                {{ number_format($est['porcentaje_avance'], 1) }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginador al pie --}}
                <div class="card-footer bg-light border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-black">
                        Mostrando del {{ $estudiantesPaginados->firstItem() ?? 0 }} al {{ $estudiantesPaginados->lastItem() ?? 0 }} de {{ $estudiantesPaginados->total() }} estudiantes
                    </small>
                    <div>
                        {{ $estudiantesPaginados->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Script SweetAlert para Notificaciones --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notificacion', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    title: info.titulo || (info.tipo === 'error' ? 'Error' : 'Aviso'),
                    text: info.mensaje,
                    icon: info.tipo || 'info',
                    timer: 3500,
                    showConfirmButton: false
                });
            });
        });
    </script>
    @endpush

</div>
