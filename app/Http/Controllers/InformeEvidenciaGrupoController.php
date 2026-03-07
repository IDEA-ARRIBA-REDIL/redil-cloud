<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\InformeEvidenciaGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Configuracion;
use Barryvdh\DomPDF\Facade\Pdf;

class InformeEvidenciaGrupoController extends Controller
{
    public function listar(Request $request, Grupo $grupo)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $filtroFechaIni = $request->get('filtroFechaIni') ? $request->get('filtroFechaIni') : \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $filtroFechaFin = $request->get('filtroFechaFin') ? $request->get('filtroFechaFin') : \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');

        $query = $grupo->informesEvidencias()->orderBy('fecha', 'desc');

        if ($filtroFechaIni && $filtroFechaFin) {
            $query->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);
        }

        $informes = $query->paginate(10);
        $meses = \App\Helpers\Helpers::meses('largo');

        

        return view('contenido.paginas.informes-evidencias-grupo.listar', compact('grupo', 'informes', 'filtroFechaIni', 'filtroFechaFin', 'meses','rolActivo'));
    }

    public function listarAdministrativo(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('grupos.subitem_informe_administrativo_de_evidencia_de_grupos');

        $filtroFechaIni = $request->get('filtroFechaIni') ? $request->get('filtroFechaIni') : \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $filtroFechaFin = $request->get('filtroFechaFin') ? $request->get('filtroFechaFin') : \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');

        $parametrosBusqueda = new \stdClass();
        $parametrosBusqueda->filtroGrupo = $request->get('filtroGrupo');

        // Determinar qué grupos puede ver el usuario
        $gruposIds = [];

        if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos')) {
            $gruposIds = Grupo::pluck('id')->toArray();
        } elseif ($rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio')) {
            $gruposIds = auth()->user()->gruposMinisterio('array');
        } elseif ($rolActivo->lista_grupos_sede_id != NULL) {
            $gruposIds = Grupo::where('sede_id', $rolActivo->lista_grupos_sede_id)->pluck('id')->toArray();
        }

        // Si se filtró por un grupo específico a través del Livewire
        if ($parametrosBusqueda->filtroGrupo) {
            $grupoRaiz = Grupo::find($parametrosBusqueda->filtroGrupo);
            if ($grupoRaiz) {
                $gruposIds = array($grupoRaiz->id);
            }
        }

        $informes = InformeEvidenciaGrupo::whereIn('grupo_id', $gruposIds)
            ->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin])
            ->with('grupo')
            ->orderBy('fecha', 'desc')
            ->paginate(12);

        $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        return view('contenido.paginas.informes-evidencias-grupo.listar-administrativo', compact('informes', 'filtroFechaIni', 'filtroFechaFin', 'meses', 'rolActivo', 'parametrosBusqueda'));
    }

    public function crear(Grupo $grupo)
    {
        $configuracion = Configuracion::first();
        return view('contenido.paginas.informes-evidencias-grupo.crear', compact('grupo', 'configuracion'));
    }

    public function store(Request $request, Grupo $grupo)
    {
        $configuracion = Configuracion::first();

        $rules = [
            'nombre' => 'required|max:100',
            'fecha' => 'required|date',
        ];

        if ($configuracion->habilitar_campo_1_informe_evidencias_grupo && $configuracion->campo_1_informe_evidencias_grupo_obligatorio) {
            $rules['campo1'] = 'required';
        }
        if ($configuracion->habilitar_campo_2_informe_evidencias_grupo && $configuracion->campo_2_informe_evidencias_grupo_obligatorio) {
            $rules['campo2'] = 'required';
        }
        if ($configuracion->habilitar_campo_3_informe_evidencias_grupo && $configuracion->campo_3_informe_evidencias_grupo_obligatorio) {
            $rules['campo3'] = 'required';
        }

        $messages = [
            'campo1.required' => 'Este campo es obligatorio.',
            'campo2.required' => 'Este campo es obligatorio.',
            'campo3.required' => 'Este campo es obligatorio.',
        ];

        $request->validate($rules, $messages);

        $grupo->informesEvidencias()->create($request->all());

        return redirect()->route('grupo.informeEvidencia.listar', $grupo)->with('success', 'Informe creado correctamente.');
    }

    public function editar(Grupo $grupo, InformeEvidenciaGrupo $informe)
    {
        $configuracion = Configuracion::first();
        return view('contenido.paginas.informes-evidencias-grupo.crear', compact('grupo', 'informe', 'configuracion'));
    }

    public function update(Request $request, Grupo $grupo, InformeEvidenciaGrupo $informe)
    {
        $configuracion = Configuracion::first();

        $rules = [
            'nombre' => 'required|max:100',
            'fecha' => 'required|date',
        ];

        if ($configuracion->habilitar_campo_1_informe_evidencias_grupo && $configuracion->campo_1_informe_evidencias_grupo_obligatorio) {
            $rules['campo1'] = 'required';
        }
        if ($configuracion->habilitar_campo_2_informe_evidencias_grupo && $configuracion->campo_2_informe_evidencias_grupo_obligatorio) {
            $rules['campo2'] = 'required';
        }
        if ($configuracion->habilitar_campo_3_informe_evidencias_grupo && $configuracion->campo_3_informe_evidencias_grupo_obligatorio) {
            $rules['campo3'] = 'required';
        }

        $messages = [
            'campo1.required' => 'Este campo es obligatorio.',
            'campo2.required' => 'Este campo es obligatorio.',
            'campo3.required' => 'Este campo es obligatorio.',
        ];

        $request->validate($rules, $messages);

        $informe->update($request->all());

        return redirect()->route('grupo.informeEvidencia.listar', $grupo)->with('success', 'Informe actualizado correctamente.');
    }

    public function eliminar(Request $request, Grupo $grupo, InformeEvidenciaGrupo $informe)
    {
        $informe->delete();

        if ($request->get('source') == 'admin') {
            return redirect()->route('grupo.informesEvidenciaAdministrativo')->with('success', 'Informe eliminado correctamente.');
        }

        return redirect()->route('grupo.informeEvidencia.listar', $grupo)->with('success', 'Informe eliminado correctamente.');
    }
    
    public function ver(Grupo $grupo, InformeEvidenciaGrupo $informe)
    {
         $configuracion = Configuracion::first();
        return view('contenido.paginas.informes-evidencias-grupo.ver', compact('grupo', 'informe', 'configuracion'));
    }

    public function descargar(Grupo $grupo, InformeEvidenciaGrupo $informe)
    {
        $configuracion = Configuracion::first();
        
        $pdf = Pdf::loadView('contenido.paginas.informes-evidencias-grupo.pdf', compact('grupo', 'informe', 'configuracion'));
        
        return $pdf->download('Informe-Evidencia-'.$informe->nombre.'-'.$informe->fecha.'.pdf');
    }
}
