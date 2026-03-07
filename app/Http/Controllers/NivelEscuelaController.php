<?php

namespace App\Http\Controllers;

use App\Models\Escuela;
use App\Models\Materia;
use App\Models\NivelEscuela;
use App\Models\PasoCrecimiento;
use App\Models\Configuracion; // Asegúrate de importar Configuracion
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use \stdClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use Illuminate\Http\Request;

class NivelEscuelaController extends Controller
{
    // --- Método listar (sin cambios respecto al anterior) ---
    public function listar(Escuela $escuela)
    {
        // Asumiendo que tienes la relación 'niveles' en el modelo Escuela
        $niveles = $escuela->niveles()->orderBy('nombre')->paginate(15);
        $configuracion=Configuracion::find(1);

        return view('contenido.paginas.escuelas.niveles.listar-niveles', [
            'escuela' => $escuela,
            'configuracion' => $configuracion,
            'niveles' => $niveles
        ])->with('moduloEscuelas', true);
    }

     // --- Método listar materias de un nivel) ---
     public function materias(NivelEscuela $nivel)
     {
         // Asumiendo que tienes la relación 'niveles' en el modelo Escuela
        $escuela=$nivel->escuela;
        $materias=$nivel->materias;
         $configuracion=Configuracion::find(1);
 
         return view('contenido.paginas.escuelas.niveles.crear-materia-nivel', [
             'escuela' => $escuela,
             'configuracion' => $configuracion,
             'nivel' => $nivel,
             'materias'=>$materias
         ])->with('moduloEscuelas', true);
     }

