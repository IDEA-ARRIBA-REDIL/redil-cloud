<div class="container-xxl container-p-y">
    <section class="redil-hero mb-4" aria-labelledby="planes-titulo">
        <div class="redil-eyebrow">ADMINISTRACIÓN / CATÁLOGO</div>
        <h1 id="planes-titulo">Un plan para cada iglesia.</h1>
        <p>Define capacidades, personalización y disponibilidad desde un solo lugar.</p>
        <button type="button" wire:click="create" wire:loading.attr="disabled" class="btn btn-dark rounded-pill">+ Crear nuevo plan</button>
    </section>

    @if(session()->has('message'))
        <div class="alert alert-success" role="status">{{ session('message') }}</div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="redil-stats mb-4">
        <div><span>Planes creados</span><strong>{{ $planes->count() }}</strong></div>
        <div><span>Disponibles</span><strong>{{ $planes->where('activo', true)->count() }}</strong></div>
        <div><span>Iglesias asignadas</span><strong>{{ $planes->sum('tenants_count') }}</strong></div>
    </div>

    @if($isModalOpen)
        <section class="card mb-4" aria-labelledby="plan-form-title" x-data x-init="$nextTick(() => { $el.scrollIntoView({block: 'start'}); $refs.nombre.focus(); })">
            <form wire:submit="store" class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 id="plan-form-title" class="h4 mb-0">{{ $plan_id ? 'Editar plan' : 'Nuevo plan' }}</h2>
                    <button type="button" wire:click="closeModal" class="btn btn-label-secondary">Cerrar</button>
                </div>
                @if($plan_id)
                    <p class="alert alert-info">Los cambios de capacidad y características se aplican a las iglesias que tienen este plan. Inactivarlo no las suspende.</p>
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="plan-nombre" class="form-label">Nombre del plan *</label>
                        <input id="plan-nombre" x-ref="nombre" wire:model.live.debounce.350ms="nombre" class="form-control" maxlength="100" required placeholder="Ej. Comunidad">
                        @error('nombre')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="plan-slug" class="form-label">Identificador (slug) *</label>
                        <input id="plan-slug" wire:model="slug" class="form-control" maxlength="100" required placeholder="comunidad">
                        @error('slug')<span class="text-danger">{{ $message }}</span>@enderror
                        <small class="form-text">Único, sin espacios. Se propone a partir del nombre al crear.</small>
                    </div>
                    <div class="col-md-6">
                        <label for="plan-capacidad" class="form-label">Límite de miembros</label>
                        <input id="plan-capacidad" type="number" min="1" max="2147483647" wire:model="max_miembros" class="form-control" placeholder="Sin límite">
                        @error('max_miembros')<span class="text-danger">{{ $message }}</span>@enderror
                        <small class="form-text">Déjalo vacío para miembros ilimitados.</small>
                    </div>
                    <div class="col-md-6 d-flex flex-column justify-content-center gap-3">
                        @foreach(['incluye_logo' => 'Logo personalizado', 'incluye_marca_blanca' => 'Marca blanca', 'activo' => 'Disponible para nuevas asignaciones'] as $campo => $etiqueta)
                            <div class="form-check form-switch">
                                <input id="plan-{{ $campo }}" class="form-check-input" type="checkbox" role="switch" wire:model="{{ $campo }}">
                                <label for="plan-{{ $campo }}" class="form-check-label">{{ $etiqueta }}</label>
                                @error($campo)<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="store">Guardar plan</span>
                        <span wire:loading wire:target="store">Guardando…</span>
                    </button>
                    <button type="button" class="btn btn-label-secondary" wire:click="closeModal" wire:loading.attr="disabled">Cancelar</button>
                </div>
            </form>
        </section>
    @endif

    <section class="card">
        <div class="card-body pb-2">
            <h2 class="h4">Tus planes</h2>
            <p class="text-muted">Inactivar retira el plan de nuevas asignaciones; no elimina datos ni cancela licencias.</p>
        </div>
        <div class="table-responsive">
            <table class="table redil-plan-table">
                <caption class="visually-hidden">Planes, capacidad, características, iglesias asignadas y acciones</caption>
                <thead><tr><th scope="col">Plan</th><th scope="col">Capacidad</th><th scope="col">Personalización</th><th scope="col">Iglesias</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead>
                <tbody>
                    @forelse($planes as $plan)
                        <tr wire:key="plan-{{ $plan->id }}">
                            <th scope="row"><strong>{{ $plan->nombre }}</strong><small class="d-block text-muted fw-normal">{{ $plan->slug }}</small></th>
                            <td>{{ $plan->capacidadFormateada() }}</td>
                            <td>
                                @if($plan->incluye_logo)<span class="badge bg-label-primary">Logo</span>@endif
                                @if($plan->incluye_marca_blanca)<span class="badge bg-label-primary">Marca blanca</span>@endif
                                @if(!$plan->incluye_logo && !$plan->incluye_marca_blanca)<span class="text-muted">Estándar</span>@endif
                            </td>
                            <td>{{ $plan->tenants_count }}</td>
                            <td><span class="badge {{ $plan->activo ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $plan->activo ? 'Activo' : 'Inactivo' }}</span></td>
                            <td><div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="edit({{ $plan->id }})" wire:loading.attr="disabled" aria-label="Editar {{ $plan->nombre }}">Editar</button>
                                <button type="button" class="btn btn-sm btn-label-secondary" wire:click="toggleActivo({{ $plan->id }})" wire:loading.attr="disabled" aria-label="{{ $plan->activo ? 'Inactivar' : 'Activar' }} {{ $plan->nombre }}">{{ $plan->activo ? 'Inactivar' : 'Activar' }}</button>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5"><h3 class="h5">Aquí comienza tu catálogo</h3><p class="text-muted">Crea el primer plan para asignarlo a una iglesia.</p><button type="button" wire:click="create" class="btn btn-primary">Crear primer plan</button></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
