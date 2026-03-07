<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraTipoUsuario extends Model
{
    use HasFactory;

    protected $table = 'bitacora_tipos_usuarios';

    protected $fillable = [
        'user_id',
        'tipo_usuario_id_anterior',
        'tipo_usuario_id_nuevo',
        'autor_id'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoUsuarioAnterior(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_id_anterior');
    }

    public function tipoUsuarioNuevo(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_id_nuevo');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
