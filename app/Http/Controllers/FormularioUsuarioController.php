<?php

namespace App\Http\Controllers;

use App\Models\FormularioUsuario;
use App\Models\Role;
use App\Models\Sede;
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
        $roles = Role::select('id', 'name', 'icono')->orderBy('name', 'asc')->get();
        $tipos = TipoFormularioUsuario::orderBy('nombre', 'asc')->get();
        $tiposUsuario = TipoUsuario::orderBy('nombre', 'asc')->get();
        $sedes = Sede::orderBy('nombre', 'asc')->get();

        return view('contenido.paginas.formularios-usuarios.nuevo', [
            'roles' => $roles,
            'tipos' => $tipos,
            'tiposUsuario' => $tiposUsuario,
            'sedes' => $sedes,
        ]);
    }

    public function crear(Request $request)
    {
        // Validación
        $validacion = [
            'nombre' => ['max:100', 'required'],
            'título' => ['max:100', 'required'],
            'etiqueta' => ['max:100', 'required'],
            'icono' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'tipoDeFormulario' => ['required'],
        ];

        $tipoFormulario = TipoFormularioUsuario::find($request->tipoDeFormulario);
        $esFormularioExterior = $tipoFormulario && $tipoFormulario->es_formulario_exterior;

        if ($request->términosCondiciones) {
            $reglasTerminos = [
                'términosYCondiciones' => 'required',
            ];
            if ($request->modalidadTerminos === 'url') {
                $reglasTerminos['url'] = 'required|url';
            } else {
                $reglasTerminos['términosYCondicionesDetallado'] = 'required';
            }
            $validacion = array_merge($validacion, $reglasTerminos);
        }

        $request->validarEdad
        ? $validacion = array_merge($validacion, ['edadMínima' => 'numeric|required', 'edadMaxima' => 'numeric|required'])
        : '';

        if (! $esFormularioExterior && $request->validarEdadOperador) {
            $validacion = array_merge($validacion, ['edadMinimaOperador' => 'numeric|required', 'edadMaximaOperador' => 'numeric|nullable']);
        }

        // Validacion de datos
        $request->validate($validacion);

        $formulario = new FormularioUsuario;
        $formulario->nombre = $request->nombre;
        $formulario->titulo = $request->título;
        $formulario->label = $request->etiqueta;
        $formulario->icono = $request->icono;
        $formulario->color = $request->color;
        $formulario->descripcion = $request->descripción;
        $formulario->validar_edad = $request->validarEdad ? true : false;
        $formulario->edad_minima = $request->edadMínima;
        $formulario->edad_maxima = $request->edadMaxima;
        $formulario->edad_mensaje_error = $request->mensaje;
        $formulario->sugerir_en_recomendaciones = $request->sugerirEnRecomendaciones ? true : false;
        $formulario->validar_edad_operador = (! $esFormularioExterior && $request->validarEdadOperador) ? true : false;
        $formulario->edad_minima_operador = (! $esFormularioExterior && $request->validarEdadOperador) ? $request->edadMinimaOperador : null;
        $formulario->edad_maxima_operador = (! $esFormularioExterior && $request->validarEdadOperador) ? $request->edadMaximaOperador : null;
        $formulario->visible_terminos_condiciones = $request->términosCondiciones ? true : false;
        $formulario->label_terminos_condiciones = $request->términosCondiciones ? $request->labelTerminosCondiciones : null;
        $formulario->mensaje_terminos_condiciones_resumen = $request->términosCondiciones ? $request->términosYCondiciones : null;
        $formulario->url_terminos_condiciones = ($request->términosCondiciones && $request->modalidadTerminos === 'url') ? $request->url : null;
        $formulario->mensaje_terminos_condiciones_detallado = ($request->términosCondiciones && $request->modalidadTerminos === 'editor') ? $request->términosYCondicionesDetallado : null;
        $formulario->tipo_formulario_id = $request->tipoDeFormulario;
        $formulario->tipo_usuario_default_id = $request->tipoUsuarioPorDefecto;
        $formulario->sede_default_id = $request->sedeDefault;

        if ($formulario->save()) {
            if (! $esFormularioExterior && $request->roles) {
                $formulario->roles()->attach($request->roles);
            }
        }

        return redirect()->route('formularioUsuario.modificar', $formulario)->with('success', 'El formulario <b>'.$formulario->nombre.'</b> fue creado con éxito.');
    }

    public function modificar(FormularioUsuario $formulario)
    {
        $roles = Role::select('id', 'name', 'icono')->orderBy('name', 'asc')->get();
        $tipos = TipoFormularioUsuario::orderBy('nombre', 'asc')->get();

        $rolesSeleccionados = $formulario->roles()->select('roles.id')->pluck('roles.id')->toArray();
        $tiposUsuario = TipoUsuario::orderBy('nombre', 'asc')->get();
        $sedes = Sede::orderBy('nombre', 'asc')->get();

        return view('contenido.paginas.formularios-usuarios.modificar', [
            'roles' => $roles,
            'rolesSeleccionados' => $rolesSeleccionados,
            'tipos' => $tipos,
            'formulario' => $formulario,
            'tiposUsuario' => $tiposUsuario,
            'sedes' => $sedes,
        ]);
    }

    public function editar(FormularioUsuario $formulario, Request $request)
    {
        // Validación
        $validacion = [
            'nombre' => ['max:100', 'required'],
            'título' => ['max:100', 'required'],
            'etiqueta' => ['max:100', 'required'],
            'icono' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'tipoDeFormulario' => ['required'],
        ];

        $tipoFormulario = TipoFormularioUsuario::find($request->tipoDeFormulario);
        $esFormularioExterior = $tipoFormulario && $tipoFormulario->es_formulario_exterior;

        if ($request->términosCondiciones) {
            $reglasTerminos = [
                'términosYCondiciones' => 'required',
            ];
            if ($request->modalidadTerminos === 'url') {
                $reglasTerminos['url'] = 'required|url';
            } else {
                $reglasTerminos['términosYCondicionesDetallado'] = 'required';
            }
            $validacion = array_merge($validacion, $reglasTerminos);
        }

        $request->validarEdad
        ? $validacion = array_merge($validacion, ['edadMínima' => 'numeric|required', 'edadMaxima' => 'numeric|required'])
        : '';

        if (! $esFormularioExterior && $request->validarEdadOperador) {
            $validacion = array_merge($validacion, ['edadMinimaOperador' => 'numeric|required', 'edadMaximaOperador' => 'numeric|nullable']);
        }

        // Validacion de datos
        $request->validate($validacion);

        $formulario->nombre = $request->nombre;
        $formulario->titulo = $request->título;
        $formulario->label = $request->etiqueta;
        $formulario->icono = $request->icono;
        $formulario->color = $request->color;
        $formulario->descripcion = $request->descripción;
        $formulario->validar_edad = $request->validarEdad ? true : false;
        $formulario->edad_minima = $request->edadMínima;
        $formulario->edad_maxima = $request->edadMaxima;
        $formulario->edad_mensaje_error = $request->mensaje;
        $formulario->sugerir_en_recomendaciones = $request->sugerirEnRecomendaciones ? true : false;
        $formulario->validar_edad_operador = (! $esFormularioExterior && $request->validarEdadOperador) ? true : false;
        $formulario->edad_minima_operador = (! $esFormularioExterior && $request->validarEdadOperador) ? $request->edadMinimaOperador : null;
        $formulario->edad_maxima_operador = (! $esFormularioExterior && $request->validarEdadOperador) ? $request->edadMaximaOperador : null;
        $formulario->visible_terminos_condiciones = $request->términosCondiciones ? true : false;
        $formulario->label_terminos_condiciones = $request->términosCondiciones ? $request->labelTerminosCondiciones : null;
        $formulario->mensaje_terminos_condiciones_resumen = $request->términosCondiciones ? $request->términosYCondiciones : null;
        $formulario->url_terminos_condiciones = ($request->términosCondiciones && $request->modalidadTerminos === 'url') ? $request->url : null;
        $formulario->mensaje_terminos_condiciones_detallado = ($request->términosCondiciones && $request->modalidadTerminos === 'editor') ? $request->términosYCondicionesDetallado : null;
        $formulario->tipo_formulario_id = $request->tipoDeFormulario;
        $formulario->tipo_usuario_default_id = $request->tipoUsuarioPorDefecto;
        $formulario->sede_default_id = $request->sedeDefault;

        if ($formulario->save()) {
            if ($esFormularioExterior) {
                $formulario->roles()->sync([]);
            } else {
                $formulario->roles()->sync($request->roles);
            }
        }

        return back()->with('success', 'El formulario <b>'.$formulario->nombre.'</b> fue creado con éxito.');
    }

    public function seccionesCampos(FormularioUsuario $formulario)
    {
        return view('contenido.paginas.formularios-usuarios.secciones-y-campos', [
            'formulario' => $formulario,
        ]);
    }
}
