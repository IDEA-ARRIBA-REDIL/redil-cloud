@extends('layouts/layoutMaster')

@section('title', 'Informe de Asistencia de Encargados')

@section('page-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'
    ])
@endsection

@section('content')
<h4 class="mb-4">{{ $informePersonalizado->nombre }}
    <br><small class="text-muted">{{ $informePersonalizado->descripcion }}</small>
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Filtros de Reporte</h5>
            </div>
            <div class="card-body mt-4">
                <form action="{{ route('informes-personalizados.obreros.exportar', $informePersonalizado->id) }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <!-- Grupo ID (Por ahora ingresado manualmente o podríamos hacer un livewire component para búsqueda, pero el campo oculto y un select bastará por ahora) -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="grupo_id">ID del Grupo Raíz (Ministerio)</label>
                            <input type="number" class="form-control" id="grupo_id" name="grupo_id" required placeholder="Ej: 1">
                            <small class="text-muted">Ingresa el ID del grupo padre desde el cual extraer la jerarquía.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="rango">Periodo</label>
                            <select id="rango" name="rango" class="form-select select2">
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
                                <option value="1t" selected>1er trimestre</option>
                                <option value="2t">2do trimestre</option>
                                <option value="3t">3er trimestre</option>
                                <option value="4t">4to trimestre</option>
                                <option value="1s">1er semestre</option>
                                <option value="2s">2do semestre</option>
                                <option value="anio">Todo el año</option>
                                <option value="semana">Por semana</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3" id="div-anio">
                            <label class="form-label" for="anio">Año</label>
                            <input type="number" class="form-control" id="anio" name="anio" value="{{ $anio }}">
                        </div>

                        <div class="col-md-3 mb-3" id="div-semana" style="display: none;">
                            <label class="form-label" for="semana">Seleccione Semana</label>
                            <input type="week" class="form-control" id="semana" name="semana">
                        </div>

                        @if($informePersonalizado->seleccione_dia_corte)
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="selectDiaCorte">Día de corte</label>
                            <select id="selectDiaCorte" name="selectDiaCorte" class="form-select">
                                <option value="">Ninguno</option>
                                <option value="0">Lunes</option>
                                <option value="1">Martes</option>
                                <option value="2">Miércoles</option>
                                <option value="3">Jueves</option>
                                <option value="4">Viernes</option>
                                <option value="5">Sábado</option>
                                <option value="6">Domingo</option>
                            </select>
                        </div>
                        @endif
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="incluir-encargados" name="incluir-encargados" checked>
                                <label class="form-check-label" for="incluir-encargados">Incluir encargados del grupo</label>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="incluir-asistentes" name="incluir-asistentes" checked>
                                <label class="form-check-label" for="incluir-asistentes">Incluir asistentes del grupo</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="selectTipoDeGrupo">Tipos de Grupos a incluir</label>
                            <select id="selectTipoDeGrupo" name="selectTipoDeGrupo[]" class="select2 form-select" multiple required>
                                @foreach($tiposDeGrupos as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($informePersonalizado->clasificaciones && $clasificacionAsistentes->count() > 0)
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="filtro_clasificacion_asistentes">Clasificaciones (Opcional)</label>
                            <select id="filtro_clasificacion_asistentes" name="filtro_clasificacion_asistentes[]" class="select2 form-select" multiple>
                                @foreach($clasificacionAsistentes as $clasificacion)
                                    <option value="{{ $clasificacion->id }}">{{ $clasificacion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Elige los campos que deseas ver reflejado en tu archivo exportado a Excel.</label>
                            <select id="info_principal" name="info_principal[]" class="select2 form-select" multiple required>
                                @foreach($camposInformeExcel as $campo)
                                    <option value="{{ $campo->id }}" selected>{{ $campo->nombre_campo_informe }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-download me-2"></i> Generar Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar Select2
        if ($.fn.select2) {
            $('.select2').select2({
                placeholder: "Seleccione una opción",
                allowClear: true
            });
        }

        // Lógica de periodo
        const rangoSelect = document.getElementById('rango');
        const divAnio = document.getElementById('div-anio');
        const divSemana = document.getElementById('div-semana');

        if (rangoSelect) {
            // Inicializar Select2 en Rango si se usó
            $(rangoSelect).on('change', function() {
                togglePeriodo(this.value);
            });
            // Por si cambia nativamente sin jquery
            rangoSelect.addEventListener('change', function(e) {
                togglePeriodo(e.target.value);
            });

            function togglePeriodo(val) {
                if (val === 'semana') {
                    divSemana.style.display = 'block';
                    divAnio.style.display = 'none';
                } else {
                    divSemana.style.display = 'none';
                    divAnio.style.display = 'block';
                }
            }
        }
    });
</script>
@endsection
