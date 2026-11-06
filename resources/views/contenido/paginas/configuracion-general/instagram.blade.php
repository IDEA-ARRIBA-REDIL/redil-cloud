@extends('layouts/layoutMaster')

@section('title', 'Instagram')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('configuracion-general.configuracionGeneral') }}"
            class="btn btn-sm btn-icon btn-outline-primary rounded-pill" aria-label="Volver a configuración">
            <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0">Instagram de la iglesia</h4>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <h5><i class="ti ti-brand-instagram me-2"></i>Publicaciones en el dashboard</h5>
            <p>Conecta una cuenta profesional de Instagram (empresa o creador). Cada iglesia administra su propia cuenta.
                Solo se solicita acceso básico para consultar el perfil y sus publicaciones.</p>

            @if ($configuracion->instagram_user_id)
                <p class="mb-2">Cuenta vinculada: <strong>{{ '@'.$configuracion->instagram_username }}</strong></p>
                @if ($configuracion->instagram_expires_at?->isFuture())
                    <p class="text-muted">Conexión vigente hasta {{ $configuracion->instagram_expires_at->format('d/m/Y') }}.</p>
                @else
                    <div class="alert alert-warning">La conexión venció. Conecta la cuenta nuevamente para recuperar las publicaciones.</div>
                @endif
            @else
                <p class="text-muted">Todavía no hay una cuenta conectada.</p>
            @endif

            @if (! $configured)
                <div class="alert alert-warning">
                    Falta configurar la aplicación de Meta en el servidor. Comparte las indicaciones de abajo con el administrador.
                </div>
            @endif
            @if (! $schemaReady)
                <div class="alert alert-warning">Falta aplicar la migración de Instagram en esta iglesia.</div>
            @endif

            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('instagram.connect') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary rounded-pill" @disabled(! $configured || ! $schemaReady)>
                        {{ $configuracion->instagram_user_id ? 'Volver a conectar Instagram' : 'Conectar Instagram' }}
                    </button>
                </form>
                @if ($configuracion->instagram_user_id)
                    <form method="POST" action="{{ route('instagram.refresh') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary rounded-pill">Renovar conexión</button>
                    </form>
                    <form method="POST" action="{{ route('instagram.disconnect') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-pill">Desvincular cuenta</button>
                    </form>
                @endif
            </div>
            <p class="small text-muted mt-4 mb-0">
                Las publicaciones se actualizan cada 10 minutos. La conexión intenta renovarse al consultar el dashboard
                cuando quedan menos de 7 días de vigencia. Si vence durante un período sin visitas o revocas el acceso,
                vuelve a conectar la cuenta. Desvincular elimina la conexión guardada en REDIL; el permiso de la aplicación
                también puede revocarse desde Instagram.
            </p>
        </div>
    </div>

    <details class="card mt-4">
        <summary class="card-header">Configuración inicial para el administrador del servidor</summary>
        <div class="card-body">
            <ol>
                <li>Crea una aplicación en Meta for Developers y configura Instagram API con Instagram Login.</li>
                <li>En el entorno del servidor define <code>INSTAGRAM_CLIENT_ID</code> y <code>INSTAGRAM_CLIENT_SECRET</code>
                    con el identificador y el secreto de la aplicación de Instagram. Nunca uses la contraseña de Instagram.</li>
                <li>Configura <code>INSTAGRAM_GRAPH_VERSION</code> con una versión admitida por tu aplicación
                    (valor predeterminado: <code>v25.0</code>).</li>
                <li>Aplica la migración de Instagram en las bases de datos de las iglesias y actualiza la caché de configuración
                    si el servidor la utiliza.</li>
                <li>Registra esta URL exacta entre las URI de redirección OAuth válidas de Meta, usando el dominio HTTPS de esta iglesia:</li>
            </ol>
            <p class="border rounded p-3 text-break"><code>{{ $callbackUrl }}</code></p>
            <p>Registra una URL por dominio de iglesia. En desarrollo, usa una cuenta autorizada como probadora de la aplicación.
                Para conectar cuentas de clientes, completa los requisitos de publicación y revisión de Meta para
                <code>instagram_business_basic</code>, incluyendo las políticas y la eliminación de datos que Meta solicite.</p>
            <p class="mb-0">Luego pulsa <strong>Conectar Instagram</strong> y autoriza la cuenta de esta iglesia.
                REDIL obtiene el identificador, el usuario y el token automáticamente; no es necesario copiarlos en un formulario.</p>
        </div>
    </details>
@endsection
