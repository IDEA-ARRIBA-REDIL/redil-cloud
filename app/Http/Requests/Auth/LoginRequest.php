<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Sanitiza y normaliza el email a minúsculas y sin espacios iniciales/finales.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim((string) $this->input('email'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Intenta autenticar las credenciales de la solicitud.
     * Aquí verificamos si el usuario fue dado de baja (SoftDeleted) antes de proceder con el intento de login normal.
     * Si está de baja y la contraseña es correcta, detenemos el login y le ofrecemos reactivar su cuenta.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = Str::lower(trim((string) $this->input('email')));

        // 1. Verificar si el usuario está dado de baja (eliminado lógicamente usando trashed())
        // Buscamos con LOWER(email) para compatibilidad total con bases de datos sensibles a mayúsculas (como PostgreSQL)
        $usuario = User::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        // Si el usuario existe, está dado de baja (trashed) y la contraseña ingresada es la correcta
        if ($usuario && $usuario->trashed() && Hash::check($this->input('password'), $usuario->password)) {
            // Limpiamos los intentos fallidos porque las credenciales sí son reales, solo el usuario está inactivo.
            RateLimiter::clear($this->throttleKey());

            // Enviamos un mensaje a la sesión tipo 'danger' que sí permite renderizar enlaces HTML gracias a {!! !!} en status-msn.blade.php
            session()->flash('danger', 'Esta cuenta ha sido dada de baja. Para reactivarla haz clic <a href="'.route('auth.reactivar', ['email' => $usuario->email]).'"><b>aquí</b></a>.');

            // Lanzamos un error genérico en el input para detener el flujo de validación.
            throw ValidationException::withMessages([
                'email' => 'Cuenta dada de baja.',
            ]);
        }

        // 2. Intento de inicio de sesión habitual
        // Usamos el email tal como está registrado en la base de datos si el usuario fue localizado, o el email saneado
        $credentials = [
            'email' => $usuario ? $usuario->email : $email,
            'password' => $this->input('password'),
        ];

        // Auth::attempt() ignora por defecto a los usuarios que están dados de baja, por lo que nunca iniciará sesión acá.
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower(trim((string) $this->input('email'))).'|'.$this->ip());
    }
}
