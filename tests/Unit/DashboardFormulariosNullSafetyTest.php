<?php

namespace Tests\Unit;

use App\Livewire\Actividades\DashboardFormularios;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Compra;
use App\Models\Inscripcion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class DashboardFormulariosNullSafetyTest extends TestCase
{
    /**
     * Prueba que el blade de dashboard-formularios renderiza sin errores cuando una compra
     * no tiene inscripción asociada ($inscripcion == null).
     */
    public function test_dashboard_formularios_renderiza_cuando_compra_no_tiene_inscripcion(): void
    {
        $actividad = new Actividad([
            'id' => 67,
            'nombre' => 'ESCUELAS CHL',
            'tiene_invitados' => false,
        ]);

        $compra = new Compra([
            'id' => 244,
            'actividad_id' => 67,
            'nombre_completo_comprador' => 'Andres Felipe Alayon',
            'telefono_comprador' => '3177648742',
            'estado' => 1,
        ]);
        $compra->setRelation('carritos', new Collection);

        $component = new DashboardFormularios;
        $component->actividad = $actividad;
        $component->setId('test-dashboard-123');

        $html = View::make('livewire.actividades.dashboard-formularios', [
            '_instance' => $component,
            'actividad' => $actividad,
            'compradores' => new Collection([$compra]),
            'elementosFormulario' => new Collection,
            'mapaRespuestas' => [],
            'mapaInscripciones' => [], // Sin inscripción
            'mapaInvitados' => collect(),
            'todasLasRespuestas' => new Collection,
        ])->render();

        $this->assertStringContainsString('Andres Felipe Alayon', $html);
        $this->assertStringContainsString('Pendiente de pago', $html);
    }

    /**
     * Prueba que el blade de dashboard-formularios renderiza sin errores cuando la categoría
     * tiene aforo nulo (ilimitado) y aforo ocupado negativo o nulo.
     */
    public function test_dashboard_formularios_renderiza_con_categoria_aforo_nulo(): void
    {
        $actividad = new Actividad([
            'id' => 67,
            'nombre' => 'ESCUELAS CHL',
            'tiene_invitados' => true,
        ]);

        $categoria = new ActividadCategoria([
            'id' => 67,
            'nombre' => 'Camino Hacia la Libertad',
            'aforo' => null,
            'aforo_ocupado' => -8,
            'limite_invitados' => null,
        ]);

        $inscripcion = new Inscripcion([
            'id' => 4997,
            'compra_id' => 247,
            'estado' => 3,
            'limite_invitados' => 0,
            'actividad_categoria_id' => 67,
        ]);
        $inscripcion->setRelation('categoriaActividad', $categoria);

        $compra = new Compra([
            'id' => 247,
            'actividad_id' => 67,
            'nombre_completo_comprador' => 'Octavio Loaiza',
            'telefono_comprador' => '3001234567',
            'estado' => 3,
        ]);
        $compra->setRelation('carritos', new Collection);

        $component = new DashboardFormularios;
        $component->actividad = $actividad;
        $component->setId('test-dashboard-456');

        $html = View::make('livewire.actividades.dashboard-formularios', [
            '_instance' => $component,
            'actividad' => $actividad,
            'compradores' => new Collection([$compra]),
            'elementosFormulario' => new Collection,
            'mapaRespuestas' => [],
            'mapaInscripciones' => [247 => $inscripcion],
            'mapaInvitados' => collect(),
            'todasLasRespuestas' => new Collection,
        ])->render();

        $this->assertStringContainsString('Octavio Loaiza', $html);
        $this->assertStringContainsString('Camino Hacia la Libertad', $html);
        $this->assertStringContainsString('Sin límite', $html);
    }
}
