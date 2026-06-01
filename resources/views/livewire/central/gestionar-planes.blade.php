<div class="container-xxl flex-grow-1 container-p-y">

    {{-- ===== TARJETAS DE ESTADÍSTICAS ===== --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Planes</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $planes->count() }}</h4>
                            </div>
                            <small>Todos los planes del sistema</small>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="bx bx-credit-card bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Planes Activos</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $planes->where('activo', true)->count() }}</h4>
                            </div>
                            <small>Disponibles para asignar</small>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="bx bx-check-circle bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Iglesias con Plan</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $planes->sum('tenants_count') }}</h4>
                            </div>
                            <small>Total de iglesias suscritas</small>
                        </div>
                        <span class="badge bg-label-info rounded p-2">
                            <i class="bx bx-church bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TABLA PRINCIPAL ===== --}}
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-column flex-sm-row">
            <h5 class="card-title mb-0">Gestión de Planes</h5>
            <div class="mt-3 mt-sm-0 d-flex gap-2">
                <a href="{{ url('/admin/dashboard') }}" class="btn btn-label-secondary">
                    <span class="tf-icons bx bx-arrow-back me-1"></span>Volver
                </a>
                <button wire:click="create" class="btn btn-primary">
                    <span class="tf-icons bx bx-plus me-1"></span>Nuevo Plan
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success m-3 mb-0">{{ session('message') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger m-3 mb-0">{{ session('error') }}</div>
        @endif

        <div class="table-responsive text-nowrap">
            <table class="table border-top">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Límite Miembros</th>
                        <th>Características</th>
                        <th>Iglesias</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($planes as $plan)
                    <tr>
                        {{-- Nombre y slug --}}
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-medium">{{ $plan->nombre }}</span>
                                <small class="text-muted font-monospace">{{ $plan->slug }}</small>
                            </div>
                        </td>

                        {{-- Límite de miembros --}}
                        <td>
                            @if($plan->max_miembros)
                                <span class="badge bg-label-secondary">
                                    <i class="bx bx-user-check me-1"></i>
                                    {{ number_format($plan->max_miembros) }}
                                </span>
                            @else
                                <span class="badge bg-label-primary">
                                    <i class="bx bx-infinite me-1"></i>
                                    Ilimitado
                                </span>
                            @endif
                        </td>

                        {{-- Características: logo y marca blanca --}}
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                @if($plan->incluye_logo)
                                    <span class="badge bg-label-warning">
                                        <i class="bx bx-image me-1"></i>Logo
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary text-decoration-line-through">
                                        <i class="bx bx-image me-1"></i>Logo
                                    </span>
                                @endif

                                @if($plan->incluye_marca_blanca)
                                    <span class="badge bg-label-info">
                                        <i class="bx bx-palette me-1"></i>Marca Blanca
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary text-decoration-line-through">
                                        <i class="bx bx-palette me-1"></i>Marca Blanca
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Conteo de tenants --}}
                        <td>
                            <span class="badge bg-label-dark">{{ $plan->tenants_count }}</span>
                        </td>

                        {{-- Estado activo --}}
                        <td>
                            @if($plan->activo)
                                <span class="badge bg-label-success" style="text-transform: uppercase;">Activo</span>
                            @else
                                <span class="badge bg-label-secondary" style="text-transform: uppercase;">Inactivo</span>
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i> Opciones
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" wire:click="edit({{ $plan->id }})">
                                            <i class="bx bx-edit-alt me-1"></i> Editar
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" wire:click="toggleActivo({{ $plan->id }})">
                                            @if($plan->activo)
                                                <i class="bx bx-x-circle me-1 text-warning"></i> Desactivar
                                            @else
                                                <i class="bx bx-check-circle me-1 text-success"></i> Activar
                                            @endif
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                           onclick="confirmarEliminarPlan({{ $plan->id }}, '{{ addslashes($plan->nombre) }}')">
                                            <i class="bx bx-trash me-1"></i> Eliminar
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bx bx-credit-card bx-lg text-muted d-block mb-2"></i>
                            No hay planes registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODAL CREAR / EDITAR ===== --}}
    @if($isModalOpen)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $plan_id ? 'Editar' : 'Nuevo' }} Plan</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        {{-- Nombre --}}
                        <div class="mb-3">
                            <label class="form-label">Nombre del Plan <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="plan-nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   wire:model.live="nombre"
                                   placeholder="Ej: Estándar Plus">
                            @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text font-monospace text-muted">#</span>
                                <input type="text"
                                       id="plan-slug"
                                       class="form-control font-monospace @error('slug') is-invalid @enderror"
                                       wire:model="slug"
                                       placeholder="estandar-plus">
                            </div>
                            @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
                            <div class="form-text">Se genera automáticamente. Identifica el plan en el sistema.</div>
                        </div>

                        {{-- Límite de miembros --}}
                        <div class="mb-3">
                            <label class="form-label">Límite de Miembros</label>
                            <input type="number"
                                   id="plan-max-miembros"
                                   class="form-control @error('max_miembros') is-invalid @enderror"
                                   wire:model="max_miembros"
                                   placeholder="Dejar vacío para ilimitado"
                                   min="1">
                            @error('max_miembros') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            <div class="form-text">Vacío = miembros ilimitados.</div>
                        </div>

                        {{-- Características --}}
                        <div class="mb-3">
                            <label class="form-label">Características Incluidas</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="plan-incluye-logo"
                                           wire:model="incluye_logo"
                                           role="switch">
                                    <label class="form-check-label" for="plan-incluye-logo">
                                        <i class="bx bx-image me-1 text-warning"></i>
                                        <strong>Logo Personalizado</strong>
                                        <small class="text-muted d-block">Permite personalizar logo e imagen de inicio de sesión.</small>
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="plan-incluye-marca-blanca"
                                           wire:model="incluye_marca_blanca"
                                           role="switch">
                                    <label class="form-check-label" for="plan-incluye-marca-blanca">
                                        <i class="bx bx-palette me-1 text-info"></i>
                                        <strong>Marca Blanca</strong>
                                        <small class="text-muted d-block">Habilita dominio propio y branding completo.</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Estado --}}
                        <div class="mb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="plan-activo"
                                       wire:model="activo"
                                       role="switch">
                                <label class="form-check-label" for="plan-activo">
                                    <strong>Plan Activo</strong>
                                    <small class="text-muted d-block">Solo los planes activos pueden asignarse a iglesias.</small>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click.prevent="store">
                        <span wire:loading wire:target="store" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Guardar Plan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@script
<script>
    /**
     * Confirmación de eliminación de plan con SweetAlert2.
     */
    window.confirmarEliminarPlan = function(id, nombre) {
        Swal.fire({
            title: '¿Eliminar plan?',
            html: `¿Estás seguro de eliminar el plan <strong>${nombre}</strong>? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('eliminar', id);
            }
        });
    };

    Livewire.on('msn', () => {
        Swal.fire({
            title: '¡Eliminado!',
            text: 'El plan ha sido eliminado correctamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
        });
    });
</script>
@endscript
