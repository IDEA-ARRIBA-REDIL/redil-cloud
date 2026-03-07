<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use \stdClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\PasoCrecimiento;
use App\Models\Configuracion;
use App\Models\Materia;
use App\Models\Escuela;
use App\Models\TipoAula;
use App\Models\Aula;
use App\Models\HorarioBase;
use App\Models\HorarioMateriaPeriodo;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\Matricula;
use App\Models\TipoUsuario;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;
use App\Models\MateriaTareaRequisito;
use App\Models\MateriaTareaCulminada;

class MateriaController extends Controller
{
    //

    public function crear(Escuela $escuela)
    {
        $configuracion = Configuracion::find(1);
        $pasosCrecimiento = $this->construirPasosCrecimiento();
        $materiasEscuela = $escuela->materias;

        return view('contenido.paginas.escuelas.materias.crear-materia', [
            'escuela' => $escuela,
            'configuracion' => $configuracion,
            'pasosCrecimiento' => $pasosCrecimiento,
            'materiasEscuela' => $materiasEscuela,
            'tareasConsolidacion' => $this->construirTareasConsolidacion(),
            'tipoUsuariosObjetivo' => TipoUsuario::all()
        ])->with('moduloEscuelas', true);
    }

    public function eliminar(Materia $materia)
    {

        // VERIFICACIÓN: Comprobamos si la materia tiene alguna relación anidada
        // que llegue hasta una 'Matricula'. La notación de punto nos permite
        // revisar a través de las relaciones del modelo.

        $horariosBase = HorarioBase::where('materia_id', $materia->id)->pluck('id')->toArray();
        $horariosMP = HorarioMateriaPeriodo::whereIn('horario_base_id', $horariosBase)->pluck('id')->toArray();
        $tieneMatriculas = Matricula::whereIn('horario_materia_periodo_id', $horariosMP)->exists();

        if ($tieneMatriculas) {
            // Si existe al menos una matrícula, no se puede eliminar.
            // Redirigimos hacia atrás con un mensaje de error.
            return redirect()->back()
                ->with('error', 'No se puede eliminar la materia  porque ya tiene alumnos matriculados en su historial.');
        }

        // Si la validación pasa, procedemos con la eliminación (soft delete).
        $materia->delete();

        // Redirigimos hacia atrás con un mensaje de éxito.
        return redirect()->back()
            ->with('success', 'La materia "' . $materia->nombre . '" ha sido eliminada exitosamente.');
    }


    private function construirPasosCrecimiento()
    {
        $pasos_crecimiento = PasoCrecimiento::orderBy('nombre', 'asc')->get();
        $estados = EstadoPasoCrecimientoUsuario::orderBy('nombre', 'asc')->get();
        $resultado = [];

        foreach ($pasos_crecimiento as $paso) {
            foreach ($estados as $estado) {
                $item = new \stdClass();
                $item->id_paso = $paso->id;
                $item->estado_id = $estado->id;
                $item->nombre = $paso->nombre . ' - ' . $estado->nombre;
                $resultado[] = $item;
            }
        }

        return $resultado;
    }

    private function construirTareasConsolidacion() {
        $tareas = TareaConsolidacion::orderBy('orden')->get();
        $estados = EstadoTareaConsolidacion::orderBy('puntaje')->get();
        $resultado = [];

        foreach ($tareas as $tarea) {
            foreach ($estados as $estado) {
                $item = new \stdClass();
                $item->id_tarea = $tarea->id;
                $item->estado_id = $estado->id;
                $item->nombre = $tarea->nombre . ' - ' . $estado->nombre;
                $resultado[] = $item;
            }
        }
        return $resultado;
    }

