<?php

namespace App\Services;

use App\Models\EstadoPago;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\TipoPago;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Servicio canónico para la integración con la pasarela ZonaPagos (API REST v5.0).
 *
 * Este es el ÚNICO servicio autorizado para comunicarse con ZonaPagos en compras,
 * abonos y matrículas de Escuelas. Usa Laravel Http Client (Http::post).
 *
 * Credenciales por tenant desde la tabla `tipos_pago` (`key_reservada = 'zona'`):
 *   enlace, client_id (id_comercio), bussines_id (usuario), key_id (clave),
 *   account_id (codigo_servicio, p.ej. '1001' en producción)
 * con fallback automático a config('services.zonapagos.*') → variables `.env`.
 */
class ZonaPagosService
{
    public const CODIGO_ESTADO_APROBADO = '1';

    public const CODIGO_ESTADO_PENDIENTE_CR = '4001';

    public function __construct(
        protected ?EstadoFinancieroService $estadoFinancieroService = null,
    ) {
        $this->estadoFinancieroService ??= app(EstadoFinancieroService::class);
    }

    /**
     * Obtiene las credenciales de ZonaPagos del tenant actual (desde TipoPago con fallback a config).
     *
     * @return array{apiUrl: string, idComercio: int, usuario: string, clave: string, codigoServicio: string}
     */
    public function obtenerCredenciales(?TipoPago $tipoPago = null): array
    {
        if (! $tipoPago) {
            $tipoPago = TipoPago::query()
                ->where('key_reservada', 'zona')
                ->where('activo', true)
                ->first();
        }

        $apiUrlRaw = ! empty($tipoPago?->enlace)
            ? (string) $tipoPago->enlace
            : (string) config('services.zonapagos.api_url', '');

        $apiUrl = rtrim($apiUrlRaw, '/');
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
     * Inicia un proceso de pago en ZonaPagos y retorna la URL de redirección.
     *
     * @param  Pago  $pago  Registro de pago ya creado en nuestra BD (su ID genera str_id_pago = P-{id}).
     * @param  array<string, mixed>  $datosComprador  ['nombre', 'apellido', 'identificacion', 'email', 'telefono']
     * @param  string  $tipoCompra  Etiqueta guardada en str_opcional1 ('COMPRA GENERAL', 'ABONO', 'ESCUELAS')
     * @return array{success: bool, payment_url?: string|null, message?: string|null, gateway_response?: array<string, mixed>|null, response?: array<string, mixed>|null}
     */
    public function iniciarPago(Pago $pago, array $datosComprador, string $tipoCompra = 'COMPRA GENERAL'): array
    {
        $compra = $pago->compra;

        if (! $compra) {
            return ['success' => false, 'message' => 'El pago no tiene una compra asociada.'];
        }

        $credenciales = $this->obtenerCredenciales($pago->tipoPago);
        $errorCredenciales = $this->validarCredenciales($credenciales);
        if ($errorCredenciales !== null) {
            Log::error("ZonaPagosService::iniciarPago — {$errorCredenciales}", ['pago_id' => $pago->id]);

            return ['success' => false, 'message' => $errorCredenciales];
        }

        // 1. Descripción del pago con límite estricto de 70 caracteres UTF-8 según manual v5.0
        $nombreActividad = $compra->actividad?->nombre ?? 'Actividad';
        $descripcionRaw = 'Pago '.$tipoCompra.' - '.$nombreActividad;
        $descripcion = mb_substr($descripcionRaw, 0, 70, 'UTF-8');

        // 2. Separar nombre y apellido del comprador (la API nunca acepta apellido vacío)
        $nombreCompleto = trim((string) ($datosComprador['nombre'] ?? ''));
        $apellido = trim((string) ($datosComprador['apellido'] ?? ''));

        if ($apellido === '' && preg_match('/\s+/u', $nombreCompleto) === 1) {
            $partes = preg_split('/\s+/u', $nombreCompleto, 2);
            $nombre = $partes[0] ?? 'Usuario';
            $apellido = $partes[1] ?? '.';
        } else {
            $nombre = $nombreCompleto !== '' ? $nombreCompleto : 'Usuario';
            $apellido = $apellido !== '' ? $apellido : '.';
        }

        // 3. Aplicar límites de longitud UTF-8 seguros exigidos por la API
        $nombre = mb_substr($nombre, 0, 50, 'UTF-8');
        $apellido = mb_substr($apellido, 0, 50, 'UTF-8');
        $email = mb_substr(trim((string) ($datosComprador['email'] ?? '')), 0, 70, 'UTF-8');
        $identificacion = mb_substr(trim((string) ($datosComprador['identificacion'] ?? '')), 0, 30, 'UTF-8');
        $telefono = ! empty($datosComprador['telefono']) ? (string) $datosComprador['telefono'] : '0000000';

        // 4. Construir payload completo conforme a API REST ZonaPagos v5.0 (incluyendo AdicionalesPago)
        $payload = [
            'InformacionPago' => [
                'flt_total_con_iva' => (float) $pago->valor,
                'flt_valor_iva' => 0,
                'str_id_pago' => 'P-'.$pago->id,
                'str_descripcion_pago' => $descripcion,
                'str_email' => $email,
                'str_id_cliente' => $identificacion,
                'str_tipo_id' => '1',
                'str_nombre_cliente' => $nombre,
                'str_apellido_cliente' => $apellido,
                'str_telefono_cliente' => $telefono,
                'str_opcional1' => $tipoCompra,
                'str_opcional2' => (string) $compra->id,
                'str_opcional3' => '',
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
                    'str_valor' => (string) $credenciales['codigoServicio'],
                ],
                [
                    'int_codigo' => 104,
                    'str_valor' => route('zonapagos.retornoUsuario'),
                ],
            ],
        ];

        $payloadLog = $payload;
        $payloadLog['InformacionSeguridad']['str_clave'] = '********';
        Log::info('ZonaPagosService::iniciarPago — Enviando petición:', $payloadLog);

        try {
            $response = Http::timeout(30)->post($credenciales['apiUrl'].'/InicioPago', $payload);

            // int_codigo = 1 indica únicamente creación exitosa de la orden en ZonaPagos (NO aprobación)
            if ($response->successful() && (int) $response->json('int_codigo') === 1) {
                $paymentUrl = (string) $response->json('str_url');

                if (! filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
                    $paymentUrl = 'https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut='.$paymentUrl;
                }

                Log::info("ZonaPagosService::iniciarPago — Éxito para Pago ID {$pago->id}. URL: {$paymentUrl}");

                return [
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'gateway_response' => $response->json(),
                ];
            }

            $errorDesc = (string) $response->json('str_descripcion_error', 'Error desconocido de la pasarela.');
            Log::error("ZonaPagosService::iniciarPago — Error API para Pago ID {$pago->id}:", $response->json() ?? []);

            return [
                'success' => false,
                'message' => $errorDesc,
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('ZonaPagosService::iniciarPago — Excepción: '.$e->getMessage());

            return ['success' => false, 'message' => 'Ocurrió un error inesperado al conectar con la pasarela.'];
        }
    }

    /**
     * Consulta el estado actual de un pago en ZonaPagos (`POST /VerificacionPago`).
     *
     * Distingue explícitamente entre:
     * - Transacción encontrada (`success = true`)
     * - Transacción no existente en ZonaPagos (`no_encontrado = true, error_red = false`)
     * - Fallo de red / 5xx / credenciales (`no_encontrado = false, error_red = true`)
     *
     * @return array{success: bool, data?: array<string, mixed>|null, message?: string|null, response?: array<string, mixed>|null, no_encontrado: bool, error_red: bool}
     */
    public function verificarPago(Pago $pago): array
    {
        $credenciales = $this->obtenerCredenciales($pago->tipoPago);
        $errorCredenciales = $this->validarCredenciales($credenciales, validarCodigoServicio: false);
        if ($errorCredenciales !== null) {
            Log::error("ZonaPagosService::verificarPago — {$errorCredenciales}", ['pago_id' => $pago->id]);

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
            'str_id_pago' => 'P-'.$pago->id,
            'int_no_pago' => -1,
        ];

        Log::info("ZonaPagosService::verificarPago — Consultando Pago ID {$pago->id}.");

        try {
            $response = Http::timeout(30)->post($credenciales['apiUrl'].'/VerificacionPago', $payload);

            if (! $response->successful()) {
                Log::error("ZonaPagosService::verificarPago — HTTP {$response->status()} para Pago ID {$pago->id}.");

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
            $cantidadPagos = (int) ($json['int_cantidad_pagos'] ?? 0);
            $strResPago = trim((string) ($json['str_res_pago'] ?? ''));

            if ($intError === 0 && $cantidadPagos > 0 && $strResPago !== '') {
                return [
                    'success' => true,
                    'data' => $json,
                    'no_encontrado' => false,
                    'error_red' => false,
                ];
            }

            $detalle = (string) ($json['str_detalle'] ?? 'Sin registros de pago en pasarela.');
            Log::warning("ZonaPagosService::verificarPago — Sin transacción finalizada para Pago ID {$pago->id}. Detalle: {$detalle}");

            return [
                'success' => false,
                'message' => 'La transacción no fue encontrada en ZonaPagos: '.$detalle,
                'response' => $json,
                'no_encontrado' => true,
                'error_red' => false,
            ];
        } catch (\Throwable $e) {
            Log::error('ZonaPagosService::verificarPago — Excepción: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Ocurrió un error inesperado al verificar el pago.',
                'no_encontrado' => false,
                'error_red' => true,
            ];
        }
    }

    /**
     * Parsea el campo `str_res_pago` de VerificacionPago (Tabla 12 del manual ZonaPagos v5.0).
     *
     * Soporta múltiples intentos separados por `|;|`, descarta segmentos vacíos finales
     * y prioriza cualquier sub-transacción aprobada (`int_estado_pago === '1'`).
     *
     * @return array<string, string>
     */
    public function parsearRespuestaVerificacion(string $strResPago): array
    {
        if (trim($strResPago) === '') {
            return [];
        }

        // 1. Dividir por separador de múltiples transacciones '|;|' y filtrar segmentos vacíos
        $segmentos = array_values(array_filter(
            array_map('trim', explode('|;|', $strResPago)),
            fn (string $segmento): bool => $segmento !== ''
        ));

        if ($segmentos === []) {
            return [];
        }

        // 2. Parsear cada segmento y priorizar el aprobado ('1') si existe; si no, el último
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
     * Punto único y transaccional de sincronización de un Pago contra ZonaPagos.
     *
     * Usado por la Sonda (`pagos:verificar-zonapagos`), el Callback (`ZonaPagosController`),
     * el Retorno de Usuario y el Polling del Checkout, garantizando comportamiento idéntico,
     * bloqueos con `lockForUpdate()`, liberación de matrículas y restauración de cupos.
     *
     * @return array{success: bool, actualizado: bool, estado?: string|null, codigo_externo?: string|null, datos_parseados?: array<string, string>, pago: Pago, motivo?: string, message?: string|null, error_red?: bool, abandonado?: bool}
     */
    public function sincronizarPagoDesdePasarela(
        Pago $pago,
        bool $evaluarAbandono = false,
        ?int $minutosAbandonoPasarela = null
    ): array {
        $pago->loadMissing(['estadoPago', 'tipoPago', 'compra']);

        if (! $pago->compra) {
            return [
                'success' => false,
                'actualizado' => false,
                'pago' => $pago,
                'message' => 'El pago no tiene una compra asociada.',
            ];
        }

        // 1. Si el pago ya tiene un estado definitivo (aprobado o rechazado/anulado), es idempotente
        if ($pago->estadoPago && ! $pago->estadoPago->estado_pendiente) {
            if (! $pago->estadoPago->estado_final_inscripcion) {
                $this->vincularMatriculaLegacySiAplica($pago);
                $this->estadoFinancieroService->limpiarReservasFallidas($pago);
            }

            return [
                'success' => true,
                'actualizado' => false,
                'estado' => $pago->estadoPago->nombre,
                'pago' => $pago->fresh(['estadoPago', 'compra']),
                'motivo' => 'ya_finalizado',
            ];
        }

        // 2. Consultar el estado real en ZonaPagos (POST /VerificacionPago)
        $resultado = $this->verificarPago($pago);

        if ($resultado['error_red']) {
            return [
                'success' => false,
                'actualizado' => false,
                'error_red' => true,
                'pago' => $pago,
                'message' => $resultado['message'] ?? 'Error de conexión con ZonaPagos.',
            ];
        }

        $minutosLimite = $minutosAbandonoPasarela ?? (int) config('services.zonapagos.minutos_abandono_pasarela', 45);
        $estaVencido = $pago->created_at !== null && $pago->created_at->lte(now()->subMinutes($minutosLimite));

        // 3. Caso A: ZonaPagos respondió OK pero el pago no existe allí (usuario cerró sin enviar al banco)
        if ($resultado['no_encontrado']) {
            if ($evaluarAbandono && $estaVencido) {
                return $this->marcarPagoComoAbandonado($pago, [
                    'verificacion_no_encontrado' => $resultado['response'] ?? ['detalle' => $resultado['message'] ?? 'Sin registro'],
                    'motivo_abandono' => "Sin registro en ZonaPagos tras {$minutosLimite} minutos.",
                ]);
            }

            return [
                'success' => true,
                'actualizado' => false,
                'estado' => $pago->estadoPago?->nombre,
                'pago' => $pago,
                'motivo' => 'pendiente_sin_registro_en_pasarela',
            ];
        }

        // 4. Caso B: Parsear trama de respuesta y mapear código externo (int_estado_pago)
        $strResPago = (string) ($resultado['data']['str_res_pago'] ?? '');
        $datosTransaccion = $this->parsearRespuestaVerificacion($strResPago);
        $codigoEstadoExterno = $datosTransaccion['int_estado_pago'] ?? null;

        if (! $codigoEstadoExterno) {
            return [
                'success' => false,
                'actualizado' => false,
                'pago' => $pago,
                'message' => 'No se pudo extraer int_estado_pago de str_res_pago.',
            ];
        }

        $nuevoEstado = EstadoPago::query()
            ->where('id_codigo_externo', $codigoEstadoExterno)
            ->where('tipo_pago_id', $pago->tipo_pago_id)
            ->first();

        if (! $nuevoEstado) {
            Log::error("ZonaPagosService::sincronizarPagoDesdePasarela — Código externo '{$codigoEstadoExterno}' no configurado para TipoPago ID {$pago->tipo_pago_id}.");

            return [
                'success' => false,
                'actualizado' => false,
                'codigo_externo' => $codigoEstadoExterno,
                'pago' => $pago,
                'message' => "Estado externo '{$codigoEstadoExterno}' no configurado en estados_pago.",
            ];
        }

        $datosActualizacion = $this->construirDatosActualizacionPago($pago, $resultado['data'] ?? [], $datosTransaccion);

        // 5. Si continúa pendiente en pasarela (888, 999, 200, 4001)
        if ($nuevoEstado->estado_pendiente) {
            // 4001 (Pendiente por CR en tarjeta) puede tardar ~24h según manual v5.0: NO expirar a los 45 min
            if ($evaluarAbandono && $estaVencido && $codigoEstadoExterno !== self::CODIGO_ESTADO_PENDIENTE_CR) {
                return $this->marcarPagoComoAbandonado($pago, array_merge(
                    $datosActualizacion['gateway_response'],
                    ['motivo_abandono' => "Transacción en estado {$codigoEstadoExterno} tras {$minutosLimite} minutos sin concretar."]
                ));
            }

            if ((int) $nuevoEstado->id !== (int) $pago->estado_pago_id) {
                $pagoActualizado = $this->estadoFinancieroService->aplicar($pago, $nuevoEstado, $datosActualizacion);

                return [
                    'success' => true,
                    'actualizado' => true,
                    'estado' => $nuevoEstado->nombre,
                    'codigo_externo' => $codigoEstadoExterno,
                    'datos_parseados' => $datosTransaccion,
                    'pago' => $pagoActualizado,
                    'motivo' => 'pendiente_actualizado',
                ];
            }

            // Persistimos última evidencia de pasarela sin reiniciar created_at ni eliminar matrícula
            $pago->gateway_response = $datosActualizacion['gateway_response'];
            $pago->save();

            return [
                'success' => true,
                'actualizado' => false,
                'estado' => $nuevoEstado->nombre,
                'codigo_externo' => $codigoEstadoExterno,
                'datos_parseados' => $datosTransaccion,
                'pago' => $pago,
                'motivo' => 'pendiente_sin_cambio',
            ];
        }

        // 6. Estado definitivo (aprobado '1' o rechazado '1000', '1001', '4000', '4003')
        $this->vincularMatriculaLegacySiAplica($pago);
        $pagoActualizado = $this->estadoFinancieroService->aplicar($pago, $nuevoEstado, $datosActualizacion);

        Log::info("ZonaPagosService::sincronizarPagoDesdePasarela — Pago ID {$pago->id} sincronizado a '{$nuevoEstado->nombre}' (código {$codigoEstadoExterno}).");

        return [
            'success' => true,
            'actualizado' => true,
            'estado' => $nuevoEstado->nombre,
            'codigo_externo' => $codigoEstadoExterno,
            'datos_parseados' => $datosTransaccion,
            'pago' => $pagoActualizado,
            'motivo' => $nuevoEstado->estado_final_inscripcion ? 'aprobado' : 'rechazado',
        ];
    }

    /**
     * Marca un pago pendiente vencido como Transacción Abandonada (ID 16 del método ZonaPagos),
     * reconciliando la compra y liberando matrícula + cupo de forma atómica.
     *
     * @param  array<string, mixed>  $evidenciaGateway
     * @return array{success: bool, actualizado: bool, estado?: string|null, pago: Pago, motivo?: string, message?: string|null, abandonado?: bool}
     */
    protected function marcarPagoComoAbandonado(Pago $pago, array $evidenciaGateway): array
    {
        $estadoAbandonado = $this->resolverEstadoAbandonado((int) $pago->tipo_pago_id);

        if (! $estadoAbandonado) {
            Log::error("ZonaPagosService::marcarPagoComoAbandonado — No existe estado anulado/abandonado para TipoPago ID {$pago->tipo_pago_id}.");

            return [
                'success' => false,
                'actualizado' => false,
                'pago' => $pago,
                'message' => 'No se encontró estado de Transacción Abandonada configurado en estados_pago.',
            ];
        }

        $this->vincularMatriculaLegacySiAplica($pago);

        $gatewayActual = is_array($pago->gateway_response) ? $pago->gateway_response : [];
        $pagoActualizado = $this->estadoFinancieroService->aplicar($pago, $estadoAbandonado, [
            'gateway_response' => array_merge($gatewayActual, [
                'abandono' => $evidenciaGateway,
                'fecha_abandono' => now()->toIso8601String(),
            ]),
        ]);

        Log::info("ZonaPagosService::marcarPagoComoAbandonado — Pago ID {$pago->id} marcado como '{$estadoAbandonado->nombre}' y reserva liberada.");

        return [
            'success' => true,
            'actualizado' => true,
            'abandonado' => true,
            'estado' => $estadoAbandonado->nombre,
            'pago' => $pagoActualizado,
            'motivo' => 'abandonado',
        ];
    }

    /**
     * Resuelve el EstadoPago "Transacción Abandonada" (ID 16 / sin código externo) para el método ZonaPagos.
     */
    protected function resolverEstadoAbandonado(int $tipoPagoId): ?EstadoPago
    {
        return EstadoPago::query()
            ->where('tipo_pago_id', $tipoPagoId)
            ->where('estado_anulado_inscripcion', true)
            ->where('estado_pendiente', false)
            ->where('estado_final_inscripcion', false)
            ->whereNull('id_codigo_externo')
            ->first()
            ?: EstadoPago::query()
                ->where('tipo_pago_id', $tipoPagoId)
                ->where('estado_anulado_inscripcion', true)
                ->where('estado_pendiente', false)
                ->where('estado_final_inscripcion', false)
                ->first();
    }

    /**
     * Si una matrícula histórica de Escuelas fue creada solo con `referencia_pago = pago->id`
     * (sin `compra_id` ni `pago_id`), completa sus FKs antes de ejecutar EstadoFinancieroService.
     */
    protected function vincularMatriculaLegacySiAplica(Pago $pago): void
    {
        if (! $pago->compra_id) {
            return;
        }

        $query = Matricula::query()
            ->where('referencia_pago', (string) $pago->id)
            ->where('estado_pago_matricula', 'pendiente')
            ->where(function ($subQuery): void {
                $subQuery->whereNull('pago_id')->orWhereNull('compra_id');
            });

        if ($pago->compra?->user_id) {
            $query->where('user_id', $pago->compra->user_id);
        }

        $query->update([
            'compra_id' => $pago->compra_id,
            'pago_id' => $pago->id,
        ]);
    }

    /**
     * Construye el arreglo de campos a actualizar en `pagos`, incluyendo datos de conciliación
     * exigidos por ZonaPagos (CUS, TicketID, forma de pago, banco/franquicia).
     *
     * @param  array<string, mixed>  $respuestaCruda
     * @param  array<string, string>  $datosTransaccion
     * @return array<string, mixed>
     */
    protected function construirDatosActualizacionPago(Pago $pago, array $respuestaCruda, array $datosTransaccion): array
    {
        $referenciaPago = ($datosTransaccion['int_n_pago'] ?? '') !== ''
            ? $datosTransaccion['int_n_pago']
            : (($datosTransaccion['int_ped_numero'] ?? '') !== '' ? $datosTransaccion['int_ped_numero'] : $pago->referencia_pago);

        $datos = [
            'referencia_pago' => $referenciaPago,
            'gateway_response' => array_merge($respuestaCruda, [
                'datos_parseados' => $datosTransaccion,
            ]),
        ];

        $connection = $pago->getConnectionName();

        if (Schema::connection($connection)->hasColumn('pagos', 'gateway_transaction_id')) {
            $cusOTicket = ($datosTransaccion['str_codigo_transaccion'] ?? '') !== ''
                ? $datosTransaccion['str_codigo_transaccion']
                : (($datosTransaccion['str_ticketID'] ?? '') !== '' ? $datosTransaccion['str_ticketID'] : $pago->gateway_transaction_id);
            $datos['gateway_transaction_id'] = $cusOTicket;
        }

        if (Schema::connection($connection)->hasColumn('pagos', 'int_id_forma_pago') && ! empty($datosTransaccion['int_id_forma_pago'])) {
            $datos['int_id_forma_pago'] = (int) $datosTransaccion['int_id_forma_pago'];
        }

        return $datos;
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
            // PSE
            $data['str_ticketID'] = trim($v[21] ?? '');
            $data['int_codigo_servicio'] = trim($v[22] ?? '');
            $data['int_codigo_banco'] = trim($v[23] ?? '');
            $data['str_nombre_banco'] = trim($v[24] ?? '');
            $data['str_codigo_transaccion'] = trim($v[25] ?? ''); // CUS
            $data['int_ciclo_transaccion'] = trim($v[26] ?? '');
        } elseif (in_array($data['int_id_forma_pago'], ['32', '51'], true)) {
            // Tarjeta de Crédito / Codensa
            $data['str_ticketID'] = trim($v[21] ?? '');
            $data['int_numero_tarjeta'] = trim($v[22] ?? '');
            $data['str_franquicia'] = trim($v[23] ?? '');
            $data['int_cod_aprobacion'] = trim($v[24] ?? '');
            $data['int_num_recibido'] = trim($v[25] ?? '');
        }

        return $data;
    }
}
