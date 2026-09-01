<?php

namespace App\Http\Controllers;

use App\Models\TipoHito;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TipoHitosController extends Controller
{
    /**
     * Listado de tipos de hitos.
     */
    public function listarTipoHitos(): View
    {
        $tiposHitos = TipoHito::withCount('hitos')->paginate(12);

        return view('contenido.paginas.tipo-hitos.listar-tipo-hitos', [
            'tiposHitos' => $tiposHitos,
        ]);
    }

    /**
     * Vista de creación de un nuevo tipo de hito.
     */
    public function creacionTipoHitos(): View
    {
        return view('contenido.paginas.tipo-hitos.crear-tipo-hitos');
    }

    /**
     * Almacena un nuevo tipo de hito.
     */
    public function crearTipoHitos(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50|unique:tipo_hitos,slug',
            'descripcion' => 'nullable|string|max:255',
            'icono' => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'slug.unique' => 'El slug ya se encuentra en uso.',
            'icono.required' => 'Debes indicar una clase de icono.',
            'color.required' => 'Debes seleccionar un color representativo.',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->nombre);

        // Asegurar unicidad del slug si fue auto-generado
        if (TipoHito::where('slug', $slug)->exists()) {
            $slug .= '-'.time();
        }

        TipoHito::create([
            'nombre' => $request->nombre,
            'slug' => $slug,
            'descripcion' => $request->descripcion,
            'icono' => $request->icono,
            'color' => $request->color,
            'requiere_trigger' => $request->boolean('requiere_trigger'),
            'requiere_actividad' => $request->boolean('requiere_actividad'),
            'permite_fotos_usuario' => $request->boolean('permite_fotos_usuario'),
            'permite_likes' => $request->boolean('permite_likes'),
            'evaluacion_dinamica' => $request->boolean('evaluacion_dinamica'),
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()->route('tipo-hitos.listarTipoHitos')
            ->with('success', 'Tipo de hito creado exitosamente.');
    }

    /**
     * Vista de edición de un tipo de hito existente.
     */
    public function actualizacionTipoHitos(int $id): View
    {
        $tipoHito = TipoHito::findOrFail($id);

        return view('contenido.paginas.tipo-hitos.editar-tipo-hitos', [
            'tipoHito' => $tipoHito,
        ]);
    }

    /**
     * Actualiza los datos de un tipo de hito.
     */
    public function actualizarTipoHitos(Request $request, int $id): RedirectResponse
    {
        $tipoHito = TipoHito::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'slug' => 'required|string|max:50|unique:tipo_hitos,slug,'.$tipoHito->id,
            'descripcion' => 'nullable|string|max:255',
            'icono' => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'slug.required' => 'El slug identificador es obligatorio.',
            'slug.unique' => 'El slug ya se encuentra en uso por otro tipo de hito.',
            'icono.required' => 'Debes indicar una clase de icono.',
            'color.required' => 'Debes seleccionar un color representativo.',
        ]);

        $tipoHito->update([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->slug),
            'descripcion' => $request->descripcion,
            'icono' => $request->icono,
            'color' => $request->color,
            'requiere_trigger' => $request->boolean('requiere_trigger'),
            'requiere_actividad' => $request->boolean('requiere_actividad'),
            'permite_fotos_usuario' => $request->boolean('permite_fotos_usuario'),
            'permite_likes' => $request->boolean('permite_likes'),
            'evaluacion_dinamica' => $request->boolean('evaluacion_dinamica'),
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()->route('tipo-hitos.listarTipoHitos')
            ->with('success', 'Tipo de hito actualizado exitosamente.');
    }

    /**
     * Elimina un tipo de hito (si no tiene hitos vinculados).
     */
    public function eliminarTipoHitos(int $id): RedirectResponse
    {
        $tipoHito = TipoHito::findOrFail($id);

        // Protección para slugs del sistema
        if (in_array($tipoHito->slug, ['general', 'automatico', 'actividad', 'manual'])) {
            return redirect()->route('tipo-hitos.listarTipoHitos')
                ->with('status_error', 'No se pueden eliminar los tipos de hito nativos del sistema.');
        }

        if ($tipoHito->hitos()->count() > 0) {
            return redirect()->route('tipo-hitos.listarTipoHitos')
                ->with('status_error', 'No se puede eliminar este tipo de hito porque tiene '.$tipoHito->hitos()->count().' hito(s) asociado(s).');
        }

        $tipoHito->delete();

        return redirect()->route('tipo-hitos.listarTipoHitos')
            ->with('success', 'Tipo de hito eliminado exitosamente.');
    }

    /**
     * Alterna el estado activo/inactivo vía AJAX.
     */
    public function toggleEstado(int $id): JsonResponse
    {
        $tipoHito = TipoHito::findOrFail($id);
        $tipoHito->activo = ! $tipoHito->activo;
        $tipoHito->save();

        return response()->json([
            'success' => true,
            'nuevo_estado' => $tipoHito->activo,
            'mensaje' => 'El estado se actualizó a '.($tipoHito->activo ? 'Activo' : 'Inactivo').'.',
        ]);
    }
}
