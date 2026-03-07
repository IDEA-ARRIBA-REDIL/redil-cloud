<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection; // ¡Asegúrate de importar Collection!
use Carbon\Carbon;                // ¡Asegúrate de importar Carbon!


class Actividad extends Model
{
  use HasFactory;
  protected $table = 'actividades';
  protected $guarded = [];





  public function validarAsistenciaActividad($usuarioId, $actividad)
  {

    $usuario = User::find($usuarioId); // Asumo que tienes un modelo User

    if (!$actividad) {
      return false; // La actividad no existe
    }

    $actividad = Actividad::find($actividad);

    // Validar género
    if ($this->genero !== 3) { // 3 significa "ambos"
      if (($this->genero === 1 && $usuario->genero !== 0) || ($this->genero === 2 && $usuario->genero !== 1)) {
        return false; // El género del usuario no está permitido
      }
    }

    // Validar tipo de usuario
    if ($this->tipoUsuarios()->count() > 0) {
      $tiposUsuariosValidos = $this->tipoUsuarios->pluck('id')->toArray();
      if (!in_array($usuario->tipo_usuario_id, $tiposUsuariosValidos)) {
        return false; // El usuario no tiene el tipo de usuario permitido
      }
    }

    // Validar rango de edad
    if ($this->rangosEdad()->count() > 0) {
      $edadUsuario = Carbon::parse($usuario->fecha_nacimiento)->age;
      $rangosEdadValidos = $this->rangosEdad->pluck('edad_minima', 'edad_maxima')->toArray();
      $edadValida = false;
      foreach ($rangosEdadValidos as $minima => $maxima) {
        if ($edadUsuario >= $minima && $edadUsuario <= $maxima) {
          $edadValida = true;
          break;
        }
      }
      if (!$edadValida) {
        return false; // La edad del usuario no está dentro de los rangos permitidos
      }
    }


    // Validar procesos requisito
    if ($this->procesosRequisito()->count() > 0) {
      $procesosRequisito = $this->procesosRequisito;
      foreach ($procesosRequisito as $requisito) {
        $pasoCrecimiento = $usuario->pasosCrecimiento()->where('paso_crecimiento_id', $requisito->id)->first();
        $estadoReq = $requisito->pivot->estado_paso_crecimiento_usuario_id ?? $requisito->pivot->estado;
        if (!$pasoCrecimiento || $pasoCrecimiento->pivot->estado_id != $estadoReq) {
          return false; // El usuario no cumple con el proceso requisito
        }
      }
    }

    // Validar tareas requisito (NUEVO)
    if (!$this->_validarTareasRequisito($usuario, $this->tareasRequisito)) {
      return false;
    }

    // Validar vinculacion_grupo
    if ($this->vinculacion_grupo) {
      // Obtener los grupos del usuario
      $gruposUsuario = $usuario->grupos;

      // Verificar si el usuario tiene al menos un grupo
      if ($gruposUsuario && $gruposUsuario->isEmpty()) { // Verificar si $gruposUsuario no es nulo
        $puedeAsistir = false; // El usuario no tiene un grupo al que asiste
      }
    }

    //validar actividad_grupo
    if ($this->actividad_grupo) {
      if (!$usuario->estadoActividadGrupos()) {
        $puedeAsistir = false; // El usuario no tiene actividad en el grupo
      }
    }

    // Validar sedes
    if ($this->sedes()->count() > 0) {
      $sedesValidas = $this->sedes->pluck('id')->toArray();
      if (!in_array($usuario->sede_id, $sedesValidas)) {
        return false; // El usuario no pertenece a una sede válida para la actividad
      }
    }
    // Validar estados civiles
    if ($this->estadosCiviles()->count() > 0) {
      $estadosCivilesValidos = $this->estadosCiviles->pluck('id')->toArray();
      if (!in_array($usuario->estado_civil_id, $estadosCivilesValidos)) {
        return false; // El usuario no tiene el estado civil permitido
      }
    }

    if ($this->tipoServicios()->count() > 0) {
      $serviciosGrupoValidos = $this->tipoServicios->pluck('id')->toArray();
      $seviciosUsuario = $usuario->serviciosPrestadosEnGrupos()->pluck('id')->toArray();
      $interseccion = array_intersect($seviciosUsuario, $serviciosGrupoValidos);
      if (empty($interseccion)) {
        return false;
      }
    }


    return true; // El usuario cumple con todas las restricciones
  }

