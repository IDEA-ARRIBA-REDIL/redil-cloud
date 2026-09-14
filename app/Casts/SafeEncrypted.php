<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SafeEncrypted implements CastsAttributes
{
    /**
     * Cast the given value upon retrieval (decrypt with fallback for legacy plain text).
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Retorna el texto plano en caso de registros existentes no encriptados
            return $value;
        }
    }

    /**
     * Prepare the given value for storage (encrypt before saving to database).
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Si ya estuviera cifrado (ej. reasignación sin cambios), verificar para evitar doble cifrado
        try {
            Crypt::decryptString($value);

            return $value;
        } catch (DecryptException $e) {
            return Crypt::encryptString($value);
        }
    }
}
