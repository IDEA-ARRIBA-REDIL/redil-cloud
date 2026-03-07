<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Compra;
use App\Models\Pago;

class ZonaPagoService
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

    public function iniciarPago(Pago $pago, array $datosComprador, string $tipoCompra = 'COMPRA GENERAL'): array
    {
        $compra = $pago->compra;
        if (!$compra) {
            return ['success' => false, 'message' => 'El pago no tiene una compra asociada.'];
        }
        $idPagoComercio = $pago->id;

        $descripcion = "Pago {$tipoCompra} - Actividad: " . $compra->actividad->nombre;
        // Limitar la descripción a lo que permita la pasarela si es necesario
        $descripcion = substr($descripcion, 0, 250);

        // Intentar separar el nombre del apellido si el apellido viene vacío
        $nombre = $datosComprador['nombre'];
        $apellido = $datosComprador['apellido'] ?: '.'; // Fallback a punto si está vacío

        $data = [
            "InformacionPago" => [
                "flt_total_con_iva" => $compra->valor,
                "flt_valor_iva" => 0,
                "str_id_pago" => (string) $idPagoComercio,
                "str_descripcion_pago" => $descripcion,
                "str_email" => $datosComprador['email'],
                "str_id_cliente" => $datosComprador['identificacion'],
                "str_tipo_id" => "1", // 1 suele ser Cédula de Ciudadanía
                "str_nombre_cliente" => $nombre,
                "str_apellido_cliente" => $apellido,
                "str_telefono_cliente" => $datosComprador['telefono'] ?: '0000000'
            ],
            "InformacionSeguridad" => [
                "int_id_comercio" => (int) $this->idComercio,
                "str_usuario" => $this->usuario,
                "str_clave" => $this->clave,
                "int_modalidad" => 1 // 1 para Pruebas, 0 para Producción
            ],
            "AdicionalesConfiguracion" => [
                ["int_codigo" => 50, "str_valor" => (string) $this->codigoServicio]
            ]
        ];

        // Log del payload (sin la clave por seguridad)
        $dataLog = $data;
        $dataLog['InformacionSeguridad']['str_clave'] = '********';
        Log::info('Enviando petición a ZonaPagos:', $dataLog);

        try {
            $response = Http::post($this->apiUrl . '/InicioPago', $data);

            // CAMBIO: Se ajusta la validación a los parámetros del manual v4.0
            if ($response->successful() && $response->json('int_codigo') === 1) {
                // CAMBIO: La URL ahora puede venir completa o parcial. Se recomienda usar la URL completa del ejemplo.
                $baseUrl = "https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=";
                $paymentUrl = $response->json('str_url');
                
                // Si la URL ya viene completa, no se concatena.
                if (!filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
                    $paymentUrl = $baseUrl . $paymentUrl;
                }

                return [
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'gateway_response' => $response->json()
                ];
            }

            Log::error('Error API ZonaPagos InicioPago: ', $response->json());
            $errorMessage = $response->json('str_descripcion_error', 'Error al comunicarse con la pasarela de pago.');
            return ['success' => false, 'message' => $errorMessage, 'response' => $response->json()];

        } catch (\Exception $e) {
            Log::error('Excepción en ZonaPagosService::iniciarPago: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ocurrió un error inesperado.'];
        }
    }

    public function verificarPago(\App\Models\Pago $pago): array
    {
        $data = [
            'int_id_comercio' => (int) $this->idComercio,
            'str_usr_comercio' => $this->usuario,
            'str_pwd_comercio' => $this->clave,
            'str_id_pago' => (string) $pago->id, // El ID de nuestra tabla 'pagos' 
            'int_no_pago' => -1, // Enviar -1 si no se usa 
        ];

        try {
            // La ruta para la verificación es /VerificacionPago 
            $response = Http::post($this->apiUrl . '/VerificacionPago', $data);

            if ($response->successful() && $response->json('int_error') === 0) {
                // El resultado viene en un string separado por '|' 
                $resultadoString = $response->json('str_res_pago');
                $parsedData = $this->parseResultadoPago($resultadoString);

                return [
                    'success' => true,
                    'data' => $parsedData,
                    'raw_response' => $response->json()
                ];
            }

            return ['success' => false, 'message' => 'La transacción no fue encontrada o hubo un error.'];

        } catch (\Exception $e) {
            Log::error('Excepción en ZonaPagosService::verificarPago: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ocurrió un error inesperado al verificar el pago.'];
        }
    }

    /**
     * Helper para parsear la respuesta de verificación de pago.
     * @param string $resultadoString
     * @return array
     */
    private function parseResultadoPago(string $resultadoString): array
    {
        // La documentación indica que los campos están separados por '|' 
        $valores = explode('|', $resultadoString);
        
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
