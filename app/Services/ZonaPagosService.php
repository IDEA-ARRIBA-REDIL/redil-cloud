<?php

namespace App\Services;

use App\Models\Pago;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio canónico para la integración con la pasarela ZonaPagos.
 *
 * Este es el ÚNICO servicio autorizado para comunicarse con ZonaPagos.
 * Usa el Laravel Http Client (Http::post) en lugar de cURL directo.
 *
 * Credenciales cargadas desde config/services.php → variables de entorno (.env):
 *   ZONAPAGOS_API_URL, ZONAPAGOS_ID_COMERCIO, ZONAPAGOS_USUARIO,
 *   ZONAPAGOS_CLAVE, ZONAPAGOS_CODIGO_SERVICIO
 */
class ZonaPagosService
{
    protected string $apiUrl;

    protected int $idComercio;

    protected string $usuario;

    protected string $clave;

    protected string $codigoServicio;

    public function __construct()
    {
        // Las credenciales nunca se hardcodean; vienen siempre del config/services.php
        $this->apiUrl = config('services.zonapagos.api_url');
        $this->idComercio = (int) config('services.zonapagos.id_comercio');
        $this->usuario = config('services.zonapagos.usuario');
        $this->clave = config('services.zonapagos.clave');
        $this->codigoServicio = config('services.zonapagos.codigo_servicio');
    }

