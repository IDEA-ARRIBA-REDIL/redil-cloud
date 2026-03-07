<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioAdicionalConsejero extends Model
{
    use HasFactory;
    protected $table = 'horarios_adicionales_consejero';
    protected $guarded = [];


    public function consejero()
    {
        return $this->belongsTo(Consejero::class);
    }
}
