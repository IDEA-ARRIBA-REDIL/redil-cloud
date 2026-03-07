<div>
    {{-- El formulario ahora tiene un ID único y una acción POST válida por defecto --}}
    <form id="reporte-form" action="{{ route('reporteEscuela.exportarResumen') }}" method="POST">
        @csrf
        <div class="row g-3">
            {{-- Fila 1: Periodo y Semana --}}
            <div class="col-12 col-md-6">
                <label for="periodo" class="form-label">1. Selecciona un periodo </label>
                <select id="periodo" class="form-select" wire:model.live="periodoSeleccionado" name="periodoSeleccionado" required>
                    <option value="">Seleccionar...</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6">
                <label for="semana" class="form-label">2. Selecciona una semana </label>
                <select id="semana" class="form-select" wire:model.live="semanaSeleccionada" name="semanaSeleccionada" required @if(empty($semanas)) disabled @endif>
                     <option value="">Seleccionar...</option>
                     @foreach($semanas as $semana)
                        <option value="{{ $semana['valor'] }}">{{ $semana['texto'] }}</option>
                     @endforeach
                </select>
            </div>

            {{-- Fila 2: Sedes y Materia --}}
            <div class="col-12 col-md-12" wire:ignore>
                <label for="select-sedes" class="form-label">3. Selecciona sedes </label>
                  <a href="javascript:;"  id="select-all-sedes" > <span class="fw-medium">Todos </span></a> |
                        <a  href="javascript:;"  id="deselect-all-sedes" ><span class="fw-medium">Ninguna </span></a>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <select id="select-sedes" class="form-select" name="sedesSeleccionadas[]" multiple required></select>
                    </div>

                </div>
            </div>

            {{-- Fila 3: Materias (AHORA MÚLTIPLE) --}}
            <div class="col-12 col-md-12" wire:ignore>
                <label for="select-materias" class="form-label">4. Selecciona materias </label>
                <a href="javascript:;" id="select-all-materias" ><span class="fw-medium"> </span>Todas</a> |
                <a href="javascript:;" id="deselect-all-materias" ><span class="fw-medium"> Ninguna </span> </a>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <select id="select-materias" class="form-select" name="materiasSeleccionadas[]" multiple required></select>
                    </div>
                    <div class="btn-group ms-2" role="group">

                    </div>
                </div>
            </div>
        </div>

        {{-- Fila 3: Botones de Acción --}}
       <div class="mt-4">
            <button type="submit" class="btn btn-outline-secondary rounded-pill action-btn mb-2" data-action="{{ route('reporteEscuela.exportarReporte') }}" @if(!$periodoSeleccionado || empty($sedesSeleccionadas) || empty($materiasSeleccionadas) || !$semanaSeleccionada) disabled @endif>
                Exportar informe detallado
            </button>
            <button type="submit" class="btn btn-outline-secondary rounded-pill action-btn mb-2" data-action="{{ route('reporteEscuela.exportarResumen') }}" @if(!$periodoSeleccionado || empty($sedesSeleccionadas) || empty($materiasSeleccionadas) || !$semanaSeleccionada) disabled @endif>
                 Exportar resumen por sede
            </button>
        </div>
    </form>

    {{-- Script completo para la interactividad --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            // --- LÓGICA PARA SELECT2 DE SEDES (Sin cambios) ---
            const selectSedes = $('#select-sedes');
            const btnSelectAllSedes = $('#select-all-sedes');
            const btnDeselectAllSedes = $('#deselect-all-sedes');
            let availableSedeIds = [];

            selectSedes.select2({ placeholder: 'Selecciona una o varias sedes', allowClear: true, width: '100%' });
            selectSedes.on('change', function (e) { @this.set('sedesSeleccionadas', $(this).val()); });
            Livewire.on('actualizar-sedes-select2', ({ sedes }) => {
                selectSedes.empty();
                availableSedeIds = sedes.map(sede => sede.id);
                const hasSedes = availableSedeIds.length > 0;
                btnSelectAllSedes.prop('disabled', !hasSedes);
                btnDeselectAllSedes.prop('disabled', !hasSedes);
                sedes.forEach(function(sede) { selectSedes.append(new Option(sede.nombre, sede.id)); });
                selectSedes.trigger('change');
            });
            btnSelectAllSedes.on('click', function() { selectSedes.val(availableSedeIds).trigger('change'); });
            btnDeselectAllSedes.on('click', function() { selectSedes.val(null).trigger('change'); });
            btnSelectAllSedes.prop('disabled', true);
            btnDeselectAllSedes.prop('disabled', true);

            // --- NUEVA LÓGICA PARA SELECT2 DE MATERIAS ---
            const selectMaterias = $('#select-materias');
            const btnSelectAllMaterias = $('#select-all-materias');
            const btnDeselectAllMaterias = $('#deselect-all-materias');
            let availableMateriaIds = [];

            selectMaterias.select2({ placeholder: 'Selecciona una o varias materias', allowClear: true, width: '100%' });
            selectMaterias.on('change', function (e) { @this.set('materiasSeleccionadas', $(this).val()); });
            Livewire.on('actualizar-materias-select2', ({ materias }) => {
                selectMaterias.empty();
                availableMateriaIds = materias.map(materia => materia.id);
                const hasMaterias = availableMateriaIds.length > 0;
                btnSelectAllMaterias.prop('disabled', !hasMaterias);
                btnDeselectAllMaterias.prop('disabled', !hasMaterias);
                materias.forEach(function(materia) { selectMaterias.append(new Option(materia.nombre, materia.id)); });
                selectMaterias.trigger('change');
            });
            btnSelectAllMaterias.on('click', function() { selectMaterias.val(availableMateriaIds).trigger('change'); });
            btnDeselectAllMaterias.on('click', function() { selectMaterias.val(null).trigger('change'); });
            btnSelectAllMaterias.prop('disabled', true);
            btnDeselectAllMaterias.prop('disabled', true);

            // --- LÓGICA PARA BOTONES DE ACCIÓN (Sin cambios) ---
            const form = document.getElementById('reporte-form');
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    form.setAttribute('action', this.dataset.action);
                });
            });
        });
    </script>
    @endpush
</div>
