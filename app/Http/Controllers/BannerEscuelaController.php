<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerEscuelaController extends Controller
{
    /**
     * Muestra la vista principal para gestionar los banners.
     */
    public function gestionar(): View
    {
        // Esto le dice a Laravel que busque y muestre el archivo de vista en:
        // resources/views/contenido/paginas/escuelas/banners/gestionar.blade.php
        return view('contenido.paginas.escuelas.banners.gestionar');
    }

    /**
     * Sube un archivo de banner de escuela mediante fetch/Alpine.js.
     */
    public function uploadBannerEscuela(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'archivo' => 'required|file|image|max:5120',
        ]);

        $archivo = $request->file('archivo');
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = 'banner-escuela-'.uniqid().'-'.time().'.'.$extension;

        $directorio = 'archivos/escuelas/banners';

        try {
            // Aseguramos que cada carpeta intermedia tenga permisos 0755 para evitar problemas de permisos en multi-tenancy
            $currentPath = storage_path('app/public');
            $relativeParts = explode('/', trim($directorio, '/'));
            foreach ($relativeParts as $part) {
                if (empty($part)) {
                    continue;
                }
                $currentPath .= '/'.$part;
                if (! file_exists($currentPath)) {
                    @mkdir($currentPath, 0755, true);
                }
                @chmod($currentPath, 0755);
            }

            // Almacenamos el archivo en el disco 'public'
            $archivo->storeAs($directorio, $nombreArchivo, 'public');

            // Aseguramos que el archivo recién creado tenga permisos 0755
            $filePath = storage_path("app/public/{$directorio}/{$nombreArchivo}");
            @chmod($filePath, 0755);

            return response()->json([
                'success' => true,
                'nombre' => $nombreArchivo,
                'ruta_relativa' => "{$directorio}/{$nombreArchivo}",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error uploading banner escuela file: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno al subir la imagen.',
            ], 500);
        }
    }
}
