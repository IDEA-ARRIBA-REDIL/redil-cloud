<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanLectorCategoria extends Model
{
    use HasFactory;

    protected $table = 'plan_lector_categorias';

    protected $fillable = [
        'nombre',
        'slug',
    ];

    public function planes(): BelongsToMany
    {
        return $this->belongsToMany(PlanLector::class, 'categoria_plan_lector');
    }
}
