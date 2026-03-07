<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\BelongsTo; // Si añades escuela_id

class MotivoInasistencia extends Model
{
     use HasFactory;
    protected $table = 'motivos_inasistencias_reporte_escuelas';
    protected $fillable = ['nombre', 'descripcion', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function detallesAsistenciaConEsteMotivo(): HasMany // Nombre de relación más claro
    {
        return $this->hasMany(ReporteAsistenciaAlumno::class, 'motivo_inasistencia_id');
    }
}