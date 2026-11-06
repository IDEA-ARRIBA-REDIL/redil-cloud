<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BibliaVersiculo extends Model
{
    protected $connection = 'central';

    protected $table = 'biblia_versiculos';

    protected $fillable = [
        'version_id',
        'libro_id',
        'capitulo',
        'versiculo',
        'texto',
        'encabezado',
    ];

    protected function casts(): array
    {
        return [
            'capitulo' => 'integer',
            'versiculo' => 'integer',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(BibliaVersion::class, 'version_id');
    }

    public function libro(): BelongsTo
    {
        return $this->belongsTo(BibliaLibro::class, 'libro_id');
    }
}
