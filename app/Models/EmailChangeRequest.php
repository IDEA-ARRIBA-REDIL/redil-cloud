<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailChangeRequest extends Model
{
    use HasFactory;

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'user_id',
        'correo_actual',
        'correo_nuevo',
        'codigo',
        'finalizado',
    ];

    /**
     * Relación con el usuario que realiza la solicitud.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