  public function verificarDisponibilidadCategorias(int $usuarioId): array
  {
    // Optimización N+1: Cargar usuario con sus pasos de crecimiento
    $usuario = User::with('pasosCrecimiento')->find($usuarioId);
    if (!$usuario) {
      return ['success' => false, 'message' => 'Usuario no encontrado.'];
    }

    // Optimización N+1: Cargar todas las categorías con sus relaciones necesarias en una sola consulta
    $categorias = $this->categorias()->with([
      'tipoUsuarios',
      'rangosEdad',
      'sedes',
      'procesosRequisito'
    ])->get();

    $categoriasDisponibles = collect();
    $primerErrorEncontrado = null;

    // Optimización N+1: Indexar los pasos del usuario para búsqueda rápida en memoria
    $pasosCrecimientoUsuario = $usuario->pasosCrecimiento->keyBy('id');

    foreach ($categorias as $categoria) {
      $motivoFallo = null;

      // 1. Validar Género
      if ($categoria->genero !== 3 && $categoria->genero !== $usuario->genero) {
        $motivoFallo = "No cumples con el requisito de género para la categoría '{$categoria->nombre}'.";
      }

      // 2. Validar Tipo de Usuario (Validación en memoria)
      if (!$motivoFallo && $categoria->tipoUsuarios->isNotEmpty()) {
        if (!$categoria->tipoUsuarios->contains('id', $usuario->tipo_usuario_id)) {
          $motivoFallo = "Tu tipo de usuario no tiene permitido el acceso a la categoría '{$categoria->nombre}'.";
        }
      }

      // 3. Validar Rango de Edad (Validación en memoria)
      if (!$motivoFallo && $categoria->rangosEdad->isNotEmpty()) {
        $edadUsuario = Carbon::parse($usuario->fecha_nacimiento)->age;
        $edadValida = $categoria->rangosEdad->contains(function ($rango) use ($edadUsuario) {
          return $edadUsuario >= $rango->edad_minima && $edadUsuario <= $rango->edad_maxima;
        });
        
        if (!$edadValida) {
          $motivoFallo = "Tu edad no está dentro del rango permitido para la categoría '{$categoria->nombre}'.";
        }
      }

      // 4. Validar Sedes (Validación en memoria)
      if (!$motivoFallo && $categoria->sedes->isNotEmpty()) {
        if (!$categoria->sedes->contains('id', $usuario->sede_id)) {
          $motivoFallo = "No perteneces a una sede válida para la categoría '{$categoria->nombre}'.";
        }
      }

      // 5. Validar Pasos de Crecimiento Requeridos (Validación en memoria)
      if (!$motivoFallo && $categoria->procesosRequisito->isNotEmpty()) {
        foreach ($categoria->procesosRequisito as $requisito) {
          // Buscar en la colección precargada del usuario
          $pasoUsuario = $pasosCrecimientoUsuario->get($requisito->id);
          $estadoReq = $requisito->pivot->estado_paso_crecimiento_usuario_id ?? $requisito->pivot->estado;
          
          if (!$pasoUsuario || $pasoUsuario->pivot->estado_id != $estadoReq) {
            $motivoFallo = "No cumples con el requisito de proceso '" . $requisito->nombre . "' para la categoría '{$categoria->nombre}'.";
            break; // Salir del bucle de requisitos, ya encontramos un fallo.
          }
        }
      }

      // 6. Validar Tareas de Consolidación (NUEVO)
      if (!$motivoFallo) {
        $tareas = $this->restriccion_por_categoria ? $categoria->tareasRequisito : $this->tareasRequisito;
        $motivosTareas = [];
        if (!$this->_validarTareasRequisito($usuario, $tareas, $motivosTareas)) {
          $motivoFallo = implode(". ", $motivosTareas) . " para la categoría '{$categoria->nombre}'.";
        }
      }

      // ... (Puedes añadir más validaciones aquí como Estado Civil, etc.)

      // Si hubo un fallo, lo registramos y pasamos a la siguiente categoría
      if ($motivoFallo) {
        if (is_null($primerErrorEncontrado)) {
          $primerErrorEncontrado = $motivoFallo;
        }
        continue;
      }

      // Si la categoría pasó todas las validaciones, la añadimos a la lista
      $categoriasDisponibles->push($categoria);
    }

    // Al final, se decide qué retornar
    if ($categoriasDisponibles->isNotEmpty()) {
      // Si al menos una categoría está disponible, la operación es un éxito.
      return ['success' => true, 'categorias' => $categoriasDisponibles, 'message' => null];
    } else {
      // Si ninguna categoría está disponible, se devuelve el primer error encontrado.
      return [
        'success' => false,
        'categorias' => null,
        'message' => $primerErrorEncontrado ?? 'No cumples con los requisitos para acceder a ninguna de las categorías de esta actividad.'
      ];
    }
  }


  /**
   * MÉTODO NUEVO 3: Valida prerrequisitos académicos después de que un período ha cerrado.
   *
   * Este método se usa para matrículas "normales" o "extraordinarias".
   * Comprueba si un usuario YA APROBÓ las materias y procesos requeridos,
   * consultando la tabla de resultados finales (`materias_aprobada_usuario`).
   *
   * @param User $usuario El objeto del usuario que se matricula.
   * @param ActividadCategoria $categoria La categoría (que representa la materia) a la que se quiere inscribir.
   * @return array ['success' => bool, 'message' => ?string]
   */
  public function validarMatriculaPostPeriodo(User $usuario, ActividadCategoria $categoria): array
  {
    $materiaPeriodo = $categoria->materiaPeriodo()->with('materia.prerrequisitosMaterias', 'materia.procesosPrerrequisito')->first();
    if (!$materiaPeriodo) {
      return ['success' => false, 'message' => 'La categoría no está vinculada a una materia válida.'];
    }

    $materiaObjetivo = $materiaPeriodo->materia;

    // 1. Validar prerrequisitos de MATERIAS APROBADAS
    foreach ($materiaObjetivo->prerrequisitosMaterias as $prerrequisito) {
      $aprobada = MateriaAprobadaUsuario::where('user_id', $usuario->id)
        ->where('materia_id', $prerrequisito->id)
        ->exists();
      if (!$aprobada) {
        return ['success' => false, 'message' => "Para inscribir '{$materiaObjetivo->nombre}', necesitas haber aprobado '{$prerrequisito->nombre}'."];
      }
    }

    // 2. Validar prerrequisitos de PROCESOS DE CRECIMIENTO
    foreach ($materiaObjetivo->procesosPrerrequisito as $procesoReq) {
      $estadoRequerido = $procesoReq->pivot->estado_paso_crecimiento_usuario_id ?? $procesoReq->pivot->estado_proceso;
      $pasoUsuario = $usuario->pasosCrecimiento()->where('paso_crecimiento_id', $procesoReq->id)->first();

      if (!$pasoUsuario || $pasoUsuario->pivot->estado_id != $estadoRequerido) {
        return ['success' => false, 'message' => "Necesitas haber completado el proceso '{$procesoReq->nombre}' para inscribir '{$materiaObjetivo->nombre}'."];
      }
    }

    // 3. Validar prerrequisitos de TAREAS DE CONSOLIDACIÓN (NUEVO)
    $motivosTareas = [];
    if (!$this->_validarTareasRequisito($usuario, $materiaObjetivo->tareasRequisito, $motivosTareas)) {
      return ['success' => false, 'message' => implode(". ", $motivosTareas)];
    }

    return ['success' => true, 'message' => 'Cumple con todos los prerrequisitos finales.'];
  }

