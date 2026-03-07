<?php

namespace App\Http\Controllers;

use App\Models\Escuela;
use App\Models\MatriculaNivel;
use App\Models\NivelAgrupacion;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatriculaNivelController extends Controller
{
    /**
     * Muestra la vista de selección de nivel para matrícula.
     */
    public function seleccionarNivel(Escuela $escuela)
    {
        // Validar tipo de matrícula
        if ($escuela->tipo_matricula !== 'niveles_agrupados') {
            return redirect()->route('escuelas.matriculas.index', $escuela);
        }

        // Obtener periodo activo (Lógica simplificada, ajustar según el sistema real de periodos)
        $periodoActivo = Periodo::where('escuela_id', $escuela->id)->where('estado', 'activo')->firstOrFail();

        // Obtener niveles disponibles
        $niveles = NivelAgrupacion::where('escuela_id', $escuela->id)
                                  ->where('activo', true)
                                  ->orderBy('orden')
                                  ->get();

        return view('contenido.paginas.escuelas.matriculas.seleccionar-nivel', [
            'escuela' => $escuela,
            'periodo' => $periodoActivo,
            'niveles' => $niveles
        ]);
    }

    /**
     * Procesa la solicitud de matrícula e inicia el wizard de selección de horarios.
     */
    public function iniciarMatricula(Request $request, Escuela $escuela)
    {
        $request->validate([
            'nivel_agrupacion_id' => 'required|exists:niveles_agrupacion,id',
            'periodo_id' => 'required|exists:periodos,id'
        ]);

        $nivel = NivelAgrupacion::findOrFail($request->nivel_agrupacion_id);

        // Aquí iría la lógica de validación de requisitos (si aprobó el nivel anterior)

        return redirect()->route('escuelas.matriculas.nivel.seleccion-horarios', [
            'escuela' => $escuela,
            'nivel' => $nivel->id,
            'periodo' => $request->periodo_id
        ]);
    }

    /**
     * Vista (posiblemente Livewire) para seleccionar los horarios de las materias del nivel.
     */
    public function seleccionHorarios(Escuela $escuela, NivelAgrupacion $nivel, Periodo $periodo)
    {
        return view('contenido.paginas.escuelas.matriculas.seleccion-horarios-nivel', [
            'escuela' => $escuela,
            'nivel' => $nivel,
            'periodo' => $periodo
        ]);
    }
}
