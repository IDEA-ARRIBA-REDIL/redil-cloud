<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 py-3">
        <div>
            <h4 class="fw-bold mb-1">Marca Blanca</h4>
            <p class="text-muted mb-0">{{ $tenant->church_name }} · Plan {{ $nombrePlan }}</p>
        </div>
        <a href="{{ url('/admin/tenants/'.$tenant->getRouteKey()) }}" class="btn btn-label-secondary">
            <i class="ti ti-arrow-left me-1"></i> Volver al tenant
        </a>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info d-flex align-items-start" role="alert">
        <i class="ti ti-shield-lock ti-lg me-2"></i>
        <div>
            Esta configuración solo está disponible para administradores centrales de Software Redil.
            La activación efectiva también está limitada por las prestaciones del plan contratado.
        </div>
    </div>

    <form wire:submit="guardar">
        <div class="card mb-4">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Disponibilidad y activación</h5>
                <div class="d-flex gap-2">
                    <span class="badge {{ $planIncluyeLogo ? 'bg-label-success' : 'bg-label-secondary' }}">
                        Logo: {{ $planIncluyeLogo ? 'incluido' : 'no incluido' }}
                    </span>
                    <span class="badge {{ $planIncluyeMarcaBlanca ? 'bg-label-success' : 'bg-label-secondary' }}">
                        Marca blanca: {{ $planIncluyeMarcaBlanca ? 'incluida' : 'no incluida' }}
                    </span>
                </div>
            </div>
            <div class="card-body pt-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-label">¿Usar logo personalizado?</div>
                        <label class="switch switch-lg">
                            <input type="checkbox" class="switch-input" wire:model="logoPersonalizado" @disabled(! $planIncluyeLogo)>
                            <span class="switch-toggle-slider"><span class="switch-on">Sí</span><span class="switch-off">No</span></span>
                        </label>
                        @unless($planIncluyeLogo)
                            <small class="d-block text-muted mt-2">El plan asignado no incluye logos personalizados.</small>
                        @endunless
                    </div>

                    <div class="col-md-6">
                        <div class="form-label">¿Habilitar Marca Blanca?</div>
                        <label class="switch switch-lg">
                            <input type="checkbox" class="switch-input" wire:model="marcaBlanca" @disabled(! $planIncluyeMarcaBlanca)>
                            <span class="switch-toggle-slider"><span class="switch-on">Sí</span><span class="switch-off">No</span></span>
                        </label>
                        @unless($planIncluyeMarcaBlanca)
                            <small class="d-block text-muted mt-2">El plan asignado no incluye marca blanca.</small>
                        @endunless
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="mb-0">Identidad de marca</h5>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="nombreCreador">Nombre del creador</label>
                        <input id="nombreCreador" type="text" class="form-control" wire:model="nombreCreador" @disabled(! $planIncluyeMarcaBlanca)>
                        @error('nombreCreador') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="urlCreador">URL del creador</label>
                        <input id="urlCreador" type="url" class="form-control" wire:model="urlCreador" @disabled(! $planIncluyeMarcaBlanca)>
                        @error('urlCreador') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="colorNombreApp">Color del nombre de la app</label>
                        <input id="colorNombreApp" type="text" class="form-control" wire:model="colorNombreApp" placeholder="white o #ffffff" @disabled(! $planIncluyeMarcaBlanca)>
                        @error('colorNombreApp') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="descripcionLogin">Descripción del login</label>
                        <input id="descripcionLogin" type="text" class="form-control" wire:model="descripcionLogin" @disabled(! $planIncluyeMarcaBlanca)>
                        @error('descripcionLogin') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sufijoApp">Sufijo de la app (SEO)</label>
                        <input id="sufijoApp" type="text" class="form-control" wire:model="sufijoApp" @disabled(! $planIncluyeMarcaBlanca)>
                        @error('sufijoApp') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="versionApp">Versión personalizada</label>
                        <input id="versionApp" type="text" class="form-control" wire:model="versionApp" placeholder="1.0.0" @disabled(! $planIncluyeMarcaBlanca)>
                        @error('versionApp') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="mb-0">Recursos gráficos</h5>
            </div>
            <div class="card-body pt-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label" for="logoAppFile">Logo principal</label>
                        @if($logoAppFile)
                            <img src="{{ $logoAppFile->temporaryUrl() }}" class="d-block rounded border mb-3 p-2 bg-dark" alt="Vista previa del logo" style="width: 180px; height: 90px; object-fit: contain;">
                        @elseif($logoAppUrl)
                            <img src="{{ $logoAppUrl }}" class="d-block rounded border mb-3 p-2 bg-dark" alt="Logo actual" style="width: 180px; height: 90px; object-fit: contain;">
                        @endif
                        <input id="logoAppFile" type="file" class="form-control" wire:model="logoAppFile" accept="image/png,image/jpeg" @disabled(! $planIncluyeLogo)>
                        <div class="form-text">Se ajustará automáticamente a 300 × 150 px sin recortar el contenido.</div>
                        <div wire:loading wire:target="logoAppFile" class="small text-muted mt-1">Cargando vista previa...</div>
                        @error('logoAppFile') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="logoAppNegroFile">Logo para fondo claro</label>
                        @if($logoAppNegroFile)
                            <img src="{{ $logoAppNegroFile->temporaryUrl() }}" class="d-block rounded border mb-3 p-2 bg-light" alt="Vista previa del logo para fondo claro" style="width: 180px; height: 90px; object-fit: contain;">
                        @elseif($logoAppNegroUrl)
                            <img src="{{ $logoAppNegroUrl }}" class="d-block rounded border mb-3 p-2 bg-light" alt="Logo actual para fondo claro" style="width: 180px; height: 90px; object-fit: contain;">
                        @endif
                        <input id="logoAppNegroFile" type="file" class="form-control" wire:model="logoAppNegroFile" accept="image/png,image/jpeg" @disabled(! $planIncluyeLogo)>
                        <div class="form-text">Se ajustará automáticamente a 300 × 150 px sin recortar el contenido.</div>
                        <div wire:loading wire:target="logoAppNegroFile" class="small text-muted mt-1">Cargando vista previa...</div>
                        @error('logoAppNegroFile') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="faviconAppFile">Favicon (.ico o .png)</label>
                        @if($faviconAppFile && str_contains($faviconAppFile->getMimeType(), 'image'))
                            <img src="{{ $faviconAppFile->temporaryUrl() }}" class="d-block rounded border mb-3 p-2 bg-light" alt="Vista previa del favicon" style="width: 64px; height: 64px; object-fit: contain;">
                        @elseif($faviconAppUrl)
                            <img src="{{ $faviconAppUrl }}" class="d-block rounded border mb-3 p-2 bg-light" alt="Favicon actual" style="width: 64px; height: 64px; object-fit: contain;">
                        @endif
                        <input id="faviconAppFile" type="file" class="form-control" wire:model="faviconAppFile" accept=".ico,image/png" @disabled(! $planIncluyeLogo)>
                        <div wire:loading wire:target="faviconAppFile" class="small text-muted mt-1">Cargando archivo...</div>
                        @error('faviconAppFile') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="guardar,logoAppFile,logoAppNegroFile,faviconAppFile">
            <span wire:loading.remove wire:target="guardar">Guardar configuración</span>
            <span wire:loading wire:target="guardar">Guardando...</span>
        </button>
    </form>
</div>
