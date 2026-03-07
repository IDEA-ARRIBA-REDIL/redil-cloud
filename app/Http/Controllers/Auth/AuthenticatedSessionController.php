<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\FormularioUsuario;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Redirect;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $formularios = FormularioUsuario::where('tipo_formulario_id', '=', 3)
            ->select('id', 'nombre', 'label', 'tipo_formulario_id')->get();

        $emailDefault =  Session::get('emailDefault') ?  Session::get('emailDefault') : '';

        // Limpiar la sesión
        Session::forget('emailDefault');


        if ($request->has('redirect')) {
          session(['url.intended' => $request->input('redirect')]);
        }


        return view('contenido.authentications.login', [
            'formularios' => $formularios,
            'emailDefault' => $emailDefault
        ]);
    }

    /**
     * Handle an incoming authentication request.
     * PASO 3 COMO EL TIENE ESE REQUEST LA SESSION YA CARGADA PERO ADEMAS TIENE LA RUTA EL PREGUNTA QUE SI INTENDED TIENE CUALQUIER COSA QUE LO REDIRIJA
     * ALLA Y SINO HAY RUTA DE INTENDED EL ENTIENDE QUE DEBE REDIRIGIRSE AL HOME
     */


    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Obtener la URL guardada
       // $redirectUrl = Session::get('url.intended');

        // Limpiar la sesión
        /*$crearHijo = Session::get('crearHijo');
        if($crearHijo == 'si')
        {
          $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
          $formulario = $rolActivo->formularios()->where('tipo_formulario_id', '=', 4)->orderBy('edad_minima','asc')->first();

          if($formulario)
          return Redirect::to("/usuario/$formulario->id/nuevo");
        }*/

        // Obtenemos el usuario autenticado
        $user = Auth::user();

        // Verificamos si tiene hijos y si NUNCA se le ha mostrado el modal
        if ($user->tiene_hijos && $user->mostrar_modal_agregar_hijos) {
          $request->session()->flash('show_children_modal', true);
        }

        //Session::forget('url.intended');
        // Session::forget('crearHijo');

        // Redireccionar a la URL guardada o a la ruta por defecto AQUI HACE LO DE ARRIBA COMENTARIADO
        return redirect()->intended(RouteServiceProvider::HOME);
    }
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
