<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ValidadorArchivoRecursoService
{
    public const TAMANO_MAXIMO_BYTES = 2097152; // 2 MB

    private const MAX_TAMANO_DESCOMPRIMIDO_DOCX = 15728640; // 15 MB (protección zip bomb)

    /**
     * @var array<int, string>
     */
    private const EXTENSIONES_PERMITIDAS = ['pdf', 'docx', 'txt'];

    /**
     * @var array<int, string>
     */
    private const EXTENSIONES_PELIGROSAS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'exe', 'bat', 'cmd', 'com', 'scr', 'msi', 'dll', 'so', 'hta',
        'sh', 'bash', 'zsh', 'ps1', 'vbs', 'vbe', 'js', 'mjs', 'cjs',
        'py', 'rb', 'pl', 'cgi', 'asp', 'aspx', 'jsp', 'jspx',
        'html', 'htm', 'xhtml', 'svg', 'jar', 'bin',
    ];

    /**
     * Valida extensión, tamaño, tipo MIME real, firma binaria y ausencia de código ejecutable malicioso.
     *
     * @return array{extension: string, nombre_original_seguro: string}
     *
     * @throws ValidationException
     */
    public function validar(UploadedFile $archivo): array
    {
        if (! $archivo->isValid()) {
            $this->fallar('El archivo no se subió correctamente. Intenta nuevamente.');
        }

        $tamano = (int) $archivo->getSize();
        if ($tamano <= 0) {
            $this->fallar('El archivo está vacío. Selecciona un documento válido.');
        }

        if ($tamano > self::TAMANO_MAXIMO_BYTES) {
            $this->fallar('El archivo supera el límite máximo permitido de 2 MB.');
        }

        $nombreOriginal = (string) $archivo->getClientOriginalName();
        $extension = $this->validarNombreYExtension($nombreOriginal);

        $rutaReal = $archivo->getRealPath();
        if (! $rutaReal || ! is_readable($rutaReal)) {
            $this->fallar('No se pudo inspeccionar el archivo subido.');
        }

        $mimeDetectado = $this->detectarMimeReal($rutaReal);

        match ($extension) {
            'pdf' => $this->validarPdf($rutaReal, $mimeDetectado),
            'docx' => $this->validarDocx($rutaReal, $mimeDetectado),
            'txt' => $this->validarTxt($rutaReal, $mimeDetectado),
        };

        return [
            'extension' => $extension,
            'nombre_original_seguro' => $this->sanitizarNombreVisible($nombreOriginal, $extension),
        ];
    }

    /**
     * Verifica que el nombre no contenga path traversal ni dobles extensiones peligrosas.
     *
     * @throws ValidationException
     */
    private function validarNombreYExtension(string $nombreOriginal): string
    {
        if (str_contains($nombreOriginal, "\0") || str_contains($nombreOriginal, '..') || preg_match('/[\/\\\\]/', $nombreOriginal) === 1) {
            $this->fallar('El nombre del archivo contiene caracteres no permitidos.');
        }

        $partes = array_values(array_filter(explode('.', strtolower(trim($nombreOriginal))), fn (string $parte): bool => $parte !== ''));
        if (count($partes) < 2) {
            $this->fallar('El archivo debe tener una extensión válida (.pdf, .docx o .txt).');
        }

        $extensionFinal = end($partes);
        if (! in_array($extensionFinal, self::EXTENSIONES_PERMITIDAS, true)) {
            $this->fallar('Solo se permiten archivos en formato PDF (.pdf), Word (.docx) o texto (.txt).');
        }

        $segmentosIntermedios = array_slice($partes, 1, -1);
        foreach ($segmentosIntermedios as $segmento) {
            if (in_array($segmento, self::EXTENSIONES_PELIGROSAS, true)) {
                $this->fallar('El archivo fue rechazado por contener una doble extensión potencialmente peligrosa.');
            }
        }

        return $extensionFinal;
    }

    private function detectarMimeReal(string $rutaReal): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($rutaReal);

        return is_string($mime) ? strtolower($mime) : '';
    }

    /**
     * Valida firma %PDF-, MIME real y ausencia de scripts ejecutables embebidos.
     *
     * @throws ValidationException
     */
    private function validarPdf(string $rutaReal, string $mimeDetectado): void
    {
        if (! in_array($mimeDetectado, ['application/pdf', 'application/x-pdf'], true)) {
            $this->fallar('El contenido del archivo no corresponde a un documento PDF válido.');
        }

        $contenido = (string) file_get_contents($rutaReal);
        if (! str_starts_with($contenido, '%PDF-')) {
            $this->fallar('La firma del archivo PDF no es válida.');
        }

        if (preg_match('/<\?php|<\?=|<script\b/i', $contenido) === 1) {
            $this->fallar('El archivo PDF contiene código potencialmente malicioso y fue bloqueado.');
        }

        if (preg_match('/\/(JavaScript|JS|Launch|EmbeddedFile|RichMedia)\b/i', $contenido) === 1) {
            $this->fallar('El archivo PDF contiene scripts o acciones ejecutables no permitidas.');
        }
    }

    /**
     * Valida estructura ZIP real de DOCX, bloquea macros VBA, archivos ejecutables, zip-slip y XXE.
     *
     * @throws ValidationException
     */
    private function validarDocx(string $rutaReal, string $mimeDetectado): void
    {
        $mimesValidos = [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
        ];

        if (! in_array($mimeDetectado, $mimesValidos, true)) {
            $this->fallar('El contenido del archivo no corresponde a un documento DOCX válido.');
        }

        $cabecera = (string) file_get_contents($rutaReal, false, null, 0, 4);
        if ($cabecera !== "PK\x03\x04") {
            $this->fallar('La firma del archivo DOCX no es válida.');
        }

        if (! class_exists(ZipArchive::class)) {
            $this->fallar('El servidor no tiene habilitada la inspección segura de archivos DOCX.');
        }

        $zip = new ZipArchive;
        if ($zip->open($rutaReal) !== true) {
            $this->fallar('El archivo DOCX está dañado o no es un paquete válido.');
        }

        try {
            $tieneContentTypes = false;
            $tieneDocumentXml = false;
            $tamanoDescomprimidoTotal = 0;

            for ($indice = 0; $indice < $zip->numFiles; $indice++) {
                $stat = $zip->statIndex($indice);
                if (! is_array($stat)) {
                    continue;
                }

                $nombreEntrada = (string) ($stat['name'] ?? '');
                $tamanoEntrada = (int) ($stat['size'] ?? 0);
                $tamanoDescomprimidoTotal += $tamanoEntrada;

                if ($tamanoDescomprimidoTotal > self::MAX_TAMANO_DESCOMPRIMIDO_DOCX) {
                    $this->fallar('El archivo DOCX excede el límite seguro de contenido interno.');
                }

                if (str_contains($nombreEntrada, '..') || str_starts_with($nombreEntrada, '/') || str_starts_with($nombreEntrada, '\\')) {
                    $this->fallar('El archivo DOCX contiene rutas internas no seguras.');
                }

                $nombreMinuscula = strtolower($nombreEntrada);
                if ($nombreMinuscula === '[content_types].xml') {
                    $tieneContentTypes = true;
                }
                if ($nombreMinuscula === 'word/document.xml') {
                    $tieneDocumentXml = true;
                }

                if (str_contains($nombreMinuscula, 'vbaproject.bin')) {
                    $this->fallar('No se permiten documentos con macros habilitadas.');
                }

                $extensionEntrada = strtolower((string) pathinfo($nombreEntrada, PATHINFO_EXTENSION));
                if ($extensionEntrada !== '' && in_array($extensionEntrada, self::EXTENSIONES_PELIGROSAS, true)) {
                    $this->fallar('El archivo DOCX contiene archivos ejecutables incrustados y fue bloqueado.');
                }

                if (in_array($extensionEntrada, ['xml', 'rels'], true)) {
                    $contenidoXml = (string) $zip->getFromIndex($indice);
                    if (preg_match('/<\?php|<\?=|<script\b|<!ENTITY\b/i', $contenidoXml) === 1) {
                        $this->fallar('El archivo DOCX contiene instrucciones maliciosas internas y fue bloqueado.');
                    }
                }
            }

            if (! $tieneContentTypes || ! $tieneDocumentXml) {
                $this->fallar('El archivo no tiene una estructura válida de documento Word (.docx).');
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Valida texto plano puro sin bytes binarios, shebangs ni código ejecutable.
     *
     * @throws ValidationException
     */
    private function validarTxt(string $rutaReal, string $mimeDetectado): void
    {
        if (! str_starts_with($mimeDetectado, 'text/plain')) {
            $this->fallar('El archivo .txt debe contener únicamente texto plano.');
        }

        $contenido = (string) file_get_contents($rutaReal);

        if (str_contains($contenido, "\0") || preg_match('/[\x01-\x08\x0B\x0C\x0E-\x1F]/', $contenido) === 1) {
            $this->fallar('El archivo .txt contiene contenido binario no permitido.');
        }

        if (str_starts_with(ltrim($contenido), '#!/')) {
            $this->fallar('No se permiten scripts ejecutables en archivos .txt.');
        }

        $patronesMaliciosos = [
            '/<\?php/i',
            '/<\?=/i',
            '/<%/i',
            '/<script\b/i',
            '/<\/script>/i',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/\bon(load|error|click|mouseover|focus)\s*=/i',
            '/\b(eval|exec|shell_exec|system|passthru|proc_open|popen|pcntl_exec|assert)\s*\(/i',
        ];

        foreach ($patronesMaliciosos as $patron) {
            if (preg_match($patron, $contenido) === 1) {
                $this->fallar('El archivo .txt contiene código potencialmente ejecutable y fue bloqueado por seguridad.');
            }
        }
    }

    private function sanitizarNombreVisible(string $nombreOriginal, string $extension): string
    {
        $base = pathinfo($nombreOriginal, PATHINFO_FILENAME);
        $limpio = trim((string) preg_replace('/[^A-Za-z0-9áéíóúÁÉÍÓÚñÑ _-]+/u', '', $base));
        $limpio = (string) preg_replace('/\s+/', ' ', $limpio);

        if ($limpio === '') {
            $limpio = 'documento';
        }

        return mb_substr($limpio, 0, 120).'.'.$extension;
    }

    /**
     * @throws ValidationException
     */
    private function fallar(string $mensaje): never
    {
        throw ValidationException::withMessages([
            'archivo' => [$mensaje],
        ]);
    }
}
