<?php

namespace App\Http\Controllers;

use App\Models\TipoServicioActividad;
use Illuminate\Http\Request;

class TipoServicioActividadController extends Controller
{
    /**
     * Listar tipos de servicios de actividad.
     */
    public function listar()
    {
        $servicios = TipoServicioActividad::all();

        return view('contenido.paginas.tipo-servicio-actividad.listar', compact('servicios'));
    }

    /**
     * Guardar nuevo tipo de servicio.
     */
    public function crear(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        TipoServicioActividad::create($validated);

        return redirect()->route('tipo-servicio-actividad.listar')->with('success', 'Tipo de servicio de actividad creado correctamente.');
    }

    /**
     * Actualizar tipo de servicio.
     */
    public function actualizar(Request $request, TipoServicioActividad $tipoServicioActividad)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $tipoServicioActividad->update($validated);

        return redirect()->route('tipo-servicio-actividad.listar')->with('success', 'Tipo de servicio de actividad actualizado correctamente.');
    }

    /**
     * Eliminar tipo de servicio.
     */
    public function eliminar(TipoServicioActividad $tipoServicioActividad)
    {
        $tipoServicioActividad->delete();

        return redirect()->route('tipo-servicio-actividad.listar')->with('success', 'Tipo de servicio de actividad eliminado correctamente.');
    }
}
