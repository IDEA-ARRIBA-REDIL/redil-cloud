<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagGeneral extends Model
{
     use HasFactory;
    protected $table = 'tags_generales';
    protected $guarded = [];
}
