<div class="row g-4">

    {{-- Indicador Visual de Pasos (Wizard) --}}
    <div class="col-12 my-10">
        <div class="d-flex justify-content-around align-items-center">
            <div class="text-center {{ $pasoActual >= 1 ? 'text-primary fw-bold' : 'text-muted' }}">
                <div class="avatar avatar-sm mx-auto mb-1 rounded-circle {{ $pasoActual >= 1 ? 'bg-label-primary text-white' : 'bg-light text-muted' }} d-flex align-items-center justify-content-center">
                    1
                </div>
                <span class="fs-6 d-none d-md-inline">Configuración</span>
            </div>
            <div class="flex-grow-1 border-top mx-3 {{ $pasoActual >= 2 ? 'border-primary' : 'border-secondary' }}" style="border-width: 2px !important;"></div>
            <div class="text-center {{ $pasoActual >= 2 ? 'text-primary fw-bold' : 'text-muted' }}">
                <div class="avatar avatar-sm mx-auto mb-1 rounded-circle {{ $pasoActual >= 2 ? 'bg-label-primary text-white' : 'bg-light text-muted' }} d-flex align-items-center justify-content-center">
                    2
                </div>
                <span class="fs-6 d-none d-md-inline">Diagnóstico</span>
            </div>
            <div class="flex-grow-1 border-top mx-3 {{ $pasoActual >= 3 ? 'border-primary' : 'border-secondary' }}" style="border-width: 2px !important;"></div>
            <div class="text-center {{ $pasoActual >= 3 ? 'text-primary fw-bold' : 'text-muted' }}">
                <div class="avatar avatar-sm mx-auto mb-1 rounded-circle {{ $pasoActual >= 3 ? 'bg-label-primary text-white' : 'bg-light text-muted' }} d-flex align-items-center justify-content-center">
                    3
                </div>
                <span class="fs-6 d-none d-md-inline">Resumen</span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PASO 1: SELECCIÓN DE ESCUELA / MATERIA / ESTADO Y CARGA DE ARCHIVO        --}}
    {{-- ========================================================================= --}}
    @if ($pasoActual === 1)
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-semibold text-black">
                        <i class="ti ti-settings me-1"></i> 1. Parámetros del Lote
                    </h5>
                </div>
                <div class="card-body pt-4">
                    {{-- 1. Selector de Escuela --}}
                    <div class="mb-3">
                        <label class="form-label text-black fw-semibold required">Escuela</label>
                        <select wire:model.live="escuelaSeleccionadaId" class="form-select @error('escuelaSeleccionadaId') is-invalid @enderror">
                            <option value="">-- Selecciona una escuela --</option>
                            @foreach ($escuelas as $escuela)
                                <option value="{{ $escuela->id }}">{{ $escuela->nombre }}</option>
                            @endforeach
                        </select>
                        @error('escuelaSeleccionadaId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 2. Selector de Materia o Nivel --}}
                    <div class="mb-3">
                        <label class="form-label text-black fw-semibold required">
                            {{ $modo === 'materias' ? 'Materia a homologar' : 'Nivel a homologar' }}
                        </label>
                        <select wire:model.live="itemSeleccionadoId" class="form-select @error('itemSeleccionadoId') is-invalid @enderror" {{ empty($escuelaSeleccionadaId) ? 'disabled' : '' }}>
                            <option value="">-- Selecciona {{ $modo === 'materias' ? 'una materia' : 'un nivel' }} --</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                        @error('itemSeleccionadoId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 3. Selector de Estado de la Carga --}}
                    <div class="mb-4">
                        <label class="form-label text-black fw-semibold required">Estado en que se guardará la carga</label>
                        <select wire:model.live="estadoHomologacionLote" class="form-select @error('estadoHomologacionLote') is-invalid @enderror" {{ empty($itemSeleccionadoId) ? 'disabled' : '' }}>
                            <option value="">-- Selecciona el estado global del lote --</option>
                            <option value="1">Aprobado</option>
                            <option value="2">En proceso</option>
                            <option value="0">Reprobado</option>
                        </select>
                        @error('estadoHomologacionLote')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        $tieneTareas = $itemModel && $itemModel->tareasCulminadas && $itemModel->tareasCulminadas->isNotEmpty();
                        $tienePasos = $itemModel && $itemModel->pasosCrecimiento && $itemModel->pasosCrecimiento->isNotEmpty();
                        $tieneTipoUsuario = $itemModel && !empty($itemModel->tipo_usuario_objetivo_id);
                        $tieneAutomatizaciones = $tieneTareas || $tienePasos || $tieneTipoUsuario;
                    @endphp

                    {{-- ============================================================= --}}
                    {{-- CASO A: ESTADO = APROBADO (Mostrar automatizaciones otorgadas)  --}}
                    {{-- ============================================================= --}}
                    @if ((int)$estadoHomologacionLote === 1 && $tieneAutomatizaciones)
                        <div class="border border-success rounded-3 p-3 mb-3" role="alert">
                            <h6 class="fw-bold text-black mb-2 d-flex align-items-center">
                                <i class="ti ti-wand fs-4 me-2 text-success"></i> Automatizaciones al aprobar
                            </h6>
                            <p class="small text-black mb-3">
                                Al procesar como <strong>Aprobado</strong>, se culminarán automáticamente las siguientes tareas, pasos y roles para todos los estudiantes del lote:
                            </p>

                            {{-- Tareas culminadas --}}
                            @if ($tieneTareas)
                                <div class="mb-3">
                                    <span class="fw-semibold text-black d-block mb-1">Tareas de consolidación:</span>
                                    <ul class="d-flex flex-wrap gap-2">
                                        @foreach ($itemModel->tareasCulminadas as $tc)
                                            @php
                                                $nombreTarea = $tc->tareaConsolidacion?->nombre ?? 'Tarea #'.$tc->tarea_consolidacion_id;
                                                $nombreEstado = $tc->estadoTarea?->nombre ?? 'Culminada';
                                            @endphp
                                            <li class="small text-black">
                                                {{ $nombreTarea }} ({{ $nombreEstado }})
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Pasos de crecimiento --}}
                            @if ($tienePasos)
                                <div class="mb-3">
                                    <span class="fw-semibold text-black d-block mb-1">Pasos de crecimiento:</span>
                                    <ul class="d-flex flex-wrap gap-2">
                                        @foreach ($itemModel->pasosCrecimiento as $pc)
                                            @php
                                                $estadoPasoId = $pc->pivot->estado_paso_crecimiento_usuario_id;
                                                $nombreEstadoPaso = isset($estadosPasosDisponibles[$estadoPasoId]) ? $estadosPasosDisponibles[$estadoPasoId]->nombre : 'Culminado';
                                            @endphp
                                            <li class="small text-black">
                                               {{ $pc->nombre }} ({{ $nombreEstadoPaso }})
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Tipo de usuario  --}}
                            @if ($itemModel->tipoUsuarioObjetivo)

                                 <div class="mb-3">
                                    <span class="fw-semibold text-black d-block mb-1">Tipo de usuario:</span>
                                    <ul class="d-flex flex-wrap gap-2">
                                        <li class="small text-black">
                                           {{ $itemModel->tipoUsuarioObjetivo->nombre }}
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ================================================================= --}}
                    {{-- CASO B: ESTADO = EN PROCESO / REPROBADO (Habilitar ajustes manuales) --}}
                    {{-- ================================================================= --}}
                    @if (in_array((string)$estadoHomologacionLote, ['0', '2']) && $tieneAutomatizaciones)
                        <div class="card border border-warning rounded-3 p-3 mb-3 ">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-black mb-0 d-flex align-items-center">
                                    <i class="ti ti-adjustments-horizontal fs-4 me-2 text-warning"></i> Ajustar avance de los estudiantes
                                </h6>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="switchAjustesAvance" wire:model.live="aplicarAjustesAvance" style="cursor: pointer; width: 2.25rem; height: 1.2rem;">
                                </div>
                            </div>
                            <p class="small text-black mb-3">
                                Activa el interruptor para definir a qué estado asignar o revertir las tareas, pasos y roles en este lote.
                            </p>

                            @if ($aplicarAjustesAvance)
                                {{-- Ajustes de Tareas --}}
                                @if ($tieneTareas)
                                    <div class="mb-3">
                                        <label class="form-label text-black fw-bold small mb-2">Tareas de consolidación:</label>
                                        <div class="row g-2">
                                            @foreach ($itemModel->tareasCulminadas as $tc)
                                                @php
                                                    $tId = $tc->tarea_consolidacion_id;
                                                    $tNombre = $tc->tareaConsolidacion?->nombre ?? 'Tarea #'.$tId;
                                                @endphp
                                                <div class="col-12">
                                                    <div class="p-2 border rounded bg-white">
                                                        <span class="small fw-semibold text-black d-block mb-1">{{ $tNombre }}</span>
                                                        <select wire:model="ajustesTareas.{{ $tId }}" class="form-select form-select-sm fw-medium rounded-pill border-1" >
                                                            <option value="" class="bg-white text-black">Sin asignar</option>
                                                            @foreach ($estadosTareasDisponibles as $et)
                                                                <option value="{{ $et->id }}" class="bg-white text-black">{{ $et->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Ajustes de Pasos de Crecimiento --}}
                                @if ($tienePasos)
                                    <div class="mb-3">
                                        <label class="form-label text-black fw-bold small mb-2">Pasos de crecimiento:</label>
                                        <div class="row g-2">
                                            @foreach ($itemModel->pasosCrecimiento as $pc)
                                                <div class="col-12">
                                                    <div class="p-2 border rounded bg-white">
                                                        <span class="small fw-semibold text-black d-block mb-1">{{ $pc->nombre }}</span>
                                                        <select wire:model="ajustesPasos.{{ $pc->id }}" class="form-select form-select-sm fw-medium rounded-pill border-1">
                                                            <option value="" class="bg-white text-black">Sin asignar</option>
                                                            @foreach ($estadosPasosDisponibles as $ep)
                                                                <option value="{{ $ep->id }}" class="bg-white text-black">{{ $ep->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Ajuste de Tipo de Usuario / Rol --}}
                                @if ($itemModel->tipoUsuarioObjetivo)
                                    <div class="mb-2">
                                        <label class="form-label text-black fw-bold small mb-1">Tipo de usuario:</label>
                                        <select wire:model.live="ajusteTipoUsuarioId" class="form-select form-select-sm">
                                            <option value="">-- Sin cambio de tipo de usuario --</option>
                                            @foreach ($tiposUsuariosDisponibles as $tu)
                                                <option value="{{ $tu->id }}">{{ $tu->nombre }}</option>
                                            @endforeach
                                        </select>

                                        @if (!empty($ajusteTipoUsuarioId))
                                            <div class="mt-2 p-2 bg-white rounded border">
                                                <div class="form-check form-switch d-flex align-items-center justify-content-between mb-0">
                                                    <div>
                                                        <label class="form-check-label text-black fw-semibold small mb-0 cursor-pointer" for="switchForzarTipoUsuario">
                                                            ¿Forzar asignación de tipo de usuario?
                                                        </label>
                                                        <div class="mt-1" style="font-size: 0.75rem;">
                                                            @if ($forzarTipoUsuario)
                                                                <span class="text-black ">
                                                                    <i class="ti ti-alert-triangle text-danger me-1"></i><strong>Forzado:</strong> Se asignará a todos los estudiantes del lote, incluso si tienen un rol de mayor jerarquía.
                                                                </span>
                                                            @else
                                                                <span class="text-black ">
                                                                    <i class="ti ti-shield-check text-success me-1"></i><strong>Respeta pesos:</strong> Solo se aplicará si el nuevo rol es igual o superior al que ya tiene el estudiante.
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <input class="form-check-input ms-2" type="checkbox" id="switchForzarTipoUsuario" wire:model.live="forzarTipoUsuario" style="cursor: pointer; width: 2.25rem; height: 1.2rem;">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-light border-0 py-2 px-3 text-black small mb-0 rounded-pill text-center">
                                    <i class="ti ti-circle-off me-1"></i> Se omitirá el ajuste de tareas, pasos y roles en este lote.
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Nota de Sede Automática --}}
                    <div class="p-3 d-flex mb-7" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
                        <i class="ti ti-building-community text-secondary me-2"></i>
                        <div class="small">
                            <strong>Nota de sede automática:</strong> <br> Se tomará la sede individual registrada en el perfil de cada estudiante.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-black">
                        <i class="ti ti-file-spreadsheet me-1"></i> 2. Archivo Excel
                    </h5>
                    <button wire:click="descargarPlantilla" type="button" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="ti ti-download me-1"></i> Descargar plantilla 
                    </button>
                </div>
                <div class="card-body pt-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="alert alert-light border mb-3">
                            <div class="fw-semibold text-black mb-1">Estructura requerida del archivo Excel:</div>
                            <ul class="small text-black mb-0 ps-3">
                                <li><code>identificacion_alumno</code>: Documento de identidad del estudiante (búsqueda prioritaria).</li>
                                <li><code>email</code>: Correo electrónico del estudiante (búsqueda secundaria de respaldo).</li>
                                <li><code>nota_final</code>: {{ (int)$estadoHomologacionLote === 1 ? 'Obligatoria (0.00 a 100.00). Acepta punto o coma decimal.' : 'Opcional / No requerida para este estado.' }}</li>
                                <li><code>observacion</code>: Opcional / Justificación del registro.</li>
                            </ul>
                        </div>

                        {{-- Zona de carga de archivo --}}
                        <div class="mb-4 p-4 border border-2 border-dashed rounded-3 text-center bg-light">
                            <i class="ti ti-cloud-upload fs-1 text-primary mb-2 d-block"></i>
                            <label for="archivoExcel" class="form-label text-dark fw-bold mb-1">Selecciona o arrastra tu archivo Excel (.xlsx, .xls, .csv)</label>
                            <input type="file" wire:model="archivoExcel" id="archivoExcel" class="form-control d-none" accept=".xlsx,.xls,.csv" />
                            <div class="mt-2">
                                <label for="archivoExcel" class="btn btn-outline-primary rounded-pill btn-sm px-3 cursor-pointer">
                                    <span wire:loading.remove wire:target="archivoExcel">Examinar archivo</span>
                                    <span wire:loading wire:target="archivoExcel"><i class="ti ti-loader rotate me-1"></i> Cargando archivo...</span>
                                </label>
                            </div>
                            @if ($archivoExcel)
                                <div class="mt-3 text-black fw-semibold">
                                    <i class="ti ti-check text-success me-1"></i> Archivo seleccionado: {{ $archivoExcel->getClientOriginalName() }}
                                </div>
                            @endif
                            @error('archivoExcel')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Botón de Acción Principal --}}
                    <div class="d-flex justify-content-end pt-3 border-top">
                        <button wire:click="analizarArchivoReal" type="button" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled" {{ !$archivoExcel || $estadoHomologacionLote === null || $estadoHomologacionLote === '' ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="analizarArchivoReal">
                                <i class="ti ti-list-search me-1"></i> Analizar 
                            </span>
                            <span wire:loading wire:target="analizarArchivoReal">
                                <i class="ti ti-loader rotate me-1"></i> Leyendo y validando archivo...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- PASO 2: PREVISUALIZACIÓN Y TABLA DE DIAGNÓSTICO (DRY-RUN)                 --}}
    {{-- ========================================================================= --}}
    @if ($pasoActual === 2)
        {{-- Tarjetas de Métricas Diagnósticas (Estilo Indicadores de Usuarios) --}}
        <div class="col-12">
            <div class="row g-2">
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md rounded-circle bg-info  d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-layout-grid fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Total filas</p>
                                <h5 class="mb-0 fw-bold text-dark">{{ $metricas['total'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md rounded-circle bg-success  d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-circle-check fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Válidos (Nuevos)</p>
                                <h5 class="mb-0 fw-bold text-dark">{{ $metricas['validos'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md rounded-circle bg-warning d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-alert-triangle fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Actualizaciones</p>
                                <h5 class="mb-0 fw-bold text-dark">{{ $metricas['advertencias'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border rounded-3 shadow-sm h-100">
                        <div class="card-body d-flex flex-row p-3 align-items-center">
                            <div class="card-icon me-2">
                                <div class="avatar avatar-md rounded-circle bg-danger d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px;">
                                    <i class="ti ti-arrow-down fs-3"></i>
                                </div>
                            </div>
                            <div class="card-title mb-0">
                                <p class="text-black mb-0" style="font-size: .8125rem">Con errores</p>
                                <h5 class="mb-0 fw-bold text-dark">{{ $metricas['errores'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de Detalle Diagnóstico Fila por Fila --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="card-title mb-0 fw-semibold text-black">
                            <i class="ti ti-list-check me-1"></i> Previsualización y diagnóstico del lote
                        </h5>
                        <small class="text-black">
                            {{ $modo === 'materias' ? 'Materia' : 'Nivel' }}: <strong>{{ $itemModel?->nombre }}</strong> |
                            Estado global:
                            <span class="badge text-white {{ (int)$estadoHomologacionLote === 1 ? 'bg-success' : ((int)$estadoHomologacionLote === 2 ? 'bg-warning' : 'bg-danger') }} rounded-pill">
                                {{ (int)$estadoHomologacionLote === 1 ? 'Aprobado' : ((int)$estadoHomologacionLote === 2 ? 'En proceso' : 'Reprobado') }}
                            </span>
                        </small>
                    </div>
                    @if ($metricas['errores'] > 0)
                        <button wire:click="descargarReporteErrores" type="button" class="btn btn-outline-danger btn-sm rounded-pill">
                            <i class="ti ti-download me-1"></i> Descargar filas con error ({{ $metricas['errores'] }})
                        </button>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">Fila</th>
                                <th>Identificación</th>
                                <th>Sede</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Nota</th>
                                <th>Observación</th>
                                <th>Diagnóstico</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filasDiagnostico as $f)
                                <tr class="{{ $f['tipo_diagnostico'] === 'error' ? 'table-danger bg-opacity-10' : ($f['tipo_diagnostico'] === 'advertencia' ? 'table-warning bg-opacity-10' : '') }}">
                                    <td class="text-center fw-bold text-black">{{ $f['fila_numero'] }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $f['usuario_nombre'] }}</div>
                                        <small class="text-black">
                                            Doc: {{ $f['identificacion'] ?: 'N/A' }} | Email: {{ $f['email'] ?: 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                       {{ $f['sede_nombre'] }}
                                    </td>
                                    <td class="text-center">
                                        @if ($f['estado_id'] === 1)
                                            <span class="badge  text-white bg-success rounded-pill">Aprobado</span>
                                        @elseif($f['estado_id'] === 2)
                                            <span class="badge text-white bg-warning rounded-pill">En proceso</span>
                                        @elseif($f['estado_id'] === 0)
                                            <span class="badge text-white bg-danger rounded-pill">Reprobado</span>
                                        @else
                                            <span class="badge text-white bg-secondary rounded-pill">{{ $f['estado_texto'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ $f['nota_final'] }}</td>
                                    <td>
                                        <small class="text-black d-inline-block text-truncate" style="max-width: 200px;" title="{{ $f['observacion'] }}">
                                            {{ $f['observacion'] }}
                                        </small>
                                    </td>
                                    <td>
                                        @if ($f['tipo_diagnostico'] === 'valido')
                                            <span class="badge bg-label-success rounded-pill">
                                                <i class="ti ti-check me-1"></i> Válido
                                            </span>
                                        @elseif($f['tipo_diagnostico'] === 'advertencia')
                                            <span class="badge bg-label-warning rounded-pill" title="{{ $f['mensaje_diagnostico'] }}">
                                                <i class="ti ti-alert-triangle me-1"></i> Actualizará
                                            </span>
                                            <div class="small text-black mt-1" style="font-size: 0.75rem;">{{ $f['mensaje_diagnostico'] }}</div>
                                        @else
                                            <span class="badge bg-label-danger rounded-pill" title="{{ $f['mensaje_diagnostico'] }}">
                                                <i class="ti ti-circle-x me-1"></i> Error
                                            </span>
                                            <div class="small text-danger mt-1" style="font-size: 0.75rem;">{{ $f['mensaje_diagnostico'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>                
            </div>
        </div>

        <div class="col-12 py-3 d-flex justify-content-between align-items-center">
            <button wire:click="reiniciar" type="button" class="btn btn-outline-secondary rounded-pill">
                <i class="ti ti-arrow-left me-1"></i> Volver a configuración
            </button>
            <button wire:click="confirmarEjecucion" type="button" class="btn btn-primary rounded-pill px-4" {{ ($metricas['validos'] + $metricas['advertencias']) === 0 ? 'disabled' : '' }}>
                <i class="ti ti-device-floppy me-1"></i> Confirmar
            </button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- PASO 3: RESUMEN DE RESULTADOS TRAS EJECUCIÓN                              --}}
    {{-- ========================================================================= --}}
    @if ($pasoActual === 3)
        <div class="col-12 mx-auto">
            <div class="card border-0 shadow text-center p-4">
                <div class="card-body">
                    <div class="avatar avatar-xl bg-label-success mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="ti ti-circle-check fs-1 text-white"></i>
                    </div>
                    <h3 class="fw-bold text-black mb-1">¡Cargue masivo completado con éxito!</h3>
                    <p class="text-black mb-10">
                        Se han procesado las homologaciones en estado <strong>{{ $resumenEjecucion['estado_nombre'] ?? '' }}</strong> para <strong>{{ $resumenEjecucion['tipo'] ?? 'Materia' }}: {{ $resumenEjecucion['item_nombre'] ?? '' }}</strong>.
                    </p>

                    <div class="row g-3 mb-4 text-start justify-content-center">
                        <div class="col-12 col-sm-6">
                            <div class="card border rounded-3 shadow-sm h-100">
                                <div class="card-body d-flex flex-row p-3 align-items-center">
                                    <div class="card-icon me-3">
                                        <div class="avatar avatar-md rounded-circle bg-success d-flex align-items-center justify-content-center text-white" style="width: 46px; height: 46px;">
                                            <i class="ti ti-circle-plus fs-3"></i>
                                        </div>
                                    </div>
                                    <div class="card-title mb-0">
                                        <p class="text-black mb-0" style="font-size: .8125rem">Nuevos registros</p>
                                        <h5 class="mb-0 fw-bold text-dark">{{ $resumenEjecucion['nuevos'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="card border rounded-3 shadow-sm h-100">
                                <div class="card-body d-flex flex-row p-3 align-items-center">
                                    <div class="card-icon me-3">
                                        <div class="avatar avatar-md rounded-circle bg-warning d-flex align-items-center justify-content-center text-white" style="width: 46px; height: 46px;">
                                            <i class="ti ti-refresh fs-3"></i>
                                        </div>
                                    </div>
                                    <div class="card-title mb-0">
                                        <p class="text-black mb-0" style="font-size: .8125rem">Actualizados</p>
                                        <h5 class="mb-0 fw-bold text-dark">{{ $resumenEjecucion['actualizados'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque para Descargar Errores si existieron filas omitidas --}}
                    @if (!empty($filasConError) || (!empty($resumenEjecucion['omitidos_error']) && $resumenEjecucion['omitidos_error'] > 0))
                        <div class="border-1 border border-warning rounded-3 p-3 mb-4 text-start" role="alert">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">
                                        <i class="ti ti-alert-triangle text-warning me-1"></i> Se omitieron {{ $resumenEjecucion['omitidos_error'] ?? count($filasConError) }} fila(s) con errores
                                    </h6>
                                    <span class="small text-black">Descarga el archivo Excel con el detalle del error para corregirlos y volver a cargarlos.</span>
                                </div>
                                <button wire:click="descargarReporteErrores" type="button" class="btn btn-warning rounded-pill btn-sm fw-medium shadow-sm">
                                    <i class="ti ti-download me-1"></i> Descargar reporte de errores (.xlsx)
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-center gap-3 mt-10">
                        <button wire:click="reiniciar" type="button" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="ti ti-reload me-1"></i> Realizar otro cargue masivo
                        </button>
                        <a href="{{ route('escuelas.homologaciones') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="ti ti-list me-1"></i> Ir a gestión de homologaciones
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Script para SweetAlert2 --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {

            Livewire.on('confirmar-cargue-masivo', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                let mensajeExtra = '';
                if (info.totalErrores > 0) {
                    mensajeExtra = `<br><span class="text-danger fw-semibold">Nota: Se omitirán ${info.totalErrores} filas con errores.</span>`;
                }

                Swal.fire({
                    title: '¿Confirmar cargue masivo?',
                    html: `Se procesarán <strong>${info.totalProcesables} registros</strong> en estado <strong>${info.estadoNombre}</strong> para la ${info.tipo}: <strong>${info.itemNombre}</strong>.${mensajeExtra}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007788',
                    cancelButtonColor: '#8592a3',
                    confirmButtonText: 'Sí, procesar ahora',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('ejecutarProcesamiento');
                    }
                });
            });

            Livewire.on('notificacion', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    title: info.titulo || (info.tipo === 'error' ? 'Error' : 'Aviso'),
                    text: info.mensaje,
                    icon: info.tipo || 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            });

        });
    </script>
    @endpush

</div>
