<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SectorEconomico extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sectores_economicos';

    protected $guarded = [];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
