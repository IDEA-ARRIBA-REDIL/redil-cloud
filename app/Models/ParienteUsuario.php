<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParienteUsuario extends Model
{
    use HasFactory;
    protected $table = 'parientes_usuarios';
    protected $guarded = [];

    public function usuario(): BelongsTo
    {
      return $this->belongsTo(User::class, 'user_id');
    }

    public function pariente(): BelongsTo
    {
      return $this->belongsTo(User::class, 'pariente_user_id');
    }
  

}
