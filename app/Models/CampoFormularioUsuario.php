<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampoFormularioUsuario extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'campos_formulario_usuario';
    protected $guarded = [];

    public function secciones(): BelongsToMany
    {
      return $this->belongsToMany(
        CampoFormularioUsuario::class,
        'campo_seccion_formulario_usuario',
        'campo_id',
        'seccion_id'
      )->withPivot('created_at','updated_at','requerido','class','orden');
    }

    public function usuarios(): BelongsToMany
    {
      return $this->belongsToMany(User::class, 'usuario_campo_formulario_usuario', 'campo_formulario_usuario_id','user_id')
        ->withPivot('valor')
        ->withTimestamps();
    }


}
