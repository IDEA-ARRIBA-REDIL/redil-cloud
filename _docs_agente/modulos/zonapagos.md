# Módulo de ZonaPagos y Pasarela PSE

Documentación técnica completa del módulo de integración con **ZonaPagos (v5.0 REST API)** para REDIL Cloud.

---

## Estructura y Archivos Principales

1. **Servicio Canónico**: [`app/Services/ZonaPagosService.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Services/ZonaPagosService.php)
2. **Controlador Callback**: [`app/Http/Controllers/ZonaPagosController.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Http/Controllers/ZonaPagosController.php)
3. **Sonda de Verificación (Cron Job)**: [`app/Console/Commands/VerificarPagosPendientes.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Console/Commands/VerificarPagosPendientes.php)
4. **Checkout Livewire**: [`app/Livewire/Carrito/Checkout.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Livewire/Carrito/Checkout.php)
5. **Seeder de Configuración**: [`database/seeders/TipoPagoSeeder.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/database/seeders/TipoPagoSeeder.php)
6. **Seeder de Estados**: [`database/seeders/EstadoPagoSeeder.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/database/seeders/EstadoPagoSeeder.php)
7. **Documentación del Agente**: [agenteZonaPagosPSE.md](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/.agent/workflows/agenteZonaPagosPSE.md)

---

## Parámetros de Integración API ZonaPagos REST v5.0

```json
{
  "InformacionPago": {
    "flt_total_con_iva": 50000.0,
    "flt_valor_iva": 0,
    "str_id_pago": "123",
    "str_descripcion_pago": "Pago COMPRA GENERAL - Conferencia 2026",
    "str_email": "comprador@ejemplo.com",
    "str_id_cliente": "1018223344",
    "str_tipo_id": "1",
    "str_nombre_cliente": "Juan",
    "str_apellido_cliente": "Pérez",
    "str_telefono_cliente": "3001234567",
    "str_opcional1": "COMPRA GENERAL"
  },
  "InformacionSeguridad": {
    "int_id_comercio": 34741,
    "str_usuario": "MANANTIAL",
    "str_clave": "********",
    "int_modalidad": -1
  },
  "AdicionalesConfiguracion": [
    { "int_codigo": 50, "str_valor": "2701" },
    { "int_codigo": 104, "str_valor": "https://dominio.com/pagos/zonapagos/callback" }
  ]
}
```

---

## Flujo de Estados de Transacción

1. **Iniciación**: El pago se crea con `estado_pago_id = 5` (`id_codigo_externo = 999` - Pendiente).
2. **Redirección**: El usuario es enviado al portal PSE / Tarjetas de ZonaPagos.
3. **Respuesta inmediata (Callback)**: ZonaPagos redirige a `zonapagos.handleCallback` donde se verifica con la API y se actualizan estados.
4. **Respuesta diferida (Sonda)**: Si la transacción quedó en proceso o no regresó el usuario, el comando `pagos:verificar-zonapagos` consulta las transacciones con antigüedad > 7 minutos.
5. **Aprobación en Cascada**: Al llegar a estado aprobado (`id_codigo_externo = 1`), se marca la compra como `PAGADA (estado = 3)` y se activan inscripciones o la matrícula de Escuelas (`$pago->matricula`).
