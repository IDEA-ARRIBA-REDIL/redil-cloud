<div>

    <!-- Role cards -->
    <div class="row g-4">
        <div class="d-flex flex-row-reverse">
            <button data-bs-target="#addRoleModal" wire:click="abrirFormularioAddRol"
                class="btn btn-primary rounded-pill px-7 py-2"><i class="ti ti-plus"></i>
                Nuevo rol </button>
        </div>
        <div class="col-xl-6 offset-xl-3 col-12">
            <div class="input-group">
                <input wire:model.live.debounce.500ms="busqueda" type="text" class="form-control" id="busqueda"
                    name="busqueda" placeholder="Buscar">
            </div>
        </div>
    </div>
    <div class="row equal-height-row  g-4 mt-2">

        @if ($roles)
            @foreach ($roles as $rol)
                <div class=" col equal-height-col col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            @if (isset($rol->icono))
                                <i class="{{ $rol->icono }} ti-lg"></i>
                            @else
                                <i class="ti ti-user-question ti-lg"></i>
                            @endif
                            <h5 class="mb-1"> {{ $rol->name }}</h5>
                            <div class="d-flex justify-content-between align-items-end mt-1">
                                <div class="role-heading flex-fill">
                                    <p>Activo</p>
                                </div>
                                <div>
                                    <a href="{{ route('configuracion.editar-permisos-rol', ['role' => $rol->id]) }}" class="text-muted"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-original-title="Actualizar permisos"><i class="ti ti-checkbox "></i></a>
                                    <a href="javascript:void(0);" class="text-muted"
                                        wire:click="abrirFormularioEditarRol({{ $rol->id }})"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-original-title="Editar rol"><i class="ti ti-edit "></i></a>
                                    <a href="javascript:void(0);" class="text-muted"
                                        wire:click="duplicarRol({{ $rol->id }})" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-original-title="Duplicar rol"><i
                                            class="ti ti-copy "></i></a>
                                    <a href="javascript:void(0);" class="text-muted"
                                        wire:click="$dispatch('eliminar',  { rolId: {{ $rol->id }} })" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-original-title="Eliminar rol"><i
                                            class="ti ti-trash "></i></a>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            No hay roles
        @endif
    </div>
    <!--/ Role cards -->

    <!-- Modal -->
    <!-- editar permisos -->
    <div wire:ignore.self class="modal fade" id="editarPermisosAlRol" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
            <div class="modal-content p-3 p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="role-title mb-2"><i class="{{ $iconoModalPermisos }} ti-lg"></i>
                            {{ $tituloModalPermisos }}</h3>
                        <p class="text-muted">Los cambios se guardan de manera automatica</p>
                    </div>
                    <!-- Add role form -->
                    <form id="addForm" class="row g-3" onsubmit="return false">
                        <div class="d-flex justify-content-start flex-column">

                            <div class="alert alert-success {{ $msnModalPermisos ?: 'd-none' }}" role="alert">
                                {!! $msnModalPermisos !!}
                            </div>
                            @foreach (json_decode($checkboxes) as $checkbox)
                                <label class="text-nowrap fw-bold mb-2 mt-3"> {{ $checkbox->bloque->nombre }}
                                    <i class="ti ti-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Allows a full access to the system"></i>
                                </label>
                                <div class="row">
                                    @foreach ($checkbox->permisos as $permiso)
                                        <div class="col-6 form-check" wire:key="permiso-{{ $permiso->id }}">
                                            <input wire:model="arrayPermisosRol" value="{{ $permiso->name }}"
                                                class="form-check-input actualizarPermiso" type="checkbox"
                                                id="{{ $permiso->titulo }}" data-permiso="{{ $permiso->id }}"
                                                data-rol="{{ $idRolUpdatePermisos }}" />
                                            <label class="form-check-label"
                                                for="{{ $permiso->titulo }}">{{ str_replace('_', ' ', $permiso->titulo) }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </form>
                    <!--/ Add role form -->
                </div>
            </div>
        </div>
    </div>
    <!--/ editar permisos -->

    <!-- Add rol-->
    <div wire:ignore.self class="modal fade" id="addRol" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-plus ti-lg"></i> Nuevo rol</h3>
                        <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span> son
                            obligatorios</p>
                    </div>
                    <form wire:submit="nuevoRol" class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nombreRol"><span
                                    class="badge badge-dot bg-info me-1"></span> Nombre @error('nombreRol')
                                    <span class="error">{{ $message }}</span>
                                @enderror </label>
                            <input type="text" wire:model="nombreRol" id="nombreRol" name="nombre"
                                class="form-control" placeholder="Nombre de rol" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="iconoRol">Icono @error('iconoRol')
                                    <span class="error">{{ $message }}</span>
                                @enderror </label>
                            <input type="text" wire:model="iconoRol" id="iconoRol" name="icono"
                                class="form-control" placeholder="Icono" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_asistentes_sede_id">Sede</label>

                            {{-- Cambiamos el input por un select --}}
                            <select wire:model="lista_asistentes_sede_id" id="lista_asistentes_sede_id"
                                    name="lista_asistentes_sede_id" class="form-select">

                                {{-- Opción por defecto --}}
                                <option value="">Seleccione una sede</option>

                                {{-- Iteramos sobre la variable $sedes que pasamos desde el componente --}}
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>

                            @error('lista_asistentes_sede_id')
                                <span class="error text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_grupos_sede_id">Sede para lista de grupos</label>
                            <select wire:model="lista_grupos_sede_id" id="lista_grupos_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_grupos_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_reportes_grupo_sede_id">Sede para reportes de grupo</label>
                            <select wire:model="lista_reportes_grupo_sede_id" id="lista_reportes_grupo_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_reportes_grupo_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_reuniones_sede_id">Sede para lista de reuniones</label>
                            <select wire:model="lista_reuniones_sede_id" id="lista_reuniones_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_reuniones_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_sedes_sede_id">Sede para lista de sedes</label>
                            <select wire:model="lista_sedes_sede_id" id="lista_sedes_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_sedes_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_ingresos_sede_id">Sede para lista de ingresos</label>
                            <select wire:model="lista_ingresos_sede_id" id="lista_ingresos_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_ingresos_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_peticiones_sede_id">Sede para lista de peticiones</label>
                            <select wire:model="lista_peticiones_sede_id" id="lista_peticiones_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_peticiones_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="zona_de_consolidacion_id">¿Zona de pastoreo?</label>
                            <select wire:model="zona_de_consolidacion_id" id="zona_de_consolidacion_id" class="form-select">
                                <option value="">Seleccione una zona</option>
                                @foreach ($zonas as $zona)
                                    <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                                @endforeach
                            </select>
                            @error('zona_de_consolidacion_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="ver_sumatoria_ingresos_reportes_grupo_id">Ver sumatoria
                                ingresos reportes grupo id @error('ver_sumatoria_ingresos_reportes_grupo_id')
                                    <span class="error">{{ $message }}</span>
                                @enderror </label>
                            <input type="number" wire:model="ver_sumatoria_ingresos_reportes_grupo_id"
                                id="ver_sumatoria_ingresos_reportes_grupo_id"
                                name="ver_sumatoria_ingresos_reportes_grupo_id" class="form-control"
                                placeholder="Ver sumatoria ingresos reportes grupo id" />
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
    <!--/ Add rol -->

    <!-- Editar rol-->
    <div wire:ignore.self class="modal fade" id="editarRol" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="ti ti-edit "></i> Editar rol</h3>
                        <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span> son
                            obligatorios</p>
                    </div>
                    <form wire:submit="editarRol({{ $idRol }})" class="row g-3">
                        @csrf
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nombreRol"><span
                                    class="badge badge-dot bg-info me-1"></span> Nombre @error('nombreRol')
                                    <span class="error">{{ $message }}</span>
                                @enderror </label>
                            <input type="text" wire:model="nombreRol" id="nombreRol" name="nombre"
                                class="form-control" placeholder="Nombre de rol" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="iconoRol">Icono @error('iconoRol')
                                    <span class="error">{{ $message }}</span>
                                @enderror </label>
                            <input type="text" wire:model="iconoRol" id="iconoRol" name="icono"
                                class="form-control" placeholder="Icono" />
                        </div>


                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_asistentes_sede_id">Sede para lista de asistentes</label>
                            <select wire:model="lista_asistentes_sede_id" id="lista_asistentes_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_asistentes_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_grupos_sede_id">Sede para lista de grupos</label>
                            <select wire:model="lista_grupos_sede_id" id="lista_grupos_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_grupos_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_reportes_grupo_sede_id">Sede para reportes de grupo</label>
                            <select wire:model="lista_reportes_grupo_sede_id" id="lista_reportes_grupo_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_reportes_grupo_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_reuniones_sede_id">Sede para lista de reuniones</label>
                            <select wire:model="lista_reuniones_sede_id" id="lista_reuniones_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_reuniones_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_sedes_sede_id">Sede para lista de sedes</label>
                            <select wire:model="lista_sedes_sede_id" id="lista_sedes_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_sedes_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_ingresos_sede_id">Sede para lista de ingresos</label>
                            <select wire:model="lista_ingresos_sede_id" id="lista_ingresos_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_ingresos_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lista_peticiones_sede_id">Sede para lista de peticiones</label>
                            <select wire:model="lista_peticiones_sede_id" id="lista_peticiones_sede_id" class="form-select">
                                <option value="">Seleccione una sede</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @error('lista_peticiones_sede_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="zona_de_consolidacion_id">¿Zona de pastoreo?</label>
                            <select wire:model="zona_de_consolidacion_id" id="zona_de_consolidacion_id" class="form-select">
                                <option value="">Seleccione una zona</option>
                                @foreach ($zonas as $zona)
                                    <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                                @endforeach
                            </select>
                            @error('zona_de_consolidacion_id') <span class="error text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="ver_sumatoria_ingresos_reportes_grupo_id">Ver sumatoria
                                ingresos reportes grupo id @error('ver_sumatoria_ingresos_reportes_grupo_id')
                                    <span class="error">{{ $message }}</span>
                                @enderror </label>
                            <input type="number" wire:model="ver_sumatoria_ingresos_reportes_grupo_id"
                                id="ver_sumatoria_ingresos_reportes_grupo_id"
                                name="ver_sumatoria_ingresos_reportes_grupo_id" class="form-control"
                                placeholder="Ver sumatoria ingresos reportes grupo id" />
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
    <!--/ Editar rol -->

</div>

@script
<script>
    // Este listener asegura que tu código se ejecuta una sola vez, cuando Livewire está listo.
    document.addEventListener('livewire:initialized', () => {

        // Listener para CERRAR modales (usando jQuery)
        window.addEventListener('cerrarModal', event => {
            // event.detail.nombreModal es la forma correcta de acceder a los datos en v3
            $('#' + event.detail.nombreModal).modal('hide');
        });

        // Listener para ABRIR modales (usando jQuery)
        window.addEventListener('abrirModal', event => {
            $('#' + event.detail.nombreModal).modal('show');
        });

        // Listener para mostrar alertas de SweetAlert
        window.addEventListener('msn', event => {
            Swal.fire({
                title: event.detail.msnTitulo,
                text: event.detail.msnTexto,
                icon: event.detail.msnIcono,
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        });

        // Listener para la confirmación de eliminación (usando $wire.on)
        $wire.on('eliminar', (event) => {
            // En v3, los parámetros vienen dentro de un objeto, accedemos al primer elemento
            let rolId = event.rolId;;

            Swal.fire({
                title: '¿Deseas eliminar este rol?',
                text: "Esta acción no es reversible.",
                icon: 'warning',
                showCancelButton: true, // Es mejor mostrar el botón de cancelar
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Despachamos el evento para que el componente lo reciba y elimine el rol
                    $wire.dispatch('eliminarRol', { rolId: rolId });
                }
            });
        });

        // Este listener de jQuery sigue funcionando perfectamente gracias a la delegación de eventos.
        // Se adjunta al 'document' y escucha cambios en cualquier elemento con la clase '.actualizarPermiso'.
        $(document).on('change', '.actualizarPermiso', function() {
            let rolId = $(this).data('rol');
            let permisoId = $(this).data('permiso');
            $wire.dispatch('updatePermiso', {
                rolId: rolId,
                permisoId: permisoId
            });
        });

    });
</script>
@endscript
