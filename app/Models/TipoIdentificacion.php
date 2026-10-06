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

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'formulario_donacion' => 'boolean',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'tipo_identificacion_id');
    }
}
