<?php

namespace App\Livewire;

use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProximosCumpleanos extends Component
{
    public function render()
    {
        $usuario = auth()->user();
        $rolActivo = $usuario?->roles()->wherePivot('activo', true)->first();
        $proximosCumpleanos = collect();

        if ($usuario && $rolActivo) {
            $tienePermisoCumpleanos = false;
            try {
                $tienePermisoCumpleanos = $rolActivo->hasPermissionTo('dashboard.ver_cumpleanos');
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist) {
                $tienePermisoCumpleanos = false;
            }

            $tienePermisoTodos = $rolActivo->hasPermissionTo('personas.lista_asistentes_todos');
            $tienePermisoMinisterio = $rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio');

            if ($tienePermisoCumpleanos || $tienePermisoTodos || $tienePermisoMinisterio) {
                $hoy = now();
                $mesDiaHoy = ($hoy->month * 100) + $hoy->day;
                $driver = DB::connection()->getDriverName();

                $query = User::query()
                    ->select([
                        'users.id',
                        'users.primer_nombre',
                        'users.segundo_nombre',
                        'users.primer_apellido',
                        'users.fecha_nacimiento',
                        'users.foto',
                        'users.genero',
                    ])
                    ->whereNotNull('users.fecha_nacimiento');

                // Si no tiene permiso global ni es pastor principal, limitar a sus discípulos
                if (! $tienePermisoTodos && ! $usuario->iglesiaEncargada()->exists()) {
                    $discipulosQuery = $usuario->discipulos('sin-eliminados', false, true);
                    $discipulosQuery->getQuery()->columns = ['users.id'];
                    $query->whereIn('users.id', $discipulosQuery->getQuery());
                }

                if ($driver === 'pgsql') {
                    $exprMesDia = '(EXTRACT(MONTH FROM users.fecha_nacimiento) * 100 + EXTRACT(DAY FROM users.fecha_nacimiento))';
                } elseif ($driver === 'sqlite') {
                    $exprMesDia = "(CAST(strftime('%m', users.fecha_nacimiento) AS INTEGER) * 100 + CAST(strftime('%d', users.fecha_nacimiento) AS INTEGER))";
                } else {
                    $exprMesDia = '(MONTH(users.fecha_nacimiento) * 100 + DAY(users.fecha_nacimiento))';
                }

                $proximosCumpleanos = $query
                    ->orderByRaw("CASE WHEN {$exprMesDia} >= ? THEN 0 ELSE 1 END ASC", [$mesDiaHoy])
                    ->orderByRaw("{$exprMesDia} ASC")
                    ->orderBy('users.primer_nombre', 'asc')
                    ->limit(20)
                    ->get();
            }
        }

        return view('livewire.proximos-cumpleanos', [
            'proximosCumpleanos' => $proximosCumpleanos,
            'configuracion' => Configuracion::first(),
        ]);
    }
}
