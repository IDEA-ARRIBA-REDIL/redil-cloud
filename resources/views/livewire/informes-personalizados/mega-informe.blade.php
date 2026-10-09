<div wire:poll.5s>
    <!-- Encabezado del Informe -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">
                        <i class="ti ti-chart-bar me-2 text-primary"></i>{{ $informe->nombre }}
                    </h4>
                    <p class="text-muted mb-0">{{ $informe->descripcion ?? 'Generación dinámica de métricas ministeriales avanzadas en segundo plano.' }}</p>
                </div>
                <a href="{{ route('informes-personalizados.index') }}" class="btn btn-label-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Volver a Informes
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 1. Formulario de Solicitud de Megainforme -->
        <div class="col-lg-5 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-filter me-2"></i>Filtros de Solicitud
                    </h5>
                </div>
                <div class="card-body mt-4">
                    <form wire:submit.prevent="solicitarInforme">
                        <!-- Selector de Grupo Raíz -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Grupo Raíz (Ministerio) <span class="text-danger">*</span></label>
                            <div wire:ignore>
                                @livewire('grupos.grupos-para-busqueda', [
                                    'conDadosDeBaja' => 'no',
                                    'multiple' => false,
                                    'id' => 'buscador_grupos_mega',
                                    'class' => 'buscador_grupos_mega'
                                ])
                            </div>
                            @error('grupo_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted d-block mt-1">Busca y selecciona el grupo desde el cual se extraerá la red ministerial.</small>
                        </div>

                        <!-- Tipo de Grupo para Agrupar -->
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="agrupar_por_tipo_grupo_id">Agrupar por Tipo de Grupo <span class="text-danger">*</span></label>
                            <select id="agrupar_por_tipo_grupo_id" wire:model="agrupar_por_tipo_grupo_id" class="form-select @error('agrupar_por_tipo_grupo_id') is-invalid @enderror">
                                <option value="">Selecciona un tipo de grupo</option>
                                @foreach($tiposDeGrupos as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('agrupar_por_tipo_grupo_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Periodo y Año -->
                        <div class="row mb-3">
                            <div class="col-md-7 mb-2">
                                <label class="form-label fw-bold" for="periodo">Periodo <span class="text-danger">*</span></label>
                                <select id="periodo" wire:model.live="periodo" class="form-select">
                                    <optgroup label="Trimestres">
                                        <option value="1t">1er Trimestre (Ene - Mar)</option>
                                        <option value="2t">2do Trimestre (Abr - Jun)</option>
                                        <option value="3t">3er Trimestre (Jul - Sep)</option>
                                        <option value="4t">4to Trimestre (Oct - Dic)</option>
                                    </optgroup>
                                    <optgroup label="Semestres">
                                        <option value="1s">1er Semestre (Ene - Jun)</option>
                                        <option value="2s">2do Semestre (Jul - Dic)</option>
                                    </optgroup>
                                    <optgroup label="Meses">
                                        <option value="1m">Enero</option>
                                        <option value="2m">Febrero</option>
                                        <option value="3m">Marzo</option>
                                        <option value="4m">Abril</option>
                                        <option value="5m">Mayo</option>
                                        <option value="6m">Junio</option>
                                        <option value="7m">Julio</option>
                                        <option value="8m">Agosto</option>
                                        <option value="9m">Septiembre</option>
                                        <option value="10m">Octubre</option>
                                        <option value="11m">Noviembre</option>
                                        <option value="12m">Diciembre</option>
                                    </optgroup>
                                    <optgroup label="Otros">
                                        <option value="anio">Todo el año</option>
                                        <option value="semana">Por semana</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-5 mb-2">
                                <label class="form-label fw-bold" for="year">Año <span class="text-danger">*</span></label>
                                <input type="number" id="year" wire:model="year" class="form-control @error('year') is-invalid @enderror" min="2000" max="{{ date('Y') + 1 }}">
                                @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Selector de Semana si periodo == 'semana' -->
                        @if($periodo === 'semana')
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="semana">Seleccione Semana <span class="text-danger">*</span></label>
                            <input type="week" id="semana" wire:model="semana" class="form-control @error('semana') is-invalid @enderror">
                            @error('semana') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif

                        <!-- Correo de Recepción -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" for="email">Enviar copia al Correo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                <input type="email" id="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="correo@ejemplo.com">
                            </div>
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted d-block mt-1">El archivo Excel generado se enviará a este correo electrónico.</small>
                        </div>

                        <!-- Botón de Envío con Loading State -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="ti ti-send me-1"></i> Solicitar Megainforme en Cola
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Encolando solicitud...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. Historial de Últimos Informes Solicitados -->
        <div class="col-lg-7 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-history me-2"></i>Mis Últimas Solicitudes
                    </h5>
                    <span class="badge bg-label-primary rounded-pill">
                        <i class="ti ti-refresh me-1"></i> Actualización automática
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($ultimosInformes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha / Hora</th>
                                        <th>Filtros</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosInformes as $itemCola)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $itemCola->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $itemCola->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold">{{ $itemCola->grupo->nombre ?? 'N/A' }}</div>
                                                <div class="badge bg-label-secondary mt-1">
                                                    {{ strtoupper($itemCola->periodo) }} {{ $itemCola->year }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($itemCola->isPending())
                                                    <span class="badge bg-label-warning">
                                                        <i class="ti ti-clock me-1"></i> En cola
                                                    </span>
                                                @elseif($itemCola->isProcessing())
                                                    <span class="badge bg-label-info">
                                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span> Procesando...
                                                    </span>
                                                @elseif($itemCola->isCompleted())
                                                    <span class="badge bg-label-success">
                                                        <i class="ti ti-circle-check me-1"></i> Listo ({{ $itemCola->tiempo_ejecucion_segundos ?? 0 }}s)
                                                    </span>
                                                @elseif($itemCola->isFailed())
                                                    <span class="badge bg-label-danger" title="{{ $itemCola->error_message }}">
                                                        <i class="ti ti-alert-circle me-1"></i> Falló
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($itemCola->isCompleted())
                                                    <a href="{{ route('informes-personalizados.en-cola.descargar', $itemCola->id) }}" class="btn btn-sm btn-success rounded-pill">
                                                        <i class="ti ti-download me-1"></i> Descargar
                                                    </a>
                                                @elseif($itemCola->isFailed())
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="Swal.fire({title: 'Detalle del Error', text: '{{ addslashes($itemCola->error_message) }}', icon: 'error'})">
                                                        <i class="ti ti-info-circle"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-file-analytics display-4 text-muted mb-3"></i>
                            <h6 class="text-muted">Aún no has solicitado ningún informe para esta plantilla.</h6>
                            <p class="text-muted small">Selecciona los filtros en el panel izquierdo y haz clic en "Solicitar Megainforme en Cola".</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
