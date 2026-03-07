<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Escuela;
use App\Models\NivelAgrupacion;
use App\Models\NivelAgrupacionConfiguracion;
use App\Models\NivelTareaRequisito;
use App\Models\NivelTareaCulminada;
use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class NivelController extends Controller
{
    public function index(Escuela $escuela)
    {
        return view('contenido.paginas.escuelas.niveles.listar', compact('escuela'));
    }

    public function create(Escuela $escuela)
    {
        return view('contenido.paginas.escuelas.niveles.crear', compact('escuela'));
    }

    public function store(Escuela $escuela, Request $request)
    {
        // 1. Validación
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
            'orden' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',

            // Configuración general
            'habilitar_calificaciones' => 'nullable|boolean',
            'habilitar_asistencias' => 'nullable|boolean',
            'habilitar_inasistencias' => 'nullable|boolean',

            // Reglas específicas (validación cruzada)
            'limite_reportes' => 'nullable|integer|min:1',
            'asistencias_minimas' => 'nullable|integer|min:1',
            'cantidad_inasistencias_alerta' => 'nullable|integer|min:1',

            'cantidad_reportes_semana' => 'nullable|integer|min:0',
            'dias_plazo_reporte' => 'nullable|integer|min:0',
            'dia_limite_reporte' => 'nullable|integer|between:0,6',
        ]);

        // Validación de lógica de negocio (Al menos uno habilitado)
        if (!$request->has('habilitar_calificaciones') && !$request->has('habilitar_asistencias')) {
            return redirect()->back()
                ->withErrors(['general' => 'Debe habilitar al menos Calificaciones o Asistencias.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // 2. Crear Nivel
            $nivel = NivelAgrupacion::create([
                'escuela_id' => $escuela->id,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'orden' => $request->orden,
                'activo' => true
            ]);

            // 3. Crear Configuración
            $configuracion = new NivelAgrupacionConfiguracion([
                'nivel_agrupacion_id' => $nivel->id,

                // Toggles Principales
                'habilitar_calificaciones' => $request->has('habilitar_calificaciones'),
                'habilitar_asistencias' => $request->has('habilitar_asistencias'),
                'habilitar_inasistencias' => $request->has('habilitar_inasistencias'),
                'caracter_obligatorio' => $request->has('caracter_obligatorio'),

                // Valores Numéricos
                'asistencias_minimas' => $request->asistencias_minimas,
                'max_reportes_permitidos' => $request->limite_reportes, // Mapeo al campo correcto de la BD
                'limite_reportes' => $request->limite_reportes, // Backup si el campo nuevo existe
                'cantidad_inasistencias_alerta' => $request->cantidad_inasistencias_alerta, // Mapeo

                // Configuración de Reportes
                'dia_limite_habilitado' => $request->has('dia_limite_habilitado'),
                'dia_limite_reporte' => $request->dia_limite_reporte,
                'cantidad_reportes_semana' => $request->cantidad_reportes_semana,
                'dias_plazo_reporte' => $request->dias_plazo_reporte,
            ]);
            $configuracion->save();

            // 4. Guardar Relaciones Complejas (Pasos y Tareas)
            $this->guardarRelaciones($nivel, $request);

            DB::commit();

            return redirect()->route('escuelas.niveles.index', $escuela)
                ->with('success', 'Grado creado exitosamente con toda su configuración.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Ocurrió un error al guardar el nivel: ' . $e->getMessage()])
                ->withInput();
        }
    }

    private function guardarRelaciones(NivelAgrupacion $nivel, Request $request)
    {
        // 1. Pasos al Iniciar
        if ($request->pasos_iniciar) {
            $indice = 1;
            foreach ($request->pasos_iniciar as $paso) {
                // $paso es array [paso_id, estado_id]
                $nivel->pasosCrecimiento()->attach($paso['paso_id'], [
                    'estado_paso_crecimiento_usuario_id' => $paso['estado_id'],
                    'estado' => $paso['estado_id'], // Legacy support
                    'al_iniciar' => true,
                    'indice' => $indice++
                ]);
            }
        }

        // 2. Prerrequisitos (Pasos requeridos)
        if ($request->pasos_requisito) {
            $indice = 1;
            foreach ($request->pasos_requisito as $paso) {
                $nivel->procesosPrerrequisito()->attach($paso['paso_id'], [
                    'estado_paso_crecimiento_usuario_id' => $paso['estado_id'],
                    'estado_proceso' => $paso['estado_id'], // Legacy support
                    'indice' => $indice++
                ]);
            }
        }

        // 3. Pasos al Culminar
        if ($request->pasos_culminar) {
            $indice = 1;
            foreach ($request->pasos_culminar as $paso) {
                $nivel->pasosCrecimiento()->attach($paso['paso_id'], [
                    'estado_paso_crecimiento_usuario_id' => $paso['estado_id'],
                    'estado' => $paso['estado_id'], // Legacy support
                    'al_iniciar' => false,
                    'indice' => $indice++
                ]);
            }
        }

        // 4. Tareas Requisito
        if ($request->tareas_requisito) {
            $indice = 1;
            foreach ($request->tareas_requisito as $tarea) {
                NivelTareaRequisito::create([
                    'nivel_agrupacion_id' => $nivel->id,
                    'tarea_consolidacion_id' => $tarea['tarea_id'],
                    'estado_tarea_consolidacion_id' => $tarea['estado_id'],
                    'indice' => $indice++
                ]);
            }
        }

        // 5. Tareas Culminadas
        if ($request->tareas_culminar) {
            $indice = 1;
            foreach ($request->tareas_culminar as $tarea) {
                NivelTareaCulminada::create([
                    'nivel_agrupacion_id' => $nivel->id,
                    'tarea_consolidacion_id' => $tarea['tarea_id'],
                    'estado_tarea_consolidacion_id' => $tarea['estado_id'],
                    'indice' => $indice++
                ]);
            }
        }
    }
}
