<?php

namespace Tests\Unit;

use App\Services\ValidadorArchivoRecursoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class ValidadorArchivoRecursoServiceTest extends TestCase
{
    private ValidadorArchivoRecursoService $validador;

    /** @var array<int, string> */
    private array $archivosTemporales = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->validador = new ValidadorArchivoRecursoService;
    }

    protected function tearDown(): void
    {
        foreach ($this->archivosTemporales as $ruta) {
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
        parent::tearDown();
    }

    public function test_acepta_archivo_txt_valido(): void
    {
        $archivo = $this->crearUploadedFile('guia-estudio.txt', "Guía de estudio para el corte 1.\nRepasar capítulos 1 y 2.", 'text/plain');

        $resultado = $this->validador->validar($archivo);

        $this->assertSame('txt', $resultado['extension']);
        $this->assertSame('guia-estudio.txt', $resultado['nombre_original_seguro']);
    }

    public function test_acepta_archivo_pdf_valido(): void
    {
        $contenidoPdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";
        $archivo = $this->crearUploadedFile('lectura-semanal.pdf', $contenidoPdf, 'application/pdf');

        $resultado = $this->validador->validar($archivo);

        $this->assertSame('pdf', $resultado['extension']);
        $this->assertSame('lectura-semanal.pdf', $resultado['nombre_original_seguro']);
    }

    public function test_acepta_archivo_docx_valido(): void
    {
        $archivo = $this->crearDocxUploadedFile('taller-clase.docx', [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>',
            'word/document.xml' => '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Taller 1</w:t></w:r></w:p></w:body></w:document>',
        ]);

        $resultado = $this->validador->validar($archivo);

        $this->assertSame('docx', $resultado['extension']);
        $this->assertSame('taller-clase.docx', $resultado['nombre_original_seguro']);
    }

    public function test_rechaza_archivo_que_supera_2mb(): void
    {
        $contenidoGrande = str_repeat('A', ValidadorArchivoRecursoService::TAMANO_MAXIMO_BYTES + 10);
        $archivo = $this->crearUploadedFile('grande.txt', $contenidoGrande, 'text/plain');

        $this->expectException(ValidationException::class);
        $this->validador->validar($archivo);
    }

    public function test_rechaza_extensiones_no_permitidas_y_dobles_extensiones(): void
    {
        $casos = [
            ['shell.php', '<?php echo 1;', 'text/plain'],
            ['imagen.png', 'contenido falso', 'image/png'],
            ['shell.php.txt', 'Texto inocente', 'text/plain'],
            ['exploit.phtml.pdf', "%PDF-1.4\n%%EOF\n", 'application/pdf'],
        ];

        foreach ($casos as [$nombre, $contenido, $mime]) {
            $archivo = $this->crearUploadedFile($nombre, $contenido, $mime);

            try {
                $this->validador->validar($archivo);
                $this->fail("Se esperaba rechazo para {$nombre}");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('archivo', $e->errors());
            }
        }
    }

    public function test_rechaza_codigo_malicioso_en_txt(): void
    {
        $payloads = [
            '<?php system($_GET["cmd"]); ?>',
            '<script>alert(document.cookie)</script>',
            "#!/bin/bash\nrm -rf /",
            'eval(base64_decode("abc"))',
        ];

        foreach ($payloads as $indice => $payload) {
            $archivo = $this->crearUploadedFile("nota-{$indice}.txt", $payload, 'text/plain');

            try {
                $this->validador->validar($archivo);
                $this->fail("Se esperaba rechazo para payload #{$indice}");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('archivo', $e->errors());
            }
        }
    }

    public function test_rechaza_pdf_falso_o_con_javascript_embebido(): void
    {
        $pdfFalso = $this->crearUploadedFile('falso.pdf', 'Esto no es un PDF real', 'application/pdf');
        try {
            $this->validador->validar($pdfFalso);
            $this->fail('Se esperaba rechazo para PDF falso.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('archivo', $e->errors());
        }

        $pdfConJs = $this->crearUploadedFile('malicioso.pdf', "%PDF-1.4\n1 0 obj << /JavaScript (app.alert('xss')) >> endobj\n%%EOF\n", 'application/pdf');
        try {
            $this->validador->validar($pdfConJs);
            $this->fail('Se esperaba rechazo para PDF con JavaScript.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('archivo', $e->errors());
        }
    }

    public function test_rechaza_docx_con_macros_php_o_xxe(): void
    {
        $docxConMacro = $this->crearDocxUploadedFile('macro.docx', [
            '[Content_Types].xml' => '<Types/>',
            'word/document.xml' => '<doc/>',
            'word/vbaProject.bin' => 'macro-binaria',
        ]);
        try {
            $this->validador->validar($docxConMacro);
            $this->fail('Se esperaba rechazo para DOCX con macro.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('archivo', $e->errors());
        }

        $docxConXxe = $this->crearDocxUploadedFile('xxe.docx', [
            '[Content_Types].xml' => '<Types/>',
            'word/document.xml' => '<!DOCTYPE foo [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><doc>&xxe;</doc>',
        ]);
        try {
            $this->validador->validar($docxConXxe);
            $this->fail('Se esperaba rechazo para DOCX con XXE.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('archivo', $e->errors());
        }
    }

    public function test_vistas_recursos_compilan_y_corrigen_aviso_subiendo_y_formato(): void
    {
        $blade = $this->app->make('blade.compiler');
        $vistaPadre = (string) file_get_contents(resource_path('views/contenido/paginas/escuelas/maestros/recursos-alumno.blade.php'));
        $vistaLivewire = (string) file_get_contents(resource_path('views/livewire/maestros/gestion-recursos.blade.php'));

        $this->assertNotEmpty($blade->compileString($vistaPadre));
        $this->assertNotEmpty($blade->compileString($vistaLivewire));

        $this->assertStringNotContainsString('mdi mdi-', $vistaLivewire);
        $this->assertStringContainsString('accept=".pdf,.docx,.txt', $vistaLivewire);
        $this->assertStringNotContainsString('x-show="subiendo" class="small text-muted mt-2 d-flex', $vistaLivewire);
        $this->assertStringContainsString('x-show="subiendo" x-cloak', $vistaLivewire);
        $this->assertStringContainsString('Video en YouTube', $vistaLivewire);
        $this->assertStringContainsString('Enlace externo', $vistaLivewire);
        $this->assertStringContainsString('Archivo adjunto', $vistaLivewire);
    }

    private function crearUploadedFile(string $nombre, string $contenido, string $mime): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'recurso_test_');
        file_put_contents($ruta, $contenido);
        $this->archivosTemporales[] = $ruta;

        return new UploadedFile($ruta, $nombre, $mime, null, true);
    }

    /**
     * @param  array<string, string>  $entradas
     */
    private function crearDocxUploadedFile(string $nombre, array $entradas): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'recurso_docx_').'.docx';
        $zip = new ZipArchive;
        $zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($entradas as $clave => $valor) {
            $zip->addFromString($clave, $valor);
        }
        $zip->close();
        $this->archivosTemporales[] = $ruta;

        return new UploadedFile(
            $ruta,
            $nombre,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );
    }
}
