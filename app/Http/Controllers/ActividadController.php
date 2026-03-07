<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Helpers\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Útil para depurar
use \stdClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use App\Models\Configuracion;
use App\Models\TipoActividad;
use App\Models\Actividad;
use App\Models\Inscripcion;
use App\Models\AbonoCategoria;
use App\Models\AbonoCategoriaMoneda;
use App\Models\Abono;
use App\Models\Moneda;
use App\Models\TipoPago;
use App\Models\Periodo;
use App\Models\Sede;
use App\Models\Compra;
use App\Models\Matricula;

use App\Models\TipoUsuario;
use App\Models\TipoServicioGrupo;
use App\Models\EstadoCivil;
use App\Models\User;
use App\Models\RangoEdad;
use App\Models\ActividadCategoria;
use App\Models\ActividadBanner;
use App\Models\ActividadEncargado;
use App\Models\ActividadCategoriaMoneda;
use App\Models\ActividadVideo;
use App\Models\PasoCrecimiento;





use App\Models\ElementoFormularioActividad;
use App\Models\TagGeneral;
use App\Models\TipoElementoFormularioActividad;
use App\Models\EstadoPasoCrecimientoUsuario;



use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class ActividadController extends Controller
{
  public function __construct()
  {
    // Aplicar middleware auth a todos los métodos excepto listado y perfil
    $this->middleware('auth')->except(['proximas', 'perfil', 'gestionarInscripciones', 'asistenciasActividad']);
  }

  /**
   * MÉTODO NUEVO: "Director" o "Índice" de Actividades.
   * Este método es el punto de entrada principal. Revisa los permisos del usuario
   * y llama al método correspondiente para mostrar el listado adecuado.
   */
  public function index(Request $request)
  {
    // Verificamos si el usuario está autenticado y si tiene el permiso específico.
    if (Auth::check() && Auth::user()->can('actividades.ver_todas_las_actividades')) {
      // Si es un administrador, lo enviamos al listado completo.
      return $this->listado($request);
    }

    // Para cualquier otro caso (invitados o usuarios sin permiso), lo enviamos al listado público.
    return $this->proximas($request);
  }

  /**
   * MÉTODO AUXILIAR PRIVADO:
   * Construye la consulta base de actividades con los filtros de búsqueda y tags.
   * Esto evita duplicar código en los métodos listado() y proximas().
   */
 private function _buildActividadesQuery(Request $request)
  {
    $hoy = now()->toDateString();

    $query = Actividad::query();

    // 1. Filtro base: Solo actividades activas
    $query->where('activa', true);

    // 2. Lógica de Vigencia (Fechas)
    // Fecha de visualización: Nula o menor/igual a hoy
    $query->where(function($q) use ($hoy) {
        $q->whereNull('fecha_visualizacion')
          ->orWhere('fecha_visualizacion', '<=', $hoy);
    });

    // Fecha de cierre: Nula o mayor/igual a hoy
    $query->where(function($q) use ($hoy) {
        $q->whereNull('fecha_cierre')
          ->orWhere('fecha_cierre', '>=', $hoy);
    });

    // 3. Lógica de búsqueda por texto
    if ($request->filled('buscar')) {
      $buscar = Helpers::sanearStringConEspacios($request->buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $query->where('nombre', 'ILIKE', '%' . $palabra . '%');
      }
    }

    // 4. Lógica de filtro por tipo de actividad
    if ($request->filled('tiposActividad')) {
      $query->whereIn('tipo_actividad_id', $request->tiposActividad);
    }

    // 5. Lógica de filtro por tags
    if ($request->filled('tags')) {
      $query->whereHas('tags', function ($q) use ($request) {
        $q->whereIn('tag_id', $request->tags);
      });
    }

    return $query;
  }


  public function listado(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);

    // 1. Usamos el método auxiliar para obtener la consulta con filtros
    if (Auth::check() && Auth::user()->can('actividades.ver_todas_las_actividades'))
      {
          $query=Actividad::whereRaw('1=1');
    }else{
      $query = $this->_buildActividadesQuery($request);
    }


    // 2. NO añadimos ningún filtro de 'activa', por lo que traerá todas.

    // 3. Paginamos los resultados
    $actividades = $query->orderBy('id', 'desc')->paginate(12);

    // Preparamos los datos para los filtros de la vista (sin cambios)
    $parametrosBusqueda = (object)['buscar' => $request->buscar, 'bandera' => $request->buscar ? 1 : ''];
    $tiposActividad = TipoActividad::get();
    $tagsGenerales = TagGeneral::get();

    return view('contenido.paginas.actividades.listado', [
      'actividades' => $actividades,
      'configuracion' => Configuracion::find(1),
      'parametrosBusqueda' => $parametrosBusqueda,
      'rolActivo' => $rolActivo,
      'usuario' => $usuario,
      'tiposActividad' => $tiposActividad,
      'tagsGenerales' => $tagsGenerales,
      'tagsFiltro' => $request->tags ?? [],
      'tiposActividadSeleccionadas' => $request->tiposActividad ?? []
    ]);
  }

  /**
   * MÉTODO EDITADO: Para el Público General.
   * Ahora usa la consulta base y MUESTRA SOLO LAS ACTIVIDADES ACTIVAS.
   */
 public function proximas(Request $request)
  {
    $hoy = now()->toDateString();

    // 1. Construimos la consulta base con los filtros de fecha, búsqueda y tags
    $query = $this->_buildActividadesQuery($request);

    // 2. Filtro específico de esta vista
    $query->where('mostrar_en_proximas_actividades', true);
    $query->orderBy('fecha_inicio', 'asc');

    // 3. Lógica de Filtrado por Usuario y Paginación
    if (Auth::check()) {
        $usuario = Auth::user();

        // Obtenemos TODAS las actividades vigentes que coinciden con la búsqueda
        $actividadesVigentes = $query->get();

        // Aplicamos el filtro de elegibilidad del usuario (Lógica del Modelo)
        // Se asume que este método retorna un array o colección de IDs permitidos
        $permitidasIds = Actividad::filtrarActividadesPermitidas($usuario, $actividadesVigentes);

        // Filtramos la colección resultante
        $coleccionFiltrada = $actividadesVigentes->whereIn('id', $permitidasIds);

        // --- PAGINACIÓN MANUAL DE LA COLECCIÓN ---
        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 12;

        // Cortamos la colección para la página actual
        $itemsPaginados = $coleccionFiltrada->slice(($page - 1) * $perPage, $perPage)->values();

        // Creamos el objeto Paginator manteniendo los parámetros de búsqueda en la URL
        $actividades = new LengthAwarePaginator(
            $itemsPaginados,
            $coleccionFiltrada->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

    } else {
        // Si es invitado, paginamos directamente desde la base de datos
        $actividades = $query->paginate(12);
    }

    // 4. Lógica de Banners (Solicitada)
    $banners = \App\Models\BannerGeneral::where('visible', true) // Asegúrate de usar el namespace correcto
        ->where(function($query) use ($hoy) {
            $query->whereNull('fecha_inicio')
                  ->orWhere('fecha_inicio', '<=', $hoy);
        })
        ->where(function($query) use ($hoy) {
            $query->whereNull('fecha_fin')
                  ->orWhere('fecha_fin', '>=', $hoy);
        })
        ->get();

    // Preparamos los datos para la vista
    $parametrosBusqueda = (object)['buscar' => $request->buscar, 'bandera' => $request->buscar ? 1 : ''];
    $tiposActividad = TipoActividad::get();
    $tagsGenerales = TagGeneral::get();

    return view('contenido.paginas.actividades.proximas-actividades', [
      'actividades' => $actividades,
      'banners' => $banners, // Enviamos los banners a la vista
      'configuracion' => Configuracion::find(1),
      'parametrosBusqueda' => $parametrosBusqueda,
      'tiposActividad' => $tiposActividad,
      'tagsGenerales' => $tagsGenerales,
      'tagsFiltro' => $request->tags ?? [],
      'tiposActividadSeleccionadas' => $request->tiposActividad ?? []
    ]);
  }


  public function duplicar(Actividad $actividad)
  {
    $actividad_original = $actividad;
    DB::beginTransaction();

    try {
      // 1. CREACIÓN MANUAL DE LA NUEVA ACTIVIDAD (Sin cambios)
      $nueva_actividad = new Actividad();
      // ... (asignación manual de campos se mantiene igual)
      $nueva_actividad->descripcion = $actividad_original->descripcion;
      $nueva_actividad->descripcion_corta = $actividad_original->descripcion_corta;
      $nueva_actividad->mensaje_informativo = $actividad_original->mensaje_informativo;
      $nueva_actividad->fecha_finalizacion = $actividad_original->fecha_finalizacion;
      $nueva_actividad->tipo_actividad_id = $actividad_original->tipo_actividad_id;
      $nueva_actividad->fecha_visualizacion = $actividad_original->fecha_visualizacion;
      $nueva_actividad->fecha_cierre = $actividad_original->fecha_cierre;
      $nueva_actividad->codigo_sap = $actividad_original->codigo_sap;
      $nueva_actividad->totalmente_publica = $actividad_original->totalmente_publica;
      $nueva_actividad->genero = $actividad_original->genero;
      $nueva_actividad->vinculacion_grupo = $actividad_original->vinculacion_grupo;
      $nueva_actividad->actividad_grupo = $actividad_original->actividad_grupo;
      $nueva_actividad->evaluacion_general = $actividad_original->evaluacion_general;
      $nueva_actividad->evaluacion_financiera = $actividad_original->evaluacion_financiera;
      $nueva_actividad->fecha_inicio = $actividad_original->fecha_inicio;
      $nueva_actividad->excluyente = $actividad_original->excluyente;
      $nueva_actividad->proyecto_sap = $actividad_original->proyecto_sap;
      $nueva_actividad->centro_costo_sap = $actividad_original->centro_costo_sap;
      $nueva_actividad->sucursal_sap = $actividad_original->sucursal_sap;
      $nueva_actividad->punto_de_pago = $actividad_original->punto_de_pago;
      $nueva_actividad->incremento_pdp = $actividad_original->incremento_pdp;
      $nueva_actividad->permite_personas_externas = $actividad_original->permite_personas_externas;
      $nueva_actividad->color = $actividad_original->color;
      $nueva_actividad->fondo = $actividad_original->fondo;
      $nueva_actividad->label_destinatario = $actividad_original->label_destinatario;
      $nueva_actividad->restriccion_por_categoria = $actividad_original->restriccion_por_categoria;
      $nueva_actividad->aforo = $actividad_original->aforo;
      $nueva_actividad->periodo_id = $actividad_original->periodo_id;
      $nueva_actividad->estado_inscripcion_defecto = $actividad->estado_inscripcion_defecto;
      $nueva_actividad->nombre = $actividad_original->nombre . ' (Copia)';
      $nueva_actividad->activa = false;
      $nueva_actividad->aforo_ocupado = 0;
      $nueva_actividad->save();
      $nueva_actividad->load('tipo');

      // 2. SINCRONIZAR RELACIONES GENERALES DE LA ACTIVIDAD (Sin cambios)
      // ... (código de sincronización de monedas, tipos de pago, sedes, etc. de la actividad)
      $nueva_actividad->monedas()->sync($actividad_original->monedas()->pluck('moneda_id'));
      $nueva_actividad->tiposPago()->sync($actividad_original->tiposPago()->pluck('tipo_pago_id'));
      $nueva_actividad->sedes()->sync($actividad_original->sedes()->pluck('sede_id'));
      $nueva_actividad->camposAdicionales()->sync($actividad_original->camposAdicionales()->pluck('campos_adicionales_actividad_id'));
      $nueva_actividad->rangosEdad()->sync($actividad_original->rangosEdad()->pluck('rango_edad_id'));
      $nueva_actividad->tipoUsuarios()->sync($actividad_original->tipoUsuarios()->pluck('tipo_usuario_id'));
      $nueva_actividad->estadosCiviles()->sync($actividad_original->estadosCiviles()->pluck('estado_civil_id'));
      $nueva_actividad->tipoServicios()->sync($actividad_original->tipoServicios()->pluck('tipo_servicio_id'));
      $nueva_actividad->tags()->sync($actividad_original->tags()->pluck('tag_id'));
      $nueva_actividad->destinatarios()->sync($actividad_original->destinatarios()->pluck('destinatario_id'));
      $procesos_requisito_sync = $actividad_original->procesosRequisito->mapWithKeys(function ($proceso) {
        return [$proceso->id => ['estado' => $proceso->pivot->estado, 'indice' => $proceso->pivot->indice]];
      });
      $nueva_actividad->procesosRequisito()->sync($procesos_requisito_sync);
      $procesos_culminados_sync = $actividad_original->procesosCulminados->mapWithKeys(function ($proceso) {
        return [$proceso->id => ['estado' => $proceso->pivot->estado, 'indice' => $proceso->pivot->indice]];
      });
      $nueva_actividad->procesosCulminados()->sync($procesos_culminados_sync);


      // =================================================================================
      // 3. DUPLICACIÓN CONDICIONAL DE CATEGORÍAS Y SUS PRECIOS/ABONOS
      // =================================================================================

      $actividad_original->load('tipo');

      foreach ($actividad_original->categorias as $categoria_original) {
        // Primero, duplicamos la categoría y sus RESTRICCIONES (sin precios).
        $nueva_categoria = $this->duplicarCategoriaBase($categoria_original, $nueva_actividad->id);

        // Ahora, duplicamos el tipo de precio correcto según el tipo de actividad.

        // CASO 1: La actividad permite abonos
        if ($actividad_original->tipo->permite_abonos == true) {
          // Buscamos y duplicamos ÚNICAMENTE desde la tabla 'abono_categoria'.
          $registros_abono_originales = AbonoCategoria::where('actividad_categoria_id', $categoria_original->id)->get();
          foreach ($registros_abono_originales as $registro_abono) {
            AbonoCategoria::create([
              'abono_id'               => $registro_abono->abono_id,
              'actividad_categoria_id' => $nueva_categoria->id,
              'valor'                  => $registro_abono->valor,
              'moneda_id'              => $registro_abono->moneda_id,
            ]);
          }

          // CASO 2: Es cualquier otro tipo de actividad (Escuelas o General)
        } else {
          // Buscamos y duplicamos ÚNICAMENTE desde la tabla 'actividad_categoria_monedas'.
          foreach ($categoria_original->monedas as $moneda) {
            $nueva_categoria->monedas()->attach($moneda->id, [
              'valor' => $moneda->pivot->valor,
              'novedad_id' => $moneda->pivot->novedad_id,
            ]);
          }
        }
      }

      DB::commit();

      return redirect()->route('actividades.actualizar', $nueva_actividad)
        ->with('success', 'La actividad se ha duplicado con éxito. Revisa la configuración y actívala cuando esté lista.');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error al duplicar actividad: ' . $e->getMessage());
      return back()->with('error', 'Ocurrió un error inesperado al duplicar la actividad: ' . $e->getMessage());
    }
  }

  /**
   * --- MÉTODO AUXILIAR MODIFICADO ---
   * Ahora solo duplica la categoría y sus restricciones, NO los precios.
   */
  private function duplicarCategoriaBase(ActividadCategoria $categoria_original, int $nueva_actividad_id): ActividadCategoria
  {
    // Se clona la información básica de la categoría
    $nueva_categoria = new ActividadCategoria();
    $nueva_categoria->actividad_id = $nueva_actividad_id;
    $nueva_categoria->nombre = $categoria_original->nombre;
    $nueva_categoria->aforo = $categoria_original->aforo;
    $nueva_categoria->limite_compras = $categoria_original->limite_compras;
    $nueva_categoria->es_gratuita = $categoria_original->es_gratuita;
    $nueva_categoria->vinculacion_grupo = $categoria_original->vinculacion_grupo;
    $nueva_categoria->actividad_grupo = $categoria_original->actividad_grupo;
    $nueva_categoria->genero = $categoria_original->genero;
    $nueva_categoria->materia_periodo_id = $categoria_original->materia_periodo_id;
    $nueva_categoria->nivel_id = $categoria_original->nivel_id;
    $nueva_categoria->aforo_ocupado = 0;
    $nueva_categoria->save();

    // --- INICIO DE LA LÓGICA AÑADIDA ---
    // Duplicar los precios estándar de la categoría (tabla actividad_categoria_monedas)
    // solo si la categoría no está marcada como gratuita.
    if ($categoria_original->es_gratuita == false) {
      foreach ($categoria_original->monedas as $moneda) {
        $nueva_categoria->monedas()->attach($moneda->id, [
          'valor' => $moneda->pivot->valor,
          'novedad_id' => $moneda->pivot->novedad_id,
        ]);
      }
    }
    // --- FIN DE LA LÓGICA AÑADIDA ---

    // Duplicar relaciones de restricción de la categoría (sin cambios)
    $nueva_categoria->sedes()->sync($categoria_original->sedes()->pluck('sede_id'));
    $nueva_categoria->rangosEdad()->sync($categoria_original->rangosEdad()->pluck('rango_edad_id'));
    $nueva_categoria->tipoUsuarios()->sync($categoria_original->tipoUsuarios()->pluck('tipo_usuario_id'));
    $nueva_categoria->estadosCiviles()->sync($categoria_original->estadosCiviles()->pluck('estado_civil_id'));
    $nueva_categoria->tipoServicios()->sync($categoria_original->tipoServicios()->pluck('tipo_servicio_id'));

    $cat_procesos_req_sync = $categoria_original->procesosRequisito->mapWithKeys(function ($proceso) {
      return [$proceso->id => ['estado' => $proceso->pivot->estado, 'indice' => $proceso->pivot->indice]];
    });
    $nueva_categoria->procesosRequisito()->sync($cat_procesos_req_sync);

    $cat_procesos_cul_sync = $categoria_original->procesosCulminados->mapWithKeys(function ($proceso) {
      return [$proceso->id => ['estado' => $proceso->pivot->estado, 'indice' => $proceso->pivot->indice]];
    });
    $nueva_categoria->procesosCulminados()->sync($cat_procesos_cul_sync);

    return $nueva_categoria;
  }



  public function nueva()
  {
    $configuracion = Configuracion::find(1);
    $tiposActividad = TipoActividad::get();

    $actividades = Actividad::where('activa', TRUE)->get();
    $arrayActividades = [];


    foreach ($actividades as $acti) {
      $arrayActividades[] =
        [
          'id' => $acti->id,
          'title' => $acti->nombre . '/' . $acti->id,
          'start' => $acti->fecha_inicio,
          'end' => $acti->fecha_finalizacion,
          'backgroundColor' => $acti->fondo,
          'textColor' => $acti->color,
          'borderColor' => '#000000'
        ];
    }

    return view('contenido.paginas.actividades.nueva', [

      'configuracion' => $configuracion,
      'tiposActividad' => $tiposActividad,
      'arrayActividades' => $arrayActividades

    ]);
  }

  public function crear(Request $request)
  {


    $configuracion = Configuracion::find(1);
    $tipoActividad = TipoActividad::find($request->tipo_actividad);
    $tiposActividad = TipoActividad::get();

    $actividad = new Actividad;
    $actividad->nombre = $request->nombre;
    $actividad->tipo_actividad_id = $tipoActividad->id;
    $actividad->descripcion = $request->descripcion;
    $actividad->fecha_inicio = $request->fecha_inicio;
    $actividad->fecha_finalizacion = $request->fecha_fin;
    $actividad->totalmente_publica = FALSE;
    if ($request->habilitadoPDP == 'on') {
      $actividad->punto_de_pago = TRUE;
    } else {
      $actividad->punto_de_pago = FALSE;
    }
    $actividad->activa = TRUE;

    $actividad->fondo = '#666CE8';
    $actividad->color = '#FFFFFF';

    $actividad->save();

    // Si la activiad es gratuita creo una categoria para esta Actividad por default
    /*
       if($actividad->tipoActividad->requiere_inscripcion == TRUE && $actividad->tipoActividad->unica_compra == FALSE && $actividad->tipoActividad->multiples_compras == FALSE)
       {
            $actividad->punto_de_pago=TRUE;
            $actividad->save();

            $categoria_actividad=new CategoriaActividad;
            $categoria_actividad->actividad_id=$actividad->id;
            $categoria_actividad->nombre="Default";
            $categoria_actividad->aforo=1000;
            $categoria_actividad->aforo_ocupado=0;
            $categoria_actividad->save();
       }
      */
    /*
      return view('contenido.paginas.actividades.actualizar',[

        'configuracion'=>$configuracion,
        'tiposActividad'=>$tiposActividad,
        'tipoActividad'=>$tipoActividad,
        'actividad'=>$actividad,


        ]);
        */

    return redirect()->route('actividades.actualizar', [$actividad]);
  }

  public function crearCategorias(Actividad $actividad)
  {
    // Cargamos todos los datos necesarios para los selects del formulario de restricciones.
    // Esto es lo que antes hacía el componente de Livewire en su método render o mount.
    $sedes = Sede::all();
    $rangosEdad = RangoEdad::all();
    $tipoUsuarios = TipoUsuario::all();
    $estadosCiviles = EstadoCivil::all();
    $tipoServicios = TipoServicioGrupo::all();

    // MODIFICADO: Llamamos al nuevo método auxiliar para generar los pasos.
    $pasos_para_select = $this->generarPasosCrecimiento();

    // Pasamos todas las variables a la vista.
    return view('contenido.paginas.actividades.crear-categoria-actividad', [
      'actividad' => $actividad,
      'monedasActividad' => $actividad->monedas, // Para los campos de precio.
      'sedes' => $sedes,
      'rangosEdad' => $rangosEdad,
      'tipoUsuarios' => $tipoUsuarios,
      'estadosCiviles' => $estadosCiviles,
      'tipoServicios' => $tipoServicios,
      'pasosCrecimientoRequisito' =>  $pasos_para_select, // Usamos el mismo nombre para consistencia
      'pasosCrecimientoCulminar' =>  $pasos_para_select,  // Usamos el mismo nombre para consistencia
    ]);
  }



  public function storeCategoria(Request $request, Actividad $actividad)
  {

    // =========================================================================
    // 1. CONSTRUCCIÓN DINÁMICA DE REGLAS DE VALIDACIÓN
    // =========================================================================
    $reglas = [
      'nombre' => 'required|string|max:255',
      'aforo' => 'required|integer|min:0',
      //'limite_compras' => 'required|integer|min:1' . ($actividad->tipo->unica_compra ? '|max:1' : ''),
      'es_gratuita' => 'nullable|boolean',
      'valoresMonedas' => 'nullable|array',
      'pasos_culminar' => 'nullable|array',
      'rangos_edad' => 'nullable|array',
      'pasos_requisito' => 'nullable|array',
      'tipo_usuarios' => 'nullable|array',
      'estados_civiles' => 'nullable|array',
      'tipo_servicios' => 'nullable|array',
    ];

    // Añadir reglas de restricciones solo si la actividad está configurada para ello
    if ($actividad->restriccion_por_categoria) {
      $reglas['genero'] = 'required|integer|in:1,2,3';
      $reglas['vinculacion_grupo'] = 'required|integer|in:1,2,3';
      $reglas['actividad_grupo'] = 'required|integer|in:1,2,3';
      $reglas['sedes'] = 'required|array|min:1';
    }

    // Añadir regla de validación para los precios SOLO si la categoría NO es gratuita
    if (!$request->has('es_gratuita')) {
      $reglas['valoresMonedas.*'] = 'required|numeric|min:0';
    }

    // Definir los mensajes de error personalizados
    $mensajes = [
      'valoresMonedas.*.required' => 'El valor para cada moneda es obligatorio si la categoría no es gratuita.',
      'sedes.required' => 'Debes seleccionar al menos una sede habilitada.',
      'genero.required' => 'El campo género es obligatorio.',
      'vinculacion_grupo.required' => 'El campo vinculación a grupo es obligatorio.',
      'actividad_grupo.required' => 'El campo actividad en grupo es obligatorio.',
    ];

    // Ejecutar la validación
    $validatedData = $request->validate($reglas, $mensajes);


    // =========================================================================
    // 2. CREACIÓN DEL REGISTRO PRINCIPAL DE LA CATEGORÍA
    // =========================================================================
    $categoriaActividad = new ActividadCategoria();
    $categoriaActividad->actividad_id = $actividad->id;
    $categoriaActividad->nombre = $validatedData['nombre'];
    $categoriaActividad->aforo = $validatedData['aforo'];
    //$categoriaActividad->limite_compras = $validatedData['limite_compras'];
    $categoriaActividad->es_gratuita = $request->has('es_gratuita');
    $categoriaActividad->aforo_ocupado = 0;
    if ($request->limiteInvitados) {
      $categoriaActividad->limite_invitados = $request->limiteInvitados;
    }

    if ($actividad->restriccion_por_categoria) {
      $categoriaActividad->genero = $request->genero;
      $categoriaActividad->vinculacion_grupo = $request->vinculacion_grupo;
      $categoriaActividad->actividad_grupo = $request->actividad_grupo;
    }

    $categoriaActividad->save();


    // =========================================================================
    // 3. MANEJO DE PRECIOS POR MONEDA
    // =========================================================================
    $valoresParaSincronizar = [];
    if ($categoriaActividad->es_gratuita) {
      foreach ($actividad->monedas as $moneda) {
        $valoresParaSincronizar[$moneda->id] = ['valor' => 0];
      }
    } elseif ($request->has('valoresMonedas')) {
      foreach ($request->valoresMonedas as $monedaId => $valor) {
        if (!is_null($valor)) {
          $valoresParaSincronizar[$monedaId] = ['valor' => $valor];
        }
      }
    }
    $categoriaActividad->monedas()->sync($valoresParaSincronizar);


    // =========================================================================
    // 4. SINCRONIZACIÓN DE RESTRICCIONES (RELACIONES MUCHOS A MUCHOS)
    // =========================================================================
    if ($actividad->restriccion_por_categoria) {
      $categoriaActividad->sedes()->sync($request->sedes ?? []);
      $categoriaActividad->rangosEdad()->sync($request->rangos_edad ?? []);
      $categoriaActividad->tipoUsuarios()->sync($request->tipo_usuarios ?? []);
      $categoriaActividad->estadosCiviles()->sync($request->estados_civiles ?? []);
      $categoriaActividad->tipoServicios()->sync($request->tipo_servicios ?? []);

      // Procesamos los Pasos de Crecimiento usando el método auxiliar del controlador
      // Procesamos los Pasos de Crecimiento usando el método auxiliar del controlador
      // $pasosRequisitoSync = $this->procesarPasosParaSync($request->pasos_requisito ?? []);
      // $categoriaActividad->procesosRequisito()->sync($pasosRequisitoSync);

      // $pasosCulminarSync = $this->procesarPasosParaSync($request->pasos_culminar ?? []);
      // $categoriaActividad->procesosCulminados()->sync($pasosCulminarSync);
    }


    // =========================================================================
    // 5. REDIRECCIÓN FINAL
    // =========================================================================
    return redirect()->route('actividades.categorias', $actividad)
      ->with('success', 'Categoría "' . $categoriaActividad->nombre . '" creada exitosamente.');
  }

  public function updateCategoria(Request $request, ActividadCategoria $categoria)
  {
    $actividad = $categoria->actividad;



    // 2. ACTUALIZACIÓN DE LOS CAMPOS DIRECTOS
    $categoria->nombre = $request['nombre'];
    $categoria->aforo = $request['aforo'];
    $categoria->limite_compras = $request['limite_compras'];
    $categoria->es_gratuita = $request->has('es_gratuita');


    if ($request->limiteInvitados) {
      $categoria->limite_invitados = $request->limiteInvitados;
    }


    if ($actividad->restriccion_por_categoria) {

      $categoria->genero = $request->genero;
      $categoria->vinculacion_grupo = $request->vinculacion_grupo;
      $categoria->actividad_grupo = $request->actividad_grupo;
    }

    // 3. MANEJO DE PRECIOS POR MONEDA (usando sync para actualizar)
    $valoresParaSincronizar = [];
    if ($categoria->es_gratuita) {
      foreach ($actividad->monedas as $moneda) {
        $valoresParaSincronizar[$moneda->id] = ['valor' => 0];
      }
    } elseif ($request->has('valoresMonedas')) {
      foreach ($request->valoresMonedas as $monedaId => $valor) {
        if (!is_null($valor)) {
          $valoresParaSincronizar[$monedaId] = ['valor' => $valor];
        }
      }
    }
    $categoria->monedas()->sync($valoresParaSincronizar);

    // 4. SINCRONIZACIÓN DE RESTRICCIONES
    if ($actividad->restriccion_por_categoria) {
      $categoria->sedes()->sync($request->sedes ?? []);
      $categoria->rangosEdad()->sync($request->rangos_edad ?? []);
      $categoria->tipoUsuarios()->sync($request->tipo_usuarios ?? []);
      $categoria->estadosCiviles()->sync($request->estados_civiles ?? []);
      $categoria->tipoServicios()->sync($request->tipo_servicios ?? []);

      // Procesamos los Pasos de Crecimiento usando el método auxiliar
      // Procesamos los Pasos de Crecimiento usando el método auxiliar
      // $pasosRequisitoSync = $this->procesarPasosParaSync($request->pasos_requisito ?? []);
      // $categoria->procesosRequisito()->sync($pasosRequisitoSync);

      // $pasosCulminarSync = $this->procesarPasosParaSync($request->pasos_culminar ?? []);
      // $categoria->procesosCulminados()->sync($pasosCulminarSync);
    }

    // 5. GUARDAR EL MODELO PRINCIPAL
    $categoria->save();



    // 6. REDIRECCIÓN FINAL
    return redirect()->route('actividades.categorias', $actividad)
      ->with('success', 'Categoría "' . $categoria->nombre . '" actualizada exitosamente.');
  }
  public function editarCategoria(ActividadCategoria $categoria)
  {
    // Cargamos la actividad padre y las relaciones necesarias para el formulario
    $actividad = $categoria->actividad;

    // Reutilizamos la misma lógica que en 'crearCategorias' para obtener los datos de los selects
    $sedes = Sede::all();
    $rangosEdad = RangoEdad::all();
    $tipoUsuarios = TipoUsuario::all();
    $estadosCiviles = EstadoCivil::all();
    $tipoServicios = TipoServicioGrupo::all();

    // MODIFICADO: Llamamos al nuevo método auxiliar para generar los pasos.
    $pasos_para_select = $this->generarPasosCrecimiento();
    // Pasamos todos los datos a la vista de edición.
    return view('contenido.paginas.actividades.editar-categoria-actividad', [
      'categoria' => $categoria, // La categoría específica que estamos editando
      'actividad' => $actividad,
      'monedasActividad' => $actividad->monedas,
      'sedes' => $sedes,
      'rangosEdad' => $rangosEdad,
      'tipoUsuarios' => $tipoUsuarios,
      'estadosCiviles' => $estadosCiviles,
      'tipoServicios' => $tipoServicios,
      'pasosCrecimientoRequisito' => $pasos_para_select,
      'pasosCrecimientoCulminar' => $pasos_para_select,
    ]);
  }


  // =========================================================================
  // MÉTODOS AUXILIARES PRIVADOS PARA MANEJAR PASOS DE CRECIMIENTO
  // =========================================================================

  /**
   * Genera el array completo de opciones para los selects de "Pasos de Crecimiento".
   * Cada paso de crecimiento se desglosa según los estados definidos en la base de datos.
   *
   * @return array
   */
  private function generarPasosCrecimiento(): array
  {
    $pasos_crecimiento = PasoCrecimiento::orderBy('id', 'asc')->get();
    $estadosPaso = EstadoPasoCrecimientoUsuario::all();
    $array_procesos = [];
    $contador_ids = 1;

    foreach ($pasos_crecimiento as $paso) {
      foreach ($estadosPaso as $estado) {
        $item = new \stdClass();
        $item->id = $contador_ids++;
        $item->id_paso = $paso->id;
        $item->nombre = $paso->nombre . ' - ' . $estado->nombre;
        $item->estado = $estado->id;
        $item->indice = $item->id;
        $array_procesos[] = $item;
      }
    }

    return $array_procesos;
  }

  /**
   * Procesa los IDs enviados desde el formulario y los convierte al formato
   * que el método sync() de Eloquent necesita para la tabla pivote.
   *
   * @param array $submitted_ids Array de IDs de las opciones seleccionadas.
   * @return array
   */
  private function procesarPasosParaSync(array $submitted_ids): array
  {
    // Si no se envió nada, devuelve un array vacío.
    if (empty($submitted_ids)) {
      return [];
    }

    // Generamos la lista completa de opciones para poder "traducir" los IDs.
    $mapeo_completo_pasos = $this->generarPasosCrecimiento();

    $pasos_a_sincronizar = [];

    // Filtramos la lista completa para quedarnos solo con las opciones que el usuario seleccionó.
    $opciones_seleccionadas = collect($mapeo_completo_pasos)->whereIn('id', $submitted_ids);

    // Construimos el array para sync(): [id_paso => ['estado_paso_crecimiento_usuario_id' => X]]
    foreach ($opciones_seleccionadas as $opcion) {
      // CAMBIO CLAVE: Añadimos el 'indice', que es el ID único de la opción del select.
      $pasos_a_sincronizar[$opcion->id_paso] = [
        'estado' => $opcion->estado,
        'estado_paso_crecimiento_usuario_id' => $opcion->estado,
        'indice' => $opcion->id
      ];
    }

    return $pasos_a_sincronizar;
  }

  public function crearCategoriaEscuela(Actividad $actividad)
  {
    // Buscamos el periodo asociado a la actividad de escuela
    $periodo = Periodo::find($actividad->periodo_id);

    // Obtenemos las materias de ese periodo, si existen
    $materiasPeriodo = $periodo ? $periodo->materiasPeriodo()->with('materia')->get() : [];

    // NUEVO: Obtenemos un array con los IDs de las materias que ya están
    // asignadas a una categoría dentro de ESTA actividad.
    $materiasUsadasIds = $actividad->categorias()
      ->whereNotNull('materia_periodo_id')
      ->pluck('materia_periodo_id')
      ->toArray();

    return view('contenido.paginas.actividades.crear-categoria-escuelas', [
      'actividad' => $actividad,
      'monedasActividad' => $actividad->monedas,
      'materiasPeriodo' => $materiasPeriodo,
      'materiasUsadasIds' => $materiasUsadasIds, // Pasamos el nuevo array a la vista
    ]);
  }


  public function storeCategoriaEscuela(Request $request, Actividad $actividad)
  {

    // 2. Creación del registro de la categoría
    $categoriaActividad = new ActividadCategoria();
    $categoriaActividad->actividad_id = $actividad->id;
    $categoriaActividad->nombre = $request->nombre;
    $categoriaActividad->aforo = $request->aforo;
    $categoriaActividad->limite_compras = $request->limite_compras;
    $categoriaActividad->es_gratuita = $request->has('es_gratuita');
    $categoriaActividad->materia_periodo_id = $request->materia_periodo_id;
    $categoriaActividad->aforo_ocupado = 0;
    $categoriaActividad->limite_compras = 1;
    $categoriaActividad->save();

    // 3. Lógica para guardar los precios (igual que antes)
    $valoresParaSincronizar = [];
    if ($categoriaActividad->es_gratuita) {
      foreach ($actividad->monedas as $moneda) {
        $valoresParaSincronizar[$moneda->id] = ['valor' => 0];
      }
    } elseif ($request->has('valoresMonedas')) {
      foreach ($request->valoresMonedas as $monedaId => $valor) {
        if (!is_null($valor)) {
          $valoresParaSincronizar[$monedaId] = ['valor' => $valor];
        }
      }
    }
    $categoriaActividad->monedas()->sync($valoresParaSincronizar);

    // 4. Redirección
    // NOTA: Asumo que la ruta para volver es 'actividades.categoriasEscuelas'. Ajusta si es diferente.
    return redirect()->route('actividades.categoriasEscuelas', $actividad)
      ->with('success', 'Categoría de escuela "' . $categoriaActividad->nombre . '" creada exitosamente.');
  }

  public function editarCategoriaEscuela(ActividadCategoria $categoria)
  {
    $actividad = $categoria->actividad;
    $periodo = Periodo::find($actividad->periodo_id);
    $materiasPeriodo = $periodo ? $periodo->materiasPeriodo()->with('materia')->get() : [];

    // NUEVO: Obtenemos los IDs de las materias usadas por OTRAS categorías
    // de esta misma actividad.
    $materiasUsadasIds = $actividad->categorias()
      ->where('id', '!=', $categoria->id) // Excluimos la categoría actual
      ->whereNotNull('materia_periodo_id')
      ->pluck('materia_periodo_id')
      ->toArray();

    return view('contenido.paginas.actividades.editar-categoria-escuelas', [
      'categoria' => $categoria,
      'actividad' => $actividad,
      'monedasActividad' => $actividad->monedas,
      'materiasPeriodo' => $materiasPeriodo,
      'materiasUsadasIds' => $materiasUsadasIds, // Pasamos el array de IDs usados a la vista
    ]);
  }

  /**
   * Actualiza una categoría de tipo Escuela en la base de datos.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\ActividadCategoria  $categoria
   * @return \Illuminate\Http\RedirectResponse
   */
  public function updateCategoriaEscuela(Request $request, ActividadCategoria $categoria)
  {
    $actividad = $categoria->actividad;

    // 1. Validación (similar a 'store', pero ignorando el nombre de la categoría actual para evitar conflictos)


    // 2. Actualización de los campos de la categoría
    $categoria->nombre = $request->nombre;
    $categoria->aforo = $request->aforo;
    $categoria->limite_compras = 1;
    $categoria->es_gratuita = $request->has('es_gratuita');
    $categoria->materia_periodo_id = $request->materia_periodo_id;
    $categoria->save();

    // 3. Sincronización de precios (usar sync es ideal para actualizaciones)
    $valoresParaSincronizar = [];
    if ($categoria->es_gratuita) {
      foreach ($actividad->monedas as $moneda) {
        $valoresParaSincronizar[$moneda->id] = ['valor' => 0];
      }
    } elseif ($request->has('valoresMonedas')) {
      foreach ($request->valoresMonedas as $monedaId => $valor) {
        if (!is_null($valor)) {
          $valoresParaSincronizar[$monedaId] = ['valor' => $valor];
        }
      }
    }
    $categoria->monedas()->sync($valoresParaSincronizar);

    // 4. Redirección
    return redirect()->route('actividades.categoriasEscuelas', $actividad)
      ->with('success', 'Categoría de escuela "' . $categoria->nombre . '" actualizada exitosamente.');
  }
  public function actualizar(Actividad $actividad)
  {


    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);
    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();
    $configuracion = Configuracion::find(1);
    $tiposActividad = TipoActividad::get();
    $monedas = Moneda::get();
    $tiposPago = TipoPago::get();
    $camposAdicionales = DB::table('campos_adicionales_actividad')->get();
    $destinatarios = DB::table('destinatarios')->get();
    $sedes = Sede::get();
    $tipoUsuarios = TipoUsuario::where('id_rol_dependiente', '!=', null)->get();

    // Obtenemos los IDs de roles dependientes primero para evitar error de tipos en PostgreSQL (string vs bigint)
    $rolesDependientesIds = \App\Models\Role::where('dependiente', true)->pluck('id')->toArray();
    $tipoUsuariosObjetivo = TipoUsuario::whereIn('id_rol_dependiente', $rolesDependientesIds)->get();

    $estadosCiviles = EstadoCivil::all();
    $tipoServicios = TipoServicioGrupo::all();
    $rangosEdad = RangoEdad::all();

    $pasos_crecimiento = PasoCrecimiento::orderBy('id', 'asc')->get();
    $contador_ids = 1;
    $array_procesos = array();

    //aqui esto es para enviar la info que tiene la actividad en base de datos para poderlo cargar en los select multiples
    $monedasActividad = $actividad->monedas()->select('moneda_id')->pluck('moneda_id')->toArray();
    $tiposPagoActividad = $actividad->tiposPago()->select('tipo_pago_id')->pluck('tipo_pago_id')->toArray();
    $camposAdicionalesActividad = $actividad->camposAdicionales()->pluck('campos_adicionales_actividad_id')->toArray();
    $rangosEdadActividad = $actividad->rangosEdad()->select('rango_edad_id')->pluck('rango_edad_id')->toArray();
    $tipoUsuariosActividad = $actividad->tipoUsuarios()->select('tipo_usuario_id')->pluck('tipo_usuario_id')->toArray();
    $estadosCivilesActividad = $actividad->estadosCiviles()->select('estado_civil_id')->pluck('estado_civil_id')->toArray();
    $tipoServiciosActividad = $actividad->tipoServicios()->select('tipo_servicio_id')->pluck('tipo_servicio_id')->toArray();
    $destinatariosActividad = $actividad->destinatarios()->select('destinatario_id')->pluck('destinatario_id')->toArray();
    $sedesActividad = $actividad->sedes()->select('sede_id')->pluck('sede_id')->toArray();


    $arrayProcesosRequiActuales = [];
    $arrayProcesosCulminarActuales = [];

    // Optimizamos cargando las relaciones una sola vez
    $procesosRequisitoActuales = $actividad->procesosRequisito;
    $procesosCulminadosActuales = $actividad->procesosCulminados;

    /// tags
    $tagsGenerales = TagGeneral::get();
    $tagsActividad = $actividad->tags()->pluck('tag_id')->toArray();

    $array_procesos = $this->generarPasosCrecimiento();

    foreach ($array_procesos as $item) {
        // Comprobamos en colección (Memoria) usando el nuevo campo FK
        $existeRequisito = $procesosRequisitoActuales->where('id', $item->id_paso)
            ->where('pivot.estado_paso_crecimiento_usuario_id', $item->estado)
            ->first();

        if ($existeRequisito) {
          array_push($arrayProcesosRequiActuales, $item->indice);
        }

        $existeCulminado = $procesosCulminadosActuales->where('id', $item->id_paso)
            ->where('pivot.estado_paso_crecimiento_usuario_id', $item->estado)
            ->first();

        if ($existeCulminado) {
          array_push($arrayProcesosCulminarActuales, $item->indice);
        }
    }
    $periodos = '';
    if ($actividad->tipo->tipo_escuelas == true) {
      $periodos = Periodo::where('estado', true)->orderBy('fecha_inicio_matricula', 'asc')->get();
    }


    if ($tiposCargo->contains('pestana_general', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades')) {

      return view('contenido.paginas.actividades.actualizar', [

        'configuracion' => $configuracion,
        'tiposActividad' => $tiposActividad,
        'actividad' => $actividad,
        'monedas' => $monedas,
        'tiposPago' => $tiposPago,
        'camposAdicionales' => $camposAdicionales,
        'destinatarios' => $destinatarios,
        'sedes' => $sedes,
        'pasosCrecimiento' => $array_procesos,
        'tipoUsuarios' => $tipoUsuarios,
        'estadosCiviles' => $estadosCiviles,
        'tipoServicios' => $tipoServicios,
        'rangosEdad' => $rangosEdad,
        'monedasActividad' => $monedasActividad,
        'tiposPagoActividad' => $tiposPagoActividad,
        'camposAdicionalesActividad' => $camposAdicionalesActividad,
        'rangosEdadActividad' => $rangosEdadActividad,
        'arrayProcesosRequiActuales' => $arrayProcesosRequiActuales,
        'arrayProcesosCulminarActuales' => $arrayProcesosCulminarActuales,
        'tipoUsuariosActividad' => $tipoUsuariosActividad,
        'estadosCivilesActividad' => $estadosCivilesActividad,
        'tipoServiciosActividad' => $tipoServiciosActividad,
        'destinatariosActividad' => $destinatariosActividad,
        'sedesActividad' => $sedesActividad,
        'rolActivo' => $rolActivo,
        'usuario' => $usuario,
        'tiposCargo' => $tiposCargo,
        'tagsGenerales' => $tagsGenerales,
        'tagsActividad' => $tagsActividad,
        'periodos' => $periodos,
        'tipoUsuariosObjetivo' => $tipoUsuariosObjetivo


      ]);
    } else {
      return Redirect::to('pagina-no-encontrada');
    }
  }

  public function update(Request $request, Actividad $actividad)
  {
    $errorFecha = $this->validarFechasActividad($request);
    if ($errorFecha) {
      return back()->with('swal_error', $errorFecha);
    }

    $actividad->nombre = $request->nombre;
    $actividad->password = $request->password;
    $actividad->fecha_finalizacion = $request->fecha_fin;
    $actividad->fecha_visualizacion = $request->fecha_visualizacion;
    $actividad->fecha_cierre = $request->fecha_cierre;
    $actividad->fecha_inicio = $request->fecha_inicio;
    $actividad->estado_inscripcion_defecto = $request->estadoInscripcion;
    $vacio = [];

    // Limpieza y decodificación del texto
    $html1 = $request->contenidoDescripcion;
    $html1 = html_entity_decode($html1, ENT_QUOTES, 'UTF-8');
    $html1 = preg_replace('/\s+/', ' ', $html1); // Eliminar espacios extra
    $html1 = trim($html1); // Eliminar espacios al inicio y final

    $actividad->descripcion = $html1;
    $actividad->descripcion_corta = $request->descripcionCorta;
    $actividad->mensaje_informativo = $request->mensajeInformativo;
    // Mismo proceso para contenidoFinal
    $html2 = $request->contenidoFinal;
    $html2 = html_entity_decode($html2, ENT_QUOTES, 'UTF-8');
    $html2 = preg_replace('/\s+/', ' ', $html2);
    $html2 = trim($html2);

    $actividad->instrucciones_finales = $html2;

    $html3 = $request->contenidoTerminos;
    $html3 = html_entity_decode($html3, ENT_QUOTES, 'UTF-8');
    $html3 = preg_replace('/\s+/', ' ', $html3);
    $html3 = trim($html3);
    $actividad->terminos_y_condiciones = $html3;


    /// TEXTOS EVALUACIÓN

    $actividad->evaluacion_general = $request->evaluacionGeneral;
    $actividad->evaluacion_financiera = $request->evaluacionFinanciera;

    if ($request->fondo == null) {
      $actividad->fondo = '#684152';
    } else {
      $actividad->fondo = $request->fondo;
    }



    if ($request->letra == null) {
      $actividad->color = '#FFFFFF';
    } else {
      $actividad->color = $request->letra;
    }

    if (isset($request->monedas)) {
      $actividad->monedas()->sync($request->monedas);
    } else {
      $actividad->monedas()->sync($vacio);
    }
    /// PARA GUARDAR LOS TIPOS PAGOS HABILITADOS PARA LA ACTIVIDAD
    if (isset($request->tiposPago)) {
      $actividad->tiposPago()->sync($request->tiposPago);
    }

    if ($request->incremento != null) {
      $actividad->incremento = $request->incremento;
    }

    if (isset($request->camposAdicionales)) {
      $actividad->camposAdicionales()->sync($request->camposAdicionales);
    } else {
      $actividad->camposAdicionales()->sync($vacio);
    }

    if (isset($request->tags)) {
      $actividad->tags()->sync($request->tags);
    } else {
      $actividad->tags()->sync($vacio);
    }
    $actividad->vinculacion_grupo = $request->vinculacionGrupo;
    $actividad->actividad_grupo = $request->actividadGrupo;
    $actividad->genero = $request->generos;


    if ($request->habilitadoPDP == 'on') {
      $actividad->punto_de_pago = TRUE;
    } else {
      $actividad->punto_de_pago = FALSE;
    }

    if ($request->tieneInvitados == 'on') {
      $actividad->tiene_invitados = TRUE;
    } else {
      $actividad->tiene_invitados = FALSE;
    }

    if ($request->editarFormulario == 'on') {
      $actividad->editar_formulario = TRUE;
    } else {
      $actividad->editar_formulario = FALSE;
    }

    if ($request->valoresCerrados == 'on') {
      $actividad->pagos_abonos_con_valores_cerrados = TRUE;
    } else {
      $actividad->pagos_abonos_con_valores_cerrados = FALSE;
    }

    if ($request->mostrarProximas == 'on') {
      $actividad->mostrar_en_proximas_actividades = TRUE;
    } else {
      $actividad->mostrar_en_proximas_actividades = FALSE;
    }

    if ($request->inicioSesion == 'on') {
      $actividad->tipo->requiere_inicio_sesion = TRUE;
    } elseif (($request->inicioSesion == '')) {
      $actividad->tipo->requiere_inicio_sesion  = FALSE;
    }

    if ($request->vistaTodos == 'on') {
      $actividad->totalmente_publica = TRUE;
    } else {
      $actividad->totalmente_publica = FALSE;
    }

    if ($request->restriccionCategoria  == 'on') {
      $actividad->restriccion_por_categoria = TRUE;
    } else {
      $actividad->restriccion_por_categoria = FALSE;
    }



    /// aqui empiezan la restrcciones pero depende si la actividad pasa a ser con restricciones generales

    if ($request->restriccionCategoria  == 'on') {

      $actividad->vinculacion_grupo = 1;
      $actividad->actividad_grupo = 1;

      if (isset($request->sedes)) {
        $actividad->sedes()->sync([]);
      }


      if (isset($request->rangosEdad)) {
        $actividad->rangosEdad()->detach($request->rangosEdad);
      }

      /*
      if (isset($request->pasosCrecimientoRequisito)) {
        $actividad->procesosRequisito()->sync([]);
      }
      */

      /*
      if (isset($request->pasosCrecimientoCulminar)) {

        $actividad->procesosCulminados()->sync([]);
      }
      */

      if (isset($request->tipoUsuarios)) {
        $actividad->tipoUsuarios()->sync([]);
      }

      $actividad->tipo_usuario_objetivo_id = $request->tipoUsuarioObjetivo;


      if (isset($request->estadosCiviles)) {
        $actividad->estadosCiviles()->sync([]);
      }


      if (isset($request->tipoServicios)) {
        $actividad->tipoServicios()->sync([]);
      }
    } else {

      $actividad->vinculacion_grupo = $request->vinculacionGrupo;
      $actividad->actividad_grupo = $request->actividadGrupo;



      if (isset($request->rangosEdad)) {
        $actividad->rangosEdad()->attach($request->rangosEdad);
      }

      $vacio = [];

      /*
      if (isset($request->pasosCrecimientoRequisito)) {
        $pasos_crecimiento = PasoCrecimiento::orderBy('id', 'asc')->get();
        $contador_ids = 1;
        $array_procesos = array();

        foreach ($pasos_crecimiento as $paso_crecimiento) {

          $item = new stdClass();
          $item->id = $contador_ids;
          $item->id_paso = $paso_crecimiento->id;
          $item->estado = 1;
          $item->indice = $contador_ids;
          $array_procesos[] = $item;
          $contador_ids = $contador_ids + 1;

          $item = new stdClass();
          $item->id = $contador_ids;
          $item->id_paso = $paso_crecimiento->id;
          $item->estado = 2;
          $item->indice = $contador_ids;
          $array_procesos[] = $item;
          $contador_ids = $contador_ids + 1;

          $item = new stdClass();
          $item->id = $contador_ids;
          $item->id_paso = $paso_crecimiento->id;
          $item->estado = 3;
          $item->indice = $contador_ids;
          $array_procesos[] = $item;
          $contador_ids = $contador_ids + 1;
        }

        $collection = collect($array_procesos)->whereIn('id', $request->pasosCrecimientoRequisito)->keyBy('id_paso')->select('estado', 'indice');

        $actividad->procesosRequisito()->sync($collection);
      } else {
        $actividad->procesosRequisito()->sync($vacio);
      }
      */

      /*
      if (isset($request->pasosCrecimientoCulminar)) {
        $pasos_crecimiento = PasoCrecimiento::orderBy('id', 'asc')->get();
        $contador_ids = 1;
        $array_procesos = array();

        foreach ($pasos_crecimiento as $paso_crecimiento) {

          $item = new stdClass();
          $item->id = $contador_ids;
          $item->id_paso = $paso_crecimiento->id;
          $item->estado = 1;
          $item->indice = $contador_ids;
          $array_procesos[] = $item;
          $contador_ids = $contador_ids + 1;

          $item = new stdClass();
          $item->id = $contador_ids;
          $item->id_paso = $paso_crecimiento->id;
          $item->estado = 2;
          $item->indice = $contador_ids;
          $array_procesos[] = $item;
          $contador_ids = $contador_ids + 1;

          $item = new stdClass();
          $item->id = $contador_ids;
          $item->id_paso = $paso_crecimiento->id;
          $item->estado = 3;
          $item->indice = $contador_ids;
          $array_procesos[] = $item;
          $contador_ids = $contador_ids + 1;
        }
        // return $request->pasosCrecimientoRequisito;
        $collection = collect($array_procesos)->whereIn('id', $request->pasosCrecimientoCulminar)->keyBy('id_paso')->select('estado', 'indice');
        //return $collection->toArray();
        $actividad->procesosCulminados()->sync($collection);
      } else {
        $actividad->procesosCulminados()->sync($vacio);
      }
      */

      if (isset($request->sedes)) {
        $actividad->sedes()->sync($request->sedes);
      } else {
        $actividad->sedes()->sync($vacio);
      }


      if (isset($request->tipoUsuarios)) {
        $actividad->tipoUsuarios()->sync($request->tipoUsuarios);
      } else {
        $actividad->tipoUsuarios()->sync($vacio);
      }


      if (isset($request->estadosCiviles)) {
        $actividad->estadosCiviles()->sync($request->estadosCiviles);
      } else {
        $actividad->estadosCiviles()->sync($vacio);
      }


      if (isset($request->tipoServicios)) {
        $actividad->tipoServicios()->sync($request->tipoServicios);
      } else {
        $actividad->tipoServicios()->sync($vacio);
      }
    }
$actividad->tipo_usuario_objetivo_id = $request->tipoUsuarioObjetivo;


    ///CONTENIDO DE SAP
    $actividad->codigo_sap = $request->cuentaSAP;
    $actividad->proyecto_sap = $request->proyectoSAP;
    $actividad->centro_costo_sap = $request->centro_costoSAP;
    $actividad->label_destinatario = $request->labelDestinatario;

    if (isset($request->destinatarios)) {
      $actividad->destinatarios()->sync($request->destinatarios);
    } else {
      $actividad->destinatarios()->sync($vacio);
    }

    $actividad->save();

    return back()->with('success', "Tu actividad: <b>" . $actividad->nombre . "</b> fue actualizada con éxito.");
  }

  public function updateEscuelas(Request $request, Actividad $actividad)
  {
    $errorFecha = $this->validarFechasActividad($request);
    if ($errorFecha) {
      return back()->with('swal_error', $errorFecha);
    }


    $actividad->nombre = $request->nombre;
    $actividad->fecha_finalizacion = $request->fecha_fin;
    $actividad->fecha_visualizacion = $request->fecha_visualizacion;
    $actividad->fecha_cierre = $request->fecha_cierre;
    $actividad->fecha_inicio = $request->fecha_inicio;
    $vacio = [];

    // Limpieza y decodificación del texto
    $html1 = $request->contenidoDescripcion;
    $html1 = html_entity_decode($html1, ENT_QUOTES, 'UTF-8');
    $html1 = preg_replace('/\s+/', ' ', $html1); // Eliminar espacios extra
    $html1 = trim($html1); // Eliminar espacios al inicio y final

    $actividad->descripcion = $html1;
    $actividad->descripcion_corta = $request->descripcionCorta;
    $actividad->mensaje_informativo = $request->mensajeInformativo;
    // Mismo proceso para contenidoFinal
    $html2 = $request->contenidoFinal;
    $html2 = html_entity_decode($html2, ENT_QUOTES, 'UTF-8');
    $html2 = preg_replace('/\s+/', ' ', $html2);
    $html2 = trim($html2);

    $actividad->instrucciones_finales = $html2;

    $html3 = $request->contenidoTerminos;
    $html3 = html_entity_decode($html3, ENT_QUOTES, 'UTF-8');
    $html3 = preg_replace('/\s+/', ' ', $html3);
    $html3 = trim($html3);
    $actividad->terminos_y_condiciones = $html3;
    $actividad->periodo_id = $request->periodoRelacionado;

      if ($request->mostrarProximas == 'on') {
      $actividad->mostrar_en_proximas_actividades = TRUE;
    } else {
      $actividad->mostrar_en_proximas_actividades = FALSE;
    }

    /// TEXTOS EVALUACIÓN

    $actividad->evaluacion_general = $request->evaluacionGeneral;
    $actividad->evaluacion_financiera = $request->evaluacionFinanciera;

    if ($request->fondo == null) {
      $actividad->fondo = '#684152';
    } else {
      $actividad->fondo = $request->fondo;
    }



    if ($request->letra == null) {
      $actividad->color = '#FFFFFF';
    } else {
      $actividad->color = $request->letra;
    }

    if (isset($request->monedas)) {
      $actividad->monedas()->sync($request->monedas);
    } else {
      $actividad->monedas()->sync($vacio);
    }
    /// PARA GUARDAR LOS TIPOS PAGOS HABILITADOS PARA LA ACTIVIDAD
    if (isset($request->tiposPago)) {
      $actividad->tiposPago()->sync($request->tiposPago);
    }

    if ($request->incremento != null) {
      $actividad->incremento = $request->incremento;
    }

    if (isset($request->camposAdicionales)) {
      $actividad->camposAdicionales()->sync($request->camposAdicionales);
    } else {
      $actividad->camposAdicionales()->sync($vacio);
    }

    if (isset($request->tags)) {
      $actividad->tags()->sync($request->tags);
    } else {
      $actividad->tags()->sync($vacio);
    }

    if ($request->periodoRelacionado != null) {
      $actividad->periodo_id = $request->periodoRelacionado;
    } else {
      $actividad->periodo_id = null;
    }


    if ($request->habilitadoPDP == 'on') {
      $actividad->punto_de_pago = TRUE;
    } else {
      $actividad->punto_de_pago = FALSE;
    }


    $actividad->tipo->requiere_inicio_sesion = TRUE;


    $actividad->totalmente_publica = TRUE;
    $actividad->restriccion_por_categoria = TRUE;
    $actividad->tipo->tipo_escuelas = TRUE;

    /// aqui empiezan la restrcciones pero depende si la actividad pasa a ser con restricciones generales



    ///CONTENIDO DE SAP
    $actividad->codigo_sap = $request->cuentaSAP;
    $actividad->proyecto_sap = $request->proyectoSAP;
    $actividad->centro_costo_sap = $request->centro_costoSAP;
    $actividad->label_destinatario = $request->labelDestinatario;

    if (isset($request->destinatarios)) {
      $actividad->destinatarios()->sync($request->destinatarios);
    } else {
      $actividad->destinatarios()->sync($vacio);
    }

    $actividad->save();

    return back()->with('success', "Tu actividad: <b>" . $actividad->nombre . "</b> fue actualizada con éxito.");
  }

  public function categorias(Actividad $actividad)
  {

    /// para poder ver los permisos
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);
    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();

    /// aqui hago la comprobación para que no se cuele por url personas que no deben ver estas rutas

    if ($tiposCargo->contains('pestana_general', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades')) {

      $categoriasActividad = $actividad->categorias;
      $monedasActividad = $actividad->monedas;

      return view(
        'contenido.paginas.actividades.categorias',
        [
          'actividad' => $actividad,
          'categoriasActividad' => $categoriasActividad,
          'monedasActividad' => $monedasActividad,
          'rolActivo' => $rolActivo,
          'usuario' => $usuario,
          'tiposCargo' => $tiposCargo

        ]
      );
    } else {
      return Redirect::to('pagina-no-encontrada');
    }
  }

  public function categoriasEscuelas(Actividad $actividad)
  {

    /// para poder ver los permisos
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);
    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();
    $periodo = Periodo::find($actividad->periodo_id);


    /// aqui hago la comprobación para que no se cuele por url personas que no deben ver estas rutas

    if ($tiposCargo->contains('pestana_general', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades')) {

      $categoriasActividad = $actividad->categorias;
      $monedasActividad = $actividad->monedas;

      return view(
        'contenido.paginas.actividades.categorias-escuelas',
        [
          'actividad' => $actividad,
          'categoriasActividad' => $categoriasActividad,
          'monedasActividad' => $monedasActividad,
          'rolActivo' => $rolActivo,
          'usuario' => $usuario,
          'tiposCargo' => $tiposCargo,


        ]
      );
    } else {
      return Redirect::to('pagina-no-encontrada');
    }
  }


  public function crearCategoria(Request $request, Actividad $actividad)
  {

    $monedasActividad = $actividad->monedas;

    $categoriaActividad = new ActividadCategoria;
    $categoriaActividad->actividad_id = $actividad->id;
    $categoriaActividad->nombre = $request->nombreCategoria;
    $categoriaActividad->aforo = $request->aforo;
    $categoriaActividad->save();


    foreach ($monedasActividad as $moneda) {
      // Buscar el valor correspondiente en el request
      $valorKey = 'valorMoneda-' . $moneda->id;

      if ($request->has($valorKey)) {
        // Crear registro en la tabla intermedia
        $categoriaActividad->monedas()->attach($moneda->id, [
          'valor' => $request->input($valorKey),
          // Puedes agregar novedad_id si es necesario
        ]);
      }
    }

    return redirect()->back()->with('success', 'Categoría creada exitosamente');
  }

  public function eliminarCategoria(ActividadCategoria $categoria)
  {
    $actividad = Actividad::find($categoria->actividad_id);
    if ($categoria->aforo_ocupado > 0) {

      return redirect()->route('actividades.categorias', [$actividad])->with('alert', "Tu categoria: <b>" . $categoria->nombre . "</b>
      no es posible eliminarla, ya que se registararon compras para esta actividad.");
    } else {
    }
  }

  public function cancelar(Actividad $actividad)
  {
    $actividad->activa = FALSE;
    $actividad->save();

    return back()->with('success', "Tu activiad: <b>" . $actividad->nombre . "</b> fue inactivada con éxito.");
  }

  public function activar(Actividad $actividad)
  {
    $actividad->activa = TRUE;
    $actividad->save();

    return back()->with('success', "Tu activiad: <b>" . $actividad->nombre . "</b> fue activada con éxito.");
  }

  public function abonos(Actividad $actividad)
  {
    $categoriasActividad = $actividad->categorias;
    $monedasActividad = $actividad->monedas;

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);
    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();


    if ($tiposCargo->contains('pestana_abonos', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades')) {

      $categoriasActividad = $actividad->categorias;
      $monedasActividad = $actividad->monedas;

      $categoriasActividad->map(function ($categoria) use ($monedasActividad) {
        $categoria->categoriasMoneda = $categoria->monedas;
      });

      return view(
        'contenido.paginas.actividades.abonos',
        [
          'actividad' => $actividad,
          'categoriasActividad' => $categoriasActividad,
          'monedasActividad' => $monedasActividad,
          'rolActivo' => $rolActivo,
          'usuario' => $usuario,
          'tiposCargo' => $tiposCargo

        ]
      );
    } else {
      return Redirect::to('pagina-no-encontrada');
    }
  }

  public function formularioActividad(Actividad $actividad)
  {

    $tiposElemento = TipoElementoFormularioActividad::all();
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);
    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();

    if ($tiposCargo->contains('pestana_formulario', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades')) {
      return view(
        'contenido.paginas.actividades.formulario-actividad',
        [
          'actividad' => $actividad,
          'tiposElemento' => $tiposElemento,
          'rolActivo' => $rolActivo,
          'usuario' => $usuario,
          'tiposCargo' => $tiposCargo


        ]
      );
    } else {
      return Redirect::to('pagina-no-encontrada');
    }
  }

  public function dashboardFormularios(Actividad $actividad)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);
    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();

    if ($tiposCargo->contains('pestana_formulario', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades'))
      return view(
        'contenido.paginas.actividades.dashboard-formularios',
        [
          'actividad' => $actividad,

          'rolActivo' => $rolActivo,
          'usuario' => $usuario,
          'tiposCargo' => $tiposCargo



        ]
      );
  }


  public function encargadosActividad(Actividad $actividad)
  {
    $configuracion = Configuracion::find(1);
    $usuario = '';
    $html = '';
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);
    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();

    if ($tiposCargo->contains('pestana_encargados', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades')) {
      return view(
        'contenido.paginas.actividades.encargados',
        [
          'actividad' => $actividad,
          'configuracion' =>  $configuracion,
          'rolActivo' => $rolActivo,
          'usuario' => $usuario,
          'tiposCargo' => $tiposCargo

        ]
      );
    } else {
      return Redirect::to('pagina-no-encontrada');
    }
  }

  public function asistenciasActividad(Actividad $actividad)
  {


    return view(
      'contenido.paginas.actividades.asistencias',
      [
        'actividad' => $actividad,



      ]
    );
  }

  public function multimedia(Actividad $actividad)
  {
    $configuracion = Configuracion::find(1);
    $bannerActual = ActividadBanner::where('actividad_id', $actividad->id)->first();
    $video = ActividadVideo::where('actividad_id', $actividad->id)->first();
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);
    $tiposCargo = $usuario->tiposCargo()->where('actividad_encargados_cargo.actividad_id', $actividad->id)->get();


    $usuario = '';
    $html = '';
    if ($tiposCargo->contains('pestana_encargados', true) || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades')) {
      return view(
        'contenido.paginas.actividades.multimedia',
        [
          'actividad' => $actividad,
          'configuracion' =>  $configuracion,
          'bannerActual' => $bannerActual,
          'video' => $video,
          'rolActivo' => $rolActivo,
          'usuario' => $usuario,
          'tiposCargo' => $tiposCargo

        ]
      );
    } else {
      return Redirect::to('pagina-no-encontrada');
    }
  }


  /**
   * Muestra el perfil público de una actividad.
   * Este método es responsable de:
   * 1. Cargar la actividad y su información.
   * 2. Si el usuario está autenticado, buscar su historial (compras, matrículas).
   * 3. Validar si el usuario cumple los requisitos para inscribirse.
   * 4. Pasar todos los datos a la vista para que se muestre la información correcta.
   *
   * @param Actividad $actividad
   * @return \Illuminate\View\View
   */
  public function perfil(Actividad $actividad)
  {
    // --- 1. INICIALIZACIÓN DE VARIABLES ---
    // Se inicializan todas las variables para asegurar que existan en la vista,
    // incluso si el usuario no está autenticado, evitando errores.
    $configuracion = Configuracion::find(1);
    $usuario = null;
    $compraExistente = null;
    $inscripcionesExistentes = collect(); // Usamos una colección vacía por defecto
    $matriculaExistente = null;
    $categoriasCompra = collect(); // Para las categorías que el usuario puede comprar
    $mensajesError = [];

    // Determinamos si la actividad tiene al menos una categoría con costo.
    // Esto simplifica la lógica en la vista para mostrar "Comprar" o "Inscribirme".
    $esActividadDePago = false;
    if (!$actividad->tipo->tipo_escuelas && !$actividad->tipo->permite_abonos) {
      $esActividadDePago = $actividad->categorias->some(function ($categoria) {
        // Asumimos la moneda principal (ID 1) para la comprobación.
        $precio = $categoria->monedas()->where('moneda_id', 1)->first()?->pivot->valor ?? 0;
        return $precio > 0;
      });
    }

    // --- 2. LÓGICA PARA USUARIOS AUTENTICADOS ---
    if (Auth::check()) {
      $usuario = Auth::user();

      // --- Búsqueda del Historial del Usuario con esta Actividad ---

      // a) Buscamos si ya existe una COMPRA para el usuario en esta actividad.
      $compraExistente = Compra::where('user_id', $usuario->id)
        ->where('actividad_id', $actividad->id)
        ->with('pagos.estadoPago', 'pagos.moneda') // Precargamos relaciones para usarlas en la vista
        ->first();

      // b) Buscamos las inscripciones. Pueden estar asociadas a una compra o ser gratuitas.
      if ($compraExistente) {
        // Si hay compra, las inscripciones están vinculadas a ella.
        $inscripcionesExistentes = Inscripcion::where('compra_id', $compraExistente->id)->get();
      } else {
        // Si no hay compra, buscamos inscripciones gratuitas (sin compra_id).
        $inscripcionesExistentes = Inscripcion::where('user_id', $usuario->id)
          ->whereNull('compra_id')
          ->whereHas('categoriaActividad', fn($q) => $q->where('actividad_id', $actividad->id))
          ->get();
      }


      // c) Si la actividad es de tipo "escuela" y hay una compra, buscamos la matrícula existente.
      if ($actividad->tipo->tipo_escuelas && $compraExistente) {
        // Se busca la matrícula a través de la referencia de pago.
        $pagosIds = $compraExistente->pagos->pluck('id');
        $matriculaExistente = \App\Models\Matricula::where('user_id', $usuario->id)
          ->whereIn('referencia_pago', $pagosIds)
          ->with([
            'horarioMateriaPeriodo.horarioBase.aula.sede',
            'horarioMateriaPeriodo.materiaPeriodo.materia'
          ])
          ->first();
      }

      // --- Validación de Requisitos para Comprar ---
    // Solo validamos si el usuario AÚN NO tiene una compra o inscripción.
    if (!$compraExistente && $inscripcionesExistentes->isEmpty()) {
        // NUEVA LÓGICA: Validar todas las categorías para mostrar estado detallado

        if($actividad->restriccion_por_categoria){
          $actividadEstados = collect([]);
          // Se llama a un método que valida las categorías disponibles para el usuario y sus dependientes
          $categoriasEstado = $actividad->validarCategoriasParaPerfil($usuario);
          $hayDisponibles = $categoriasEstado->contains('estado', 'DISPONIBLE');
        }else{
          // Si no hay restricción por categoría, validamos el acceso general para el grupo
          $actividadEstados = $actividad->validarCategoriasGeneralParaPerfil($usuario);
          $categoriasEstado = collect([]);
          $hayDisponibles = $actividadEstados->contains('estado', 'DISPONIBLE');
      }
    }
  }

    // Si hay un error en la sesión (por ejemplo, de una redirección), lo capturamos.
    // Esto tiene prioridad sobre otros mensajes para mostrar el feedback más reciente.
    if (session()->has('error')) {
      $mensajesError = session('error');
    }


    $video = ActividadVideo::where('actividad_id', $actividad->id)->first();

    // --- 3. PREPARAR Y RETORNAR LA VISTA ---
    // Pasamos todas las variables, tanto las generales como las del historial del usuario.
    return view('contenido.paginas.actividades.perfil-actividad', [
      'actividad' => $actividad,
      'configuracion' => $configuracion,
      'usuario' => $usuario,
      'fechaHoy' => Carbon::now()->format('Y-m-d'),
      'mensajesError' => $mensajesError,

      // Todas las categorías para la pestaña "Precios"
      'categoriasActividad' => $actividad->categorias()->orderBy('id', 'asc')->get(),

      // Categorías que el usuario tiene permitido comprar (para el botón de la derecha)
      'categoriasCompra' => $categoriasCompra,

      // Variables del Historial del Usuario
      'compraExistente' => $compraExistente,
      'inscripcionesExistentes' => $inscripcionesExistentes,
      'matriculaExistente' => $matriculaExistente,
      'esActividadDePago' => $esActividadDePago,
      'video' => $video,
      // NUEVAS VARIABLES
      'categoriasEstado' => $categoriasEstado ?? collect(),
      'actividadEstados' => $actividadEstados ?? collect(),
      'hayDisponibles' => $hayDisponibles ?? true // Por defecto true si no hay usuario logueado
    ]);
  }




  public function uploadBanner(Request $request, Actividad $actividad)
  {
    $configuracion = Configuracion::find(1);
    if ($request->foto) {
      if ($configuracion->version == 1) {
        $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/banner-actividad/');
        !is_dir($path) && mkdir($path, 0777, true);

        $imagenPartes = explode(';base64,', $request->foto);
        $imagenBase64 = base64_decode($imagenPartes[1]);
        $nombreFoto = 'banner-activdad' . $actividad->id . '.jpg';
        $imagenPath = $path . $nombreFoto;
        file_put_contents($imagenPath, $imagenBase64);

        $banner = new ActividadBanner();
        $banner->nombre = $nombreFoto;
        $banner->actividad_id = $actividad->id;
        $banner->img = $imagenPath;
        $banner->save();
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

    return back()->with('success', "el banner para la actividad <b>" . $actividad->nombre . "</b> fue creado con éxito.");
  }

  public function eliminarBanner($banner)
  {
    $configuracion = Configuracion::find(1);
    $bannerActual = ActividadBanner::find($banner);
    Storage::delete($configuracion->ruta_almacenamiento . '/img/banner-actividad/' . $bannerActual->nombre);
    $bannerActual->delete();
    return back()->with('success', "el banner para la actividad fue eliminado  con éxito.");
  }

  public function newVideo(Request $request, Actividad $actividad)
  {
    $configuracion = Configuracion::find(1);
    // 1. La URL de YouTube que viene del formulario o de otra fuente.
    $url = $request->iframe;

    // 2. Obtiene solo la parte de la consulta (ej: "v=ZT8U4cIu9Ss").
    $queryString = parse_url($url, PHP_URL_QUERY);

    // 3. Convierte la consulta en un arreglo (ej: ['v' => 'ZT8U4cIu9Ss']).
    parse_str($queryString, $queryParams);

    // 4. Accede al valor de 'v' para obtener el ID.
    // Usamos el operador '??' para evitar errores si 'v' no existe.
    $youtubeId = $queryParams['v'] ?? null;

    $video = new ActividadVideo();
    $video->nombre = $request->nombre;
    $video->url = $youtubeId;
    $video->actividad_id = $actividad->id;
    $video->save();

    return back()->with('success', "el video para la actividad <b>" . $actividad->nombre . "</b> fue creado con éxito.");
  }

  public function gestionarInscripciones(Inscripcion $inscripcion)
  {
    return view('contenido.paginas.actividades.gestionar-inscripciones', [
      'inscripcion' => $inscripcion
    ]);
  }

  public function eliminarVideo($video)
  {
    $video = ActividadVideo::find($video);
    $video->delete();
    return back()->with('success', "el video para la actividad fue eliminado  con éxito.");
  }

  /**
   * Valida las restricciones de fechas solicitadas para la actividad.
   *
   * @param Request $request
   * @return string|null El mensaje de error o null si es válido.
   */
  private function validarFechasActividad(Request $request)
  {
    $fecha_visualizacion = $request->fecha_visualizacion;
    $fecha_inicio = $request->fecha_inicio;
    $fecha_cierre = $request->fecha_cierre;
    $fecha_finalizacion = $request->fecha_fin;

    // Validación: fecha_visualizacion nunca puede ser mayor a fecha_finalizacion
    if ($fecha_visualizacion > $fecha_finalizacion) {
      return "La fecha de visualización no puede ser mayor a la fecha de finalización.";
    }

    // Validación: fecha_visualizacion nunca puede ser mayor a la fecha_cierre
    if ($fecha_visualizacion > $fecha_cierre) {
      return "La fecha de visualización no puede ser mayor a la fecha de cierre.";
    }

    // Validación: fecha_cierre nunca puede ser mayor a la fecha_finalizacion
    if ($fecha_cierre > $fecha_finalizacion) {
      return "La fecha de cierre no puede ser mayor a la fecha de finalización.";
    }

    return null;
  }
}
