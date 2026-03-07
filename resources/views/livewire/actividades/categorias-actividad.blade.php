<div>

    <!-- botonera -->
    <div class="row mb-5 mt-5">

        <div class="row mb-5 mt-5 w-100">
            <div class="col-12 text-start">
                <a href="{{ route('actividades.crearCategorias', $actividad) }}" class="btn w-30 align-content-center rounded-pill float-start btn-primary rounded-pill waves-effect waves-light">
                    <i class="ti ti-plus me-1"></i> Nueva categoría
                </a>
            </div>

        </div>
    </div>
    <!-- /botonera -->


    <div id="container-categorias" class="row">
        <!-- listado de categorias aqui carga las que ya estan creadas en la base de datos-->
        @if (isset($categoriasActividad))
        @foreach ($categoriasActividad as $categoria)
        <div class="col-lg-4 col-sm-12 mb-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <div class="d-flex align-items-start">

                            <div class="me-2 ms-1 mt-1 px-1">
                                <div class="client-info">
                                    <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $categoria->nombre }}:
                                        {{ $categoria->id }}</h5>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="dropdown ">
                        <button class="btn btn-text-secondary border rounded text-muted p-1 me-n1" type="button" id="salesByCountryTabs" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-md text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">

                            <a href="{{ route('actividades.editarCategoria', $categoria) }}" class="dropdown-item">
                                Editar
                            </a>

                            <a href="javascript:void(0);" wire:click="confirmarEliminarCategoria('{{ $categoria->id }}')" class="dropdown-item">Eliminar
                            </a>
                            <a class="dropdown-item" href="javascript:void(0);">Inactivar

                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="nav-align-top">
                        <div class="tab-content border-0  mx-1">
                            <div class="tab-pane fade show active" id="navs-justified-new-{{ $categoria->id }}" role="tabpanel">
                                <ul class="timeline mb-0">
                                    <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none"></span>
                                    <div class="timeline-event ps-1">

                                        <div class="row justify-content-between mb-2">
                                            @foreach ($categoria->monedas as $moneda)
                                            @php
                                            // Aseguramos que nombre_corto tenga un valor por defecto si es null
                                            $nombreMoneda = $moneda->nombre_corto; // Puedes cambiar 'COP' por la moneda por defecto que prefieras
                                            $currency = Number::currency($moneda->pivot->valor);
                                            @endphp
                                            <div class="col-12 col-md-6 align-items-center">

                                                <div class="d-flex flex-column text-star">

                                                    <small class="d-flex  text-muted"><i class="d-flex  ti ti-currency-dollar me-2 float-start"></i>
                                                        Valor
                                                        {{ $moneda->nombre_corto }}
                                                        :</small>
                                                    <small class="d-flex fw-semibold text-black">
                                                        <h5 class="mb-0"> {{ $currency }}
                                                        </h5>
                                                    </small>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>

                                        <div class="row justify-content-between mb-2">
                                            <div class="col-12 col-md-6 align-items-center">

                                                <div class="d-flex flex-column text-star">
                                                    <small class="text-muted"> <i class="ti ti-user-heart"></i>
                                                        Aforo total
                                                        :</small>
                                                    <small class="fw-semibold text-black">
                                                        <h5 class="mb-0">{{ $categoria->aforo }}</h5>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 align-items-center">

                                                <div class="d-flex flex-column text-star">
                                                    <small class="text-muted"><i class="ti ti-user-minus"></i>Aforo ocupado
                                                        :</small>
                                                    <small class="fw-semibold text-black">
                                                        <h5 class="mb-0 text-danger">
                                                            {{ $categoria->aforo_ocupado > 0 ? $categoria->aforo_ocupado : '0' }}
                                                        </h5>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row justify-content-between mb-2">
                                            <div class="col-12 col-md-6 align-items-center">

                                                <div class="d-flex flex-column text-star">
                                                    <small class="text-muted"> <i class="ti ti-user-check"></i>
                                                        Aforo
                                                        restante :</small>
                                                    <small class="fw-semibold text-black">
                                                        <h5 class="mb-0 text-success">
                                                            {{ $categoria->aforo - $categoria->aforo_ocupado }}
                                                        </h5>
                                                    </small>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ listado de categorias -->
        @endforeach
        @else
        <div class="flex-grow-1 me-2">
            <p class="fs-7 text-wrap m-0">No hay resultados</p>
        </div>
        @endif
    </div>


    <!--/ carga del modal para crear una nueva categoria PENDIENTE POR ELIMINAR-->
    <div wire:ignore.self class="modal fade" id="modalNuevaCategoria" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-plus"></i> Nueva categoría </h3>
                        <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span> son
                            obligatorios </p>
                    </div>
                    <form id="formnuevaCategoria" wire:submit.prevent="nuevaCategoria" class="row g-3">
                        @csrf
                        <!-- Nombre categoria -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nombreNuevo">
                                <span class="badge badge-dot bg-info me-1"></span> Nombre
                                @error('nombreNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </label>
                            <input type="text" wire:model="nombreNuevo" id="nombreNuevo" class="form-control @error('nombreNuevo') is-invalid  @enderror" placeholder="Nombre" />
                        </div>

                        <!-- Aforo -->
                        <div class="col-12 col-md-4 mb-3">
                            <label class="form-label" for="aforoNuevo">
                                <span class="badge badge-dot bg-info me-1"></span> Aforo
                                @error('aforoNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </label>
                            <input type="number" wire:model="aforoNuevo" id="aforoNuevo" min="0" class="form-control @error('aforoNuevo') is-invalid  @enderror">
                        </div>

                        <!-- limite de compras-->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="limiteCompras">Cantidad limite de compras</label>
                            <input @if ($actividad->tipo->unica_compra == true) max=1 @endif min=1 type="number"
                            wire:model="limiteCompras" id="limiteCompras" class="form-control" />
                        </div>

                        <!-- limite de invitados-->
                        @if($actividad->tiene_invidados == true)
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="limiteInvitados">Cantidad limite de compras</label>
                            <input type="number" wire:model="limiteInvitados" id="limiteInvitados" class="form-control" />
                        </div>
                        @endif

                        <!-- Es gratuita -->
                        <div class="col-12 col-md-2">
                            <label class="form-label" for="nombreNuevo">¿Es gratuita?</label>
                            <label class="switch switch-lg">
                                <input type="checkbox" wire:model="esGratuitaNuevo" id="esGratuitaNuevo" class="switch-input" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">SI</span>
                                    <span class="switch-off">NO</span>
                                </span>
                                <span class="switch-label"></span>
                            </label>
                        </div>



                        <!-- Valores de monedas (si no es gratuita) -->
                        @if (!$esGratuitaNuevo)
                        @foreach ($monedasActividad as $moneda)
                        <div class="col-lg-6 col-md-6 col-sm-12 mt-3">
                            <div class="mb-3">
                                <label class="form-label" for="valoresMonedasNuevo.{{ $moneda->id }}">
                                    <span class="badge badge-dot bg-info me-1"></span> Valor en:
                                    <b>{{ $moneda->nombre }}</b>
                                    @error('valoresMonedasNuevo.' . $moneda->id)
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </label>
                                <input type="number" wire:model="valoresMonedasNuevo.{{ $moneda->id }}" id="valoresMonedasNuevo.{{ $moneda->id }}" class="form-control  @error('valoresMonedasNuevo.' . $moneda->id) is-invalid @enderror">
                            </div>
                        </div>
                        @endforeach
                        @endif

                        @if ($actividadActual->restriccion_por_categoria == true)
                        <div id="container-restricciones" class="row">
                            <h4 class="mb-2"><i class="ti ti-lock-off"></i> Restricciones por categoria </h4>

                            <!-- Género -->
                            <div class="col-lg-6 col-sm-12 @error('generosNuevo') is-invalid @enderror  mb-3">
                                <label class="form-label">Género</label>
                                @error('generoNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-genero">
                                    <select wire:model.live="generoNuevo" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar género',
                                                    allowClear: true
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('generoNuevo', $(this.$refs.select).val())
                                                });
                                            }
                                        }" x-ref="select" id="generoNuevo" name='generoNuevo' class="select2 form-select">
                                        <option value="0">Selecciona un opción</option>
                                        <option value="1" {{ old('genero') == 1 ? 'selected' : '' }}>Hombres
                                        </option>
                                        <option value="2" {{ old('genero') == 2 ? 'selected' : '' }}>Mujeres
                                        </option>
                                        <option value="3" {{ old('genero') == 3 ? 'selected' : '' }}>Ambos
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Vinculación a grupo -->
                            <div class="col-lg-6 col-sm-12 @error('vinculacionGrupoNuevo') is-invalid @enderror mb-3">
                                <label class="form-label">Definir vinculación a grupo</label>
                                @error('vinculacionGrupoNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-vinculacion">
                                    <select wire:model.live="vinculacionGrupoNuevo" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar vinculación',
                                                    allowClear: true
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('vinculacionGrupoNuevo', $(this.$refs.select).val())
                                                });
                                            }
                                        }" x-ref="select" id="vinculacionGrupoNuevo" class="select2 form-select">
                                        <option value="0">Selecciona un opción</option>
                                        <option value="1" {{ old('vinculacionGrupoNuevo') == 1 ? 'selected' : '' }}>Pertenece a
                                            grupo</option>
                                        <option value="2" {{ old('vinculacionGrupoNuevo') == 2 ? 'selected' : '' }}>No pertenece
                                        </option>
                                        <option value="3" {{ old('vinculacionGrupoNuevo') == 3 ? 'selected' : '' }}>Ambos
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Actividad en grupo -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="actividadGrupoNuevo" class="form-label">Definir actividad en
                                    grupo</label>
                                @error('actividadGrupoNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-grupo"">
                                        <select wire:model.live=" actividadGrupoNuevo" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar actividad',
                                                    allowClear: true
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('actividadGrupoNuevo', $(this.$refs.select).val())
                                                });
                                            }
                                        }" x-ref="select" id="actividadGrupoNuevo" class="select2 form-select">
                                    <option value="0">Selecciona un opción</option>
                                    <option value="1" {{ old('actividadGrupoNuevo') == 1 ? 'selected' : '' }}>Activos
                                    </option>
                                    <option value="2" {{ old('actividadGrupoNuevo') == 2 ? 'selected' : '' }}>Inactivos
                                    </option>
                                    <option value="3" {{ old('actividadGrupoNuevo') == 3 ? 'selected' : '' }}>Ambos
                                        </select>
                                </div>
                            </div>

                            <!-- sedes habilitadas-->
                            <div class="col-lg-6  @error('sedesNuevo') is-invalid @enderror col-sm-12 mb-3">
                                <label class="form-label">Sedes habilitadas</label>
                                @error('sedesNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div id='container-select-sedes' wire:ignore>
                                    <select wire:model.live="sedesNuevo" x-data="{
                                            init() {
                                                const select = $(this.$refs.select);
                                                select.select2({
                                                    placeholder: 'Seleccionar sedes',
                                                    allowClear: true
                                                });
                                                select.on('change', () => {
                                                    // Usa @this.set con el array completo
                                                    @this.set('sedesNuevo', select.val() || [])
                                                });
                                            }
                                        }" x-ref="select" id="sedesNuevo" name="sedesNuevo[]" class="select2 form-select" multiple>
                                        @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('sedesNuevo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Pasos Crecimiento Culminar -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label class="form-label">Procesos a culminar</label>
                                <div wire:ignore id="container-select-pasos-culminar">
                                    <select wire:model.live="pasosCrecimientoCulminarNuevo" x-data="{
                                                init() {
                                                    $(this.$refs.select).select2({
                                                        placeholder: 'Seleccionar procesos',
                                                        allowClear: true,
                                                        dropdownParent: $('#formnuevaCategoria')
                                                    });
                                                    $(this.$refs.select).on('change', () => {
                                                        @this.set('pasosCrecimientoCulminarNuevo', $(this.$refs.select).val() || [])
                                                    });
                                                }
                                            }" x-ref="select" name="pasosCrecimientoCulminar" class="select2 form-select" multiple>
                                        @foreach ($pasosCrecimientoCulminar as $paso)
                                        <option value="{{ $paso->id }}">{{ $paso->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- Pasos Rangos edad -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="rangosEdad" class="form-label">Definir rangos de edad</label>
                                @error('rangosEdadNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-edad">
                                    <select wire:model.live="rangosEdadNuevo" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar rangos de edad',
                                                    allowClear: true
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('rangosEdadNuevo', $(this.$refs.select).val() || [])
                                                });
                                            }
                                        }" x-ref="select" name="rangosEdad[]" class="select2 form-select" multiple>
                                        @foreach ($rangosEdad as $rango)
                                        <option value="{{ $rango->id }}">{{ $rango->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Pasos Crecimiento Requisito -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="pasosCrecimientoRequisito" class="form-label">Definir procesos
                                    requisito </label>
                                @error('pasosCrecimientoRequisitoNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-pasos">
                                    <select wire:model.live="pasosCrecimientoRequisitoNuevo" x-data="{
                                                init() {
                                                    $(this.$refs.select).select2({
                                                        placeholder: 'Seleccionar procesos requisito',
                                                        allowClear: true
                                                    });
                                                    $(this.$refs.select).on('change', () => {
                                                        @this.set('pasosCrecimientoRequisitoNuevo', $(this.$refs.select).val() || [])
                                                    });
                                                }
                                            }" x-ref="select" name="pasosCrecimientoRequisito[]" class="select2 form-select" multiple>
                                        @foreach ($pasosCrecimientoRequisito as $paso)
                                        <option value="{{ $paso->id }}">
                                            {{ $paso->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Tipo Usuarios -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="tipoUsuarios" class="form-label">Definir tipo usuario</label>
                                @error('tipoUsuariosNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-usuarios">
                                    <select wire:model.live="tipoUsuariosNuevo" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar tipos de usuario',
                                                    allowClear: true
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('tipoUsuariosNuevo', $(this.$refs.select).val() || [])
                                                });
                                            }
                                        }" x-ref="select" name="tipoUsuarios[]" class="select2 form-select" multiple>
                                        @foreach ($tipoUsuarios as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Estados Civiles -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label class="form-label">Definir estados civiles</label>
                                @error('estadosCivilesNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-estados">
                                    <select wire:model.live="estadosCivilesNuevo" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar estados civiles',
                                                    allowClear: true
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('estadosCivilesNuevo', $(this.$refs.select).val() || [])
                                                });
                                            }
                                        }" x-ref="select" name="estadosCiviles[]" class="select2 form-select" multiple>
                                        @foreach ($estadosCiviles as $estado)
                                        <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Tipo Servicios -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="tipoServicios" class="form-label">Definir tipos servicios</label>
                                @error('tipoServiciosNuevo')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-servicios">
                                    <select wire:model.live="tipoServiciosNuevo" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar tipos de servicios',
                                                    allowClear: true
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('tipoServiciosNuevo', $(this.$refs.select).val() || [])
                                                });
                                            }
                                        }" x-ref="select" name="tipoServicios[]" class="select2 form-select" multiple>
                                        @foreach ($tipoServicios as $tipoSer)
                                        <option value="{{ $tipoSer->id }}">{{ $tipoSer->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                        </div>
                        @endif
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--/ carga del modal para crear una editar  categoria PENDIENTE POR ELIMINAR-->
    <div wire:ignore.self class="modal fade" id="modalEditarCategoria" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-plus"></i> Editar categoría </h3>
                        <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span> son
                            obligatorios </p>
                    </div>
                    <form id="formeditarCategoria" wire:submit.prevent="actualizarCategoria" class="row g-3">
                        @csrf
                        <!-- Nombre categoria -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nombreEditar">
                                <span class="badge badge-dot bg-info me-1"></span> Nombre
                                @error('nombreEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </label>
                            <input value='{{ $nombreEditar }}' type="text" wire:model.live="nombreEditar" name="nombreEditar" class="form-control @error('nombreEditar') is-invalid  @enderror" />
                        </div>

                        <!-- Aforo  editar  -->
                        <div class="col-12 col-md-4 mb-3">
                            <label class="form-label" for="aforoEditar">
                                <span class="badge badge-dot bg-info me-1"></span> Aforo
                                @error('aforoEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </label>
                            <input value='{{ $aforoEditar }}' type="number" wire:model="aforoEditar" name="aforoEditar" min="0" class="form-control @error('aforoEditar') is-invalid  @enderror">
                        </div>
                        <!-- limite de compras-->


                        <div class="col-12 col-md-6">
                            <label class="form-label" for="limiteComprasEditar">Cantidad limite de compras</label>
                            <input @if ($actividad->tipo->unica_compra == true) max=1 @endif type="number"
                            value='{{ $limiteComprasEditar }}' wire:model="limiteComprasEditar"
                            class="form-control" name="limiteComprasEditar" />
                        </div>

                        <!-- Es gratuita  editar  -->
                        <div class="col-12 col-md-2">
                            <label class="form-label" for="nombreNuevo">¿Es gratuita?</label>
                            <label class="switch switch-lg">
                                <input @checked($esGratuitaEditar==true) type="checkbox" wire:model="esGratuitaEditar" name="esGratuitaEditar" class="switch-input" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">SI</span>
                                    <span class="switch-off">NO</span>
                                </span>
                                <span class="switch-label"></span>
                            </label>
                        </div>



                        <!-- Valores de monedas (si no es gratuita)  editar  -->
                        @if (!$esGratuitaEditar)
                        @foreach ($monedasActividad as $moneda)
                        <div class="col-lg-6 col-md-6 col-sm-12 mt-3">
                            <div class="mb-3">
                                <label class="form-label" for="valoresMonedasEditar.{{ $moneda->id }}">
                                    <span class="badge badge-dot bg-info me-1"></span> Valor en:
                                    <b>{{ $moneda->nombre }}</b>
                                    @error('valoresMonedasEditar.' . $moneda->id)
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </label>
                                <input type="number" wire:model="valoresMonedasEditar.{{ $moneda->id }}" name="valoresMonedasEditar.{{ $moneda->id }}" class="form-control  @error('valoresMonedasEditar.' . $moneda->id) is-invalid @enderror">
                            </div>
                        </div>
                        @endforeach
                        @endif
                        @if ($actividad->restriccion_por_categoria == true)
                        <div id="container-restricciones" class="row">
                            <h4 class="mb-2"><i class="ti ti-lock-off"></i> Restricciones por categoria</h4>

                            <!-- Género  editar  -->
                            <div class="col-lg-6 col-sm-12 @error('generosEditar') is-invalid @enderror  mb-3">
                                <label class="form-label">Género </label>
                                @error('generosEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore.self id="container-select-genero-editar">
                                    <select wire:model.live="generosEditar" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar género',
                                                    allowClear: true,
                                                    dropdownParent: $('#modalEditarCategoria')
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('generosEditar', $(this.$refs.select).val())
                                                });
                                            }
                                        }" x-ref="select" name="generosEditar" class="select2 form-select">
                                        <option value="1" @if ($generosEditar==1) selected @endif>
                                            Hombres
                                        </option>
                                        <option value="2" @if ($generosEditar==2) selected @endif>
                                            Mujeres
                                        </option>
                                        <option value="3" @if ($generosEditar==3) selected @endif>
                                            Ambos
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Vinculación a grupo  editar  -->
                            <div class="col-lg-6 col-sm-12 @error('sedesNuevo') is-invalid @enderror mb-3">
                                <label for="vinculacionGrupoEditar" class="form-label">Definir vinculación a
                                    grupo</label>
                                @error('vinculacionGrupoEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore.self id="container-select-vinculacion-editar">
                                    <select wire:model.live="vinculacionGrupoEditar" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar vinculación',
                                                    allowClear: true,
                                                    dropdownParent: $('#modalEditarCategoria')
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('vinculacionGrupoEditar', $(this.$refs.select).val())
                                                });
                                            }
                                        }" x-ref="select" name="vinculacionGrupoEditar" class="select2 form-select">

                                        <option value="1" {{ $vinculacionGrupoEditar == 1 ? 'selected' : '' }}>Pertenece a
                                            grupo</option>
                                        <option value="2" {{ $vinculacionGrupoEditar == 2 ? 'selected' : '' }}>No pertenece
                                        </option>
                                        <option value="3" {{ $vinculacionGrupoEditar == 3 ? 'selected' : '' }}>Ambos
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Actividad en grupo  editar  -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="actividadGrupoEditar" class="form-label">Definir actividad en
                                    grupo</label>
                                @error('actividadGrupoEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore.self id="container-select-grupo-editar"">
                                        <select wire:model.live=" actividadGrupoEditar" x-data="{
                                            init() {
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar actividad',
                                                    allowClear: true,
                                                    dropdownParent: $('#modalEditarCategoria')
                                                });
                                                $(this.$refs.select).on('change', () => {
                                                    @this.set('actividadGrupoEditar', $(this.$refs.select).val())
                                                });
                                            }
                                        }" x-ref="select" name="actividadGrupoEditar" class="select2 form-select">

                                    <option value="1" {{ $actividadGrupoEditar == 1 ? 'selected' : '' }}>
                                        Activos
                                    </option>
                                    <option value="2" {{ $actividadGrupoEditar == 2 ? 'selected' : '' }}>
                                        Inactivos
                                    </option>
                                    <option value="3" {{ $actividadGrupoEditar == 3 ? 'selected' : '' }}>
                                        Ambos
                                        </select>
                                </div>
                            </div>

                            <!-- sedes habilitadas editar  -->
                            <div class="col-lg-6  @error('sedesEditar') is-invalid @enderror col-sm-12 mb-3">
                                <label for="sedesEditar" class="form-label">Sedes habilitadas </label>
                                @error('sedesEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div id='container-select-sedes-editar' wire:ignore>
                                    <select wire:model="sedesEditar" x-data="{
                                            // init() es un método de Alpine que se ejecuta cuando se inicializa el componente
                                            init() {
                                                // Obtiene la referencia al elemento select usando Alpine.js
                                                const select = $(this.$refs.select);

                                                // Inicialización de Select2 (librería jQuery)
                                                select.select2({
                                                    placeholder: 'Seleccionar sedes',
                                                    allowClear: true,
                                                    // Especifica el contenedor padre para el dropdown (importante para modales)
                                                    dropdownParent: $('#formeditarCategoria')
                                                });

                                                // PASO 1: Establecer valores iniciales
                                                // Verifica si sedesEditar es un array en el componente Livewire
                                                if (Array.isArray(@this.sedesEditar)) {
                                                    // Establece los valores iniciales en Select2 y dispara el evento change
                                                    select.val(@this.sedesEditar).trigger('change');
                                                }

                                                // PASO 2: Manejo de cambios del usuario
                                                // Cuando el usuario cambia las selecciones en Select2
                                                select.on('change', () => {
                                                    // Actualiza el valor en el componente Livewire
                                                    // select.val() devuelve los valores seleccionados
                                                    // || [] asegura que siempre sea un array
                                                    @this.set('sedesEditar', select.val() || []);
                                                });

                                                // PASO 3: Manejo de cambios desde Livewire
                                                // Observa cambios en la variable sedesEditar del componente Livewire
                                                @this.$watch('sedesEditar', value => {
                                                    // Compara los valores actuales con los nuevos para evitar bucles
                                                    if (JSON.stringify(select.val()) !== JSON.stringify(value)) {
                                                        // Actualiza Select2 si hay diferencias
                                                        select.val(value).trigger('change');
                                                    }
                                                });
                                            }
                                        }" x-ref="select" id="sedesEditar" name="sedesEditar[]" class="select2 form-select" multiple>
                                        @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('sedesEditar')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Pasos Crecimiento Culminar  editar  -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label class="form-label">Procesos a culminar </label>
                                <div wire:ignore id="container-select-pasos-culminar-editar">
                                    <select wire:model.live="pasosCrecimientoCulminarEditar" x-data="{
                                                init() {
                                                    const select = $(this.$refs.select);
                                                    $(this.$refs.select).select2({
                                                        placeholder: 'Seleccionar procesos',
                                                        allowClear: true,
                                                        dropdownParent: $('#formeditarCategoria')
                                                    });
                                                    if (Array.isArray(@this.pasosCrecimientoCulminarEditar)) {
                                                        // Establece los valores iniciales en Select2 y dispara el evento change
                                                        select.val(@this.pasosCrecimientoCulminarEditar).trigger('change');
                                                    }

                                                    select.on('change', () => {
                                                        // Actualiza el valor en el componente Livewire
                                                        // select.val() devuelve los valores seleccionados
                                                        // || [] asegura que siempre sea un array
                                                        @this.set('pasosCrecimientoCulminarEditar', select.val() || []);
                                                    });

                                                    @this.$watch('pasosCrecimientoCulminarEditar', value => {
                                                        // Compara los valores actuales con los nuevos para evitar bucles
                                                        if (JSON.stringify(select.val()) !== JSON.stringify(value)) {
                                                            // Actualiza Select2 si hay diferencias
                                                            select.val(value).trigger('change');
                                                        }
                                                    });
                                                }
                                            }" x-ref="select" name="pasosCrecimientoCulminar" class="select2 form-select" multiple>
                                        @foreach ($pasosCrecimientoCulminar as $paso)
                                        <option value="{{ $paso->id }}">{{ $paso->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- Pasos Rangos edad  editar  -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="rangosEdadEditar" class="form-label">Definir rangos de edad</label>
                                @error('rangosEdadEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-edad-editar">
                                    <select wire:model.live="rangosEdadEditar" x-data="{
                                            init() {
                                                const select = $(this.$refs.select);
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar procesos',
                                                    allowClear: true,
                                                    dropdownParent: $('#formeditarCategoria')
                                                });
                                                if (Array.isArray(@this.rangosEdadEditar)) {
                                                    // Establece los valores iniciales en Select2 y dispara el evento change
                                                    select.val(@this.rangosEdadEditar).trigger('change');
                                                }

                                                select.on('change', () => {
                                                    // Actualiza el valor en el componente Livewire
                                                    // select.val() devuelve los valores seleccionados
                                                    // || [] asegura que siempre sea un array
                                                    @this.set('rangosEdadEditar', select.val() || []);
                                                });

                                                @this.$watch('rangosEdadEditar', value => {
                                                    // Compara los valores actuales con los nuevos para evitar bucles
                                                    if (JSON.stringify(select.val()) !== JSON.stringify(value)) {
                                                        // Actualiza Select2 si hay diferencias
                                                        select.val(value).trigger('change');
                                                    }
                                                });
                                            }
                                        }" x-ref="select" name="rangosEdadEditar[]" class="select2 form-select" multiple>
                                        @foreach ($rangosEdad as $rango)
                                        <option value="{{ $rango->id }}">{{ $rango->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Pasos Crecimiento Requisito  editar  -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="pasosCrecimientoRequisitoEditar" class="form-label">Definir procesos
                                    requisito </label>
                                @error('pasosCrecimientoRequisitoEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-pasos-editar">
                                    <select wire:model.live="pasosCrecimientoRequisitoEditar" x-data="{
                                                init() {
                                                    const select = $(this.$refs.select);
                                                    $(this.$refs.select).select2({
                                                        placeholder: 'Seleccionar procesos',
                                                        allowClear: true,
                                                        dropdownParent: $('#formeditarCategoria')
                                                    });
                                                    if (Array.isArray(@this.pasosCrecimientoRequisitoEditar)) {
                                                        // Establece los valores iniciales en Select2 y dispara el evento change
                                                        select.val(@this.pasosCrecimientoRequisitoEditar).trigger('change');
                                                    }

                                                    select.on('change', () => {
                                                        // Actualiza el valor en el componente Livewire
                                                        // select.val() devuelve los valores seleccionados
                                                        // || [] asegura que siempre sea un array
                                                        @this.set('pasosCrecimientoRequisitoEditar', select.val() || []);
                                                    });

                                                    @this.$watch('pasosCrecimientoRequisitoEditar', value => {
                                                        // Compara los valores actuales con los nuevos para evitar bucles
                                                        if (JSON.stringify(select.val()) !== JSON.stringify(value)) {
                                                            // Actualiza Select2 si hay diferencias
                                                            select.val(value).trigger('change');
                                                        }
                                                    });
                                                }
                                            }" x-ref="select" name="pasosCrecimientoRequisitoEditar[]" class="select2 form-select" multiple>
                                        @foreach ($pasosCrecimientoRequisito as $paso)
                                        <option value="{{ $paso->id }}">
                                            {{ $paso->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Tipo Usuarios  editar  -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="tipoUsuarios" class="form-label">Definir tipo usuario</label>
                                @error('tipoUsuariosEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-usuarios-editar">
                                    <select wire:model.live="tipoUsuariosEditar" x-data="{
                                            init() {
                                                const select = $(this.$refs.select);
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar procesos',
                                                    allowClear: true,
                                                    dropdownParent: $('#formeditarCategoria')
                                                });
                                                if (Array.isArray(@this.tipoUsuariosEditar)) {
                                                    // Establece los valores iniciales en Select2 y dispara el evento change
                                                    select.val(@this.tipoUsuariosEditar).trigger('change');
                                                }

                                                select.on('change', () => {
                                                    // Actualiza el valor en el componente Livewire
                                                    // select.val() devuelve los valores seleccionados
                                                    // || [] asegura que siempre sea un array
                                                    @this.set('tipoUsuariosEditar', select.val() || []);
                                                });

                                                @this.$watch('tipoUsuariosEditar', value => {
                                                    // Compara los valores actuales con los nuevos para evitar bucles
                                                    if (JSON.stringify(select.val()) !== JSON.stringify(value)) {
                                                        // Actualiza Select2 si hay diferencias
                                                        select.val(value).trigger('change');
                                                    }
                                                });
                                            }
                                        }" x-ref="select" name="tipoUsuariosEditar[]" class="select2 form-select" multiple>
                                        @foreach ($tipoUsuarios as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Estados Civiles  editar  -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="estadosCivilesEditar" class="form-label">Definir estados
                                    civiles</label>
                                @error('estadosCivilesEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-estados-editar">
                                    <select wire:model.live="estadosCivilesEditar" x-data="{
                                            init() {
                                                const select = $(this.$refs.select);
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar procesos',
                                                    allowClear: true,
                                                    dropdownParent: $('#formeditarCategoria')
                                                });
                                                if (Array.isArray(@this.estadosCivilesEditar)) {
                                                    // Establece los valores iniciales en Select2 y dispara el evento change
                                                    select.val(@this.estadosCivilesEditar).trigger('change');
                                                }

                                                select.on('change', () => {
                                                    // Actualiza el valor en el componente Livewire
                                                    // select.val() devuelve los valores seleccionados
                                                    // || [] asegura que siempre sea un array
                                                    @this.set('estadosCivilesEditar', select.val() || []);
                                                });

                                                @this.$watch('estadosCivilesEditar', value => {
                                                    // Compara los valores actuales con los nuevos para evitar bucles
                                                    if (JSON.stringify(select.val()) !== JSON.stringify(value)) {
                                                        // Actualiza Select2 si hay diferencias
                                                        select.val(value).trigger('change');
                                                    }
                                                });
                                            }
                                        }" x-ref="select" name="estadosCivilesEditar[]" class="select2 form-select" multiple>
                                        @foreach ($estadosCiviles as $estado)
                                        <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Tipo Servicios  editar  -->
                            <div class="col-lg-6 col-sm-12 mb-3">
                                <label for="tipoServicios" class="form-label">Definir tipos servicios</label>
                                @error('tipoServiciosEditar')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div wire:ignore id="container-select-servicios-editar">
                                    <select wire:model.live="tipoServiciosEditar" x-data="{
                                            init() {
                                                const select = $(this.$refs.select);
                                                $(this.$refs.select).select2({
                                                    placeholder: 'Seleccionar procesos',
                                                    allowClear: true,
                                                    dropdownParent: $('#formeditarCategoria')
                                                });
                                                if (Array.isArray(@this.tipoServiciosEditar)) {
                                                    // Establece los valores iniciales en Select2 y dispara el evento change
                                                    select.val(@this.tipoServiciosEditar).trigger('change');
                                                }

                                                select.on('change', () => {
                                                    // Actualiza el valor en el componente Livewire
                                                    // select.val() devuelve los valores seleccionados
                                                    // || [] asegura que siempre sea un array
                                                    @this.set('tipoServiciosEditar', select.val() || []);
                                                });

                                                @this.$watch('tipoServiciosEditar', value => {
                                                    // Compara los valores actuales con los nuevos para evitar bucles
                                                    if (JSON.stringify(select.val()) !== JSON.stringify(value)) {
                                                        // Actualiza Select2 si hay diferencias
                                                        select.val(value).trigger('change');
                                                    }
                                                });
                                            }
                                        }" x-ref="select" id="tipoServiciosEditar" name="tipoServiciosEditar[]" class="select2 form-select" multiple>
                                        @foreach ($tipoServicios as $tipoSer)
                                        <option value="{{ $tipoSer->id }}">{{ $tipoSer->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                        </div>
                        @endif
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form id="eliminarCategoría" method="POST" action="">
        @csrf
    </form>


</div>

@assets
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])

