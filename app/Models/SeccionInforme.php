<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionInforme extends Model
{
    use HasFactory;

    protected $table = 'secciones_informes';

    protected $guarded = [];

    public function informe(): BelongsTo
    {
        return $this->belongsTo(Informe::class, 'informe_id');
    }

    public function informePersonalizado(): BelongsTo
    {
        return $this->informe();
    }

    public function subsecciones(): HasMany
    {
        return $this->hasMany(SubseccionInforme::class, 'seccion_informe_id')->orderBy('orden', 'asc');
    }
}
