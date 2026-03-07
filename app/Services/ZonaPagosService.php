<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Compra;
use App\Models\Pago;

class ZonaPagosService
{
    protected $apiUrl;
    protected $idComercio;
    protected $usuario;
    protected $clave;
    protected $codigoServicio;

    public function __construct()
    {
        $this->apiUrl = config('services.zonapagos.api_url');
        $this->idComercio = config('services.zonapagos.id_comercio');
        $this->usuario = config('services.zonapagos.usuario');
        $this->clave = config('services.zonapagos.clave');
        $this->codigoServicio = config('services.zonapagos.codigo_servicio');
    }

    /**
     * Inicia un proceso de pago.
     *
     * @param Pago $pago El objeto Pago que se va a procesar.
     * @param array $datosComprador Datos del comprador.
     * @return array
     */
    // 2. CORRECCIÓN: Se cambia el tipo del primer argumento de Compra a Pago.
    // CAMBIO N°3: Se añade el parámetro $tipoCompra a la firma del método.
    public function iniciarPago(Pago $pago, array $datosComprador, string $tipoCompra): array
    {
        $compra = $pago->compra;

        // Se construye el string JSON
        $jsonString = ' {
                        "InformacionPago": {
                            "flt_total_con_iva": ' . $pago->valor . ',
                            "flt_valor_iva": 0.00,
                            "str_id_pago": "' . (string)$pago->id . '",
                            "str_descripcion_pago": "Pago Actividad:' . $compra->actividad->nombre . '",
                            "str_email": "' . $datosComprador['email'] . '",
                            "str_id_cliente": "' . $datosComprador['identificacion'] . '",
                            "str_tipo_id": "1",
                            "str_nombre_cliente": "' . $datosComprador['nombre'] . '",
                            "str_apellido_cliente": "' . ($datosComprador['apellido'] ?? ' ') . '",
                            "str_telefono_cliente": "' . $datosComprador['telefono'] . '",
                            
                            "str_opcional1": "' . $tipoCompra . '",
                            "str_opcional2": "",
                            "str_opcional3": "",
                            "str_opcional4": "",
                            "str_opcional5": ""
                        },
                        "InformacionSeguridad": {
                            "int_id_comercio" : "' . $this->idComercio . '",
                            "str_usuario" : "' . $this->usuario . '",
                            "str_clave" : "' . $this->clave . '",
                            "int_modalidad" : 1
                        },
                        "AdicionalesConfiguracion": [
                            {
                            "int_codigo": 50,
                            "str_valor": "' . $this->codigoServicio . '"
                            },
                            {
                            "int_codigo": 104,
                            "str_valor": "' . route('zonapagos.handleCallback') . '"
                            }
                        ]
        }';

        // ... (El resto del método con la lógica de cURL se mantiene igual) ...
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiUrl . '/InicioPago',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonString,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

            $responseBody = curl_exec($ch);
            // ... (resto de la lógica)
            curl_close($ch);
            $responseJson = json_decode($responseBody, true);
            if ($responseJson['int_codigo'] === 1) {
                return [
                    'success' => true,
                    'payment_url' => $responseJson['str_url'],
                    'gateway_response' => $responseJson
                ];
            }
            return ['success' => false, 'message' => 'Error de pasarela'];
        } catch (\Exception $e) {
            Log::error('Excepción en ZonaPagosService::iniciarPago (cURL): ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ocurrió un error inesperado al conectar con el banco.'];
        }
    }

    public function verificarPago(Pago $pago): array
    {
        // El manual indica que la verificación se hace con un JSON que contiene las credenciales y el id del pago.
        $data = [
            'int_id_comercio' => (int)$this->idComercio,
            'str_usr_comercio' => $this->usuario,
            'str_pwd_comercio' => $this->clave,
            'str_id_pago' => (string)$pago->id,
            'int_no_pago' => -1, // Como indica el manual, si no se usa, se envía -1.
        ];

        $jsonString = json_encode($data);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiUrl . '/VerificacionPago',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonString,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

            $responseBody = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \Exception("cURL Error: " . $curlError);
            }

            $responseJson = json_decode($responseBody, true);

            // La respuesta exitosa de verificación tiene int_error = 0
            if ($httpStatus == 200 && isset($responseJson['int_error']) && $responseJson['int_error'] === 0) {
                return [
                    'success' => true,
                    'data' => $responseJson,
                ];
            }

            return ['success' => false, 'message' => 'La transacción no fue encontrada o hubo un error.', 'response' => $responseJson];
        } catch (\Exception $e) {
            Log::error('Excepción en ZonaPagosService::verificarPago (cURL): ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ocurrió un error inesperado al verificar el pago.'];
        }
    }

    /**
     * Helper para parsear la respuesta de verificación de pago.
     * @param string $resultadoString
     * @return array
     */
    public function parsearRespuestaVerificacion(string $strResPago): array
    {
        // La respuesta puede contener múltiples transacciones separadas por '|;|'.
        // Nos interesa la última, que suele ser la definitiva.
        $transacciones = explode('|;|', $strResPago);
        $ultimaTransaccionStr = trim(end($transacciones));

        if (empty($ultimaTransaccionStr)) {
            return [];
        }

        $valores = explode('|', $ultimaTransaccionStr);

        // Mapeo basado en la Tabla 12 del instructivo I-TI-008
        $data = [
            'int_ped_numero' => $valores[0] ?? null,
            'int_n_pago' => $valores[1] ?? null,
            'int_pago_parcial' => $valores[2] ?? null,
            'int_pago_terminado' => $valores[3] ?? null,
            'int_estado_pago' => $valores[4] ?? null, // Este es el estado que nos importa
            'dbl_valor_pagado' => $valores[5] ?? null,
            'dbl_total_pago' => $valores[6] ?? null,
            'dbl_valor_iva_pagado' => $valores[7] ?? null,
            'str_descripcion' => $valores[8] ?? null,
            'str_id_cliente' => $valores[9] ?? null,
            'str_nombre' => $valores[10] ?? null,
            'str_apellido' => $valores[11] ?? null,
            'str_telefono' => $valores[12] ?? null,
            'str_email' => $valores[13] ?? null,
            'str_campo1' => $valores[14] ?? null, // str_opcional1
            'str_campo2' => $valores[15] ?? null, // str_opcional2
            'str_campo3' => $valores[16] ?? null, // str_opcional3
            'str_campo4' => $valores[17] ?? null, // str_opcional4
            'str_campo5' => $valores[18] ?? null, // str_opcional5
            'dat_fecha' => $valores[19] ?? null,
            'int_id_forma_pago' => $valores[20] ?? null,
        ];

        // Añadir campos específicos del medio de pago si aplica (ej. PSE)
        if (($data['int_id_forma_pago'] ?? '') == '29') { // 29 es PSE
            $data['str_ticketID'] = $valores[21] ?? null;
            $data['int_codigo_servicio'] = $valores[22] ?? null;
            $data['int_codigo_banco'] = $valores[23] ?? null;
            $data['str_nombre_banco'] = $valores[24] ?? null;
            $data['str_codigo_transaccion'] = $valores[25] ?? null; // CUS
            $data['int_ciclo_transaccion'] = $valores[26] ?? null;
        }

        return $data;
    }
}
