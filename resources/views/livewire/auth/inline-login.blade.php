<div class="card border-0 shadow-sm mt-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color: #2b3445;">Inicia sesión para continuar</h5>
        <p class="text-muted small mb-4 titulo-descripcion">Identifícate para inscribirte o comprar este curso.</p>

        <form wire:submit.prevent="login">
            @include('layouts.status-msn')

            <div class="mb-3">
                <label for="inline-email" class="form-label d-none">Correo Electrónico</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                    <input wire:model="email" type="email" id="inline-email" class="form-control input-login @error('email') is-invalid @enderror" placeholder="Correo electrónico" autofocus>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="inline-password" class="form-label d-none">Contraseña</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-lock"></i></span>
                    <input wire:model="password" type="password" id="inline-password" class="form-control input-login @error('password') is-invalid @enderror" placeholder="Contraseña">
                </div>
                @error('password')
                    <div class="invalid-feedback d-block mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input wire:model="remember" class="form-check-input" type="checkbox" id="inline-remember">
                    <label class="form-check-label text-muted small" for="inline-remember">
                        Recordarme
                    </label>
                </div>
                <a href="{{ route('password.request') }}" class="small text-primary">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill" wire:loading.attr="disabled">
                <span wire:loading.remove>Ingresar</span>
                <span wire:loading><i class="ti ti-refresh ti-spin me-1"></i> Validando...</span>
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="small text-muted mb-0">¿No tienes cuenta?</p>
            <a href="{{ route('register') }}" class="small fw-bold text-primary">Regístrate aquí</a>
        </div>
    </div>
</div>
