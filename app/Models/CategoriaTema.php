<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaTema extends Model
{
    use HasFactory;
    protected $table = 'categorias_tema';
    protected $guarded = [];

    public function temas(): BelongsToMany
    {
      return $this->belongsToMany(Tema::class, 'temas_categorias', 'categoria_tema_id', 'tema_id' )->withPivot(
        'created_at',
        'updated_at'
      );;
    }

}
