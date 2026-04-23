<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNotificacion extends Model
{
    use HasFactory;

    protected $table = 'tipos_notificaciones';

    protected $fillable = [
        'slug',
        'modulo',
        'titulo',
        'descripcion',
        'alcance',
        'dias_vigencia',
        'sedes_ids',
        'tipos_usuario_ids',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'alcance'          => 'array',
            'sedes_ids'        => 'array',
            'tipos_usuario_ids' => 'array',
            'activo'           => 'boolean',
            'dias_vigencia'    => 'integer',
        ];
    }

    /** Constantes de alcance */
    public const ALCANCE_GLOBAL = 'global';

    public const ALCANCE_INDIVIDUAL = 'individual';

    public const ALCANCE_ESCALA_MINISTERIAL = 'escala_ministerial';

    public const ALCANCE_MINISTERIO_DIRECTO = 'ministerio_directo';

    /** Listado canónico de alcances disponibles con etiquetas. */
    public static function alcancesDisponibles(): array
    {
        return [
            self::ALCANCE_INDIVIDUAL => 'Individual (solo el usuario)',
            self::ALCANCE_MINISTERIO_DIRECTO => 'Ministerio Directo (solo líder directo)',
            self::ALCANCE_ESCALA_MINISTERIAL => 'Escala Ministerial (todos sus líderes)',
            self::ALCANCE_GLOBAL => 'Global (todos los usuarios)',
        ];
    }
}
