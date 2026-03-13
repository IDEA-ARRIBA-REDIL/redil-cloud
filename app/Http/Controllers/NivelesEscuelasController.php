<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Escuela;
use App\Models\NivelEscuela;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;

class NivelesEscuelasController extends Controller
{
    /**
     * Muestra el formulario para crear un nuevo nivel de escuela.
     *
     * @return \Illuminate\View\View
     */
    public function crear(Escuela $escuela)
    {
        // Obtenemos la configuración general del sistema
        $configuracion = Configuracion::find(1);

        // Obtenemos los tipos de usuario objetivos (para las restricciones)
        $tipoUsuariosObjetivo = TipoUsuario::all();

        // Obtenemos otros niveles de la misma escuela para los prerrequisitos
        $nivelesDisponibles = NivelEscuela::where('escuela_id', $escuela->id)->get();

        // Retornamos la vista con los datos necesarios
        return view('contenido.paginas.escuelas.niveles-escuelas.crear-nivel-escuela', [
            'escuela' => $escuela,
            'configuracion' => $configuracion,
            'tipoUsuariosObjetivo' => $tipoUsuariosObjetivo,
            'nivelesDisponibles' => $nivelesDisponibles,
        ])->with('moduloEscuelas', true);
    }

    /**
     * Guarda un nuevo nivel de escuela en la base de datos.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function guardar(Escuela $escuela, Request $request)
    {
        // Validación de los campos (replicando la lógica de materias)
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripción' => 'required|string',
            'asistenciasMinimas' => 'nullable|integer|min:1',
            'cantidadInasistencias' => 'nullable|integer|min:1',
            'limiteReportes' => 'nullable|integer|min:1',
            'cantidadReportesSemana' => 'nullable|integer|min:1',
            'diasPlazoReporte' => 'nullable|integer|min:0',
        ], [
            'nombre.required' => 'El nombre del nivel es obligatorio.',
            'descripción.required' => 'La descripción es obligatoria.',
        ]);

        // Debemos habilitar al menos un sistema
        if (! $request->habilitarCalificaciones && ! $request->habilitarAsistencias) {
            return redirect()->back()
                ->withErrors(['general' => 'Debe habilitar al menos Calificaciones o Asistencias'])
                ->withInput();
        }

        // Creamos la instancia del nivel
        $nivel = new NivelEscuela;
        $nivel->nombre = $request->nombre;
        $nivel->descripcion = $request->descripción;
        $nivel->escuela_id = $escuela->id;

        // Configuración de asistencias y calificaciones
        $nivel->habilitar_asistencias = $request->has('habilitarAsistencias');
        $nivel->habilitar_calificaciones = $request->has('habilitarCalificaciones');
        $nivel->habilitar_inasistencias = $request->has('habilitarInasistencias');
        $nivel->habilitar_traslado = $request->has('habilitarTraslado');
        $nivel->caracter_obligatorio = $request->has('obligatorio');

        $nivel->asistencias_minimas = $request->asistenciasMinimas;
        $nivel->asistencias_minima_alerta = $request->cantidadInasistencias;

        // Nuevos campos de paridad
        $nivel->limite_reporte_asistencias = $request->limiteReportes;
        $nivel->dia_limite_reporte = $request->dia;
        $nivel->tiene_dia_limite = $request->has('diaLimiteHabilitado');
        $nivel->cantidad_limite_reportes_semana = $request->cantidadReportesSemana ?? 1;
        $nivel->dias_plazo_reporte = $request->diasPlazoReporte;
        $nivel->tipo_usuario_objetivo_id = $request->tipoUsuarioObjetivo;

        // Guardamos para obtener el ID
        $nivel->save();

        // Manejo de prerrequisitos (niveles)
        if ($request->niveles_prerrequisito) {
            $datosPivot = [];
            foreach ($request->niveles_prerrequisito as $nivelId) {
                $datosPivot[$nivelId] = ['escuela_id' => $escuela->id];
            }
            $nivel->prerrequisitos()->sync($datosPivot);
        }

        // Manejo de la portada (si se envió)
        if ($request->foto) {
            $configuracion = Configuracion::find(1);
            $path = public_path('storage/'.$configuracion->ruta_almacenamiento.'/img/niveles/');
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $imagenPartes = explode(';base64,', $request->foto);
            if (isset($imagenPartes[1])) {
                $imagenBase64 = base64_decode($imagenPartes[1]);
                $nombreFoto = 'nivel'.$nivel->id.'.png';
                file_put_contents($path.$nombreFoto, $imagenBase64);
                $nivel->portada = $nombreFoto;
                $nivel->save();
            }
        }

        return redirect()->back()->with('success', 'Nivel creado exitosamente');
    }
}