      public function crearMateria(Request $request, NivelEscuela $nivel)
    {
        // Obtener ID de la escuela desde el nivel
        $escuelaId = $nivel->escuela_id;

        // Validar los datos del formulario del Offcanvas
        // Usar un 'error bag' diferente ('materiaRapida') es opcional pero ayuda
        // si tienes otros formularios en la misma página.
        $validatedData = $request->validateWithBag('materiaRapida', [
            'nombre' =>  'required|string|max:100',
            'descripcion' => 'nullable|string',
             // No necesitamos validar nivel_id ni escuela_id aquí, ya los tenemos.
        ]);

        DB::beginTransaction();
        try {
            // Crear la nueva materia
            $materia = new Materia();
            $materia->nombre = $validatedData['nombre'];
            $materia->descripcion = $validatedData['descripcion'] ?? null; // Asignar descripción si existe
            $materia->nivel_id = $nivel->id; // Asociar al nivel actual
            $materia->escuela_id = $escuelaId; // Asociar a la escuela del nivel


            $materia->save(); // Guardar la nueva materia

            DB::commit(); // Confirmar transacción

             // Redireccionar DE VUELTA a la página de gestión del nivel
             // con un mensaje de éxito.
            return redirect()->route('niveles.materias', $nivel) // Asumiendo que 'niveles.editar' es la ruta de tu vista principal
                             ->with('success', 'Materia "' . $materia->nombre . '" creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack(); // Revertir en caso de error
            Log::error("Error guardando materia rápida para nivel {$nivel->id}: " . $e->getMessage());

            // Redireccionar atrás con el error (y los datos viejos del input)
            // Usando el 'error bag' para que los errores se muestren en el contexto correcto si es necesario
            return back()->withInput()
                         ->withErrors(['error_materia_rapida' => 'Ocurrió un error al guardar la materia.'], 'materiaRapida');
                         // ->withErrors($validator->errors(), 'materiaRapida'); // Si usaras Validator::make
        }
    }

    // --- Método crear (modificado para pasar $pasosCrecimiento con formato) ---
     public function crear(Escuela $escuela)
    {
        // Obtener la configuración (para rutas de storage, etc.)
        $configuracion = Configuracion::find(1); // O tu lógica para obtenerla

        // Obtener otros niveles para el selector de prerrequisitos
        $otrosNiveles = NivelEscuela::where('escuela_id', $escuela->id)
                                     ->orderBy('nombre')
                                     ->get();

        // Obtener y construir los Pasos de Crecimiento con el formato ID|Estado
        $pasosCrecimiento = $this->construirPasosCrecimiento(); // Usa el helper

        return view('contenido.paginas.escuelas.niveles.crear-nivel', [
            'escuela' => $escuela,
            'configuracion' => $configuracion, // Pasar configuración a la vista
            'otrosNiveles' => $otrosNiveles,
            'pasosCrecimiento' => $pasosCrecimiento // Pasar pasos con formato
        ])->with('moduloEscuelas', true);
    }

    // --- NUEVO: Helper para construir pasos (copiado y adaptado de MateriaController) ---
     private function construirPasosCrecimiento()
    {
        $pasos_crecimiento = PasoCrecimiento::orderBy('id', 'asc')->get();
        $contador_ids = 1; // Este contador parece innecesario si solo usas id_paso y estado
        $pasosFormateados = [];

        foreach ($pasos_crecimiento as $paso)
        {
            // Los estados podrían venir de una tabla o ser fijos (1=No Realizado, 2=En Curso, 3=Realizado)
            $estados = [1 => 'No Realizado', 2 => 'En Curso', 3 => 'Realizado'];

            foreach ($estados as $numEstado => $nombreEstado) {
                 $item = new stdClass();
                 // $item->id = $contador_ids++; // ID único de la opción (quizás no necesario)
                 $item->id_paso = $paso->id; // ID real del PasoCrecimiento
                 $item->nombre = $paso->nombre . ' - ' . $nombreEstado; // Nombre para mostrar
                 $item->estado = $numEstado; // Estado numérico
                 $pasosFormateados[] = $item;
            }
        }
        return $pasosFormateados;
    }


    // --- Método guardar (ACTUALIZADO con lógica de MateriaController) ---
    public function guardar(Request $request, Escuela $escuela)
    {
        $configuracion = Configuracion::find(1); // Necesario para guardar imagen

        // Validación de los datos recibidos (adaptada para nivel)
        $validatedData = $request->validate([
            'nombre' => 'required', 'string', 'max:255',
            'descripcion' => 'nullable|string',
            'foto' => 'nullable|string', // Validar como string (base64) o file si cambias el método
            'paso_iniciar_id' => 'nullable|string', // Espera formato ID|Estado
            'paso_culminar_id' => 'nullable|string',// Espera formato ID|Estado
            'prerrequisitos' => 'nullable|array', // Prerrequisitos de Nivel
            'prerrequisitos.*' => 'exists:niveles_escuelas,id', // Valida IDs de Nivel
            'procesos_prerrequisito' => 'nullable|array', // Procesos prerrequisito
            'procesos_prerrequisito.*' => 'string', // Espera formato ID|Estado
            'cantidadInasistencias'=>'numeric|min:1',             
            'asistenciasMinimas'=>'numeric|min:1'
            // Añadir validaciones para los switches si los implementaste en el nivel
            // 'habilitarAsistencias' => 'nullable',
            // 'asistenciasMinimas' => 'nullable|required_if:habilitarAsistencias,on|integer|min:1',
            // etc...
        ]);

        // --- Lógica de Switches (si aplica a niveles) ---
        // Ejemplo: Si tuvieras un switch 'requiere_aprobacion'
        // $requiereAprobacion = $request->has('requiere_aprobacion');


        // --- Inicio Transacción ---
        DB::beginTransaction();
        try {
            // Crear el NivelEscuela con datos básicos
            $nivel = new NivelEscuela();
            $nivel->nombre = $validatedData['nombre'];
            $nivel->descripcion = $validatedData['descripcion'] ?? null; // Usar el valor del input oculto
            $nivel->escuela_id = $escuela->id;
            $nivel->asistencias_minimas=$request->asistenciasMinimas;
            $nivel->asistencias_minima_alerta=$request->cantidadInasistencias;
            // Asigna otros campos validados si existen (ej. $nivel->requiere_aprobacion = $requiereAprobacion;)
            if($request->habilitarAsistencias == 'on')
            {
                $nivel->habilitar_asistencias=$request->habilitarAsistencias;
            }

            if($request->habilitarCalificaciones == 'on')
            {
                $nivel->habilitar_calificaciones=$request->habilitarCalificaciones;
            }
            if($request->habilitarInasistencias == 'on')
            {
                $nivel->habilitar_inasistencias=$request->habilitarInasistencias;
            }
            if($request->habilitarTraslado == 'on')
            {
                $nivel->habilitar_traslado=$request->habilitarTraslado;
            }
            if($request->obligatorio == 'on')
            {
                $nivel->caracter_obligatorio=$request->obligatorio;
            }
            $nivel->save(); // Guardar para obtener ID

            // Guardar Portada (lógica copiada de MateriaController)
            if ($request->filled('foto')) {
                 // Asumiendo versión 1 (storage local)
                 $path = storage_path('app/public/' . $configuracion->ruta_almacenamiento . '/img/niveles/'); // Usar storage_path y public link
                 if (!Storage::disk('public')->exists($configuracion->ruta_almacenamiento . '/img/niveles/')) {
                      Storage::disk('public')->makeDirectory($configuracion->ruta_almacenamiento . '/img/niveles/');
                 }

                 if (str_contains($request->foto, ';base64,')) {
                     @list($type, $file_data) = explode(';', $request->foto);
                     @list(, $file_data) = explode(',', $file_data);
                     $imageData = base64_decode($file_data);

                     $nombreFoto = 'nivel_' . $nivel->id . '_' . time() . '.png'; // Nombre único
                     $imagePath = $configuracion->ruta_almacenamiento . '/img/niveles/' . $nombreFoto;

                     Storage::disk('public')->put($imagePath, $imageData);

                     $nivel->portada = $nombreFoto; // Guardar solo el nombre del archivo
                     $nivel->save();
                 }
            }

            // Guardar Relaciones (usando helpers adaptados)
            $this->guardarRelacionesNivel($nivel, $request);
            $this->guardarProcesosPrerrequisitoNivel($nivel, $request->input('procesos_prerrequisito'));


            // --- Confirmar Transacción ---
            DB::commit();

            return redirect()->route('niveles.editar', $nivel) // Redirigir a editar el nivel creado
                             ->with('success', 'Nivel creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error guardando nivel: " . $e->getMessage()); // Loguear el error
            return back()->withInput()->withErrors(['error' => 'Ocurrió un error al guardar el nivel. Detalles: ' . $e->getMessage()]);
        }
    }

     // --- NUEVO: Helper guardarRelacionesNivel (Adaptado de MateriaController) ---
     private function guardarRelacionesNivel(NivelEscuela $nivel, Request $request)
    {
        // Limpiar pasos de crecimiento existentes (detach es seguro en creación y actualización)
        $nivel->pasosCrecimiento()->detach();

        // Guardar paso al iniciar (usa helper procesarPasoNivel)
        if ($request->filled('paso_iniciar_id')) {
            $this->procesarPasoNivel($nivel, $request->input('paso_iniciar_id'), true);
        }

        // Guardar paso al culminar (usa helper procesarPasoNivel)
        if ($request->filled('paso_culminar_id')) {
            $this->procesarPasoNivel($nivel, $request->input('paso_culminar_id'), false);
        }

        // Sincronizar Niveles prerrequisito (usa sync directamente)
        // El input name en la vista es 'prerrequisitos[]'
        $nivel->prerrequisitos()->sync($request->input('prerrequisitos', []));
    }

    // --- NUEVO: Helper procesarPasoNivel (Adaptado de MateriaController) ---
    private function procesarPasoNivel(NivelEscuela $nivel, $pasoCompleto, $esInicio)
    {
        if (str_contains($pasoCompleto, '|')) {
            list($pasoId, $estado) = explode('|', $pasoCompleto);

            // Validar que $pasoId y $estado sean numéricos si es necesario
            if (is_numeric($pasoId) && is_numeric($estado)) {
                // Adjuntar a la tabla pivote 'nivel_paso_crecimiento'
                $nivel->pasosCrecimiento()->attach($pasoId, [
                    'estado' => $estado,        // Columna 'estado' en tabla pivote
                    'al_iniciar' => $esInicio   // Columna 'al_iniciar' en tabla pivote
                ]);
            } else {
                 // Opcional: Loguear un aviso si el formato no es válido
                 \Log::warning("Formato inválido para paso de crecimiento nivel {$nivel->id}: {$pasoCompleto}");
            }
        }
    }

     // --- NUEVO: Helper guardarProcesosPrerrequisitoNivel (Adaptado de MateriaController) ---
     private function guardarProcesosPrerrequisitoNivel(NivelEscuela $nivel, $procesos)
    {
        $procesosData = [];
    
            foreach ((array)$procesos as $proceso) {
                if (str_contains($proceso, '|')) {
                    list($pasoId, $estado) = explode('|', $proceso);
                    $procesosData[$pasoId] = ['estado_proceso' => $estado];
                }
            }

        // Sincronizar con la tabla pivote usando la relación 'procesosPrerrequisito'
        $nivel->procesosPrerrequisito()->sync($procesosData);
    }


    // --- Método editar (ACTUALIZADO para cargar datos necesarios y pasar a la vista) ---
    public function editar(NivelEscuela $nivel)
    {
        $configuracion = Configuracion::find(1);
        $nivel->load(['escuela', 'prerrequisitos', 'pasosCrecimiento', 'procesosPrerrequisito']);
        $escuela = $nivel->escuela;

        // Obtener otros niveles para el selector
        $otrosNiveles = NivelEscuela::where('escuela_id', $escuela->id)
                                     ->where('id', '!=', $nivel->id)
                                     ->orderBy('nombre')
                                     ->get();

        // Construir pasos con formato para los selects
        $pasosCrecimiento = $this->construirPasosCrecimiento();

      

        // Preparar datos actuales para preseleccionar en la vista
        $prerrequisitosActuales = $nivel->prerrequisitos->pluck('id')->toArray();

     

        $pasoIniciarActual = $nivel->pasosCrecimiento()->wherePivot('al_iniciar', true)->first();
        $pasoCulminarActual = $nivel->pasosCrecimiento()->wherePivot('al_iniciar', false)->first();
        $pasoIniciarSeleccionado = $pasoIniciarActual ? $pasoIniciarActual->id . '|' . $pasoIniciarActual->pivot->estado : null;
        $pasoCulminarSeleccionado = $pasoCulminarActual ? $pasoCulminarActual->id . '|' . $pasoCulminarActual->pivot->estado : null;

        $procesosPrerrequisitoActuales = $nivel->procesosPrerrequisito->map(function ($paso) {
            // Reconstruir el formato ID|Estado que espera el select
            return $paso->id . '|' . $paso->pivot->estado_proceso;
        })->toArray();

        


        return view('contenido.paginas.escuelas.niveles.gestionar-nivel', [ // Asegúrate que la vista se llame así
            'nivel' => $nivel,
            'escuela' => $escuela,
            'configuracion' => $configuracion,
            'otrosNiveles' => $otrosNiveles,
            'pasosCrecimiento' => $pasosCrecimiento,
            'pasoIniciarActual'=>$pasoIniciarActual,
            'pasoCulminarActual'=>$pasoCulminarActual,
            'pasoIniciarSeleccionado'=>$pasoIniciarSeleccionado,
            'pasoCulminarSeleccionado'=> $pasoCulminarSeleccionado,
            'prerrequisitosActuales'=>$prerrequisitosActuales,
            'procesosPrerrequisitoActuales' => $procesosPrerrequisitoActuales, // Pasar valores ID|Estado
        ])->with('moduloEscuelas', true);
    }


    // --- Método actualizar (Añadir lógica similar a guardar para relaciones) ---
    public function actualizar(Request $request, NivelEscuela $nivel)
    {
         $configuracion = Configuracion::find(1);

         // Validación similar a guardar, ignorando el ID actual para unique
          $validatedData = $request->validate([
            'nombre' => 'required',
            'descripcion' => 'nullable|string',
            'foto' => 'nullable|string',
            'paso_iniciar_id' => 'nullable|string',
            'paso_culminar_id' => 'nullable|string',
            'prerrequisitos' => 'nullable|array',
            'prerrequisitos.*' => 'exists:niveles_escuelas,id',
            'procesos_prerrequisito' => 'nullable|array',
            'procesos_prerrequisito.*' => 'string',
             // Validaciones de switches si aplican...
        ]);

        DB::beginTransaction();
        try {
            // Actualizar campos básicos
            $nivel->nombre = $validatedData['nombre'];
            $nivel->descripcion = $validatedData['descripcion'] ?? null;
            // Actualizar otros campos...
            $nivel->asistencias_minimas=$request->asistenciasMinimas;
            $nivel->asistencias_minima_alerta=$request->cantidadInasistencias;
            // Asigna otros campos validados si existen (ej. $nivel->requiere_aprobacion = $requiereAprobacion;)
            if($request->habilitarAsistencias == 'on')
            {
                $nivel->habilitar_asistencias=$request->habilitarAsistencias;
            }

            if($request->habilitarCalificaciones == 'on')
            {
                $nivel->habilitar_calificaciones=$request->habilitarCalificaciones;
            }
            if($request->habilitarInasistencias == 'on')
            {
                $nivel->habilitar_inasistencias=$request->habilitarInasistencias;
            }
            if($request->habilitarTraslado == 'on')
            {
                $nivel->habilitar_traslado=$request->habilitarTraslado;
            }
            if($request->obligatorio == 'on')
            {
                $nivel->caracter_obligatorio=$request->obligatorio;
            }
            $nivel->save();

             // Actualizar Portada (misma lógica que en guardar)
             if ($request->filled('foto')) {
                 // ... (copiar/adaptar bloque de guardado de imagen de guardar()) ...
                 // Asegúrate de usar $nivel->id y la ruta correcta para niveles
                  if (str_contains($request->foto, ';base64,')) {
                     @list($type, $file_data) = explode(';', $request->foto);
                     @list(, $file_data) = explode(',', $file_data);
                     $imageData = base64_decode($file_data);
                     $nombreFoto = 'nivel_' . $nivel->id . '_' . time() . '.png';
                     $imagePath = $configuracion->ruta_almacenamiento . '/img/niveles/' . $nombreFoto;
                     Storage::disk('public')->put($imagePath, $imageData);
                     // Opcional: Eliminar foto anterior si existe y tiene un nombre diferente
                     // if ($nivel->portada && Storage::disk('public')->exists($configuracion->ruta_almacenamiento . '/img/niveles/' . $nivel->portada)) {
                     //     Storage::disk('public')->delete($configuracion->ruta_almacenamiento . '/img/niveles/' . $nivel->portada);
                     // }
                     $nivel->portada = $nombreFoto;
                     $nivel->save();
                 }
            }
          
            // Sincronizar/Actualizar Relaciones (usando los mismos helpers)
            // detach/attach para pasos y sync para prerrequisitos es seguro para actualizar
            $this->guardarRelacionesNivel($nivel, $request); // Usa detach + attach para pasos, sync para prerrequisitos
            $this->guardarProcesosPrerrequisitoNivel($nivel, $request->procesos_prerrequisito); // Usa sync

            DB::commit();

            return redirect()->route('niveles.editar', $nivel)
                             ->with('success', 'Nivel actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error actualizando nivel {$nivel->id}: " . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Ocurrió un error al actualizar el nivel. Detalles: ' . $e->getMessage()]);
        }
    }

    public function horariosMateria(Materia $materia)
    {
         // Asumiendo que tienes la relación 'niveles' en el modelo Escuela
         $nivel=$materia->nivel;
        $escuela=$nivel->escuela;

         $configuracion=Configuracion::find(1);
 
         return view('contenido.paginas.escuelas.niveles.horarios-materia-nivel', [
             'escuela' => $escuela,
             'configuracion' => $configuracion,
             'nivel' => $nivel,
             'materia'=>$materia
         ])->with('moduloEscuelas', true);
    }


    // --- Método eliminar (sin cambios respecto al anterior) ---
    public function eliminar(NivelEscuela $nivel)
    {
        // Opcional: Verificar dependencias (materias, etc.)
        if ($nivel->materias()->exists()) {
             return back()->withErrors(['error' => 'No se puede eliminar el nivel porque tiene materias asociadas.']);
        }

        try {
            $escuela_id = $nivel->escuela_id;
            // Opcional: Eliminar portada si existe
            // if ($nivel->portada) { ... Storage::disk('public')->delete(...); }
            $nivel->delete();

            return redirect()->route('escuelas.niveles.listar', $escuela_id)
                             ->with('success', 'Nivel eliminado exitosamente.');

        } catch (\Exception $e) {
             \Log::error("Error eliminando nivel {$nivel->id}: " . $e->getMessage());
            return back()->withErrors(['error' => 'Ocurrió un error al eliminar el nivel.']);
        }
    }
}