  /**
   * MÉTODO NUEVO 4: Valida prerrequisitos académicos durante un período activo (Pre-Matrícula).
   *
   * LÓGICA CORREGIDA: Ahora busca la nota mínima para aprobar en el sistema de
   * calificaciones del período correspondiente, en lugar de en la materia.
   *
   * @param User $usuario El objeto del usuario que se matricula.
   * @param ActividadCategoria $categoria La categoría (que representa la materia) a la que se quiere inscribir.
   * @return array ['success' => bool, 'message' => ?string]
   */
  public function validarPreMatriculaEnProgreso(User $usuario, ActividadCategoria $categoria): array
  {
    $materiaPeriodo = $categoria->materiaPeriodo()->with('materia.prerrequisitosMaterias')->first();
    if (!$materiaPeriodo) {
      return ['success' => false, 'message' => 'La categoría no está vinculada a una materia válida.'];
    }

    $materiaObjetivo = $materiaPeriodo->materia;

    foreach ($materiaObjetivo->prerrequisitosMaterias as $prerrequisito) {
      // Buscar la matrícula activa del usuario en la materia prerrequisito
      $matriculaActiva = Matricula::where('user_id', $usuario->id)
        ->with(['periodo.sistemaCalificaciones.calificaciones'])
        ->whereHas('horarioMateriaPeriodo.materiaPeriodo', function ($query) use ($prerrequisito) {
          $query->where('materia_id', $prerrequisito->id)
            ->whereHas('periodo', fn($q) => $q->where('estado', true));
        })->first();

      if (!$matriculaActiva) {
        return ['success' => false, 'message' => "Debes estar cursando activamente '{$prerrequisito->nombre}' o haberla aprobado para inscribir '{$materiaObjetivo->nombre}'."];
      }

      // 1. Obtener el sistema de calificaciones del período del prerrequisito
      $periodoPrerrequisito = $matriculaActiva->periodo;
      if (!$periodoPrerrequisito || !$periodoPrerrequisito->sistemaCalificaciones) {
        return ['success' => false, 'message' => "No se ha configurado un sistema de calificaciones para el período de '{$prerrequisito->nombre}'."];
      }

      $sistema = $periodoPrerrequisito->sistemaCalificaciones;

      // 2. Buscar el umbral mínimo para aprobar (la calificación 'aprobado' con la nota mínima más baja)
      $calificacionAprobatoria = $sistema->calificaciones
        ->where('aprobado', true)
        ->sortBy('nota_minima')
        ->first();

      if (!$calificacionAprobatoria) {
        return ['success' => false, 'message' => "No se ha definido una calificación de 'aprobado' en el sistema '{$sistema->nombre}'."];
      }

      $notaMinimaParaAprobar = (float)$calificacionAprobatoria->nota_minima;

      // 3. Calcular progreso actual
      $notaActual = $this->_calcularNotaActualPonderada($matriculaActiva);
      $asistenciasActuales = $this->_contarAsistenciasActuales($matriculaActiva);

      // 4. Validar contra los requerimientos del prerrequisito
      $motivosBloqueo = [];
      
      if ($notaActual < $notaMinimaParaAprobar) {
        $motivosBloqueo[] = "Nota insuficiente: {$notaActual} (mínimo {$notaMinimaParaAprobar} req.)";
      }

      if ($asistenciasActuales < $prerrequisito->asistencias_minimas) {
        $motivosBloqueo[] = "Asistencias insuficientes: {$asistenciasActuales} (mínimo {$prerrequisito->asistencias_minimas} req.)";
      }

      if (!empty($motivosBloqueo)) {
        return [
          'success' => false, 
          'message' => "Tu progreso en " . $prerrequisito->nombre . " no es suficiente: " . implode(" y ", $motivosBloqueo) . "."
        ];
      }
    }

    // 5. Validar prerrequisitos de PROCESOS DE CRECIMIENTO de la materia objetivo (NUEVO)
    foreach ($materiaObjetivo->procesosPrerrequisito as $procesoReq) {
      $estadoRequerido = $procesoReq->pivot->estado_paso_crecimiento_usuario_id ?? $procesoReq->pivot->estado_proceso;
      $pasoUsuario = $usuario->pasosCrecimiento()->where('paso_crecimiento_id', $procesoReq->id)->first();

      if (!$pasoUsuario || $pasoUsuario->pivot->estado_id != $estadoRequerido) {
        return ['success' => false, 'message' => "Necesitas haber completado el proceso '{$procesoReq->nombre}' para inscribir '{$materiaObjetivo->nombre}'."];
      }
    }

    // 6. Validar prerrequisitos de TAREAS DE CONSOLIDACIÓN de la materia objetivo (NUEVO)
    $motivosTareas = [];
    if (!$this->_validarTareasRequisito($usuario, $materiaObjetivo->tareasRequisito, $motivosTareas)) {
      return ['success' => false, 'message' => implode(". ", $motivosTareas)];
    }

    return ['success' => true, 'message' => 'Cumple con el progreso mínimo para la pre-matrícula.'];
  }

