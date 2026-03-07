<?php

namespace App\Http\Controllers;

use App\Models\FormularioUsuario;
use App\Models\Role;
use App\Models\TipoFormularioUsuario;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;

class FormularioUsuarioController extends Controller
{
  public function listar()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('configuraciones.subitem_gestionar_formulario_usuarios');
    return view('contenido.paginas.formularios-usuarios.listar', []);
  }

  public function listarCampos()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('configuraciones.subitem_gestionar_campos_formulario_usuario');

    return view('contenido.paginas.campos-formulario-usuario.listar', []);
  }

  public function nuevo()
  {
    $roles = Role::select('id','name','icono')->orderBy('name','asc')->get();
    $tipos = TipoFormularioUsuario::orderBy('nombre','asc')->get();
    $tiposUsuario = TipoUsuario::orderBy('nombre','asc')->get();

    return view('contenido.paginas.formularios-usuarios.nuevo', [
      'roles' => $roles,
      'tipos' => $tipos,
      'tiposUsuario' => $tiposUsuario
    ]);
  }

  public function crear(Request $request)
  {
    // Validación
    $validacion = [
      'nombre' => ['max:100', 'required'],
      'título' => ['max:100', 'required'],
      'tipoDeFormulario' => ['required']
    ];

    $request->términosCondiciones
    ? $validacion = array_merge($validacion, ['términosYCondiciones' => 'required'])
    : '';

    $request->validarEdad
    ? $validacion = array_merge($validacion, ['edadMínima' => 'numeric|required' , 'edadMaxima' => 'numeric|required'])
    : '';

    // Validacion de datos
    $request->validate($validacion);

    $formulario = new FormularioUsuario;
    $formulario->nombre = $request->nombre;
    $formulario->titulo = $request->título;
    $formulario->label = $request->etiqueta;
    $formulario->descripcion = $request->descripción;
    $formulario->validar_edad = $request->validarEdad ? TRUE : FALSE;
    $formulario->edad_minima = $request->edadMínima;
    $formulario->edad_maxima = $request->edadMaxima;
    $formulario->edad_mensaje_error = $request->mensaje;
    $formulario->visible_terminos_condiciones = $request->términosCondiciones ? TRUE : FALSE;
    $formulario->url_terminos_condiciones = $request->url;
    $formulario->mensaje_terminos_condiciones_resumen = $request->términosYCondiciones;
    $formulario->mensaje_terminos_condiciones_detallado = $request->términosYCondicionesDetallado;
    $formulario->tipo_formulario_id = $request->tipoDeFormulario;
    $formulario->tipo_usuario_default_id = $request->tipoUsuarioPorDefecto;


    if($formulario->save())
    {
      $formulario->roles()->attach($request->roles);
    }

    return redirect()->route('formularioUsuario.modificar', $formulario)->with('success', "El formulario <b>".$formulario->nombre."</b> fue creado con éxito.");
  }

  public function modificar(FormularioUsuario $formulario)
  {
    $roles = Role::select('id','name','icono')->orderBy('name','asc')->get();
    $tipos = TipoFormularioUsuario::orderBy('nombre','asc')->get();

    $rolesSeleccionados = $formulario->roles()->select('roles.id')->pluck('roles.id')->toArray();
    $tiposUsuario = TipoUsuario::orderBy('nombre','asc')->get();
    return view('contenido.paginas.formularios-usuarios.modificar', [
      'roles' => $roles,
      'rolesSeleccionados' => $rolesSeleccionados,
      'tipos' => $tipos,
      'formulario' => $formulario,
      'tiposUsuario' => $tiposUsuario
    ]);
  }

  public function editar(FormularioUsuario $formulario, Request $request)
  {
    // Validación
    $validacion = [
      'nombre' => ['max:100', 'required'],
      'título' => ['max:100', 'required'],
      'tipoDeFormulario' => ['required']
    ];

    $request->términosCondiciones
    ? $validacion = array_merge($validacion, ['términosYCondiciones' => 'required'])
    : '';

    $request->validarEdad
    ? $validacion = array_merge($validacion, ['edadMínima' => 'numeric|required' , 'edadMaxima' => 'numeric|required'])
    : '';

    // Validacion de datos
    $request->validate($validacion);

    $formulario->nombre = $request->nombre;
    $formulario->titulo = $request->título;
    $formulario->label = $request->etiqueta;
    $formulario->descripcion = $request->descripción;
    $formulario->validar_edad = $request->validarEdad ? TRUE : FALSE;
    $formulario->edad_minima = $request->edadMínima;
    $formulario->edad_maxima = $request->edadMaxima;
    $formulario->edad_mensaje_error = $request->mensaje;
    $formulario->visible_terminos_condiciones = $request->términosCondiciones ? TRUE : FALSE;
    $formulario->url_terminos_condiciones = $request->url;
    $formulario->mensaje_terminos_condiciones_resumen = $request->términosYCondiciones;
    $formulario->mensaje_terminos_condiciones_detallado = $request->términosYCondicionesDetallado;
    $formulario->tipo_formulario_id = $request->tipoDeFormulario;
    $formulario->tipo_usuario_default_id = $request->tipoUsuarioPorDefecto;


    if($formulario->save())
    {
      $formulario->roles()->sync($request->roles);
    }

    return back()->with('success', "El formulario <b>".$formulario->nombre."</b> fue creado con éxito.");
  }

  public function seccionesCampos(FormularioUsuario $formulario)
  {
    return view('contenido.paginas.formularios-usuarios.secciones-y-campos', [
      'formulario' => $formulario
    ]);
  }
}
