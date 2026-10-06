<?php

namespace App\Models;

use App\Enums\EstadoInformeCola;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformeEnCola extends Model
{
    use HasFactory;

    protected $table = 'informes_en_cola';

    protected $fillable = [
        'informe_personalizado_id',
        'grupo_id',
        'agrupar_por_tipo_grupo_id',
        'year',
        'periodo',
        'semana',
        'email',
        'usuario_creacion_id',
        'nombre_archivo',
        'estado',
        'error_message',
        'tiempo_ejecucion_segundos',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoInformeCola::class,
            'year' => 'integer',
            'tiempo_ejecucion_segundos' => 'integer',
        ];
    }

    public function informePersonalizado(): BelongsTo
    {
        return $this->belongsTo(InformePersonalizado::class, 'informe_personalizado_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function tipoGrupoAgrupacion(): BelongsTo
    {
        return $this->belongsTo(TipoGrupo::class, 'agrupar_por_tipo_grupo_id');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creacion_id');
    }

    public function isCompleted(): bool
    {
        return $this->estado === EstadoInformeCola::Completado;
    }

    public function isPending(): bool
    {
        return $this->estado === EstadoInformeCola::Pendiente;
    }

    public function isProcessing(): bool
    {
        return $this->estado === EstadoInformeCola::Procesando;
    }

    public function isFailed(): bool
    {
        return $this->estado === EstadoInformeCola::Fallido;
    }
}
