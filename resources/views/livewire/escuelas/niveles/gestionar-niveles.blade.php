<div class="row g-4">
    @forelse ($niveles as $nivel)
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card h-100 rounded rounded-3">
             <img style="height: 150px; object-fit: cover;" class="card-img-top mb-2 rounded-top"
                 @if ($nivel->portada != '') src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/' . $nivel->portada) }}"
            @else
                src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/niveles/default.png') }}" @endif
            alt="Portada ">

            <div class="card-body p-4 pt-2">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $nivel->nombre }}</h5>
                    <div class="dropdown zindex-2">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical text-black"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('escuelas.niveles.gestionar-materias', $nivel) }}">
                                <i class="ti ti-book me-1"></i> Gestionar Materias
                            </a>
                            <a class="dropdown-item" href="{{ route('niveles.editar', $nivel) }}">
                                <i class="ti ti-pencil me-1"></i> Editar
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" wire:click="eliminar({{ $nivel->id }})" wire:confirm="¿Estás seguro de eliminar este grado?">
                                <i class="ti ti-trash me-1"></i> Eliminar
                            </a>
                        </div>
                    </div>
                </div>

                <p class="mb-3 text-muted small">{{ Str::limit($nivel->descripcion, 80) }}</p>

                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-black small">Calificaciones:</span>
                        <span class="badge bg-label-secondary text-black">{{ $nivel->configuracion?->habilitar_calificaciones ? 'Sí' : 'No' }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-black small">Asistencias:</span>
                        <span class="badge bg-label-secondary text-black">{{ $nivel->configuracion?->habilitar_asistencias ? 'Sí' : 'No' }}</span>
                    </div>
                     <div class="d-flex align-items-center justify-content-between">
                        <span class="text-black small">Materias:</span>
                        <span class="badge bg-label-secondary text-black">{{ $nivel->materias()->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center p-5">
                <div class="avatar avatar-xl bg-label-secondary rounded-circle mb-3 mx-auto">
                    <i class="ti ti-hierarchy-off ti-xl"></i>
                </div>
                <h4>No hay grados registrados</h4>
                <p class="text-muted">Comienza creando el primer grado para esta escuela.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>
