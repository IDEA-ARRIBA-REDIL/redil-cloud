<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSubseccionInforme extends Model
{
    use HasFactory;

    protected $table = 'items_subsecciones_informes';

    protected $fillable = [
        'subseccion_informe_id',
        'nombre',
        'orden',
        'visualizar_por_mes',
        'visualizar_por_semanas',
        'fecha_creacion',
        'paso_crecimiento_id',
        'estado_paso_crecimiento',
        'filtrar_fecha_paso_crecimiento',
        'parametro_de_comparacion',
        'paso_crecimiento_id_2',
        'no_existe_paso_crecimiento_id_2',
        'estado_paso_crecimiento_2',
        'filtrar_fecha_paso_crecimiento_2',
        'cantidad_dias_dilacion',
        'grupo_de_personas',
        'filtrar_tipo_vinculacion',
        'filtrar_estado_civil',
        'tipo_baja_alta_id',
        'estado_reporte_dado_baja',
        'filtrar_fecha_reporte_baja_alta',
        'estado_matricula',
        'filtro_fecha_matricula',
        'con_operacion',
        'operacion',
        'item_a',
        'item_b',
        'totalizar_items',
    ];

    protected function casts(): array
    {
        return [
            'visualizar_por_mes' => 'boolean',
            'visualizar_por_semanas' => 'boolean',
            'fecha_creacion' => 'boolean',
            'filtrar_fecha_paso_crecimiento' => 'boolean',
            'no_existe_paso_crecimiento_id_2' => 'boolean',
            'filtrar_fecha_paso_crecimiento_2' => 'boolean',
            'filtrar_fecha_reporte_baja_alta' => 'boolean',
            'estado_reporte_dado_baja' => 'boolean',
            'filtro_fecha_matricula' => 'boolean',
            'con_operacion' => 'boolean',
            'grupo_de_personas' => 'integer',
            'cantidad_dias_dilacion' => 'integer',
            'operacion' => 'integer',
        ];
    }

    public function subseccion(): BelongsTo
    {
        return $this->belongsTo(SubseccionInforme::class, 'subseccion_informe_id');
    }

    public function pasoCrecimiento(): BelongsTo
    {
        return $this->belongsTo(PasoCrecimiento::class, 'paso_crecimiento_id');
    }

    public function pasoCrecimiento2(): BelongsTo
    {
        return $this->belongsTo(PasoCrecimiento::class, 'paso_crecimiento_id_2');
    }

    public function tipoBajaAlta(): BelongsTo
    {
        return $this->belongsTo(TipoBajaAlta::class, 'tipo_baja_alta_id');
    }

    public function itemA(): BelongsTo
    {
        return $this->belongsTo(self::class, 'item_a');
    }

    public function itemB(): BelongsTo
    {
        return $this->belongsTo(self::class, 'item_b');
    }
}
