<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">SaaS /</span> Super Admins Centrales
        </h4>

        <div>
            <a href="{{ route('central.admin.dashboard') }}" class="btn btn-secondary btn-sm me-2">Volver al Dashboard</a>
            <button wire:click="create" class="btn btn-primary btn-sm">Nuevo Admin</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <h5 class="card-header">Usuarios Administrativos REDIL</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($admins as $admin)
                    <tr>
                        <td>{{ $admin->id }}</td>
                        <td><strong>{{ $admin->name }}</strong></td>
                        <td>{{ $admin->email }}</td>
                        <td>
                            @if($admin->is_suspended)
                                <span class="badge bg-label-danger">Suspendido</span>
                            @else
                                <span class="badge bg-label-success">Activo</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i> Opciones
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);" wire:click="edit({{ $admin->id }})"><i class="bx bx-edit-alt me-1"></i> Editar</a>
                                    
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" wire:click="toggleSuspension({{ $admin->id }})"><i class="bx bx-power-off me-1"></i> {{ $admin->is_suspended ? 'Reactivar' : 'Suspender' }}</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay super administradores.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($isModalOpen)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $admin_id ? 'Editar' : 'Crear' }} Super Admin</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" wire:model.defer="name">
                            @error('name') <span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" wire:model.defer="email">
                            @error('email') <span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña {{ $admin_id ? '(Dejar en blanco para no cambiar)' : '' }}</label>
                            <input type="password" class="form-control" wire:model.defer="password">
                            @error('password') <span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click.prevent="store">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
