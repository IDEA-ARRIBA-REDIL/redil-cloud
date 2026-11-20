<?php

namespace App\Services;

use App\Models\Ofrenda;
use App\Models\TipoPago;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio exclusivo para el módulo de Donaciones / Ofrendas Online con ZonaPagos (API REST v5.0).
 *
 * Opera de forma 100% independiente de Pago, Compra e Ingreso.
 * Carga las credenciales por tenant desde el modelo TipoPago (`key_reservada = 'zona'`)
 * con fallback automático a `config('services.zonapagos.*')`.
 */
class ZonaPagosOfrendaService
{
    public const CODIGO_ESTADO_APROBADO = '1';

    public const CODIGO_ESTADO_PENDIENTE_CR = '4001';

    /**
     * Obtiene las credenciales de ZonaPagos del tenant actual (desde TipoPago o config).
     *
     * @return array{apiUrl: string, idComercio: int, usuario: string, clave: string, codigoServicio: string}
     */
    public function obtenerCredenciales(?TipoPago $tipoPago = null): array
    {
        if (! $tipoPago) {
            $tipoPago = TipoPago::where('key_reservada', 'zona')
                ->where('activo', true)
                ->first();
        }

        $apiUrl = ! empty($tipoPago?->enlace)
            ? rtrim((string) $tipoPago->enlace, '/')
            : rtrim((string) config('services.zonapagos.api_url', ''), '/');

        // Si el enlace en BD tiene el sufijo /InicioPago, lo limpiamos para usar la base
        if (str_ends_with($apiUrl, '/InicioPago')) {
            $apiUrl = substr($apiUrl, 0, -strlen('/InicioPago'));
        }

        $idComercio = ! empty($tipoPago?->client_id)
            ? (int) $tipoPago->client_id
            : (int) config('services.zonapagos.id_comercio', 0);

        $usuario = ! empty($tipoPago?->bussines_id)
            ? (string) $tipoPago->bussines_id
            : (string) config('services.zonapagos.usuario', '');

        $clave = ! empty($tipoPago?->key_id)
            ? (string) $tipoPago->key_id
            : (string) config('services.zonapagos.clave', '');

        $codigoServicio = ! empty($tipoPago?->account_id)
            ? (string) $tipoPago->account_id
            : (string) config('services.zonapagos.codigo_servicio', '');

        return [
            'apiUrl' => $apiUrl,
            'idComercio' => $idComercio,
            'usuario' => $usuario,
            'clave' => $clave,
            'codigoServicio' => $codigoServicio,
        ];
    }

