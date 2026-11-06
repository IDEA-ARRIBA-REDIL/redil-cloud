<?php

namespace Tests\Feature;

use App\Http\Controllers\GestionarTipoDeGruposController;
use App\Http\Controllers\UserController;
use App\Models\Role;
use App\Models\TipoGrupo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class IntegracionCpanelGruposUsuariosTest extends TestCase
{
    public function test_saves_only_denied_actions_and_keeps_other_actions_allowed(): void
    {
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('sync')->once()->with([
            8 => [
                'asignar_encargado' => false,
                'desvincular_encargado' => true,
                'asignar_asistente' => true,
                'desvincular_asistente' => false,
            ],
        ]);
        $this->synchronize([
            'gestionar_privilegios_roles' => '1',
            'restricciones_roles' => [
                ['rol_id' => 8, 'denegar_asignar_encargado' => '1', 'denegar_desvincular_asistente' => '1'],
                ['rol_id' => 9],
            ],
        ], $relation);
    }

    public function test_old_forms_do_not_erase_existing_role_restrictions(): void
    {
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldNotReceive('sync');
        $this->synchronize([], $relation);
    }

    public function test_explicitly_removing_all_rows_clears_restrictions(): void
    {
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('sync')->once()->with([]);
        $this->synchronize(['gestionar_privilegios_roles' => '1'], $relation);
    }

    public function test_switching_roles_redirects_to_dashboard_instead_of_previous_restricted_page(): void
    {
        Route::get('/integration-dashboard', fn () => 'dashboard')->name('dashboard');
        $role = new Role(['name' => 'Líder']);
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('switchActiveRole')->once()->with($role);
        $request = Request::create('/switch-role', 'POST');
        $request->setUserResolver(fn () => $user);
        $response = app(UserController::class)->switchRole($request, $role);
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
        $this->assertSame('Has cambiado al rol: Líder', session('success'));
    }

    public function test_consolidation_denies_access_without_an_active_role(): void
    {
        $this->mockConsolidationRole(null);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->consolidationScope();
    }

    public function test_consolidation_denies_access_without_a_data_scope_permission(): void
    {
        $role = Mockery::mock(Role::class)->makePartial();
        $role->shouldReceive('verificacionDelPermiso')->with('consolidacion.dashboard_consolidacion');
        $role->shouldReceive('hasPermissionTo')->andReturn(false);
        $this->mockConsolidationRole($role);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->consolidationScope();
    }

    public function test_consolidation_global_permission_takes_precedence_over_ministry_scope(): void
    {
        $role = Mockery::mock(Role::class)->makePartial();
        $role->shouldReceive('verificacionDelPermiso')->with('consolidacion.dashboard_consolidacion');
        $role->shouldReceive('hasPermissionTo')->andReturn(true);
        $this->mockConsolidationRole($role);
        $scope = $this->consolidationScope();
        $this->assertTrue($scope['esTodaConsolidacion']);
        $this->assertFalse($scope['esSoloMinisterio']);
    }

    private function mockConsolidationRole(?Role $role): void
    {
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('wherePivot')->with('activo', true)->andReturnSelf();
        $relation->shouldReceive('first')->andReturn($role);
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('roles')->andReturn($relation);
        $user->shouldNotReceive('consolidacion');
        $this->actingAs($user);
    }

    private function consolidationScope(): array
    {
        $method = new \ReflectionMethod(\App\Http\Controllers\ConsolidacionController::class, 'getAlcanceConsolidacion');

        return $method->invoke(app(\App\Http\Controllers\ConsolidacionController::class));
    }

    private function synchronize(array $input, BelongsToMany $relation): void
    {
        $tipo = Mockery::mock(TipoGrupo::class)->makePartial();
        $tipo->shouldReceive('privilegiosRoles')->andReturn($relation);
        $controller = new class extends GestionarTipoDeGruposController
        {
            public function synchronizeForTest(Request $request, TipoGrupo $tipo): void
            {
                $this->sincronizarPrivilegiosRoles($request, $tipo);
            }
        };
        $controller->synchronizeForTest(Request::create('/', 'POST', $input), $tipo);
    }
}
