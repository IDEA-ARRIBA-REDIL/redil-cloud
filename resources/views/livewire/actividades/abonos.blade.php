<div>

    <div class="row mb-5 mt-5">
        <div class="me-auto ">
            <button type="button" class="btn rounded-pill float-end btn-primary rounded-pill waves-effect waves-light"
                data-bs-toggle="modal" data-bs-target="#modalNuevoAbono">
                <i class="ti ti-plus"></i> Nuevo abono
            </button>
        </div>
    </div>
    <div id="container-categorias" class="row flex-row">
        {{-- =================================================================== --}}
        {{-- ======================= PANEL IZQUIERDO (OPTIMIZADO) ============== --}}
        {{-- =================================================================== --}}
        <div id='container-left' class="col-sm-12 col-lg-3">
            <div class="card p-7">
                @foreach ($actividad->categorias as $categoria)
                    <div class="mb-4">
                        <h4>{{ $categoria->nombre }}</h4>
                        @foreach ($categoria->monedas as $moneda)
                            <div class="mt-3">
                                <p class="mb-1">
                                    {{ $moneda->nombre == 'Pesos colombianos' ? 'Valor en Pesos' : 'Valor en Dolares' }}
                                </p>
                                <div class="d-flex justify-content-between">
                                    <span>Valor total {{ $moneda->nombre_corto }}</span>
                                    <b>${{ number_format($moneda->pivot->valor) }}</b>
                                </div>
                                {{-- VERSIÓN CORREGIDA Y OPTIMIZADA --}}
                                @php
                                    $datosAbono = $abonosExistentesPorCategoria[$categoria->id][$moneda->id] ?? [
                                        'restante' => 0,
                                    ];
                                    $valorResultado = $datosAbono['restante'];
                                @endphp
                                <div
                                    class="d-flex justify-content-between {{ $valorResultado <= 0 ? 'text-success' : 'text-danger' }}">
                                    <span>Valor restante {{ $moneda->nombre_corto }}</span>
                                    <b>${{ number_format($valorResultado) }}</b>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if (!$loop->last)
                        <hr>
                    @endif
                @endforeach
            </div>
        </div>
        {{-- =================================================================== --}}
        {{-- ======================= PANEL IZQUIERDO (FIN) ===================== --}}
        {{-- =================================================================== --}}

        <div id='container-right' class="col-lg-8 col-sm-12 flex-fill">
            @php $contador = 1; @endphp
            @if (count($abonosActividad) > 0)
                @foreach ($abonosActividad as $abono)
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h5 class="text-primary d-flex justify-content-between align-items-center">
                                <b>Abono {{ $contador }}</b>
                                <div class="dropdown">
                                    <button class="btn btn-text-secondary btn-sm rounded-pill text-muted border-0 p-2"
                                        type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class="ti ti-dots-vertical ti-md"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);"
                                            wire:click="abrirModalActualizarAbono('{{ $abono->id }}')"
                                            class="dropdown-item">Editar</a>
                                        <a href="javascript:void(0);"
                                            wire:click="confirmarEliminarAbonoCategoria('{{ $abono->id }}')"
                                            class="dropdown-item">Eliminar</a>
                                    </div>
                                </div>
                            </h5>
                            <p>Del
                                <b>{{ \Carbon\Carbon::parse($abono->fecha_inicio)->format('d-m-Y') }}</b>
                                al
                                <b>{{ \Carbon\Carbon::parse($abono->fecha_fin)->format('d-m-Y') }}</b>
                            </p>
                        </div>
                        <div class="card-body">
                            @php
                                $pagosAgrupados = $abono->abonoCategorias->groupBy('moneda.nombre');
                            @endphp

                            <div class="row">
                                @foreach ($pagosAgrupados as $moneda => $listaDePagos)
                                    <div class="col-12 mb-2 mt-3">
                                        <h5 class="pb-1">Pagos en {{ $moneda }}</h5>
                                    </div>
                                    @foreach ($listaDePagos as $abonoCategoria)
                                        <div class="col-lg-6  col-sm-12  mb-3">
                                            ID ACTIVIDAD:{{ $abonoCategoria->categoria->actividad_id }} // ID ABONO
                                            ACTIVIDAD:{{ $abonoCategoria->id }}
                                            <div class="me-2">
                                                <span class="text-black">
                                                    <b>{{ $abonoCategoria->categoria->nombre }}</b>
                                                </span>
                                            </div>
                                            <p style="border-bottom: 2px solid #e6e6e8 !important;" class="pb-1 ps-1">
                                                <i class="ti ti-currency-dollar me-2"></i>
                                                <span>${{ number_format($abonoCategoria->valor, 2) }} </span>
                                            </p>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @php $contador++; @endphp
                @endforeach
            @else
                <div class="card">
                    <div class="card-body">
                        <h4 class="m-3">No se encontraron abonos para esta actividad</h4>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- ======================= MODAL NUEVO ABONO ========================= --}}
    {{-- =================================================================== --}}
    <div wire:ignore.self class="modal fade" id="modalNuevoAbono" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content p-0">
                <div class="modal-header border-bottom ">
                    <div class="text-start mb-4">
                        <h4 class="mb-2 tittle text-black fw-semibold"> Nuevo abono</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <form wire:submit="nuevoAbono()">
                    @csrf
                    <div style="padding:5%" class="modal-body row">
                        <div class="ps-0 col-12 col-md-6">
                            <label class="form-label" for="fecha_inicio_abono">Fecha inicio de abono</label>
                            <input required id="fecha_inicio_abono" type="date" wire:model.live="fecha_inicio_abono"
                                class="form-control" />
                            @error('fecha_inicio_abono')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="fecha_fin_abono">Fecha fin de abono</label>
                            <input required id="fecha_fin_abono" type="date" wire:model.live="fecha_fin_abono"
                                min="{{ $fecha_inicio_abono ?? '' }}" class="form-control" />
                            @error('fecha_fin_abono')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <h5 class="ps-0 mb-2 mt-4 text-black">Por favor, especifica los valores por categoría</h5>

                        @if ($esGratuita == false)
                            @foreach ($categoriasActividad as $categoria)
                                <div class="row card-body mt-3">
                                    <label class="form-label fw-bolder">{{ $categoria->nombre }}</label>
                                    @foreach ($categoria->monedas as $moneda)
                                        <div class="col-lg-6 col-md-6 col-sm-12 mt-3 pe-0">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="valoresMonedasNuevo.{{ $categoria->id }}.{{ $moneda->id }}">
                                                    Valor en: <b>{{ $moneda->nombre }}</b>
                                                </label>
                                                <input required type="number"
                                                    placeholder="{{ $moneda->nombre_corto }}"
                                                    wire:model.live="valoresMonedasNuevo.{{ $categoria->id }}.{{ $moneda->id }}"
                                                    wire:key="valoresMonedasNuevo.{{ $categoria->id }}.{{ $moneda->id }}"
                                                    class="form-control" min="0" step="0.01">
                                                @error('valoresMonedasNuevo')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="modal-footer border-top">
                        <div class="col-12 mt-5 text-end">
                            <button type="submit" class="btn rounded-pill btn-primary me-sm-3 me-1">Guardar</button>
                            <button type="reset" class="btn rounded-pill btn-outline-secondary"
                                data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- ======================= MODAL EDITAR ABONO ======================== --}}
    {{-- =================================================================== --}}
    <div wire:ignore.self class="modal fade" id="modalEditarAbono" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content p-0">
                <div class="modal-header border-bottom">
                    <div class="text-start">
                        <h4 class="mb-2 tittle text-black fw-semibold"> Editar abono</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="actualizarAbono()">
                    @csrf
                    <div style="padding:5%" class="modal-body row">
                        <div class="ps-0 col-12 col-md-6">
                            <label class="form-label" for="fechaInicioAbonoEditar">Fecha inicio de abono</label>
                            <input required id="fechaInicioAbonoEditar" type="date"
                                wire:model.live="fechaInicioAbonoEditar" class="form-control" />
                            @error('fechaInicioAbonoEditar')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="fechaFinAbonoEditar">Fecha fin de abono</label>
                            <input required id="fechaFinAbonoEditar" type="date"
                                wire:model.live="fechaFinAbonoEditar" min="{{ $fechaInicioAbonoEditar ?? '' }}"
                                class="form-control" />
                            @error('fechaFinAbonoEditar')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <h5 class="ps-0 mb-2 mt-4 text-black">Por favor, especifica los valores por categoría</h5>

                        @if ($esGratuita == false)
                            @foreach ($categoriasActividad as $categoria)
                                <div class="row card-body mt-3">
                                    <label class="form-label fw-bolder">{{ $categoria->nombre }}</label>
                                    @foreach ($categoria->monedas as $moneda)
                                        <div class="col-lg-6 col-md-6 col-sm-12 mt-3 pe-0">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="valoresMonedasEditar.{{ $categoria->id }}.{{ $moneda->id }}">
                                                    Valor en: <b>{{ $moneda->nombre }}</b>
                                                </label>
                                                {{-- ESTE INPUT AHORA FUNCIONARÁ CORRECTAMENTE --}}
                                                <input required type="number"
                                                    placeholder="{{ $moneda->nombre_corto }}"
                                                    wire:model.live="valoresMonedasEditar.{{ $categoria->id }}.{{ $moneda->id }}"
                                                    wire:key="valoresMonedasEditar.{{ $categoria->id }}.{{ $moneda->id }}"
                                                    class="form-control" min="0" step="0.01">
                                                @error("valoresMonedasEditar.{$categoria->id}.{$moneda->id}")
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="modal-footer border-top">
                        <div class="col-12 mt-3 text-end">
                            <button type="submit" class="btn rounded-pill btn-primary me-sm-3 me-1">Guardar
                                Cambios</button>
                            <button type="reset" class="btn rounded-pill btn-outline-secondary"
                                data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- ======================== ASSETS Y ESTILOS ========================= --}}
    {{-- =================================================================== --}}
    @assets
        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js']);
    @endassets

    <style>
        .modal .modal-header .btn-close {
            position: absolute;
            top: 2rem;
            right: 2rem;
        }
    </style>

    @script
        <script>
            document.addEventListener('livewire:navigated', () => {
                /**
                 * ---------------------------------------------------------------------
                 * INICIALIZACIÓN DE DATOS Y ESTADO
                 * ---------------------------------------------------------------------
                 */

                // Se pasa el array de valores faltantes desde PHP a JS.
                // Se usa 'let' para que la variable pueda ser actualizada más tarde.
                let valoresFaltantes = @json($valoresFaltantes);

                // Bandera para evitar que la alerta del navegador se muestre después de confirmar la nuestra.
                let navegacionConfirmada = false;

                /**
                 * ---------------------------------------------------------------------
                 * LÓGICA DE ALERTA AL SALIR DE LA PÁGINA
                 * ---------------------------------------------------------------------
                 */

                // Función que construye y muestra el SweetAlert con los datos dinámicos.
                function mostrarAlertaSalida(urlDestino) {
                    let mensajeHtml = '<p class="text-center mb-2">Aún tienes valores pendientes por completar:</p>';
                    mensajeHtml += '<ul class="list-group list-group-flush">';

                    valoresFaltantes.forEach(item => {
                        let valorFormateado = new Intl.NumberFormat('es-CO', {
                            style: 'currency',
                            currency: item.moneda
                        }).format(item.valor);
                        mensajeHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    Faltan en <strong>${item.categoria}</strong>:
                                    <span class="badge bg-danger rounded-pill text-white">${valorFormateado}</span>
                               </li>`;
                    });

                    mensajeHtml +=
                        '</ul><p class="text-center mt-3">Si sales ahora, la información de pagos estará incompleta.</p>';

                    Swal.fire({
                        title: '¿Estás seguro de que quieres salir?',
                        html: mensajeHtml,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, salir de todas formas',
                        cancelButtonText: 'Quedarme'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Si el usuario confirma, se activa la bandera y se navega a la URL.
                            navegacionConfirmada = true;
                            window.location.href = urlDestino;
                        }
                    });
                }

                // Interceptor para clics en enlaces <a>.
                document.addEventListener('click', function(event) {
                    const link = event.target.closest('a');
                    const esEnlaceNavegacion = link && link.href && !link.href.startsWith('javascript:') && link
                        .target !== '_blank';

                    if (esEnlaceNavegacion && valoresFaltantes.length > 0) {
                        event.preventDefault(); // Detiene la navegación.
                        mostrarAlertaSalida(link.href); // Muestra la alerta personalizada.
                    }
                });

                // Interceptor para acciones del navegador (recargar, cerrar pestaña, botón atrás).
                window.onbeforeunload = function(event) {
                    if (!navegacionConfirmada && valoresFaltantes.length > 0) {
                        event.preventDefault(); // Requerido por algunos navegadores.
                        return 'Tienes valores pendientes. ¿Seguro que quieres salir?'; // Mensaje para el diálogo genérico del navegador.
                    }
                };

                /**
                 * ---------------------------------------------------------------------
                 * LISTENERS DE EVENTOS DE LIVEWIRE
                 * ---------------------------------------------------------------------
                 */

                // Listener para actualizar la variable JS cuando el backend la modifica.
                Livewire.on('valoresFaltantesActualizados', (event) => {
                    valoresFaltantes = event.valores;
                    console.log('✅ Valores faltantes actualizados en el cliente: ', valoresFaltantes);
                });

                // Listener para abrir modales de Bootstrap.
                $wire.on('abrirModal', (event) => {
                    const modal = new bootstrap.Modal(document.getElementById(event.nombreModal));
                    modal.show();
                });

                // Listener para mostrar alertas generales de SweetAlert.
                Livewire.on('msn', (event) => {
                    Swal.fire({
                        title: event.msnTitulo,
                        html: event.msnTexto,
                        icon: event.msnIcono,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                });

                // Listener para cerrar modales de Bootstrap.
                Livewire.on('cerrarModal', (event) => {
                    const modalElement = document.getElementById(event.nombreModal);
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                });

                // Listener para la confirmación de eliminación.
                Livewire.on('confirmarEliminarAbonoCategoria', (event) => {
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
                            @this.call('eliminarAbonoCategoria', event.abonoCategoriaId);
                        }
                    });
                });
            });
        </script>
    @endscript

</div>