    public function guardar(Escuela $escuela, Request $request)
    {

        // Validación de los campos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripción' => 'required|string',
            'nivel_id' => 'nullable|integer',
            'asistenciasMinimas' => 'integer|min:1',
            'asistenciasAlerta' => 'integer|min:1',
            'paso_iniciar_id' => 'nullable|string',
            'paso_culminar_id' => 'nullable|string',
            'proceso_prerrequisito' => 'nullable|array',
            'proceso_prerrequisito.*' => 'string',
            'materias_prerrequisito' => 'nullable|array',
            'materias_prerrequisito.*' => 'string',
        ], [
            'nombre.required' => 'El nombre de la materia es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'descripción.required' => 'La descripción es obligatoria.',
            'asistenciasMinimas.integer' => 'Las asistencias mínimas deben ser un número entero.',
            'asistenciasMinimas.min' => 'Las asistencias mínimas deben ser al menos 1.',
            'asistenciasAlerta.integer' => 'La cantidad de inasistencias para alerta debe ser un número entero.',
            'asistenciasAlerta.min' => 'La cantidad de inasistencias para alerta debe ser al menos 1.',
        ]);

        // Validación adicional para al menos un sistema habilitado
        if (!$request->habilitarCalificaciones && !$request->habilitarAsistencias) {
            return redirect()->back()
                ->withErrors(['general' => 'Debe habilitar al menos Calificaciones o Asistencias'])
                ->withInput();
        }


        $configuracion = Configuracion::find(1);
        $materia = new Materia();
        $materia->nombre = $request->nombre;
        $materia->descripcion = $request->descripción;


        $materia->limite_reporte_asistencias = $request->limiteReportes;
        $materia->dia_limite_reporte = $request->dia;
        if ($request->diaLimiteHabilitado == 'on') {
            $materia->tiene_dia_limite  = $request->diaLimiteHabilitado;
        }



        if ($request->habilitarAsistencias == 'on') {
            $materia->habilitar_asistencias = $request->habilitarAsistencias;
        }

        if ($request->habilitarCalificaciones == 'on') {
            $materia->habilitar_calificaciones = $request->habilitarCalificaciones;
        }
        if ($request->habilitarInasistencias == 'on') {
            $materia->habilitar_inasistencias = $request->habilitarInasistencias;
        }
        if ($request->habilitarTraslado == 'on') {
            $materia->habilitar_traslado = $request->habilitarTraslado;
        }
        if ($request->obligatorio == 'on') {
            $materia->caracter_obligatorio = $request->obligatorio;
        }
        $materia->escuela_id = $escuela->id;
        $materia->nivel_id = $request->nivel_id;
        $materia->asistencias_minimas = $request->asistenciasMinimas;
        $materia->asistencias_minima_alerta = $request->cantidadInasistencias;
        $materia->tipo_usuario_objetivo_id = $request->tipoUsuarioObjetivo;

        $materia->save();



        // AÑADO LA PORTADA
        if ($request->foto) {
            if ($configuracion->version == 1) {
                $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/materias/');
                !is_dir($path) && mkdir($path, 0777, true);

                $imagenPartes = explode(';base64,', $request->foto);
                $imagenBase64 = base64_decode($imagenPartes[1]);
                $nombreFoto = 'materia' . $materia->id . '.png';
                $imagenPath = $path . $nombreFoto;
                file_put_contents($imagenPath, $imagenBase64);
                $materia->portada = $nombreFoto;
                $materia->save();
            } else {
                /*
             $s3 = AWS::get('s3');
             $s3->putObject(array(
               'Bucket'     => $_ENV['aws_bucket'],
               'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
               'SourceFile' => "img/temp/".Input::get('foto-hide'),
             ));*/
            }
        }



        // Materias prerrequisito



        $this->guardarRelaciones($materia, $request);
        $this->guardarProcesosPrerrequisito($materia, $request->proceso_prerrequisito);


        return redirect()->route('materias.gestionar', $materia)->with('success', 'Materia creada exitosamente');
    }

    private function guardarRelaciones(Materia $materia, Request $request)
    {
        // Limpiar relaciones existentes SOLO para 'al_iniciar' (1)
        // Dejamos 'al_culminar' (0) intactos porque los gestiona Livewire
        $materia->pasosCrecimiento()->wherePivot('al_iniciar', 1)->detach();

        // Guardar paso al iniciar (Lista)
        $this->guardarPasosIniciarList($materia, $request->pasos_iniciar);

        // Guardar paso al culminar
        if ($request->paso_culminar_id) {
            $this->procesarPaso($materia, $request->paso_culminar_id, false);
        }

        // Materias prerrequisito
        $materia->prerrequisitosMaterias()->sync($request->materias_prerrequisito ?? []);

        // Tareas Prerrequisito
        $this->guardarTareasPrerrequisito($materia, $request->tareas_prerrequisito);

        // Tareas Culminadas
        $this->guardarTareasCulminadas($materia, $request->tareas_culminadas);

        // Pasos Culminados (Lista)
        $this->guardarPasosCulminadosList($materia, $request->pasos_culminados);
    }


    private function guardarPasosIniciarList(Materia $materia, $pasos)
    {
        if ($pasos) {
            $indice = $materia->pasosCrecimiento()->wherePivot('al_iniciar', 1)->max('indice') ?? 0;
            $indice++;

            foreach ($pasos as $pasoData) {
                 if (str_contains($pasoData, '|')) {
                    list($pasoId, $estadoId) = explode('|', $pasoData);

                    // Check if exists
                    $exists = $materia->pasosCrecimiento()
                        ->where('paso_crecimiento_id', $pasoId)
                        ->wherePivot('al_iniciar', 1)
                        ->exists();

                    if (!$exists) {
                         $materia->pasosCrecimiento()->attach($pasoId, [
                            'estado_paso_crecimiento_usuario_id' => $estadoId,
                            'estado' => $estadoId, // Legacy
                            'al_iniciar' => 1,
                            'indice' => $indice++
                        ]);
                    }
                }
            }
        }
    }

    private function guardarPasosCulminadosList(Materia $materia, $pasos)
    {
        if ($pasos) {
            $indice = $materia->pasosCrecimiento()->wherePivot('al_iniciar', 0)->max('indice') ?? 0;
            $indice++;

            foreach ($pasos as $pasoData) {
                 if (str_contains($pasoData, '|')) {
                    list($pasoId, $estadoId) = explode('|', $pasoData);

                    // Check if exists
                    $exists = $materia->pasosCrecimiento()
                        ->where('paso_crecimiento_id', $pasoId)
                        ->wherePivot('al_iniciar', 0)
                        ->exists();

                    if (!$exists) {
                         $materia->pasosCrecimiento()->attach($pasoId, [
                            'estado_paso_crecimiento_usuario_id' => $estadoId,
                            'estado' => $estadoId, // Legacy
                            'al_iniciar' => 0,
                            'indice' => $indice++
                        ]);
                    }
                }
            }
        }
    }

    private function procesarPaso($materia, $pasoCompleto, $esInicio)
    {
        if (str_contains($pasoCompleto, '|')) {
            list($pasoId, $estadoId) = explode('|', $pasoCompleto);
            $materia->pasosCrecimiento()->attach($pasoId, [
                'estado_paso_crecimiento_usuario_id' => $estadoId,
                'estado' => $estadoId, // Mantenemos por retrocompatibilidad si es necesario
                'al_iniciar' => $esInicio
            ]);
        }
    }

    private function guardarTareasPrerrequisito(Materia $materia, $tareas)
    {
        // Limpiamos todo para simplificar (podría optimizarse, pero replicamos la lógica de detach/attach o sync si fuera many-to-many estricto)
        // Como 'MateriaTareaRequisito' tiene su propio modelo y tabla (no es un simple pivot N:N con tabla standard), hacemos delete/create
        MateriaTareaRequisito::where('materia_id', $materia->id)->delete();

        if ($tareas) {
            $indice = 1;
            foreach ($tareas as $tareaCompleta) {
                if (str_contains($tareaCompleta, '|')) {
                    list($tareaId, $estadoId) = explode('|', $tareaCompleta);
                    MateriaTareaRequisito::create([
                        'materia_id' => $materia->id,
                        'tarea_consolidacion_id' => $tareaId,
                        'estado_tarea_consolidacion_id' => $estadoId,
                        'indice' => $indice++
                    ]);
                }
            }
        }
    }

    private function guardarTareasCulminadas(Materia $materia, $tareas)
    {
        MateriaTareaCulminada::where('materia_id', $materia->id)->delete();

        if ($tareas) {
            $indice = 1;
            foreach ($tareas as $tareaCompleta) {
               if (str_contains($tareaCompleta, '|')) {
                    list($tareaId, $estadoId) = explode('|', $tareaCompleta);
                    MateriaTareaCulminada::create([
                        'materia_id' => $materia->id,
                        'tarea_consolidacion_id' => $tareaId,
                        'estado_tarea_consolidacion_id' => $estadoId,
                        'indice' => $indice++
                    ]);
               }
            }
        }
    }


    public function gestionar(Materia $materia)
    {
        $configuracion = Configuracion::find(1);
        $escuela = Escuela::find($materia->escuela->id);

        $pasoInicio = $materia->pasosCrecimiento()->wherePivot('al_iniciar', true)->first();
        $pasoFin = $materia->pasosCrecimiento()->wherePivot('al_iniciar', false)->first();

        $pasosCrecimiento = $this->construirPasosCrecimiento();

        $materiasEscuela = $escuela->materias;

        return view('contenido.paginas.escuelas.materias.gestionar-materia', [
            'materia' => $materia,
            'configuracion' => $configuracion,
            'escuela' => $materia->escuela,
            'pasosCrecimiento' => $pasosCrecimiento,
            'pasoInicioSeleccionado' => $pasoInicio ? $pasoInicio->id . '|' . ($pasoInicio->pivot->estado_paso_crecimiento_usuario_id ?? $pasoInicio->pivot->estado) : null,
            'pasoFinSeleccionado' => $pasoFin ? $pasoFin->id . '|' . ($pasoFin->pivot->estado_paso_crecimiento_usuario_id ?? $pasoFin->pivot->estado) : null,
            'materiasEscuela' => $materiasEscuela,
            'tipoUsuariosObjetivo' => TipoUsuario::all()
        ])->with('moduloEscuelas', true);
    }

    //bloque nuevo
    public function horarios(Materia $materia)
    {
        return view('contenido.paginas.escuelas.materias.gestionar-horarios-materia', [
            'materia' => $materia,
        ])->with('moduloEscuelas', true);
    }


    public function modelo(Materia $materia)
    {
        return view('contenido.paginas.escuelas.materias.gestionar-modelo-materia', [
            'materia' => $materia,
        ])->with('moduloEscuelas', true);
    }



    // Método actualizar modificado
    public function actualizar(Materia $materia, Request $request)
    {
        $configuracion = Configuracion::find(1); // Mover si no se usa directamente aquí

        $rules = [
            'nombre' => 'required|string|max:100',
            'descripción' => 'required|string',
            'nivel_id' => 'nullable|integer',

            // --- INICIO DE VALIDACIONES NUEVAS Y MODIFICADAS ---
            'limiteReportes' => [
                'nullable',
                'integer',
                'min:1',
                'required_with:asistenciasMinimas,cantidadInasistencias',
            ],
            'asistenciasMinimas' => [
                'nullable',
                'integer',
                'min:1',
                'lte:limiteReportes',
            ],
            'cantidadInasistencias' => [
                'nullable',
                'integer',
                'min:1',
                'lte:limiteReportes',
            ],
            // --- FIN DE VALIDACIONES NUEVAS Y MODIFICADAS ---

            'paso_iniciar_id' => 'nullable|string',
            'paso_culminar_id' => 'nullable|string',
            'proceso_prerrequisito' => 'nullable|array',
            'proceso_prerrequisito.*' => 'string',
            'materias_prerrequisito' => 'nullable|array',
            'materias_prerrequisito.*' => 'string',

            // Considera añadir reglas para otros campos del formulario
        ];

        $messages = [
            'nombre.required' => 'El nombre de la materia es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'descripción.required' => 'La descripción es obligatoria.',
            'limiteReportes.required_with' => 'El campo Límite de reportes es obligatorio cuando se especifican asistencias mínimas o cantidad de inasistencias para alerta.',
            'limiteReportes.min' => 'El límite de reportes debe ser al menos 1.',
            'asistenciasMinimas.lte' => 'Las asistencias mínimas no pueden ser superiores al límite de reportes.',
            'asistenciasMinimas.min' => 'Las asistencias mínimas deben ser al menos 1.',
            'cantidadInasistencias.lte' => 'La cantidad de inasistencias para alerta no puede ser superior al límite de reportes.',
            'cantidadInasistencias.min' => 'La cantidad de inasistencias para alerta debe ser al menos 1.',
        ];

        // Validación de los campos
        $validatedData = $request->validate($rules, $messages);

        // Validación adicional para al menos un sistema habilitado (opcional aquí si ya está en el JS y no quieres doble validación server-side para esto)
        if (!$request->has('habilitarCalificaciones') && !$request->has('habilitarAsistencias')) {
            return redirect()->back()
                ->withErrors(['general' => 'Debe habilitar al menos Calificaciones o Asistencias.'])
                ->withInput();
        }

        // Actualizar campos básicos
        $materia->nombre = $validatedData['nombre'];
        $materia->descripcion = $validatedData['descripción'] ?? $validatedData['descripcion']; // Ajusta según el nombre real
        $materia->nivel_id = $validatedData['nivel_id'] ?? null;

        $materia->limite_reporte_asistencias = $validatedData['limiteReportes'] ?? null;
        $materia->asistencias_minimas = $validatedData['asistenciasMinimas'] ?? null;
        $materia->asistencias_minima_alerta = $validatedData['cantidadInasistencias'] ?? null;
        $materia->tipo_usuario_objetivo_id = $request->tipoUsuarioObjetivo;

        // Toggles
        $materia->tiene_dia_limite = $request->has('diaLimiteHabilitado');
        $materia->dia_limite_reporte = $request->input('dia');

        $materia->habilitar_asistencias = $request->has('habilitarAsistencias');
        $materia->habilitar_inasistencias = $request->has('habilitarInasistencias');
        $materia->habilitar_calificaciones = $request->has('habilitarCalificaciones');
        $materia->habilitar_traslado = $request->has('habilitarTraslado');
        $materia->caracter_obligatorio = $request->has('obligatorio');


        if ($request->input('cantidadReportesSemana') != "") {
            $materia->cantidad_limite_reportes_semana = $request->input('cantidadReportesSemana');
        } else {
            $materia->cantidad_limite_reportes_semana = 0;
        }

        if ($request->diasPlazoReporte != "") {
            $materia->dias_plazo_reporte = $request->diasPlazoReporte;
        } else {
            $materia->dias_plazo_reporte = 0;
        }


        $materia->save();

        // Limpiar relaciones previas
        //$materia->pasosCrecimiento()->detach(); // YA NO: handled in guardarRelaciones specifically
        //$materia->prerrequisitosMaterias()->detach(); // handled in guardarRelaciones sync

        // $this->guardarProcesosPrerrequisito($materia, $request->proceso_prerrequisito); // COMENTADO: Gestionado por Livewire
        // Reguardar relaciones (pasos, prerrequisitos)
        $this->guardarRelaciones($materia, $request);


        // Actualizar portada (misma lógica que en guardar())
        if ($request->foto) {
            if ($configuracion->version == 1) {
                $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/materias/');
                !is_dir($path) && mkdir($path, 0777, true);

                $data = explode(';base64,', $request->foto)[1];
                file_put_contents(
                    $path . 'materia' . $materia->id . '.png',
                    base64_decode($data)
                );
                $materia->portada = 'materia' . $materia->id . '.png';
                $materia->save();
            }
        }

        return redirect()->route('materias.gestionar', $materia)
            ->with('success', 'Materia actualizada exitosamente');
    }

    private function actualizarRelaciones(Materia $materia, Request $request)
    {
        // Sincronizar pasos de crecimiento
        $materia->pasosCrecimiento()->detach();

        if ($request->paso_iniciar_id) {
            $materia->pasosCrecimiento()->attach($request->paso_iniciar_id, [
                'al_iniciar' => true,
                'estado' => 'En curso'
            ]);
        }

        if ($request->paso_culminar_id) {
            $materia->pasosCrecimiento()->attach($request->paso_culminar_id, [
                'al_iniciar' => false,
                'estado' => 'Finalizado'
            ]);
        }

        // Sincronizar prerrequisitos


        // Procesos prerrequisito
        $materia->prerrequisitosPasos()->detach();
        if ($request->proceso_prerrequisito) {
            foreach ($request->proceso_prerrequisito as $proceso) {
                if (str_contains($proceso, '|')) {
                    list($pasoId, $estadoRequerido) = explode('|', $proceso);
                    $materia->prerrequisitosPasos()->attach($pasoId, [
                        'estado_requerido' => $estadoRequerido
                    ]);
                }
            }
        }
    }

    private function guardarProcesosPrerrequisito(Materia $materia, $procesos)
    {
        $procesosData = [];

        foreach ((array)$procesos as $proceso) {
            if (str_contains($proceso, '|')) {
                list($pasoId, $estado) = explode('|', $proceso);
                $procesosData[$pasoId] = ['estado_proceso' => $estado];
            }
        }

        $materia->procesosPrerrequisito()->sync($procesosData);
    }

    public function actualizarMateriaRapido(Request $request, Materia $materia)
    {
        // Obtener la escuela para la validación unique
        $escuelaId = $materia->escuela_id;

        // Validar los datos recibidos del Offcanvas de edición
        $validatedData = $request->validateWithBag('materiaRapidaUpdate', [
            'nombre' => [
                'required',
                'string',
                'max:100',
                // Validar unicidad del nombre dentro de la escuela, ignorando la materia actual
                Rule::unique('materias')->where(fn($query) => $query->where('escuela_id', $escuelaId))->ignore($materia->id),
            ],
            'descripcion' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar la materia
            $materia->nombre = $validatedData['nombre'];
            $materia->descripcion = $validatedData['descripcion'] ?? null;
            $materia->save(); // Guardar los cambios

            DB::commit(); // Confirmar transacción

            // Redireccionar DE VUELTA a la página desde donde se editó
            // (probablemente la gestión del nivel al que pertenece)
            // Es importante tener el ID del nivel para esto.
            // Si la materia siempre tiene un nivel_id, podemos usarlo.
            if ($materia->nivel_id) {
                return redirect()->route('niveles.materias', $materia->nivel_id) // Asume ruta niveles.editar
                    ->with('success', 'Materia "' . $materia->nombre . '" actualizada exitosamente.');
            } else {
                // Si una materia podría no tener nivel (caso raro aquí), redirigir a otro lugar o atrás.
                return back()->with('success', 'Materia "' . $materia->nombre . '" actualizada exitosamente.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizando materia rápida {$materia->id}: " . $e->getMessage());

            // Redireccionar atrás con el error
            return back()->withInput()
                ->withErrors(['error_materia_rapida_update' => 'Ocurrió un error al actualizar la materia.'], 'materiaRapidaUpdate');
        }
    }
}
