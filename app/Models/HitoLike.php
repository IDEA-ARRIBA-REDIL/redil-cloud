<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HitoLike extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'hito_likes';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function hito(): BelongsTo
    {
        return $this->belongsTo(Hito::class, 'hito_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