  /**
   * (HELPER PRIVADO) Calcula la nota ponderada actual de un estudiante en una materia.
   */
  private function _calcularNotaActualPonderada(Matricula $matricula): float
  {
    $horarioId = $matricula->horario_materia_periodo_id;
    $usuarioId = $matricula->user_id;

    // 1. Obtener la Materia Periodo vinculada
    $materiaPeriodo = $matricula->horarioMateriaPeriodo->materiaPeriodo;
    if (!$materiaPeriodo) return 0.0;

    // 2. Obtener los cortes vinculados a esta materia-periodo
    $cortesMateria = CorteMateriaPeriodo::where('materia_periodo_id', $materiaPeriodo->id)->get();
    
    $sumaPonderadaFinal = 0;
    $pesoTotalEvaluadoFinal = 0;

    foreach ($cortesMateria as $corteMateria) {
      // 3. Obtener los ítems de evaluación para este corte y el horario específico del alumno
      $items = ItemCorteMateriaPeriodo::where('corte_periodo_id', $corteMateria->corte_periodo_id)
        ->where('horario_materia_periodo_id', $horarioId)
        ->get();

      if ($items->isEmpty()) continue;

      $sumaPonderadaCorte = 0;
      $pesoTotalItemsCorte = 0;
      $hayNotasEnCorte = false;

      foreach ($items as $item) {
        // Encontrar la nota del alumno para este ítem
        $respuesta = AlumnoRespuestaItem::where('user_id', $usuarioId)
          ->where('item_corte_materia_periodo_id', $item->id)
          ->first();

        if ($respuesta && !is_null($respuesta->nota_obtenida)) {
          $sumaPonderadaCorte += ($respuesta->nota_obtenida * $item->porcentaje);
          $pesoTotalItemsCorte += $item->porcentaje;
          $hayNotasEnCorte = true;
        }
      }

      if ($hayNotasEnCorte && $pesoTotalItemsCorte > 0) {
        // Nota del corte normalizada (si el total de pesos de ítems no es 100% aún)
        $notaCorte = $sumaPonderadaCorte / $pesoTotalItemsCorte;
        
        // Sumar al cálculo global de la materia usando el peso del corte
        $sumaPonderadaFinal += ($notaCorte * $corteMateria->porcentaje);
        $pesoTotalEvaluadoFinal += $corteMateria->porcentaje;
      }
    }

    // 4. Retornar la nota final normalizada según lo evaluado hasta ahora
    if ($pesoTotalEvaluadoFinal > 0) {
      return round($sumaPonderadaFinal / $pesoTotalEvaluadoFinal, 2);
    }

    return 0.0;
  }

