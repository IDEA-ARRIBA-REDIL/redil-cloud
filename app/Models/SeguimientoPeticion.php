<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoPeticion extends Model
{
    use HasFactory;
    protected $table = 'seguimientos_peticion';
    protected $guarded = [];

    public function peticion(): BelongsTo
    {
      return $this->belongsTo(Peticio::class);
    }

    public function usuarioCreacion(): BelongsTo
    {
      return $this->belongsTo(User::class, "usuario_id");
    }
}
