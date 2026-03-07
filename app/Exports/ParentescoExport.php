<?php

namespace App\Exports;

use App\Models\ParienteUsuario;
use App\Models\TipoParentesco;
use App\Models\CampoExtra;
use App\Models\CampoInformeExcel;
use App\Models\PasoCrecimiento;
use App\Models\User;
use App\Helpers\Helpers;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ParentescoExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */



    public function __construct($parametrosBusqueda, $camposRelacionesUsuarios, $arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra)
    {
      $this->camposRelacionesUsuarios = $camposRelacionesUsuarios;
      $this->arrayCamposInfoPersonal = $arrayCamposInfoPersonal;
      $this->arrayPasosCrecimiento = $arrayPasosCrecimiento;
      $this->arrayDatosCongregacionales = $arrayDatosCongregacionales;
      $this->arrayCamposExtra = $arrayCamposExtra;
      $this->parametrosBusqueda = $parametrosBusqueda;

    }


    public function view(): View
    {
        /// aqui envio las variables necesarias para ejecutar el controlador del excel y poder general la vista

      $parientes=[];
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $campos =  $this->camposRelacionesUsuarios->pluck('value')->toArray();
       array_push($campos,"parientes_usuarios.id","user_id");


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

      if(isset($this->parametrosBusqueda->buscador_usuario))
      {
        $parientes=$parientes->where('user_id',$this->parametrosBusqueda->buscador_usuario);  
    
      }

      
      

      /// relaciones segun el asistente

      if(isset($this->parametrosBusqueda->inputGruposIds) && !isset($this->parametrosBusqueda->buscador_usuario))
      {
       
        $grupo=Grupo::find($this->parametrosBusqueda->inputGruposIds);
       
        if ($this->parametrosBusqueda->tipoMinisterioSeleccionado == 0) 
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


      

        //

        return view('contenido.paginas.familias.exportar.exportarParentescos', [
            'parientes' => $parientes,
            'camposRelacionesUsuarios' => $this->camposRelacionesUsuarios,
            'arrayCamposInfoPersonal' => $this->arrayCamposInfoPersonal,
            'arrayPasosCrecimiento' => $this->arrayPasosCrecimiento,
            'arrayDatosCongregacionales' => $this->arrayDatosCongregacionales,
            'arrayCamposExtra' => $this->arrayCamposExtra
          ]);



         
    }
    
}
