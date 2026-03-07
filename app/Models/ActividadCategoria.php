<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ActividadCategoria extends Model
{
    use HasFactory;
    protected $table = 'actividad_categorias';
    protected $guarded = [];

    public function materia()
    {
        return $this->belongsTo(MateriaPeriodo::class, 'materia_periodo_id');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class);
    }

    public function monedas(): BelongsToMany
    {
        return $this->belongsToMany(Moneda::class, 'actividad_categoria_monedas', 'actividad_categoria_id', 'moneda_id')
            ->withPivot('valor', 'novedad_id', 'created_at', 'updated_at');
    }

    // En el modelo CategoriaActividad
    public function getValoresMonedaAttribute()
    {
        return $this->monedas->mapWithKeys(function ($moneda) {
            return [
                $moneda->id => $this->getPrecioParaMoneda($moneda->id)
            ];
        })->toArray();
    }

    private function getPrecioParaMoneda($monedaId)
    {
        // Lógica para obtener el precio de la categoría en una moneda específica
        return ActividadCategoriaMoneda::where('actividad_categoria_id', $this->id)
            ->where('moneda_id', $monedaId)
            ->value('valor');
    }


    // Nueva relación para obtener los abonos
    public function abonosCategoria(): HasMany
    {
        return $this->hasMany(AbonoCategoria::class, 'actividad_categoria_id');
    }


    // Nueva relación para obtener las materias_periodo
   public function materiaPeriodo(): BelongsTo
    {
        return $this->belongsTo(MateriaPeriodo::class, 'materia_periodo_id');
    }

    // Si necesitas acceder directamente a los abonos
    public function abonos(): BelongsToMany
    {
        return $this->belongsToMany(
            Abono::class,
            'abono_categoria',
            'actividad_categoria_id',
            'abono_id'
        )->withPivot(
            'created_at',
            'updated_at',
            'valor',
            'moneda_id'
        );
    }

    //// estas relaciones se crearon cuando se modificaron que la restricciones tambien se pudieran por categoria

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'actividad_categoria_sedes', 'actividad_categoria_id', 'sede_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function rangosEdad(): BelongsToMany
    {
        return $this->belongsToMany(RangoEdad::class, 'actividad_categoria_rangos_edad', 'actividad_categoria_id', 'rango_edad_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }



    public function procesosRequisito(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'actividad_categoria_procesos_requisitos', 'actividad_categoria_id', 'paso_crecimiento_id')
        ->using(ActividadCategoriaProcesoRequisito::class)
        ->withPivot(
            'created_at',
            'updated_at',
            'estado', // Mantener por compatibilidad
            'estado_paso_crecimiento_usuario_id', // NUEVO: FK dinámico
            'indice'
        )->with('pivot.estadoPasoCrecimiento:id,nombre,color');
    }

    public function procesosCulminados(): BelongsToMany
    {
        return $this->belongsToMany(PasoCrecimiento::class, 'actividad_categoria_procesos_culminados', 'actividad_categoria_id', 'paso_crecimiento_id')
        ->using(ActividadCategoriaProcesoCulminado::class)
        ->withPivot(
            'created_at',
            'updated_at',
            'estado', // Mantener por compatibilidad
            'estado_paso_crecimiento_usuario_id', // NUEVO: FK dinámico
            'indice'
        )->with('pivot.estadoPasoCrecimiento:id,nombre,color');
    }

    // ========== RELACIONES DE TAREAS DE CONSOLIDACIÓN ==========

    public function tareasRequisito(): HasMany
    {
        return $this->hasMany(ActividadCategoriaTareaRequisito::class, 'actividad_categoria_id');
    }

    public function tareasCulminadas(): HasMany
    {
        return $this->hasMany(ActividadCategoriaTareaCulminada::class, 'actividad_categoria_id');
    }

    public function tipoUsuarios()
    {
        return $this->belongsToMany(TipoUsuario::class, 'actividad_categoria_tipos_usuarios', 'actividad_categoria_id', 'tipo_usuario_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function estadosCiviles()
    {
        return $this->belongsToMany(EstadoCivil::class, 'actividad_categoria_estados_civiles', 'actividad_categoria_id', 'estado_civil_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function tipoServicios()
    {
        return $this->belongsToMany(TipoServicioGrupo::class, 'actividad_categoria_tipos_servicios_grupos', 'actividad_categoria_id', 'tipo_servicio_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }
}
