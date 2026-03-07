<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aula extends Model
{
  
    use HasFactory, SoftDeletes;
     protected $table = 'aulas';

    /**
     * Un aula tiene muchos horarios base.
     */
    public function horariosBase()
    {
        return $this->hasMany(HorarioBase::class, 'aula_id');
    }

    public function tipo()
    {
        return $this->belongsTo(TipoAula::class, 'tipo_aula_id');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
