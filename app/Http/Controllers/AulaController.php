<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\TipoAula;
use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\View\View;
use stdClass; // Para los tags de búsqueda
use App\Exports\AulasExport; // Importa la nueva clase de exportación
use Maatwebsite\Excel\Facades\Excel; // Importa el Facade de Excel
use Illuminate\Support\Str; // Para generar el nombre del archivo (opcional pero recomendado)
use Carbon\Carbon; // Para añadir fecha al nombre del archivo (opcional)

class AulaController extends Controller
{
    /**
     * Muestra una lista de todas las aulas.
     */
    public function gestionar(Request $request)
    {
        // Datos para los select de los filtros
        $sedes = Sede::orderBy('nombre')->get();
        $tipos_aula = TipoAula::orderBy('nombre')->get();

        // Inicialización para filtros y tags
        $tagsBusqueda = [];
        $banderaFiltros = 0;

        // Obtener valores de los filtros del request
        $filtroNombre = $request->input('filtro_nombre_aula');
        $filtroSedeId = $request->input('filtro_sede');
        $filtroTipoAulaId = $request->input('filtro_tipo_aula');

        // Construcción de la consulta base
        // Cargamos las relaciones 'sede' y 'tipo' para acceder a sus nombres
        // y evitar problemas de N+1 queries en la vista o al crear tags.
        $queryAulas = Aula::query()->with(['sede', 'tipo']);

        // Aplicar filtro por Nombre (insensible a mayúsculas/minúsculas)
        if ($filtroNombre) {
            $queryAulas->where('nombre', 'ilike', '%' . $filtroNombre . '%'); // Usamos ILIKE para PostgreSQL

            $tag = new stdClass();
            $tag->label = $filtroNombre;
            $tag->field = 'filtro_nombre_aula'; // ID del input en el HTML
            $tag->value = $filtroNombre;
            $tagsBusqueda[] = $tag;
            $banderaFiltros = 1;
        }

        // Aplicar filtro por Sede
        if ($filtroSedeId) {
            $queryAulas->where('sede_id', $filtroSedeId);
            $sedeModel = Sede::find($filtroSedeId);
            if ($sedeModel) {
                $tag = new stdClass();
                $tag->label = 'Sede: ' . $sedeModel->nombre;
                $tag->field = 'filtro_sede'; // ID del select en el HTML
                $tag->value = $filtroSedeId;
                $tagsBusqueda[] = $tag;
                $banderaFiltros = 1;
            }
        }

        // Aplicar filtro por Tipo de Aula
        if ($filtroTipoAulaId) {
            $queryAulas->where('tipo_aula_id', $filtroTipoAulaId);
            $tipoAulaModel = TipoAula::find($filtroTipoAulaId);
            if ($tipoAulaModel) {
                $tag = new stdClass();
                $tag->label = 'Tipo: ' . $tipoAulaModel->nombre;
                $tag->field = 'filtro_tipo_aula'; // ID del select en el HTML
                $tag->value = $filtroTipoAulaId;
                $tagsBusqueda[] = $tag;
                $banderaFiltros = 1;
            }
        }

        // Ordenar y paginar los resultados
        $aulas = $queryAulas->orderBy('nombre', 'asc')->paginate(12); // Puedes ajustar el número de ítems por página

        return view('contenido.paginas.escuelas.aulas.gestionar-aulas', [ // Asegúrate que la ruta a tu vista sea correcta
            'aulas' => $aulas,
            'sedes' => $sedes,
            'tipos_aula' => $tipos_aula,
            'tagsBusqueda' => $tagsBusqueda,
            'banderaFiltros' => $banderaFiltros,
            // Pasar los valores actuales de los filtros para rellenar el formulario del offcanvas
            'filtroNombreActual' => $filtroNombre,
            'filtroSedeIdActual' => $filtroSedeId,
            'filtroTipoAulaIdActual' => $filtroTipoAulaId,
        ]);
    }

    public function editar($aulaId)
    {
        $aula = Aula::with(['sede', 'tipo'])->findOrFail($aulaId);
        return response()->json($aula);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
            'nombre' => 'required|max:100',

            'sede_id' => 'required',
            'tipo_aula_id' => 'required',
            'descripcion' => 'nullable|string',

        ]);

        $aula = Aula::find($request->id);
        $aula->nombre = $request->nombre;
        $aula->sede_id = $request->sede_id;
        $aula->tipo_aula_id = $request->tipo_aula_id;
        $aula->descripcion = $request->descripcion;
        if ($request->activo == 'on') {
            $aula->activo = true;
        } else {
            $aula->activo = false;
        }

        $aula->save();

        return redirect()->route('aulas.gestionar')->with('success', 'Aula actualizada exitosamente.');
    }

    /**
     * Muestra el formulario para crear una nueva aula.
     */
    public function create()
    {
        return view('aulas.create');
    }

    /**
     * Guarda una nueva aula en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',

            'descripcion' => 'nullable|string',
        ]);


        $aula = new Aula();
        $aula->nombre = $request->nombre;
        $aula->sede_id = $request->sede_id;
        $aula->direccion = $request->direccion;
        $aula->tipo_aula_id = $request->tipo_aula_id;
        $aula->descripcion = $request->descripcion;
        if ($request->activo == 'on') {
            $aula->activo = true;
        } else {
            $aula->activo = false;
        }
        $aula->save();




        return redirect()->route('aulas.gestionar')->with('success', 'Aula creada exitosamente.');
    }

    public function eliminar($aulaId)
    {
        // Busca el aula o lanza una excepción si no existe
        $aula = Aula::findOrFail($aulaId);

        // Elimina todos los horarios base relacionados con el aula
        $aula->horariosBase()->delete();

        // Ahora elimina el aula
        $aula->delete();

        return redirect()->route('aulas.gestionar')->with('success', 'Aula eliminada exitosamente.');
    }

    public function exportarExcel(Request $request) // Recibe el Request para pasar filtros
    {
        // Genera un nombre de archivo descriptivo (opcionalmente con fecha)
        $fecha = Carbon::now()->format('Ymd-His');
        $fileName = "aulas-exportadas-{$fecha}.xlsx";

        // Crea una instancia de tu clase de exportación, pasando el Request actual
        $export = new AulasExport($request);

        // Dispara la descarga del archivo
        return Excel::download($export, $fileName);
    }

    // Aquí puedes agregar más métodos como show, edit, update, destroy, etc.
}
