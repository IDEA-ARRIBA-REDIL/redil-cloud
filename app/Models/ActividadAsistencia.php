<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ActividadAsistencia extends Model {
    protected $table = 'actividad_asistencias';
    protected $fillable = ['actividad_id', 'user_id'];

    public function actividad() {
        return $this->belongsTo(Actividad::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}