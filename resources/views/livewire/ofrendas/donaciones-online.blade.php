<div>
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        {{-- Navegación de las 3 Pestañas en una sola página --}}
        <div class="card-header bg-lighter border-bottom p-0">
            <ul class="nav nav-tabs nav-fill border-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        type="button"
                        wire:click="irAPestana(1)"
                        class="nav-link donacion-tab-btn py-3 px-4 d-flex align-items-center justify-content-center gap-2 {{ $pestanaActiva === 1 ? 'active' : ($pestanaActiva > 1 ? 'completed' : 'disabled') }}"
                        @disabled($pestanaActiva < 1)
                    >
                        <span class="donacion-step-circle">
                            @if($pestanaActiva > 1)
                                <i class="ti ti-check ti-xs"></i>
                            @else
                                1
                            @endif
                        </span>
                        <span>Modalidad e Identificación</span>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button
                        type="button"
                        wire:click="irAPestana(2)"
                        class="nav-link donacion-tab-btn py-3 px-4 d-flex align-items-center justify-content-center gap-2 {{ $pestanaActiva === 2 ? 'active' : ($pestanaActiva > 2 ? 'completed' : 'disabled') }}"
                        @disabled($pestanaActiva < 2)
                    >
                        <span class="donacion-step-circle">
                            @if($pestanaActiva > 2)
                                <i class="ti ti-check ti-xs"></i>
                            @else
                                2
                            @endif
                        </span>
                        <span>Información del Donante</span>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button
                        type="button"
                        class="nav-link donacion-tab-btn py-3 px-4 d-flex align-items-center justify-content-center gap-2 {{ $pestanaActiva === 3 ? 'active' : 'disabled' }}"
                        disabled
                    >
                        <span class="donacion-step-circle">3</span>
                        <span>Detalles de la Donación</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4 p-md-5">
            {{-- ============================================================== --}}
            {{-- PESTAÑA 1: MODALIDAD E IDENTIFICACIÓN                          --}}
            {{-- ============================================================== --}}
            @if($pestanaActiva === 1)
                <div wire:key="pestana-1">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-black mb-1">¿Cómo deseas realizar tu donación?</h4>
                        <p class="text-muted mb-0">Selecciona si ya haces parte de nuestra plataforma o si deseas realizar una donación personal independiente.</p>
                    </div>

                    <div class="row g-4 mb-4">
                        {{-- Opción A: Usuario Registrado --}}
                        <div class="col-md-6">
                            <div
                                wire:click="seleccionarModalidad('registrado')"
                                class="card h-100 donacion-card-option {{ $modalidad === 'registrado' ? 'active' : '' }}"
                            >
                                <div class="card-body d-flex align-items-start gap-3">
                                    <span class="donacion-card-icon">
                                        <i class="ti ti-user-check ti-md"></i>
                                    </span>
                                    <div>
                                        <h6 class="fw-bold donacion-card-title mb-1">Ya estoy registrado en la plataforma</h6>
                                        <p class="donacion-card-desc small mb-0">
                                            Ingresa tu número de cédula o documento para buscar y cargar automáticamente tu información registrada.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Opción B: Donación Personal / Independiente --}}
                        <div class="col-md-6">
                            <div
                                wire:click="seleccionarModalidad('independiente')"
                                class="card h-100 donacion-card-option {{ $modalidad === 'independiente' ? 'active' : '' }}"
                            >
                                <div class="card-body d-flex align-items-start gap-3">
                                    <span class="donacion-card-icon">
                                        <i class="ti ti-heart-handshake ti-md"></i>
                                    </span>
                                    <div>
                                        <h6 class="fw-bold donacion-card-title mb-1">Quiero hacer una donación personal</h6>
                                        <p class="donacion-card-desc small mb-0">
                                            Realiza tu ofrenda o donación de forma independiente diligenciando tus datos básicos de contacto.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($modalidad === 'registrado')
                        <div class="card bg-lighter border-0 p-4" wire:key="form-busqueda-registrado">
                            <form wire:submit="buscarUsuarioRegistrado">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold text-black">Tipo de documento</label>
                                        <div
                                            wire:ignore
                                            x-data
                                            x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'tipoIdentificacionBusqueda', 'Selecciona tipo de documento'))"
                                        >
                                            <select
                                                x-ref="select"
                                                wire:model="tipoIdentificacionBusqueda"
                                                class="select2 form-select bg-white"
                                            >
                                                @foreach($tiposIdentificacion as $tipoDoc)
                                                    <option value="{{ $tipoDoc->id }}" @selected($tipoIdentificacionBusqueda == $tipoDoc->id)>
                                                        {{ $tipoDoc->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold text-black">Número de identificación (Cédula) <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            wire:model="identificacionBusqueda"
                                            class="form-control bg-white @error('identificacionBusqueda') is-invalid @enderror"
                                            placeholder="Ingresa tu número de documento sin puntos ni espacios"
                                        />
                                        @error('identificacionBusqueda')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                @if($usuarioNoEncontrado)
                                    <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4 mb-0">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ti ti-alert-circle ti-md flex-shrink-0"></i>
                                            <div>
                                                <strong>No encontramos un usuario registrado con la identificación "{{ $identificacionBusqueda }}".</strong>
                                                <div class="small">Verifica el número digitado o continúa realizando una donación personal independiente.</div>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="continuarComoIndependiente"
                                            class="btn btn-warning btn-sm rounded-pill flex-shrink-0"
                                        >
                                            Continuar como donación personal <i class="ti ti-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="buscarUsuarioRegistrado">
                                            Buscar y continuar <i class="ti ti-arrow-right ms-1"></i>
                                        </span>
                                        <span wire:loading wire:target="buscarUsuarioRegistrado">
                                            <span class="spinner-border spinner-border-sm me-1"></span> Buscando...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card bg-lighter border-0 p-4 text-center">
                            <p class="text-black mb-3">
                                En el siguiente paso podrás ingresar tu nombre completo, identificación, teléfono, correo electrónico, país y dirección para registrar tu donación.
                            </p>
                            <div>
                                <button
                                    type="button"
                                    wire:click="continuarComoIndependiente"
                                    class="btn btn-primary rounded-pill px-5"
                                >
                                    Continuar a mis datos <i class="ti ti-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ============================================================== --}}
            {{-- PESTAÑA 2: INFORMACIÓN BÁSICA DEL DONANTE                      --}}
            {{-- ============================================================== --}}
            @if($pestanaActiva === 2)
                <div wire:key="pestana-2">
                    @if($esRegistrado)
                        {{-- Aviso para Usuario Registrado (Campos Bloqueados + Regla de Sesión) --}}
                        <div class="alert alert-primary d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="ti ti-lock ti-md mt-1 flex-shrink-0"></i>
                                <div>
                                    <h6 class="alert-heading fw-bold mb-1">Hemos cargado tus datos registrados</h6>
                                    @if($usuarioEstaLogueado)
                                        <p class="mb-0 small">
                                            Por seguridad tus datos están bloqueados en este formulario. Como tienes tu sesión activa, puedes actualizarlos desde tu perfil si lo necesitas.
                                        </p>
                                    @else
                                        <p class="mb-0 small">
                                            Por seguridad tus datos se encuentran bloqueados y no pueden modificarse desde aquí. <strong>Si necesitas actualizar tu información personal, debes ingresar a tu cuenta en la plataforma y actualizar tus datos desde tu perfil.</strong>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if($usuarioEstaLogueado && $userId)
                                <a
                                    href="{{ route('usuario.perfil', $userId) }}"
                                    class="btn btn-primary btn-sm rounded-pill flex-shrink-0"
                                >
                                    <i class="ti ti-user-edit me-1"></i> Actualizar datos
                                </a>
                            @endif
                        </div>
                    @else
                        {{-- Aviso para Donación Personal / Independiente (Campos Obligatorios) --}}
                        <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
                            <i class="ti ti-info-circle ti-md mt-1 flex-shrink-0"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1">Donación personal (independiente)</h6>
                                <p class="mb-0 small">
                                    Por favor completa tus datos básicos. Todos los campos marcados con <strong>*</strong> (<strong>Nombre completo, Identificación, Teléfono, Correo electrónico, País y Dirección</strong>) son <strong>obligatorios</strong> para procesar tu donación y enviarte el comprobante.
                                </p>
                            </div>
                        </div>
                    @endif

                    <form wire:submit="continuarAPestana3">
                        <div class="row g-3">
                            {{-- Nombre completo --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-black">
                                    Nombre completo <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="nombreCompleto"
                                    class="form-control {{ $esRegistrado ? 'bg-lighter text-muted' : '' }} @error('nombreCompleto') is-invalid @enderror"
                                    placeholder="Ingresa tu nombre y apellidos completos"
                                    @readonly($esRegistrado)
                                    @disabled($esRegistrado)
                                />
                                @error('nombreCompleto')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tipo de Identificación --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Tipo de identificación <span class="text-danger">*</span>
                                </label>
                                <div
                                    wire:ignore
                                    x-data
                                    x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'tipoIdentificacionId', 'Selecciona un tipo de documento'))"
                                >
                                    <select
                                        x-ref="select"
                                        wire:model="tipoIdentificacionId"
                                        class="select2 form-select {{ $esRegistrado ? 'bg-lighter text-muted' : '' }}"
                                        @disabled($esRegistrado)
                                    >
                                        <option value="">Selecciona un tipo de documento</option>
                                        @foreach($tiposIdentificacion as $tipoDoc)
                                            <option value="{{ $tipoDoc->id }}" @selected($tipoIdentificacionId == $tipoDoc->id)>
                                                {{ $tipoDoc->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('tipoIdentificacionId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Número de Identificación --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Número de identificación <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="identificacion"
                                    class="form-control {{ $esRegistrado ? 'bg-lighter text-muted' : '' }} @error('identificacion') is-invalid @enderror"
                                    placeholder="Número de documento"
                                    @readonly($esRegistrado)
                                    @disabled($esRegistrado)
                                />
                                @error('identificacion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Teléfono móvil --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Teléfono móvil <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="telefono"
                                    class="form-control {{ $esRegistrado ? 'bg-lighter text-muted' : '' }} @error('telefono') is-invalid @enderror"
                                    placeholder="Ej: 3001234567"
                                    @readonly($esRegistrado)
                                    @disabled($esRegistrado)
                                />
                                @error('telefono')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Correo electrónico --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Correo electrónico <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="email"
                                    wire:model="email"
                                    class="form-control {{ $esRegistrado ? 'bg-lighter text-muted' : '' }} @error('email') is-invalid @enderror"
                                    placeholder="ejemplo@correo.com"
                                    @readonly($esRegistrado)
                                    @disabled($esRegistrado)
                                />
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- País --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    País <span class="text-danger">*</span>
                                </label>
                                <div
                                    wire:ignore
                                    x-data
                                    x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'paisId', 'Selecciona tu país'))"
                                >
                                    <select
                                        x-ref="select"
                                        wire:model="paisId"
                                        class="select2 form-select {{ $esRegistrado ? 'bg-lighter text-muted' : '' }}"
                                        @disabled($esRegistrado)
                                    >
                                        <option value="">Selecciona tu país</option>
                                        @foreach($paises as $pais)
                                            <option value="{{ $pais->id }}" @selected($paisId == $pais->id)>
                                                {{ $pais->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('paisId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Dirección --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Dirección de residencia <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="direccion"
                                    class="form-control {{ $esRegistrado ? 'bg-lighter text-muted' : '' }} @error('direccion') is-invalid @enderror"
                                    placeholder="Ingresa tu dirección"
                                    @readonly($esRegistrado)
                                    @disabled($esRegistrado)
                                />
                                @error('direccion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Aceptación de Términos y Habeas Data --}}
                            <div class="col-12 mt-3">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        id="aceptaTerminosCheck"
                                        wire:model="aceptaTerminos"
                                        class="form-check-input @error('aceptaTerminos') is-invalid @enderror"
                                    />
                                    <label class="form-check-label text-black" for="aceptaTerminosCheck">
                                        Acepto la
                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalTerminosDonacion" class="fw-semibold text-primary text-decoration-underline">
                                            política de tratamiento de datos personales y términos de donación
                                        </a>
                                        <span class="text-danger">*</span>
                                    </label>
                                    @error('aceptaTerminos')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <button
                                type="button"
                                wire:click="irAPestana(1)"
                                class="btn btn-outline-secondary rounded-pill px-4"
                            >
                                <i class="ti ti-arrow-left me-1"></i> Volver
                            </button>

                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                Continuar a la donación <i class="ti ti-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- ============================================================== --}}
            {{-- PESTAÑA 3: DETALLES DE LA DONACIÓN Y PAGO                      --}}
            {{-- ============================================================== --}}
            @if($pestanaActiva === 3)
                <div wire:key="pestana-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-lighter rounded p-3 mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-user-heart ti-md text-primary"></i>
                            <div>
                                <span class="small text-muted d-block">Donante:</span>
                                <strong class="text-black">{{ $nombreCompleto }}</strong>
                                @if($identificacion)
                                    <span class="text-muted small">({{ $identificacion }})</span>
                                @endif
                            </div>
                        </div>
                        <span class="donacion-pill-tag mt-2 mt-md-0">
                            {{ $esRegistrado ? 'Usuario registrado' : 'Donación personal' }}
                        </span>
                    </div>

                    @if($mensajeErrorPasarela)
                        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                            <i class="ti ti-alert-triangle ti-md flex-shrink-0"></i>
                            <div>{{ $mensajeErrorPasarela }}</div>
                        </div>
                    @endif

                    <form wire:submit="procesarDonacion">
                        <div class="row g-3">
                            {{-- Tipo de Donación --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Tipo de donación <span class="text-danger">*</span>
                                </label>
                                <div
                                    wire:ignore
                                    x-data
                                    x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'tipoOfrendaId', 'Selecciona el tipo de donación'))"
                                >
                                    <select
                                        x-ref="select"
                                        wire:model.live="tipoOfrendaId"
                                        class="select2 form-select"
                                    >
                                        <option value="">Selecciona el tipo de donación</option>
                                        @foreach($tiposOfrenda as $tipo)
                                            <option value="{{ $tipo->id }}" @selected($tipoOfrendaId == $tipo->id)>
                                                {{ $tipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('tipoOfrendaId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Destinado a (Destinatario) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Destinado a <span class="text-danger">*</span>
                                </label>
                                <div
                                    wire:key="wrap-destinatario-{{ $tipoOfrendaId ?? '0' }}-{{ count($destinatariosDisponibles) }}"
                                    x-data
                                    x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'destinatarioId', '{{ $tipoOfrendaId ? 'Selecciona el destinatario' : 'Primero selecciona un tipo de donación' }}'))"
                                >
                                    <select
                                        x-ref="select"
                                        wire:model="destinatarioId"
                                        class="select2 form-select"
                                        @disabled(!$tipoOfrendaId)
                                    >
                                        <option value="">
                                            {{ $tipoOfrendaId ? 'Selecciona el destinatario' : 'Primero selecciona un tipo de donación' }}
                                        </option>
                                        @foreach($destinatariosDisponibles as $dest)
                                            <option value="{{ $dest->id }}" @selected($destinatarioId == $dest->id)>
                                                {{ $dest->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('destinatarioId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Moneda --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Moneda <span class="text-danger">*</span>
                                </label>
                                <div
                                    wire:ignore
                                    x-data
                                    x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'monedaId', 'Selecciona la moneda'))"
                                >
                                    <select
                                        x-ref="select"
                                        wire:model="monedaId"
                                        class="select2 form-select"
                                    >
                                        @foreach($monedas as $moneda)
                                            <option value="{{ $moneda->id }}" @selected($monedaId == $moneda->id)>
                                                {{ $moneda->nombre }} ({{ $moneda->nombre_corto ?? 'COP' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('monedaId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Valor a Donar --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-black">
                                    Valor a donar <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input
                                        type="number"
                                        step="any"
                                        min="1000"
                                        wire:model="valorDonacion"
                                        class="form-control @error('valorDonacion') is-invalid @enderror"
                                        placeholder="Ej: 50000"
                                    />
                                </div>
                                @error('valorDonacion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <button
                                type="button"
                                wire:click="irAPestana(2)"
                                class="btn btn-outline-secondary rounded-pill px-4"
                                wire:loading.attr="disabled"
                            >
                                <i class="ti ti-arrow-left me-1"></i> Volver
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary rounded-pill px-5"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="procesarDonacion">
                                    <i class="ti ti-lock-check me-1"></i> Donar ahora con ZonaPagos
                                </span>
                                <span wire:loading wire:target="procesarDonacion">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Conectando con ZonaPagos...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de Política de Tratamiento de Datos / Términos --}}
    <div class="modal fade" id="modalTerminosDonacion" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-black">Política de Tratamiento de Datos Personales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-black" style="max-height: 60vh; overflow-y: auto;">
                    {!! nl2br(e($terminosCondicionesTexto)) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary rounded-pill" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>
</div>
