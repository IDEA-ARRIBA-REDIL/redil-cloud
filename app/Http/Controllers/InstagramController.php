<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstagramCallbackRequest;
use App\Models\Configuracion;
use App\Services\InstagramService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class InstagramController extends Controller
{
    public function __construct(private InstagramService $instagram) {}

    public function index(Request $request): View
    {
        $this->authorizeConfiguration($request);

        return view('contenido.paginas.configuracion-general.instagram', [
            'configuracion' => Configuracion::firstOrFail(),
            'configured' => $this->instagram->configured(),
            'schemaReady' => Schema::hasColumn('configuraciones', 'instagram_access_token'),
            'callbackUrl' => route('instagram.callback'),
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $this->authorizeConfiguration($request);
        $this->requireStorage();
        if (! $this->instagram->configured()) {
            return to_route('instagram.index')->with('error', 'El administrador del servidor debe configurar la aplicación de Instagram en Meta.');
        }
        $state = Str::random(64);
        $redirectUri = route('instagram.callback');
        if (parse_url($redirectUri, PHP_URL_SCHEME) !== 'https') {
            return to_route('instagram.index')->with('error', 'La conexión con Meta requiere un dominio con HTTPS.');
        }
        $request->session()->put($this->stateKey(), [
            'hash' => hash('sha256', $state),
            'tenant' => (string) tenant('id'),
            'user' => (string) $request->user()->getAuthIdentifier(),
            'expires' => now()->addMinutes(10)->timestamp,
            'redirect_uri' => $redirectUri,
        ]);

        return redirect()->away($this->instagram->authorizationUrl($state, $redirectUri));
    }

    public function callback(InstagramCallbackRequest $request): RedirectResponse
    {
        $this->authorizeConfiguration($request);
        $this->requireStorage();
        $pending = $request->session()->pull($this->stateKey());
        $data = $request->validated();
        abort_unless(is_array($pending)
            && ($pending['tenant'] ?? null) === (string) tenant('id')
            && ($pending['user'] ?? null) === (string) $request->user()->getAuthIdentifier()
            && ($pending['expires'] ?? 0) > now()->timestamp
            && hash_equals($pending['hash'] ?? '', hash('sha256', $data['state'])), 403, 'La conexión venció o no corresponde a esta sesión.');

        if (! empty($data['error']) || empty($data['code'])) {
            return to_route('instagram.index')->with('error', 'No se autorizó la conexión de Instagram. Puedes intentarlo nuevamente.');
        }
        if (! $this->instagram->configured()) {
            return to_route('instagram.index')->with('error', 'La aplicación de Instagram no está configurada en el servidor.');
        }
        try {
            $this->instagram->connect(Configuracion::firstOrFail(), $data['code'], $pending['redirect_uri']);
        } catch (RuntimeException|DecryptException) {
            return to_route('instagram.index')->with('error', 'No se pudo conectar Instagram. Verifica la cuenta profesional, los permisos y la configuración de Meta.');
        }

        return to_route('instagram.index')->with('success', 'Instagram conectado. Las publicaciones aparecerán en el dashboard.');
    }

    public function refresh(Request $request): RedirectResponse
    {
        $this->authorizeConfiguration($request);
        $this->requireStorage();
        try {
            $this->instagram->refreshToken(Configuracion::firstOrFail());
        } catch (RuntimeException|DecryptException) {
            return to_route('instagram.index')->with('error', 'No fue posible renovar la conexión. Conecta Instagram nuevamente.');
        }

        return to_route('instagram.index')->with('success', 'La conexión está vigente. Meta permite renovar tokens después de sus primeras 24 horas.');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $this->authorizeConfiguration($request);
        $this->requireStorage();
        $request->session()->forget($this->stateKey());
        $this->instagram->disconnect(Configuracion::firstOrFail());

        return to_route('instagram.index')->with('success', 'Cuenta desvinculada de esta iglesia. Puedes revocar también el acceso desde Instagram.');
    }

    private function authorizeConfiguration(Request $request): void
    {
        abort_unless(tenant('id') !== null, 404);
        $role = $request->user()?->roles()->wherePivot('activo', true)->first();
        abort_unless($role?->hasAnyPermission('configuraciones.subitem_general'), 403);
    }

    private function stateKey(): string
    {
        return 'instagram_oauth_'.hash('sha256', (string) tenant('id'));
    }

    private function requireStorage(): void
    {
        abort_unless(Schema::hasColumn('configuraciones', 'instagram_access_token'), 409,
            'El administrador debe aplicar la migración de Instagram en esta iglesia.');
    }
}
