<div>
    @php
        $titulosPasoMovil = [
            1 => 'Modalidad e identificación',
            2 => 'Información del donante',
            3 => 'Detalles de la donación',
        ];
    @endphp

    {{-- ================================================================== --}}
    {{-- HERO OSCURO Y STEPPER SUPERIOR                                     --}}
    {{-- ================================================================== --}}
    <section class="donation-hero">
        <div class="donation-hero-banner" style="background-image: var(--donacion-hero-bg, none);" aria-hidden="true"></div>
        <div class="container" style="max-width: 1080px;">
            <div class="eyebrow">Donaciones</div>
            <h1 class="hero-h">Haz tu <em>donación</em></h1>
            <p class="hero-sub">
                Tres pasos y menos de dos minutos. Tu aporte llega directo al ministerio o emisora que elijas.
            </p>

            <div class="steps-wrap">
                <ul class="steps">
                    <li class="{{ $pestanaActiva > 1 ? 'done' : ($pestanaActiva === 1 ? 'active' : '') }}">
                        <button
                            type="button"
                            class="step-trigger"
                            @if($pestanaActiva > 1) wire:click="irAPestana(1)" @else disabled @endif
                        >
                            <span class="step-circle">
                                <span>1</span>
                                <svg class="ic" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="step-label">Modalidad</span>
                        </button>
                        <div class="step-connector"></div>
                    </li>

                    <li class="{{ $pestanaActiva > 2 ? 'done' : ($pestanaActiva === 2 ? 'active' : '') }}">
                        <button
                            type="button"
                            class="step-trigger"
                            @if($pestanaActiva > 2) wire:click="irAPestana(2)" @else disabled @endif
                        >
                            <span class="step-circle">
                                <span>2</span>
                                <svg class="ic" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="step-label">Tus datos</span>
                        </button>
                        <div class="step-connector"></div>
                    </li>

                    <li class="{{ $pestanaActiva === 3 ? 'active' : '' }}">
                        <button type="button" class="step-trigger" disabled>
                            <span class="step-circle">
                                <span>3</span>
                                <svg class="ic" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="step-label">Donación</span>
                        </button>
                    </li>
                </ul>

                <div class="steps-mobile">
                    <div class="caption">
                        Paso {{ $pestanaActiva }} de 3 · {{ $titulosPasoMovil[$pestanaActiva] ?? '' }}
                    </div>
                    <div class="track">
                        <div class="fill" style="width: {{ round(($pestanaActiva / 3) * 100, 2) }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- CONTENIDO PRINCIPAL EN 2 COLUMNAS                                  --}}
    {{-- ================================================================== --}}
    <main class="donation-main">
        <div class="container" style="max-width: 1080px;">
            <div class="row g-4">
                {{-- Columna izquierda: Tarjeta del formulario en 3 pasos --}}
                <div class="col-lg-8">
                    <div class="donation-card">
                        <div class="card-body-pad">

                            {{-- ============================================== --}}
                            {{-- PASO 1: MODALIDAD E IDENTIFICACIÓN             --}}
                            {{-- ============================================== --}}
                            @if($pestanaActiva === 1)
                                <div wire:key="pestana-1" class="step-pane-fade">
                                    <div class="step-label-top">Paso 01</div>
                                    <h2 class="step-title">¿Cómo deseas realizar tu donación?</h2>
                                    <p class="step-subtitle">
                                        Selecciona si ya haces parte de nuestra plataforma o si deseas realizar una donación personal independiente.
                                    </p>

                                    <div class="row g-3 mb-4">
                                        {{-- Opción A: Usuario Registrado --}}
                                        <div class="col-md-6">
                                            <div
                                                wire:click="seleccionarModalidad('registrado')"
                                                wire:keydown.enter="seleccionarModalidad('registrado')"
                                                wire:keydown.space.prevent="seleccionarModalidad('registrado')"
                                                tabindex="0"
                                                role="radio"
                                                aria-checked="{{ $modalidad === 'registrado' ? 'true' : 'false' }}"
                                                class="modality-option {{ $modalidad === 'registrado' ? 'selected' : '' }}"
                                            >
                                                <div class="modality-icon">
                                                    <svg class="ic" viewBox="0 0 24 24">
                                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                                        <circle cx="9" cy="7" r="4"/>
                                                        <path d="m16 11 2 2 4-4"/>
                                                    </svg>
                                                </div>
                                                <h6>Ya estoy registrado en la plataforma</h6>
                                                <p>
                                                    Ingresa tu número de cédula o documento para buscar y cargar automáticamente tu información registrada.
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Opción B: Donación Personal / Independiente --}}
                                        <div class="col-md-6">
                                            <div
                                                wire:click="seleccionarModalidad('independiente')"
                                                wire:keydown.enter="seleccionarModalidad('independiente')"
                                                wire:keydown.space.prevent="seleccionarModalidad('independiente')"
                                                tabindex="0"
                                                role="radio"
                                                aria-checked="{{ $modalidad === 'independiente' ? 'true' : 'false' }}"
                                                class="modality-option {{ $modalidad === 'independiente' ? 'selected' : '' }}"
                                            >
                                                <div class="modality-icon">
                                                    <svg class="ic" viewBox="0 0 24 24">
                                                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                                                    </svg>
                                                </div>
                                                <h6>Quiero hacer una donación personal</h6>
                                                <p>
                                                    Realiza tu ofrenda o donación de forma independiente diligenciando tus datos básicos de contacto.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($modalidad === 'registrado')
                                        <div wire:key="form-busqueda-registrado">
                                            <form wire:submit="buscarUsuarioRegistrado">
                                                <div class="row g-3 align-items-end">
                                                    <div class="col-md-5">
                                                        <label class="field-label">Tipo de documento</label>
                                                        <div
                                                            wire:ignore
                                                            x-data
                                                            x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'tipoIdentificacionBusqueda', 'Selecciona tipo de documento'))"
                                                        >
                                                            <select
                                                                x-ref="select"
                                                                wire:model="tipoIdentificacionBusqueda"
                                                                class="select2 form-select"
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
                                                        <label class="field-label">
                                                            Número de identificación (Cédula) <span class="req">*</span>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            wire:model="identificacionBusqueda"
                                                            class="form-control @error('identificacionBusqueda') is-invalid @enderror"
                                                            placeholder="Ingresa tu número de documento sin puntos ni espacios"
                                                        />
                                                        @error('identificacionBusqueda')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                @if($usuarioNoEncontrado)
                                                    <div class="warning-banner d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4">
                                                        <div class="d-flex align-items-start gap-2">
                                                            <svg class="ic mt-1" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                                            <div>
                                                                <strong>No encontramos un usuario registrado con la identificación "{{ $identificacionBusqueda }}".</strong>
                                                                <div class="small">Verifica el número digitado o continúa realizando una donación personal independiente.</div>
                                                            </div>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            wire:click="continuarComoIndependiente"
                                                            class="btn-donacion-primary btn-donacion-sm flex-shrink-0"
                                                        >
                                                            Continuar como donación personal →
                                                        </button>
                                                    </div>
                                                @endif

                                                <div class="d-flex justify-content-end pane-footer">
                                                    <button type="submit" class="btn-donacion-primary" wire:loading.attr="disabled">
                                                        <span wire:loading.remove wire:target="buscarUsuarioRegistrado">
                                                            Buscar y continuar →
                                                        </span>
                                                        <span wire:loading wire:target="buscarUsuarioRegistrado">
                                                            <span class="spinner-border spinner-border-sm me-1"></span> Buscando...
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @else
                                        <div wire:key="form-continuar-independiente">
                                            <div class="info-banner mb-0">
                                                <svg class="ic" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                                                <div>
                                                    En el siguiente paso podrás ingresar tu nombre completo, identificación, teléfono, correo electrónico, país y dirección para registrar tu donación.
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-end pane-footer">
                                                <button
                                                    type="button"
                                                    wire:click="continuarComoIndependiente"
                                                    class="btn-donacion-primary"
                                                >
                                                    Continuar a mis datos →
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- ============================================== --}}
                            {{-- PASO 2: INFORMACIÓN BÁSICA DEL DONANTE         --}}
                            {{-- ============================================== --}}
                            @if($pestanaActiva === 2)
                                <div wire:key="pestana-2" class="step-pane-fade">
                                    <div class="step-label-top">Paso 02</div>
                                    <h2 class="step-title">Información del donante</h2>
                                    <p class="step-subtitle">
                                        @if($esRegistrado)
                                            Verifica tus datos registrados para continuar con tu donación y recibir el comprobante.
                                        @else
                                            Completa tus datos básicos para procesar la donación y enviarte el comprobante.
                                        @endif
                                    </p>

                                    @if($esRegistrado)
                                        {{-- Aviso para Usuario Registrado (Campos Bloqueados + Regla de Sesión) --}}
                                        <div class="info-banner d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                            <div class="d-flex align-items-start gap-2">
                                                <svg class="ic" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <div>
                                                    <strong>Hemos cargado tus datos registrados.</strong>
                                                    @if($usuarioEstaLogueado)
                                                        Por seguridad tus datos están bloqueados en este formulario. Como tienes tu sesión activa, puedes actualizarlos desde tu perfil si lo necesitas.
                                                    @else
                                                        Por seguridad tus datos se encuentran bloqueados y no pueden modificarse desde aquí. <strong>Si necesitas actualizar tu información personal, debes ingresar a tu cuenta en la plataforma y actualizar tus datos desde tu perfil.</strong>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($usuarioEstaLogueado && $userId)
                                                <a
                                                    href="{{ route('usuario.perfil', $userId) }}"
                                                    class="btn-donacion-primary btn-donacion-sm flex-shrink-0"
                                                >
                                                    <i class="ti ti-user-edit"></i> Actualizar datos
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        {{-- Aviso para Donación Personal / Independiente (Campos Obligatorios) --}}
                                        <div class="info-banner">
                                            <svg class="ic" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                                            <div>
                                                <strong>Donación personal (independiente).</strong> Por favor completa tus datos básicos. Todos los campos marcados con <strong>*</strong> (<strong>Nombre completo, Identificación, Teléfono, Correo electrónico, País y Dirección</strong>) son <strong>obligatorios</strong> para procesar tu donación y enviarte el comprobante.
                                            </div>
                                        </div>
                                    @endif

                                    <form wire:submit="continuarAPestana3">
                                        <div class="row g-3">
                                            {{-- Nombre completo --}}
                                            <div class="col-12">
                                                <label class="field-label">
                                                    Nombre completo <span class="req">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="nombreCompleto"
                                                    class="form-control @error('nombreCompleto') is-invalid @enderror"
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
                                                <label class="field-label">
                                                    Tipo de identificación <span class="req">*</span>
                                                </label>
                                                <div
                                                    wire:ignore
                                                    x-data
                                                    x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'tipoIdentificacionId', 'Selecciona un tipo de documento'))"
                                                >
                                                    <select
                                                        x-ref="select"
                                                        wire:model="tipoIdentificacionId"
                                                        class="select2 form-select"
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
                                                <label class="field-label">
                                                    Número de identificación <span class="req">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="identificacion"
                                                    class="form-control @error('identificacion') is-invalid @enderror"
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
                                                <label class="field-label">
                                                    Teléfono móvil <span class="req">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="telefono"
                                                    class="form-control @error('telefono') is-invalid @enderror"
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
                                                <label class="field-label">
                                                    Correo electrónico <span class="req">*</span>
                                                </label>
                                                <input
                                                    type="email"
                                                    wire:model="email"
                                                    class="form-control @error('email') is-invalid @enderror"
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
                                                <label class="field-label">
                                                    País <span class="req">*</span>
                                                </label>
                                                <div
                                                    wire:ignore
                                                    x-data
                                                    x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'paisId', 'Selecciona tu país'))"
                                                >
                                                    <select
                                                        x-ref="select"
                                                        wire:model="paisId"
                                                        class="select2 form-select"
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
                                                <label class="field-label">
                                                    Dirección de residencia <span class="req">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="direccion"
                                                    class="form-control @error('direccion') is-invalid @enderror"
                                                    placeholder="Ingresa tu dirección"
                                                    @readonly($esRegistrado)
                                                    @disabled($esRegistrado)
                                                />
                                                @error('direccion')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Aceptación de Términos y Habeas Data --}}
                                            <div class="col-12 mt-2">
                                                <div class="form-check">
                                                    <input
                                                        type="checkbox"
                                                        id="aceptaTerminosCheck"
                                                        wire:model="aceptaTerminos"
                                                        class="form-check-input @error('aceptaTerminos') is-invalid @enderror"
                                                    />
                                                    <label class="form-check-label" for="aceptaTerminosCheck" style="font-size: 14px; color: var(--ink);">
                                                        Acepto la
                                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalTerminosDonacion" class="policy-link">
                                                            política de tratamiento de datos personales y términos de donación
                                                        </a>
                                                        <span class="req">*</span>
                                                    </label>
                                                    @error('aceptaTerminos')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pane-footer">
                                            <button
                                                type="button"
                                                wire:click="irAPestana(1)"
                                                class="btn-donacion-ghost"
                                            >
                                                ← Volver
                                            </button>

                                            <button type="submit" class="btn-donacion-primary">
                                                Continuar a la donación →
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            {{-- ============================================== --}}
                            {{-- PASO 3: DETALLES DE LA DONACIÓN Y PAGO         --}}
                            {{-- ============================================== --}}
                            @if($pestanaActiva === 3)
                                @php
                                    $palabrasNombre = preg_split('/\s+/', trim($nombreCompleto)) ?: [];
                                    if (count($palabrasNombre) >= 2) {
                                        $inicialesDonante = mb_strtoupper(mb_substr($palabrasNombre[0], 0, 1) . mb_substr($palabrasNombre[1], 0, 1));
                                    } elseif (count($palabrasNombre) === 1 && $palabrasNombre[0] !== '') {
                                        $inicialesDonante = mb_strtoupper(mb_substr($palabrasNombre[0], 0, 2));
                                    } else {
                                        $inicialesDonante = 'DN';
                                    }
                                @endphp

                                <div wire:key="pestana-3" class="step-pane-fade">
                                    <div class="step-label-top">Paso 03</div>
                                    <h2 class="step-title">Detalles de la donación</h2>
                                    <p class="step-subtitle">
                                        Elige a quién va dirigida tu donación y el monto que deseas aportar.
                                    </p>

                                    <div class="donor-chip">
                                        <div class="avatar">{{ $inicialesDonante }}</div>
                                        <div>
                                            <div class="name">{{ $nombreCompleto }}</div>
                                            <div class="doc">
                                                @if($identificacion)
                                                    {{ $identificacion }} ·
                                                @endif
                                                {{ $esRegistrado ? 'Usuario registrado' : 'Donación personal' }}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="irAPestana(1)"
                                            class="edit-link"
                                        >
                                            Editar
                                        </button>
                                    </div>

                                    @if($mensajeErrorPasarela)
                                        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                                            <i class="ti ti-alert-triangle ti-md flex-shrink-0"></i>
                                            <div>{{ $mensajeErrorPasarela }}</div>
                                        </div>
                                    @endif

                                    <form wire:submit="procesarDonacion">
                                        <div class="row g-3 mb-3">
                                            {{-- Tipo de Donación --}}
                                            <div class="col-md-6">
                                                <label class="field-label">
                                                    Tipo de donación <span class="req">*</span>
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

                                            {{-- ¿A quién va dirigida? (Destinatario) --}}
                                            <div class="col-md-6">
                                                <label class="field-label">
                                                    ¿A quién va dirigida? <span class="req">*</span>
                                                </label>
                                                <div
                                                    wire:key="wrap-destinatario-{{ $tipoOfrendaId ?? '0' }}-{{ count($destinatariosDisponibles) }}"
                                                    x-data
                                                    x-init="$nextTick(() => window.initDonacionSelect2($refs.select, $wire, 'destinatarioId', '{{ $tipoOfrendaId ? 'Busca una emisora o destino...' : 'Primero selecciona un tipo de donación' }}'))"
                                                >
                                                    <select
                                                        x-ref="select"
                                                        wire:model="destinatarioId"
                                                        class="select2 form-select"
                                                        @disabled(!$tipoOfrendaId)
                                                    >
                                                        <option value="">
                                                            {{ $tipoOfrendaId ? 'Busca una emisora o destino...' : 'Primero selecciona un tipo de donación' }}
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
                                        </div>

                                        <div class="row g-3 mb-2">
                                            {{-- Moneda --}}
                                            <div class="col-md-5">
                                                <label class="field-label">
                                                    Moneda <span class="req">*</span>
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

                                            {{-- Valor a Donar con chips sugeridos --}}
                                            <div
                                                class="col-md-7"
                                                x-data="{
                                                    montoActual: @js((string) $valorDonacion),
                                                    otroValor: @js($valorDonacion !== '' && !in_array((string) $valorDonacion, ['20000', '50000', '100000', '200000'], true)),
                                                    seleccionarMonto(monto) {
                                                        this.otroValor = false;
                                                        this.montoActual = String(monto);
                                                        this.$refs.amountInput.value = String(monto);
                                                        this.$refs.amountInput.dispatchEvent(new Event('input', { bubbles: true }));
                                                    },
                                                    seleccionarOtro() {
                                                        this.otroValor = true;
                                                        this.montoActual = '';
                                                        this.$refs.amountInput.value = '';
                                                        this.$refs.amountInput.dispatchEvent(new Event('input', { bubbles: true }));
                                                        this.$nextTick(() => this.$refs.amountInput.focus());
                                                    }
                                                }"
                                            >
                                                <label class="field-label">
                                                    Valor a donar <span class="req">*</span>
                                                </label>

                                                <div class="amount-chips">
                                                    <button
                                                        type="button"
                                                        class="amount-chip"
                                                        :class="{ 'active': !otroValor && String(montoActual) === '20000' }"
                                                        x-on:click="seleccionarMonto(20000)"
                                                    >
                                                        $20.000
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="amount-chip"
                                                        :class="{ 'active': !otroValor && String(montoActual) === '50000' }"
                                                        x-on:click="seleccionarMonto(50000)"
                                                    >
                                                        $50.000
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="amount-chip"
                                                        :class="{ 'active': !otroValor && String(montoActual) === '100000' }"
                                                        x-on:click="seleccionarMonto(100000)"
                                                    >
                                                        $100.000
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="amount-chip"
                                                        :class="{ 'active': !otroValor && String(montoActual) === '200000' }"
                                                        x-on:click="seleccionarMonto(200000)"
                                                    >
                                                        $200.000
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="amount-chip"
                                                        :class="{ 'active': otroValor }"
                                                        x-on:click="seleccionarOtro()"
                                                    >
                                                        Otro valor
                                                    </button>
                                                </div>

                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        x-ref="amountInput"
                                                        type="number"
                                                        step="any"
                                                        min="1000"
                                                        wire:model="valorDonacion"
                                                        x-on:input="montoActual = $event.target.value; if (!['20000','50000','100000','200000'].includes(String(montoActual))) { otroValor = true; } else { otroValor = false; }"
                                                        class="form-control @error('valorDonacion') is-invalid @enderror"
                                                        placeholder="Ej: 50000"
                                                    />
                                                </div>
                                                @error('valorDonacion')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pane-footer">
                                            <button
                                                type="button"
                                                wire:click="irAPestana(2)"
                                                class="btn-donacion-ghost"
                                                wire:loading.attr="disabled"
                                            >
                                                ← Volver
                                            </button>

                                            <button
                                                type="submit"
                                                class="btn-donacion-primary"
                                                wire:loading.attr="disabled"
                                            >
                                                <span wire:loading.remove wire:target="procesarDonacion" class="d-inline-flex align-items-center gap-2">
                                                    <svg class="ic" style="width: 16px; height: 16px;" viewBox="0 0 24 24">
                                                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                                                        <path d="m9 12 2 2 4-4"/>
                                                    </svg>
                                                    Donar ahora con ZonaPagos
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
                </div>

                {{-- Columna derecha: Tarjeta oscura de Transparencia --}}
                <div class="col-lg-4">
                    <aside class="sidebar-note">
                        <div class="eyebrow">Transparencia</div>
                        <h6>Tu donación en cifras</h6>
                        <ul>
                            <li>
                                <svg class="ic" viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                <span>El 100% de tu ofrenda se destina directamente al ministerio o emisora que elijas.</span>
                            </li>
                            <li>
                                <svg class="ic" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                <span>Recibirás tu comprobante de donación automáticamente a tu correo.</span>
                            </li>
                            <li>
                                <svg class="ic" viewBox="0 0 24 24"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4M16 13H8M16 17H8"/></svg>
                                <span>Puedes solicitar tu certificado de donación para deducción de renta.</span>
                            </li>
                        </ul>

                        <div class="sidebar-divider"></div>

                        <div class="trust-row">
                            <svg class="ic" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Conexión cifrada y procesada por ZonaPagos</span>
                        </div>
                        <div class="trust-row">
                            <svg class="ic" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>Cumplimos la Ley 1581 de protección de datos</span>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </main>

    {{-- Modal de Política de Tratamiento de Datos / Términos --}}
    <div class="modal fade" id="modalTerminosDonacion" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" style="color: var(--ink); font-family: var(--cd);">
                        Política de Tratamiento de Datos Personales
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto; color: var(--ink); font-size: 14.5px; line-height: 1.7;">
                    {!! nl2br(e($terminosCondicionesTexto)) !!}
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn-donacion-primary btn-donacion-sm" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>
</div>