  /**
   * ¡MÉTODO DE VALIDACIÓN DE TAQUILLA (PDP) ACTUALIZADO!
   *
   * Este método revisa TODAS las categorías de una actividad de tipo Escuela
   * y devuelve un array estructurado con el estado de cada una.
   * Incorpora AMBAS lógicas: post-período y pre-matrícula.
   *
   * @param User $usuario El usuario (alumno) que está en la taquilla.
   * @return \Illuminate\Support\Collection
   */
  public function validarCategoriasEscuelaParaTaquilla(User $usuario): Collection
  {
    // 1. Precargamos todas las relaciones necesarias
    $categorias = $this->categorias()
      ->with([
        'materiaPeriodo.materia.prerrequisitosMaterias',
        'materiaPeriodo.materia.procesosPrerrequisito',
        'materiaPeriodo.horariosMateriaPeriodo.horarioBase', // ¡NUEVO! Cargar horarios y bases
        'sedes',
        'rangosEdad',
        'tipoUsuarios',
        'estadosCiviles',
        'procesosRequisito',
        'tareasRequisito.tareaConsolidacion',
        'tareasRequisito.estadoTarea'
      ])
      ->get();

    // 2. Obtenemos el "historial académico" del usuario UNA SOLA VEZ.
    $materiasAprobadasIds = $usuario->resultadosMaterias()
      ->where('aprobado', true)
      ->pluck('materia_id'); //

    // 3. Iteramos sobre cada categoría (materia) y la validamos
    $resultadoFinal = $categorias->map(function ($categoria) use ($usuario, $materiasAprobadasIds) {

      $estado = 'DISPONIBLE'; // Estado por defecto
      $motivosFallo = [];

      // Si la categoría no está ligada a una materia (error de config)
      if (!$categoria->materiaPeriodo || !$categoria->materiaPeriodo->materia) {
        return (object)[
          'categoria' => $categoria,
          'estado' => 'BLOQUEADA',
          'motivos' => ['Error: Categoría no vinculada a una materia.'],
        ];
      }

      // --- LÓGICA DE CUPOS (¡NUEVO!) ---
      // Calculamos los cupos reales basándonos en los horarios habilitados
      $horarios = $categoria->materiaPeriodo->horariosMateriaPeriodo->where('habilitado', true);
      $totalCapacidad = $horarios->sum(fn($h) => $h->horarioBase->capacidad ?? 0);
      $totalCuposDisponibles = $horarios->sum('cupos_disponibles');

      // Sobrescribimos los atributos de la categoría para la vista
      $categoria->aforo = $totalCapacidad;
      $categoria->aforo_ocupado = $totalCapacidad - $totalCuposDisponibles;
      // ----------------------------------

      $materiaActual = $categoria->materiaPeriodo->materia;

      // --- VALIDACIÓN 1: ¿YA LA APROBÓ? ---
      if ($materiasAprobadasIds->contains($materiaActual->id)) {
        return (object)[
          'categoria' => $categoria,
          'estado' => 'APROBADA',
          'motivos' => [],
        ];
      }

      // --- VALIDACIÓN 2: REGLAS GENERALES DE CATEGORÍA (Género, Sede, Edad...) ---
      // Esta lógica se basa en tu método 'verificarDisponibilidadCategorias'

      if ($categoria->genero && $categoria->genero !== 3 && $categoria->genero !== $usuario->genero) {
        $motivosFallo[] = 'No cumple el requisito de género';
      }

      if ($categoria->sedes()->exists() && !$categoria->sedes()->where('sede_id', $usuario->sede_id)->exists()) {
        $motivosFallo[] = 'No disponible para la sede del usuario';
      }

      if ($categoria->rangosEdad()->exists()) {
        $edadUsuario = Carbon::parse($usuario->fecha_nacimiento)->age;
        if (!$categoria->rangosEdad()->where('edad_minima', '<=', $edadUsuario)->where('edad_maxima', '>=', $edadUsuario)->exists()) {
          $motivosFallo[] = "Edad ($edadUsuario) fuera del rango permitido";
        }
      }

      // (Puedes añadir más validaciones generales aquí: tipoUsuarios, estadosCiviles...)
      if ($categoria->tipoUsuarios->isNotEmpty() && !$categoria->tipoUsuarios->contains('id', $usuario->tipo_usuario_id)) {
        $motivosFallo[] = "Tipo de usuario no permitido para esta categoría.";
      }

      if ($categoria->estadosCiviles->isNotEmpty() && !$categoria->estadosCiviles->contains('id', $usuario->estado_civil_id)) {
        $motivosFallo[] = "Estado civil no admitido para esta categoría.";
      }

      // Validar procesos requisito de la categoría
      if ($categoria->procesosRequisito->isNotEmpty()) {
        foreach ($categoria->procesosRequisito as $requisito) {
          $pasoUsuario = $usuario->pasosCrecimiento()->where('paso_crecimiento_id', $requisito->id)->first();
          $estadoReq = $requisito->pivot->estado_paso_crecimiento_usuario_id ?? $requisito->pivot->estado;
          if (!$pasoUsuario || $pasoUsuario->pivot->estado_id != $estadoReq) {
            $motivosFallo[] = "Pendiente: " . $requisito->nombre;
          }
        }
      }

      // Validar tareas de consolidación de la categoría
      $motivosTareasCat = [];
      if (!$this->_validarTareasRequisito($usuario, $categoria->tareasRequisito, $motivosTareasCat)) {
        $motivosFallo = array_merge($motivosFallo, $motivosTareasCat);
      }

      // --- VALIDACIÓN 3: REGLAS ACADÉMICAS (PRERREQUISITOS) ---
      // Usamos la lógica de tus métodos existentes.

      // Primero, intentamos la validación "Post Período" (la más estricta)
      $validacionPost = $this->validarMatriculaPostPeriodo($usuario, $categoria); //

      if ($validacionPost['success']) {
        // ¡Perfecto! Cumple todos los prerrequisitos (ya los aprobó)
        // No hacemos nada, el estado sigue 'DISPONIBLE'

      } else {
        // "Post Período" falló. Ahora intentamos la validación "En Progreso"
        // (Esta es la lógica que pediste añadir)

        $validacionProgreso = $this->validarPreMatriculaEnProgreso($usuario, $categoria); //

        if ($validacionProgreso['success']) {
          // ¡Genial! Cumple los requisitos "en progreso" (pre-matrícula)
          // No hacemos nada, el estado sigue 'DISPONIBLE'

        } else {
          // AMBAS validaciones fallaron. El usuario está bloqueado.
          // Usamos el mensaje de la validación 'Post' como el motivo principal.
          $motivosFallo[] = $validacionPost['message'] ?? 'No cumple los prerrequisitos académicos.';
        }

        // --- VALIDACIÓN DE TAREAS DE CONSOLIDACIÓN PARA LA MATERIA (NUEVO) ---
        $motivosTareasMateria = [];
        if (!$this->_validarTareasRequisito($usuario, $materiaActual->tareasRequisito, $motivosTareasMateria)) {
          $motivosFallo = array_merge($motivosFallo, $motivosTareasMateria);
        }
      }

      // 4. Devolvemos el objeto de estado para esta categoría
      if (count($motivosFallo) > 0) {
        $estado = 'BLOQUEADA';
      }

      return (object)[
        'categoria' => $categoria,
        'estado' => $estado,
        'motivos' => $motivosFallo,
      ];
    });

    return $resultadoFinal;
  }

