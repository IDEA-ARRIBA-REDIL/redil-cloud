<?php

namespace Tests\Unit;

use App\Models\Actividad;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class ActividadVisibilidadDashboardTest extends TestCase
{
    /**
     * Actividad totalmente pública sin restricciones por categoría debe estar disponible.
     */
    public function test_actividad_totalmente_publica_esta_disponible(): void
    {
        $usuario = new User([
            'id' => 999,
            'genero' => 0, // Masculino
        ]);
        $usuario->setRelation('rolActivo', null);
        $usuario->setRelation('pasosCrecimiento', new Collection);

        $actividad = new Actividad([
            'id' => 101,
            'nombre' => 'Evento Abierto',
            'totalmente_publica' => true,
            'restriccion_por_categoria' => false,
            'mostrar_en_proximas_actividades' => true,
        ]);
        $actividad->setRelation('tipoUsuarios', new Collection);
        $actividad->setRelation('rangosEdad', new Collection);
        $actividad->setRelation('sedes', new Collection);
        $actividad->setRelation('procesosRequisito', new Collection);
        $actividad->setRelation('tareasRequisito', new Collection);
        $actividad->setRelation('estadosCiviles', new Collection);
        $actividad->setRelation('tipoServicios', new Collection);
        $actividad->setRelation('categorias', new Collection);

        $resultado = $actividad->validarUsuarioEnCategoria($usuario, null);

        $this->assertSame('DISPONIBLE', $resultado->estado);
        $this->assertEmpty($resultado->motivos);

        $accesoGlobal = $actividad->validarAccesoGlobal($usuario);
        $this->assertTrue($accesoGlobal['success']);
    }

    /**
     * Actividad con totalmente_publica = false y SIN restricciones definidas
     * debe quedar BLOQUEADA para un usuario general (no disponible para el público general).
     */
    public function test_actividad_no_totalmente_publica_sin_restricciones_queda_bloqueada(): void
    {
        $usuario = new User([
            'id' => 999,
            'genero' => 0,
        ]);
        $usuario->setRelation('rolActivo', null);
        $usuario->setRelation('pasosCrecimiento', new Collection);

        $actividad = new Actividad([
            'id' => 68,
            'nombre' => 'Ayuno 2027 / Un Nuevo Comienzo',
            'totalmente_publica' => false,
            'restriccion_por_categoria' => false,
            'mostrar_en_proximas_actividades' => false,
            'genero' => null,
        ]);
        $actividad->setRelation('tipoUsuarios', new Collection);
        $actividad->setRelation('rangosEdad', new Collection);
        $actividad->setRelation('sedes', new Collection);
        $actividad->setRelation('procesosRequisito', new Collection);
        $actividad->setRelation('tareasRequisito', new Collection);
        $actividad->setRelation('estadosCiviles', new Collection);
        $actividad->setRelation('tipoServicios', new Collection);
        $actividad->setRelation('categorias', new Collection);

        $resultado = $actividad->validarUsuarioEnCategoria($usuario, null);

        $this->assertSame('BLOQUEADA', $resultado->estado);
        $this->assertContains('Actividad no disponible para el público general.', $resultado->motivos);

        $accesoGlobal = $actividad->validarAccesoGlobal($usuario);
        $this->assertFalse($accesoGlobal['success']);

        $permitidas = Actividad::filtrarActividadesPermitidas($usuario, collect([$actividad]));
        $this->assertNotContains(68, $permitidas);
    }

    /**
     * Actividad con totalmente_publica = false pero con restricciones definidas
     * debe estar DISPONIBLE si el usuario cumple la restricción.
     */
    public function test_actividad_con_restriccion_genero_valida_correctamente(): void
    {
        $usuarioHombre = new User([
            'id' => 998,
            'genero' => 0, // Masculino en REDIL
        ]);
        $usuarioHombre->setRelation('rolActivo', null);
        $usuarioHombre->setRelation('pasosCrecimiento', new Collection);

        $usuarioMujer = new User([
            'id' => 997,
            'genero' => 1, // Femenino en REDIL
        ]);
        $usuarioMujer->setRelation('rolActivo', null);
        $usuarioMujer->setRelation('pasosCrecimiento', new Collection);

        // Actividad solo para hombres (genero = 1)
        $actividadHombres = new Actividad([
            'id' => 102,
            'nombre' => 'Congreso Hombres',
            'totalmente_publica' => false,
            'restriccion_por_categoria' => false,
            'mostrar_en_proximas_actividades' => true,
            'genero' => 1,
        ]);
        $actividadHombres->setRelation('tipoUsuarios', new Collection);
        $actividadHombres->setRelation('rangosEdad', new Collection);
        $actividadHombres->setRelation('sedes', new Collection);
        $actividadHombres->setRelation('procesosRequisito', new Collection);
        $actividadHombres->setRelation('tareasRequisito', new Collection);
        $actividadHombres->setRelation('estadosCiviles', new Collection);
        $actividadHombres->setRelation('tipoServicios', new Collection);
        $actividadHombres->setRelation('categorias', new Collection);

        // Usuario hombre debe ser DISPONIBLE
        $resHombre = $actividadHombres->validarUsuarioEnCategoria($usuarioHombre, null);
        $this->assertSame('DISPONIBLE', $resHombre->estado);

        // Usuario mujer debe ser BLOQUEADA
        $resMujer = $actividadHombres->validarUsuarioEnCategoria($usuarioMujer, null);
        $this->assertSame('BLOQUEADA', $resMujer->estado);
        $this->assertContains('No cumple con el requisito de género.', $resMujer->motivos);
    }
}
