<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Intercesor extends Model
{
    use HasFactory;

    protected $table = 'intercesores';

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'solo_peticiones_asignadas' => 'boolean',
            'ver_peticiones_de_invitados' => 'boolean',
        ];
    }

    /**
     * Relación con el usuario.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con las sedes asignadas.
     */
    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'intercesor_sede', 'intercesor_id', 'sede_id');
    }

    /**
     * Relación con los tipos de peticiones que atiende.
     */
    public function tipoPeticiones(): BelongsToMany
    {
        return $this->belongsToMany(TipoPeticion::class, 'intercesor_tipo_peticion', 'intercesor_id', 'tipo_peticion_id');
    }
}
