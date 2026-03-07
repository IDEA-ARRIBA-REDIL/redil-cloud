<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;

class CustomVerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 1. Encontrar al usuario por el ID que viene en la URL.
        // Usamos findOrFail para que muestre un error 404 si el ID no es válido.
        $user = User::findOrFail($request->route('id'));

        // 2. Comprobar que el hash del enlace coincide con el email del usuario.
        // Esto previene que alguien pueda verificar el email de otro usuario solo cambiando el ID.
        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            // Si no coincide, es una acción no autorizada.
            abort(403);
        }

        // 3. Si el usuario ya tiene el email verificado, lo redirigimos.
        if ($user->hasVerifiedEmail()) {
            return redirect(config('app.frontend_url') . '/dashboard?verified=1');
        }

        // 4. Si pasa las comprobaciones, marcamos el email como verificado.
        if ($user->markEmailAsVerified()) {
            // Y disparamos el evento 'Verified'.
            event(new Verified($user));
        }

        // 5. (Opcional pero recomendado) Autenticamos al usuario automáticamente.
        Auth::login($user);

        // 6. Redirigimos al usuario a su panel de control (dashboard).
        return redirect(config('app.frontend_url') . '/dashboard?verified=1');
    }
}
