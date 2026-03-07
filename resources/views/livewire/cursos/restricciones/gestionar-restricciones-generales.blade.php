<div>
    <form wire:submit.prevent="guardar">
        <div class="row">

            {{-- Excluyente --}}
            <div class="mb-3 col-md-6">
                <div class="small fw-medium mb-1">¿Es excluyente con otros cursos?</div>
                <label class="switch switch-lg">
                    <input wire:model="excluyente" type="checkbox" class="switch-input" />
                    <span class="switch-toggle-slider">
                        <span class="switch-on"></span>
                        <span class="switch-off"></span>
                    </span>
                </label>
            </div>

            {{-- Género --}}
            <div class="col-12 mb-3">
                <label class="form-label d-block mb-2 me-2">Género</label>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="custom-radio-card w-100 border rounded p-4 cursor-pointer" for="genero_1_c">
                            <span class="fw-medium text-black">Masculino</span>
                            <input class="form-check-input mt-0" type="radio" wire:model="genero" id="genero_1_c" value="1">
                        </label>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="custom-radio-card w-100 border rounded p-4 cursor-pointer" for="genero_2_c">
                            <span class="fw-medium text-black">Femenino</span>
                            <input class="form-check-input mt-0" type="radio" wire:model="genero" id="genero_2_c" value="2">
                        </label>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="custom-radio-card w-100 border rounded p-4 cursor-pointer" for="genero_3_c">
                            <span class="fw-medium text-black">Ambos</span>
                            <input class="form-check-input mt-0" type="radio" wire:model="genero" id="genero_3_c" value="3">
                        </label>
                    </div>
                </div>
            </div>

            {{-- Vinculación Grupo --}}
            <div class="col-md-6 mb-3" wire:ignore>
                <label class="form-label">Definir vinculación a grupo</label>
                <select id="vinculacion_grupo" class="select2 form-select">
                    <option value="1">Pertenece a grupo</option>
                    <option value="2">No pertenece</option>
                    <option value="3">Ambos</option>
                </select>
            </div>

            {{-- Actividad en Grupo --}}
            <div class="col-md-6 mb-3" wire:ignore>
                <label class="form-label">Definir actividad en grupo</label>
                <select id="actividad_grupo" class="select2 form-select">
                    <option value="1">Activos</option>
                    <option value="2">Inactivos</option>
                    <option value="3">Ambos</option>
                </select>
            </div>

            {{-- Sedes --}}
            <div class="col-12 mb-3" wire:ignore>
                <label class="form-label">Sedes habilitadas</label>
                <select id="sedes" class="select2 form-select" multiple>
                    @foreach ($sedes as $sede)
                        <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Rangos Edad --}}
            <div class="col-12 mb-3" wire:ignore>
                <label class="form-label">Definir rangos de edad</label>
                <select id="rangos_edad" class="select2 form-select" multiple>
                    @foreach ($rangosEdad as $rango)
                        <option value="{{ $rango->id }}">{{ $rango->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Estados Civiles --}}
            <div class="col-12 mb-3" wire:ignore>
                <label class="form-label">Definir estados civiles</label>
                <select id="estados_civiles" class="select2 form-select" multiple>
                    @foreach ($estadosCiviles as $estado)
                        <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>



            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-primary">Guardar Restricciones Generales</button>
            </div>

        </div>
    </form>
</div>

@script
<script>
    $(document).ready(function() {
        // Vinculación Grupo
        $('#vinculacion_grupo').val(@this.vinculacion_grupo).trigger('change');
        $('#vinculacion_grupo').on('change', function (e) {
            @this.set('vinculacion_grupo', $(this).val());
        });

        // Actividad Grupo
        $('#actividad_grupo').val(@this.actividad_grupo).trigger('change');
        $('#actividad_grupo').on('change', function (e) {
            @this.set('actividad_grupo', $(this).val());
        });

        // Sedes
        $('#sedes').val(@json($sedesSeleccionadas)).trigger('change');
        $('#sedes').on('change', function (e) {
            @this.set('sedesSeleccionadas', $(this).val());
        });

        // Rangos Edad
        $('#rangos_edad').val(@json($rangosEdadSeleccionados)).trigger('change');
        $('#rangos_edad').on('change', function (e) {
            @this.set('rangosEdadSeleccionados', $(this).val());
        });

        // Estados Civiles
        $('#estados_civiles').val(@json($estadosCivilesSeleccionados)).trigger('change');
        $('#estados_civiles').on('change', function (e) {
            @this.set('estadosCivilesSeleccionados', $(this).val());
        });

        // Tipo Servicios
        $('#tipo_servicios').val(@json($tipoServiciosSeleccionados)).trigger('change');
        $('#tipo_servicios').on('change', function (e) {
            @this.set('tipoServiciosSeleccionados', $(this).val());
        });
    });
</script>
@endscript
