<?php

namespace App\Http\Controllers;

use App\Models\TipoServicioReporteReunion;
use Illuminate\Http\Request;

class TipoServicioReporteReunionController extends Controller
{
    /**
     * Listar tipos de servicios de reporte de reunión.
     */
    public function listar()
    {
        $servicios = TipoServicioReporteReunion::all();

        return view('contenido.paginas.tipo-servicio-reunion.listar', compact('servicios'));
    }

    /**
     * Guardar nuevo tipo de servicio.
     */
    public function crear(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        TipoServicioReporteReunion::create($validated);

        return redirect()->route('tipo-servicio-reunion.listar')->with('success', 'Tipo de servicio de reunión creado correctamente.');
    }

    /**
     * Actualizar tipo de servicio.
     */
    public function actualizar(Request $request, TipoServicioReporteReunion $tipoServicioReporteReunion)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $tipoServicioReporteReunion->update($validated);

        return redirect()->route('tipo-servicio-reunion.listar')->with('success', 'Tipo de servicio de reunión actualizado correctamente.');
    }

    /**
     * Eliminar tipo de servicio.
     */
    public function eliminar(TipoServicioReporteReunion $tipoServicioReporteReunion)
    {
        $tipoServicioReporteReunion->delete();

        return redirect()->route('tipo-servicio-reunion.listar')->with('success', 'Tipo de servicio de reunión eliminado correctamente.');
    }
}
