<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TaquillaCardDesignTest extends TestCase
{
    /**
     * Verifica que en la vista de operación de taquilla:
     * 1. Se muestre 'Cupos totales:' en lugar de 'Cupos:'.
     * 2. No exista el error ortográfico 'Ya nnscrito' y esté 'Ya inscrito'.
     * 3. La descripción se interprete mediante {!! $descRaw !!} para evitar mostrar código HTML crudo.
     */
    public function test_vista_operacion_taquilla_card_diseno(): void
    {
        $filePath = dirname(__DIR__, 2).'/resources/views/livewire/taquilla/operacion-taquilla.blade.php';
        $viewContent = file_get_contents($filePath);

        // 1. Cupos totales
        $this->assertStringContainsString('Cupos totales:', $viewContent);
        $this->assertStringNotContainsString('<span class="d-block text-muted">Cupos:</span>', $viewContent);

        // 2. Corrección ortográfica
        $this->assertStringNotContainsString('Ya nnscrito', $viewContent);
        $this->assertStringContainsString('Ya inscrito', $viewContent);

        // 3. Renderizado de descripción HTML interpretada sin código crudo
        $this->assertStringContainsString('{!! $descRaw !!}', $viewContent);
        $this->assertStringContainsString('card-descripcion', $viewContent);
        $this->assertStringNotContainsString('{{ $categoria->materia->descripcion', $viewContent);
    }
}
