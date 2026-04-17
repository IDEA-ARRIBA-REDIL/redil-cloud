<?php

namespace Tests\Feature;

use App\Models\EntidadRelacionada;
use App\Models\TipoUsuario;
use App\Models\User;
use Tests\TestCase;

class EntityIntegrationTest extends TestCase
{
    /**
     * Test that a TipoUsuario can have a related entity.
     */
    public function test_tipo_usuario_has_related_entity(): void
    {
        // 1. Create a related entity
        $entidad = EntidadRelacionada::create([
            'nombre' => 'LICEO TEST',
            'nit' => '800.123.456-1',
        ]);

        // 2. Create a user type associated with that entity
        $tipoUsuario = TipoUsuario::create([
            'nombre' => 'Maestro Liceo Test',
            'entidad_relacionada_id' => $entidad->id,
            'es_empleado' => true,
        ]);

        // 3. Verify the relationship
        $this->assertEquals('LICEO TEST', $tipoUsuario->entidadRelacionada->nombre);
        $this->assertTrue($tipoUsuario->es_empleado);
        $this->assertFalse($tipoUsuario->es_administrativo);
    }

    /**
     * Test that we can filter users by their entity.
     */
    public function test_can_filter_users_by_entity(): void
    {
        // Setup entities
        $liceo = EntidadRelacionada::create(['nombre' => 'LICEO']);
        $radio = EntidadRelacionada::create(['nombre' => 'RADIO']);

        // Setup user types
        $tipoLiceo = TipoUsuario::create(['nombre' => 'Empleado Liceo', 'entidad_relacionada_id' => $liceo->id]);
        $tipoRadio = TipoUsuario::create(['nombre' => 'Empleado Radio', 'entidad_relacionada_id' => $radio->id]);

        // Setup users
        User::factory()->create(['name' => 'Maria', 'tipo_usuario_id' => $tipoLiceo->id]);
        User::factory()->create(['name' => 'Juan', 'tipo_usuario_id' => $tipoRadio->id]);

        // Filter
        $usersLiceo = User::whereHas('tipoUsuario.entidadRelacionada', function ($query) {
            $query->where('nombre', 'LICEO');
        })->get();

        $this->assertCount(1, $usersLiceo);
        $this->assertEquals('Maria', $usersLiceo->first()->name);
    }
}
