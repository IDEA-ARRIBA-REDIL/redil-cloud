<?php

namespace App\Services;

use App\Models\ParienteUsuario;
use App\Models\TipoParentesco;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RelacionesFamiliares
{
    public function guardar(int $usuario, int $pariente, int $tipo, int $responsabilidad, ?int $relacionId = null): void
    {
        DB::transaction(function () use ($usuario, $pariente, $tipo, $responsabilidad, $relacionId): void {
            // Las dos fichas bloquean a las mismas personas en el mismo orden.
            $personas = User::query()->whereKey([$usuario, $pariente])->orderBy('id')->lockForUpdate()->pluck('id');
            if ($personas->count() !== 2) {
                throw ValidationException::withMessages(['pariente' => 'Se necesitan dos personas vigentes y diferentes.']);
            }

            $directa = $this->buscar($usuario, $pariente);
            $inversa = $this->buscar($pariente, $usuario);
            if ($relacionId === null && ($directa || $inversa)) {
                throw ValidationException::withMessages(['pariente' => 'Ya existe una relación entre estas personas.']);
            }
            if ($relacionId !== null && ($directa?->id !== $relacionId || ! $inversa)) {
                throw ValidationException::withMessages(['pariente' => 'La relación cambió o está incompleta. Revisa ambas fichas.']);
            }

            $parentesco = TipoParentesco::find($tipo);
            $reciproco = $parentesco?->relacionado_con ?? $tipo;
            if (! $parentesco || ($reciproco != $tipo && ! TipoParentesco::whereKey($reciproco)->exists())) {
                throw ValidationException::withMessages(['tipoParentesco' => 'El parentesco seleccionado o su inverso ya no existe.']);
            }

            $directa ??= new ParienteUsuario(['user_id' => $usuario, 'pariente_user_id' => $pariente]);
            $inversa ??= new ParienteUsuario(['user_id' => $pariente, 'pariente_user_id' => $usuario]);
            $directa->fill(['tipo_pariente_id' => $tipo, 'es_el_responsable' => $responsabilidad === 2])->save();
            $inversa->fill(['tipo_pariente_id' => $reciproco, 'es_el_responsable' => $responsabilidad === 3])->save();
        }, 3);
    }

    public function eliminar(ParienteUsuario $relacion): void
    {
        DB::transaction(function () use ($relacion): void {
            User::withTrashed()->whereKey([$relacion->user_id, $relacion->pariente_user_id])->orderBy('id')->lockForUpdate()->pluck('id');
            $actual = $this->buscar($relacion->user_id, $relacion->pariente_user_id);
            abort_unless($actual?->id === $relacion->id, 404);
            $actual->delete();
            $this->buscar($relacion->pariente_user_id, $relacion->user_id)?->delete();
        }, 3);
    }

    private function buscar(int $usuario, int $pariente): ?ParienteUsuario
    {
        return ParienteUsuario::where('user_id', $usuario)->where('pariente_user_id', $pariente)->lockForUpdate()->first();
    }
}
