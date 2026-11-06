<?php

namespace App\Services;

use App\Models\Configuracion;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class InstagramService
{
    public static function redactTelemetryUrl(string $url): string
    {
        if (in_array(parse_url($url, PHP_URL_HOST), ['api.instagram.com', 'graph.instagram.com', 'www.instagram.com'], true)
            || parse_url($url, PHP_URL_PATH) === '/configuracion/instagram/callback') {
            return explode('?', $url, 2)[0];
        }

        return $url;
    }

    public function configured(): bool
    {
        return filled(config('services.instagram.client_id'))
            && filled(config('services.instagram.client_secret'))
            && preg_match('/^v\d+\.\d+$/', (string) config('services.instagram.graph_version')) === 1;
    }

    public function authorizationUrl(string $state, string $redirectUri): string
    {
        return 'https://www.instagram.com/oauth/authorize?'.http_build_query([
            'client_id' => config('services.instagram.client_id'),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'instagram_business_basic',
            'state' => $state,
            'enable_fb_login' => '0',
            'force_authentication' => '1',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function connect(Configuracion $configuracion, string $code, string $redirectUri): void
    {
        $short = $this->request('POST', 'https://api.instagram.com/oauth/access_token', [
            'client_id' => config('services.instagram.client_id'),
            'client_secret' => config('services.instagram.client_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);
        if (isset($short['data'])) {
            if (! is_array($short['data']) || count($short['data']) !== 1) {
                throw new RuntimeException('La respuesta de Instagram no identifica una sola cuenta.');
            }
            $short = $short['data'][0];
        }
        if (! is_array($short) || ! is_string($short['access_token'] ?? null) || $short['access_token'] === '') {
            throw new RuntimeException('Instagram no entregó un token de acceso.');
        }

        $long = $this->request('GET', 'https://graph.instagram.com/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => config('services.instagram.client_secret'),
            'access_token' => $short['access_token'],
        ]);
        $this->validateToken($long);
        $profile = $this->request('GET', $this->graphUrl('me'), ['fields' => 'user_id,username'], $long['access_token']);
        if (! preg_match('/^\d+$/', (string) ($profile['user_id'] ?? ''))
            || ! preg_match('/^[a-zA-Z0-9._]{1,30}$/', (string) ($profile['username'] ?? ''))) {
            throw new RuntimeException('Instagram no entregó un perfil válido.');
        }

        $this->forgetFeed($configuracion);
        $configuracion->forceFill([
            'instagram_user_id' => (string) $profile['user_id'],
            'instagram_username' => $profile['username'],
            'instagram_access_token' => $long['access_token'],
            'instagram_expires_at' => now()->addSeconds((int) $long['expires_in']),
            'instagram_refreshed_at' => now(),
        ])->save();
    }

    /**
     * @return array{posts: array<int, array<string, string>>, unavailable: bool}
     */
    public function feed(Configuracion $configuracion): array
    {
        $empty = ['posts' => [], 'unavailable' => true];
        if (! $configuracion->instagram_user_id || ! $configuracion->getRawOriginal('instagram_access_token')
            || ! $configuracion->instagram_expires_at?->isFuture()) {
            return $empty;
        }

        $key = $this->cacheKey($configuracion);
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            if ($configuracion->instagram_expires_at->lt(now()->addDays(7))) {
                $this->refreshToken($configuracion);
            }
            if (! $configuracion->getRawOriginal('instagram_access_token')) {
                return $empty;
            }
            $response = $this->request('GET', $this->graphUrl($configuracion->instagram_user_id.'/media'), [
                'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink',
                'limit' => 6,
            ], $configuracion->instagram_access_token);
            if (! is_array($response['data'] ?? null)) {
                throw new RuntimeException('Instagram no entregó publicaciones válidas.');
            }
            $posts = [];
            foreach (array_slice($response['data'], 0, 6) as $post) {
                if (! is_array($post)) {
                    continue;
                }
                $permalink = $post['permalink'] ?? '';
                if (! is_string($permalink) || ! $this->safeUrl($permalink)
                    || ! in_array(parse_url($permalink, PHP_URL_HOST), ['www.instagram.com', 'instagram.com'], true)) {
                    continue;
                }
                $mediaType = $post['media_type'] ?? 'IMAGE';
                $image = $mediaType === 'VIDEO' ? ($post['thumbnail_url'] ?? '') : ($post['media_url'] ?? '');
                $posts[] = [
                    'id' => (string) ($post['id'] ?? ''),
                    'caption' => is_string($post['caption'] ?? null) ? $post['caption'] : '',
                    'media_type' => is_string($mediaType) ? $mediaType : 'IMAGE',
                    'image' => is_string($image) && $this->safeUrl($image) ? $image : '',
                    'permalink' => $permalink,
                ];
            }
            $result = ['posts' => $posts, 'unavailable' => false];
            Cache::put($this->cacheKey($configuracion), $result, now()->addMinutes(10));

            return $result;
        } catch (RuntimeException|\Illuminate\Contracts\Encryption\DecryptException) {
            Cache::put($this->cacheKey($configuracion), $empty, now()->addMinute());

            return $empty;
        }
    }

    public function refreshToken(Configuracion $configuracion): void
    {
        $lock = Cache::lock('instagram:refresh:'.tenant('id').':'.$configuracion->getKey(), 30);
        if (! $lock->get()) {
            return;
        }
        try {
            $configuracion->refresh();
            if (! $configuracion->instagram_expires_at?->isFuture()) {
                throw new RuntimeException('La conexión venció. Conecta Instagram de nuevo.');
            }
            if ($configuracion->instagram_refreshed_at?->gt(now()->subDay())) {
                return;
            }
            $originalToken = $configuracion->getRawOriginal('instagram_access_token');
            $token = $this->request('GET', 'https://graph.instagram.com/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $configuracion->instagram_access_token,
            ]);
            $this->validateToken($token);
            $this->forgetFeed($configuracion);
            $configuracion->forceFill([
                'instagram_access_token' => $token['access_token'],
                'instagram_expires_at' => now()->addSeconds((int) $token['expires_in']),
                'instagram_refreshed_at' => now(),
            ]);
            $attributes = $configuracion->getAttributes();
            Configuracion::query()->whereKey($configuracion->getKey())
                ->where('instagram_access_token', $originalToken)
                ->update(array_intersect_key($attributes, array_flip([
                    'instagram_access_token', 'instagram_expires_at', 'instagram_refreshed_at',
                ])));
            $configuracion->refresh();
        } finally {
            $lock->release();
        }
    }

    public function disconnect(Configuracion $configuracion): void
    {
        $this->forgetFeed($configuracion);
        $configuracion->forceFill([
            'instagram_user_id' => null,
            'instagram_username' => null,
            'instagram_access_token' => null,
            'instagram_expires_at' => null,
            'instagram_refreshed_at' => null,
        ])->save();
    }

    public function forgetFeed(Configuracion $configuracion): void
    {
        Cache::forget($this->cacheKey($configuracion));
    }

    private function cacheKey(Configuracion $configuracion): string
    {
        return 'instagram:feed:'.tenant('id').':'.$configuracion->getKey().':'
            .hash('sha256', (string) $configuracion->getRawOriginal('instagram_access_token'));
    }

    private function graphUrl(string $path): string
    {
        return 'https://graph.instagram.com/'.config('services.instagram.graph_version').'/'.$path;
    }

    private function safeUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && parse_url($url, PHP_URL_SCHEME) === 'https'
            && parse_url($url, PHP_URL_USER) === null
            && parse_url($url, PHP_URL_PASS) === null;
    }

    /** @param array<string, mixed> $data */
    private function validateToken(array $data): void
    {
        if (! is_string($data['access_token'] ?? null) || $data['access_token'] === ''
            || ! is_numeric($data['expires_in'] ?? null) || (int) $data['expires_in'] <= 0) {
            throw new RuntimeException('Instagram no entregó un token con vigencia válida.');
        }
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    private function request(string $method, string $url, array $parameters, ?string $token = null): array
    {
        $client = Http::acceptJson()->connectTimeout(3)->timeout(10)->withoutRedirecting();
        if ($token !== null) {
            $client = $client->withToken($token);
        }
        try {
            $response = $method === 'POST'
                ? $client->asForm()->post($url, $parameters)
                : $client->get($url, $parameters);
        } catch (ConnectionException) {
            throw new RuntimeException('No fue posible comunicarse con Instagram. Intenta nuevamente.');
        }
        if (! $response->successful() || ! is_array($response->json()) || isset($response->json()['error'])) {
            throw new RuntimeException('Instagram rechazó la solicitud. Revisa los permisos o conecta la cuenta nuevamente.');
        }

        return $response->json();
    }
}
