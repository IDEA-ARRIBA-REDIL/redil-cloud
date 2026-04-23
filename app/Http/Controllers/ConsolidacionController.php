<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\BitacoraCrecimientoUsuario;
use App\Models\BitacoraEstadoCivil;
use App\Models\BitacoraTareaConsolidacion;
use App\Models\BitacoraTipoUsuario;
use App\Models\BitacoraIntegranteGrupo;
use App\Models\BloqueDashboardConsolidacion;
use App\Models\Configuracion;
use App\Models\Escuela;
use App\Models\EstadoCivil;
use App\Models\EstadoNivelAcademico;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\EstadoTareaConsolidacion;
use App\Models\FiltroConsolidacion;
use App\Models\HistorialTareaConsolidacionUsuario;
use App\Models\Matricula;
use App\Models\NivelAcademico;
use App\Models\Ocupacion;   
use App\Models\PasoCrecimiento;
use App\Models\Profesion;
use App\Models\RangoEdad;
use App\Models\Sede;
use App\Models\TareaConsolidacion;
use App\Models\TipoUsuario;
use App\Models\TipoVinculacion;
use App\Models\User;
use App\Models\Zona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DetalleConsolidacionKpiExport;
use App\Exports\DetalleConsolidacionKpiDashboardExport;
use App\Exports\DashboardCosechaExport;
use stdClass;


class ConsolidacionController extends Controller
{
    public function bloques()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        // Por ahora usamos el mismo permiso que el dashboard o uno específico si existe.
        // Usaremos el de dashboard consolidación por el momento.
        // $rolActivo->verificacionDelPermiso('consolidacion.subitem_dashboard_consolidacion');