    /**
     * Inicia un proceso de pago en ZonaPagos y retorna la URL de redirección.
     *
     * @param  Pago  $pago  Registro de pago ya creado en nuestra BD (su ID es el str_id_pago).
     * @param  array  $datosComprador  ['nombre', 'apellido', 'identificacion', 'email', 'telefono']
     * @param  string  $tipoCompra  Etiqueta que se guarda en str_opcional1 ('COMPRA GENERAL', 'ABONO', 'ESCUELAS')
     * @return array ['success' => bool, 'payment_url' => string|null, 'message' => string|null, 'gateway_response' => array|null]
     */
    public function iniciarPago(Pago $pago, array $datosComprador, string $tipoCompra = 'COMPRA GENERAL'): array
    {
        $compra = $pago->compra;

        if (! $compra) {
            return ['success' => false, 'message' => 'El pago no tiene una compra asociada.'];
        }

        // ─── str_descripcion_pago: límite estricto de 70 caracteres según manual ────
        $descripcionRaw = 'Pago '.$tipoCompra.' - '.$compra->actividad->nombre;
        $descripcion = substr($descripcionRaw, 0, 70);

        // ─── Separar nombre y apellido del comprador ─────────────────────────────────
        // El Checkout envía el nombre completo en 'nombre' y '' en 'apellido'.
        // Si apellido llega vacío, intentamos dividir el nombre completo.
        $nombreCompleto = trim($datosComprador['nombre']);
        $apellido = trim($datosComprador['apellido'] ?? '');

        if (empty($apellido) && str_word_count($nombreCompleto) > 1) {
            $partes = explode(' ', $nombreCompleto, 2);
            $nombre = $partes[0];
            $apellido = $partes[1];
        } else {
            $nombre = $nombreCompleto;
            // Fallback requerido por la API: nunca enviar apellido vacío
            $apellido = $apellido ?: '.';
        }

        // ─── Límites de tamaño según manual ─────────────────────────────────────────
        // str_nombre_cliente: max 50 chars | str_apellido_cliente: max 50 chars
        $nombre = substr($nombre, 0, 50);
        $apellido = substr($apellido, 0, 50);
        $email = substr($datosComprador['email'] ?? '', 0, 70);
        $identificacion = substr($datosComprador['identificacion'] ?? '', 0, 30);
        $telefono = $datosComprador['telefono'] ?: '0000000';

        // ─── Payload para la API de ZonaPagos ────────────────────────────────────────
        $payload = [
            'InformacionPago' => [
                'flt_total_con_iva' => (float) $pago->valor,
                'flt_valor_iva' => 0,
                // str_id_pago: identificador único de nuestra transacción (max 30 chars)
                'str_id_pago' => (string) $pago->id,
                'str_descripcion_pago' => $descripcion,
                'str_email' => $email,
                'str_id_cliente' => $identificacion,
                'str_tipo_id' => '1', // 1 = CC Cédula de Ciudadanía
                'str_nombre_cliente' => $nombre,
                'str_apellido_cliente' => $apellido,
                'str_telefono_cliente' => (string) $telefono,
                // str_opcional1 guarda el tipo de compra para que la Sonda diferencie
                // entre inscripciones normales, abonos y matrículas de Escuelas.
                'str_opcional1' => $tipoCompra,
                'str_opcional2' => '',
                'str_opcional3' => '',
                'str_opcional4' => '',
                'str_opcional5' => '',
            ],
            'InformacionSeguridad' => [
                'int_id_comercio' => $this->idComercio, // CORRECCIÓN: entero, no string
                'str_usuario' => $this->usuario,
                'str_clave' => $this->clave,
                // CORRECCIÓN CRÍTICA: el manual exige siempre -1, NO 0 ni 1.
                'int_modalidad' => -1,
            ],
            'AdicionalesConfiguracion' => [
                [
                    // Código 50: código de servicio PSE (obligatorio para PSE)
                    'int_codigo' => 50,
                    'str_valor' => (string) $this->codigoServicio,
                ],
                [
                    // Código 104: URL de retorno personalizada para el cliente
                    // ZonaPagos redirige aquí cuando el usuario quiere "volver al comercio"
                    'int_codigo' => 104,
                    'str_valor' => route('zonapagos.handleCallback'),
                ],
            ],
        ];

        // Registramos el payload en log (ocultando la clave por seguridad)
        $payloadLog = $payload;
        $payloadLog['InformacionSeguridad']['str_clave'] = '********';
        Log::info('ZonaPagosService::iniciarPago — Enviando petición:', $payloadLog);

        try {
            $response = Http::timeout(30)->post($this->apiUrl.'/InicioPago', $payload);

            // La API retorna int_codigo = 1 cuando el inicio de pago fue exitoso
            if ($response->successful() && $response->json('int_codigo') === 1) {
                $paymentUrl = $response->json('str_url');

                // La URL puede venir completa. Si no, le agregamos la base.
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

            // La API retornó un error; logueamos la respuesta completa para diagnóstico
            $errorDesc = $response->json('str_descripcion_error', 'Error desconocido de la pasarela.');
            Log::error("ZonaPagosService::iniciarPago — Error API para Pago ID {$pago->id}:", $response->json() ?? []);

            return [
                'success' => false,
                'message' => $errorDesc,
                'response' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('ZonaPagosService::iniciarPago — Excepción: '.$e->getMessage());

            return ['success' => false, 'message' => 'Ocurrió un error inesperado al conectar con la pasarela.'];
        }
    }

    /**
     * Consulta el estado actual de un pago en ZonaPagos.
     *
     * Usada por la Sonda (cron) y el Controller de callback para verificar el estado real.
     *
     * @return array ['success' => bool, 'data' => array|null, 'message' => string|null]
     */
    public function verificarPago(Pago $pago): array
    {
        $payload = [
            'int_id_comercio' => $this->idComercio,
            'str_usr_comercio' => $this->usuario,
            'str_pwd_comercio' => $this->clave,
            // str_id_pago: el mismo ID que enviamos en iniciarPago
            'str_id_pago' => (string) $pago->id,
            // int_no_pago: -1 indica que no filtramos por número de pago específico
            'int_no_pago' => -1,
        ];

        Log::info("ZonaPagosService::verificarPago — Consultando Pago ID {$pago->id}.");

        try {
            $response = Http::timeout(30)->post($this->apiUrl.'/VerificacionPago', $payload);

            // int_error = 0 significa que se encontraron pagos (éxito de la consulta)
            if ($response->successful() && $response->json('int_error') === 0) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            $detalle = $response->json('str_detalle', 'Sin detalle.');
            Log::warning("ZonaPagosService::verificarPago — No se encontró el Pago ID {$pago->id}. Detalle: {$detalle}");

            return [
                'success' => false,
                'message' => 'La transacción no fue encontrada o hubo un error: '.$detalle,
                'response' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('ZonaPagosService::verificarPago — Excepción: '.$e->getMessage());

            return ['success' => false, 'message' => 'Ocurrió un error inesperado al verificar el pago.'];
        }
    }

    /**
     * Parsea el campo str_res_pago de la respuesta de verificación.
     *
     * El campo str_res_pago puede contener MÚLTIPLES transacciones separadas por '|;|'
     * (ejemplo: pago fraccionado en PSE + TC). Cada transacción tiene sus campos
     * separados por '|'. Este método retorna la ÚLTIMA transacción (la definitiva).
     *
     * Mapeo basado en Tabla 12 del manual ZonaPagos I-TI-008 v5.0
     *
     * @param  string  $strResPago  El string crudo del campo str_res_pago
     */
    public function parsearRespuestaVerificacion(string $strResPago): array
    {
        if (empty(trim($strResPago))) {
            return [];
        }

        // CORRECCIÓN: primero dividir por el separador de múltiples transacciones '|;|'
        // para aislar cada transacción individual. Luego tomamos la última.
        $transacciones = explode('|;|', $strResPago);
        $ultimaTransaccionStr = trim(end($transacciones));

        if (empty($ultimaTransaccionStr)) {
            return [];
        }

        // Ahora sí dividimos por '|' para obtener los campos de esa transacción
        $v = explode('|', $ultimaTransaccionStr);

        // Mapeo posicional exacto según Tabla 12 del manual
        $data = [
            'int_ped_numero' => trim($v[0] ?? ''),   // Número de pedido ZonaPagos
            'int_n_pago' => trim($v[1] ?? ''),   // Número de transacción
            'int_pago_parcial' => trim($v[2] ?? ''),   // ¿Pago parcial?
            'int_pago_terminado' => trim($v[3] ?? ''),   // 200=iniciado, 1=terminado, 2=pendiente mixto
            'int_estado_pago' => trim($v[4] ?? ''),   // ← ESTADO CLAVE: 1, 999, 1000, 1001, 4000, 4001, 4003
            'dbl_valor_pagado' => trim($v[5] ?? ''),   // Valor pagado
            'dbl_total_pago' => trim($v[6] ?? ''),   // Total enviado por el comercio
            'dbl_valor_iva_pagado' => trim($v[7] ?? ''),  // IVA pagado
            'str_descripcion' => trim($v[8] ?? ''),   // Concepto del pago
            'str_id_cliente' => trim($v[9] ?? ''),   // Identificación del cliente
            'str_nombre' => trim($v[10] ?? ''),  // Nombre del cliente
            'str_apellido' => trim($v[11] ?? ''),  // Apellido del cliente
            'str_telefono' => trim($v[12] ?? ''),  // Teléfono del cliente
            'str_email' => trim($v[13] ?? ''),  // Email del cliente
            'str_campo1' => trim($v[14] ?? ''),  // str_opcional1 enviado al inicio → tipoCompra
            'str_campo2' => trim($v[15] ?? ''),  // str_opcional2
            'str_campo3' => trim($v[16] ?? ''),  // str_opcional3
            'str_campo4' => trim($v[17] ?? ''),  // str_opcional4
            'str_campo5' => trim($v[18] ?? ''),  // str_opcional5
            'dat_fecha' => trim($v[19] ?? ''),  // Fecha de la transacción
            'int_id_forma_pago' => trim($v[20] ?? ''),  // Medio de pago: 29=PSE, 32=TC, 48=Bancolombia...
        ];

        // Campos adicionales según el medio de pago
        if ($data['int_id_forma_pago'] === '29') {
            // PSE
            $data['str_ticketID'] = trim($v[21] ?? '');
            $data['int_codigo_servicio'] = trim($v[22] ?? '');
            $data['int_codigo_banco'] = trim($v[23] ?? '');
            $data['str_nombre_banco'] = trim($v[24] ?? '');
            $data['str_codigo_transaccion'] = trim($v[25] ?? ''); // CUS
            $data['int_ciclo_transaccion'] = trim($v[26] ?? '');
        } elseif (in_array($data['int_id_forma_pago'], ['32', '51'])) {
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
