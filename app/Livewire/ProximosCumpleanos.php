<?php

namespace App\Livewire;

use App\Models\Configuracion;
use App\Models\User;
use Livewire\Component;

class ProximosCumpleanos extends Component
{
    public function render()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $personas = collect();

        if ($rolActivo) {
            if (
                $rolActivo->hasPermissionTo('personas.lista_asistentes_todos') ||
                $rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')
            ) {
                // 1. Discípulos si tiene permiso ministerial
                if ($rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
                    $personas = auth()->user()->discipulos('sin-eliminados', false);
                }

                // 2. Todos los usuarios activos si tiene permiso general
                if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos')) {
                    $personas = User::query()
                        ->whereNotNull('fecha_nacimiento')
                        ->get();
                }
            }
        }

        $hoy = now();

        $proximosCumpleanos = $personas
            ->whereNotNull('fecha_nacimiento')
            ->sortBy(function ($usuario) use ($hoy) {
                $cumpleEsteAnio = $usuario->fecha_nacimiento->copy()->setYear($hoy->year);

                if ($cumpleEsteAnio->isBefore($hoy->startOfDay())) {
                    return $cumpleEsteAnio->addYear();
                }

                return $cumpleEsteAnio;
            })
            ->take(20);

        return view('livewire.proximos-cumpleanos', [
            'proximosCumpleanos' => $proximosCumpleanos,
            'configuracion' => Configuracion::first(),
        ]);
    }
}