  /**
 * MÉTODO REFACTOREADO: Valida categorías para el PERFIL PÚBLICO.
 * Soporta validación del usuario principal Y su círculo familiar.
 *
 * @param User $usuario
 * @return Collection
 */
public function validarCategoriasParaPerfil(User $usuario): Collection
{
  // Obtenemos los parientes del usuario para validar si alguno de ellos cumple
  $parientes = $usuario->parientesDelUsuario()->get();

  // Si es tipo escuela, reutilizamos la lógica existente que ya devuelve una colección detallada.
  if ($this->tipo->tipo_escuelas) {
    return $this->validarCategoriasEscuelaParaTaquilla($usuario);
  }

  // Pre-cargar relaciones de categorías
  $categorias = $this->categorias()->with([
    'tipoUsuarios',
    'rangosEdad',
    'sedes',
    'procesosRequisito',
    'monedas'
  ])->get();

  return $categorias->map(function ($categoria) use ($usuario, $parientes) {
    // 1. Validar al usuario principal
    $resUsuario = $this->validarUsuarioEnCategoria($usuario, $categoria);
    
    if ($resUsuario->estado === 'DISPONIBLE') {
      return $resUsuario;
    }

    // 2. Si el usuario no cumple, validamos a sus parientes
    $motivosAcumulados = [];
    $motivosAcumulados['Tú'] = $resUsuario->motivos;

    foreach ($parientes as $pariente) {
      $resPariente = $this->validarUsuarioEnCategoria($pariente, $categoria);
      if ($resPariente->estado === 'DISPONIBLE') {
        return $resPariente; // Con que uno cumpla, la categoría está disponible
      }
      $nombreP = $pariente->primer_nombre . ' ' . $pariente->primer_apellido;
      $motivosAcumulados[$nombreP] = $resPariente->motivos;
    }

    // 3. Si nadie cumplió, consolidamos los motivos agrupándolos por fallo
    $agrupados = [];
    foreach ($motivosAcumulados as $persona => $fallos) {
      foreach ($fallos as $fallo) {
        $agrupados[$fallo][] = $persona;
      }
    }

    $motivosFinales = [];
    foreach ($agrupados as $fallo => $personas) {
      $nombres = implode(', ', $personas);
      $motivosFinales[] = "[$nombres] $fallo";
    }

    return (object)[
      'categoria' => $categoria,
      'estado' => 'BLOQUEADA',
      'motivos' => $motivosFinales
    ];
  });
}

