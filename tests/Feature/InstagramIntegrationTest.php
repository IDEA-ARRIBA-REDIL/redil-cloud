<?php

namespace Tests\Feature;

use App\Http\Controllers\InstagramController;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\RevisarSuspensionTenant;
use App\Livewire\Dashboard\InstagramFeed;
use App\Models\Configuracion;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InstagramService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Mockery;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class InstagramIntegrationTest extends TestCase
{
    private InstagramService $instagram;

    private Configuracion $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'instagram_test',
            'database.connections.instagram_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'cache.default' => 'array',
            'session.driver' => 'array',
            'logging.default' => 'null',
            'services.instagram.client_id' => 'app-id',
            'services.instagram.client_secret' => 'app-secret',
            'services.instagram.graph_version' => 'v25.0',
        ]);
        DB::purge('instagram_test');
        Schema::create('configuraciones', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
        $this->migration()->up();
        $this->configuration = Configuracion::query()->create();
        $this->instagram = app(InstagramService::class);
        Cache::flush();
        Http::preventStrayRequests();
        $this->setTenant('church-a');
        $this->withoutMiddleware([
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            RevisarSuspensionTenant::class,
            LocaleMiddleware::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);
        $this->actingAs($this->userWithPermission(true));
    }

    protected function tearDown(): void
    {
        app(\Stancl\Tenancy\Tenancy::class)->tenant = null;
        DB::purge('instagram_test');
        parent::tearDown();
    }

    public function test_connect_requests_only_basic_access_and_binds_state_to_tenant_and_user(): void
    {
        $response = $this->post('https://church.test/configuracion/instagram/conectar')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('instagram_business_basic', $query['scope']);
        $this->assertSame('https://church.test/configuracion/instagram/callback', $query['redirect_uri']);
        $this->assertSame(64, strlen($query['state']));
        $this->assertSame('church-a', session($this->stateKey().'.tenant'));
        $this->assertSame('42', session($this->stateKey().'.user'));
        $this->assertStringNotContainsString('app-secret', $response->headers->get('Location'));
        Http::assertNothingSent();
    }

    public function test_missing_server_credentials_and_non_https_prevent_connection(): void
    {
        config(['services.instagram.client_secret' => null]);
        $this->post('https://church.test/configuracion/instagram/conectar')->assertSessionHas('error');
        $this->assertNull(session($this->stateKey()));
        config(['services.instagram.client_secret' => 'app-secret']);
        $this->post('http://church.test/configuracion/instagram/conectar')->assertSessionHas('error');
        $this->assertNull(session($this->stateKey()));
        Http::assertNothingSent();
    }

    public function test_callback_connects_account_and_encrypts_and_hides_token(): void
    {
        $this->fakeConnection();
        $this->withSession([$this->stateKey() => $this->pending()])
            ->get($this->callbackUrl())->assertRedirect()->assertSessionHas('success');
        $this->configuration->refresh();
        $this->assertSame('12345', $this->configuration->instagram_user_id);
        $this->assertSame('iglesia.test', $this->configuration->instagram_username);
        $this->assertSame('long-secret', $this->configuration->instagram_access_token);
        $this->assertNotSame('long-secret', $this->configuration->getRawOriginal('instagram_access_token'));
        $this->assertArrayNotHasKey('instagram_access_token', $this->configuration->toArray());
        $this->assertTrue($this->configuration->instagram_expires_at->isFuture());
        $this->assertNull(session($this->stateKey()));
        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://api.instagram.com/oauth/access_token'
            && $request['redirect_uri'] === 'https://church.test/configuracion/instagram/callback'
            && $request['code'] === 'code-secret');
        Http::assertSent(fn (ClientRequest $request): bool => str_contains($request->url(), '/v25.0/me')
            && $request->hasHeader('Authorization', 'Bearer long-secret'));
        $this->get($this->callbackUrl())->assertForbidden();
        Http::assertSentCount(3);
    }

    public function test_flat_oauth_token_response_is_supported(): void
    {
        $this->fakeConnection(false);
        $this->instagram->connect($this->configuration, 'code-secret', 'https://church.test/callback');
        $this->assertSame('long-secret', $this->configuration->fresh()->instagram_access_token);
    }

    public function test_wrong_state_expired_state_and_other_tenant_or_user_are_rejected(): void
    {
        foreach ([
            ['hash' => hash('sha256', 'different')],
            ['expires' => now()->subMinute()->timestamp],
            ['tenant' => 'church-b'],
            ['user' => '43'],
        ] as $change) {
            $this->withSession([$this->stateKey() => array_replace($this->pending(), $change)])
                ->get($this->callbackUrl())->assertForbidden();
        }
        Http::assertNothingSent();
        $this->assertNull($this->configuration->fresh()->instagram_access_token);
    }

    public function test_invalid_callback_does_not_flash_authorization_code(): void
    {
        $this->get('https://church.test/configuracion/instagram/callback?code=code-secret')->assertStatus(400);
        $this->assertNull(session('_old_input.code'));
        Http::assertNothingSent();
    }

    public function test_cancelled_authorization_preserves_previous_connection(): void
    {
        $this->connected();
        $this->withSession([$this->stateKey() => $this->pending()])
            ->get('https://church.test/configuracion/instagram/callback?state='.str_repeat('a', 64).'&error=access_denied')
            ->assertSessionHas('error');
        $this->assertSame('existing-secret', $this->configuration->fresh()->instagram_access_token);
        Http::assertNothingSent();
    }

    public function test_failed_meta_exchange_preserves_previous_account_and_omits_remote_error(): void
    {
        $this->connected();
        Http::fake(['*' => Http::response(['error' => ['message' => 'sensitive-remote-content']], 400)]);
        $response = $this->withSession([$this->stateKey() => $this->pending()])->get($this->callbackUrl());
        $response->assertSessionHas('error');
        $this->assertStringNotContainsString('sensitive-remote-content', session('error'));
        $this->assertSame('existing-secret', $this->configuration->fresh()->instagram_access_token);
    }

    public function test_only_active_role_can_manage_instagram(): void
    {
        $this->connected();
        $this->actingAs($this->userWithPermission(false));
        $this->get('https://church.test/configuracion/instagram')->assertForbidden();
        $this->post('https://church.test/configuracion/instagram/conectar')->assertForbidden();
        $this->post('https://church.test/configuracion/instagram/renovar')->assertForbidden();
        $this->delete('https://church.test/configuracion/instagram')->assertForbidden();
        $this->get($this->callbackUrl())->assertForbidden();
        Http::assertNothingSent();
        $this->assertSame('existing-secret', $this->configuration->fresh()->instagram_access_token);
    }

    public function test_guest_cannot_manage_connection(): void
    {
        auth()->logout();
        $this->post('https://church.test/configuracion/instagram/conectar')->assertRedirect();
        Http::assertNothingSent();
    }

    public function test_feed_is_cached_and_cache_is_separated_by_tenant(): void
    {
        $this->connected();
        Http::fake(['*' => Http::response(['data' => [$this->postData()]], 200)]);
        $this->assertCount(1, $this->instagram->feed($this->configuration)['posts']);
        $this->instagram->feed($this->configuration);
        Http::assertSentCount(1);
        $this->setTenant('church-b');
        $this->instagram->feed($this->configuration);
        Http::assertSentCount(2);
    }

    public function test_feed_without_connection_or_with_expired_token_makes_no_requests(): void
    {
        $this->assertTrue($this->instagram->feed($this->configuration)['unavailable']);
        $this->connected(['instagram_expires_at' => now()->subDay()]);
        $this->assertTrue($this->instagram->feed($this->configuration)['unavailable']);
        Http::assertNothingSent();
    }

    public function test_feed_handles_images_reels_carousels_and_unsafe_links(): void
    {
        $this->connected();
        Http::fake(['*' => Http::response(['data' => [
            $this->postData(),
            array_replace($this->postData(), ['id' => '2', 'media_type' => 'VIDEO', 'thumbnail_url' => 'https://cdninstagram.com/reel.jpg']),
            array_replace($this->postData(), ['id' => '3', 'media_type' => 'CAROUSEL_ALBUM']),
            array_replace($this->postData(), ['id' => '4', 'permalink' => 'javascript:alert(1)']),
            array_replace($this->postData(), ['id' => '5', 'permalink' => 'https://evil.test/post']),
            array_replace($this->postData(), ['id' => '6', 'media_url' => 'javascript:alert(1)']),
        ]])]);
        $feed = $this->instagram->feed($this->configuration);
        $this->assertFalse($feed['unavailable']);
        $this->assertCount(4, $feed['posts']);
        $this->assertSame('https://cdninstagram.com/reel.jpg', $feed['posts'][1]['image']);
        $this->assertSame('CAROUSEL_ALBUM', $feed['posts'][2]['media_type']);
        $this->assertSame('', $feed['posts'][3]['image']);
        $this->assertStringNotContainsString('existing-secret', json_encode($feed));
    }

    public function test_meta_failure_is_cached_briefly_without_breaking_dashboard(): void
    {
        $this->connected();
        Http::fake(['*' => Http::response(['error' => ['message' => 'secret-error']], 429)]);
        $this->assertSame(['posts' => [], 'unavailable' => true], $this->instagram->feed($this->configuration));
        $this->instagram->feed($this->configuration);
        Http::assertSentCount(1);
    }

    public function test_timeout_is_handled_without_exposing_connection_details(): void
    {
        $this->connected();
        Http::fake(['*' => Http::failedConnection()]);
        $this->assertTrue($this->instagram->feed($this->configuration)['unavailable']);
    }

    public function test_empty_feed_is_not_reported_as_a_connection_failure(): void
    {
        $this->connected();
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->assertSame(['posts' => [], 'unavailable' => false], $this->instagram->feed($this->configuration));
    }

    public function test_expiring_token_is_renewed_encrypted_and_used_for_feed(): void
    {
        $this->connected(['instagram_expires_at' => now()->addDays(3), 'instagram_refreshed_at' => now()->subDays(50)]);
        Http::fake([
            'https://graph.instagram.com/refresh_access_token*' => Http::response(['access_token' => 'renewed-secret', 'expires_in' => 5184000]),
            'https://graph.instagram.com/v25.0/*' => Http::response(['data' => [$this->postData()]]),
        ]);
        $this->assertFalse($this->instagram->feed($this->configuration)['unavailable']);
        $this->assertSame('renewed-secret', $this->configuration->fresh()->instagram_access_token);
        $this->assertNotSame('renewed-secret', $this->configuration->fresh()->getRawOriginal('instagram_access_token'));
        Http::assertSent(fn (ClientRequest $request): bool => str_contains($request->url(), '/media')
            && $request->hasHeader('Authorization', 'Bearer renewed-secret'));
    }

    public function test_refresh_during_first_day_does_not_call_meta(): void
    {
        $this->connected(['instagram_refreshed_at' => now()]);
        $this->instagram->refreshToken($this->configuration);
        Http::assertNothingSent();
    }

    public function test_refresh_cannot_restore_a_concurrently_disconnected_account(): void
    {
        $this->connected(['instagram_refreshed_at' => now()->subDays(2)]);
        Http::fake(function (): \Illuminate\Http\Client\Response|\GuzzleHttp\Promise\PromiseInterface {
            $this->instagram->disconnect($this->configuration->fresh());

            return Http::response(['access_token' => 'renewed-secret', 'expires_in' => 5184000]);
        });
        $this->instagram->refreshToken($this->configuration);
        $this->assertNull($this->configuration->fresh()->instagram_access_token);
        $this->assertNull($this->configuration->fresh()->instagram_user_id);
    }

    public function test_disconnect_clears_connection_pending_oauth_and_cached_posts(): void
    {
        $this->connected();
        Http::fake(['*' => Http::response(['data' => [$this->postData()]])]);
        $this->instagram->feed($this->configuration);
        $this->withSession([$this->stateKey() => $this->pending()])
            ->delete('https://church.test/configuracion/instagram')->assertSessionHas('success');
        $this->assertNull($this->configuration->fresh()->instagram_access_token);
        $this->assertNull($this->configuration->fresh()->instagram_user_id);
        $this->assertNull(session($this->stateKey()));
        $this->assertEmpty($this->instagram->feed($this->configuration->fresh())['posts']);
        Http::assertSentCount(1);
    }

    public function test_widget_escapes_captions_and_does_not_expose_token(): void
    {
        $this->connected();
        Http::fake(['*' => Http::response(['data' => [array_replace($this->postData(), ['caption' => '<script>alert(1)</script>'])]])]);
        Livewire::test(InstagramFeed::class)
            ->assertSee('Síguenos en Instagram')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('existing-secret', false)
            ->assertDontSee('instagram_access_token', false);
    }

    public function test_widget_hides_when_disconnected(): void
    {
        Livewire::test(InstagramFeed::class)->assertDontSee('Síguenos en Instagram');
        Http::assertNothingSent();
    }

    public function test_settings_only_receive_public_connection_metadata(): void
    {
        $this->connected();
        $request = Request::create('https://church.test/configuracion/instagram');
        $request->setUserResolver(fn (): User => auth()->user());
        $view = app(InstagramController::class)->index($request);
        $this->assertTrue($view->getData()['configured']);
        $this->assertArrayNotHasKey('instagram_access_token', $view->getData()['configuracion']->toArray());
    }

    public function test_migration_can_be_reversed_without_removing_configuration(): void
    {
        $this->migration()->down();
        $this->assertFalse(Schema::hasColumn('configuraciones', 'instagram_access_token'));
        $this->assertSame(1, Configuracion::query()->count());
    }

    public function test_missing_migration_returns_a_clear_error_without_calling_meta(): void
    {
        $this->migration()->down();
        $this->post('https://church.test/configuracion/instagram/conectar')->assertStatus(409);
        Http::assertNothingSent();
    }

    public function test_instagram_credentials_are_removed_from_telemetry_urls(): void
    {
        $this->assertSame('https://graph.instagram.com/access_token', InstagramService::redactTelemetryUrl(
            'https://graph.instagram.com/access_token?client_secret=secret&access_token=token'
        ));
        $this->assertSame('https://church.test/configuracion/instagram/callback', InstagramService::redactTelemetryUrl(
            'https://church.test/configuracion/instagram/callback?code=secret&state=state'
        ));
        $this->assertSame('https://other.test/?page=2', InstagramService::redactTelemetryUrl('https://other.test/?page=2'));
    }

    public function test_malformed_token_response_does_not_overwrite_existing_connection(): void
    {
        $this->connected();
        Http::fake([
            'https://api.instagram.com/oauth/access_token' => Http::response(['access_token' => 'short']),
            'https://graph.instagram.com/access_token*' => Http::response(['access_token' => 'long', 'expires_in' => 0]),
        ]);
        $this->withSession([$this->stateKey() => $this->pending()])->get($this->callbackUrl())->assertSessionHas('error');
        $this->assertSame('existing-secret', $this->configuration->fresh()->instagram_access_token);
        Http::assertSentCount(2);
    }

    private function migration(): \Illuminate\Database\Migrations\Migration
    {
        return require base_path('database/migrations/tenant/2026_10_30_102536_add_instagram_connection_to_configuraciones_table.php');
    }

    private function userWithPermission(bool $allowed): User
    {
        $role = Mockery::mock(Role::class)->makePartial();
        $role->shouldReceive('hasAnyPermission')->with('configuraciones.subitem_general')->andReturn($allowed);
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('wherePivot')->with('activo', true)->andReturnSelf();
        $relation->shouldReceive('first')->andReturn($role);
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 42, 'email_verified_at' => now()]);
        $user->shouldReceive('roles')->andReturn($relation);

        return $user;
    }

    private function setTenant(string $id): void
    {
        $tenant = new Tenant;
        $tenant->id = $id;
        app(\Stancl\Tenancy\Tenancy::class)->tenant = $tenant;
    }

    private function connected(array $overrides = []): void
    {
        $this->configuration->forceFill(array_replace([
            'instagram_user_id' => '12345',
            'instagram_username' => 'iglesia.test',
            'instagram_access_token' => 'existing-secret',
            'instagram_expires_at' => now()->addDays(50),
            'instagram_refreshed_at' => now()->subDays(2),
        ], $overrides))->save();
    }

    private function stateKey(): string
    {
        return 'instagram_oauth_'.hash('sha256', 'church-a');
    }

    private function pending(): array
    {
        return [
            'hash' => hash('sha256', str_repeat('a', 64)),
            'tenant' => 'church-a',
            'user' => '42',
            'expires' => now()->addMinutes(10)->timestamp,
            'redirect_uri' => 'https://church.test/configuracion/instagram/callback',
        ];
    }

    private function callbackUrl(): string
    {
        return 'https://church.test/configuracion/instagram/callback?state='.str_repeat('a', 64).'&code=code-secret';
    }

    private function fakeConnection(bool $wrapped = true): void
    {
        $short = ['access_token' => 'short-secret', 'user_id' => '12345'];
        Http::fake([
            'https://api.instagram.com/oauth/access_token' => Http::response($wrapped ? ['data' => [$short]] : $short),
            'https://graph.instagram.com/access_token*' => Http::response(['access_token' => 'long-secret', 'expires_in' => 5184000]),
            'https://graph.instagram.com/v25.0/me*' => Http::response(['user_id' => '12345', 'username' => 'iglesia.test']),
        ]);
    }

    private function postData(): array
    {
        return [
            'id' => '1',
            'caption' => 'Una publicación de prueba',
            'media_type' => 'IMAGE',
            'media_url' => 'https://cdninstagram.com/image.jpg',
            'permalink' => 'https://www.instagram.com/p/test/',
        ];
    }
}
