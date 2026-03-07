<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Grupo;
use App\Models\IntegranteGrupo;
use App\Models\TipoUsuario;
use App\Models\ParienteUsuario;
use App\Models\Configuracion;
use App\Models\TipoParentesco;
use App\Models\CampoExtra;
use App\Models\CampoInformeExcel;
use App\Models\PasoCrecimiento;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ParentescoExport;

use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;


class ParienteUsuarioController extends Controller
{
    //gestionar

    public function gestionar(Request $request, $userId=null)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        // verificar si cumple el permiso
        $rolActivo->verificacionDelPermiso('familiar.subitem_gentionar_relaciones');


        $configuracion=Configuracion::find(1);
        $usuario='';
        $parientes=array();
        $tiposParentesco=TipoParentesco::get();
        $html='';
        $usuario=User::find($userId);

        if(isset($userId))
        {

          $parientes = $usuario
          ->parientesDelUsuario()
          ->leftJoin('tipos_parentesco', 'parientes_usuarios.tipo_pariente_id', '=', 'tipos_parentesco.id')
          ->select(
            'users.id',
            'users.foto',
            'users.identificacion',
            'users.primer_nombre',
            'users.segundo_nombre',
            'users.primer_apellido',
            'users.segundo_apellido',
            'users.tipo_identificacion_id',
            'tipos_parentesco.nombre as nombre_parentesco',
            'tipos_parentesco.nombre_masculino',
            'tipos_parentesco.nombre_femenino',
            'parientes_usuarios.es_el_responsable',
            'parientes_usuarios.id'
          )
          ->get();

        }