  /**
   * HELPER: Valida requisitos de un usuario específico para una categoría o actividad (si no hay restricción por categoría).
   */
  public function validarUsuarioEnCategoria(User $usuario, ActividadCategoria $categoria = null)
{
  $motivosFallo = [];
  $pasosCrecimientoUsuario = $usuario->pasosCrecimiento->keyBy('id');

  // Si no hay categoría, usamos las restricciones de la actividad misma
  $objetoRestriccion = ($this->restriccion_por_categoria && $categoria) ? $categoria : $this;

  // 1. Validar Género
  if ($objetoRestriccion->genero !== 3) {
    if (($objetoRestriccion->genero === 1 && $usuario->genero !== 0) || ($objetoRestriccion->genero === 2 && $usuario->genero !== 1)) {
      $motivosFallo[] = "No cumple con el requisito de género.";
    }
  }

  // 2. Validar Tipo de Usuario
  if ($objetoRestriccion->tipoUsuarios->isNotEmpty()) {
    if (!$objetoRestriccion->tipoUsuarios->contains('id', $usuario->tipo_usuario_id)) {
      $motivosFallo[] = "Tipo de usuario no permitido.";
    }
  }

  // 3. Validar Rango de Edad
  if ($objetoRestriccion->rangosEdad->isNotEmpty()) {
    if (!$usuario->fecha_nacimiento) {
        $motivosFallo[] = "Fecha de nacimiento no registrada.";
    } else {
        $edadUsuario = Carbon::parse($usuario->fecha_nacimiento)->age;
        $edadValida = $objetoRestriccion->rangosEdad->contains(function ($rango) use ($edadUsuario) {
          return $edadUsuario >= $rango->edad_minima && $edadUsuario <= $rango->edad_maxima;
        });
        if (!$edadValida) {
          $motivosFallo[] = "Edad ($edadUsuario años) fuera de rango.";
        }
    }
  }

  // 4. Validar Sedes
  if ($objetoRestriccion->sedes->isNotEmpty()) {
    if (!$objetoRestriccion->sedes->contains('id', $usuario->sede_id)) {
      $motivosFallo[] = "No pertenece a una sede válida.";
    }
  }

  // 5. Validar Pasos de Crecimiento
  if ($objetoRestriccion->procesosRequisito->isNotEmpty()) {
    foreach ($objetoRestriccion->procesosRequisito as $requisito) {
      $pasoUsuario = $pasosCrecimientoUsuario->get($requisito->id);
      $estadoRequerido = $requisito->pivot->estado_paso_crecimiento_usuario_id ?? $requisito->pivot->estado;
      if (!$pasoUsuario || $pasoUsuario->pivot->estado_id != $estadoRequerido) {
        $motivosFallo[] = "Pendiente: " . $requisito->nombre;
      }
    }
  }

  // 6. Validar Tareas de Consolidación
  $tareasRequisito = ($this->restriccion_por_categoria && $categoria) ? $categoria->tareasRequisito : $this->tareasRequisito;
  $this->_validarTareasRequisito($usuario, $tareasRequisito, $motivosFallo);

  // 7. Validar Estados Civiles
  if ($objetoRestriccion->estadosCiviles->isNotEmpty()) {
    if (!$objetoRestriccion->estadosCiviles->contains('id', $usuario->estado_civil_id)) {
      $motivosFallo[] = "Estado civil no admitido.";
    }
  }

  // 8. Validar Servicios
  if ($objetoRestriccion->tipoServicios->isNotEmpty()) {
    $serviciosUsuario = $usuario->serviciosPrestadosEnGrupos()->pluck('id')->toArray();
    $serviciosCategoria = $objetoRestriccion->tipoServicios->pluck('id')->toArray();
    if (empty(array_intersect($serviciosUsuario, $serviciosCategoria))) {
      $motivosFallo[] = "Servicios requeridos insuficientes.";
    }
  }

  return (object)[
    'categoria' => $categoria,
    'estado' => empty($motivosFallo) ? 'DISPONIBLE' : 'BLOQUEADA',
    'motivos' => $motivosFallo
  ];
}

/**
 * MÉTODO DINÁMICO (NUEVO): Decide automáticamente qué validación aplicar 
 * (por categoría o general) y retorna un reporte de acceso unificado.
 */
public function validarAccesoGlobal(User $usuario): array
{
    // Si la actividad es tipo escuela, delegamos a la lógica específica de escuela
    if ($this->tipo && $this->tipo->tipo_escuelas) {
        $resultado = $this->validarCategoriasEscuelaParaTaquilla($usuario);
        $disponibles = $resultado->where('estado', 'DISPONIBLE');
        return [
            'success' => $disponibles->isNotEmpty(),
            'message' => $disponibles->isEmpty() ? ($resultado->first()->motivos[0] ?? 'No cumples los prerrequisitos académicos.') : null,
            'categorias' => $disponibles,
            'tipo_restriccion' => 'escuela'
        ];
    }

    // Si es restricción por categoría normal
    if ($this->restriccion_por_categoria) {
        $resultado = $this->verificarDisponibilidadCategorias($usuario->id);
        return [
            'success' => $resultado['success'],
            'message' => $resultado['message'],
            'categorias' => $resultado['categorias'] ?? null,
            'tipo_restriccion' => 'categoria'
        ];
    }

    // Si es restricción general
    $resultado = $this->validarUsuarioEnCategoria($usuario, null);
    return [
        'success' => $resultado->estado === 'DISPONIBLE',
        'message' => implode(". ", $resultado->motivos),
        'categorias' => $this->categorias, // Todas disponibles si es general
        'tipo_restriccion' => 'general'
    ];
}

/**
 * MÉTODO ESTÁTICO (NUEVO): Filtra una lista de actividades y devuelve solo los IDs
 * de aquellas a las que el usuario puede asistir.
 */
public static function filtrarActividadesPermitidas(User $usuario, $actividades): array
{
    return collect($actividades)->filter(function ($actividad) use ($usuario) {
        $res = $actividad->validarAccesoGlobal($usuario);
        return $res['success'];
    })->pluck('id')->toArray();
}

public function validarCategoriasGeneralParaPerfil(User $usuario): Collection
{
    $parientes = $usuario->parientesDelUsuario()->get();
    
    // 1. Validar al usuario principal
    $resUsuario = $this->validarUsuarioEnCategoria($usuario);
    
    if ($resUsuario->estado === 'DISPONIBLE') {
        return collect([$resUsuario]);
    }

    // 2. Validar parientes
    $motivosAcumulados = [];
    $motivosAcumulados['Tú'] = $resUsuario->motivos;

    foreach ($parientes as $pariente) {
        $resPariente = $this->validarUsuarioEnCategoria($pariente);
        if ($resPariente->estado === 'DISPONIBLE') {
            return collect([$resPariente]);
        }
        $nombreP = $pariente->primer_nombre . ' ' . $pariente->primer_apellido;
        $motivosAcumulados[$nombreP] = $resPariente->motivos;
    }

    // 3. Consolidar motivos agrupándolos por fallo
    $agrupados = [];
    foreach ($motivosAcumulados as $persona => $fallos) {
        foreach ($fallos as $fallo) {
            $agrupados[$fallo][] = $persona;
        }
    }

    $motivosFinales = [];
    foreach ($agrupados as $fallo => $personas) {
        $nombres = implode(', ', $personas);
        $motivosFinales[] = "[$nombres] $fallo";
    }

    return collect([(object)[
        'actividad' => $this,
        'estado' => 'BLOQUEADA',
        'motivos' => $motivosFinales
    ]]);
  }

  /**
   * MÉTODO PÚBLICO: Valida solo las tareas requisito de la actividad para un usuario.
   */
  public function validarTareasRequisito(User $usuario): array
  {
    $motivosFallo = [];
    $tareas = $this->tareasRequisito;
    
    $cumple = $this->_validarTareasRequisito($usuario, $tareas, $motivosFallo);

    return [
        'estado' => $cumple ? 'DISPONIBLE' : 'BLOQUEADA',
        'motivo' => implode(', ', $motivosFallo)
    ];
  }

  private function _contarAsistenciasActuales(Matricula $matricula): int
  {
    $horarioId = $matricula->horario_materia_periodo_id;
    $usuarioId = $matricula->user_id;

    // Contar registros de asistencia donde el alumno asistió
    // y el reporte de clase pertenece al horario de la matrícula.
    return ReporteAsistenciaAlumnos::where('user_id', $usuarioId)
      ->where('asistio', true)
      ->whereHas('reporteClase', function ($query) use ($horarioId) {
        $query->where('horario_materia_periodo_id', $horarioId);
      })
      ->count();
  }

  public function categoriasDisponiblesParaUsuario($usuarioId)
  {
    $usuario = User::find($usuarioId);
    if (!$usuario) return collect();

    return $this->categorias->filter(function ($categoria) use ($usuario) {
        $resultado = $this->validarUsuarioEnCategoria($usuario, $categoria);
        return $resultado->estado === 'DISPONIBLE';
    });
  }
  public function tipo(): BelongsTo
  {
    return $this->belongsTo(TipoActividad::class, 'tipo_actividad_id');
  }

