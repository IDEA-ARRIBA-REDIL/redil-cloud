<?php

namespace App\Livewire;

use App\Models\TipoUsuario;
use App\Models\User;
use App\Models\Configuracion;
use Livewire\Component;
use Carbon\Carbon;

class ProximosCumpleanos extends Component
{
  public function render()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $personas = collect();

    if ($rolActivo) {

      // 2. Obtenemos los tipos de usuario (tal como lo tenías)
      $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')
        ->where('visible', true)
        ->where('tipo_pastor_principal', '!=', true)
        ->get();

      // 3. Obtenemos la lista de $personas según el permiso
      if (
        $rolActivo->hasPermissionTo('personas.lista_asistentes_todos') ||
        $rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')
      ) {
        // si es un usuario diferente al super administrador
        if ($rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
          $personas = auth()->user()->discipulos('todos');
        }

        /// si es el super administrador los trae todos
        if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos')) {
          $personas = User::withTrashed()
            ->whereNotNull('email_verified_at')
            ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
            ->whereIn('tipo_usuario_id', $tiposUsuarios->pluck('id')->toArray())
            ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
            ->get()
            ->unique('id');
        }
      }
    }

    $hoy = now();

    $proximosCumpleanos = $personas
      ->whereNotNull('fecha_nacimiento')
      ->sortBy(function ($usuario) use ($hoy) {

        // Asegúrate de que 'fecha_nacimiento' esté en los $casts del modelo User
        $cumpleEsteAnio = $usuario->fecha_nacimiento->copy()->setYear($hoy->year);

        if ($cumpleEsteAnio->isBefore($hoy->startOfDay())) {
          return $cumpleEsteAnio->addYear();
        }
        return $cumpleEsteAnio;
      })
      ->take(20);

    // --- 5. Pasamos los datos a la vista del componente ---
    return view('livewire.proximos-cumpleanos', [
      'proximosCumpleanos' => $proximosCumpleanos,
      'configuracion' => Configuracion::find(1)
    ]);
  }
}
