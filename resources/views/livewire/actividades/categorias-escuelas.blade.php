<div>

    <!-- botonera -->
    <div class="row mb-5 mt-5">
        <div class="me-auto ">
            <button type="button"
                class="btn d-none  rounded-pill float-end btn-primary rounded-pill waves-effect waves-light"
                data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">
                <i class="ti ti-plus"></i> Nueva categoría escuela vieja
            </button>
            <a href="{{ route('actividades.crearCategoriaEscuela', $actividad) }}"
                class="btn rounded-pill float-end btn-primary rounded-pill waves-effect waves-light">
                <i class="ti ti-plus"></i> Nueva categoría escuela
            </a>
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
                                    <div class="badge bg-label-primary rounded-pill p-1_5 mr-2">
                                        <i class="ti ti-category-2 ti-l m-1"></i>
                                    </div>
                                    <div class="me-2 ms-1 mt-1 px-1">
                                        <div class="client-info"><span
                                                class="text-body h5">{{ $categoria->nombre }}</span>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="dropdown">
                                <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1"
                                    type="button" id="salesByCountryTabs" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-md text-muted"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">

                                    <a href="{{ route('actividades.editarCategoriaEscuela', $categoria) }}"
                                        class="dropdown-item">
                                        Editar </a>
                                    <a href="javascript:void(0);"
                                        wire:click="confirmarEliminarCategoria('{{ $categoria->id }}')"
                                        class="dropdown-item">Eliminar
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);">Inactivar

                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="nav-align-top">
                                <div class="tab-content border-0  mx-1">
                                    <div class="tab-pane fade show active" id="navs-justified-new-{{ $categoria->id }}"
                                        role="tabpanel">
                                        <ul class="timeline mb-0">
                                            <span
                                                class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none"></span>
                                            <div class="timeline-event ps-1">
                                                @foreach ($categoria->monedas as $moneda)
                                                    <div class="d-flex align-items-center">

                                                        <div class="badge rounded bg-label-primary me-2 p-2"><i
                                                                class="ti ti-currency-dollar "></i></div>
                                                        <div class="card-info">

                                                            @php
                                                                // Aseguramos que nombre_corto tenga un valor por defecto si es null
                                                                $nombreMoneda = $moneda->nombre_corto; // Puedes cambiar 'COP' por la moneda por defecto que prefieras
                                                                $currency = Number::currency(
                                                                    $moneda->pivot->valor,
                                                                    in: $nombreMoneda,
                                                                    locale: 'co',
                                                                );
                                                            @endphp
                                                            <small>Valor</small>
                                                            @if ($moneda->nombre_corto == 'USD')
                                                                <h5 class="mb-0">USD {{ $currency }} </h5>
                                                            @else
                                                                <h5 class="mb-0">$ {{ $currency }} </h5>
                                                            @endif

                                                        </div>


                                                    </div>
                                                @endforeach


                                                @if (isset($categoria->materiaPeriodo))
                                                    @php
                                                        $materiaPeriodo = $categoria->materiaPeriodo;
                                                    @endphp
                                                    <div class="d-flex align-items-center">
                                                        <div class="badge rounded bg-label-primary me-2 p-2"><i
                                                                class="ti ti-vocabulary"></i></div>
                                                        <div class="card-info mt-3">
                                                            <h6>Periodo: {{ $actividad->periodo->nombre }}</h6>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="badge rounded bg-label-primary me-2 p-2">
                                                            <i class="ti ti-address-book"></i>
                                                        </div>
                                                        <div class="card-info mt-3">
                                                            <h6>Materia Asociada:
                                                                {{ $materiaPeriodo->materia->nombre }}
                                                            </h6>
                                                        </div>
                                                    </div>
                                                @else
                                                    <p>No tienes materias asociadas </p>
                                                @endif

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



    <!-- Modal Nueva Categoría -->
    <div wire:ignore.self class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-plus"></i> Nueva categoría </h3>
                        <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span> son
                            obligatorios</p>{{ $variable }}
                    </div>

                    <form wire:submit.prevent="nuevaCategoria" class="row g-3" id="formnuevaCategoria">
                        @csrf
                        <!-- Nombre categoria -->
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="nombreNuevo">
                                <span class="badge badge-dot bg-info me-1"></span> Nombre
                                @error('nombreNuevo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </label>
                            <input type="text" wire:model="nombreNuevo" id="nombreNuevo"
                                class="form-control @error('nombreNuevo') is-invalid @enderror" placeholder="Nombre" />
                        </div>

                        <!-- Es gratuita -->
                        <div class="col-12 col-md-2">
                            <label class="form-label">¿Es gratuita?</label>
                            <label class="switch switch-lg">
                                <input type="checkbox" wire:model="esGratuitaNuevo" class="switch-input" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">SI</span>
                                    <span class="switch-off">NO</span>
                                </span>
                            </label>
                        </div>

                        <!-- Valores de monedas (si no es gratuita) -->
                        @if (!$esGratuitaNuevo)
                            @foreach ($monedasActividad as $moneda)
                                <div class="col-lg-6 col-md-6 col-sm-12 mt-3">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <span class="badge badge-dot bg-info me-1"></span> Valor en:
                                            <b>{{ $moneda->nombre }}</b>
                                            @error('valoresMonedasNuevo.' . $moneda->id)
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </label>
                                        <input type="number" wire:model="valoresMonedasNuevo.{{ $moneda->id }}"
                                            class="form-control @error('valoresMonedasNuevo.' . $moneda->id) is-invalid @enderror">
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Materia para asociar al periodo -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <span class="badge badge-dot bg-info me-1"></span> Materia asociada para la matrícula
                            </label>
                            <div wire:ignore>
                                <select id="materiaPeriodoSelect" wire:model="materiaPeriodo"
                                    class="select2 form-select" data-placeholder="Seleccione una materia">
                                    <option value="">Seleccione una materia</option>
                                    @foreach ($materiasPeriodo as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->materia->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('materiaPeriodo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12 text-center mt-5">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                            <button type="reset" class="btn btn-label-secondary"
                                data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--/ carga del modal para crear una editar  categoria-->
    <div wire:ignore.self class="modal fade" id="modalEditarCategoria" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-plus"></i> Editar categoría </h3>
                        <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span> son
                            obligatorios </p>{{ $variable }}
                    </div>
                    <form id="formeditarCategoria" wire:submit.prevent="actualizarCategoria" class="row g-3">
                        @csrf
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nombreEditar">
                                <span class="badge badge-dot bg-info me-1"></span> Nombre
                                @error('nombreEditar')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </label>
                            <input type="text" wire:model.live="nombreEditar"
                                class="form-control @error('nombreEditar') is-invalid @enderror" />
                        </div>

                        <div class="col-12 col-md-2">
                            <label class="form-label" for="esGratuitaEditar">¿Es gratuita?</label>
                            <label class="switch switch-lg">
                                <input type="checkbox" wire:model="esGratuitaEditar" class="switch-input" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on">SI</span>
                                    <span class="switch-off">NO</span>
                                </span>
                                <span class="switch-label"></span>
                            </label>
                        </div>

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
                                        <input type="number" wire:model="valoresMonedasEditar.{{ $moneda->id }}"
                                            class="form-control @error('valoresMonedasEditar.' . $moneda->id) is-invalid @enderror">
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Materia para asociar al periodo -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <span class="badge badge-dot bg-info me-1"></span> Materia asociada para la matrícula
                            </label>
                            <div wire:ignore>
                                <select id="materiaPeriodoSelectEditar" wire:model="materiaPeriodoEditar"
                                    class="select2 form-select" data-placeholder="Seleccione una materia">
                                    <option value="">Seleccione una materia</option>
                                    @foreach ($materiasPeriodo as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->materia->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('materiaPeriodoEditar')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Cancelar</button>
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
        Livewire.on('abrirModal', () => {
            const modalId = event.detail.nombreModal;
            $(`#${modalId}`).modal('show');

            // Reiniciar Select2
            $(`#${modalId} .select2`).each(function() {
                $(this).select2({
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $(`#${modalId}`)
                });

                // Sincronizar valores con Livewire
                const livewireProperty = $(this).data('livewire-property');
                if (livewireProperty) {
                    const currentValue = @this.get(livewireProperty);
                    $(this).val(currentValue).trigger('change');
                }
            });
        });

        document.addEventListener('livewire:initialized', () => {
            // Inicializar Select2 después de que Livewire esté listo
            const materiaPeriodoSelect = $('#materiaPeriodoSelect');

            materiaPeriodoSelect.select2({
                placeholder: 'Seleccione una materia',
                allowClear: true,
                width: '100%'
            });

            // Manejar cambios en Select2
            materiaPeriodoSelect.on('change', function() {
                const selectedValue = $(this).val();
                @this.set('materiaPeriodo', selectedValue);
            });

            // Inicializar Select2 después de que Livewire esté listo editar
            const materiaPeriodoSelectEditar = $('#materiaPeriodoSelectEditar');

            materiaPeriodoSelectEditar.select2({
                placeholder: 'Seleccione una materia',
                allowClear: true,
                width: '100%'
            });

            // Manejar cambios en Select2
            materiaPeriodoSelectEditar.on('change', function() {
                const selectedValue = $(this).val();
                @this.set('materiaPeriodoEditar', selectedValue);
            });




            // Manejar eventos de Livewire
            Livewire.on('msn', () => {
                Swal.fire({
                    title: event.detail.msnTitulo,
                    html: event.detail.msnTexto,
                    icon: event.detail.msnIcono,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
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
                    title: '¿Estás seguro?',
                    text: "No podrás revertir esta acción",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarCategoria', event.categoriaId);
                    }
                });
            });
        });
    </script>
@endscript
