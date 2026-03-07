<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbonoCategoria extends Model
{

    use HasFactory;
    protected $table = 'abono_categoria';
    protected $guarded = [];


    public function abono()
    {
        return $this->belongsTo(Abono::class);
    }

    public function categoria()
    {
        return $this->belongsTo(ActividadCategoria::class, 'actividad_categoria_id');
    }

    public function moneda()
    {
        return $this->belongsTo(Moneda::class);
    }
}
