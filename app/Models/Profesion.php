<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profesion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profesiones';

    protected $guarded = [];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'profesion_id');
    }
}
