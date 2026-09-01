<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsigniaUser extends Model
{
    use HasFactory;

    protected $table = 'insignia_user';

    protected $guarded = [];

    protected $casts = [
        'progreso_actual' => 'integer',
        'completada' => 'boolean',
        'obtenida_el' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function insignia(): BelongsTo
    {
        return $this->belongsTo(Insignia::class, 'insignia_id');
    }
}
