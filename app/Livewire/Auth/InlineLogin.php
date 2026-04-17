<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class InlineLogin extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    /**
     * Reglas de validación.
     */
    protected array $rules = [
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ];

    /**
     * Procesa el inicio de sesión.
     */
    public function login()
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        // 1. Verificar si el usuario está dado de baja (SoftDeleted)
        // Replicamos la lógica de LoginRequest para mantener la seguridad y el flujo de reactivación.
        $usuario = User::withTrashed()->where('email', $this->email)->first();

        if ($usuario && $usuario->trashed() && Hash::check($this->password, $usuario->password)) {
            RateLimiter::clear($this->throttleKey());

            // Enviamos el mensaje de peligro con el enlace de reactivación
            session()->flash('danger', 'Esta cuenta ha sido dada de baja. Para reactivarla haz clic <a href="' . route('auth.reactivar', ['email' => $usuario->email]) . '"><b>aquí</b></a>.');
            
            return;
        }

        // 2. Intento de inicio de sesión habitual
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            $this->addError('email', trans('auth.failed'));
            return;
        }

        // Limpiar limitador al tener éxito
        RateLimiter::clear($this->throttleKey());

        // Regenerar sesión y token (Higiene de Seguridad)
        session()->regenerate();

        // Redirigir a la misma página para actualizar el estado del campus/previsualización
        return redirect()->to(request()->header('Referer', route('dashboard')));
    }

    /**
     * Asegura que la solicitud no esté siendo limitada.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Llave para el limitador de tasa.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.inline-login');
    }
}
