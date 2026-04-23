<?php

namespace App\Services;

use App\Models\TipoNotificacion;
use App\Models\User;
use App\Notifications\NotificacionGeneral;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class NotificacionService
{
    /**
     * Despacha una notificación inteligente basada en su configuración de alcance.
     *
     * @param  string  $slug  Slug del tipo de notificación (ej: 'matricula_registrada')
     * @param  array  $datosMensaje  Arreglo con titulo, mensaje, icono, url, color
     * @param  User|null  $usuarioOriginador  El usuario que dispara la acción (default: auth()->user())
     * @param  User|null  $usuarioTarget  Usuario específico para alcance 'individual'
     */
    public static function dispatch(
        string $slug,
        array $datosMensaje,
        ?User $usuarioOriginador = null,
        ?User $usuarioTarget = null
    ): bool {
        $usuarioOriginador = $usuarioOriginador ?? auth()->user();

        // 1. Obtener la configuración del tipo de notificación
        $config = TipoNotificacion::where('slug', $slug)->first();

        // 2. Si no existe o está desactivada, salimos
        if (! $config || ! $config->activo) {
            return false;
        }

        // 3. Calcular la fecha de expiración si aplica
        if ($config->dias_vigencia) {
            $datosMensaje['expira_en'] = Carbon::now()->addDays($config->dias_vigencia)->toISOString();
        }

        // 4. Resolver la audiencia acumulando cada alcance configurado
        $alcances = is_array($config->alcance) ? $config->alcance : [$config->alcance];
        $audiencia = collect();

        foreach ($alcances as $alcance) {
            $audiencia = $audiencia->merge(
                self::resolverAudiencia($alcance, $usuarioOriginador, $usuarioTarget)
            );
        }

        // 5. Limpiar duplicados
        $audiencia = $audiencia->unique('id');

        // 6. Aplicar filtros de Sede y Tipo de Usuario si están configurados
        if ($config->sedes_ids) {
            $audiencia = $audiencia->whereIn('sede_id', $config->sedes_ids);
        }

        if ($config->tipos_usuario_ids) {
            $audiencia = $audiencia->whereIn('tipo_usuario_id', $config->tipos_usuario_ids);
        }

        // Si ningún alcance es individual-puro, quitamos al originador de la lista
        $tieneAlcanceIndividual = in_array(TipoNotificacion::ALCANCE_INDIVIDUAL, $alcances);
        if (! $tieneAlcanceIndividual) {
            $audiencia = $audiencia->reject(fn ($u) => $u->id === $usuarioOriginador?->id);
        }

        // 7. Enviar
        if ($audiencia->isNotEmpty()) {
            Notification::send($audiencia, new NotificacionGeneral($datosMensaje));
        }

        return true;
    }

    /**
     * Resuelve la audiencia para un alcance individual.
     */
    private static function resolverAudiencia(
        string $alcance,
        ?User $usuarioOriginador,
        ?User $usuarioTarget
    ): Collection {
        return match ($alcance) {
            TipoNotificacion::ALCANCE_GLOBAL => User::all(),

            TipoNotificacion::ALCANCE_INDIVIDUAL => collect(
                array_filter([$usuarioTarget ?? $usuarioOriginador])
            ),

            TipoNotificacion::ALCANCE_ESCALA_MINISTERIAL => $usuarioOriginador
                ? $usuarioOriginador->lideres('objeto')->get()
                : collect(),

            TipoNotificacion::ALCANCE_MINISTERIO_DIRECTO => $usuarioOriginador
                ? collect($usuarioOriginador->encargadosDirectos())
                : collect(),

            default => collect(),
        };
    }
}
