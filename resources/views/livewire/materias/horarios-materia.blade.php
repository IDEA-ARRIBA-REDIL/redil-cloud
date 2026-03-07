<div>
    <div class="card mt-4">

        <div class="card-header justify-content-between  ">
            <h5 class="mb-0">Horarios de la materia</h5>
            <div class="justify-content-between">
                <button wire:click="abrirFormularioNuevo" style="width:196px;padding:10px 10px;"
                    class="btn btn-primary rounded-pill me-4 mt-5" type="button" aria-controls="offcanvasRight">
                    <i class="ti ti-plus me-1"></i> Agregar horario
                </button>

                @if (count($horarios) > 0)
                    <button type="button" style="width:126px;padding:10px 10px;"
                        {{--  AÑADIR wire:click aquí  --}}
                        wire:click="exportarExcel"
                        class="btn btn-meddium btn-outline-secondary fw-semibold float-end me-4  mt-5"
                        id="btnXls">
                        Excel<i class="ti ti-file-type-xls ms-1"></i> {{-- Añadí un margen al icono --}}
                    </button> {{-- Asegúrate de cerrar bien el tag button --}}
                 @endif


                @if ($this->hayFiltros)
                    <button type="button" style="width:206px;padding:10px 10px;"
                        class="btn btn-meddium btn-outline-danger fw-semibold float-end me-4 mt-5"
                        wire:click="resetFiltros">
                        Eliminar Filtros <i class="ti ti-filter-off"></i>
                    </button>
                @else
                    <button type="button" style="width:126px;padding:10px 10px;"
                        class="btn btn-meddium btn-outline-secondary fw-semibold me-4 float-end mt-5 " id="btnFiltro"
                        data-bs-toggle="offcanvas" data-bs-target="#addEventSidebarFiltros"
                        aria-controls="addEventSidebar">
                        Filtros <i class="ti ti-filter"></i>
                    </button>
                @endif



            </div>

        </div>
        <div class="row mx-3">
            @foreach ($horarios as $horario)
                <div class="col-lg-4 col-md-6 col-12 p-4">
                    <div class="card horario-card shadow rounded position-relative">
                        <!-- Menú de opciones (botón flotante) -->
                        <div   class="position-absolute top-0 end-0 mt-5 me-3">
                            <div style="border-radius: 20px !important;" class="dropdown zindex-2 border rounded  p-1">
                                <button class="btn btn-sm  p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button class="dropdown-item"
                                            wire:click="abrirFormularioEditar({{ $horario->id }})">
                                            Editar
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="toggleEstado({{ $horario->id }})">

                                            {{ $horario->activo ? 'Inactivar' : 'Activar' }}
                                        </button>
                                    </li>

                                    <li>
                                        <button class="dropdown-item"
                                            wire:click="confirmarEliminar({{ $horario->id }})">
                                            Eliminar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-body text-center">


                            <span class="pb-1 d-block text-start">
                                <span class="fw-bold text-black">{{ $horario->dia_semana }}</span><br>
                                <span class="text-muted">{{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</span>
                                <br>
                                <!-- Badge de estado -->
                                <span
                                    class="badge {{ $horario->activo ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill mt-2">
                                    {{ $horario->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </span>

                            <div class="mt-3">

                                <div class="d-flex flex-row justify-content-between mb-2">
                                    <div class="d-flex flex-row">

                                        <i class="ti ti-home-2"></i>
                                        @php
                                            $tipoAula = \App\Models\TipoAula::find($horario->aula->tipo_aula_id);
                                        @endphp
                                        <small class="text-black text-start"><b>Tipo Aula:
                                            </b>{{ $tipoAula->nombre }}</small>
                                    </div>
                                    <div class="d-flex flex-row">
                                        <i class="ti ti-building me-2"></i>
                                        <small class="text-black text-start"><b>Sede:
                                            </b>{{ $horario->aula->sede->nombre }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-row justify-content-between mb-2">
                                <div class="d-flex flex-row">

                                    <i class="ti ti-building me-2"></i>
                                    <small class="text-black text-start"><b>Aula: </b>
                                        {{ $horario->aula->nombre }}</small>
                                </div>
                                <div class="d-flex flex-row">
                                    <i class="ti ti-abacus me-2"></i>
                                    <small class="text-black text-start"><b>Cupos iniciales: </b>
                                        {{ $horario->capacidad }}</small>
                                </div>
                            </div>

                            <div class="d-flex flex-row justify-content-between mb-2">
                                <div class="d-flex flex-row">

                                    <i class="ti ti-abacus me-2"></i>
                                    <small class="text-black tet-satart"><b>Cupos limite: </b>
                                        {{ $horario->capacidad_limite }}</small>
                                </div>

                            </div>


                        </div>
                    </div>
                </div>
            @endforeach

            @if ($horarios->isEmpty())
                <div class="col-12 text-center py-4">
                    <i class="ti ti-calendar-off fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No hay horarios registrados</p>
                </div>
            @endif

            <div class="card-footer">
                {{ $horarios->links() }}
            </div>
        </div>
    </div>

    <!-- Offcanvas nuevo horario -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel"
        data-bs-backdrop="static" data-bs-scroll="true" wire:ignore.self>
        <div class="offcanvas-header">
            <h4 class="offcanvas-title fw-bold text-primary" id="offcanvasRightLabel">
                Nuevo horario
            </h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form wire:submit.prevent="guardarHorario" id="offcanvas-form">

                <div class="mb-3">
                    <label for="sedeAula" class="form-label">Sede</label>
                    <select wire:model="sedeAula" wire:change="buscarAulas($event.target.value)"
                        onclick="event.stopPropagation(); event.preventDefault();" onmousedown="event.stopPropagation()"
                        class="form-select" id="sedeAula" required>

                        <option value="">Seleccionar sede</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sede_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Select de Aula -->
                <div class="mb-3">
                    <label for="aula_id" class="form-label">Aula</label>
                    <select wire:model="aula_id" class="form-select" id="aula_id" name="aula_id" required
                        @if (!$sedeAula) disabled @endif>
                        <option value="">Seleccionar aula</option>
                        @foreach ($aulas as $aula)
                            <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                        @endforeach
                    </select>
                    @error('aula_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Select de Día -->
                <div class="mb-3">
                    <label for="dia" class="form-label">Día</label>
                    <select wire:model="dia" class="form-select" id="dia" name="dia" required>
                        <option value="">Seleccionar día</option>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                        <option value="0">Domingo</option>
                    </select>
                    @error('dia')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="cupos_iniciales" class="form-label">Cupos iniciales</label>
                    <input wire:model="cupos_iniciales" type="number" class="form-control" id="cupos_iniciales"
                        required>
                    @error('cupos_iniciales')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="cupos_limite" class="form-label">Cupos limite</label>
                    <input wire:model="cupos_limite" type="number" class="form-control" id="cupos_limite" required>
                    @error('cupos_limite')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Hora Inicio -->
                <div class="mb-3">
                    <label for="hora_inicio" class="form-label">Hora Inicio</label>
                    <input wire:model="hora_inicio" type="time" class="form-control" id="hora_inicio" required>
                    @error('hora_inicio')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Hora Fin -->
                <div class="mb-3">
                    <label for="hora_fin" class="form-label">Hora Fin</label>
                    <input wire:model="hora_fin" type="time" class="form-control" id="hora_fin" required>
                    @error('hora_fin')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </form>
        </div>

        <!-- En la sección del footer del offcanvas: -->
        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <div class="mt-4 d-flex justify-content-start">
                <button type="submit" class="btn rounded-pill btn-primary me-2" form="offcanvas-form">
                    {{ $horarioEditando ? 'Actualizar' : 'Guardar' }}
                </button>
                <button type="button" class="btn rounded-pill  btn-outline-secondary" wire:click.prevent="cancelar">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Offcanvas de Filtros -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="addEventSidebarFiltros"
        aria-labelledby="addEventSidebarLabel" wire:ignore.self>
        <div class="offcanvas-header">
            <h4 class="offcanvas-title" id="addEventSidebarLabel">Filtros de horarios</h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form wire:submit.prevent="aplicarFiltros">
                <!-- Filtro por Sede -->
                <div class="mb-3">
                    <label class="form-label">Sede</label>
                    <select wire:model="sedeFiltro" class="form-select">
                        <option value="">Todas las sedes</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Tipo de Aula -->
                <div class="mb-3">
                    <label class="form-label">Tipo de aula</label>
                    <select wire:model="tipoAulaFiltro" class="form-select">
                        <option value="">Todos los tipos</option>
                        @foreach ($tiposAula as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>


        </div>
        <div class="offcanvas-footer p-5  border-top border-2 px-8">
            <button type="submit" class="btn btn-primary rounded-pill">
                Aplicar Filtros
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-pill" wire:click="resetFiltros">
                Limpiar Filtros
            </button>


        </div>
        </form>
    </div>

    <!-- Offcanvas de Edición -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditar" aria-labelledby="offcanvasEditarLabel"
        wire:ignore.self>
        <div class="offcanvas-header">
            <h4 class="offcanvas-title fw-bold text-primary" id="offcanvasEditarLabel">Editar horario</h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form wire:submit.prevent="actualizarHorario" id="offcanvas-form-editar">
                <div class="mb-3">
                    <label for="sedeAulaEditar" class="form-label">Sede</label>
                    <select wire:model="sedeAula" wire:change="buscarAulas($event.target.value)" class="form-select"
                        required>
                        <option value="">Seleccionar sede</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sedeAula')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="aula_id_editar" class="form-label">Aula</label>
                    <select wire:model="aula_id" class="form-select" required
                        @if (!$sedeAula) disabled @endif>
                        <option value="">Seleccionar aula</option>
                        @foreach ($aulas as $aula)
                            <option value="{{ $aula->id }}" @if ($aula->id == $aula_id) selected @endif>
                                {{ $aula->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('aula_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="dia_editar" class="form-label">Día</label>
                    <select wire:model="dia" class="form-select" required>
                        <option value="">Seleccionar día</option>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                        <option value="0">Domingo</option>
                    </select>
                    @error('dia')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="cupos_iniciales" class="form-label">Cupos iniciales</label>
                    <input wire:model="cupos_iniciales" type="number" class="form-control"
                        id="cupos_iniciales_editar" required>
                    @error('cupos_iniciales')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="cupos_limite" class="form-label">Cupos limite</label>
                    <input wire:model="cupos_limite" type="number" class="form-control" id="cupos_limite_editar"
                        required>
                    @error('cupos_limite')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="hora_inicio_editar" class="form-label">Hora Inicio</label>
                    <input wire:model="hora_inicio" type="time" class="form-control" required>
                    @error('hora_inicio')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="hora_fin_editar" class="form-label">Hora Fin</label>
                    <input wire:model="hora_fin" type="time" class="form-control" required>
                    @error('hora_fin')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </form>
        </div>

        <div class="offcanvas-footer p-5 border-top border-2 px-8">
            <div class="mt-4 d-flex justify-content-start">
                <button type="submit" class="btn rounded-pill btn-primary me-2" form="offcanvas-form-editar">
                    Actualizar
                </button>

            </div>
        </div>
    </div>
</div>


@assets
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])

    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endassets


@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100px',
                allowClear: true,
                placeholder: 'Ninguno'
            });

        });

        $(document).ready(function() {
            // Configurar cierre controlado


            Livewire.on('filtrosAplicados', () => {
                $('#addEventSidebarFiltros').offcanvas('hide');
            });

            // Agregar este nuevo listener
            Livewire.on('resetearFiltrosOffcanvas', () => {
                // Reiniciar los selects del offcanvas de filtros
                $('#addEventSidebarFiltros select').val('').trigger('change');
            });


            // Agrega estos nuevos listeners
            Livewire.on('abrirOffcanvas', () => {


                const nombreOffCanvas = event.detail.nombreModal;
                $('#' + nombreOffCanvas).offcanvas('show');


                const backdrop = document.createElement('div');
                backdrop.className = 'offcanvas-backdrop fade show';
                document.body.appendChild(backdrop);

                var offcanvasElement = document.getElementById(nombreOffCanvas);
                var offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
                    backdrop: true
                });
                offcanvas.show();
                offcanvasElement.addEventListener('hidden.bs.offcanvas', () => {
                    backdrop.remove();
                });


            });

            Livewire.on('cerrarOffcanvas', () => {
                const nombreOffCanvas = event.detail.nombreModal;
                $('#' + nombreOffCanvas).offcanvas('hide');
                $('.offcanvas-backdrop').remove();
            });


        });
    </script>
@endpush
</div>
