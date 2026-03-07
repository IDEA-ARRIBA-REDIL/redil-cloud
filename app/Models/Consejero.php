<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consejero extends Model
{
    use HasFactory;
    protected $table = 'consejeros';
    protected $guarded = [];

    public function usuario():BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Las sedes donde este consejero está habilitado.
     */
    public function sedes():BelongsToMany
    {
        // Modelo, tabla pivote
        return $this->belongsToMany(Sede::class, 'consejero_sede', 'consejero_id', 'sede_id');
    }

    /**
     * Los tipos de consejería que ofrece este consejero.
     */
    public function tipoConsejerias():BelongsToMany
    {
        // Modelo, tabla pivote
        return $this->belongsToMany(TipoConsejeria::class, 'consejero_tipo_consejeria', 'consejero_id', 'tipo_consejeria_id');
    }

    public function horariosHabituales():HasMany
    {
        return $this->hasMany(HorarioHabitual::class, 'consejero_id');
    }

    public function horariosAdicionales():HasMany
    {
        return $this->hasMany(HorarioAdicionalConsejero::class, 'consejero_id');
    }

    public function horariosBloqueados():HasMany
    {
        return $this->hasMany(HorarioBloqueadoConsejero::class, 'consejero_id');
    }
}
