<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoIdentificacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_identificaciones';

    public const CARACTERES_SOLO_NUMEROS = 'solo_numeros';

    public const CARACTERES_ALFANUMERICO = 'alfanumerico';

    public const CARACTERES_ALFANUMERICO_SIMBOLOS = 'alfanumerico_simbolos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'formulario_donacion' => 'boolean',
            'forzar_mayusculas' => 'boolean',
        ];
    }

    /**
     * Retorna la etiqueta descriptiva del tipo de caracteres permitidos.
     */
    public function getFormatoEtiquetaAttribute(): string
    {
        return match ($this->tipo_caracteres) {
            self::CARACTERES_ALFANUMERICO => 'Alfanumérico',
            self::CARACTERES_ALFANUMERICO_SIMBOLOS => 'Letras, números y símbolos',
            default => 'Solo números',
        };
    }

    /**
     * Retorna la expresión regular de validación backend según el tipo configurado.
     */
    public function getRegexValidacionAttribute(): ?string
    {
        return match ($this->tipo_caracteres) {
            self::CARACTERES_ALFANUMERICO => 'regex:/^[a-zA-Z0-9]+$/',
            self::CARACTERES_ALFANUMERICO_SIMBOLOS => null,
            default => 'regex:/^[0-9]+$/',
        };
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'tipo_identificacion_id');
    }
}