      return view('contenido.paginas.familias.gestionar',[

        'configuracion'=>$configuracion,
        'userId'=>$userId,
        'parientes'=>$parientes,
        'usuario'=>$usuario,
        'tiposParentesco'=>$tiposParentesco,
        'rolActivo' => $rolActivo
        ]);
    }

    public function crear(Request $request)
    {

      if(!$request->parientePrincipal || !$request->buscador_asistente_modal)
      {
        return back()->with('danger', "No fue posible crear la relación, todos los campos son necesarios, intenta de nuevo.");
      }

      $parientePrincipal=User::find($request->parientePrincipal);
      $parientedelModal=User::find($request->buscador_asistente_modal);
      $parentescoActual=ParienteUsuario::where('pariente_user_id',$parientedelModal->id)
      ->where('user_id',$parientePrincipal->id)->first();

      if($parientePrincipal == $parientedelModal )
      {
        return back()->with('danger', "No fue posible crear la relación, elegiste en ambos casos el mismo asistente.");
      }elseif(isset($parentescoActual->id))
      {
        return back()->with('danger', "No fue posible crear la relación, ya existe una relación actualmente.");
      }else
      {
        $tiposParentescoSeleccionado=TipoParentesco::find($request->tipoParentesco);
        $tipoParentescoPariente=$tiposParentescoSeleccionado;

        if(isset($tiposParentescoSeleccionado->relacionado_con))
        {
          $tipoParentescoPariente=TipoParentesco::find($tiposParentescoSeleccionado->relacionado_con);
        }

        $responsabilidad = $request->responsabilidad;
        $asistenteResponsable = false;
        $parienteResponsable = false;

        if( $responsabilidad == 2 )
          $asistenteResponsable = true;
        elseif($responsabilidad == 3)
          $parienteResponsable = true;

          // Esta es la relacion del asistente con el pariente
            $parientePrincipal->usuariosDelPariente()->attach($parientedelModal->id,
            array(
              "es_el_responsable"=> $parienteResponsable ,
              "tipo_pariente_id"=>  $tipoParentescoPariente->id,
            ));

            // Esta es la relacion del pariente con el asistente
            $parientePrincipal->parientesDelUsuario()->attach($parientedelModal->id,
            array(
              "es_el_responsable"=> $asistenteResponsable ,
              "tipo_pariente_id"=>  $tiposParentescoSeleccionado->id,
            ));

      }

      return back()->with('success', "La relación fue creada con éxito.");

    }

    public function eliminar(ParienteUsuario $pariente)
    {
      $usuarioSeleccioanado=$pariente->user_id;
      $relacion1=ParienteUsuario::where('user_id',$pariente->user_id)->where('pariente_user_id',$pariente->pariente_user_id)->first();

      $relacion2=ParienteUsuario::where('pariente_user_id',$pariente->user_id)->where('user_id',$pariente->pariente_user_id)->first();

      if(isset($relacion1))
      $relacion1->delete();

       if(isset($relacion2))
      $relacion2->delete();


      return redirect()->route('familias.gestionar',$usuarioSeleccioanado)->with('success', " El tema fue eliminado  con éxito.");
    }

    public function informes(Request $request)
    {

        $configuracion=Configuracion::find(1);
        $usuario='';
        $parientes=[];
        $tiposParentesco=TipoParentesco::get();
        $buscar='';
        $userId=$request->buscador_usuario;
        $grupoId=$request->inputGruposIds;
        $tipoMinisterioSeleccionado=$request->filtroTipoMinisterio;

         $user=auth()->user();


        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        if (
          $rolActivo->hasPermissionTo('personas.lista_asistentes_todos') ||
          $rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')
        ) {

              if($rolActivo->hasPermissionTo('personas.lista_asistentes_todos'))
              {
                  $parientes=DB::table('users')->leftJoin('parientes_usuarios','parientes_usuarios.user_id','users.id')
                  ->where('user_id','!=',null);
              }else{

                $parientes = auth()
                ->user()
                ->discipulos('todos')->toQuery()->leftJoin('integrantes_grupo','grupo_id', 'integrantes_grupo.user_id')
                      ->leftJoin('parientes_usuarios','parientes_usuarios.user_id','users.id')
                      ->where('parientes_usuarios.user_id','!=',null);
              }

          }



          if(isset($userId))
        {
          $parientes=$parientes->where('users.id',$userId);

        }


        if(isset($grupoId) && !isset($userId))
        {

          $grupo=Grupo::find($grupoId);

          if ($tipoMinisterioSeleccionado == 0)
          {
            $gruposIds = $grupo->gruposMinisterio('array');

            //Agrego el id del grupo que estoy consultado
            array_push($gruposIds, $grupo->id);

            $idsUsers = IntegranteGrupo::whereIn('grupo_id', $gruposIds)
              ->select('user_id')
              ->pluck('user_id')
              ->toArray();

          } else {
            $idsUsers = IntegranteGrupo::where('grupo_id', '=', $grupo->id)
              ->select('user_id')
              ->pluck('user_id')
              ->toArray();

          }
          // aqui traigo solo los ids unicos, debido a que una persona puede estar en varios grupos pero no necesito traer varias veces su relaciones

          $parientes=$parientes->whereIn('users.id',$idsUsers);

        };

        /// aqui se cierra la consulta porque es necesario para convertirla en una collection y poder hacer el mapeo
        $parientes=$parientes->orderBy('users.id','desc')->paginate(12);


        /// aqui mapeo la collection para poder agregarle algunos datos al arreglo que
        ///voy a necesitar para construir las cards
        $parientes->map( function($pariente) use($usuario)
        {
          ///aqui tengo que detectar el usuario del otro lado de la relacion o sea el parienteSecundario o parienteB
          $usuarioParienteSecundario=User::find($pariente->pariente_user_id);
          $tipoParentesco=TipoParentesco::find($pariente->tipo_pariente_id);
          /// luego traigo todas las relaciones entre ambos usuarios
          $relacion=$usuarioParienteSecundario->parientesDelUsuario()
          ->leftJoin('tipos_parentesco', 'parientes_usuarios.tipo_pariente_id', '=', 'tipos_parentesco.id')
          ->where('user_id',$usuarioParienteSecundario->id)
          ->where('pariente_user_id',$pariente->user_id)
          ->select(
            'tipos_parentesco.nombre as nombre_parentesco',
            'tipos_parentesco.nombre_masculino',
            'tipos_parentesco.nombre_femenino',
            'parientes_usuarios.es_el_responsable',
            'parientes_usuarios.id'
          )
          ->first();

            /// aqui como es una collection, le agrego los registros nuevos que voy a necesitar para construir
            /// la card en la vista
          $pariente->nombreParienteSecundario=$usuarioParienteSecundario->nombre(3);
          $pariente->fotoParienteSecundario=$usuarioParienteSecundario->foto;
          $pariente->generoParienteSecundario=$usuarioParienteSecundario->genero;
          $pariente->relacion_id=$relacion->id;
          $pariente->responsableParienteSecundario=$relacion->es_el_responsable;
          $pariente->nombre_masculino= $tipoParentesco->nombre_masculino;
          $pariente->nombre_femenino= $tipoParentesco->nombre_femenino;

        });

        /// estos son las variable para el modal del excel que se abre con el boton de excel
        $camposInformeExcel = CampoInformeExcel::orderBy('orden', 'asc')->get();
        $pasosCrecimiento = PasoCrecimiento::orderBy('updated_at', 'asc')->get();
        $camposExtras = CampoExtra::where('visible', '=', true)->get();
        $camposRelacionesUsuarios=collect(Helpers::camposRelacionesUsuarios());



      return view('contenido.paginas.familias.informes',[

        'configuracion'=>$configuracion,
        'userId'=>$userId,
        'parientes'=>$parientes,
        'usuario'=>$usuario,
        'tipoMinisterioSeleccionado'=>$tipoMinisterioSeleccionado,
        'grupoId'=>$grupoId,
        'camposInformeExcel'=>$camposInformeExcel,
        'pasosCrecimiento'=>$pasosCrecimiento,
        'camposExtras'=>$camposExtras,
        'camposRelacionesUsuarios'=>$camposRelacionesUsuarios

        ]);
    }

    public function generarExcel(Request $request)
    {
      /// aqui lo que pasa es lo siguiente se generan todas las variables necesarias para hacer funcionar
      /// el controlador del exportar del excel que estamos haciendo en este momento
      $configuracion=Configuracion::find(1);
      /// estos son todos los campos especificos del informe de relaciones
      $camposRelacionesUsuarios=collect(Helpers::camposRelacionesUsuarios());

      /// aqui los filtro segun los que seleccionaron en el modal
      $camposRelacionesUsuarios=$camposRelacionesUsuarios->whereIn('id',$request->camposRelacionesUsuarios);
      /// aqui estan son los campos de los filtros antes del modal, en este caso usuario, grupo y tipo de grupo
      $parametrosBusqueda = json_decode($request->parametrosBusqueda);

      //// aqui son los campos del informe que creamos nosotros para que siempre esten ahi
      $arrayCamposInfoPersonal = $request->informacionPersonal ? $request->informacionPersonal : []; //$arrayCamposInfoPersonal
      $arrayPasosCrecimiento = $request->informacionMinisterial ? $request->informacionMinisterial : []; // $arrayPasosCrecimiento
      $arrayDatosCongregacionales = $request->informacionCongregacional ? $request->informacionCongregacional : []; // $arrayDatosCongregacionales
      $arrayCamposExtra = $request->informacionCamposExtras ? $request->informacionCamposExtras : []; // $arrayCamposExtra

      /// aqui lo que se hace es crear el archivo que se va a crear en el servidor
      $nombreArchivo = 'informe_parentescos_' . Carbon::now()->format('Y-m-d-H-i-s');
      $rutaArchivo = "/$configuracion->ruta_almacenamiento/informes/familias/$nombreArchivo.xlsx";

      /// aqui envio las variables necesarias para ejecutar el controlador del excel y poder general la vista

      $parientes=[];
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $campos =  $camposRelacionesUsuarios->pluck('value')->toArray();
       array_push($campos,"parientes_usuarios.id","user_id");

      /*
        ESTE CODIGO LO DEJO ACA PORQUE ES DE EJEMPLO PARA CONSTRIUR LA CONSULTA
        QUE USE PARA EXPORTAR EL EXCEL

                //// aqui es para consultar las relaciones segun le pertenezcan todas o solo ministerio
                if (
                    $rolActivo->hasPermissionTo('personas.lista_asistentes_todos') ||
                    $rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')
                  ) {

                        if($rolActivo->hasPermissionTo('personas.lista_asistentes_todos'))
                        {

                            $parientes= ParienteUsuario::leftJoin('users','parientes_usuarios.user_id','users.id')
                            ->where('user_id','!=',null);
                        }else{

                          $parientes = auth()
                          ->user()
                          ->discipulos('todos')->toQuery()->leftJoin('integrantes_grupo','grupo_id', 'integrantes_grupo.user_id')
                                ->leftJoin('parientes_usuarios','parientes_usuarios.user_id','users.id')
                                ->where('parientes_usuarios.user_id','!=',null);
                        }

                    }



                /// relaciones segun el asistente

                if(isset($parametrosBusqueda->buscador_usuario))
                {
                  $parientes=$parientes->where('user_id',$parametrosBusqueda->buscador_usuario);

                }




                /// relaciones segun el asistente

                if(isset($parametrosBusqueda->inputGruposIds) && !isset($parametrosBusqueda->buscador_usuario))
                {

                  $grupo=Grupo::find($parametrosBusqueda->inputGruposIds);

                  if ($tipoMinisterioSeleccionado == 0)
                  {
                    $gruposIds = $grupo->gruposMinisterio('array');

                    //Agrego el id del grupo que estoy consultado
                    array_push($gruposIds, $grupo->id);

                    $idsUsers = IntegranteGrupo::whereIn('grupo_id', $gruposIds)
                      ->select('user_id')
                      ->pluck('user_id')
                      ->toArray();

                  } else {
                    $idsUsers = IntegranteGrupo::where('grupo_id', '=', $grupo->id)
                      ->select('user_id')
                      ->pluck('user_id')
                      ->toArray();

                  }
                  // aqui traigo solo los ids unicos, debido a que una persona puede estar en varios grupos pero no necesito traer varias veces su relaciones

                  $parientes=$parientes->whereIn('users.id',$idsUsers);

                };


              //$parientes->select('parientes_usuarios.*','users.primer_nombre','users.segundo_nombre', 'users.primer_apellido')->get();
              $parientes=$parientes->select('parientes_usuarios.*','users.primer_nombre','users.segundo_nombre', 'users.genero','users.primer_apellido')->get();
              $parientes->map( function($pariente)
              {
                ///aqui tengo que detectar el usuario del otro lado de la relacion o sea el parienteSecundario o parienteB
                $usuarioParienteSecundario=User::find($pariente->pariente_user_id);
                $tipoParentesco=TipoParentesco::find($pariente->tipo_pariente_id);
                /// luego traigo todas las relaciones entre ambos usuarios
                $relacion=$usuarioParienteSecundario->parientesDelUsuario()
                ->leftJoin('tipos_parentesco', 'parientes_usuarios.tipo_pariente_id', '=', 'tipos_parentesco.id')
                ->where('user_id',$usuarioParienteSecundario->id)
                ->where('pariente_user_id',$pariente->user_id)
                ->select(
                  'tipos_parentesco.nombre as nombre_parentesco',
                  'tipos_parentesco.nombre_masculino',
                  'tipos_parentesco.nombre_femenino',
                  'parientes_usuarios.es_el_responsable',
                  'parientes_usuarios.id'
                )
                ->first();

                  /// aqui como es una collection, le agrego los registros nuevos que voy a necesitar para construir
                  /// la card en la vista
                $pariente->nombreParienteSecundario=$usuarioParienteSecundario->nombre(3);
                $pariente->fotoParienteSecundario=$usuarioParienteSecundario->foto;
                $pariente->generoParienteSecundario=$usuarioParienteSecundario->genero;
                $pariente->relacion_id=$relacion->id;
                $pariente->responsableParienteSecundario=$relacion->es_el_responsable;
                $pariente->nombre_masculino= $tipoParentesco->nombre_masculino;
                $pariente->nombre_femenino= $tipoParentesco->nombre_femenino;

              });


            return view('contenido.paginas.familias.exportar.exportarParentescos', [
              'parientes' => $parientes,
              'camposRelacionesUsuarios' => $camposRelacionesUsuarios,
              'arrayCamposInfoPersonal' => $arrayCamposInfoPersonal,
              'arrayPasosCrecimiento' => $arrayPasosCrecimiento,
              'arrayDatosCongregacionales' => $arrayDatosCongregacionales,
              'arrayCamposExtra' => $arrayCamposExtra
            ]);

      */

      Excel::store(
        new ParentescoExport( $parametrosBusqueda, $camposRelacionesUsuarios, $arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra ),
        $rutaArchivo,
        'public'
      );


      return back()->with(
        'success',
        'El informe fue generado con éxito, <a href="'.Storage::url($rutaArchivo).'" class=" link-success fw-bold" download="'.$nombreArchivo.'.xlsx"> descargalo aquí</a>'
      );



    }


}