@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js']);
@endassets

@script
<script>
    /// este es el evento que abre el modal para actualizar la categoria.
    Livewire.on('abrirModal', () => {
        const modalId = event.detail.nombreModal;
        $(`#${modalId}`).modal('show');

        // Reiniciar Select2
        $(`#${modalId} .select2`).each(function() {
            $(this).select2({
                allowClear: true
                , width: '100%'
                , dropdownParent: $(`#${modalId}`)
            });

            // Sincronizar valores con Livewire
            const livewireProperty = $(this).data('livewire-property');
            if (livewireProperty) {
                const currentValue = @this.get(livewireProperty);
                $(this).val(currentValue).trigger('change');
            }
        });
    });


    $(document).ready(function() {
        // Inicializar Select2 pero solo del modal de nueva categoria
        $('#modalNuevaCategoria .select2').select2({
            allowClear: true
            , width: '100%'
            , dropdownParent: $('#formnuevaCategoria')

        });

    });


    Livewire.on('msn', () => {
        Swal.fire({
            title: event.detail.msnTitulo
            , html: event.detail.msnTexto
            , icon: event.detail.msnIcono
            , customClass: {
                confirmButton: 'btn btn-primary'
            }
            , buttonsStyling: false
        });
    });

    Livewire.on('cerrarModal', () => {
        $('#' + event.detail.nombreModal).modal('hide');
        setTimeout(function() {
            location.reload();
        }, 2000);
        //$(".select2").val('').trigger('change')
    });

    Livewire.on('confirmarEliminarCategoria', (event) => {
        Swal.fire({
            title: '¿Estás seguro?'
            , text: "No podrás revertir esta acción"
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonColor: '#3085d6'
            , cancelButtonColor: '#d33'
            , confirmButtonText: 'Sí, eliminar'
            , cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Evento recibido:', event.categoriaId); // Añade este log para depuración
                @this.call('eliminarCategoria', event.categoriaId);
            }
        });
    });

</script>
@endscript
