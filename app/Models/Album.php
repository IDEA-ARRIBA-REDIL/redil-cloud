<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    use HasFactory;
    protected $table = 'albumes';
    protected $guarded = [];

    public function canciones(): HasMany
    {
      return $this->hasMany(Cancion::class);
    }
}