    /**
     * Inicia el proceso de pago de una Ofrenda en ZonaPagos y retorna la URL de redirección.
     *
     * @param  array{nombre: string, apellido?: string, identificacion: string, email: string, telefono: string}  $datosDonante
     * @return array{success: bool, payment_url?: string, message?: string, gateway_response?: array<string, mixed>|null}
     */
    public function iniciarPago(Ofrenda $ofrenda, array $datosDonante): array
    {
        $credenciales = $this->obtenerCredenciales($ofrenda->tipoPago);
        $errorCredenciales = $this->validarCredenciales($credenciales);
        if ($errorCredenciales !== null) {
            Log::error("ZonaPagosOfrendaService::iniciarPago — {$errorCredenciales}", ['ofrenda_id' => $ofrenda->id]);

            return [
                'success' => false,
                'message' => $errorCredenciales,
            ];
        }

        // 1. Construir la descripción del pago (máximo 70 caracteres UTF-8 exigidos por ZonaPagos)
        $nombreTipoOfrenda = $ofrenda->tipoOfrenda?->nombre ?? 'Ofrenda';
        $descripcion = mb_substr('Donacion '.$nombreTipoOfrenda.' #'.$ofrenda->id, 0, 70, 'UTF-8');

        // 2. Separar nombre y apellido del donante para los campos de la pasarela
        $nombreCompleto = trim((string) ($datosDonante['nombre'] ?? ''));
        $apellido = trim((string) ($datosDonante['apellido'] ?? ''));

        if ($apellido === '' && preg_match('/\s+/u', $nombreCompleto) === 1) {
            $partes = preg_split('/\s+/u', $nombreCompleto, 2);
            $nombre = $partes[0] ?? 'Donante';
            $apellido = $partes[1] ?? '.';
        } else {
            $nombre = $nombreCompleto !== '' ? $nombreCompleto : 'Donante';
            $apellido = $apellido !== '' ? $apellido : '.';
        }

        $nombre = mb_substr($nombre, 0, 50, 'UTF-8');
        $apellido = mb_substr($apellido, 0, 50, 'UTF-8');
        $email = mb_substr(trim((string) ($datosDonante['email'] ?? '')), 0, 70, 'UTF-8');
        $identificacion = mb_substr(trim((string) ($datosDonante['identificacion'] ?? '')), 0, 30, 'UTF-8');
        $telefono = ! empty($datosDonante['telefono']) ? (string) $datosDonante['telefono'] : '0000000';

        // 3. Armar el payload completo v5.0 (incluyendo AdicionalesPago) con prefijo O-
        $payload = [
            'InformacionPago' => [
                'flt_total_con_iva' => (float) $ofrenda->valor,
                'flt_valor_iva' => 0,
                'str_id_pago' => 'O-'.$ofrenda->id,
                'str_descripcion_pago' => $descripcion,
                'str_email' => $email,
                'str_id_cliente' => $identificacion,
                'str_tipo_id' => '1',
                'str_nombre_cliente' => $nombre,
                'str_apellido_cliente' => $apellido,
                'str_telefono_cliente' => $telefono,
                'str_opcional1' => 'OFRENDA',
                'str_opcional2' => (string) ($ofrenda->tipo_ofrenda_id ?? ''),
                'str_opcional3' => (string) ($ofrenda->destinatario_id ?? ''),
                'str_opcional4' => '',
                'str_opcional5' => '',
            ],
            'InformacionSeguridad' => [
                'int_id_comercio' => $credenciales['idComercio'],
                'str_usuario' => $credenciales['usuario'],
                'str_clave' => $credenciales['clave'],
                'int_modalidad' => -1,
            ],
            'AdicionalesPago' => [],
            'AdicionalesConfiguracion' => [
                [
                    'int_codigo' => 50,
                    'str_valor' => $credenciales['codigoServicio'],
                ],
                [
                    'int_codigo' => 104,
                    'str_valor' => route('zonapagos.retornoUsuario'),
                ],
            ],
        ];

        $payloadLog = $payload;
        $payloadLog['InformacionSeguridad']['str_clave'] = '********';
        Log::info('ZonaPagosOfrendaService::iniciarPago — Enviando petición:', $payloadLog);

        try {
            $response = Http::timeout(30)->post($credenciales['apiUrl'].'/InicioPago', $payload);

            if ($response->successful() && (int) $response->json('int_codigo') === 1) {
                $paymentUrl = (string) $response->json('str_url');

                if (! filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
                    $paymentUrl = 'https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut='.$paymentUrl;
                }

                Log::info("ZonaPagosOfrendaService::iniciarPago — Éxito para Ofrenda ID {$ofrenda->id}. URL: {$paymentUrl}");

                return [
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'gateway_response' => $response->json(),
                ];
            }

            $errorDesc = (string) $response->json('str_descripcion_error', 'Error desconocido de la pasarela.');
            Log::error("ZonaPagosOfrendaService::iniciarPago — Error API para Ofrenda ID {$ofrenda->id}:", $response->json() ?? []);

            return [
                'success' => false,
                'message' => $errorDesc,
                'gateway_response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('ZonaPagosOfrendaService::iniciarPago — Excepción: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Ocurrió un error inesperado al conectar con la pasarela de pagos.',
            ];
        }
    }

