<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NotificacionReactivacionCuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ReactivacionCuentaController extends Controller
{
    /**
     * Muestra el formulario para solicitar la reactivación de la cuenta.
     * Si el usuario llegó desde el error del Login, el email se carga por defecto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function mostrarFormulario(Request $request)
    {
        return view('contenido.authentications.reactivar-solicitud', [
            'email' => $request->query('email')
        ]);
    }

    /**
     * Recibe la solicitud con el correo del usuario, valida que sea un usuario
     * dado de baja y le envía un correo electrónico con un enlace firmado temporal
     * para reactivarse.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enviarEnlaceReactivacion(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        // Buscamos expresamente usuarios que estén en la papelera (SoftDeletes)
        $usuario = User::onlyTrashed()->where('email', $request->email)->first();

        if (! $usuario) {
            // Si el correo no existe o el usuario no está dado de baja, no le revelamos la condición explícitamente
            // para evitar enumeración y escaneo de correos; solo damos respuesta genérica.
            return back()->with('error', 'No se ha encontrado ninguna cuenta dada de baja asociada a este correo.');
        }

        // Generamos la URL firmada temporal (expira en 30 minutos).
        $urlFirmada = URL::temporarySignedRoute(
            'auth.reactivar.procesar', // Nombre de la ruta que procesa el click
            now()->addMinutes(30),
            ['id' => $usuario->id]
        );

        // Enviamos la notificación al usuario
        $usuario->notify(new NotificacionReactivacionCuenta($urlFirmada));

        return back()->with('success', 'Se ha enviado un enlace a tu correo electrónico para reactivar la cuenta.');
    }

    /**
     * Procesa la URL firmada cuando el usuario hace clic desde el correo.
     * Este método restaura la cuenta (quita el deleted_at) y finaliza el proceso.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restaurarCuenta(Request $request, $id)
    {
        // Encontramos únicamente al usuario dado de baja.
        $usuario = User::onlyTrashed()->findOrFail($id);

        // Restauramos al usuario. La función restore() es nativa de SoftDeletes.
        $usuario->restore();

        // El usuario ya está restablecido, puede loguearse de nuevo.
        return redirect()->route('login')->with('success', 'Tu cuenta ha sido reactivada exitosamente. Por favor, inicia sesión de nuevo.');
    }
}
