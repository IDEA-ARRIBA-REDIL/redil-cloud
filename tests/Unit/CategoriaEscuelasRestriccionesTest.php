<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CategoriaEscuelasRestriccionesTest extends TestCase
{
    /**
     * Verifica que la vista de edición de categoría de escuela contenga los campos de restricciones
     * cuando la actividad tiene restriccion_por_categoria = true.
     */
    public function test_vista_editar_categoria_escuelas_incluye_campos_de_restricciones(): void
    {
        $filePath = dirname(__DIR__, 2).'/resources/views/contenido/paginas/actividades/editar-categoria-escuelas.blade.php';
        $viewContent = file_get_contents($filePath);

        $this->assertStringContainsString('restriccion_por_categoria', $viewContent);
        $this->assertStringContainsString('name="genero"', $viewContent);
        $this->assertStringContainsString('name="vinculacion_grupo"', $viewContent);
        $this->assertStringContainsString('name="actividad_grupo"', $viewContent);
        $this->assertStringContainsString('name="sedes[]"', $viewContent);
        $this->assertStringContainsString('name="rangos_edad[]"', $viewContent);
        $this->assertStringContainsString('name="tipo_usuarios[]"', $viewContent);
        $this->assertStringContainsString('name="estados_civiles[]"', $viewContent);
        $this->assertStringContainsString('name="tipo_servicios[]"', $viewContent);
        $this->assertStringContainsString('name="aforo"', $viewContent);

        // En actividades de tipo Escuela, los pasos de crecimiento y tareas de consolidación
        // se gestionan directamente a nivel de Materia (gestionar-materia.blade.php), por lo que
        // NO deben estar en la vista de edición de categoría de escuela.
        $this->assertStringNotContainsString('actividad-categoria.gestionar-pasos-requisito', $viewContent);
        $this->assertStringNotContainsString('actividad-categoria.gestionar-tareas-requisito', $viewContent);
    }

    /**
     * Verifica que la vista de creación de categoría de escuela contenga los campos de restricciones
     * cuando la actividad tiene restriccion_por_categoria = true.
     */
    public function test_vista_crear_categoria_escuelas_incluye_campos_de_restricciones(): void
    {
        $filePath = dirname(__DIR__, 2).'/resources/views/contenido/paginas/actividades/crear-categoria-escuelas.blade.php';
        $viewContent = file_get_contents($filePath);

        $this->assertStringContainsString('restriccion_por_categoria', $viewContent);
        $this->assertStringContainsString('name="genero"', $viewContent);
        $this->assertStringContainsString('name="vinculacion_grupo"', $viewContent);
        $this->assertStringContainsString('name="actividad_grupo"', $viewContent);
        $this->assertStringContainsString('name="sedes[]"', $viewContent);
        $this->assertStringContainsString('name="rangos_edad[]"', $viewContent);
        $this->assertStringContainsString('name="tipo_usuarios[]"', $viewContent);
        $this->assertStringContainsString('name="estados_civiles[]"', $viewContent);
        $this->assertStringContainsString('name="tipo_servicios[]"', $viewContent);
        $this->assertStringContainsString('name="aforo"', $viewContent);
    }
}