    /**
     * Consulta el estado actual de una Ofrenda en ZonaPagos (`POST /VerificacionPago`).
     *
     * Distingue explícitamente entre transacción encontrada (`success`), inexistente en pasarela
     * (`no_encontrado`) y error de red/servidor (`error_red`).
     *
     * @return array{success: bool, data?: array<string, mixed>, response?: array<string, mixed>|null, message?: string, no_encontrado: bool, error_red: bool}
     */
    public function verificarPago(Ofrenda $ofrenda): array
    {
        $credenciales = $this->obtenerCredenciales($ofrenda->tipoPago);
        $errorCredenciales = $this->validarCredenciales($credenciales, validarCodigoServicio: false);
        if ($errorCredenciales !== null) {
            Log::error("ZonaPagosOfrendaService::verificarPago — {$errorCredenciales}", ['ofrenda_id' => $ofrenda->id]);

            return [
                'success' => false,
                'message' => $errorCredenciales,
                'no_encontrado' => false,
                'error_red' => true,
            ];
        }

        $payload = [
            'int_id_comercio' => $credenciales['idComercio'],
            'str_usr_comercio' => $credenciales['usuario'],
            'str_pwd_comercio' => $credenciales['clave'],
            'str_id_pago' => 'O-'.$ofrenda->id,
            'int_no_pago' => -1,
        ];

        Log::info("ZonaPagosOfrendaService::verificarPago — Consultando Ofrenda ID {$ofrenda->id}.");

        try {
            $response = Http::timeout(30)->post($credenciales['apiUrl'].'/VerificacionPago', $payload);

            if (! $response->successful()) {
                Log::error("ZonaPagosOfrendaService::verificarPago — HTTP {$response->status()} para Ofrenda ID {$ofrenda->id}.");

                return [
                    'success' => false,
                    'message' => "Error HTTP {$response->status()} al consultar ZonaPagos.",
                    'response' => $response->json(),
                    'no_encontrado' => false,
                    'error_red' => true,
                ];
            }

            $json = $response->json() ?? [];
            $intError = $json['int_error'] ?? null;
            $cantidadPagos = $json['int_cantidad_pagos'] ?? null;
            $strResPago = trim((string) ($json['str_res_pago'] ?? ''));

            if ($intError === 0 && $strResPago !== '' && ($cantidadPagos === null || (int) $cantidadPagos > 0)) {
                return [
                    'success' => true,
                    'data' => $json,
                    'no_encontrado' => false,
                    'error_red' => false,
                ];
            }

            $detalle = (string) ($json['str_detalle'] ?? 'Sin registros de pago en pasarela.');
            Log::warning("ZonaPagosOfrendaService::verificarPago — No se encontró Ofrenda ID {$ofrenda->id}. Detalle: {$detalle}");

            return [
                'success' => false,
                'message' => 'La transacción no fue encontrada en ZonaPagos: '.$detalle,
                'response' => $json,
                'no_encontrado' => true,
                'error_red' => false,
            ];
        } catch (\Throwable $e) {
            Log::error('ZonaPagosOfrendaService::verificarPago — Excepción: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Ocurrió un error inesperado al verificar la ofrenda.',
                'no_encontrado' => false,
                'error_red' => true,
            ];
        }
    }

    /**
     * Parsea el campo `str_res_pago` de VerificacionPago (Tabla 12 del manual ZonaPagos v5.0).
     * Descarta segmentos vacíos finales tras `|;|` y prioriza la sub-transacción aprobada (`1`).
     *
     * @return array<string, string>
     */
    public function parsearRespuestaVerificacion(string $strResPago): array
    {
        if (trim($strResPago) === '') {
            return [];
        }

        $segmentos = array_values(array_filter(
            array_map('trim', explode('|;|', $strResPago)),
            fn (string $segmento): bool => $segmento !== ''
        ));

        if ($segmentos === []) {
            return [];
        }

        $transaccionesParseadas = array_map(
            fn (string $segmento): array => $this->parsearSegmentoTransaccion($segmento),
            $segmentos
        );

        foreach ($transaccionesParseadas as $transaccion) {
            if (($transaccion['int_estado_pago'] ?? '') === self::CODIGO_ESTADO_APROBADO) {
                return $transaccion;
            }
        }

        return end($transaccionesParseadas) ?: [];
    }

