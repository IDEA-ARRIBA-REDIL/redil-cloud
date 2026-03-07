<div>
    {{-- Do your work, then step back. --}}
    <div class="row">
        <div class="col-lg-3 col-md-12 col-xs-12">
            <div class="card">
                <div class="card-header pb-0 w-100  ">
                    <div class="card-title mb-0">

                        <p><b>Listado de cargos asignados</b></p>
                    </div>
                </div>
                <div class="card-body pb-0 w-100  ">
                    @foreach ($cargosAsignados as $cargoAsignado)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $cargoAsignado->tipoCargo->nombre }}</span>
                            <span
                                class="badge rounded-pill badge-center h-px-10 w-px-10 bg-label-primary ms-1 mx-1">{{ $cargoAsignado->total }}</span>

                        </div>
                        <hr>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-9 col-md-12 col-xs-12">
            <!-- Familiar principal -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <p class="card-text text-uppercase fw-bold"><i class="ti ti-user ms-n1 me-2"></i>Buscar usuario</p>
                </div>
                <div class="card-body pb-20 row">
                    @livewire('Usuarios.usuarios-para-busqueda', [
                        'id' => 'buscador-usuario-servidores-actividad',
                        'tipoBuscador' => 'unico',
                        'conDadosDeBaja' => 'no',
                        'class' => 'col-12 col-md-12 mb-3',
                        'placeholder' => 'Seleccione un usuario',
                        'queUsuariosCargar' => 'todos',
                        'contenidoExtraListaPersonas' => true,
                        'cantUsuariosCargados' => 4,
                        'modulo' => 'anadir-encargados-actividad',
                    ])
                </div>

            </div>
            <!--/ Familiar principal -->
        </div>

    </div>
    <!-- modalNuevoAbono-->
    <div wire:ignore.self class="modal fade" id="modalNuevoCargo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-pencil-cog"></i> Gestionar cargos {{ $variable }}</h3>

                    </div>
                    <form id="formnuevoCargo" wire:submit.prevent="nuevoCargo" class="row g-3">
                        @csrf
                        <div class="form-group">
                            <label> Seleccione un tipo de cargo </label>
                            <select wire:model="selectTipoCargo" name="selectTipoCargo" class="form-select mt-2">
                                <option value="0"> Seleccione una opción</option>
                                @foreach ($tiposCargos as $cargo)
                                    <option value='{{ $cargo->id }}'> {{ $cargo->nombre }} </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-2">
                            <label> Descripción labor</label>
                            <textarea wire:model="descripcionCargo" name="descripcionCargo" max=1000 class="mt-2 form-control"></textarea>
                        </div>

                        <div class="col-12 text-center">
                            <button type="submit" class="btn rounded-pill btn-primary me-sm-3 me-1">Añadir
                                cargo</button>

                        </div>

                    </form>

                    <hr>
                    <center>
                        <h5>Listado de cargos actuales </h5>
                    </center>
                    @foreach ($tiposCargoUsuario as $cargoUsuario)
                        <div class="row mt-3 me-3 ms-3 p-5  ">
                            <div class=" col-lg-4 col-sm-12 mb-2">
                                <span> <b>{{ $cargoUsuario->tipoCargo->nombre }}</b></span>
                            </div>
                            <div class=" col-lg-4 col-sm-12  p-0">
                                {{ $cargoUsuario->descripcion }}
                            </div>
                            <div class=" col-lg-4 col-sm-12 mt-2">
                                <button wire:click="eliminarCargo({{ $cargoUsuario->id }})" type="button"
                                    class="btn ms-3 btn-editar-input btn-secondary float-end p-1_5">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <hr>
                <div class="modal-footer">
                    <div class="col-12 text-end">
                        <button @if (count($tiposCargoUsuario) == 0) disabled @endif wire:click="notificarPorEmail"
                            type="button" class="btn rounded-pill btn-success me-sm-3 me-1">Notificar por
                            e-mail</button>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--/ modalEditarCategoria -->
    <div wire:ignore.self class="modal fade" id="modalEditarCargo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"> Editar cargo </h3>
                    </div>



                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('abrirModal', () => {
                $('#' + event.detail.nombreModal).modal('show');

            });

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
        </script>
    @endscript

    <style>
        .list-usuarios :hover {
            background: #e4e4e4;
        }
    </style>



</div>
