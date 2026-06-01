<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Admins</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $admins->count() }}</h4>
                            </div>
                            <small>Todos los super administradores</small>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="bx bx-user bx-sm"></i>
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
                            <span>Admins Activos</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $admins->where('is_suspended', false)->count() }}</h4>
                            </div>
                            <small>Cuentas con acceso</small>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="bx bx-user-check bx-sm"></i>
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
                            <span>Suspendidos</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $admins->where('is_suspended', true)->count() }}</h4>
                            </div>
                            <small>Sin acceso al panel</small>
                        </div>
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="bx bx-user-x bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-column flex-sm-row">
            <h5 class="card-title mb-0">Gestión de Administradores</h5>
            <div class="mt-3 mt-sm-0 d-flex gap-2">
                <a href="{{ url('/admin/dashboard') }}" class="btn btn-label-secondary">
                    <span class="tf-icons bx bx-arrow-back me-1"></span>Volver
                </a>
                <button wire:click="create" class="btn btn-primary">
                    <span class="tf-icons bx bx-plus me-1"></span>Nuevo Admin
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
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($admins as $admin)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($admin->name, 0, 2)) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="javascript:void(0);" class="text-body text-truncate"><span class="fw-medium">{{ $admin->name }}</span></a>
                                    <small class="text-muted">{{ $admin->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($admin->is_suspended)
                                <span class="badge bg-label-danger" style="text-transform: uppercase;">Suspendido</span>
                            @else
                                <span class="badge bg-label-success" style="text-transform: uppercase;">Activo</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" wire:click="edit({{ $admin->id }})" class="text-body"><i class="bx bx-edit-alt me-2"></i></a>
                                <a href="javascript:void(0);" wire:click="toggleSuspension({{ $admin->id }})" class="text-body"><i class="bx {{ $admin->is_suspended ? 'bx-check-circle text-success' : 'bx-x-circle text-danger' }} mx-1"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">No hay administradores registrados.</td>
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
                            <input type="text" class="form-control" wire:model.defer="name" placeholder="John Doe">
                            @error('name') <span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" wire:model.defer="email" placeholder="john@example.com">
                            @error('email') <span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label">Contraseña {{ $admin_id ? '(Dejar en blanco para no cambiar)' : '' }}</label>
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" wire:model.defer="password" placeholder="············">
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            @error('password') <span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click.prevent="store">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
