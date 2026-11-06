<?php

namespace Tests\Feature;

use App\Models\EntidadRelacionada;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EntityIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'entity_testing', 'database.connections.entity_testing' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge('entity_testing');
        (require database_path('migrations/tenant/2026_03_25_145000_create_entidades_relacionadas_table.php'))->up();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            foreach (['primer_nombre', 'primer_apellido', 'email', 'password', 'foto', 'remember_token', 'telefono_movil'] as $campo) {
                $table->string($campo)->nullable();
            }
            $table->boolean('activo')->default(true);
            $table->integer('tipo_usuario_id')->nullable();
            $table->integer('sede_id')->nullable();
            $table->integer('genero')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        (require database_path('migrations/tenant/2026_03_31_160000_add_entidad_relacionada_id_to_users_table.php'))->up();
    }

    public function test_user_has_related_entity(): void
    {
        $entidad = EntidadRelacionada::create(['nombre' => 'LICEO TEST', 'nit' => '800.123.456-1']);
        $usuario = User::withoutEvents(fn () => User::factory()->create(['entidad_relacionada_id' => $entidad->id]));
        $this->assertSame('LICEO TEST', $usuario->entidadRelacionada->nombre);
        $this->assertSame('800.123.456-1', $usuario->entidadRelacionada->nit);
    }

    public function test_can_filter_users_by_entity(): void
    {
        $liceo = EntidadRelacionada::create(['nombre' => 'LICEO']);
        $radio = EntidadRelacionada::create(['nombre' => 'RADIO']);
        User::withoutEvents(function () use ($liceo, $radio): void {
            User::factory()->create(['primer_nombre' => 'Maria', 'entidad_relacionada_id' => $liceo->id]);
            User::factory()->create(['primer_nombre' => 'Juan', 'entidad_relacionada_id' => $radio->id]);
        });
        $usersLiceo = User::whereHas('entidadRelacionada', fn ($query) => $query->where('nombre', 'LICEO'))->get();
        $this->assertCount(1, $usersLiceo);
        $this->assertSame('Maria', $usersLiceo->first()->primer_nombre);
    }
}
