<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SeccionFormularioUsuario extends Model
{
    use HasFactory;
    protected $table = 'secciones_formulario_usuario';
    protected $guarded = [];

    public function formulario(): BelongsTo
    {
      return $this->belongsTo(FormularioUsuario::class);
    }

    public function campos(): BelongsToMany
    {
      return $this->belongsToMany(
        CampoFormularioUsuario::class,
        'campo_seccion_formulario_usuario',
        'seccion_id',
        'campo_id'
      )->withPivot('created_at','updated_at', 'requerido', 'class', 'orden', 'informacion_de_apoyo');
    }

}
