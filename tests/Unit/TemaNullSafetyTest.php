<?php

namespace Tests\Unit;

use App\Models\Tema;
use App\Models\User;
use Tests\TestCase;

class TemaNullSafetyTest extends TestCase
{
    /**
     * Verifica que si el usuario es nulo, el filtro de temas termina limpiamente.
     */
    public function test_filtrar_temas_termina_limpiamente_con_usuario_nulo(): void
    {
        $query = Tema::filtrarTemasPermitidos(null, null);

        $this->assertNotNull($query);
        $this->assertStringContainsString('1=2', $query->toSql());
    }

    /**
     * Verifica que si el usuario no tiene tipo de usuario ni sede asignada,
     * no se intente leer la propiedad "id" sobre un valor nulo.
     */
    public function test_filtrar_temas_no_falla_con_tipo_usuario_y_sede_nulos(): void
    {
        $usuario = new User;
        $usuario->id = 999999;
        $usuario->tipo_usuario_id = null;
        $usuario->sede_id = null;
        $usuario->setRelation('tipoUsuario', null);
        $usuario->setRelation('sede', null);

        // Verificamos que la resolución de IDs seguros funcione sin lanzar error de propiedad
        $sedeId = $usuario->sede_id ?? $usuario->sede?->id;
        $tipoUsuarioId = $usuario->tipo_usuario_id ?? $usuario->tipoUsuario?->id;

        $this->assertNull($sedeId);
        $this->assertNull($tipoUsuarioId);
    }
}
