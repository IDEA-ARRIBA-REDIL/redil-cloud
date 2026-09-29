<?php

namespace App\Services;

use App\Jobs\FinalizarMateriaJob;
use App\Models\MateriaAprobadaUsuario;
use App\Models\MateriaPeriodo;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GestionCierreMateriaService
{
    public function clave(int $materiaId): string
    {
        return 'cierre-materia:'.(tenant('id') ?? 'central').':'.$materiaId;
    }

    public function estado(int $materiaId): ?string
    {
        return Cache::store()->get($this->clave($materiaId))['estado'] ?? null;
    }

    public function estados(array $materiaIds): array
    {
        $claves = array_map(fn (int $id): string => $this->clave($id), $materiaIds);
        $valores = Cache::store()->many($claves);

        return collect($materiaIds)->mapWithKeys(fn (int $id): array => [$id => $valores[$this->clave($id)]['estado'] ?? null])->all();
    }

    public function vigente(int $materiaId, string $token): bool
    {
        $estado = Cache::store()->get($this->clave($materiaId));

        return ($estado['token'] ?? null) === $token && ($estado['estado'] ?? null) === 'procesando';
    }

    public function solicitar(MateriaPeriodo $materia, User $usuario): void
    {
        Cache::store()->lock($this->clave($materia->id).':accion', 15)->block(3, function () use ($materia, $usuario): void {
            $materia->refresh();
            $this->validarPeriodoAbierto($materia);
            if ($materia->finalizado || $this->estado($materia->id) === 'procesando') {
                throw ValidationException::withMessages(['materia' => 'La materia ya está cerrada o tiene un cierre en proceso.']);
            }
            $resumen = app(ResumenPeriodoService::class)->obtener($materia->periodo)['materias']->firstWhere('id', $materia->id)['resumen'];
            if (($resumen['motivos']['CRITERIOS_INCOMPLETOS'] ?? 0) > 0 || ($resumen['motivos']['MATRICULA_INCONSISTENTE'] ?? 0) > 0) {
                throw ValidationException::withMessages(['materia' => 'Revisa los criterios incompletos o las matrículas con varios horarios antes de cerrar la materia.']);
            }
            $token = (string) Str::uuid();
            Cache::store()->put($this->clave($materia->id), ['estado' => 'procesando', 'token' => $token], now()->addHours(6));
            try {
                Bus::dispatch(new FinalizarMateriaJob($materia, $usuario, tokenCierre: $token));
            } catch (Throwable $error) {
                $this->terminar($materia->id, $token, true);
                report($error);
                throw ValidationException::withMessages(['materia' => 'No se pudo iniciar el cierre. Puedes intentarlo nuevamente.']);
            }
        });
    }

    public function reabrir(MateriaPeriodo $materia): void
    {
        Cache::store()->lock($this->clave($materia->id).':accion', 15)->block(3, function () use ($materia): void {
            $materia->getConnection()->transaction(function () use ($materia): void {
                $actual = MateriaPeriodo::query()->lockForUpdate()->findOrFail($materia->id);
                $this->validarPeriodoAbierto($actual);
                if ($this->estado($actual->id) === 'procesando') {
                    throw ValidationException::withMessages(['materia' => 'Espera a que termine el cierre antes de reabrir la materia.']);
                }
                if (! $actual->finalizado) {
                    throw ValidationException::withMessages(['materia' => 'Esta materia ya está abierta.']);
                }
                MateriaAprobadaUsuario::query()->where('periodo_id', $actual->periodo_id)
                    ->where('materia_periodo_id', $actual->id)->delete();
                $actual->update(['finalizado' => false]);
            });
            Cache::store()->forget($this->clave($materia->id));
        });
    }

    public function terminar(int $materiaId, string $token, bool $fallo = false): void
    {
        if (! $this->vigente($materiaId, $token)) {
            return;
        }
        if ($fallo) {
            Cache::store()->put($this->clave($materiaId), ['estado' => 'error', 'token' => $token], now()->addHours(6));
        } else {
            Cache::store()->forget($this->clave($materiaId));
        }
    }

    private function validarPeriodoAbierto(MateriaPeriodo $materia): void
    {
        if (! $materia->periodo()->value('estado')) {
            throw ValidationException::withMessages(['materia' => 'Primero debes reabrir el periodo para modificar el cierre de sus materias.']);
        }
    }
}
