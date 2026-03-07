<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Iglesia extends Model
{
  use HasFactory;
  protected $table = 'iglesias';
  protected $guarded = [];

  public function pastoresEncargados(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'pastores_principales', 'iglesia_id', 'user_id')->withTimestamps();
  }

  public function pais(): BelongsTo
  {
    return $this->belongsTo(Pais::class);
  }

  public function municipio(): BelongsTo
  {
    return $this->belongsTo(Municipio::class);
  }
}
