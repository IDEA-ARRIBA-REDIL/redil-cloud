<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BloqueDashboardConsolidacion extends Model
{
    use HasFactory;

    protected $table = 'bloques_dashboard_consolidacion';

    protected $guarded = [];

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'bloque_dashboard_consolidacion_sede', 'bloque_id', 'sede_id');
    }
}
