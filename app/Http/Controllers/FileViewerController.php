<?php

namespace App\Http\Controllers;

use App\Models\CursoLeccion;
use Illuminate\Http\Request;
use Vish4395\LaravelFileViewer\LaravelFileViewer;
use Illuminate\Support\Facades\Storage;

class FileViewerController extends Controller
{
    public function preview($leccionId)
    {
        $leccion = CursoLeccion::findOrFail($leccionId);

        if (!$leccion->archivo_path) {
            abort(404, 'Archivo no encontrado');
        }

        // 1. Nombre del archivo
        $fileName = basename($leccion->archivo_path);

        // 2. Ruta RELATIVA al disco (por defecto 'public').
        // El paquete usa Storage::disk('public')->exists($filePath), así que NO debe ser absoluta.
        $filePath = $leccion->archivo_path;

        // 3. URL pública para el navegador
        $fileUrl = url(Storage::url($leccion->archivo_path));

        // MEJORA: Manejo nativo para PDFs
        // El paquete usa visores externos (Google/Office) que a veces fallan o salen en blanco para PDFs.
        // Los navegadores modernos renderizan PDFs nativamente mucho mejor.
        $mimeType = Storage::disk('public')->mimeType($filePath);

        if ($mimeType === 'application/pdf') {
            $disposition = request()->has('download') ? 'attachment' : 'inline';
            return response()->file(storage_path('app/public/' . $leccion->archivo_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition . '; filename="' . $fileName . '"'
            ]);
        }

        // MEJORA: Manejo específico para Office (PPTX, DOCX, XLSX)
        $officeMimes = [
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
            'application/vnd.ms-powerpoint', // ppt
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
            'application/msword', // doc
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
            'application/vnd.ms-excel' // xls
        ];

        if (in_array($mimeType, $officeMimes)) {
            return redirect('https://docs.google.com/viewer?url=' . urlencode($fileUrl) . '&embedded=true');
        }

        $file_viewer = new LaravelFileViewer();
        return $file_viewer->show($fileName, $filePath, $fileUrl);
    }
}
