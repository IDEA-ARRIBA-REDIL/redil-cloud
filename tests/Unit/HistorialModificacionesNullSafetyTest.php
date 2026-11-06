<?php

namespace Tests\Unit;

use App\Models\HistorialModificacionPago;
use PHPUnit\Framework\TestCase;

class HistorialModificacionesNullSafetyTest extends TestCase
{
    /**
     * Verifica que la vista de historial de modificaciones utilice operadores null-safe (?->)
     * para evitar excepciones de tipo "Call to a member function nombre() on null".
     */
    public function test_vista_historial_modificaciones_tiene_llamadas_nullsafe(): void
    {
        $filePath = dirname(__DIR__, 2).'/resources/views/livewire/taquilla/historial-modificaciones.blade.php';
        $viewContent = file_get_contents($filePath);

        // No debe existir llamada directa sin nullsafe
        $this->assertStringNotContainsString('$modificacion->usuarioAfectado->nombre(3)', $viewContent);
        $this->assertStringNotContainsString('$modificacion->caja->usuario->nombre(3)', $viewContent);

        // Debe contener llamadas seguras con nullsafe
        $this->assertStringContainsString('$modificacion->usuarioAfectado?->nombre(3)', $viewContent);
        $this->assertStringContainsString('Usuario Desconocido', $viewContent);
        $this->assertStringContainsString('$modificacion->asesor?->nombre(3)', $viewContent);
    }

    /**
     * Verifica que el archivo de exportación Excel de historial de modificaciones
     * maneje usuarios nulos o eliminados de forma segura con ?->.
     */
    public function test_export_historial_modificaciones_es_nullsafe(): void
    {
        $filePath = dirname(__DIR__, 2).'/app/Exports/HistorialModificacionesExport.php';
        $content = file_get_contents($filePath);

        $this->assertStringNotContainsString('$modificacion->usuarioAfectado->nombre(3)', $content);
        $this->assertStringNotContainsString('$modificacion->asesor->nombre(3)', $content);
        $this->assertStringContainsString('$modificacion->usuarioAfectado?->nombre(3)', $content);
        $this->assertStringContainsString('$modificacion->asesor?->nombre(3)', $content);
    }

    /**
     * Verifica que el modelo HistorialModificacionPago incluya withTrashed() en sus relaciones
     * para no perder información histórica ante borrado lógico (soft deletes).
     */
    public function test_modelo_historial_modificacion_pago_incluye_with_trashed(): void
    {
        $filePath = dirname(__DIR__, 2).'/app/Models/HistorialModificacionPago.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString("belongsTo(User::class, 'usuario_afectado_id')->withTrashed()", $content);
        $this->assertStringContainsString("belongsTo(User::class, 'asesor_id')->withTrashed()", $content);
        $this->assertStringContainsString("belongsTo(Compra::class, 'compra_id')->withTrashed()", $content);
        $this->assertStringContainsString("belongsTo(Pago::class, 'pago_id')->withTrashed()", $content);
        $this->assertStringContainsString("belongsTo(Caja::class, 'caja_id')->withTrashed()", $content);
        $this->assertStringContainsString("belongsTo(PuntoDePago::class, 'punto_de_pago_id')->withTrashed()", $content);
    }

    /**
     * Verifica que la vista de historial de transacciones también use operadores null-safe
     * para actividades y categorías.
     */
    public function test_vista_historial_transacciones_es_nullsafe(): void
    {
        $filePath = dirname(__DIR__, 2).'/resources/views/livewire/taquilla/historial-transacciones.blade.php';
        $viewContent = file_get_contents($filePath);

        $this->assertStringContainsString('$compra->actividad?->tipo?->permite_abonos', $viewContent);
        $this->assertStringContainsString('$inscripcion->categoriaActividad?->nombre', $viewContent);
    }
}
