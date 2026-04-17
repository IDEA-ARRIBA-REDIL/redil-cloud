<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanLectorTipoContenido extends Model
{
    use HasFactory;

    protected $table = 'plan_lector_tipo_contenidos';

    protected $fillable = [
        'nombre',
        'slug',
        'es_html',
        'es_json',
        'es_link',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'es_html' => 'boolean',
            'es_json' => 'boolean',
            'es_link' => 'boolean',
        ];
    }
}
 