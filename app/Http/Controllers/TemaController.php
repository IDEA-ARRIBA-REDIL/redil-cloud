<?php

namespace App\Http\Controllers;

use App\Models\CategoriaTema;
use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\Tema;
use App\Models\TemaCategoria;
use App\Models\TipoGrupo;
use App\Models\Sede;
use App\Models\User;
use App\Models\TipoUsuario;
use App\Models\Grupo;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
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
      'configuracion' => $configuracion
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
      'rolActivo' => $rolActivo
    ]);
  }

  public function crear(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $configuracion = Configuracion::find(1);

    //validar si tiene nombre
    $request->validate(
      [
        'nombre_del_tema' => ['required']
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
    $tema->estado = TRUE;
    $tema->contenido = $html;

    if ($tema->save()) {

      // AÑADO LA PORTADA
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/temas/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'tema' . $tema->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $tema->portada = $nombreFoto;
          $tema->save();
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

    return back()->with('success', "El tema <b>" . $tema->titulo . "</b> fue creado con éxito.");
  }

  public function cargar(Request $request)
  {

    $configuracion = Configuracion::find(1);
    $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/temas');
    !is_dir($path) && mkdir($path, 0777, true);

    $imageFolder = 'storage/' . $configuracion->ruta_almacenamiento . '/img/temas/';

    $validatedData = $request->validate([
      'file' => 'required|file',
    ]);

    $path = $request->file('file')->store('public/' . $configuracion->ruta_almacenamiento . '/img/temas');
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
      'tiposUsuarioTema' => $tiposUsuarioTema
    ]);
  }

  public function update(Request $request, Tema $tema)
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $configuracion = Configuracion::find(1);

    ///validar el archivo
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
    $tema->estado = TRUE;
    $tema->contenido = $html;
    $tema->save();

    if ($tema->save()) {

      // AÑADO LA PORTADA
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/temas/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'tema' . $tema->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $tema->portada = $nombreFoto;
          $tema->save();
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

    return back()->with('success', "El tema <b>" . $tema->titulo . "</b> fue actualizado con éxito.");
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

    /// AQUI PRIMERO FILTRO LOS TEMAS TOTALES SI TIENE ESE PERMISO
    if ($rolActivo->hasPermissionTo('temas.ver_todos_los_temas')) {
      $temas = Tema::leftJoin('temas_categorias', 'temas.id', '=', 'temas_categorias.tema_id')
        ->select('temas.*', 'temas_categorias.categoria_tema_id')
        ->get();
    } else {
      ///AQUI ES PARA SOLO CARGAR LOS TEMAS QUE ME CORRESPONDEN
      $user = auth()->user();
      $grupos = $user->gruposDondeAsiste()->select('grupos.id')->pluck('grupos.id')->toArray();
      $tiposGrupo = $user->gruposDondeAsiste()->select('grupos.tipo_grupo_id')->pluck('grupos.tipo_grupo_id')->toArray();
      $sede = $user->sede;
      $tipoUsuario = $user->tipoUsuario;

      $temas = Tema::leftJoin('sedes_temas', 'temas.id', '=', 'sedes_temas.tema_id')
        ->leftJoin('tipos_usuarios_temas', 'temas.id', '=', 'tipos_usuarios_temas.tema_id')
        ->leftJoin('tipos_grupos_temas', 'temas.id', '=', 'tipos_grupos_temas.tema_id')
        ->leftJoin('grupos_temas', 'temas.id', '=', 'grupos_temas.tema_id')
        ->leftJoin('temas_categorias', 'temas.id', '=', 'temas_categorias.tema_id')
        ->where(function ($query) {
          return $query->where('sedes_temas.sede_id', null)
            ->where('tipos_usuarios_temas.tipo_usuario_id', null);
        })
        ->orWhere(function ($query) use ($sede, $tipoUsuario, $grupos, $tiposGrupo) {
          return $query->where('sedes_temas.sede_id', $sede->id);
        })->orWhere(function ($query) use ($tipoUsuario) {
          return $query->where('tipos_usuarios_temas.tipo_usuario_id', $tipoUsuario->id);
        })
        ->orWhere(function ($query) use ($tiposGrupo) {
          return $query->whereIn('tipos_grupos_temas.tipo_grupo_id', $tiposGrupo);
        })->orWhere(function ($query) use ($grupos) {
          return $query->whereIn('grupos_temas.grupo_id', $grupos);
        })
        ->select('temas.*', 'sedes_temas.sede_id', 'tipos_usuarios_temas.tipo_usuario_id', 'tipos_grupos_temas.tipo_grupo_id', 'grupos_temas.grupo_id', 'temas_categorias.categoria_tema_id')
        ->get();

      $temas = $temas->filter(function ($tema) use ($sede, $grupos, $tipoUsuario, $tiposGrupo) {
        $bandera = TRUE;
        // FILTRO DE TEMA POR SEDE
        if ($tema->sede_id  && $tema->sede_id != $sede->id) {
          $bandera = FALSE;
        }
        // FILTRO DE TEMA POR GRUPOS
        if ($tema->grupo_id && !in_array($tema->grupo_id, $grupos)) {
          $bandera = FALSE;
        }
        // FILTRO DE TEMA POR TIPOUSUARIO
        if ($tema->tipo_usuario_id  && $tema->tipo_usuario_id != $tipoUsuario->id) {
          $bandera = FALSE;
        }
        // FILTRO DE TEMA POR TIPOS GRUPO
        if ($tema->tipo_grupo_id && !in_array($tema->tipo_grupo_id, $tiposGrupo)) {
          $bandera = FALSE;
        }

        return $bandera;
      });
    }
    // Busqueda por palabra clave
    if ($request->buscar) {
      $buscar = htmlspecialchars($request->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $temas = $temas->filter(function ($tema) use ($palabra) {
          $respuesta  = false !== stristr(Helpers::sanearStringConEspacios($tema->titulo), $palabra);
          return $respuesta;
        });
      }

      $buscar = $request->buscar;
      $textoBusqueda .=  '<b> Con busqueda: </b>"' . $buscar . '" ';
      $bandera = 1;

      // Crear una tag
      $tag = new stdClass();
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
      $temas = $temas->whereIn('categoria_tema_id', $request->categorias);

      $cts = CategoriaTema::whereIn('id', $request->categorias)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $textoBusqueda .= '<b> Categoria: </b>"' . implode(', ', $cts) . '"';
      $bandera = 1;

      $categoriasTema = CategoriaTema::whereIn('id', $request->categorias)->select('nombre','id')->get();
      foreach($categoriasTema  as $categoria)
      {
        $tag = new stdClass();
        $tag->label = $categoria->nombre;
        $tag->field = 'categorias';
        $tag->value = $categoria->id;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;
      }
    }


    /// AQUI SE FINALIZA LA CONSULTO TOTAL
    if ($temas->count() > 0) {
      /// AQUI PONGO ESA FUNCION TOQUERY PORQUE DEBO PASARLO DEL FORMATO COLLECTION QUE USO PARA EL FILTER
      /// Y LUEGO DEBO PONERLA EN UN ARREGLO DE TIPO OBJETO PARA PODER HACER EL ORDER BY Y EL PAGINATE
      $temas =  $temas->toQuery()->orderBy('id', 'desc')->paginate(12);
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
        'rolActivo' => $rolActivo

      ]
    );
  }

  public function eliminar(Tema $tema)
  {
    $configuracion = Configuracion::find(1);

    if ($tema->portada != 'default.png')
      Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/img/temas' . '/' . $tema->portada);

    $tema->delete();
    return redirect()->route('tema.lista')->with('success', " El tema fue eliminado  con éxito.");
  }
}
