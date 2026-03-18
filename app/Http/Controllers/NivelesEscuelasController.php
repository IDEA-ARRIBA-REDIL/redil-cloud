<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Escuela;
use App\Models\Materia;
use App\Models\NivelEscuela;
use App\Models\NivelTareaCulminada;
use App\Models\NivelTareaRequisito;
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
     * Guarda un nuevo nivel de escuela en la base de datos. cambios
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
        $nivel->tipo_usuario_inicial_id = $request->tipoUsuarioInicial;

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

        // Guardar relaciones (pasos y tareas)
        $this->guardarRelaciones($nivel, $request);

        return redirect()->back()->with('success', 'Nivel creado exitosamente');
    }

    /**
     * Muestra el formulario para editar un nivel existente.
     */
    public function editar(Escuela $escuela, NivelEscuela $nivel)
    {
        $configuracion = Configuracion::find(1);
        $tipoUsuariosObjetivo = TipoUsuario::all();
        $nivelesDisponibles = NivelEscuela::where('escuela_id', $escuela->id)
            ->where('id', '!=', $nivel->id)
            ->get();

        // Prerrequisitos actuales para el select multiple
        $prerrequisitosIds = $nivel->prerrequisitos->pluck('id')->toArray();

        return view('contenido.paginas.escuelas.niveles-escuelas.actualizar-nivel-escuela', [
            'escuela' => $escuela,
            'nivel' => $nivel,
            'configuracion' => $configuracion,
            'tipoUsuariosObjetivo' => $tipoUsuariosObjetivo,
            'nivelesDisponibles' => $nivelesDisponibles,
            'prerrequisitosIds' => $prerrequisitosIds,
        ])->with('moduloEscuelas', true);
    }

    /**
     * Actualiza un nivel de escuela en la base de datos.
     */
    public function actualizar(Escuela $escuela, NivelEscuela $nivel, Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripción' => 'required|string',
        ]);

        $nivel->nombre = $request->nombre;
        $nivel->descripcion = $request->descripción;
        $nivel->habilitar_asistencias = $request->has('habilitarAsistencias');
        $nivel->habilitar_calificaciones = $request->has('habilitarCalificaciones');
        $nivel->habilitar_inasistencias = $request->has('habilitarInasistencias');
        $nivel->habilitar_traslado = $request->has('habilitarTraslado');
        $nivel->caracter_obligatorio = $request->has('obligatorio');

        $nivel->asistencias_minimas = $request->asistenciasMinimas;
        $nivel->asistencias_minima_alerta = $request->cantidadInasistencias;
        $nivel->limite_reporte_asistencias = $request->limiteReportes;
        $nivel->dia_limite_reporte = $request->dia;
        $nivel->tiene_dia_limite = $request->has('diaLimiteHabilitado');
        $nivel->cantidad_limite_reportes_semana = $request->cantidadReportesSemana ?? 1;
        $nivel->dias_plazo_reporte = $request->diasPlazoReporte;
        $nivel->tipo_usuario_objetivo_id = $request->tipoUsuarioObjetivo;
        $nivel->tipo_usuario_inicial_id = $request->tipoUsuarioInicial;

        $nivel->save();

        // Sincronizar prerrequisitos
        if ($request->niveles_prerrequisito) {
            $datosPivot = [];
            foreach ($request->niveles_prerrequisito as $nivelId) {
                $datosPivot[$nivelId] = ['escuela_id' => $escuela->id];
            }
            $nivel->prerrequisitos()->sync($datosPivot);
        } else {
            $nivel->prerrequisitos()->detach();
        }

        // Manejo de la portada
        if ($request->foto) {
            $configuracion = Configuracion::find(1);
            $path = public_path('storage/'.$configuracion->ruta_almacenamiento.'/img/niveles/');
            $imagenBase64 = base64_decode(explode(';base64,', $request->foto)[1]);
            $nombreFoto = 'nivel'.$nivel->id.'.png';
            file_put_contents($path.$nombreFoto, $imagenBase64);
            $nivel->portada = $nombreFoto;
            $nivel->save();
        }

        // Las relaciones de pasos y tareas se manejan vía Livewire directamente al modelo
        // Pero el botón 'Guardar' del formulario principal puede ser aprovechado para redirigir
        return redirect()->route('escuelas.niveles', $escuela)->with('success', 'Grado actualizado exitosamente');
    }

    /**
     * Vista de gestión de materias para un nivel.
     */
    public function gestionarMaterias(Escuela $escuela, NivelEscuela $nivel)
    {
        $materiasAgrupadas = $nivel->materiasAgrupadas()->orderBy('id', 'asc')->get();

        return view('contenido.paginas.escuelas.niveles-escuelas.gestionar-materias', [
            'escuela' => $escuela,
            'nivel' => $nivel,
            'materiasAgrupadas' => $materiasAgrupadas,
        ])->with('moduloEscuelas', true);
    }

    /**
     * Guarda una materia simplificada asociada al nivel.
     */
    public function guardarMateriaAgrupada(Escuela $escuela, NivelEscuela $nivel, Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripción' => 'nullable|string',
        ]);

        Materia::create([
            'nivel_id' => $nivel->id,
            'escuela_id' => $escuela->id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripción,
        ]);

        return redirect()->back()->with('success', 'Materia agregada correctamente');
    }

    /**
     * Elimina una materia agrupada.
     */
    public function eliminarMateriaAgrupada(Escuela $escuela, NivelEscuela $nivel, Materia $materia)
    {
        $materia->delete();

        return redirect()->back()->with('success', 'Materia eliminada correctamente');
    }

    private function guardarRelaciones(NivelEscuela $nivel, Request $request)
    {
        // Limpiar relaciones existentes SOLO para 'al_iniciar' (1)
        $nivel->pasosCrecimiento()->wherePivot('al_iniciar', 1)->detach();

        // Guardar paso al iniciar (Lista)
        $this->guardarPasosIniciarList($nivel, $request->pasos_iniciar);

        // Pasos al culminar (Si existiera un selector simple, pero aquí usamos Livewire)
        // Si hay datos de pasos_culminados del draft (modo creación)
        $this->guardarPasosCulminarList($nivel, $request->pasos_culminados);

        // Procesos prerrequisito
        $this->guardarProcesosPrerrequisito($nivel, $request->proceso_prerrequisito);

        // Tareas Prerrequisito
        $this->guardarTareasPrerrequisito($nivel, $request->tareas_prerrequisito);

        // Tareas Culminadas
        $this->guardarTareasCulminadas($nivel, $request->tareas_culminadas);
    }

    private function guardarPasosIniciarList(NivelEscuela $nivel, $pasos)
    {
        foreach ((array) $pasos as $index => $item) {
            if (str_contains($item, '|')) {
                [$pasoId, $estadoId] = explode('|', $item);
                $nivel->pasosCrecimiento()->attach($pasoId, [
                    'estado_paso_crecimiento_usuario_id' => $estadoId,
                    'estado' => $estadoId, // Legacy mapping
                    'al_iniciar' => 1,
                    'indice' => $index + 1,
                ]);
            }
        }
    }

    private function guardarPasosCulminarList(NivelEscuela $nivel, $pasos)
    {
        foreach ((array) $pasos as $index => $item) {
            if (str_contains($item, '|')) {
                [$pasoId, $estadoId] = explode('|', $item);
                $nivel->pasosCrecimiento()->attach($pasoId, [
                    'estado_paso_crecimiento_usuario_id' => $estadoId,
                    'estado' => $estadoId,
                    'al_iniciar' => 0,
                    'indice' => $index + 1,
                ]);
            }
        }
    }

    private function guardarProcesosPrerrequisito(NivelEscuela $nivel, $procesos)
    {
        $procesosData = [];
        foreach ((array) $procesos as $index => $proceso) {
            if (str_contains($proceso, '|')) {
                [$pasoId, $estadoId] = explode('|', $proceso);
                $procesosData[$pasoId] = [
                    'estado_paso_crecimiento_usuario_id' => $estadoId,
                    'estado_proceso' => $estadoId,
                    'indice' => $index + 1,
                ];
            }
        }
        $nivel->procesosPrerrequisito()->sync($procesosData);
    }

    private function guardarTareasPrerrequisito(NivelEscuela $nivel, $tareas)
    {
        foreach ((array) $tareas as $index => $tarea) {
            if (str_contains($tarea, '|')) {
                [$tareaId, $estadoId] = explode('|', $tarea);
                NivelTareaRequisito::create([
                    'nivel_id' => $nivel->id,
                    'tarea_consolidacion_id' => $tareaId,
                    'estado_tarea_consolidacion_id' => $estadoId,
                    'indice' => $index + 1,
                ]);
            }
        }
    }

    private function guardarTareasCulminadas(NivelEscuela $nivel, $tareas)
    {
        foreach ((array) $tareas as $index => $tarea) {
            if (str_contains($tarea, '|')) {
                [$tareaId, $estadoId] = explode('|', $tarea);
                NivelTareaCulminada::create([
                    'nivel_id' => $nivel->id,
                    'tarea_consolidacion_id' => $tareaId,
                    'estado_tarea_consolidacion_id' => $estadoId,
                    'indice' => $index + 1,
                ]);
            }
        }
    }
}
