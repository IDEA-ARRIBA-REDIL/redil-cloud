<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabitoRuedaDeLaVida extends Model
{
    use HasFactory;
    protected $table = 'habitos_rueda_de_la_vida';
    protected $guarded = [];
}