    /**
     * Valida que las credenciales mínimas de ZonaPagos estén configuradas.
     *
     * @param  array{apiUrl: string, idComercio: int, usuario: string, clave: string, codigoServicio: string}  $credenciales
     */
    protected function validarCredenciales(array $credenciales, bool $validarCodigoServicio = true): ?string
    {
        if ($credenciales['apiUrl'] === '' || $credenciales['idComercio'] <= 0 || $credenciales['usuario'] === '' || $credenciales['clave'] === '') {
            return 'Credenciales de ZonaPagos incompletas. Verifica tipos_pago o las variables ZONAPAGOS_* en .env.';
        }

        if ($validarCodigoServicio && $credenciales['codigoServicio'] === '') {
            return 'Código de servicio ZonaPagos (account_id / ZONAPAGOS_CODIGO_SERVICIO) no configurado.';
        }

        return null;
    }

    /**
     * Parsea una transacción individual separada por `|` según Tabla 12 del manual ZonaPagos v5.0.
     *
     * @return array<string, string>
     */
    protected function parsearSegmentoTransaccion(string $segmento): array
    {
        $v = explode('|', $segmento);

        $data = [
            'int_ped_numero' => trim($v[0] ?? ''),
            'int_n_pago' => trim($v[1] ?? ''),
            'int_pago_parcial' => trim($v[2] ?? ''),
            'int_pago_terminado' => trim($v[3] ?? ''),
            'int_estado_pago' => trim($v[4] ?? ''),
            'dbl_valor_pagado' => trim($v[5] ?? ''),
            'dbl_total_pago' => trim($v[6] ?? ''),
            'dbl_valor_iva_pagado' => trim($v[7] ?? ''),
            'str_descripcion' => trim($v[8] ?? ''),
            'str_id_cliente' => trim($v[9] ?? ''),
            'str_nombre' => trim($v[10] ?? ''),
            'str_apellido' => trim($v[11] ?? ''),
            'str_telefono' => trim($v[12] ?? ''),
            'str_email' => trim($v[13] ?? ''),
            'str_campo1' => trim($v[14] ?? ''),
            'str_campo2' => trim($v[15] ?? ''),
            'str_campo3' => trim($v[16] ?? ''),
            'str_campo4' => trim($v[17] ?? ''),
            'str_campo5' => trim($v[18] ?? ''),
            'dat_fecha' => trim($v[19] ?? ''),
            'int_id_forma_pago' => trim($v[20] ?? ''),
        ];

        if ($data['int_id_forma_pago'] === '29') {
            $data['str_ticketID'] = trim($v[21] ?? '');
            $data['int_codigo_servicio'] = trim($v[22] ?? '');
            $data['int_codigo_banco'] = trim($v[23] ?? '');
            $data['str_nombre_banco'] = trim($v[24] ?? '');
            $data['str_codigo_transaccion'] = trim($v[25] ?? '');
            $data['int_ciclo_transaccion'] = trim($v[26] ?? '');
        } elseif (in_array($data['int_id_forma_pago'], ['32', '51'], true)) {
            $data['str_ticketID'] = trim($v[21] ?? '');
            $data['int_numero_tarjeta'] = trim($v[22] ?? '');
            $data['str_franquicia'] = trim($v[23] ?? '');
            $data['int_cod_aprobacion'] = trim($v[24] ?? '');
            $data['int_num_recibido'] = trim($v[25] ?? '');
        }

        return $data;
    }
}