  public function tipoUsuarioObjetivo(): BelongsTo
  {
    return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_objetivo_id');
  }

  public function monedas(): BelongsToMany
  {
    return $this->belongsToMany(Moneda::class, 'actividad_monedas', 'actividad_id', 'moneda_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function tiposPago(): BelongsToMany
  {
    return $this->belongsToMany(TipoPago::class, 'actividad_tipos_pago', 'actividad_id', 'tipo_pago_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function sedes(): BelongsToMany
  {
    return $this->belongsToMany(Sede::class, 'actividad_sedes', 'actividad_id', 'sede_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function camposAdicionales(): BelongsToMany
  {
    return $this->belongsToMany(CamposAdicionalesActividad::class, 'actividad_campos_adicionales_actividad', 'actividad_id', 'campos_adicionales_actividad_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function rangosEdad(): BelongsToMany
  {
    return $this->belongsToMany(RangoEdad::class, 'actividad_rangos_edad', 'actividad_id', 'rango_edad_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }



  public function procesosRequisito(): BelongsToMany
  {
    return $this->belongsToMany(PasoCrecimiento::class, 'actividad_procesos_requisito', 'actividad_id', 'paso_crecimiento_id')
    ->using(ActividadProcesoRequisito::class)
    ->withPivot(
      'created_at',
      'updated_at',
      'estado', // Mantener por compatibilidad
      'estado_paso_crecimiento_usuario_id', // NUEVO: FK dinámico
      'indice'
    );
  }

  public function procesosCulminados(): BelongsToMany
  {
    return $this->belongsToMany(PasoCrecimiento::class, 'actividad_procesos_culminados', 'actividad_id', 'paso_crecimiento_id')
    ->using(ActividadProcesoCulminado::class)
    ->withPivot(
      'created_at',
      'updated_at',
      'estado', // Mantener por compatibilidad
      'estado_paso_crecimiento_usuario_id', // NUEVO: FK dinámico
      'indice'
    );
  }

  // ========== RELACIONES DE TAREAS DE CONSOLIDACIÓN ==========

  public function tareasRequisito(): HasMany
  {
    return $this->hasMany(ActividadTareaRequisito::class, 'actividad_id');
  }

  public function tareasCulminadas(): HasMany
  {
    return $this->hasMany(ActividadTareaCulminada::class, 'actividad_id');
  }

  public function tipoUsuarios()
  {
    return $this->belongsToMany(TipoUsuario::class, 'actividad_tipos_usuarios', 'actividad_id', 'tipo_usuario_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function estadosCiviles()
  {
    return $this->belongsToMany(EstadoCivil::class, 'actividad_estados_civiles', 'actividad_id', 'estado_civil_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function tipoServicios()
  {
    return $this->belongsToMany(TipoServicioGrupo::class, 'actividad_tipos_servicios_grupos', 'actividad_id', 'tipo_servicio_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function tags()
  {
    return $this->belongsToMany(TagGeneral::class, 'actividad_tags', 'actividad_id', 'tag_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }


  public function destinatarios()
  {
    return $this->belongsToMany(Destinatario::class, 'actividad_destinatarios', 'actividad_id', 'destinatario_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }
  public function inscripciones()
  {
    return $this->hasManyThrough(Inscripcion::class, ActividadCategoria::class);
  }

  public function periodo(): BelongsTo
  {
    return $this->belongsTo(Periodo::class, 'periodo_id');
  }



  public function categorias(): HasMany
  {
    return $this->hasMany(ActividadCategoria::class);
  }

  public function asistencias(): HasMany
  {
    return $this->hasMany(ActividadAsistencia::class);
  }

  public function elementos(): HasMany
  {
    return $this->hasMany(ElementoFormularioActividad::class);
  }

  public function banner(): HasOne
  {
    return $this->hasOne(ActividadBanner::class);
  }

  /**
   * Método público para validar cualquier colección de tareas (ej: de una materia).
   */
  public function validarTareasRequisitoCualquiera(User $usuario, $tareasRequisito, &$motivosFallo = null): bool
  {
    return $this->_validarTareasRequisito($usuario, $tareasRequisito, $motivosFallo);
  }

  /**
   * (HELPER PRIVADO) Centraliza la validación de tareas de consolidación requisito.
   */
  private function _validarTareasRequisito(User $usuario, $tareasRequisito, &$motivosFallo = null): bool
  {
    if ($tareasRequisito->isEmpty()) {
      return true;
    }

    $cumpleTodo = true;
    foreach ($tareasRequisito as $tareaReq) {
      // Buscar la asignación de esta tarea al usuario con el estado requerido
      $asignacionTarea = $usuario->tareasConsolidacion()
        ->wherePivot('tarea_consolidacion_id', $tareaReq->tarea_consolidacion_id)
        ->wherePivot('estado_tarea_consolidacion_id', $tareaReq->estado_tarea_consolidacion_id)
        ->first();

      if (!$asignacionTarea) {
        $cumpleTodo = false;
        if (is_array($motivosFallo)) {
          $motivosFallo[] = "Requiere tarea \"" . $tareaReq->tareaConsolidacion->nombre . 
                            "\" en estado \"" . $tareaReq->estadoTarea->nombre . "\"";
        }
      }
    }
    return $cumpleTodo;
  }
}
