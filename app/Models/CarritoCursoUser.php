<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarritoCursoUser extends Model
{
    use HasFactory;

    protected $table = 'carritos_curso_user';

    protected $fillable = [
        'user_id',
        'items',
        'total',
        'estado'
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Metodos auxiliares sugeridos
    public function vaciar()
    {
        $this->update(['items' => [], 'total' => 0]);
    }

    public function agregarCurso(Curso $curso, $precio)
    {
        $currentItems = $this->items ?? [];

        // Evitar duplicados si ya esta en el carrito
        $existe = collect($currentItems)->firstWhere('curso_id', $curso->id);
        if(!$existe) {
            $currentItems[] = [
                'curso_id' => $curso->id,
                'nombre' => $curso->nombre,
                'precio' => $precio
            ];

            $nuevoTotal = collect($currentItems)->sum('precio');

            $this->update([
                'items' => $currentItems,
                'total' => $nuevoTotal
            ]);
        }
    }
}