        return view('contenido.paginas.consolidacion.bloques');
    }

    public function listar(Request $request, $tipo = 'todos')
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consolidacion.subitem_lista_consolidacion');

        $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')
            ->where('visible', true)
            ->where('tipo_pastor_principal', '!=', true)
            ->get();

        $rangosEdad = RangoEdad::all();
        $estadosCiviles = EstadoCivil::all();
        $tiposVinculaciones = TipoVinculacion::withTrashed()->get();
        $pasosCrecimiento = PasoCrecimiento::orderBy('updated_at', 'asc')->get();
        $estadosPasosDeCrecimiento = EstadoPasoCrecimientoUsuario::orderBy('puntaje', 'asc')->get();
        $ocupaciones = Ocupacion::orderBy('nombre', 'asc')->get();
        $nivelesAcademicos = NivelAcademico::orderBy('nombre', 'asc')->get();
        $estadosNivelAcademico = EstadoNivelAcademico::orderBy('id', 'asc')->get();
        $profesiones = Profesion::orderBy('nombre', 'asc')->get();
        $sedes = Sede::orderBy('nombre', 'asc')->get();

        $configuracion = Configuracion::find(1);
        $meses = Helpers::meses('largo');

        /* $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->subDays(30)->format('Y-m-d');
         $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->format('Y-m-d');*/

        $parametrosBusqueda['buscar'] = $request->buscar ? $request->buscar : '';

        $parametrosBusqueda['filtroPorSexo'] = $request->filtroPorSexo;
        $parametrosBusqueda['filtroPorTipoDeUsuario'] = $request->filtroPorTipoDeUsuario;
        $parametrosBusqueda['filtroPorRangoEdad'] = $request->filtroPorRangoEdad;
        $parametrosBusqueda['filtroPorEstadosCiviles'] = $request->filtroPorEstadosCiviles;
        $parametrosBusqueda['filtroPorTiposVinculaciones'] = $request->filtroPorTiposVinculaciones;
        $parametrosBusqueda['filtroPorOcupacion'] = $request->filtroPorOcupacion;
        $parametrosBusqueda['filtroPorProfesion'] = $request->filtroPorProfesion;
        $parametrosBusqueda['filtroPorNivelAcademico'] = $request->filtroPorNivelAcademico;
        $parametrosBusqueda['filtroPorEstadoNivelAcademico'] = $request->filtroPorEstadoNivelAcademico;
        $parametrosBusqueda['filtroPorSede'] = $request->filtroPorSede;

        $parametrosBusqueda['textoBusqueda'] = '';
        $parametrosBusqueda['tagsBusqueda'] = [];
        $parametrosBusqueda['bandera'] = '';
        $parametrosBusqueda['tipo'] = $tipo;

        $parametrosBusqueda = (object) $parametrosBusqueda;

        $personas = collect();
        if ($rolActivo->hasPermissionTo('consolidacion.lista_toda_consolidacion') || $rolActivo->hasPermissionTo('consolidacion.lista_consolidacion_solo_ministerio')) {
            if ($rolActivo->hasPermissionTo('consolidacion.lista_consolidacion_solo_ministerio')) {
                $personas = auth()->user()->consolidacion();
            }

            if ($rolActivo->hasPermissionTo('consolidacion.lista_toda_consolidacion')) {

                $tipoUsuariosHabilitados = TipoUsuario::where('habilitado_para_consolidacion', true)
                    ->pluck('id')
                    ->unique('id')
                    ->toArray();

                $personas = User::withTrashed()
                    ->whereIn('tipo_usuario_id', $tipoUsuariosHabilitados)
                    ->get()
                    ->unique('id');
            }

        }

        //  Empezamos con un Constructor de Consultas (Query Builder) en lugar de una colección vacía.
        $personasQuery = User::query();

        //  Aplicamos la lógica de permisos directamente a la consulta.
        if ($rolActivo->hasPermissionTo('consolidacion.lista_toda_consolidacion')) {

            $tipoUsuariosHabilitados = TipoUsuario::where('habilitado_para_consolidacion', true)->pluck('id');
            $personasQuery->withTrashed()->whereIn('tipo_usuario_id', $tipoUsuariosHabilitados);

        } elseif ($rolActivo->hasPermissionTo('consolidacion.lista_consolidacion_solo_ministerio')) {

            // Asumiendo que auth()->user()->consolidacion() devuelve una relación o una colección de usuarios,
            // obtenemos sus IDs para filtrar la consulta principal.
            $idsPersonasDelMinisterio = auth()->user()->consolidacion()->pluck('id');
            $personasQuery->whereIn('id', $idsPersonasDelMinisterio);
        } else {
            // Si no tiene ninguno de los permisos, forzamos a que no devuelva resultados.
            $personasQuery->whereRaw('1=2');
        }

        if (isset($configuracion->edad_minima_consolidacion) && is_numeric($configuracion->edad_minima_consolidacion)) {

            $edadMinima = (int) $configuracion->edad_minima_consolidacion;

            // Solo aplicamos el filtro si la edad mínima es mayor que 0
            if ($edadMinima > 0) {
                // Usamos whereRaw para aplicar la función de edad de PostgreSQL
                // y pasamos el valor como un "binding" (?) para seguridad.
                $personasQuery->whereRaw('EXTRACT(YEAR FROM AGE(fecha_nacimiento)) >= ?', [$edadMinima]);
            }
        }

        // Calculamos los indicadores ANTES de aplicar los filtros de tipo ('todos', 'sin-tareas')
        // Clonamos la consulta para no afectarla.
        $indicadoresQuery = clone $personasQuery;
        $indicadoresGenerales = [];

        $item = new stdClass;
        $item->nombre = 'Todas';
        $item->url = 'todos';
        $item->cantidad = (clone $indicadoresQuery)->count(); // Usamos clone para no alterar la consulta
        $item->color = '#fff';
        $item->icono = 'ti ti-asterisk';
        $indicadoresGenerales[] = $item;

        $item = new stdClass;
        $item->nombre = 'Sin tareas';
        $item->url = 'sin-tareas';
        $item->cantidad = (clone $indicadoresQuery)->doesntHave('tareasConsolidacion')->count();
        $item->color = '#fff';
        $item->icono = 'ti ti-user-off';
        $indicadoresGenerales[] = $item;

        $filtrosDinamicos = FiltroConsolidacion::with('condiciones')->orderBy('orden')->get();

        foreach ($filtrosDinamicos as $filtro) {
            $queryParaContar = clone $indicadoresQuery;

            $estadosCivilesFiltro = $filtro->estadosCiviles()->pluck('estados_civiles.id')->toArray();

            // Aquí filtro por los estados civiles del filtro
            if ($estadosCivilesFiltro) {
                $queryParaContar->whereIn('estado_civil_id', $estadosCivilesFiltro);
            }

            foreach ($filtro->condiciones as $condicion) {

                // --- INICIO DE LA LÓGICA IF/ELSE ---
                if ($condicion->pivot->incluir) {
                    // Si es INCLUIR, usamos whereHas
                    $queryParaContar->whereHas('tareasConsolidacion', function ($subQuery) use ($condicion) {
                        $subQuery->where('tareas_consolidacion.id', $condicion->id)
                            ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $condicion->pivot->estado_tarea_consolidacion_id);
                    });
                } else {
                    // Si es EXCLUIR, usamos whereDoesntHave
                    $queryParaContar->whereDoesntHave('tareasConsolidacion', function ($subQuery) use ($condicion) {
                        $subQuery->where('tareas_consolidacion.id', $condicion->id)
                            ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $condicion->pivot->estado_tarea_consolidacion_id);
                    });
                }
                // --- FIN DE LA LÓGICA IF/ELSE ---
            }

            $item = new stdClass;
            $item->nombre = $filtro->nombre;
            $item->url = 'filtro-'.$filtro->id;
            $item->cantidad = $queryParaContar->count();
            $item->color = $filtro->color ?? '#fff';
            $item->icono = $filtro->icono ?? 'ti ti-filter';
            $indicadoresGenerales[] = $item;
        }

        //  APLICAMOS LOS FILTROS DE TIPO
        if ($tipo == 'sin-tareas') {
            $personasQuery->doesntHave('tareasConsolidacion');

            // ----> APLICACIÓN DE FILTROS DINÁMICOS (ACTUALIZADO) <----
        } elseif (str_starts_with($tipo, 'filtro-')) {
            $filtroId = substr($tipo, 7);
            $filtro = FiltroConsolidacion::with('condiciones')->find($filtroId);

            if ($filtro) {

                $estadosCivilesFiltro = $filtro->estadosCiviles()->pluck('estados_civiles.id')->toArray();

                // Aquí filtro por los estados civiles del filtro
                if ($estadosCivilesFiltro) {
                    $personasQuery->whereIn('estado_civil_id', $estadosCivilesFiltro);
                }

                foreach ($filtro->condiciones as $condicion) {

                    // --- INICIO DE LA LÓGICA IF/ELSE ---
                    if ($condicion->pivot->incluir) {
                        // Si es INCLUIR, usamos whereHas
                        $personasQuery->whereHas('tareasConsolidacion', function ($subQuery) use ($condicion) {
                            $subQuery->where('tareas_consolidacion.id', $condicion->id)
                                ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $condicion->pivot->estado_tarea_consolidacion_id);
                        });
                    } else {
                        // Si es EXCLUIR, usamos whereDoesntHave
                        $personasQuery->whereDoesntHave('tareasConsolidacion', function ($subQuery) use ($condicion) {
                            $subQuery->where('tareas_consolidacion.id', $condicion->id)
                                ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $condicion->pivot->estado_tarea_consolidacion_id);
                        });
                    }
                    // --- FIN DE LA LÓGICA IF/ELSE ---
                }
            }
        }

        $personasQuery = $this->filtrosBusqueda($personasQuery, $parametrosBusqueda);

        // 5. Finalmente, ordenamos y paginamos
        $personas = $personasQuery->orderBy('id', 'desc')->paginate(12);
        $indicadoresGenerales = collect($indicadoresGenerales);

        // Obtenemos todas las tareas marcadas como 'default' para pasarlas a la vista.
        $tareasDefault = TareaConsolidacion::where('default', true)->orderBy('orden')->get();

        $estados = EstadoTareaConsolidacion::orderBy('puntaje', 'asc')->get();

        return view('contenido.paginas.consolidacion.listar', [
            'rolActivo' => $rolActivo,
            'personas' => $personas,
            'configuracion' => $configuracion,
            'tareasDefault' => $tareasDefault,
            // 'filtroFechaIni' => $filtroFechaIni,
            // 'filtroFechaFin' => $filtroFechaFin,
            'meses' => $meses,
            'estados' => $estados,
            'indicadoresGenerales' => $indicadoresGenerales,
            'parametrosBusqueda' => $parametrosBusqueda,
            'tipo' => $tipo,
            'sedes' => $sedes,
            'tiposUsuarios' => $tiposUsuarios,
            'rangosEdad' => $rangosEdad,
            'estadosCiviles' => $estadosCiviles,
            'tiposVinculaciones' => $tiposVinculaciones,
            'pasosCrecimiento' => $pasosCrecimiento,
            'estadosPasosDeCrecimiento' => $estadosPasosDeCrecimiento,
            'ocupaciones' => $ocupaciones,
            'nivelesAcademicos' => $nivelesAcademicos,
            'estadosNivelAcademico' => $estadosNivelAcademico,
            'profesiones' => $profesiones,
        ]);
    }

    public function filtrosBusqueda($personas, $parametrosBusqueda)
    {
        // /si el usuario ejecutó una busqueda se añaden las consultas necesarias
        if ($parametrosBusqueda->buscar != '') {
            $buscarSaneado = htmlspecialchars($parametrosBusqueda->buscar);
            $buscarSaneado = Helpers::sanearStringConEspacios($parametrosBusqueda->buscar);
            $buscar = str_replace(["'"], '', $parametrosBusqueda->buscar);

            $personas->where(function ($q) use ($buscarSaneado, $buscar) {
                $q->whereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.segundo_nombre, users.primer_apellido, users.segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.segundo_apellido, users.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw('LOWER(users.email) LIKE LOWER(?)', ['%'.$buscar.'%'])
                    ->orWhereRaw('LOWER(users.identificacion) LIKE LOWER(?)', [$buscar.'%']);
            });

            $parametrosBusqueda->textoBusqueda .= '<b>, Con busqueda: </b>"'.$buscar.'" ';
            $parametrosBusqueda->bandera = 1;

            // Crear una tag
            $tag = new stdClass;
            $tag->label = $parametrosBusqueda->buscar;
            $tag->field = 'filtroBuscar';
            $tag->value = $buscar;
            $tag->fieldAux = '';
            $parametrosBusqueda->tagsBusqueda[] = $tag;
        }

        // Filtro por sexo
        $personas = $this->filtrarSexo($personas, $parametrosBusqueda);

        // Filtro por tipo de usuario
        $personas = $this->filtroPorTipoUsuario($personas, $parametrosBusqueda);

        // Filtro por rango de edad
        $personas = $this->filtrarEdad($personas, $parametrosBusqueda);

        // Filtro por esatdos civiles
        $personas = $this->filtrarEstadoCivil($personas, $parametrosBusqueda);

        // Filtro por tipo vinculacion
        $personas = $this->filtrarTipoVinculacion($personas, $parametrosBusqueda);

        // Filtro por ocupacion
        $personas = $this->filtrarOcupacion($personas, $parametrosBusqueda);

        // Filtro por nivel academico
        $personas = $this->filtrarNivelAcademico($personas, $parametrosBusqueda);

        // Filtro por estado nivel academico
        $personas = $this->filtrarEstadoNivelAcademico($personas, $parametrosBusqueda);

        // Filtro por profesion
        $personas = $this->filtrarProfesion($personas, $parametrosBusqueda);

        // Filtro por sedes
        $personas = $this->filtrarPorSede($personas, $parametrosBusqueda);

        return $personas;
    }

    public function filtroPorTipoUsuario($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorTipoDeUsuario) {
            $personas = $personas->whereIn('tipo_usuario_id', $parametrosBusqueda->filtroPorTipoDeUsuario);

            $tiposUsuarios = TipoUsuario::select('id', 'nombre')
                ->whereIn('id', $parametrosBusqueda->filtroPorTipoDeUsuario)
                ->pluck('nombre')
                ->toArray();

            $parametrosBusqueda->textoBusqueda .= '<b>, Tipos de usuario: </b>"'.implode(', ', $tiposUsuarios).'"';
            $parametrosBusqueda->bandera = 1;

            // Crear las tags para cada tipo de usuario

            $tiposUsuariosSeleccionados = TipoUsuario::whereIn('id', $parametrosBusqueda->filtroPorTipoDeUsuario)->get();
            foreach ($tiposUsuariosSeleccionados as $tipoUsuario) {
                $tag = new stdClass;
                $tag->label = $tipoUsuario->nombre;
                $tag->field = 'filtroPorTipoDeUsuario';
                $tag->value = $tipoUsuario->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }
        }

        return $personas;
    }

    public function filtrarEdad($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorRangoEdad) {
            $rangos = RangoEdad::whereIn('id', $parametrosBusqueda->filtroPorRangoEdad)->get();
            $edadesPermitidas = [];

            $parametrosBusqueda->textoBusqueda .=
              '<b>, Edades: </b>"'.implode(', ', $rangos->pluck('nombre')->toArray()).'"';
            $parametrosBusqueda->bandera = 1;

            foreach ($rangos as $rango) {
                for ($x = $rango->edad_minima; $x <= $rango->edad_maxima; $x++) {
                    $edadesPermitidas[] = $x;
                }

                // Crear una tag por cada rango de edad
                $tag = new stdClass;
                $tag->label = $rango->nombre;
                $tag->field = 'filtroPorRangoEdad';
                $tag->value = $rango->id; // Usamos el ID del rango como valor
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }

            /*$personas = $personas->filter(function ($persona) use ($edadesPermitidas) {
              $edadPersona = Carbon::parse($persona->fecha_nacimiento)->age;
              return in_array($edadPersona, $edadesPermitidas);
            });*/

            $personas = $personas->where(function ($query) use ($rangos) {
                // Preparamos la expresión SQL para calcular la edad en PostgreSQL
                $sqlCalculoEdad = DB::raw('EXTRACT(YEAR FROM AGE(fecha_nacimiento))');

                // Por cada rango, añadimos una condición 'OR WHERE'
                //    Ej: (edad BETWEEN 18 AND 25) OR (edad BETWEEN 30 AND 40)
                foreach ($rangos as $rango) {
                    $query->orWhereBetween($sqlCalculoEdad, [$rango->edad_minima, $rango->edad_maxima]);
                }
            });
        }

        return $personas;
    }

    public function filtrarSexo($personas, $parametrosBusqueda)
    {
        if (is_numeric($parametrosBusqueda->filtroPorSexo)) {
            $personas = $personas->where('genero', '=', $parametrosBusqueda->filtroPorSexo);

            $parametrosBusqueda->textoBusqueda .= $parametrosBusqueda->filtroPorSexo == 0 ? '<b>, Sexo: </b> Hombres' : '<b>, Sexo:</b> Mujeres';
            $sexoLabel = $parametrosBusqueda->filtroPorSexo == 0 ? 'Hombre' : 'Mujer';

            $parametrosBusqueda->bandera = 1;

            $tag = new stdClass;
            $tag->label = $sexoLabel;
            $tag->field = 'filtroPorSexo';
            $tag->value = $parametrosBusqueda->filtroPorSexo; // Guardar el valor del filtro
            $tag->fieldAux = '';
            $parametrosBusqueda->tagsBusqueda[] = $tag;
        }

        return $personas;
    }

    public function filtrarEstadoCivil($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorEstadosCiviles) {
            $personas = $personas->whereIn('estado_civil_id', $parametrosBusqueda->filtroPorEstadosCiviles);

            $estadosCiviles = EstadoCivil::whereIn('id', $parametrosBusqueda->filtroPorEstadosCiviles)
                ->select('nombre')
                ->pluck('nombre')
                ->toArray();

            $parametrosBusqueda->textoBusqueda .= '<b>, Estados civiles: </b>"'.implode(', ', $estadosCiviles).'"';
            $parametrosBusqueda->bandera = 1;

            // Crear las tags para cada estado civil
            $estadosCivilesSeleccionados = EstadoCivil::whereIn('id', $parametrosBusqueda->filtroPorEstadosCiviles)->get();
            foreach ($estadosCivilesSeleccionados as $estadoCivil) {
                $tag = new stdClass;
                $tag->label = $estadoCivil->nombre;
                $tag->field = 'filtroPorEstadosCiviles';
                $tag->value = $estadoCivil->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }
        }

        return $personas;
    }

    public function filtrarTipoVinculacion($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorTiposVinculaciones) {
            $personas = $personas->whereIn('tipo_vinculacion_id', $parametrosBusqueda->filtroPorTiposVinculaciones);

            $tiposVinculacion = TipoVinculacion::whereIn('id', $parametrosBusqueda->filtroPorTiposVinculaciones)
                ->select('nombre')
                ->pluck('nombre')
                ->toArray();

            $parametrosBusqueda->textoBusqueda .= '<b>, Tipos de vinculación:</b> "'.implode(', ', $tiposVinculacion).'"';
            $parametrosBusqueda->bandera = 1;

            // Crear las tags para cada tipo de vinculación
            $tiposVinculacionSeleccionados = TipoVinculacion::whereIn('id', $parametrosBusqueda->filtroPorTiposVinculaciones)->get();
            foreach ($tiposVinculacionSeleccionados as $tipoVinculacion) {
                $tag = new stdClass;
                $tag->label = $tipoVinculacion->nombre;
                $tag->field = 'filtroPorTiposVinculaciones';
                $tag->value = $tipoVinculacion->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }
        }

        return $personas;
    }

    public function filtrarPasoCrecimiento($numeroFiltro, $personas, $pasosCrecimiento, $estado, $fechaInicio, $fechaFin, $parametrosBusqueda)
    {
        if ($pasosCrecimiento) {
            $pasosDeCrecimiento = PasoCrecimiento::whereIn('id', $pasosCrecimiento)
                ->select('nombre')
                ->pluck('nombre')
                ->toArray();

            $parametrosBusqueda->textoBusqueda .= ', <b>Pasos de crecimiento';

            $personasPasoCrecimiento = CrecimientoUsuario::whereIn('paso_crecimiento_id', $pasosCrecimiento);
            $parametrosBusqueda->textoBusqueda .= '[ ';
            if ($fechaInicio && $fechaFin) {
                $personasPasoCrecimiento = $personasPasoCrecimiento->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                $parametrosBusqueda->textoBusqueda .= ' Del '.$fechaInicio.' al '.$fechaFin.' | ';
            }

            $estadoSeleccionado = EstadoPasoCrecimientoUsuario::find($estado);
            if ($estadoSeleccionado) {
                $parametrosBusqueda->textoBusqueda .= 'Estado '.$estadoSeleccionado->nombre.' ]:';

                if ($estadoSeleccionado->default) {
                    $arrayIdsTodosEstados = EstadoPasoCrecimientoUsuario::where('default', false)
                        ->select('id')
                        ->pluck('id')
                        ->toArray();

                    $personasPasoCrecimiento = $personasPasoCrecimiento->whereNotIn('estado_id', $arrayIdsTodosEstados);
                } else {
                    $personasPasoCrecimiento = $personasPasoCrecimiento->where('estado_id', $estadoSeleccionado->id);
                }
            }

            $parametrosBusqueda->textoBusqueda .= '</b>';

            $parametrosBusqueda->textoBusqueda .= '"'.implode(', ', $pasosDeCrecimiento).'"';
            $parametrosBusqueda->bandera = 1;

            $idUserPasoCrecimiento = $personasPasoCrecimiento
                ->select('user_id')
                ->pluck('user_id')
                ->toArray();

            // Crear las tags para cada paso de crecimiento
            $pasosCrecimientoSeleccionados = PasoCrecimiento::whereIn('id', $pasosCrecimiento)->get();
            foreach ($pasosCrecimientoSeleccionados as $paso) {
                $tag = new stdClass;
                $tag->label = 'Paso '.$numeroFiltro.': '.$paso->nombre;
                $tag->field = 'filtroPorPasosCrecimiento'.$numeroFiltro; // o 'filtroPorPasosCrecimiento2', dependiendo de cuál se esté usando
                $tag->value = $paso->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }

            // Crear las tags para estado del paso de crecimiento
            if ($estadoSeleccionado) {
                $tag = new stdClass;
                $tag->label = 'Estado paso '.$numeroFiltro.': '.$estadoSeleccionado->nombre;
                $tag->field = 'filtroEstadoPasos'.$numeroFiltro; // o 'filtroEstadoPasos2', dependiendo de cuál se esté usando
                $tag->fieldAux = '';
                $tag->value = $paso->id;
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }

            if ($fechaInicio && $fechaFin) {
                $tag = new stdClass;
                $tag->label = 'Rango paso '.$numeroFiltro.': '.$fechaInicio.' a '.$fechaFin;
                $tag->field = 'filtroFechaIniPaso'.$numeroFiltro; // o 'filtroEstadoPasos2', dependiendo de cuál se esté usando
                $tag->fieldAux = 'filtroFechaFinPaso'.$numeroFiltro;
                $tag->value = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }

            $personas = $personas->whereIn('id', $idUserPasoCrecimiento);
        }

        return $personas;
    }

    public function filtrarOcupacion($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorOcupacion) {
            $personas = $personas->whereIn('ocupacion_id', $parametrosBusqueda->filtroPorOcupacion);

            $ocupaciones = Ocupacion::whereIn('id', $parametrosBusqueda->filtroPorOcupacion)
                ->select('nombre')
                ->pluck('nombre')
                ->toArray();

            $parametrosBusqueda->textoBusqueda .= '<b>, Ocupaciones: </b>"'.implode(', ', $ocupaciones).'"';
            $parametrosBusqueda->bandera = 1;

            // Crear las tags para cada ocupación
            $ocupacionesSeleccionadas = Ocupacion::whereIn('id', $parametrosBusqueda->filtroPorOcupacion)->get();
            foreach ($ocupacionesSeleccionadas as $ocupacion) {
                $tag = new stdClass;
                $tag->label = $ocupacion->nombre;
                $tag->field = 'filtroPorOcupacion';
                $tag->value = $ocupacion->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }
        }

        return $personas;
    }

    public function filtrarNivelAcademico($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorNivelAcademico) {
            $personas = $personas->whereIn('nivel_academico_id', $parametrosBusqueda->filtroPorNivelAcademico);

            $nivelesAcademicos = NivelAcademico::whereIn('id', $parametrosBusqueda->filtroPorNivelAcademico)
                ->select('nombre')
                ->pluck('nombre')
                ->toArray();

            $parametrosBusqueda->textoBusqueda .= ', <b>Niveles académicos: </b>"'.implode(', ', $nivelesAcademicos).'"';
            $parametrosBusqueda->bandera = 1;

            // Crear las tags para cada nivel académico
            $nivelesAcademicosSeleccionados = NivelAcademico::whereIn('id', $parametrosBusqueda->filtroPorNivelAcademico)->get();
            foreach ($nivelesAcademicosSeleccionados as $nivelAcademico) {
                $tag = new stdClass;
                $tag->label = $nivelAcademico->nombre;
                $tag->field = 'filtroPorNivelAcademico';
                $tag->value = $nivelAcademico->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }
        }

        return $personas;
    }

    public function filtrarEstadoNivelAcademico($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorEstadoNivelAcademico) {
            $personas = $personas->where(
                'estado_nivel_academico_id',
                '=',
                $parametrosBusqueda->filtroPorEstadoNivelAcademico
            );

            $estadoNivelAcademico = EstadoNivelAcademico::where('id', $parametrosBusqueda->filtroPorEstadoNivelAcademico)->first();

            $parametrosBusqueda->textoBusqueda .=
              '<b>, Estados niveles académicos: </b>"'.$estadoNivelAcademico->nombre.'"';
            $parametrosBusqueda->bandera = 1;

            // Crear la tag para el estado del nivel académico
            if ($estadoNivelAcademico) {
                $tag = new stdClass;
                $tag->label = 'Estado nivel académico: '.$estadoNivelAcademico->nombre;
                $tag->field = 'filtroPorEstadoNivelAcademico';
                $tag->value = $estadoNivelAcademico->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }
        }

        return $personas;
    }

    public function filtrarProfesion($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorProfesion) {
            $personas = $personas->where('profesion_id', '=', $parametrosBusqueda->filtroPorProfesion);

            $profesiones = Profesion::whereIn('id', $parametrosBusqueda->filtroPorProfesion)
                ->select('nombre')
                ->pluck('nombre')
                ->toArray();

            $parametrosBusqueda->textoBusqueda .= '<b>, Profesiones: </b>"'.implode(', ', $profesiones).'"';
            $parametrosBusqueda->bandera = 1;

            // Crear la tag para la profesión
            $profesionesSeleccionadas = Profesion::whereIn('id', $parametrosBusqueda->filtroPorProfesion)->get();
            foreach ($profesionesSeleccionadas as $profesion) {
                $tag = new stdClass;
                $tag->label = $profesion->nombre;
                $tag->field = 'filtroPorProfesion';
                $tag->value = $profesion->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }
        }

        return $personas;
    }

    public function filtrarPorSede($personas, $parametrosBusqueda)
    {
        if ($parametrosBusqueda->filtroPorSede) {
            $personas = $personas->whereIn('sede_id', $parametrosBusqueda->filtroPorSede);

            $sedes = Sede::whereIn('id', $parametrosBusqueda->filtroPorSede)
                ->select('nombre')
                ->pluck('nombre')
                ->toArray();

            $parametrosBusqueda->textoBusqueda .= '<b>, Sedes: </b>"'.implode(', ', $sedes).'"';
            $parametrosBusqueda->bandera = 1;

            // Crear la tag para la sede
            $sedesSeleccionadas = Sede::whereIn('id', $parametrosBusqueda->filtroPorSede)->get();
            foreach ($sedesSeleccionadas as $sede) {
                $tag = new stdClass;
                $tag->label = $sede->nombre;
                $tag->field = 'filtroPorSede';
                $tag->value = $sede->id;
                $tag->fieldAux = '';
                $parametrosBusqueda->tagsBusqueda[] = $tag;
            }
        }

        return $personas;
    }

    public function gestionarTareas(User $usuario)
    {
        // return HistorialTareaConsolidacionUsuario::orderBy('id', 'desc')->get();
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consolidacion.gestionar_tareas');

        return view('contenido.paginas.consolidacion.gestionar-tareas', [
            'usuario' => $usuario,
        ]);
    }

    public function dashboard(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consolidacion.dashboard_consolidacion');

        //return Matricula::get();
        //return User::find(17);

       /* $matricula = Matricula::firstOrCreate([
                    "id"=> 1,
                    "user_id"=> 17,
                    "periodo_id"=> 1,
                    "horario_materia_periodo_id"=> 1,
                    "referencia_pago"=> null,
                    "valor_a_pagar"=> null,
                    "valor_pagado"=> null,
                    "fecha_pago"=> null,
                    "estado_pago_matricula"=> "finalizada",
                    "observacion"=> null,
                    "fecha_matricula"=> "2026-04-08T05:00:00.000000Z",
                    "sede_id"=> 1,
                    "material_sede_id"=> 2,
                    "trasladado"=> false,
                    "fecha_bloqueo"=> null,
                    "bloqueado"=> false,
                    "escuela_id"=> 1,
                    "tipo_pago_id"=> null
                ]);

                return $matricula;*/

        // Lógica para Rango de Fechas (Semanas)
        $rangoFechas = $request->rango_fechas;

        if ($rangoFechas) {
            $fechas = explode(' a ', $rangoFechas);
            if (count($fechas) >= 2) {
                $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
                $fin = Carbon::parse(trim($fechas[1]))->endOfDay();
            } else {
                $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
                $fin = Carbon::parse(trim($fechas[0]))->endOfDay();
            }
        } else {
            // Default: Este mes
            $inicio = Carbon::now()->startOfMonth();
            $fin = Carbon::now()->endOfMonth();
            $rangoFechas = $inicio->format('Y-m-d').' a '.$fin->format('Y-m-d');
        }

        // --- LÓGICA DE FILTROS Y VISTAS ---

        // 1. Verificar si estamos en "Vista Detalle" (Drill Down)
        $bloqueDetalleId = $request->bloque_detalle_id ?? null;
        $esVistaDetalle = ! empty($bloqueDetalleId);
        $bloqueActual = null;
        $sedesDisponibles = collect();
        $sedesSeleccionadas = [];

        // Switch de filtros
        $bloquesDisponibles = collect();
        $bloquesSeleccionados = [];

        // IDs finales sobre los cuales filtrar la data general
        $sedesIdsFiltrar = [];

        // DATA PARA LA VISTA
        $datosDesglose = []; // Ya sea por Bloque o por Sede
        $tipoDesglose = 'bloque'; // 'bloque' o 'sede'

        $esPeticionFiltro = $request->has('rango_fechas');

        // Caso 1: VISTA DETALLE (Viendo un bloque específico)
        if ($esVistaDetalle) {
            $bloqueActual = BloqueDashboardConsolidacion::with('sedes')->find($bloqueDetalleId);

            if ($bloqueActual) {
                $tipoDesglose = 'sede';
                $sedesDisponibles = $bloqueActual->sedes; // Sedes de ESTE bloque

                // Si el selector no está en el request, asumimos "seleccionar todo" (cambio de vista o primer ingreso).
                if ($request->has('sedes_seleccionadas')) {
                    $sedesSeleccionadas = $request->sedes_seleccionadas;
                } else {
                    $sedesSeleccionadas = $sedesDisponibles->pluck('id')->toArray();
                }

                // IDs filtrar son exactamente los seleccionados (que son validos para este bloque)
                // Filtramos $sedesSeleccionadas para asegurar que pertenezcan al bloque (seguridad)
                $sedesIdsFiltrar = $sedesDisponibles->whereIn('id', $sedesSeleccionadas)->pluck('id')->toArray();

            } else {
                // Si el bloque no existe, volver a vista general (fallback)
                $esVistaDetalle = false;
            }
        }

        // Caso 2: VISTA GENERAL (Viendo todos los bloques)
        if (! $esVistaDetalle) {
            $bloquesDisponibles = BloqueDashboardConsolidacion::with('sedes')->get();

            if ($request->has('bloques_seleccionados')) {
                $bloquesSeleccionados = $request->bloques_seleccionados;
            } else {
                $bloquesSeleccionados = $bloquesDisponibles->pluck('id')->toArray();
            }

            if (! empty($bloquesSeleccionados)) {
                $bloquesFiltrados = $bloquesDisponibles->whereIn('id', $bloquesSeleccionados);
                foreach ($bloquesFiltrados as $bloque) {
                    $sedesIdsFiltrar = array_merge($sedesIdsFiltrar, $bloque->sedes->pluck('id')->toArray());
                }
            }
        }

        $sedesIdsFiltrar = array_unique($sedesIdsFiltrar);

        // --- CALLBACK DE FILTRO GENERAL (Aplica para ambos casos) ---
        $filtroSedesCallback = function ($query) use ($inicio, $fin, $sedesIdsFiltrar) {
            if (! empty($sedesIdsFiltrar)) {
                $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sedesIdsFiltrar) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereIn('sede_id_nuevo', $sedesIdsFiltrar)
                        ->whereRaw('id = (
                        SELECT MAX(bs.id) 
                        FROM bitacora_sedes as bs
                        WHERE bs.user_id = bitacora_sedes.user_id 
                        AND bs.created_at BETWEEN ? AND ?
                    )', [$inicio, $fin]);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        };

        // --- CÁLCULOS GLOBALES (Afectados por el filtro actual) ---
        $totalCosecha = User::withTrashed()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });
            })
            ->tap($filtroSedesCallback)
            ->count();

        // Lógica para Cosecha Efectiva
        $cosechaEfectiva = User::withTrashed()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });
            })
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin]);
                })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
            })
            ->tap($filtroSedesCallback)
            ->count();

        $cosechaDesercion = $totalCosecha - $cosechaEfectiva;

        $porcentajeEfectividad = $totalCosecha > 0 ? round(($cosechaEfectiva / $totalCosecha) * 100, 2) : 0;

        // Vinculaciones Globales
        $userIdsCosecha = User::withTrashed()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });
            })
            ->tap($filtroSedesCallback)
            ->pluck('id');

        $vinculacionesCosecha = TipoVinculacion::withCount(['usuarios' => function ($query) use ($userIdsCosecha) {
            $query->withTrashed()->whereIn('users.id', $userIdsCosecha);
        }])->get();

        // --- Helpers para Desglose y Métricas ---

        $limiteEdad = Configuracion::where('id', 1)->value('limite_menor_edad') ?? 18;

        $calcDistribucion = function ($coleccion, $limite) {
            $adultos = 0;
            $menores = 0;
            foreach ($coleccion as $m) {
                if ($m->user && $m->user->fecha_nacimiento) {
                    $fechaMatricula = Carbon::parse($m->fecha_matricula);
                    $edad = $m->user->fecha_nacimiento->diffInYears($fechaMatricula);
                    if ($edad < $limite) {
                        $menores++;
                    } else {
                        $adultos++;
                    }
                } else {
                    $adultos++;
                }
            }

            return ['adultos' => $adultos, 'menores' => $menores];
        };

        $calcDistribucionUsuarios = function ($coleccion, $limite, $fechaBase = null) {
            $adultos = 0;
            $menores = 0;
            $fechaRef = $fechaBase ? Carbon::parse($fechaBase) : Carbon::now();
            foreach ($coleccion as $u) {
                if ($u->fecha_nacimiento) {
                    // Edad al momento de la referencia (inicio del periodo o fecha actual)
                    $edad = $u->fecha_nacimiento->diffInYears($fechaRef);
                    if ($edad < $limite) {
                        $menores++;
                    } else {
                        $adultos++;
                    }
                } else {
                    $adultos++; // Fallback
                }
            }
            return ['adultos' => $adultos, 'menores' => $menores];
        };

        // --- Lógica para Desglose (Bloques o Sedes) ---

        // Helper para calcular métricas de un conjunto de IDs de Sede
        $calcularMetricasIdsSedes = function ($idsSedes) use ($inicio, $fin) {
            // Callback local
            $filtroLocal = function ($query) use ($inicio, $fin, $idsSedes) {
                if (! empty($idsSedes)) {
                    $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $idsSedes) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereIn('sede_id_nuevo', $idsSedes)
                            ->whereRaw('id = (SELECT MAX(bs.id) FROM bitacora_sedes as bs WHERE bs.user_id = bitacora_sedes.user_id AND bs.created_at BETWEEN ? AND ?)', [$inicio, $fin]);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            };

            $total = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?) AND tipo_usuario_id_nuevo IN (SELECT id FROM tipos_usuario WHERE habilitado_para_consolidacion = true OR es_miembro_oficial = true)', [$inicio, $fin]);
                    });
                })->tap($filtroLocal)->count();

            return $total;
        };

        // Por simplicidad, rehago el calculo completo iterativo para asegurar consistencia con el código anterior
        // pero adaptado al tipo de desglose

        $itemsAProcesar = []; // Lista de objetos (Bloques o Sedes)

        if ($tipoDesglose == 'bloque') {
            $itemsAProcesar = $bloquesDisponibles->whereIn('id', $bloquesSeleccionados);
        } else {
            // En vista detalle, mostramos las sedes FILTRADAS.
            // Si todas, son todas las del bloque. Si seleccionó, son subset.
            $itemsAProcesar = $sedesDisponibles->whereIn('id', $sedesIdsFiltrar);
        }

        foreach ($itemsAProcesar as $item) {
            // Determinar IDs de sede para este item específico
            $sedesItemIds = [];
            if ($tipoDesglose == 'bloque') {
                $sedesItemIds = $item->sedes->pluck('id')->toArray();
            } else {
                $sedesItemIds = [$item->id];
            }

            // Callback item
            $filtroItem = function ($query) use ($inicio, $fin, $sedesItemIds) {
                if (! empty($sedesItemIds)) {
                    $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sedesItemIds) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereIn('sede_id_nuevo', $sedesItemIds)
                            ->whereRaw('id = (SELECT MAX(bs.id) FROM bitacora_sedes as bs WHERE bs.user_id = bitacora_sedes.user_id AND bs.created_at BETWEEN ? AND ?)', [$inicio, $fin]);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            };

            // 1. Total
            $totalItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where(function ($q2) {
                                    $q2->where('habilitado_para_consolidacion', true)
                                       ->orWhere('es_miembro_oficial', true);
                                });
                            });
                    });
                })->tap($filtroItem)->count();

            // 2. Efectiva
            $efectivaItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where(function ($q2) {
                                    $q2->where('habilitado_para_consolidacion', true)
                                       ->orWhere('es_miembro_oficial', true);
                                });
                            });
                    });
                })
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                        ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                            $sub->whereBetween('created_at', [$inicio, $fin])->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])->where('dado_baja', false);
                        });
                })->tap($filtroItem)->count();

            // 3. Vinculaciones
            $idsItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('habilitado_para_consolidacion', true)
                                    ->orWhere('es_miembro_oficial', true);
                            });
                    });
                })->tap($filtroItem)->pluck('id');

            $vinculacionesItem = TipoVinculacion::withCount(['usuarios' => function ($query) use ($idsItem) {
                $query->withTrashed()->whereIn('users.id', $idsItem);
            }])->get();

            // --- DATOS ESCUELAS (Desglose) ---
            $subQueryLatestDateItem = Matricula::whereIn('user_id', $idsItem)
                ->whereBetween('fecha_matricula', [$inicio, $fin])
                ->select('user_id', DB::raw('MAX(fecha_matricula) as max_fecha'))
                ->groupBy('user_id');

            $latestMatriculaIdsItem = Matricula::joinSub($subQueryLatestDateItem, 'latest_dates_item', function ($join) {
                $join->on('matriculas.user_id', '=', 'latest_dates_item.user_id')
                    ->on('matriculas.fecha_matricula', '=', 'latest_dates_item.max_fecha');
            })
                ->select(DB::raw('MAX(matriculas.id) as max_id'))
                ->groupBy('matriculas.user_id')
                ->pluck('max_id');

            $matriculasCollectionItem = Matricula::whereIn('id', $latestMatriculaIdsItem)
                ->whereHas('escuela', function ($q) {
                    $q->where('habilitada_consolidacion', true);
                })
                ->with(['horarioMateriaPeriodo.horarioBase.aula.tipo', 'user:id,fecha_nacimiento'])
                ->get();

            $totalMatriculasItem = $matriculasCollectionItem->count();
            $userIdsMatriculadosItem = $matriculasCollectionItem->pluck('user_id')->unique();

            // Sector vs Templo
            $sectorItem = $matriculasCollectionItem->filter(function ($m) {
                return optional(optional(optional(optional($m->horarioMateriaPeriodo)->horarioBase)->aula)->tipo)->sector == true;
            });
            $temploItem = $matriculasCollectionItem->filter(function ($m) {
                return optional(optional(optional(optional($m->horarioMateriaPeriodo)->horarioBase)->aula)->tipo)->sector == false;
            });

            $matriculasSectorItem = $sectorItem->count();
            $matriculasTemploItem = $temploItem->count();

            // Edades
            $distSectorItem = $calcDistribucion($sectorItem, $limiteEdad);
            $distTemploItem = $calcDistribucion($temploItem, $limiteEdad);
            $sectorAdultosItem = $distSectorItem['adultos'];
            $sectorMenoresItem = $distSectorItem['menores'];
            $temploAdultosItem = $distTemploItem['adultos'];
            $temploMenoresItem = $distTemploItem['menores'];

            // Effectiveness
            $matriculasDesercionesItem = $matriculasCollectionItem->where('bloqueado', true)->count();
            $matriculasEfectivosItem = $totalMatriculasItem - $matriculasDesercionesItem;
            $porcentajeEfectividadMatriculasItem = $totalMatriculasItem > 0 ? round(($matriculasEfectivosItem / $totalMatriculasItem) * 100, 2) : 0;

            // --- GRÁFICAS POR ITEM (Semanal y Vinculación) ---

            // 1. Cosecha Semanal Item
            $fechasCosechaItem = User::withTrashed()
                ->whereBetween('created_at', [$inicio, $fin])
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('habilitado_para_consolidacion', true);
                            });
                    });
                })->tap($filtroItem)->pluck('created_at');

            // 2. Desercion Semanal Item
            $fechasDesercionItem = User::withTrashed()
                ->whereBetween('created_at', [$inicio, $fin])
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('habilitado_para_consolidacion', true);
                            });
                    });
                })
                ->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->where('dado_baja', true);
                })->tap($filtroItem)->pluck('created_at');

            $cosechaPorSemanaItem = $fechasCosechaItem->groupBy(function ($d) {
                return Carbon::parse($d)->startOfWeek()->format('Y-m-d');
            });
            $desercionPorSemanaItem = $fechasDesercionItem->groupBy(function ($d) {
                return Carbon::parse($d)->startOfWeek()->format('Y-m-d');
            });

            $itemGraficaSemanal = [];
            $periodoItem = \Carbon\CarbonPeriod::create($inicio->copy()->startOfWeek(), '1 week', $fin->copy()->startOfWeek()->max($inicio->copy()->startOfWeek())); // Ensure valid range

            foreach ($periodoItem as $fecha) {
                $lunes = $fecha->format('Y-m-d');
                $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');

                $cant = isset($cosechaPorSemanaItem[$lunes]) ? $cosechaPorSemanaItem[$lunes]->count() : 0;
                $cantDes = isset($desercionPorSemanaItem[$lunes]) ? $desercionPorSemanaItem[$lunes]->count() : 0;

                $itemGraficaSemanal[] = ['x' => $domingoLabel, 'y' => $cant, 'y_desercion' => $cantDes];
            }

            // 3. Vinculacion Semanal Item
            $cosechaVinculadaItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('habilitado_para_consolidacion', true);
                            });
                    });
                })->tap($filtroItem)->select('id', 'created_at', 'tipo_vinculacion_id')->get();

            $agrupadoVincItem = $cosechaVinculadaItem->groupBy(function ($u) {
                return Carbon::parse($u->created_at)->startOfWeek()->format('Y-m-d');
            })
                ->map(function ($s) {
                    return $s->groupBy('tipo_vinculacion_id');
                });

            $itemGraficaVinculacion = ['labels' => [], 'series' => []];
            $tiposVinculacion = TipoVinculacion::all(); // Cached or efficient enough
            foreach ($tiposVinculacion as $tv) {
                $itemGraficaVinculacion['series'][$tv->id] = ['name' => $tv->nombre, 'data' => []];
            }

            foreach ($periodoItem as $fecha) {
                $lunes = $fecha->format('Y-m-d');
                $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
                $itemGraficaVinculacion['labels'][] = $domingoLabel;

                foreach ($tiposVinculacion as $tv) {
                    $c = (isset($agrupadoVincItem[$lunes]) && isset($agrupadoVincItem[$lunes][$tv->id])) ? $agrupadoVincItem[$lunes][$tv->id]->count() : 0;
                    $itemGraficaVinculacion['series'][$tv->id]['data'][] = $c;
                }
            }
            $itemGraficaVinculacion['series'] = array_values($itemGraficaVinculacion['series']);

            // 4. Matriculas Semanal Item
            $fechasMatriculasItem = $matriculasCollectionItem->pluck('fecha_matricula');
            $matriculasPorSemanaItem = $fechasMatriculasItem->groupBy(function ($date) {
                return Carbon::parse($date)->locale('es')->startOfWeek()->format('Y-m-d');
            });

            $itemGraficaMatriculasSemanal = [];
            foreach ($periodoItem as $fecha) {
                $lunes = $fecha->format('Y-m-d');
                $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
                $cant = isset($matriculasPorSemanaItem[$lunes]) ? $matriculasPorSemanaItem[$lunes]->count() : 0;
                $itemGraficaMatriculasSemanal[] = ['x' => $domingoLabel, 'y' => $cant];
            }

            // 4. Bautismos vs Traslados
            $usuariosTrasladosItem = User::withTrashed()->whereIn('id', $idsItem)
                ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where('es_miembro_oficial', true);
                        });
                })
                ->where(function ($query) use ($inicio, $fin) {
                    $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                        ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                            $sub->whereBetween('created_at', [$inicio, $fin])
                                ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                ->where('dado_baja', false);
                        });
                })
                ->whereHas('tipoVinculacion', function ($q) {
                    $q->where('viene_de_otra_iglesia', true);
                })->select('id', 'fecha_nacimiento')->get();

            $usuariosBautismosItem = User::withTrashed()->whereIn('id', $idsItem)
                ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where('es_miembro_oficial', true);
                        });
                })
                ->where(function ($query) use ($inicio, $fin) {
                    $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                        ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                            $sub->whereBetween('created_at', [$inicio, $fin])
                                ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                ->where('dado_baja', false);
                        });
                })
                ->where(function ($query) {
                    $query->whereDoesntHave('tipoVinculacion')
                        ->orWhereHas('tipoVinculacion', function ($q) {
                            $q->where('viene_de_otra_iglesia', false);
                        });
                })->select('id', 'fecha_nacimiento')->get();

            $miembrosTrasladosItem = $usuariosTrasladosItem->count();
            $miembrosBautismosItem = $usuariosBautismosItem->count();

            $distTrasladosItem = $calcDistribucionUsuarios($usuariosTrasladosItem, $limiteEdad, $inicio);
            $trasladosAdultosItem = $distTrasladosItem['adultos'];
            $trasladosMenoresItem = $distTrasladosItem['menores'];

            $distBautismosItem = $calcDistribucionUsuarios($usuariosBautismosItem, $limiteEdad, $inicio);
            $bautismosAdultosItem = $distBautismosItem['adultos'];
            $bautismosMenoresItem = $distBautismosItem['menores'];

            $pendientesUnionLibreItem = User::withTrashed()->whereIn('id', $userIdsMatriculadosItem)
                ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
                    $query->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('estadoCivilNuevo', function ($q) {
                            $q->where('es_union_libre', true);
                        });
                })->count();

            $miembrosFormalizadosItemTotal = User::withTrashed()->whereIn('id', $idsItem)
                ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where('es_miembro_oficial', true);
                        });
                })
                ->where(function ($query) use ($inicio, $fin) {
                    $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                        ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                            $sub->whereBetween('created_at', [$inicio, $fin])
                                ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                ->where('dado_baja', false);
                        });
                })
                ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
                    $query->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b_ec2.id) FROM bitacora_estados_civiles as b_ec2 WHERE b_ec2.user_id = bitacora_estados_civiles.user_id AND b_ec2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('estadoCivilNuevo', function ($q) {
                            $q->where('es_matrimonio', true);
                        });
                })
                ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
                    $query->whereBetween('created_at', [$inicio, $fin])
                        ->whereHas('estadoCivilNuevo', function ($q) {
                            $q->where('es_union_libre', true);
                        });
                })->count();

            $datosDesglose[] = (object) [
                'id' => $item->id,
                'nombre' => $item->nombre,
                'totalCosecha' => $totalItem,
                'cosechaEfectiva' => $efectivaItem,
                'cosechaDesercion' => $totalItem - $efectivaItem,
                'porcentajeEfectividad' => $totalItem > 0 ? round(($efectivaItem / $totalItem) * 100, 2) : 0,
                'vinculacionesCosecha' => $vinculacionesItem,

                // New School Metrics
                'totalMatriculas' => $totalMatriculasItem,
                'matriculasSector' => $matriculasSectorItem,
                'matriculasTemplo' => $matriculasTemploItem,
                'sectorAdultos' => $sectorAdultosItem,
                'sectorMenores' => $sectorMenoresItem,
                'temploAdultos' => $temploAdultosItem,
                'temploMenores' => $temploMenoresItem,
                // Union Libre vs Aptos
                'matriculasUnionLibre' => $this->getMatriculasUnionLibre($userIdsMatriculadosItem, $inicio, $fin),
                'matriculasAptos' => $totalMatriculasItem - $this->getMatriculasUnionLibre($userIdsMatriculadosItem, $inicio, $fin),
                'matriculasDeserciones' => $matriculasDesercionesItem,
                'matriculasEfectivos' => $matriculasEfectivosItem,
                'porcentajeEfectividadMatriculas' => $porcentajeEfectividadMatriculasItem,

                // New Membership KPIs for Tab 3 Breakdown
                'totalMiembros' => User::withTrashed()->whereIn('id', $idsItem)
                    ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('es_miembro_oficial', true);
                            });
                    })
                    ->where(function ($query) use ($inicio, $fin) {
                        $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                            $sub->whereBetween('created_at', [$inicio, $fin]);
                        })
                            ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                                $sub->whereBetween('created_at', [$inicio, $fin])
                                    ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                    ->where('dado_baja', false);
                            });
                    })->count(),

                'miembrosUbicados' => User::withTrashed()->whereIn('id', $idsItem)
                    ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('es_miembro_oficial', true);
                            });
                    })
                    ->where(function ($query) use ($inicio, $fin) {
                        $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                            $sub->whereBetween('created_at', [$inicio, $fin]);
                        })
                            ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                                $sub->whereBetween('created_at', [$inicio, $fin])
                                    ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                    ->where('dado_baja', false);
                            });
                    })
                    ->whereHas('bitacorasIntegranteGrupo', function ($query) use ($inicio, $fin) {
                        $query->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b3.id) FROM bitacora_integrantes_grupo as b3 WHERE b3.user_id = bitacora_integrantes_grupo.user_id AND b3.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('estado_vinculacion', true);
                    })->count(),

                'pendientesMembresiaUnionLibre' => $pendientesUnionLibreItem,
                'miembrosFormalizados' => $miembrosFormalizadosItemTotal,
                'totalUnionLibreMatriculados' => $pendientesUnionLibreItem + $miembrosFormalizadosItemTotal,

                'miembrosTraslados' => $miembrosTrasladosItem,
                'miembrosBautismos' => $miembrosBautismosItem,
                'trasladosAdultos' => $trasladosAdultosItem,
                'trasladosMenores' => $trasladosMenoresItem,
                'bautismosAdultos' => $bautismosAdultosItem,
                'bautismosMenores' => $bautismosMenoresItem,

                'graficaSemanal' => $itemGraficaSemanal,
                'graficaVinculacion' => $itemGraficaVinculacion,
                'graficaMatriculasSemanal' => $itemGraficaMatriculasSemanal,
            ];
        }

        // return User::withTrashed()->whereIn('id', $userIdsCosecha)->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido')->get();

        // --- INDICADOR 2: ESCUELAS ---
        // Obtenemos los IDs de las matrículas más recientes por usuario dentro del rango (por fecha_matricula)
        $subQueryLatestDate = Matricula::whereIn('user_id', $userIdsCosecha)
            ->whereBetween('fecha_matricula', [$inicio, $fin])
            ->whereHas('escuela', function ($q) {
                $q->where('habilitada_consolidacion', true);
            })
            ->select('user_id', DB::raw('MAX(fecha_matricula) as max_fecha'))
            ->groupBy('user_id');

        $latestMatriculaIds = Matricula::joinSub($subQueryLatestDate, 'latest_dates', function ($join) {
            $join->on('matriculas.user_id', '=', 'latest_dates.user_id')
                ->on('matriculas.fecha_matricula', '=', 'latest_dates.max_fecha');
        })
            ->whereHas('escuela', function ($q) {
                $q->where('habilitada_consolidacion', true);
            })
            ->select(DB::raw('MAX(matriculas.id) as max_id'))
            ->groupBy('matriculas.user_id')
            ->pluck('max_id');

        $matriculasCosecha = Matricula::whereIn('id', $latestMatriculaIds)->get();

        $totalMatriculas = $matriculasCosecha->count();

        // --- INDICADOR: SECTOR VS TEMPLO ---
        $matriculasSectorBase = Matricula::whereIn('id', $latestMatriculaIds)
            ->whereHas('horarioMateriaPeriodo.horarioBase.aula.tipo', function ($q) {
                $q->where('sector', true);
            });

        $matriculasSector = $matriculasSectorBase->count();

        $matriculasTemploBase = Matricula::whereIn('id', $latestMatriculaIds)
            ->whereHas('horarioMateriaPeriodo.horarioBase.aula.tipo', function ($q) {
                $q->where('sector', false);
            });

        $matriculasTemplo = $matriculasTemploBase->count();

        // --- INDICADOR: DISTRIBUCIÓN POR EDAD (Adultos vs Menores) ---
        $config = Configuracion::first(); // Asumiendo que hay una única configuración global
        $limiteEdad = $config->limite_menor_edad ?? 18;

        // Obtener matrículas con fecha de nacimiento para el cálculo
        $matriculasSectorData = $matriculasSectorBase->with('user:id,fecha_nacimiento')->get();
        $matriculasTemploData = $matriculasTemploBase->with('user:id,fecha_nacimiento')->get();

        $calcDistribucion = function ($coleccion, $limite, $inicioRange) {
            $adultos = 0;
            $menores = 0;
            foreach ($coleccion as $m) {
                if ($m->user && $m->user->fecha_nacimiento) {
                    // Edad al momento de la matrícula
                    $fechaMatricula = Carbon::parse($m->fecha_matricula);
                    $edad = $m->user->fecha_nacimiento->diffInYears($fechaMatricula);
                    if ($edad < $limite) {
                        $menores++;
                    } else {
                        $adultos++;
                    }
                } else {
                    // Fallback si no hay fecha de nacimiento: Adulto por defecto (ajustable según negocio)
                    $adultos++;
                }
            }

            return ['adultos' => $adultos, 'menores' => $menores];
        };

        $distSector = $calcDistribucion($matriculasSectorData, $limiteEdad, $inicio);
        $distTemplo = $calcDistribucion($matriculasTemploData, $limiteEdad, $inicio);

        $sectorAdultos = $distSector['adultos'];
        $sectorMenores = $distSector['menores'];
        $temploAdultos = $distTemplo['adultos'];
        $temploMenores = $distTemplo['menores'];

        $userIdsMatriculados = Matricula::whereIn('id', $latestMatriculaIds)
            ->where('bloqueado', false)
            ->pluck('user_id')
            ->unique();

        $matriculasUnionLibre = $this->getMatriculasUnionLibre($userIdsMatriculados, $inicio, $fin);
        $matriculasAptos = $totalMatriculas - $matriculasUnionLibre;

        // --- INDICADOR: DESERCIONES VS EFECTIVOS ---
        $matriculasDeserciones = Matricula::whereIn('id', $latestMatriculaIds)
            ->where('bloqueado', true)
            ->count();

        $matriculasEfectivos = $totalMatriculas - $matriculasDeserciones;

        // --- Lógica para Gráfica Semanal (Cosecha) ---
        $porcentajeEfectividadMatriculas = $totalMatriculas > 0 ? round(($matriculasEfectivos / $totalMatriculas) * 100, 2) : 0;

        $datosGraficaSemanal = [];

        // Obtenemos las fechas de creación de la cosecha filtrada
        // Reusamos la lógica de $totalCosecha pero solo obtenemos pluck('created_at')
        $fechasCosecha = User::withTrashed()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });
            })
            ->tap($filtroSedesCallback)
            ->pluck('created_at');

        // Obtenemos las fechas de creación de las DESERCIONES (Bajas)
        // Usuarios creados en el rango, que TIENEN una baja activa (último reporte es baja)
        $fechasDesercion = User::withTrashed()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });
            })
            ->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->where('dado_baja', true);
            })
            ->tap($filtroSedesCallback)
            ->pluck('created_at');

        // Agrupamos por semana (Lunes a Domingo)
        // El formato de la key será el Lunes de esa semana
        $cosechaPorSemana = $fechasCosecha->groupBy(function ($date) {
            return Carbon::parse($date)->locale('es')->startOfWeek()->format('Y-m-d');
        });

        $desercionPorSemana = $fechasDesercion->groupBy(function ($date) {
            return Carbon::parse($date)->locale('es')->startOfWeek()->format('Y-m-d');
        });

        // Generamos el periodo completo de semanas para rellenar huecos
        // Ajustamos inicio y fin al Lunes de la semana correspondiente
        $inicioSemana = $inicio->copy()->startOfWeek();
        $finSemana = $fin->copy()->startOfWeek();

        // Si el rango es menor a una semana, al menos mostramos esa semana
        if ($finSemana->lt($inicioSemana)) {
            $finSemana = $inicioSemana->copy();
        }

        $periodo = \Carbon\CarbonPeriod::create($inicioSemana, '1 week', $finSemana);

        foreach ($periodo as $fecha) {
            $lunes = $fecha->format('Y-m-d');
            // El usuario prefiere solo el último día de la semana en formato año-mes-día (ej: 26-01-11)
            $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');

            $cantidad = isset($cosechaPorSemana[$lunes]) ? $cosechaPorSemana[$lunes]->count() : 0;
            $cantidadDesercion = isset($desercionPorSemana[$lunes]) ? $desercionPorSemana[$lunes]->count() : 0;

            $datosGraficaSemanal[] = [
                'x' => $domingoLabel,
                'y' => $cantidad,
                'y_desercion' => $cantidadDesercion,
            ];
        }

        // --- Lógica para Gráfica Semanal por Vinculación ---
        $datosVinculacionSemanal = [
            'labels' => [], // Fechas (Domingos)
            'series' => [],  // [ {name: 'Amigo', data: [...]}, ... ]
        ];

        // Obtenemos todos los tipos de vinculación para tener las series completas
        $tiposVinculacion = TipoVinculacion::all();

        // Obtenemos los usuarios con su vinculación
        $cosechaVinculada = User::withTrashed()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });
            })
            ->tap($filtroSedesCallback)
            ->select('id', 'created_at', 'tipo_vinculacion_id')
            ->get();

        // Agrupamos por semana y luego por vinculación
        $agrupadoVinc = $cosechaVinculada->groupBy(function ($u) {
            return Carbon::parse($u->created_at)->startOfWeek()->format('Y-m-d');
        })->map(function ($semana) {
            return $semana->groupBy('tipo_vinculacion_id');
        });

        // Inicializamos las series
        foreach ($tiposVinculacion as $tv) {
            $datosVinculacionSemanal['series'][$tv->id] = [
                'name' => $tv->nombre,
                'data' => [],
            ];
        }

        // Recorremos el periodo para llenar las labels y los datos
        foreach ($periodo as $fecha) {
            $lunes = $fecha->format('Y-m-d');
            $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
            $datosVinculacionSemanal['labels'][] = $domingoLabel;

            foreach ($tiposVinculacion as $tv) {
                $count = 0;
                if (isset($agrupadoVinc[$lunes]) && isset($agrupadoVinc[$lunes][$tv->id])) {
                    $count = $agrupadoVinc[$lunes][$tv->id]->count();
                }
                $datosVinculacionSemanal['series'][$tv->id]['data'][] = $count;
            }
        }

        // Convertimos las series a array indexado para JS
        $datosVinculacionSemanal['series'] = array_values($datosVinculacionSemanal['series']);

        // --- Lógica para Gráfica Semanal (Escuelas / Matrículas) ---
        $datosMatriculasSemanal = [];

        // Agrupamos las matrículas (ya filtradas) por fecha de matrícula
        $fechasMatriculas = $matriculasCosecha->pluck('fecha_matricula');

        $matriculasPorSemana = $fechasMatriculas->groupBy(function ($date) {
            return Carbon::parse($date)->locale('es')->startOfWeek()->format('Y-m-d');
        });

        // Reusamos $periodo ya calculado
        foreach ($periodo as $fecha) {
            $lunes = $fecha->format('Y-m-d');
            $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');

            $cantidad = isset($matriculasPorSemana[$lunes]) ? $matriculasPorSemana[$lunes]->count() : 0;

            $datosMatriculasSemanal[] = [
                'x' => $domingoLabel,
                'y' => $cantidad,
            ];
        }

        $bloquesDisponiblesView = $esVistaDetalle ? collect() : $bloquesDisponibles;
        
        // --- Lógica para Membresías (Tab 3) ---
        $queryBaseMiembros = User::withTrashed()
            ->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where('es_miembro_oficial', true);
                        });
                });
            })
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin]);
                })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
            })
            ->tap($filtroSedesCallback);

        $totalMiembros = (clone $queryBaseMiembros)->count();
        $miembrosUbicados = (clone $queryBaseMiembros)->whereHas('bitacorasIntegranteGrupo', function ($query) use ($inicio, $fin) {
            $query->whereBetween('created_at', [$inicio, $fin])
                ->whereRaw('id = (SELECT MAX(b3.id) FROM bitacora_integrantes_grupo as b3 WHERE b3.user_id = bitacora_integrantes_grupo.user_id AND b3.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                ->where('estado_vinculacion', true);
        })->count();

        $porcentajeEfectividadMembresia = $totalMiembros > 0 ? round(($miembrosUbicados / $totalMiembros) * 100, 2) : 0;

        // --- INDICADORES ADICIONALES TAB 3 (Unión Libre) ---
        $userIdsMatriculadosEfectivos = $matriculasCosecha->where('bloqueado', false)->pluck('user_id')->unique();

        // 1. Pendientes por membresía (Unión libre)
        $pendientesMembresiaUnionLibre = User::withTrashed()
            ->whereIn('id', $userIdsMatriculadosEfectivos)
            ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
                $query->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('estadoCivilNuevo', function ($q) {
                        $q->where('es_union_libre', true);
                    });
            })->count();

        // 2. Miembros que estaban en unión libre
        $miembrosFormalizados = (clone $queryBaseMiembros)
            ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
                $query->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b_ec2.id) FROM bitacora_estados_civiles as b_ec2 WHERE b_ec2.user_id = bitacora_estados_civiles.user_id AND b_ec2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('estadoCivilNuevo', function ($q) {
                        $q->where('es_matrimonio', true);
                    });
            })
            ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
                $query->whereBetween('created_at', [$inicio, $fin])
                    ->whereHas('estadoCivilNuevo', function ($q) {
                        $q->where('es_union_libre', true);
                    });
            })->count();

        // 3. Unión libre matriculados
        $totalUnionLibreMatriculados = $pendientesMembresiaUnionLibre + $miembrosFormalizados;

        // 4. Bautismos vs Traslados
        $usuariosTraslados = (clone $queryBaseMiembros)
            ->whereHas('tipoVinculacion', function ($q) {
                $q->where('viene_de_otra_iglesia', true);
            })->select('id', 'fecha_nacimiento')->get();

        $usuariosBautismos = (clone $queryBaseMiembros)
            ->where(function ($query) {
                $query->whereDoesntHave('tipoVinculacion')
                    ->orWhereHas('tipoVinculacion', function ($q) {
                        $q->where('viene_de_otra_iglesia', false);
                    });
            })->select('id', 'fecha_nacimiento')->get();

        $miembrosTraslados = $usuariosTraslados->count();
        $miembrosBautismos = $usuariosBautismos->count();

        $distTraslados = $calcDistribucionUsuarios($usuariosTraslados, $limiteEdad, $inicio);
        $trasladosAdultos = $distTraslados['adultos'];
        $trasladosMenores = $distTraslados['menores'];

        $distBautismos = $calcDistribucionUsuarios($usuariosBautismos, $limiteEdad, $inicio);
        $bautismosAdultos = $distBautismos['adultos'];
        $bautismosMenores = $distBautismos['menores'];

        return view('contenido.paginas.consolidacion.dashboard', compact(
            'rangoFechas',
            'totalCosecha',
            'cosechaEfectiva',
            'cosechaDesercion',
            'porcentajeEfectividad',
            'vinculacionesCosecha',
            'esVistaDetalle',
            'bloqueActual',
            'tipoDesglose',
            'datosDesglose', // Reemplaza a datosPorBloque
            // Variables Filtro Bloque
            'bloquesDisponibles',
            'bloquesSeleccionados',
            // Variables Filtro Sede
            'sedesDisponibles',
            'sedesSeleccionadas',
            // Indicador 2
            'totalMatriculas',
            'matriculasSector',
            'matriculasTemplo',
            'sectorAdultos',
            'sectorMenores',
            'temploAdultos',
            'temploMenores',
            'matriculasUnionLibre',
            'matriculasAptos',
            'matriculasDeserciones',
            'matriculasEfectivos',
            'porcentajeEfectividadMatriculas',
            'datosGraficaSemanal',
            'datosVinculacionSemanal',
            'datosMatriculasSemanal',
            'totalMiembros',
            'miembrosUbicados',
            'porcentajeEfectividadMembresia',
            'pendientesMembresiaUnionLibre',
            'miembrosFormalizados',
            'totalUnionLibreMatriculados',
            'miembrosTraslados',
            'miembrosBautismos',
            'trasladosAdultos',
            'trasladosMenores',
            'bautismosAdultos',
            'bautismosMenores'
        ));
    }

    /* public function dashboard(Request $request)
    {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $rolActivo->verificacionDelPermiso('consolidacion.dashboard_consolidacion');

      $anio = $request->anio ?? date('Y');
      $semana = $request->semana ?? (int)date('W');

      $anios = range(date('Y') + 1, 2022);
      $semanas = range(1, 52);

      // Cálculo de fechas para la semana seleccionada
      $fechaInicioSemana = Carbon::now()->setISODate($anio, $semana)->startOfWeek()->format('Y-m-d');
      $fechaFinSemana = Carbon::now()->setISODate($anio, $semana)->endOfWeek()->format('Y-m-d');

      // 1. Obtener tipos de usuario habilitados para consolidación
      $tiposConsolidables = TipoUsuario::where('habilitado_para_consolidacion', true)->pluck('id');

      // 2. Obtener IDs únicos de usuarios que entraron a consolidación en ese rango (según bitácora)
      $userIdsSemanales = BitacoraTipoUsuario::whereBetween('created_at', [$fechaInicioSemana . ' 00:00:00', $fechaFinSemana . ' 23:59:59'])
        ->whereIn('tipo_usuario_id_nuevo', $tiposConsolidables)
        ->distinct()
        ->pluck('user_id');

      // 3. Estadísticas para la pestaña Semanal: Usuarios clasificados por Tipo de Vinculación
      $vinculacionesSemanales = TipoVinculacion::withCount(['usuarios' => function ($query) use ($userIdsSemanales) {
        $query->whereIn('id', $userIdsSemanales);
      }])->get();

      return view('contenido.paginas.consolidacion.dashboard', compact(
        'anio',
        'semana',
        'anios',
        'semanas',
        'vinculacionesSemanales',
        'fechaInicioSemana',
        'fechaFinSemana'
      ));
    }*/

    // Reporte de desempeño de colaboradores con filtrado por zona, sede y estado
    public function reporteDesempeño(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('consolidacion.reporte_desempeño');
        }

        $rangoFechas = $request->rango_fechas;
        if ($rangoFechas) {
            $fechas = explode(' a ', $rangoFechas);
            if (count($fechas) >= 2) {
                $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
                $fin = Carbon::parse(trim($fechas[1]))->endOfDay();
            } else {
                $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
                $fin = Carbon::parse(trim($fechas[0]))->endOfDay();
            }
        } else {
            $inicio = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);
            $fin = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY);
            $rangoFechas = $inicio->format('Y-m-d').' a '.$fin->format('Y-m-d');
        }

        // Filtros de Zonas
        $zonasDisponibles = Zona::orderBy('nombre')->get();
        $zonasSeleccionadas = $request->input('zonas_seleccionadas', $zonasDisponibles->pluck('id')->toArray());

        // Obtener los modelos de las zonas seleccionadas para mostrar en las cards
        $zonasParaReporte = Zona::with('sedes')->whereIn('id', $zonasSeleccionadas)->orderBy('nombre')->get();

        // Tipos de tareas para las cabeceras de la tabla
        $tiposTarea = TareaConsolidacion::orderBy('orden')->get();
        $estadosTarea = EstadoTareaConsolidacion::all();

        foreach ($zonasParaReporte as $zona) {
            $sedeIds = $zona->sedes->pluck('id');

            // 1. Métricas de Cosecha para la Zona
            // Usuarios creados en el rango cuya última bitácora de tipo los habilita para consolidación
            $cosechaZonaQuery = User::withTrashed()
                ->whereIn('sede_id', $sedeIds)
                ->whereBetween('created_at', [$inicio, $fin])
                ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });

            $zona->totalCosecha = $cosechaZonaQuery->count();

            $zona->cosechaEfectivaQuery = (clone $cosechaZonaQuery)
                ->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])
                        ->where('dado_baja', true);
                });

            $zona->cosechaEfectiva = (clone $zona->cosechaEfectivaQuery)->count();

            // Cantidad de los de la cosecha efectiva que no tiene ninguna tarea gestionada en el periodo
            $zona->sinGestionPeriodo = (clone $zona->cosechaEfectivaQuery)
                ->whereDoesntHave('asignacionesConsolidacion.bitacora', function ($q) use ($inicio, $fin) {
                    $q->whereBetween('created_at', [$inicio, $fin]);
                })
                ->count();

            // 2. Métricas de Matrícula para la Zona
            $userIdsZona = (clone $cosechaZonaQuery)->pluck('id');
            $subQueryLatestMatriculaZona = Matricula::whereIn('user_id', $userIdsZona)
                ->whereBetween('fecha_matricula', [$inicio, $fin])
                ->select('user_id', DB::raw('MAX(fecha_matricula) as max_fecha'))
                ->groupBy('user_id');

            $latestMatriculaIdsZona = Matricula::joinSub($subQueryLatestMatriculaZona, 'latest_dates', function ($join) {
                $join->on('matriculas.user_id', '=', 'latest_dates.user_id')
                    ->on('matriculas.fecha_matricula', '=', 'latest_dates.max_fecha');
            })
                ->select(DB::raw('MAX(matriculas.id) as max_id'))
                ->groupBy('matriculas.user_id')
                ->pluck('max_id');

            $zona->totalMatriculas = Matricula::whereIn('id', $latestMatriculaIdsZona)
                ->whereHas('escuela', fn ($q) => $q->where('habilitada_consolidacion', true))
                ->count();

            // 3. Métricas de Crecimiento para la Zona (Desglosadas)
            $pasosHabilitados = PasoCrecimiento::where('habilitada_consolidacion', true)->orderBy('orden')->get();
            $metricasCrecimientoZona = [];

            foreach ($pasosHabilitados as $paso) {
                $subQueryLatestZona = BitacoraCrecimientoUsuario::whereIn('sede_id', $sedeIds)
                    ->where('paso_crecimiento_id', $paso->id)
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->select('user_id', DB::raw('MAX(id) as max_id'))
                    ->groupBy('user_id');

                $totalPasoZona = BitacoraCrecimientoUsuario::joinSub($subQueryLatestZona, 'latest_bitacora_zona', function ($join) {
                    $join->on('bitacora_crecimiento_usuario.id', '=', 'latest_bitacora_zona.max_id');
                })
                    ->whereHas('estadoNuevo', fn ($q) => $q->where('finalizado', true))
                    ->count();

                $metricasCrecimientoZona[] = [
                    'paso_id' => $paso->id,
                    'nombre' => $paso->nombre,
                    'total' => $totalPasoZona,
                ];
            }
            $zona->metricasCrecimiento = $metricasCrecimientoZona;

            // Listado de personas para prueba (Cosecha Efectiva)

            // 3. Desglose por Sedes dentro de la Zona
            $desgloseSedes = [];
            foreach ($zona->sedes as $sede) {
                $cosechaSedeQuery = User::withTrashed()
                    ->where('sede_id', $sede->id)
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('habilitado_para_consolidacion', true);
                            });
                    });

                $totalCosechaSede = (clone $cosechaSedeQuery)->count();

                $cosechaEfectivaSedeQuery = (clone $cosechaSedeQuery)
                    ->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->where('dado_baja', true);
                    });

                $totalEfectivaSede = (clone $cosechaEfectivaSedeQuery)->count();

                $sinGestionSede = (clone $cosechaEfectivaSedeQuery)
                    ->whereDoesntHave('asignacionesConsolidacion.bitacora', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('created_at', [$inicio, $fin]);
                    })
                    ->count();

                // Métricas de Matrícula para la Sede
                $userIdsSede = (clone $cosechaSedeQuery)->pluck('id');
                $subQueryLatestMatriculaSede = Matricula::whereIn('user_id', $userIdsSede)
                    ->whereBetween('fecha_matricula', [$inicio, $fin])
                    ->select('user_id', DB::raw('MAX(fecha_matricula) as max_fecha'))
                    ->groupBy('user_id');

                $latestMatriculaIdsSede = Matricula::joinSub($subQueryLatestMatriculaSede, 'latest_dates_sede', function ($join) {
                    $join->on('matriculas.user_id', '=', 'latest_dates_sede.user_id')
                        ->on('matriculas.fecha_matricula', '=', 'latest_dates_sede.max_fecha');
                })
                    ->select(DB::raw('MAX(matriculas.id) as max_id'))
                    ->groupBy('matriculas.user_id')
                    ->pluck('max_id');

                $totalMatriculasSede = Matricula::whereIn('id', $latestMatriculaIdsSede)
                    ->whereHas('escuela', fn ($q) => $q->where('habilitada_consolidacion', true))
                    ->count();

                // Métricas de Crecimiento para la Sede (Desglosadas)
                $metricasCrecimientoSede = [];
                foreach ($pasosHabilitados as $paso) {
                    $subQueryLatestSede = BitacoraCrecimientoUsuario::where('sede_id', $sede->id)
                        ->where('paso_crecimiento_id', $paso->id)
                        ->whereBetween('created_at', [$inicio, $fin])
                        ->select('user_id', DB::raw('MAX(id) as max_id'))
                        ->groupBy('user_id');

                    $totalPasoSede = BitacoraCrecimientoUsuario::joinSub($subQueryLatestSede, 'latest_bitacora_sede', function ($join) {
                        $join->on('bitacora_crecimiento_usuario.id', '=', 'latest_bitacora_sede.max_id');
                    })
                        ->whereHas('estadoNuevo', fn ($q) => $q->where('finalizado', true))
                        ->count();

                    $metricasCrecimientoSede[$paso->id] = $totalPasoSede;
                }

                // Tabulación de Gestiones por Tarea y Estado para esta sede
                $gestionesSede = BitacoraTareaConsolidacion::where('sede_id', $sede->id)
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->with('tareaConsolidacionUsuario')
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->tareaConsolidacionUsuario->tarea_consolidacion_id ?? 0;
                    });

                $tabulacionTareasSede = [];
                foreach ($tiposTarea as $tipoT) {
                    $statusCounts = [];
                    foreach ($estadosTarea as $estT) {
                        $count = 0;
                        if ($gestionesSede->has($tipoT->id)) {
                            $count = $gestionesSede->get($tipoT->id)
                                ->where('estado_tarea_consolidacion_id', $estT->id)
                                ->count();
                        }
                        $statusCounts[$estT->id] = $count;
                    }
                    $tabulacionTareasSede[$tipoT->id] = [
                        'nombre' => $tipoT->nombre,
                        'estados' => $statusCounts,
                        'total_tarea' => array_sum($statusCounts),
                    ];
                }

                $desgloseSedes[] = [
                    'id' => $sede->id,
                    'nombre' => $sede->nombre,
                    'cosecha' => $totalCosechaSede,
                    'efectiva' => $totalEfectivaSede,
                    'sin_gestion' => $sinGestionSede,
                    'total_matriculas' => $totalMatriculasSede,
                    'crecimiento' => $metricasCrecimientoSede,
                    'tabulacion_tareas' => $tabulacionTareasSede,
                ];
            }
            $zona->desgloseSedes = $desgloseSedes;
            $zona->rankingColaboradores = []; // Inicialización de seguridad

            // 4. Ranking de Colaboradores de la Zona (Lógica Híbrida)
            // A. Identificar colaboradores ACTUALMENTE asignados a esta zona
            $colaboradoresActuales = User::whereIn('sede_id', $sedeIds)
                ->whereHas('roles', function ($q) {
                    $q->where('model_has_roles.activo', true)
                        ->whereHas('permissions', function ($p) {
                            $p->where('name', 'consolidacion.dashboard_consolidacion');
                        });
                })
                ->get();

            $ranking = [];
            // Plantilla de tareas para inicializar cada colaborador
            $tareasPlantilla = [];
            foreach ($tiposTarea as $t) {
                $tareasPlantilla[$t->id] = [
                    'total' => 0,
                    'estados' => array_fill_keys($estadosTarea->pluck('id')->toArray(), 0),
                ];
            }

            // Inicializar con la plantilla a todos los que están hoy
            foreach ($colaboradoresActuales as $colab) {
                $ranking[$colab->id] = [
                    'id' => $colab->id,
                    'nombre' => $colab->primer_nombre.' '.$colab->primer_apellido,
                    'foto' => $colab->profile_photo_url,
                    'tareas' => $tareasPlantilla,
                    'total' => 0,
                ];
            }

            // B. Cruzar con las gestiones REALES de la Bitácora en el periodo
            $gestionesRanking = BitacoraTareaConsolidacion::where('zona_id', $zona->id)
                ->whereBetween('created_at', [$inicio, $fin])
                ->with(['autor', 'tareaConsolidacionUsuario'])
                ->get();

            foreach ($gestionesRanking as $bitacora) {
                $autorId = $bitacora->autor_id;
                if (! $autorId || ! $bitacora->autor) {
                    continue;
                }
                if (! $bitacora->tareaConsolidacionUsuario) {
                    continue;
                }

                $tareaId = $bitacora->tareaConsolidacionUsuario->tarea_consolidacion_id;

                // Si el autor hizo gestiones en el pasado pero ya no está en la zona hoy,
                // igual debemos incluirlo en el reporte de ese periodo.
                if (! isset($ranking[$autorId])) {
                    $ranking[$autorId] = [
                        'id' => $autorId,
                        'nombre' => $bitacora->autor->primer_nombre.' '.$bitacora->autor->primer_apellido,
                        'foto' => $bitacora->autor->profile_photo_url,
                        'tareas' => $tareasPlantilla,
                        'total' => 0,
                    ];
                }

                if (isset($ranking[$autorId]['tareas'][$tareaId])) {
                    $estadoId = $bitacora->estado_tarea_consolidacion_id;
                    $ranking[$autorId]['tareas'][$tareaId]['total']++;
                    $ranking[$autorId]['tareas'][$tareaId]['estados'][$estadoId]++;
                    $ranking[$autorId]['total']++;
                }
            }

            // Ordenar ranking por total descendente
            uasort($ranking, fn ($a, $b) => $b['total'] <=> $a['total']);
            $zona->rankingColaboradores = $ranking;
            $zona->totalGestionesRanking = array_sum(array_column($ranking, 'total'));

        }

        // Preparar colecciones con ordenamientos específicos para cada pestaña
        $zonasDesempeno = $zonasParaReporte->sortByDesc('sinGestionPeriodo');
        $zonasRanking = $zonasParaReporte->sortByDesc('totalGestionesRanking');

        return view('contenido.paginas.consolidacion.reporte-desempeno', compact(
            'rangoFechas',
            'zonasDisponibles',
            'zonasSeleccionadas',
            'zonasDesempeno',
            'zonasRanking',
            'tiposTarea',
            'estadosTarea'
        ));
    }

    public function exportKpiExcel(Request $request)
    {
        $datos = $this->getDatosDetalleKpi($request);
        $usuarios = $datos['query']->get(); // Obtener todos sin paginar

        return Excel::download(
            new DetalleConsolidacionKpiExport($usuarios, $datos), 
            'detalle_desempeno_consolidacion.xlsx'
        );
    }

    public function detalleKpi(Request $request)
    {
        $datos = $this->getDatosDetalleKpi($request);

        $usuarios = $datos['query']->paginate(25)->withQueryString();

        return view('contenido.paginas.consolidacion.detalle-kpi', [
            'usuarios' => $usuarios,
            'kpi' => $datos['kpi'],
            'zona' => $datos['zona'],
            'sede' => $datos['sede'] ?? null,
            'rangoFechas' => $datos['rangoFechas'],
            'paso' => $datos['paso'] ?? null,
        ]);
    }

    private function getDatosDetalleKpi(Request $request)
    {
        $kpi = $request->kpi ?? 'cosecha_total';
        $zonaId = $request->zona_id;
        $sedeId = $request->sede_id;
        $rangoFechas = $request->rango_fechas;
        $search = $request->buscar;

        $zona = Zona::with('sedes')->findOrFail($zonaId);
        $sedeIds = $sedeId ? [$sedeId] : $zona->sedes->pluck('id')->toArray();
        $sede = $sedeId ? Sede::findOrFail($sedeId) : null;

        // Fechas (Lógica igual a reporteDesempeño)
        $inicio = Carbon::now()->startOfMonth()->toDateTimeString();
        $fin = Carbon::now()->toDateTimeString();

        if ($rangoFechas) {
            $fechas = explode(' a ', $rangoFechas);
            $inicio = Carbon::parse($fechas[0])->startOfDay()->toDateTimeString();
            $fechaRawFin = isset($fechas[1]) ? $fechas[1] : $fechas[0];
            $fin = Carbon::parse($fechaRawFin)->endOfDay()->toDateTimeString();
        } else {
            $rangoFechas = Carbon::parse($inicio)->format('Y-m-d').' a '.Carbon::parse($fin)->format('Y-m-d');
        }

        $query = User::withTrashed();
        $paso = null;

        switch ($kpi) {
            case 'cosecha_total':
                $query->whereIn('sede_id', $sedeIds)
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('habilitado_para_consolidacion', true);
                            });
                    });
                break;

            case 'cosecha_efectiva':
                $query->whereIn('sede_id', $sedeIds)
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('habilitado_para_consolidacion', true);
                            });
                    })
                    ->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->where('dado_baja', true);
                    });
                break;

            case 'sin_gestion':
                $query->whereIn('sede_id', $sedeIds)
                    ->whereBetween('created_at', [$inicio, $fin])
                    ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q) {
                                $q->where('habilitado_para_consolidacion', true);
                            });
                    })
                    ->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->where('dado_baja', true);
                    })
                    ->whereDoesntHave('asignacionesConsolidacion.bitacora', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('created_at', [$inicio, $fin]);
                    });
                break;

            case 'matriculas':
                $query->whereHas('matriculas', function ($q) use ($sedeIds, $inicio, $fin) {
                    $q->whereBetween('fecha_matricula', [$inicio, $fin])
                        ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
                })->whereIn('sede_id', $sedeIds);
                break;

            default:
                if (str_starts_with($kpi, 'paso_')) {
                    $pasoId = str_replace('paso_', '', $kpi);
                    $paso = PasoCrecimiento::findOrFail($pasoId);

                    $query->whereHas('bitacoraCrecimiento', function ($q) use ($pasoId, $inicio, $fin, $sedeIds) {
                        $q->where('paso_crecimiento_id', $pasoId)
                            ->whereIn('sede_id', $sedeIds)
                            ->whereBetween('created_at', [$inicio, $fin])
                            ->whereHas('estadoNuevo', fn ($q2) => $q2->where('finalizado', true));
                    });
                }
                break;
        }

        if ($search) {
            $buscarSaneado = strtolower(Helpers::sanearStringConEspacios($search));
            $query->where(function ($q) use ($search, $buscarSaneado) {
                $q->whereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', segundo_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw('LOWER(telefono_movil) LIKE LOWER(?)', [$search.'%'])
                    ->orWhereRaw('LOWER(email) LIKE LOWER(?)', ['%'.$search.'%'])
                    ->orWhereRaw('LOWER(identificacion) LIKE LOWER(?)', [$search.'%']);
            });
        }

        return [
            'query' => $query,
            'kpi' => $kpi,
            'zona' => $zona,
            'sede' => $sede,
            'rangoFechas' => $rangoFechas,
            'paso' => $paso,
        ];
    }

    private function getMatriculasUnionLibre($userIds, $inicio, $fin)
    {
        if ($userIds->isEmpty()) {
            return 0;
        }

        $subQueryBitacora = BitacoraEstadoCivil::whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$inicio, $fin])
            ->select('user_id', DB::raw('MAX(created_at) as max_created_at'))
            ->groupBy('user_id');

        $latestBitacoraIds = BitacoraEstadoCivil::joinSub($subQueryBitacora, 'latest_bitacora_helper', function ($join) {
            $join->on('bitacora_estados_civiles.user_id', '=', 'latest_bitacora_helper.user_id')
                ->on('bitacora_estados_civiles.created_at', '=', 'latest_bitacora_helper.max_created_at');
        })
            ->select(DB::raw('MAX(bitacora_estados_civiles.id) as max_id'))
            ->groupBy('bitacora_estados_civiles.user_id')
            ->pluck('max_id'); 

        return BitacoraEstadoCivil::whereIn('id', $latestBitacoraIds)
            ->whereHas('estadoCivilNuevo', function ($q) {
                $q->where('es_union_libre', true);
            })
            ->count();
    }

    public function exportarDetalleKpiDashboard(Request $request)
    {
        $datos = $this->getDatosDetalleKpiDashboard($request);

        // Obtener todos los registros sin paginación para exportar
        $usuarios = $datos['query']->get();

        return Excel::download(
            new DetalleConsolidacionKpiDashboardExport($usuarios, $datos), 
            'detalle_kpi_dashboard_consolidacion.xlsx'
        );
    }

    public function detalleKpiDashboard(Request $request)
    {
        $datos = $this->getDatosDetalleKpiDashboard($request);

        $usuarios = $datos['query']->paginate(25)->withQueryString();

        return view('contenido.paginas.consolidacion.detalle-kpi-dashboard', [
            'usuarios' => $usuarios,
            'kpi' => $datos['kpi'],
            'bloquesSeleccionados' => $datos['bloquesSeleccionados'],
            'sedesSeleccionadas' => $datos['sedesSeleccionadas'],
            'bloqueDetalle' => $datos['bloqueDetalle'],
            'sedeDetalle' => $datos['sedeDetalle'],
            'rangoFechas' => $datos['rangoFechas'],
        ]);
    }

    private function getDatosDetalleKpiDashboard(Request $request)
    {
        $kpi = $request->kpi ?? 'cosecha_total';
        
        $bloquesRaw = $request->bloques_seleccionados;
        $bloquesIds = is_array($bloquesRaw) ? $bloquesRaw : ($bloquesRaw ? explode(',', $bloquesRaw) : []);
        
        $sedesRaw = $request->sedes_seleccionadas;
        $sedesIdsRequest = is_array($sedesRaw) ? $sedesRaw : ($sedesRaw ? explode(',', $sedesRaw) : []);
        
        $rangoFechas = $request->rango_fechas;
        $search = $request->buscar;

        // Si se hizo click desde un desglose específico o vista detalle de bloque
        $bloqueEspecificoId = $request->bloque_id ?? $request->bloque_detalle_id;
        $sedeEspecificaId = $request->sede_id;

        // Preparar filtros base
        $bloquesSeleccionados = BloqueDashboardConsolidacion::whereIn('id', $bloquesIds)->get();
        if ($bloquesSeleccionados->isEmpty()) {
            $bloquesSeleccionados = BloqueDashboardConsolidacion::all();
        }

        $sedesPermitidasIds = $bloquesSeleccionados->flatMap(function($b) { return $b->sedes->pluck('id'); })->unique()->toArray();
        if (!empty($sedesIdsRequest)) {
            $sedesPermitidasIds = array_intersect($sedesPermitidasIds, $sedesIdsRequest);
        }

        $sedeDetalle = null;
        $bloqueDetalle = null;

        if ($sedeEspecificaId) {
            $sedesPermitidasIds = [$sedeEspecificaId];
            $sedeDetalle = Sede::find($sedeEspecificaId);
        } elseif ($bloqueEspecificoId) {
            $bloqueFiltrado = BloqueDashboardConsolidacion::find($bloqueEspecificoId);
            $bloqueDetalle = $bloqueFiltrado;
            if ($bloqueFiltrado) {
                // Si hay un bloque específico, sus sedes son la base.
                $sedesBloque = $bloqueFiltrado->sedes->pluck('id')->toArray();
                
                // Si además hay un filtro global de sedes, intersectamos.
                if (!empty($sedesIdsRequest)) {
                    $sedesPermitidasIds = array_intersect($sedesIdsRequest, $sedesBloque);
                    // Si el filtro global es incompatible con el bloque clicado, priorizamos el bloque.
                    if (empty($sedesPermitidasIds)) {
                        $sedesPermitidasIds = $sedesBloque;
                    }
                } else {
                    $sedesPermitidasIds = $sedesBloque;
                }
            }
        }

        // Fechas
        $inicio = Carbon::now()->startOfMonth()->toDateTimeString();
        $fin = Carbon::now()->toDateTimeString();

        if ($rangoFechas) {
            $fechas = explode(' a ', $rangoFechas);
            $inicio = Carbon::parse($fechas[0])->startOfDay()->toDateTimeString();
            $fechaRawFin = isset($fechas[1]) ? $fechas[1] : $fechas[0];
            $fin = Carbon::parse($fechaRawFin)->endOfDay()->toDateTimeString();
        } else {
            $rangoFechas = Carbon::parse($inicio)->format('Y-m-d').' a '.Carbon::parse($fin)->format('Y-m-d');
        }

        $sedesIdsFiltrar = $sedesPermitidasIds;

        $filtroSedesCallback = function ($query) use ($inicio, $fin, $sedesIdsFiltrar) {
            if (! empty($sedesIdsFiltrar)) {
                $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sedesIdsFiltrar) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereIn('sede_id_nuevo', $sedesIdsFiltrar)
                        ->whereRaw('id = (
                        SELECT MAX(bs.id) 
                        FROM bitacora_sedes as bs
                        WHERE bs.user_id = bitacora_sedes.user_id 
                        AND bs.created_at BETWEEN ? AND ?
                    )', [$inicio, $fin]);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        };

        $query = User::withTrashed()->tap($filtroSedesCallback);

        switch ($kpi) {
            // ================= TAB 1: COSECHA =================
            case 'cosecha_total':
                $query->whereBetween('created_at', [$inicio, $fin])
                      ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
                          $q->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                      });
                break;
            case 'cosecha_efectiva':
                $query->whereBetween('created_at', [$inicio, $fin])
                      ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
                          $q->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                      })
                      ->where(function ($q) use ($inicio, $fin) {
                          $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                              $sub->whereBetween('created_at', [$inicio, $fin]);
                          })
                              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                                  $sub->whereBetween('created_at', [$inicio, $fin])
                                      ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                      ->where('dado_baja', false);
                              });
                      });
                break;
            case 'deserciones':
                $query->whereBetween('created_at', [$inicio, $fin])
                      ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
                          $q->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                      })
                      ->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                          $sub->whereBetween('created_at', [$inicio, $fin])
                              ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                              ->where('dado_baja', true);
                      });
                break;
            
            // ================= TAB 2: ESCUELAS =================
            case 'total_matriculas':
                $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
                    $q->whereBetween('fecha_matricula', [$inicio, $fin])
                      ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
                });
                break;
            case 'matriculas_sector':
                $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
                    $q->whereBetween('fecha_matricula', [$inicio, $fin])
                      ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true)->where('es_de_sector', true));
                });
                break;
            case 'matriculas_templo':
                $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
                    $q->whereBetween('fecha_matricula', [$inicio, $fin])
                      ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true)->where('es_de_sector', false));
                });
                break;
            case 'matriculas_deserciones':
                $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
                    $q->whereBetween('fecha_matricula', [$inicio, $fin])
                      ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
                })->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])
                        ->where('dado_baja', true);
                });
                break;
            case 'matriculas_efectivos':
                $query->whereHas('matriculas', function ($q) use ($inicio, $fin) {
                    $q->whereBetween('fecha_matricula', [$inicio, $fin])
                      ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
                })->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])
                        ->where('dado_baja', true);
                });
                break;
            case 'matriculas_aptos':
                // Cosecha Efectiva
                $query->whereBetween('created_at', [$inicio, $fin])
                      ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
                          $q->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                      })
                      ->where(function ($q) use ($inicio, $fin) {
                          $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                              $sub->whereBetween('created_at', [$inicio, $fin]);
                          })
                              ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                                  $sub->whereBetween('created_at', [$inicio, $fin])
                                      ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                      ->where('dado_baja', false);
                              });
                      });
                break;
            case 'matriculas_union_libre':
                $matriculadosEfectivosIds = User::withTrashed()
                    ->whereIn('sede_id', $sedesPermitidasIds)
                    ->whereHas('matriculas', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('fecha_matricula', [$inicio, $fin])
                          ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
                    })->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->where('dado_baja', true);
                    })->pluck('id');
                
                $unionLibreIds = [];
                if ($matriculadosEfectivosIds->isNotEmpty()) {
                    $subQueryBitacora = BitacoraEstadoCivil::whereIn('user_id', $matriculadosEfectivosIds)
                        ->whereBetween('created_at', [$inicio, $fin])
                        ->select('user_id', DB::raw('MAX(created_at) as max_created_at'))
                        ->groupBy('user_id');

                    $latestIds = BitacoraEstadoCivil::joinSub($subQueryBitacora, 'latest_helper', function ($join) {
                        $join->on('bitacora_estados_civiles.user_id', '=', 'latest_helper.user_id')
                             ->on('bitacora_estados_civiles.created_at', '=', 'latest_helper.max_created_at');
                    })->select(DB::raw('MAX(bitacora_estados_civiles.id) as max_id'))
                      ->groupBy('bitacora_estados_civiles.user_id')
                      ->pluck('max_id');

                    $unionLibreIds = BitacoraEstadoCivil::whereIn('id', $latestIds)
                        ->whereHas('estadoCivilNuevo', fn($q) => $q->where('es_union_libre', true))
                        ->pluck('user_id');
                }
                $query->whereIn('id', $unionLibreIds);
                break;
            
            // ================= TAB 3: MEMBRESÍA =================
            case 'total_miembros':
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                             ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                             ->whereHas('tipoUsuarioNuevo', function ($q) {
                                 $q->where('es_miembro_oficial', true);
                             });
                })
                ->where(function ($query) use ($inicio, $fin) {
                    $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
                });
                break;
            case 'miembros_ubicados':
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                             ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                             ->whereHas('tipoUsuarioNuevo', fn($q) => $q->where('es_miembro_oficial', true));
                })
                ->where(function ($query) use ($inicio, $fin) {
                    $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
                })
                ->whereHas('bitacorasIntegranteGrupo', function ($query) use ($inicio, $fin) {
                    $query->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b3.id) FROM bitacora_integrantes_grupo as b3 WHERE b3.user_id = bitacora_integrantes_grupo.user_id AND b3.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->where('estado_vinculacion', true);
                });
                break;
            case 'bautismos':
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                             ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                             ->whereHas('tipoUsuarioNuevo', fn($q) => $q->where('es_miembro_oficial', true));
                })
                ->where(function ($query) use ($inicio, $fin) {
                    $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
                })
                ->where(function ($query) {
                    $query->whereDoesntHave('tipoVinculacion')
                        ->orWhereHas('tipoVinculacion', function ($q) {
                            $q->where('viene_de_otra_iglesia', false);
                        });
                });
                break;
            case 'traslados':
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                             ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                             ->whereHas('tipoUsuarioNuevo', fn($q) => $q->where('es_miembro_oficial', true));
                })
                ->where(function ($query) use ($inicio, $fin) {
                    $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
                })
                ->whereHas('tipoVinculacion', function ($q) {
                    $q->where('viene_de_otra_iglesia', true);
                });
                break;
            case 'union_libre_matriculados':
                // Parte A: Pendientes (Matriculados en periodo en Unión Libre)
                $idsA = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
                    ->tap($filtroSedesCallback)
                    ->whereHas('matriculas', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('fecha_matricula', [$inicio, $fin])
                          ->where('bloqueado', false)
                          ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
                    })
                    ->whereHas('bitacorasEstadoCivil', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('estadoCivilNuevo', fn($q2) => $q2->where('es_union_libre', true));
                    })->pluck('id');

                // Parte B: Miembros Formalizados (Matrimonio actual, Union Libre previo)
                $idsB = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
                    ->tap($filtroSedesCallback)
                    ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('tipoUsuarioNuevo', fn($q) => $q->where('es_miembro_oficial', true));
                    })
                    ->where(function ($q) use ($inicio, $fin) {
                        $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                            $sub->whereBetween('created_at', [$inicio, $fin]);
                        })
                            ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                                $sub->whereBetween('created_at', [$inicio, $fin])
                                    ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                    ->where('dado_baja', false);
                            });
                    })
                    ->whereHas('bitacorasEstadoCivil', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b_ec2.id) FROM bitacora_estados_civiles as b_ec2 WHERE b_ec2.user_id = bitacora_estados_civiles.user_id AND b_ec2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('estadoCivilNuevo', fn($q2) => $q2->where('es_matrimonio', true));
                    })
                    ->whereHas('bitacorasEstadoCivil', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('created_at', [$inicio, $fin])
                            ->whereHas('estadoCivilNuevo', fn($q2) => $q2->where('es_union_libre', true));
                    })->pluck('id');

                $query->whereIn('id', $idsA->merge($idsB)->unique());
                break;
            case 'miembros_formalizados':
                $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where('es_miembro_oficial', true);
                        });
                })
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin]);
                    })
                        ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                            $sub->whereBetween('created_at', [$inicio, $fin])
                                ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                ->where('dado_baja', false);
                        });
                })
                ->whereHas('bitacorasEstadoCivil', function ($q2) use ($inicio, $fin) {
                    $q2->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b_ec2.id) FROM bitacora_estados_civiles as b_ec2 WHERE b_ec2.user_id = bitacora_estados_civiles.user_id AND b_ec2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('estadoCivilNuevo', function ($q3) {
                            $q3->where('es_matrimonio', true);
                        });
                })
                ->whereHas('bitacorasEstadoCivil', function ($q2) use ($inicio, $fin) {
                    $q2->whereBetween('created_at', [$inicio, $fin])
                        ->whereHas('estadoCivilNuevo', function ($q3) {
                            $q3->where('es_union_libre', true);
                        });
                });
                break;
            case 'pendientes_membresia_union_libre':
                $idsMatriculados = User::withTrashed()
                    ->tap($filtroSedesCallback)
                    ->whereHas('matriculas', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('fecha_matricula', [$inicio, $fin])
                          ->whereHas('escuela', fn ($q2) => $q2->where('habilitada_consolidacion', true));
                    })->pluck('id');

                $query->whereIn('id', $idsMatriculados)
                    ->whereHas('bitacorasEstadoCivil', function ($q) use ($inicio, $fin) {
                        $q->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(b_ec.id) FROM bitacora_estados_civiles as b_ec WHERE b_ec.user_id = bitacora_estados_civiles.user_id AND b_ec.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->whereHas('estadoCivilNuevo', function ($q2) {
                                $q2->where('es_union_libre', true);
                            });
                    });
                break;
                
            default:
                if (str_starts_with($kpi, 'cosecha_vinculacion_')) {
                    $vinculacionId = str_replace('cosecha_vinculacion_', '', $kpi);
                    $query->whereBetween('created_at', [$inicio, $fin])
                          ->whereHas('bitacorasTipoUsuario', function ($q) use ($inicio, $fin) {
                              $q->whereBetween('created_at', [$inicio, $fin])
                                ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                                ->whereHas('tipoUsuarioNuevo', function ($q2) {
                                    $q2->where('habilitado_para_consolidacion', true)
                                       ->orWhere('es_miembro_oficial', true);
                                });
                          })->where('tipo_vinculacion_id', $vinculacionId);
                } else if (str_starts_with($kpi, 'traslados_')) {
                    $edadRef = Carbon::now()->subYears(18)->format('Y-m-d');
                    $esAdulto = str_contains($kpi, 'adultos');
                    
                    $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                                 ->whereHas('tipoUsuarioNuevo', fn($q) => $q->where('es_miembro_oficial', true));
                    })->whereHas('tipo_vinculacion', function ($q) {
                        $q->where('viene_de_otra_iglesia', true);
                    });

                    if ($esAdulto) {
                        $query->whereNotNull('fecha_nacimiento')->where('fecha_nacimiento', '<=', $edadRef);
                    } else {
                        $query->where(function($q) use ($edadRef) {
                            $q->whereNull('fecha_nacimiento')->orWhere('fecha_nacimiento', '>', $edadRef);
                        });
                    }
                } else if (str_starts_with($kpi, 'bautismos_')) {
                    $edadRef = Carbon::now()->subYears(18)->format('Y-m-d');
                    $esAdulto = str_contains($kpi, 'adultos');
                    
                    $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                        $subQuery->whereBetween('created_at', [$inicio, $fin])
                                 ->whereHas('tipoUsuarioNuevo', fn($q) => $q->where('es_miembro_oficial', true));
                    })->whereHas('tipo_vinculacion', function ($q) {
                        $q->where('viene_de_otra_iglesia', false);
                    });

                    if ($esAdulto) {
                        $query->whereNotNull('fecha_nacimiento')->where('fecha_nacimiento', '<=', $edadRef);
                    } else {
                        $query->where(function($q) use ($edadRef) {
                            $q->whereNull('fecha_nacimiento')->orWhere('fecha_nacimiento', '>', $edadRef);
                        });
                    }
                }
                break;
        }

        if ($search) {
            $buscarSaneado = strtolower(Helpers::sanearStringConEspacios($search));
            $query->where(function ($q) use ($search, $buscarSaneado) {
                $q->whereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', segundo_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw('LOWER(telefono_movil) LIKE LOWER(?)', [$search.'%'])
                    ->orWhereRaw('LOWER(email) LIKE LOWER(?)', ['%'.$search.'%'])
                    ->orWhereRaw('LOWER(identificacion) LIKE LOWER(?)', [$search.'%']);
            });
        }

        return [
            'query' => $query,
            'kpi' => $kpi,
            'bloquesSeleccionados' => $bloquesIds,
            'sedesSeleccionadas' => $sedesIdsRequest,
            'bloqueDetalle' => $bloqueDetalle,
            'sedeDetalle' => $sedeDetalle,
            'rangoFechas' => $rangoFechas
        ];
    }

    public function exportarDashboard(Request $request) 
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consolidacion.dashboard_consolidacion');

        $rangoFechas = $request->rango_fechas;
        if ($rangoFechas) {
            $fechas = explode(' a ', $rangoFechas);
            if (count($fechas) >= 2) {
                $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
                $fin = Carbon::parse(trim($fechas[1]))->endOfDay();
            } else {
                $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
                $fin = Carbon::parse(trim($fechas[0]))->endOfDay();
            }
        } else {
            $inicio = Carbon::now()->startOfMonth();
            $fin = Carbon::now()->endOfMonth();
        }

        $esVistaDetalle = $request->has('bloque_detalle_id') && !empty($request->bloque_detalle_id);
        
        $bloquesIterar = collect();
        if ($esVistaDetalle) {
            $bloque = BloqueDashboardConsolidacion::with('sedes')->find($request->bloque_detalle_id);
            if ($bloque) {
                if ($request->has('sedes_seleccionadas') && is_array($request->sedes_seleccionadas)) {
                    $bloque->sedes = $bloque->sedes->whereIn('id', $request->sedes_seleccionadas);
                }
                $bloquesIterar->push($bloque);
            }
        } else { 
            $bloquesDisponibles = BloqueDashboardConsolidacion::with('sedes')->get();
            if ($request->has('bloques_seleccionados') && is_array($request->bloques_seleccionados) && count($request->bloques_seleccionados) > 0) {
                $bloquesSeleccionados = $request->bloques_seleccionados;
            } else {
                $bloquesSeleccionados = $bloquesDisponibles->pluck('id')->toArray();
            }

            $bloquesIterar = $bloquesDisponibles->whereIn('id', $bloquesSeleccionados);
        }

        $tiposVinculaciones = TipoVinculacion::orderBy('id', 'asc')->get();
        $titulosVinculaciones = $tiposVinculaciones->pluck('nombre')->toArray();

        // Construir texto de filtros para el encabezado del archivo
        $textoRango = $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d');
        $filtrosExtra = "Rango de fechas: {$textoRango}";

        $dataExport = [];

        foreach ($bloquesIterar as $bloque) {
            foreach ($bloque->sedes as $sede) {
                $metricasSede = $this->getCosechaMetricsForExport([$sede->id], $inicio, $fin);
                
                $fila = [
                    'Sede / Bloque' => $sede->nombre,
                    'Total Cosecha' => $metricasSede['total'] == 0 ? '0' : $metricasSede['total'],
                    'Deserciones' => $metricasSede['deserciones'] == 0 ? '0' : $metricasSede['deserciones'],
                    'Cosecha Efectiva' => $metricasSede['efectiva'] == 0 ? '0' : $metricasSede['efectiva'],
                    'Efectividad (%)' => $metricasSede['porcentaje'] == 0 ? '0' : $metricasSede['porcentaje'],
                ];
                
                foreach ($tiposVinculaciones as $tv) {
                    $valorVinculacion = $metricasSede['vinculaciones'][$tv->id] ?? 0;
                    $fila[$tv->nombre] = $valorVinculacion == 0 ? '0' : $valorVinculacion;
                }
                
                $fila['Total matrículas'] = $metricasSede['total_matriculas'] == 0 ? '0' : $metricasSede['total_matriculas'];
                $fila['Matrículas efectivas'] = $metricasSede['matriculas_efectivas'] == 0 ? '0' : $metricasSede['matriculas_efectivas'];
                $fila['Efectividad de matrículas (%)'] = $metricasSede['porcentaje_matriculas'] == 0 ? '0' : $metricasSede['porcentaje_matriculas'];
                $fila['Templo'] = $metricasSede['matriculas_templo'] == 0 ? '0' : $metricasSede['matriculas_templo'];
                $fila['Sector'] = $metricasSede['matriculas_sector'] == 0 ? '0' : $metricasSede['matriculas_sector'];
                $fila['Sector Adultos'] = $metricasSede['sector_adultos'] == 0 ? '0' : $metricasSede['sector_adultos'];
                $fila['Sector Warriors'] = $metricasSede['sector_warriors'] == 0 ? '0' : $metricasSede['sector_warriors'];
                $fila['Templo Adultos'] = $metricasSede['templo_adultos'] == 0 ? '0' : $metricasSede['templo_adultos'];
                $fila['Templo Warriors'] = $metricasSede['templo_warriors'] == 0 ? '0' : $metricasSede['templo_warriors'];
                $fila['Aptos'] = $metricasSede['aptos'] == 0 ? '0' : $metricasSede['aptos'];
                $fila['Unión Libre'] = $metricasSede['union_libre'] == 0 ? '0' : $metricasSede['union_libre'];
                
                $fila['Total Miembros'] = $metricasSede['total_miembros'] == 0 ? '0' : $metricasSede['total_miembros'];
                $fila['Ef. Matrículas a Membresías (%)'] = $metricasSede['ef_matriculas_membresias'] == 0 ? '0' : $metricasSede['ef_matriculas_membresias'];
                $fila['Ubicados en Grupos'] = $metricasSede['ubicados_grupos'] == 0 ? '0' : $metricasSede['ubicados_grupos'];
                $fila['Ef. Ubicación en Grupos (%)'] = $metricasSede['ef_ubicacion'] == 0 ? '0' : $metricasSede['ef_ubicacion'];
                $fila['Total Unión Libre'] = $metricasSede['total_union_libre_mat'] == 0 ? '0' : $metricasSede['total_union_libre_mat'];
                $fila['Pendientes'] = $metricasSede['pendientes_union_libre'] == 0 ? '0' : $metricasSede['pendientes_union_libre'];
                $fila['Formalizados'] = $metricasSede['formalizados'] == 0 ? '0' : $metricasSede['formalizados'];
                $fila['Ef. Formalización (%)'] = $metricasSede['ef_formalizacion'] == 0 ? '0' : $metricasSede['ef_formalizacion'];
                $fila['Total Traslados'] = $metricasSede['total_traslados'] == 0 ? '0' : $metricasSede['total_traslados'];
                $fila['Adultos (Traslados)'] = $metricasSede['traslados_adultos'] == 0 ? '0' : $metricasSede['traslados_adultos'];
                $fila['Warriors (Traslados)'] = $metricasSede['traslados_warriors'] == 0 ? '0' : $metricasSede['traslados_warriors'];
                $fila['Total Bautismos'] = $metricasSede['total_bautismos'] == 0 ? '0' : $metricasSede['total_bautismos'];
                $fila['Adultos (Bautismos)'] = $metricasSede['bautismos_adultos'] == 0 ? '0' : $metricasSede['bautismos_adultos'];
                $fila['Warriors (Bautismos)'] = $metricasSede['bautismos_warriors'] == 0 ? '0' : $metricasSede['bautismos_warriors'];
                
                $dataExport[] = $fila;
            }

            $sedesBloqueIds = $bloque->sedes->pluck('id')->toArray();
            $metricasBloque = $this->getCosechaMetricsForExport($sedesBloqueIds, $inicio, $fin);

            $filaBloque = [
                'Sede / Bloque' => 'TOTAL: ' . strtoupper($bloque->nombre),
                'Total Cosecha' => $metricasBloque['total'] == 0 ? '0' : $metricasBloque['total'],
                'Deserciones' => $metricasBloque['deserciones'] == 0 ? '0' : $metricasBloque['deserciones'],
                'Cosecha Efectiva' => $metricasBloque['efectiva'] == 0 ? '0' : $metricasBloque['efectiva'],
                'Efectividad (%)' => $metricasBloque['porcentaje'] == 0 ? '0' : $metricasBloque['porcentaje'],
            ];
            
            foreach ($tiposVinculaciones as $tv) {
                $valorVincBloque = $metricasBloque['vinculaciones'][$tv->id] ?? 0;
                $filaBloque[$tv->nombre] = $valorVincBloque == 0 ? '0' : $valorVincBloque;
            }
            
            $filaBloque['Total matrículas'] = $metricasBloque['total_matriculas'] == 0 ? '0' : $metricasBloque['total_matriculas'];
            $filaBloque['Matrículas efectivas'] = $metricasBloque['matriculas_efectivas'] == 0 ? '0' : $metricasBloque['matriculas_efectivas'];
            $filaBloque['Efectividad de matrículas (%)'] = $metricasBloque['porcentaje_matriculas'] == 0 ? '0' : $metricasBloque['porcentaje_matriculas'];
            $filaBloque['Templo'] = $metricasBloque['matriculas_templo'] == 0 ? '0' : $metricasBloque['matriculas_templo'];
            $filaBloque['Sector'] = $metricasBloque['matriculas_sector'] == 0 ? '0' : $metricasBloque['matriculas_sector'];
            $filaBloque['Sector Adultos'] = $metricasBloque['sector_adultos'] == 0 ? '0' : $metricasBloque['sector_adultos'];
            $filaBloque['Sector Warriors'] = $metricasBloque['sector_warriors'] == 0 ? '0' : $metricasBloque['sector_warriors'];
            $filaBloque['Templo Adultos'] = $metricasBloque['templo_adultos'] == 0 ? '0' : $metricasBloque['templo_adultos'];
            $filaBloque['Templo Warriors'] = $metricasBloque['templo_warriors'] == 0 ? '0' : $metricasBloque['templo_warriors'];
            $filaBloque['Aptos'] = $metricasBloque['aptos'] == 0 ? '0' : $metricasBloque['aptos'];
            $filaBloque['Unión Libre'] = $metricasBloque['union_libre'] == 0 ? '0' : $metricasBloque['union_libre'];
            
            $filaBloque['Total Miembros'] = $metricasBloque['total_miembros'] == 0 ? '0' : $metricasBloque['total_miembros'];
            $filaBloque['Ef. Matrículas a Membresías (%)'] = $metricasBloque['ef_matriculas_membresias'] == 0 ? '0' : $metricasBloque['ef_matriculas_membresias'];
            $filaBloque['Ubicados en Grupos'] = $metricasBloque['ubicados_grupos'] == 0 ? '0' : $metricasBloque['ubicados_grupos'];
            $filaBloque['Ef. Ubicación en Grupos (%)'] = $metricasBloque['ef_ubicacion'] == 0 ? '0' : $metricasBloque['ef_ubicacion'];
            $filaBloque['Total Unión Libre'] = $metricasBloque['total_union_libre_mat'] == 0 ? '0' : $metricasBloque['total_union_libre_mat'];
            $filaBloque['Pendientes'] = $metricasBloque['pendientes_union_libre'] == 0 ? '0' : $metricasBloque['pendientes_union_libre'];
            $filaBloque['Formalizados'] = $metricasBloque['formalizados'] == 0 ? '0' : $metricasBloque['formalizados'];
            $filaBloque['Ef. Formalización (%)'] = $metricasBloque['ef_formalizacion'] == 0 ? '0' : $metricasBloque['ef_formalizacion'];
            $filaBloque['Total Traslados'] = $metricasBloque['total_traslados'] == 0 ? '0' : $metricasBloque['total_traslados'];
            $filaBloque['Adultos (Traslados)'] = $metricasBloque['traslados_adultos'] == 0 ? '0' : $metricasBloque['traslados_adultos'];
            $filaBloque['Warriors (Traslados)'] = $metricasBloque['traslados_warriors'] == 0 ? '0' : $metricasBloque['traslados_warriors'];
            $filaBloque['Total Bautismos'] = $metricasBloque['total_bautismos'] == 0 ? '0' : $metricasBloque['total_bautismos'];
            $filaBloque['Adultos (Bautismos)'] = $metricasBloque['bautismos_adultos'] == 0 ? '0' : $metricasBloque['bautismos_adultos'];
            $filaBloque['Warriors (Bautismos)'] = $metricasBloque['bautismos_warriors'] == 0 ? '0' : $metricasBloque['bautismos_warriors'];
            
            $dataExport[] = $filaBloque;
        }

        return Excel::download(new DashboardCosechaExport($dataExport, $titulosVinculaciones, $filtrosExtra), 'Dashboard_Cosecha_'.$inicio->format('Y-m-d').'_al_'.$fin->format('Y-m-d').'.xlsx');
    }

    private function getCosechaMetricsForExport($sedesItemIds, $inicio, $fin)
    {
        $filtroItem = function ($query) use ($inicio, $fin, $sedesItemIds) {
            if (!empty($sedesItemIds)) {
                $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sedesItemIds) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereIn('sede_id_nuevo', $sedesItemIds)
                        ->whereRaw('id = (SELECT MAX(bs.id) FROM bitacora_sedes as bs WHERE bs.user_id = bitacora_sedes.user_id AND bs.created_at BETWEEN ? AND ?)', [$inicio, $fin]);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        };

        $totalItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });
            })->tap($filtroItem)->count();

        $efectivaItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('habilitado_para_consolidacion', true)
                                   ->orWhere('es_miembro_oficial', true);
                            });
                        });
                });
            })
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin]);
                })
                ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])->where('dado_baja', false);
                });
            })->tap($filtroItem)->count();

        $idsItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])
                        ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                        ->whereHas('tipoUsuarioNuevo', function ($q) {
                            $q->where('habilitado_para_consolidacion', true)
                                ->orWhere('es_miembro_oficial', true);
                        });
                });
            })->tap($filtroItem)->pluck('id');

        $vinculacionesDB = TipoVinculacion::withCount(['usuarios' => function ($query) use ($idsItem) {
            $query->withTrashed()->whereIn('users.id', $idsItem);
        }])->get();

        $vinculacionesArray = [];
        foreach($vinculacionesDB as $v) {
            $vinculacionesArray[$v->id] = $v->usuarios_count;
        }

        $porcentaje = $totalItem > 0 ? round(($efectivaItem / $totalItem) * 100, 2) : 0;

        // --- DATOS ESCUELAS ---
        $limiteEdad = Configuracion::where('id', 1)->value('limite_menor_edad') ?? 18;
        
        $calcDistribucion = function ($coleccion, $limite) {
            $adultos = 0;
            $menores = 0;
            foreach ($coleccion as $m) {
                if ($m->user && $m->user->fecha_nacimiento) {
                    $fechaMatricula = Carbon::parse($m->fecha_matricula);
                    $edad = $m->user->fecha_nacimiento->diffInYears($fechaMatricula);
                    if ($edad < $limite) {
                        $menores++;
                    } else {
                        $adultos++;
                    }
                } else {
                    $adultos++;
                }
            }
            return ['adultos' => $adultos, 'menores' => $menores];
        };

        $subQueryLatestDateItem = Matricula::whereIn('user_id', $idsItem)
            ->whereBetween('fecha_matricula', [$inicio, $fin])
            ->select('user_id', DB::raw('MAX(fecha_matricula) as max_fecha'))
            ->groupBy('user_id');

        $latestMatriculaIdsItem = Matricula::joinSub($subQueryLatestDateItem, 'latest_dates_item', function ($join) {
            $join->on('matriculas.user_id', '=', 'latest_dates_item.user_id')
                ->on('matriculas.fecha_matricula', '=', 'latest_dates_item.max_fecha');
        })
            ->select(DB::raw('MAX(matriculas.id) as max_id'))
            ->groupBy('matriculas.user_id')
            ->pluck('max_id');

        $matriculasCollectionItem = Matricula::whereIn('id', $latestMatriculaIdsItem)
            ->whereHas('escuela', function ($q) {
                $q->where('habilitada_consolidacion', true);
            })
            ->with(['horarioMateriaPeriodo.horarioBase.aula.tipo', 'user:id,fecha_nacimiento'])
            ->get();

        $totalMatriculasItem = $matriculasCollectionItem->count();
        $userIdsMatriculadosItem = $matriculasCollectionItem->pluck('user_id')->unique();

        $sectorItem = $matriculasCollectionItem->filter(function ($m) {
            return optional(optional(optional(optional($m->horarioMateriaPeriodo)->horarioBase)->aula)->tipo)->sector == true;
        });
        $temploItem = $matriculasCollectionItem->filter(function ($m) {
            return optional(optional(optional(optional($m->horarioMateriaPeriodo)->horarioBase)->aula)->tipo)->sector == false;
        });

        $matriculasSectorItem = $sectorItem->count();
        $matriculasTemploItem = $temploItem->count();

        $distSectorItem = $calcDistribucion($sectorItem, $limiteEdad);
        $distTemploItem = $calcDistribucion($temploItem, $limiteEdad);
        
        $matriculasDesercionesItem = $matriculasCollectionItem->where('bloqueado', true)->count();
        $matriculasEfectivosItem = $totalMatriculasItem - $matriculasDesercionesItem;
        $porcentajeEfectividadMatriculasItem = $totalMatriculasItem > 0 ? round(($matriculasEfectivosItem / $totalMatriculasItem) * 100, 2) : 0;

        $matriculasUnionLibre = $this->getMatriculasUnionLibre($userIdsMatriculadosItem, $inicio, $fin);
        $matriculasAptos = $totalMatriculasItem - $matriculasUnionLibre;

        // --- DATOS MEMBRESÍAS ---
        $calcDistribucionUsuarios = function ($usuarios, $limite, $inicioDate) {
            $adultos = 0;
            $menores = 0;
            foreach ($usuarios as $u) {
                if ($u->fecha_nacimiento) {
                    $edad = $u->fecha_nacimiento->diffInYears($inicioDate);
                    if ($edad < $limite) {
                        $menores++;
                    } else {
                        $adultos++;
                    }
                } else {
                    $adultos++;
                }
            }
            return ['adultos' => $adultos, 'menores' => $menores];
        };

        $usuariosTrasladosItem = User::withTrashed()->whereIn('id', $idsItem)
            ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                $subQuery->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('tipoUsuarioNuevo', function ($q) {
                        $q->where('es_miembro_oficial', true);
                    });
            })
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin]);
                })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
            })
            ->whereHas('tipoVinculacion', function ($q) {
                $q->where('viene_de_otra_iglesia', true);
            })->select('id', 'fecha_nacimiento')->get();

        $usuariosBautismosItem = User::withTrashed()->whereIn('id', $idsItem)
            ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                $subQuery->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('tipoUsuarioNuevo', function ($q) {
                        $q->where('es_miembro_oficial', true);
                    });
            })
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin]);
                })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
            })
            ->where(function ($query) {
                $query->whereDoesntHave('tipoVinculacion')
                    ->orWhereHas('tipoVinculacion', function ($q) {
                        $q->where('viene_de_otra_iglesia', false);
                    });
            })->select('id', 'fecha_nacimiento')->get();

        $miembrosTrasladosItem = $usuariosTrasladosItem->count();
        $miembrosBautismosItem = $usuariosBautismosItem->count();

        $distTrasladosItem = $calcDistribucionUsuarios($usuariosTrasladosItem, $limiteEdad, $inicio);
        $trasladosAdultosItem = $distTrasladosItem['adultos'];
        $trasladosMenoresItem = $distTrasladosItem['menores'];

        $distBautismosItem = $calcDistribucionUsuarios($usuariosBautismosItem, $limiteEdad, $inicio);
        $bautismosAdultosItem = $distBautismosItem['adultos'];
        $bautismosMenoresItem = $distBautismosItem['menores'];

        $miembrosFormalizadosItemTotal = User::withTrashed()->whereIn('id', $idsItem)
            ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                $subQuery->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('tipoUsuarioNuevo', function ($q) {
                        $q->where('es_miembro_oficial', true);
                    });
            })
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin]);
                })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
            })
            ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
                $query->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b_ec2.id) FROM bitacora_estados_civiles as b_ec2 WHERE b_ec2.user_id = bitacora_estados_civiles.user_id AND b_ec2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('estadoCivilNuevo', function ($q) {
                        $q->where('es_matrimonio', true);
                    });
            })
            ->whereHas('bitacorasEstadoCivil', function ($query) use ($inicio, $fin) {
                $query->whereBetween('created_at', [$inicio, $fin])
                    ->whereHas('estadoCivilNuevo', function ($q) {
                        $q->where('es_union_libre', true);
                    });
            })->count();

        $totalMiembros = User::withTrashed()->whereIn('id', $idsItem)
            ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                $subQuery->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('tipoUsuarioNuevo', function ($q) {
                        $q->where('es_miembro_oficial', true);
                    });
            })
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin]);
                })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
            })->count();

        $miembrosUbicados = User::withTrashed()->whereIn('id', $idsItem)
            ->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
                $subQuery->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('tipoUsuarioNuevo', function ($q) {
                        $q->where('es_miembro_oficial', true);
                    });
            })
            ->where(function ($query) use ($inicio, $fin) {
                $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin]);
                })
                    ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                        $sub->whereBetween('created_at', [$inicio, $fin])
                            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                            ->where('dado_baja', false);
                    });
            })
            ->whereHas('bitacorasIntegranteGrupo', function ($query) use ($inicio, $fin) {
                $query->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b3.id) FROM bitacora_integrantes_grupo as b3 WHERE b3.user_id = bitacora_integrantes_grupo.user_id AND b3.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->where('estado_vinculacion', true);
            })->count();

        $totalUnionLibreMatriculados = $matriculasUnionLibre + $miembrosFormalizadosItemTotal;
        
        $ef_matriculas_membresias = $matriculasEfectivosItem > 0 ? round(($totalMiembros / $matriculasEfectivosItem) * 100, 2) : 0;
        $ef_ubicacion = $totalMiembros > 0 ? round(($miembrosUbicados / $totalMiembros) * 100, 2) : 0;
        $ef_formalizacion = $totalUnionLibreMatriculados > 0 ? round(($miembrosFormalizadosItemTotal / $totalUnionLibreMatriculados) * 100, 2) : 0;

        return [
            'total' => $totalItem,
            'efectiva' => $efectivaItem,
            'deserciones' => $totalItem - $efectivaItem,
            'porcentaje' => $porcentaje,
            'vinculaciones' => $vinculacionesArray,
            
            // Estadísticas de escuelas
            'total_matriculas' => $totalMatriculasItem,
            'matriculas_efectivas' => $matriculasEfectivosItem,
            'porcentaje_matriculas' => $porcentajeEfectividadMatriculasItem,
            'matriculas_templo' => $matriculasTemploItem,
            'matriculas_sector' => $matriculasSectorItem,
            'sector_adultos' => $distSectorItem['adultos'],
            'sector_warriors' => $distSectorItem['menores'],
            'templo_adultos' => $distTemploItem['adultos'],
            'templo_warriors' => $distTemploItem['menores'],
            'aptos' => $matriculasAptos,
            'union_libre' => $matriculasUnionLibre,

            // Estadísticas de membresías
            'total_miembros' => $totalMiembros,
            'ef_matriculas_membresias' => $ef_matriculas_membresias,
            'ubicados_grupos' => $miembrosUbicados,
            'ef_ubicacion' => $ef_ubicacion,
            'total_union_libre_mat' => $totalUnionLibreMatriculados,
            'pendientes_union_libre' => $matriculasUnionLibre,
            'formalizados' => $miembrosFormalizadosItemTotal,
            'ef_formalizacion' => $ef_formalizacion,
            'total_traslados' => $miembrosTrasladosItem,
            'traslados_adultos' => $trasladosAdultosItem,
            'traslados_warriors' => $trasladosMenoresItem,
            'total_bautismos' => $miembrosBautismosItem,
            'bautismos_adultos' => $bautismosAdultosItem,
            'bautismos_warriors' => $bautismosMenoresItem,
        ];
    }
}
