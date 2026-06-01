<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\CategoriaTema;
use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\Sede;
use App\Models\Tema;
use App\Models\TipoGrupo;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use stdClass;

class TemaController extends Controller
{
    public function nuevo()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('temas.item_nuevo_tema');
        $gruposDondeAsisteIds = null;
        $sedes = Sede::get();
        $categorias = CategoriaTema::get();
        $tiposGrupo = TipoGrupo::get();
        $tiposUsuarios = TipoUsuario::get();
        $configuracion = Configuracion::find(1);

        return view('contenido.paginas.temas.nuevo-tema', [
            'categorias' => $categorias,
            'gruposDondeAsisteIds' => $gruposDondeAsisteIds,
            'usuario' => $rolActivo,
            'sedes' => $sedes,
            'tiposGrupo' => $tiposGrupo,
            'tiposUsuarios' => $tiposUsuarios,
            'configuracion' => $configuracion,
        ]);
    }

    public function ver(Tema $tema)
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('temas.ver_tema');

        return view('contenido.paginas.temas.ver-tema', [
            'tema' => $tema,
            'configuracion' => $configuracion,
            'rolActivo' => $rolActivo,
        ]);
    }

    public function crear(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $configuracion = Configuracion::find(1);

        // validar si tiene nombre
        $request->validate(
            [
                'nombre_del_tema' => ['required'],
            ]
        );

        // Saneo el contenidoEditor para que no tenga comillas dobles
        $html = $request->contenidoEditor;
        $html = str_replace("'", '', $html);
        $html = str_replace("\'", '', $html);

        $tema = new Tema;
        $tema->titulo = $request->nombre_del_tema;
        $tema->url = $request->url_externo;
        $tema->portada = 'default.png';
        $tema->estado = true;
        $tema->contenido = $html;

        if ($tema->save()) {

            // AÑADO LA PORTADA
            if ($request->foto) {
                $path = 'img/temas/';
                
                $imagenPartes = explode(';base64,', $request->foto);
                $imagenBase64 = base64_decode($imagenPartes[1]);
                $nombreFoto = 'tema'.$tema->id.'.png';
                
                Storage::put($path . $nombreFoto, $imagenBase64);
                
                $tema->portada = $nombreFoto;
                $tema->save();
            }

            //  CREO LA RELACIÓN CON LAS SEDES
            $tema->sedes()->attach($request->sedes);
            //  CREO LA RELACIÓN CON LAS CATEGORIAS
            $tema->categorias()->attach($request->categorias);
            //  CREO LA RELACIÓN CON LOS TIPOS DE USUARIOS
            $tema->tiposUsuarios()->attach($request->tipoUsuarios);
            //  CREO LA RELACIÓN CON LOS TIPOS DE GRUPO
            $tema->tiposGrupos()->attach($request->tipoGrupo);
            //  CREO LA RELACIÓN CON LOS  GRUPOS
            $tema->temasGrupos()->attach(json_decode($request->inputGruposIds));
        }

        return back()->with('success', 'El tema <b>'.$tema->titulo.'</b> fue creado con éxito.');
    }

    public function cargar(Request $request)
    {
        $validatedData = $request->validate([
            'file' => 'required|file',
        ]);

        $path = $request->file('file')->store('img/temas');

        return ['location' => Storage::url($path)];
    }

    public function actualizar(Tema $tema)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('temas.editar_tema');

        $sedes = Sede::get();
        $categorias = CategoriaTema::get();
        $tiposGrupo = TipoGrupo::get();
        $tiposUsuarios = TipoUsuario::get();
        $configuracion = Configuracion::find(1);
        $temas_categoria = $tema->categorias()->select('categoria_tema_id')->pluck('categoria_tema_id')->toArray();
        $gruposSeleccionadosIds = $tema->temasGrupos()->select('grupos.id')->pluck('grupos.id')->toArray();
        $tiposGrupoTema = $tema->tiposGrupos()->select('tipo_grupos.id')->pluck('tipo_grupos.id')->toArray();
        $sedesTema = $tema->sedes()->select('sedes.id')->pluck('sedes.id')->toArray();
        $tiposUsuarioTema = $tema->tiposUsuarios()->select('tipo_usuarios.id')->pluck('tipo_usuarios.id')->toArray();

        return view('contenido.paginas.temas.actualizar-tema', [
            'categorias' => $categorias,
            'tema' => $tema,
            'configuracion' => $configuracion,
            'rolActivo' => $rolActivo,
            'temas_categoria' => $temas_categoria,
            'sedes' => $sedes,
            'tiposGrupo' => $tiposGrupo,
            'tiposUsuarios' => $tiposUsuarios,
            'gruposSeleccionadosIds' => $gruposSeleccionadosIds,
            'tiposGrupoTema' => $tiposGrupoTema,
            'sedesTema' => $sedesTema,
            'tiposUsuarioTema' => $tiposUsuarioTema,
        ]);
    }

    public function update(Request $request, Tema $tema)
    {

        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $configuracion = Configuracion::find(1);

        // /validar el archivo
        $request->validate(
            [
                'nombre_del_tema' => ['required'],
            ]
        );

        $html = $request->contenidoEditor;
        $html = str_replace("'", '', $html);
        $html = str_replace("\'", '', $html);

        $tema->titulo = $request->nombre_del_tema;
        $tema->url = $request->url_externo;
        $tema->estado = true;
        $tema->contenido = $html;
        $tema->save();

        if ($tema->save()) {

            // AÑADO LA PORTADA
            if ($request->foto) {
                $path = 'img/temas/';
                
                $imagenPartes = explode(';base64,', $request->foto);
                $imagenBase64 = base64_decode($imagenPartes[1]);
                $nombreFoto = 'tema'.$tema->id.'.png';
                
                Storage::put($path . $nombreFoto, $imagenBase64);
                
                $tema->portada = $nombreFoto;
                $tema->save();
            }

            // PRIMERO CREO LA RELACIÓN CON LAS SEDES
            $tema->sedes()->sync($request->sedes);
            // PRIMERO CREO LA RELACIÓN CON LAS CATEGORIAS
            $tema->categorias()->sync($request->categorias);
            //  CREO LA RELACIÓN CON LOS TIPOS DE USUARIOS
            $tema->tiposUsuarios()->sync($request->tipoUsuarios);
            //  CREO LA RELACIÓN CON LOS TIPOS DE GRUPOS
            $tema->tiposGrupos()->sync($request->tipoGrupo);
            //  CREO LA RELACIÓN CON LOS  GRUPOS
            $tema->temasGrupos()->sync(json_decode($request->inputGruposIds));
        }

        return back()->with('success', 'El tema <b>'.$tema->titulo.'</b> fue actualizado con éxito.');
    }

    public function listar(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('temas.item_listado_temas');

        $configuracion = Configuracion::find(1);
        $categorias = CategoriaTema::get();
        $buscar = '';
        $textoBusqueda = '';
        $tagsBusqueda = [];
        $bandera = 0;
        $temas = [];

        // / AQUI PRIMERO FILTRO LOS TEMAS TOTALES SI TIENE ESE PERMISO
        $temas = Tema::filtrarTemasPermitidos(auth()->user(), $rolActivo);
        // Busqueda por palabra clave
        if ($request->buscar) {
            $buscar = htmlspecialchars($request->buscar);
            $buscar = Helpers::sanearStringConEspacios($buscar);
            $buscar = str_replace(["'"], '', $buscar);
            $buscar_array = explode(' ', $buscar);

            foreach ($buscar_array as $palabra) {
                $temas = $temas->where('temas.titulo', 'like', '%'.$palabra.'%');
            }

            $buscar = $request->buscar;
            $textoBusqueda .= '<b> Con busqueda: </b>"'.$buscar.'" ';
            $bandera = 1;

            // Crear una tag
            $tag = new stdClass;
            $tag->label = $request->buscar;
            $tag->field = 'buscar';
            $tag->value = $request->buscar;
            $tag->fieldAux = '';
            $tagsBusqueda[] = $tag;
        }

        // BUSQUEDA POR CATEGORIAS
        $categoriasSeleccionadas = [];

        if ($request->categorias) {
            $categoriasSeleccionadas = $request->categorias;
            $temas = $temas->whereHas('categorias', function ($query) use ($categoriasSeleccionadas) {
                $query->whereIn('categoria_tema_id', $categoriasSeleccionadas);
            });

            $cts = CategoriaTema::whereIn('id', $request->categorias)
                ->select('nombre')
                ->pluck('nombre')
                ->toArray();

            $textoBusqueda .= '<b> Categoria: </b>"'.implode(', ', $cts).'"';
            $bandera = 1;

            $categoriasTema = CategoriaTema::whereIn('id', $request->categorias)->select('nombre', 'id')->get();
            foreach ($categoriasTema as $categoria) {
                $tag = new stdClass;
                $tag->label = $categoria->nombre;
                $tag->field = 'categorias';
                $tag->value = $categoria->id;
                $tag->fieldAux = '';
                $tagsBusqueda[] = $tag;
            }
        }

        // / AQUI SE FINALIZA LA CONSULTO TOTAL
        if ($temas->count() > 0) {
            // / AQUI PONGO ESA FUNCION TOQUERY PORQUE DEBO PASARLO DEL FORMATO COLLECTION QUE USO PARA EL FILTER
            // / Y LUEGO DEBO PONERLA EN UN ARREGLO DE TIPO OBJETO PARA PODER HACER EL ORDER BY Y EL PAGINATE
            $temas = $temas->orderBy('temas.id', 'desc')->paginate(12);
        } else {
            $temas = Tema::whereRaw('1=2')->paginate(12);
        }

        return view(
            'contenido.paginas.temas.listar-temas',
            [
                'temas' => $temas,
                'categorias' => $categorias,
                'buscar' => $buscar,
                'configuracion' => $configuracion,
                'textoBusqueda' => $textoBusqueda,
                'tagsBusqueda' => $tagsBusqueda,
                'bandera' => $bandera,
                'categoriasSeleccionadas' => $categoriasSeleccionadas,
                'rolActivo' => $rolActivo,

            ]
        );
    }

    public function eliminar(Tema $tema)
    {
        if ($tema->portada != 'default.png') {
            $path = 'img/temas/';
            Storage::delete($path . $tema->portada);
        }

        $tema->delete();

        return redirect()->route('tema.lista')->with('success', ' El tema fue eliminado  con éxito.');
    }
}
