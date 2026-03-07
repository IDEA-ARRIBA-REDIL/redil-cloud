<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadDestinatario extends Model
{
    use HasFactory;
    protected $table = 'actividad_destinatarios';
    protected $guarded = [];
}
