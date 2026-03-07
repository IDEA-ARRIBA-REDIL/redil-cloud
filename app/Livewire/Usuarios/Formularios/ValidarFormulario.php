<?php

namespace App\Livewire\Usuarios\Formularios;

use App\Livewire\Generales\BarrioLocalidadBuscador;
use App\Models\CampoFormularioUsuario;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\Rule;
use Livewire\Attributes\On;

class ValidarFormulario extends Component
{
    public $formulario;
    public $usuario = null;
    public $paso = [0];
    public $data;
    public $errores;

    public function mount()
    {
    }

    #[On('validar')]
    public function validar($tipoValidacion, $seccionId, $dataSeccion)
    {
      $validacion = [];

      if($tipoValidacion == 'seccion')
      {
        $campos = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
        ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
        ->where('secciones_formulario_usuario.formulario_usuario_id','=', $this->formulario->id)
        ->where('secciones_formulario_usuario.id','=', $seccionId)
        ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido')
        ->get();
      }elseif($tipoValidacion == 'formulario'){
        $campos = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
        ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
        ->where('secciones_formulario_usuario.formulario_usuario_id','=', $this->formulario->id)
        ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido')
        ->get();
      }


      $this->paso = $dataSeccion;

      // primer_nombre
      if ($campos->where('nombre_bd','primer_nombre')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','primer_nombre')->first();
        $validarPrimerNombre = $campoTemporal->requerido ?  ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
        $validacion = array_merge($validacion, [ $campoTemporal->name_id => $validarPrimerNombre]);
      }

      // segundo_nombre
      if ($campos->where('nombre_bd','segundo_nombre')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','segundo_nombre')->first();
        $validarSegundoNombre = $campoTemporal->requerido ?  ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
        $validacion = array_merge($validacion, [ $campoTemporal->name_id  => $validarSegundoNombre]);
      }

      // primer_apellido
      if ($campos->where('nombre_bd','primer_apellido')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','primer_apellido')->first();
        $validarPrimerApellido = $campoTemporal->requerido ?  ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPrimerApellido]);
      }

      // segundo_apellido
      if ($campos->where('nombre_bd','segundo_apellido')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','segundo_apellido')->first();
        $validarSegundoApellido = $campoTemporal->requerido ?  ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSegundoApellido]);
      }

      //fecha_nacimiento
      if ($campos->where('nombre_bd','fecha_nacimiento')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','fecha_nacimiento')->first();
        $validarFechaNacimiento = $campoTemporal->requerido ? ['date', 'required'] : ['date', 'nullable'] ;
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarFechaNacimiento]);
      }

      // genero
      if ($campos->where('nombre_bd','genero')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','genero')->first();
        $validarGenero = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarGenero]);
      }

      // estado_civil
      if ($campos->where('nombre_bd','estado_civil_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','estado_civil_id')->first();
        $validarEstadoCivil = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEstadoCivil]);
      }

      // Tipo Identificacion
      if ($campos->where('nombre_bd','tipo_identificacion_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','tipo_identificacion_id')->first();
        $validarTipoIdentificacion = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoIdentificacion]);
      }

      // Identificacion
      if ($campos->where('nombre_bd','identificacion')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','identificacion')->first();
        $validarIdentificacion = $campoTemporal->requerido ? ['string', 'required', 'max:255', Rule::unique('users', 'identificacion')] : ['string', 'nullable', 'max:255', Rule::unique('users', 'identificacion')];
        if($this->usuario)
        {
          $validarIdentificacion = $campoTemporal->requerido ?  ['string', 'required', 'max:255', Rule::unique('users', 'identificacion')->ignore($this->usuario->id)] : ['string', 'nullable', 'max:255', Rule::unique('users', 'identificacion')->ignore($this->usuario->id)];
        }
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIdentificacion]);
      }

      // pais_nacimiento
      if ($campos->where('nombre_bd','pais_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','pais_id')->first();
        $validarPaisNacimiento = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPaisNacimiento]);
      }

      // vivienda_en_calidad_de
      if ($campos->where('nombre_bd','tipo_vivienda_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','tipo_vivienda_id')->first();
        $validarViviendaEnCalidadDe = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarViviendaEnCalidadDe]);
      }

      // direccion
      if ($campos->where('nombre_bd','direccion')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','direccion')->first();
        $validarDireccion = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarDireccion]);
      }

      // telefono_fijo
      if ($campos->where('nombre_bd','telefono_fijo')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','telefono_fijo')->first();
        $validarTelefonoFijo = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoFijo]);
      }

      // telefono_movil
      if ($campos->where('nombre_bd','telefono_movil')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','telefono_movil')->first();
        $validarTelefonoMovil = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoMovil]);
      }

      // telefono_otro
      if ($campos->where('nombre_bd','telefono_otro')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','telefono_otro')->first();
        $validarTelefonoOtro = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoOtro]);
      }

      // Email
      if ($campos->where('nombre_bd','email')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','email')->first();
        $validarEmail = $campoTemporal->requerido ? ['string', 'required', 'email', 'max:255', Rule::unique('users', 'email')] : ['string', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')];
        if($this->usuario)
        {
          $validarEmail = $campoTemporal->requerido ? ['string', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->usuario->id)] : ['string', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->usuario->id)];

        }
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEmail]);
      }

      // nivel_academico
      if ($campos->where('nombre_bd','nivel_academico_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','nivel_academico_id')->first();
        $validarNivelAcademico = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarNivelAcademico]);
      }

      // estado_nivel_academico
      if ($campos->where('nombre_bd','estado_nivel_academico_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','estado_nivel_academico_id')->first();
        $validarEstadoNivelAcademico = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEstadoNivelAcademico]);
      }

      // profesion
      if ($campos->where('nombre_bd','profesion_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','profesion_id')->first();
        $validarProfesion = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarProfesion]);
      }

      // ocupacion
      if ($campos->where('nombre_bd','ocupacion_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','ocupacion_id')->first();
        $validarOcupacion = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarOcupacion]);
      }

      //sector_economico
      if ($campos->where('nombre_bd','sector_economico_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','sector_economico_id')->first();
        $validarSectorEconomico = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSectorEconomico]);
      }


      //tipo_sangre
      if ($campos->where('nombre_bd','tipo_sangre_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','tipo_sangre_id')->first();
        $validarTipoSangre = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoSangre]);
      }

      //indicaciones_medicas
      if ($campos->where('nombre_bd','indicaciones_medicas')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','indicaciones_medicas')->first();
        $validarIndicacionesMedicas = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIndicacionesMedicas]);
      }

      //tipo_identificacion_acudiente
      if ($campos->where('nombre_bd','tipo_identificacion_acudiente_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','tipo_identificacion_acudiente_id')->first();
        $validarTipoIdentificacionAcudiente =  $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoIdentificacionAcudiente]);
      }

      //identificacion_acudiente
      if ($campos->where('nombre_bd','identificacion_acudiente')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','identificacion_acudiente')->first();
        $validarIdentificacionAcudiente =  $campoTemporal->requerido  ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIdentificacionAcudiente]);
      }

      //nombre_acudiente
      if ($campos->where('nombre_bd','nombre_acudiente')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','nombre_acudiente')->first();
        $validarNombreAcudiente =  $campoTemporal->requerido  ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarNombreAcudiente]);
      }

      //telefono_acudiente
      if ($campos->where('nombre_bd','telefono_acudiente')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','telefono_acudiente')->first();
        $validarTelefonoAcudiente =  $campoTemporal->requerido  ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoAcudiente]);
      }

      /*
        //archivo_a
        if ($campos->where('nombre_bd','archivo_a')->count() > 0) {
          $campoTemporal = $campos->where('nombre_bd','archivo_a')->first();
          if ($request->hasFile('archivo_a')) {
            $validarArchivoA = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
            $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoA]);
          }
        }

        //archivo_b
        if ($campos->where('nombre_bd','archivo_b')->count() > 0) {
          $campoTemporal = $campos->where('nombre_bd','archivo_b')->first();
          if ($request->hasFile('archivo_b')) {
            $validarArchivoB = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
            $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoB]);
          }
        }

        //archivo_c
        if ($campos->where('nombre_bd','archivo_c')->count() > 0) {
          $campoTemporal = $campos->where('nombre_bd','archivo_c')->first();
          if ($request->hasFile('archivo_c')) {
            $validarArchivoC = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
            $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoC]);
          }
        }

        //archivo_d
        if ($campos->where('nombre_bd','archivo_d')->count() > 0) {
          $campoTemporal = $campos->where('nombre_bd','archivo_d')->first();
          if ($request->hasFile('archivo_d')) {
            $validarArchivoD = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
            $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoD]);
          }
        }
      */

      //informacion_opcional
      if ($campos->where('nombre_bd','informacion_opcional')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','informacion_opcional')->first();
        $validarInformacionOpcional = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarInformacionOpcional]);
      }

      if ($campos->where('nombre_bd','campo_reservado')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','campo_reservado')->first();
        $validarInformacionOpcional = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarInformacionOpcional]);
      }

      //tipo_vinculacion
      if ($campos->where('nombre_bd','tipo_vinculacion_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','tipo_vinculacion_id')->first();
        $validarTipoVinculacion = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoVinculacion]);
      }

      // sede_id
      if ($campos->where('nombre_bd','sede_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','sede_id')->first();
        $validarSede = $campoTemporal->requerido ? ['numeric', 'required'] :  ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSede]);
      }

      // ubicacion localidad y barrio
      if ($campos->where('nombre_bd','ubicacion')->count() > 0)
      {
        if ($campos->where('nombre_bd','pregunta_vives_en')->count() > 0 )
        {
          $preguntaVivesEn = $campos->where('nombre_bd','pregunta_vives_en')->first();
          if (array_key_exists($preguntaVivesEn->name_id, $dataSeccion))
          {
            $campoTemporal = $campos->where('nombre_bd','ubicacion')->first();
            $validarUbicacion = $campoTemporal->requerido ? ['numeric', 'required'] :  ['numeric', 'nullable'];
            $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarUbicacion]);
          }

        }else{
          $campoTemporal = $campos->where('nombre_bd','ubicacion')->first();
          $validarUbicacion = $campoTemporal->requerido ? ['numeric', 'required'] :  ['numeric', 'nullable'];
          $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarUbicacion]);
        }
      }

      if ($campos->where('nombre_bd','password')->count() > 0)
      {
        $campoTemporal = $campos->where('nombre_bd','password')->first();
        $validarPassword = $campoTemporal->requerido ? ['required', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[*.?\-&$#]).+$/', 'confirmed'] :  ['nullable', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[*.?\-&$#]).+$/', 'confirmed'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPassword]);
      }

      //tipo_pariente_id
      if ($campos->where('nombre_bd','tipo_pariente_id')->count() > 0) {
        $campoTemporal = $campos->where('nombre_bd','tipo_pariente_id')->first();
        $validarTipoParentesco = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoParentesco]);
      }

      // tienes una peticion
      if ($campos->where('nombre_bd', 'tienes_una_peticion')->count() > 0) {
        $campoTienesUnaPeticion = $campos->where('nombre_bd', 'tienes_una_peticion')->first();
        if (array_key_exists($campoTienesUnaPeticion->name_id, $dataSeccion))
        {
          // tipo_peticion_id
          if ($campos->where('nombre_bd', 'tipo_peticion_id')->count() > 0) {
            $campoTemporal = $campos->where('nombre_bd', 'tipo_peticion_id')->first();
            $validarTipoPeticion = $campoTemporal->requerido ? ['required'] : ['nullable'];
            $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoPeticion]);
          }

          // descripcion_peticion
          if ($campos->where('nombre_bd', 'descripcion_peticion')->count() > 0) {
            $campoTemporal = $campos->where('nombre_bd', 'descripcion_peticion')->first();
            $validarDescripcionPeticion = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
            $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarDescripcionPeticion]);
          }
        }
      }

      /// seccion comprobacion campos extras
      foreach ($campos->where('es_campo_extra', true) as $campoExtra) {
        $validarCampoExtra = [];
        $campoExtra->requerido ? array_push($validarCampoExtra, 'required') : '';
        $validacion = array_merge($validacion, [$campoExtra->name_id => $validarCampoExtra]);
      }

      $validator = Validator::make($dataSeccion, $validacion);

      /* Reinincio de los mns error de los livewire */
      $this->dispatch('mostrarMensajeError',
      mostrarError: false,
      msnError: ''
      )->to(BarrioLocalidadBuscador::class);


      $this->dispatch('mostrarMensajeError',
      mostrarError: false,
      msnError: ''
      )->to(FechaNacimiento::class);
      /* Reinincio de los mns error de los livewire */

      if ($validator->fails()) {
        $this->errores = $validator->errors()->toArray();
        $this->dispatch('validacionFormulario', resultado: false, errores:$this->errores, data: $dataSeccion );

        // Dispatch error a los componente livewire

        if ($campos->where('nombre_bd','ubicacion')->count() > 0)
        {
          $ubicacion = $campos->where('nombre_bd','ubicacion')->first();
          if ($campos->where('nombre_bd','pregunta_vives_en')->count() > 0 )
          {
            $preguntaVivesEn = $campos->where('nombre_bd','pregunta_vives_en')->first();

            if( $validator->errors()->has($ubicacion->name_id))
            {
              $this->dispatch('mostrarMensajeError',
              mostrarError: true,
              msnError: $validator->errors()->first($ubicacion->name_id)
              )->to(BarrioLocalidadBuscador::class);
            }

          }else{
            if( $validator->errors()->has($ubicacion->name_id))
            {
              $this->dispatch('mostrarMensajeError',
              mostrarError: true,
              msnError: $validator->errors()->first($ubicacion->name_id)
              )->to(BarrioLocalidadBuscador::class);
            }
          }
        }

        //fecha_nacimiento
        if ($campos->where('nombre_bd','fecha_nacimiento')->count() > 0) {
          $fechaNacimiento = $campos->where('nombre_bd','fecha_nacimiento')->first();
          if( $validator->errors()->has($fechaNacimiento->name_id))
          {
            $this->dispatch('mostrarMensajeError',
            mostrarError: true,
            msnError: $validator->errors()->first($fechaNacimiento->name_id)
            )->to(FechaNacimiento::class);
          }else{
            $this->dispatch('mostrarMensajeError',
            mostrarError: false,
            msnError: ''
            )->to(FechaNacimiento::class);
          }
        }

      } else {
        $this->errores = "";
        $this->dispatch('validacionFormulario', resultado: true, errores:$this->errores, data: $dataSeccion,  seccionId: $seccionId);
      }
    }

    #[On('crearResumen')]
    public function crearResumen($dataSeccion)
    {
      $html = '';

      $this->paso = $dataSeccion;
      $secciones = $this->formulario->secciones()->orderBy('orden', 'asc')->get();
      foreach($secciones as $seccion)
      {
        $html.= '
        <div class="row px-2 py-2">
          <div class="d-flex">
            <div class="flex-grow-1">
              <p class="fs-5 text-black fw-semibold m-0 ">'.$seccion->titulo.'</p>
            </div>
            <div class="d-flex justify-content-start">
              <a class="btn text-info p-0 step-especifico" data-seccion="'.$seccion->orden.'"><i class="ti ti-pencil"></i></a>
            </div>
          </div>';

        $campos = $seccion->campos()->where('visible_resumen', true)->orderBy('orden', 'asc')->get();
        foreach($campos as $campo)
        {
          $valor = $dataSeccion[$campo->name_id];

          if($campo->nombre_bd == "password")
          {
            $html.= '
            <div class="col-12 col-md-6">
              <div class="py-2 border-bottom">
                <p class="fs-6 text-black m-0">'.$campo->nombre.'</p>
                <p class="fs-6 text-black fw-semibold m-0">***************</p>
              </div>
            </div>';
          }else{
            $html.= '
            <div class="col-12 col-md-6">
              <div class="py-2 border-bottom">
                <p class="fs-6 text-black m-0">'.$campo->nombre.'</p>
                <p class="fs-6 text-black fw-semibold m-0">'.$valor.'</p>
              </div>
            </div>';
          }
        }

        $html.= '</div>';
      }
      $this->dispatch('imprimirResumen', html: $html);
    }


    public function render()
    {
        return view('livewire.usuarios.formularios.validar-formulario'); // Asegúrate de crear esta vista
    }
}
