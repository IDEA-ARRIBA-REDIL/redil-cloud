<?php

namespace App\Services;

use App\Mail\CodigoAdminMail;
use App\Models\UserAdminRedil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SeguridadAdminService
{
    public function exigir(): UserAdminRedil
    {
        $admin = Auth::guard('admin')->user();
        $evidencia = session('admin_mfa', []);
        abort_unless(! tenant() && $admin && ! $admin->is_suspended
            && ($evidencia['id'] ?? null) === $admin->id
            && ($evidencia['expires'] ?? 0) > now()->timestamp
            && hash_equals($evidencia['password'] ?? '', hash('sha256', $admin->password)), 403);

        return $admin;
    }

    public function iniciar(string $email, string $password): void
    {
        abort_if((bool) tenant(), 403);
        $email = Str::lower(trim($email));
        $this->limitar('login:ip:'.request()->ip(), 20, 900);
        $this->limitar('login:email:'.$email, 5, 900);
        $admin = UserAdminRedil::query()->where('email', $email)->first();
        $valido = $admin && Hash::check($password, $admin->password);
        if (! $valido || $admin->is_suspended) {
            throw ValidationException::withMessages(['email' => 'No fue posible iniciar sesión con estas credenciales.']);
        }
        $this->limitar('mfa:envio:'.$admin->id, 3, 3600);
        $this->cancelar();
        $codigo = (string) random_int(100000, 999999);
        $challenge = Str::random(64);
        Cache::put('central:mfa:'.$challenge, [
            'id' => $admin->id, 'hash' => Hash::make($codigo), 'password' => hash('sha256', $admin->password),
        ], now()->addMinutes(config('admin_global.mfa_minutes')));
        session()->regenerate();
        session()->put('admin_challenge', $challenge);
        Mail::to($admin->email)->queue(new CodigoAdminMail($codigo));
    }

    public function verificar(string $codigo): void
    {
        abort_if((bool) tenant(), 403);
        $this->limitar('mfa:ip:'.request()->ip(), 20, 900);
        $challenge = session('admin_challenge', '');
        $this->limitar('mfa:intento:'.$challenge, 5, 900);
        $key = 'central:mfa:'.$challenge;
        $lock = Cache::lock($key.':lock', 10);
        if (! $challenge || ! $lock->get()) {
            throw ValidationException::withMessages(['codigo' => 'Código inválido o vencido. Inicia de nuevo.']);
        }
        try {
            $datos = Cache::get($key);
            $admin = $datos ? UserAdminRedil::query()->find($datos['id']) : null;
            if (! $admin || $admin->is_suspended || ! Hash::check($codigo, $datos['hash'])
                || ! hash_equals($datos['password'], hash('sha256', $admin->password))) {
                throw ValidationException::withMessages(['codigo' => 'Código inválido o vencido.']);
            }
            Cache::forget($key);
            session()->forget('admin_challenge');
            Auth::guard('admin')->login($admin, false);
            session()->regenerate();
            session()->put('admin_mfa', [
                'id' => $admin->id, 'password' => $datos['password'],
                'expires' => now()->addHours(config('admin_global.session_hours'))->timestamp,
            ]);
        } finally {
            $lock->release();
        }
    }

    public function cancelar(): void
    {
        if ($challenge = session()->pull('admin_challenge')) {
            Cache::forget('central:mfa:'.$challenge);
        }
    }

    public function limitar(string $identificador, int $maximo, int $segundos): void
    {
        $key = 'central:security:'.hash('sha256', $identificador);
        if (RateLimiter::tooManyAttempts($key, $maximo)) {
            throw ValidationException::withMessages(['email' => 'Demasiados intentos. Espera antes de volver a intentar.', 'codigo' => 'Demasiados intentos. Espera antes de volver a intentar.']);
        }
        RateLimiter::hit($key, $segundos);
    }
}
