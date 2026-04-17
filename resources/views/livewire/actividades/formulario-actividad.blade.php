<div>

    <!-- Botón para abrir modal -->
    <button type="button" class="btn rounded-pill btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalNuevoElemento">
        <i class="fa fa-plus me-1"></i> Crear elemento
    </button>

    <!-- Botón para abrir modal duplicar categoria -->
    <button type="button" class="btn rounded-pill btn-secondary mb-2" data-bs-toggle="modal" data-bs-target="#modalDuplicaeElemento">
        <i class="ti ti-folders me-1"></i> Duplicar elementos
    </button>


    <!-- Lista de elementos existentes -->
    <div class="h-100 mt-4">
        @if(count($elementos) > 0)
        <!-- AQUI DEBE TENER ESTE ELEMENTOS PORQUE ES DONDE LEE EL SCRIPT DEL DRAG AND DROP -->
        <div class="mt-4" id="elementos-container">
            @foreach($elementos as $elemento)
                @php
                    $borderColor = 'primary';
                    if($elemento->tipoElemento->clase == 'encabezado') $borderColor = 'secondary';
                    if(in_array($elemento->tipoElemento->clase, ['corta', 'larga'])) $borderColor = 'info';
                    if(in_array($elemento->tipoElemento->clase, ['fecha', 'numero', 'moneda'])) $borderColor = 'success';
                    if(in_array($elemento->tipoElemento->clase, ['archivo', 'imagen'])) $borderColor = 'warning';
                    if(in_array($elemento->tipoElemento->clase, ['unica_respuesta', 'multiple_respuesta', 'si_no'])) $borderColor = 'primary';
                @endphp

                <div class="card mb-3 draggable-item shadow-sm border-0" data-id="{{$elemento->id}}" style="border-left: 4px solid var(--bs-{{$borderColor}}) !important; transition: all 0.2s ease-in-out;">
                    <div class="d-flex align-items-stretch">
                        <div class="drag-handle d-flex align-items-center justify-content-center bg-lighter" style="width: 40px; cursor: grab; border-top-left-radius: 0.375rem; border-bottom-left-radius: 0.375rem;">
                            <i class="fas fa-grip-vertical text-muted"></i>
                        </div>
                        
                        <div class="card-body py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100">
                            <div class="flex-grow-1">
                                @if($elemento->tipoElemento->clase == 'encabezado')
                                    <h5 class="card-title fw-bold mb-1">{{$elemento->titulo}}</h5>
                                    <p class="card-text text-muted mb-0 small">{{$elemento->descripcion}}</p>
                                @else
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="badge bg-label-{{$borderColor}} me-2">{{$elemento->tipoElemento->nombre}}</span>
                                        @if($elemento->required)
                                            <span class="badge bg-label-danger me-2" title="Requerido"><i class="ti ti-star"></i></span>
                                        @endif
                                        @if(!$elemento->visible)
                                            <span class="badge bg-label-secondary me-2" title="Oculto al público"><i class="ti ti-eye-off"></i></span>
                                        @endif
                                        @if($elemento->visible_asistencia)
                                            <span class="badge bg-label-info me-2" title="Asistencia"><i class="ti ti-clipboard-check"></i></span>
                                        @endif
                                    </div>
                                    <h6 class="card-title mb-1" id='input-{{$elemento->tipoElemento->clase}}-{{$elemento->id}}'>{{$elemento->titulo}}</h6>
                                    @if($elemento->descripcion)
                                        <p class="card-text text-muted mb-0 small">{{$elemento->descripcion}}</p>
                                    @endif
                                    
                                    @if(in_array($elemento->tipoElemento->clase, ['unica_respuesta', 'multiple_respuesta']))
                                        <ul class="mt-2 mb-0 ps-3 text-muted small">
                                            @foreach($elemento->opciones as $opcion)
                                            <li>{{$opcion->valor_texto}}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endif
                            </div>

                            <div class="d-flex align-items-center mt-3 mt-md-0 ms-md-3">
                                <button wire:click="abrirOffcanvas({{$elemento->id}})" type="button" class="btn btn-sm btn-icon btn-label-primary me-2" title="Editar">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <button wire:click="confirmarEliminarElemento({{$elemento->id}})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="Eliminar">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <p>No se han creado elementos</p>
        @endif
    </div>



    <!-- este es el off canvas que se abre a la derecha para editar un elemento -->
    <form id="formeditarElemento" wire:submit.prevent="ActualizarElemento" class="row g-3">
        <div wire:ignore.self class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar" aria-labelledby="modalSeccion1Label">
            <div class="offcanvas-header my-1 px-8">
                <h4 class="offcanvas-title fw-bold text-primary" id="modalSeccion1Label">Editar elemento</h4>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-6 px-8">
                @csrf
                @if($elementoSeleccionado)
                <div class="p-4">
                    <div class="mb-4">
                        <span class="text-black ti-14px mb-4">Actualiza la configuración de tu elemento o pregunta.</span>
                    </div>
                    <!-- Titulo del elemento -->
                    <div id='container-titulo' class="form-floating mb-4">
                        <input class='form-control' id='elementoTitulo' placeholder="Título" wire:model="elementoTitulo">
                        <label for="elementoTitulo">Titulo del elemento o pregunta</label>
                    </div>

                    <!-- descripcion -->
                    <div id='container-descripcion' class="form-floating mb-4">
                        <textarea max=500 class='form-control' placeholder="Descripción" wire:model="elementoDescripcion" id='elementoDescrpcion' style="height: 100px">{!!trim($elementoSeleccionado->descripcion)!!}</textarea>
                        <label for="elementoDescrpcion">Descripción elemento</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label mb-2 d-block">Ajustes Generales</label>
                        <!-- es requerido -->
                        <div class="form-check form-switch mb-3 bg-lighter p-2 rounded border d-flex justify-content-between align-items-center">
                            <label class="form-check-label ms-0 fw-medium" for="elementoRequired">Requerido <br><small class="text-muted fw-normal text-wrap" style="font-size:0.75rem;">El usuario debe llenarlo obligatoriamente</small></label>
                            <input class="form-check-input" type="checkbox" role="switch" wire:model="elementoRequired" id="elementoRequired">
                        </div>

                        <!-- es visible -->
                        <div class="form-check form-switch mb-3 bg-lighter p-2 rounded border d-flex justify-content-between align-items-center">
                            <label class="form-check-label ms-0 fw-medium" for="elementoVisible">Visible <br><small class="text-muted fw-normal text-wrap" style="font-size:0.75rem;">Se muestra en el formulario de inscripción</small></label>
                            <input class="form-check-input" type="checkbox" role="switch" wire:model="elementoVisible" id="elementoVisible">
                        </div>

                        <!-- es visible asistencia-->
                        <div class="form-check form-switch mb-3 bg-lighter p-2 rounded border d-flex justify-content-between align-items-center">
                            <label class="form-check-label ms-0 fw-medium" for="elementoVisibleAsistencia">Visible en Toma de Asistencia <br><small class="text-muted fw-normal text-wrap" style="font-size:0.75rem;">Los consolidadores verán este campo</small></label>
                            <input class="form-check-input" type="checkbox" role="switch" wire:model="elementoVisibleAsistencia" id="elementoVisibleAsistencia">
                        </div>
                    </div>


                    <!-- configuracion por elemento -->
                    @if($tipoElemento->tiene_respuesta == true)
                    <!-- tipo elemento seleccionado -->
                    <div wire:ignore id='container-tipo' class="mb-3 form-group">
                        <label class="form-label">
                            Tipo Elemento
                        </label>
                        <select required wire:model="elementoTipo" name="elementoTipo" class="select2 form-select">
                            <option value='0'>Seleccione una opción</option>
                            @foreach($tipos as $tipo)
                            <option @if($tipo->id == $tipoElemento->id) selected @endif value="{{$tipo->id}}">{{$tipo->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($tipoElemento->clase == 'corta' || $tipoElemento->clase == 'larga' || $tipoElemento->clase == 'numerico' )
                    <div id="container-long-max">
                        <label class="form-label">
                            Longitud maxima
                        </label>
                        <input class='form-control' value="{{$elementoSeleccionado->long_max}}" id='elementoLongitudMax'>
                    </div>

                    <div id="container-long-min">
                        <label class="form-label">
                            Longitud minima
                            <input class='form-control' value="{{$elementoSeleccionado->long_min}}" id="elementoLongitudMin">
                    </div>
                    @endif

                    @if($tipoElemento->clase == 'moneda' )
                    <div id="container-long-max">
                        <label class="form-label">
                            Monto maximo
                        </label>
                        <input class='form-control' value="{{$elementoSeleccionado->long_max}}" id='elementoLongitudMax'>
                    </div>

                    <div id="container-long-min">
                        <label class="form-label">
                            Monto minimo
                            <input class='form-control' value="{{$elementoSeleccionado->long_min}}" id="elementoLongitudMin">
                    </div>
                    @endif

                    @if($tipoElemento->clase == 'archivo')
                    <div id="container-long-max">
                        <label class="form-label">
                            Tamaño maximo MB
                        </label>
                        <input type="number" wire:model="pesoMaximoArchivo" class='form-control' name="pesoMaximoArchivo" id="pesoMaximoArchivo">
                    </div>
                    @endif

                    @if($tipoElemento->clase == 'imagen' )
                    <div id="container-long-max">
                        <label class="form-label">
                            Tamaño maximo MB
                        </label>
                        <input type="number" wire:model="pesoMaximo" class='form-control' name="pesoMaximo" id="pesoMaximo">
                    </div>

                    <div id="container-long-min">
                        <label class="form-label">Dimensiones:</label>

                        <label class="form-label">Ancho:</label>
                        <input class='form-control' wire:model="anchoImagen" name="anchoImagen" id="anchoImagen">

                        <label class="form-label">Alto:</label>
                        <input class='form-control' wire:model="altoImagen" name="altoImagen" id="altoImagen">
                    </div>
                    @endif

                    @if($tipoElemento->clase == 'unica_respuesta' || $tipoElemento->clase == 'multiple_respuesta')

                    <div class="border p-4 rounded mt-3 bg-lighter">
                        <label class="form-label d-block mb-3 fw-medium">Opciones de Respuesta</label>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <!-- AQUI EL CARGA LAS OPCIONES QUE VIENEN POR BASE DE DATOS -->
                            @foreach($opcionesElementosActualizadas as $opcion)
                            <span class="badge bg-primary d-flex align-items-center rounded-pill py-2 px-3 shadow-sm" style="font-size: 0.85rem">
                                {{ $opcion->valor_texto }}
                                <i wire:click="removeOpcion('{{ $opcion->valor_texto }}')" class="ti ti-x ms-2 cursor-pointer bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:16px; height:16px; font-size: 0.65rem;"></i>
                            </span>
                            @endforeach
                        </div>
                        <div class="input-group">
                            <input type="text" wire:model="nuevaOpcion" wire:keydown.space.prevent="addOpcion" wire:keydown.enter.prevent="addOpcion" placeholder="Escribe y presiona Enter o Espacio..." class="form-control">
                            <button class="btn btn-primary" type="button" wire:click="addOpcion">
                                <i class="ti ti-plus me-1"></i> Añadir
                            </button>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
                @endif

            </div>
            <div class="offcanvas-footer p-5  border-top border-2 px-8">
                <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Guardar</button>
                <button type="button" data-bs-dismiss="offcanvas" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
            </div>

        </div>

    </form>


    <!-- modalNuevoElemento-->
    <div wire:ignore.self class="modal fade" id="modalNuevoElemento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-folders"></i> Nuevo elemento </h3>

                    </div>
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input required type="text" wire:model="titulo" class="form-control" placeholder="Ingrese título">
                        @error('titulo')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select required wire:model="tipo_elemento_id" class="select2 form-select">
                            <option value='0'>Seleccione una opción</option>
                            @foreach($tipos as $tipo)
                            <option value="{{$tipo->id}}">{{$tipo->nombre}}</option>
                            @endforeach

                        </select>
                        @error('tipo_elemento_id')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea wire:model="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="required" id="required">
                                <label class="form-check-label" for="required">
                                    Requerido
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="visible" id="visible">
                                <label class="form-check-label" for="visible">
                                    Visible
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="visible_asistencia" id="visible_asistencia">
                                <label class="form-check-label" for="visible_asistencia">
                                    Visible en Asistencia
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-12 text-center">
                        <button wire:click="guardar" type="button" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- modalDupliarElemento-->
    <div wire:ignore.self class="modal fade" id="modalDuplicaeElemento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-l modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-plus"></i> Duplicar elementos </h3>
                        <p class="text-muted">elige una actividad de la que deseas duplicar sus elementos </p>
                    </div>
                    <div class="col-12 mb-3 ">
                        <select wire:model.live="actividadIduplicar" x-data="{
                        init() {
                            $(this.$refs.select).select2({
                                placeholder: 'Selecciona una actividad',
                                allowClear: true
                            });
                            $(this.$refs.select).on('change', () => {
                                @this.set('actividadIduplicar', $(this.$refs.select).val())
                            });
                        }
                    }" x-ref="select" class="select2 form-select">

                            <option value="">Seleccione una actividad</option>
                            @foreach ($actividadesTotales as $actividadOriginal)
                            <option value='{{$actividadOriginal->id}}'> {{$actividadOriginal->nombre}} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer pb-0 mt-10">
                        <div class="col-12 text-center">
                            <button wire:click="duplicarElemento({{$actividad->id}})" type="button" class="btn btn-primary me-sm-3 me-1">Duplicar</button>
                            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        @assets
        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/select2/select2.scss',])

        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/select2/select2.js']);
        @endassets


        @script
        <script>
            $(document).ready(function() {
                // Inicializar Select2 pero solo del modal de nueva categoria
                $('#modalDuplicaeElemento .select2').select2({
                    allowClear: true
                    , width: '100%'
                    , dropdownParent: $('#modalDuplicaeElemento')

                });

            });

            /// para abir eloff canvas de la izquierda
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('abrirOffcanvas', () => {
                    const modals = document.querySelectorAll('.modal');
                    modals.forEach(modal => {
                        new bootstrap.Modal(modal);
                    });

                    const offcanvas = document.getElementById('addEventSidebar');
                    const bsOffcanvas = new bootstrap.Offcanvas(offcanvas);
                    bsOffcanvas.show();

                    // Agregar backdrop
                    const backdrop = document.createElement('div');
                    backdrop.className = 'offcanvas-backdrop fade show';
                    document.body.appendChild(backdrop);

                    // Remover backdrop al cerrar
                    offcanvas.addEventListener('hidden.bs.offcanvas', () => {
                        backdrop.remove();
                    });
                });
            });


            // ESTO SON PARA LOS SWEET FIRE CUANDO SE ACABE CADA ELEMENTO
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
            // ESTO ES PARA CERRAR EL MODAL
            Livewire.on('cerrarModal', () => {
                $('#' + event.detail.nombreModal).modal('hide');
                $(".select2").val('').trigger('change')
            });

            //// PARA QUE ESTO FUNCIONE DEBE CARGARSE LA LIBRERIA  SORTABLE.MIN
            document.addEventListener('livewire:initialized', () => {
                const container = document.getElementById('elementos-container');
                if (container) {
                    ///AQUI ES EL SCRIPT QUE HACE LA FUNCION DE DRAG AND DROP
                    Sortable.create(container, {
                        animation: 150
                        , handle: '.drag-handle'
                        , onEnd: function(evt) {
                            //AQUI OBTIENE TODOS LOS ITEMS QUE SON DE TIPO DRAG AND DROP
                            const items = container.getElementsByClassName('draggable-item');
                            /// AQUI HACE UN RECORRIDO POR CADA UNO Y OBTIENE LA PROPUEDAD DATA-ID
                            const orderedIds = Array.from(items).map(item => item.dataset.id);
                            /// AQUI LO QUE HACE ES QUE EJECUTA LA FUNCION EN EL CONTROLADOR DEL LIVEWIRE, POR MEDIO DE ON
                            Livewire.dispatch('updateOrders', {
                                orderedIds: orderedIds
                            });
                        }
                    });
                }
            });


            /// confirmar eliminar elemento
            Livewire.on('confirmarEliminarElemento', (event) => {
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
                        console.log('Evento recibido:', event.elementoId); // Añade este log para depuración
                        @this.call('eliminarElemento', event.elementoId);
                    }
                });
            });

        </script>
        @endscript

        {{-- Success is as dangerous as failure. --}}
    </div>
