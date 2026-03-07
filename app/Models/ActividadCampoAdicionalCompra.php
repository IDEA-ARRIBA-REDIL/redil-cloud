<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActividadCampoAdicionalCompra extends Model
{
    use HasFactory;
    protected $table = 'actividad_campos_adicionales_compra';
    protected $guarded = [];

    public function campoAdicional()
    {
        return $this->belongsTo(CamposAdicionalesActividad::class, 'campo_adicional_id');
    }
}
