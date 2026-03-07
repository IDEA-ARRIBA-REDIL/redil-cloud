<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActividadEncargado extends Model
{
    use HasFactory;
    protected $table = 'actividad_encargados_cargo';
    protected $guarded = [];



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tipoCargo(): BelongsTo
    {
        return $this->belongsTo(TipoCargoActividad::class, 'tipo_cargo_id');
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class);
    }
}
