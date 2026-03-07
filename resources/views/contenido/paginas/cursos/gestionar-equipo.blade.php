@extends('layouts.layoutMaster')

@section('title', 'Gestionar Equipo del Curso')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/ui-modals.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Confirmación de Eliminación
            const deleteForms = document.querySelectorAll('.form-eliminar-miembro');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const miembroNombre = this.dataset.nombre || 'este miembro';

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: `¿Realmente deseas remover a ${miembroNombre} del curso? ¡No podrás revertir esto!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, ¡remover!',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            // Confirmación Genérica (Activar/Desactivar)
            const confirmActionForms = document.querySelectorAll('.form-confirmar-accion');
            confirmActionForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const mensajeConfirmacion = this.dataset.msjConfirmacion || '¿Estás seguro?';

                    Swal.fire({
                        title: 'Confirmar Acción',
                        text: mensajeConfirmacion,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-primary me-3',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            // Limpiar Offcanvas de Creación al cerrar
            const offcanvasCrear = document.getElementById('offcanvasCrearMiembro');
            if (offcanvasCrear) {
                offcanvasCrear.addEventListener('hidden.bs.offcanvas', event => {
                    const formCrear = document.getElementById('formCrearMiembro');
                    if (formCrear) {
                        formCrear.reset();
                    }
                });
            }
        });
    </script>
@endsection

@section('content')
    @include('layouts.status-msn')


        <a href="{{ route('cursos.gestionar') }}" class="float-end btn btn-outline-primary">
                <i class="ti ti-arrow-left me-1"></i> Volver al Listado
            </a>


    <div class="row mb-4 mt-4 align-items-center">
        <div class="col-12">
            <h4 class="mb-1 fw-semibold text-primary">Gestionar equipo (creadores y asesores)</h4>
            <div class="text-black small">Asigna los creadores y asesores para: {{ $curso->nombre }}</div>
                  <button class="mt-3 btn btn-primary rounded-pill" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasCrearMiembro" aria-controls="offcanvasCrearMiembro">
                <i class="ti ti-plus me-1"></i> Asignar miembro
            </button>
        </div>

    </div>

    <div class="row equal-height-row ">
        @forelse ($equipo as $miembro)
            <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-4 equal-height-col">
                <div class="card h-100 border rounded">
                    {{-- Portada Banner --}}
                    <img class="card-img-top object-fit-cover" style="height: 100px;"
                        src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/banner-usuario/' . $miembro->user->portada) : Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/banner-usuario/' . $miembro->user->portada) }}"
                        alt="portada {{ $miembro->user->primer_nombre ?? '' }}" />

                    <div class="card-body">
                        <div class="user-profile-header d-flex flex-row text-start mb-2 ">
                            <div class="flex-grow-1 mt-n10 mx-auto text-start">
                                @if (in_array($miembro->user->foto, ['default-m.png', 'default-f.png']))
                                    <div class="avatar avatar-xl">
                                        <span class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                            {{ $miembro->user->inicialesNombre() }} </span>
                                    </div>
                                @else
                                    <div class="avatar avatar-xl">
                                        <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $miembro->user->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $miembro->user->foto }}"
                                            alt="{{ $miembro->user->foto ?? 'foto' }}"
                                            class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                    </div>
                                @endif

                                <div class="d-flex align-items-start justify-content-between mt-2">
                                    <div class="align-items-center">
                                        <h5 class="mb-0 fw-semibold">
                                            {{ $miembro->user->nombre(3) ?? 'Usuario no encontrado' }}
                                        </h5>
                                        <span class="badge px-3 py-1 rounded-pill bg-label-{{ $miembro->activo ? 'primary' : 'secondary' }}">
                                            {{ $miembro->activo ? 'Activo' : 'Inactivo' }}
                                        </span><br>
                                    </div>
                                    <div style="border-radius: 50%" class="dropdown zindex-2 mx-1 border rounded p-1">
                                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical text-black rounded-circle "></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if ($miembro->activo)
                                                <li>
                                                    <form action="{{ route('cursos.equipo.desactivar', $miembro->id) }}"
                                                        method="POST" class="form-confirmar-accion"
                                                        data-msj-confirmacion="¿Seguro que deseas desactivar a este miembro?">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-dark">Desactivar</button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('cursos.equipo.activar', $miembro->id) }}"
                                                        method="POST" class="form-confirmar-accion"
                                                        data-msj-confirmacion="¿Seguro que deseas activar a este miembro?">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-dark">Activar</button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li>
                                                <form action="{{ route('cursos.equipo.eliminar') }}" method="POST"
                                                    class="form-eliminar-miembro"
                                                    data-nombre="{{ $miembro->user->nombre(3) ?? 'Miembro ID ' . $miembro->id }}">
                                                    @csrf
                                                    <input type="hidden" name="miembro_id" value="{{ $miembro->id }}">
                                                    <button type="submit" class="dropdown-item">Eliminar</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4 mb-4 g-3">
                            <div class="col-6 p-0">
                                <div class="d-flex flex-column">
                                    <small class="text-dark"><i class="ti ti-mail text-black me-2"></i> Email:</small>
                                    <small class="fw-semibold text-black text-truncate" title="{{ $miembro->user->email ?? '' }}">
                                        {{ $miembro->user->email ?? '' }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-6 p-0">
                                <div class="d-flex flex-column">
                                    <small class="text-dark"><i class="ti ti-user text-black me-2"></i> Cargo:</small>
                                    @if($miembro->tipoCargo)
                                        <span class="w-75 badge rounded-pill bg-label-info mt-1">{{ $miembro->tipoCargo->nombre }}</span>
                                    @else
                                        <span class="w-75 badge rounded-pill bg-label-secondary mt-1">N/A</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i> No se encontraron miembros asignados al equipo de este curso.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="d-flex justify-content-center mt-4">
        @if (isset($equipo) && $equipo->count())
            {{ $equipo->appends(request()->query())->links() }}
        @endif
    </div>

    {{-- Offcanvas para Asignar Nuevo Miembro --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCrearMiembro" aria-labelledby="offcanvasCrearMiembroLabel">
        <div class="offcanvas-header">
            <h4 id="offcanvasCrearMiembroLabel" class="offcanvas-title text-primary fw-semibold">Asignar miembro</h4>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <form id="formCrearMiembro" class="d-flex flex-column" method="POST" action="{{ route('cursos.equipo.guardar', $curso) }}">
            @csrf
            <div class="offcanvas-body flex-grow-1">
                {{-- Buscador de Usuarios --}}
                <div class="mb-4">
                    @livewire('usuarios.usuarios-para-busqueda', [
                        'id' => 'usuario_id',
                        'tipoBuscador' => 'unico',
                        'conDadosDeBaja' => 'no',
                        'label' => 'Seleccionar usuario (*)',
                        'placeholder' => 'Buscar por nombre o identificación...',
                        'queUsuariosCargar' => 'todos',
                    ])
                    @error('usuario_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Cargo del Miembro --}}
                <div class="mb-4">
                    <label for="tipo_cargo_curso_id" class="form-label">Cargo a asignar (*)</label>
                    <select required class="form-select" id="tipo_cargo_curso_id" name="tipo_cargo_curso_id">
                        <option value="">Selecciona un cargo...</option>
                        @foreach ($tiposCargo as $cargo)
                            <option value="{{ $cargo->id }}">{{ $cargo->nombre }}</option>
                        @endforeach
                    </select>
                    @error('tipo_cargo_curso_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Estado Activo/Inactivo --}}
                <div class="mb-3">
                    <label class="form-label">Estado Inicial</label>
                    <div class="form-check form-switch form-switch-lg">
                        <input type="hidden" name="activo" value="0">
                        <input class="form-check-input" type="checkbox" name="activo" id="switchActivo" value="1" checked>
                        <label class="form-check-label" for="switchActivo">Activo</label>
                    </div>
                </div>
            </div>

            {{-- Footer con los botones de acción --}}
            <div class="offcanvas-footer border-top p-3">
                <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Guardar asignación</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect " data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </form>
    </div>
@endsection

@section('page-script')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Livewire.on('usuario-seleccionado', (event) => {
                // event.id contains the selected user's ID, or null if unselected
                const inputUsuarioId = document.getElementById('usuario_id');
                if (inputUsuarioId) {
                    inputUsuarioId.value = event.id ? event.id : '';
                }
            });
        });
    </script>
@endsection
