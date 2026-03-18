<div>
    <div id="container-listado-niveles">
        <!-- Botón para abrir el modal de creación -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary mb-0">Grados asociados</h4>
            <a href="{{ route('niveles-escuelas.crear', $escuelaId) }}" class="btn btn-primary rounded-pill shadow-sm">
                <i class="ti ti-plus me-1"></i> Nuevo grado
            </a>
        </div>

        <div class="row mb-4 d-none">
            <div class="col-md-4">
                <div class="input-group input-group-merge shadow-sm">
                    <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                    <input type="text" class="form-control" placeholder="Buscar grado..." wire:model.live="search"
                        aria-label="Buscar grado..." aria-describedby="basic-addon-search31">
                </div>
            </div>
        </div>

        <!-- Listado de Niveles (Grados) -->
        <div class="row">
            @forelse($niveles as $nivel)
                <div class="col-12 col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition-all" style="border-radius: 15px;">
                        @if ($nivel->portada)
                            <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/' . $nivel->portada) }}"
                                class="card-img-top object-fit-cover"
                                style="height: 150px; border-top-left-radius: 15px; border-top-right-radius: 15px;"
                                alt="Portada {{ $nivel->nombre }}">
                        @else
                            <div class="card-img-top bg-label-secondary d-flex align-items-center justify-content-center"
                                style="height: 150px; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                <i class="ti ti-camera fs-1 opacity-25"></i>
                            </div>
                        @endif
                        <div class="card-header bg-transparent border-0 pb-0">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="d-flex align-items-center">

                                    <div>
                                        <h5 class="mb-0 fw-bold text-dark">{{ $nivel->nombre }}</h5>
                                        <small class="text-black">ID: #{{ $nivel->id }}</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button style="border-radius: 20px;"
                                        class="btn  border circle p-1 btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
                                        type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical fs-4 text-black"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('niveles-escuelas.editar', [$escuelaId, $nivel->id]) }}">
                                                Actualizar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('niveles-escuelas.gestionar-materias', [$escuelaId, $nivel->id]) }}">
                                                Gestionar materias
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button"
                                                class="dropdown-item d-flex align-items-center text-black"
                                                @click="confirmarEliminacionNivel({{ $nivel->id }}, '{{ $nivel->nombre }}')">
                                                Eliminar
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row justify-content-between mb-2">
                                <div class="col-12 col-md-6 align-items-center mb-2">
                                    <div class="d-flex flex-column">
                                        <small class="text-black"> <i class="ti ti-notebook text-black me-2"></i>Total
                                            materias:</small>
                                        <small class="fw-semibold text-black">
                                            {{ $nivel->materias->count() }}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 align-items-center mb-2">
                                    <div class="d-flex flex-column text-star">
                                        <small class="text-black"> <i
                                                class="ti ti-calendar-check text-black me-2"></i>Asistencias
                                            mín.:</small>
                                        <small class="fw-semibold text-black">
                                            {{ $nivel->asistencias_minimas ?? '0' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-between mb-2">
                                <div class="col-12 col-md-6 align-items-center mb-2">
                                    <div class="d-flex flex-column text-star">
                                        <small class="text-black"> <i
                                                class="ti ti-chart-bar text-black me-2"></i>Calificaciones:</small>
                                        <small class="fw-semibold text-black">
                                            {{ $nivel->habilitar_calificaciones ? 'Habilitado' : 'Deshabilitado' }}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 align-items-center mb-2">
                                    <div class="d-flex flex-column text-star">
                                        <small class="text-black"> <i
                                                class="ti ti-alert-circle text-black me-2"></i>Obligatorio:</small>
                                        <small class="fw-semibold text-black">
                                            {{ $nivel->caracter_obligatorio ? 'Sí' : 'No' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card bg-label-info border-0 py-4 shadow-none">
                        <div class="card-body text-center">
                            <i class="ti ti-info-circle fs-1 mb-3"></i>
                            <h5 class="fw-bold">No hay grados registrados</h5>
                            <p class="mb-0">Aún no has creado grados para esta escuela.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @script
        <script>
            window.confirmarEliminacionNivel = function(id, nombre) {
                Swal.fire({
                    title: '¿Eliminar grado?',
                    text: `¿Estás seguro de que deseas eliminar el grado "${nombre}"? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ea5455',
                    cancelButtonColor: '#808390',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-danger me-2 shadow-none',
                        cancelButton: 'btn btn-label-secondary shadow-none'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarNivel', id);
                    }
                })
            }

            Livewire.on('msn', (data) => {
                let msn = data.msn || (data[0] ? data[0].msn : null);
                let icon = data.icon || (data[0] ? data[0].icon : 'info');

                Swal.fire({
                    title: icon === 'success' ? '¡Éxito!' : 'Información',
                    text: msn,
                    icon: icon,
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        </script>
    @endscript

    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</div>
