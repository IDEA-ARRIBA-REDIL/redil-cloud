<?php

namespace App\Http\Controllers;

use App\Models\RecursoGeneralEscuela;
use Illuminate\Support\Facades\Auth;

class RecursoGeneralEscuelaController extends Controller
{
    public function index()
    {

        return view('contenido.paginas.escuelas.recursos-generales.index');
    }

    public function misRecursos()
    {
        // 1. Obtenemos al usuario autenticado.
        $user = Auth::user();
        $recursos = collect(); // Por defecto, una colección vacía.

        // 2. Verificamos que el usuario haya iniciado sesión.
        if ($user) {
            // 3. Obtenemos el ROL ACTIVO del usuario (según la lógica de tu app).
            $rolActivo = $user->roles()->wherePivot('activo', true)->first();

            // 4. Si tiene un rol activo, buscamos los recursos asociados a ese rol.
            if ($rolActivo) {
                $recursos = RecursoGeneralEscuela::where('visible', true)
                    ->whereHas('roles', function ($query) use ($rolActivo) {
                        $query->where('role_id', $rolActivo->id);
                    })
                    ->latest()
                    ->get();
            }
        }

        // 5. Pasamos la colección de recursos (llena o vacía) a la vista.
        return view('contenido.paginas.escuelas.recursos-generales.mis-recursos', [
            'recursos' => $recursos,
        ]);
    }

    /**
     * Sube un archivo de recurso general mediante fetch/Alpine.js.
     */
    public function uploadArchivoRecursoGeneral(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,docx,xlsx,pptx,jpg,jpeg,png|max:10240',
        ]);

        $archivo = $request->file('archivo');
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = 'recurso-general-'.uniqid().'-'.time().'.'.$extension;

        $directorio = 'archivos/escuelas/recursos-generales';

        try {
            // Aseguramos que cada carpeta intermedia tenga permisos 0755
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
            \Illuminate\Support\Facades\Log::error('Error uploading general resource file: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno al subir el archivo.',
            ], 500);
        }
    }
}
