# Módulo de ZonaPagos y Pasarela PSE (Producción v5.0)

Documentación técnica del módulo de integración con **ZonaPagos (v5.0 REST API)** en REDIL Cloud, alineada con `https://docs.zonavirtual.com/docs`.

---

## 1. Archivos del Módulo

1. **Servicio Canónico**: [`app/Services/ZonaPagosService.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Services/ZonaPagosService.php)
2. **Motor Financiero Transaccional**: [`app/Services/EstadoFinancieroService.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Services/EstadoFinancieroService.php)
3. **Controlador Callback y Retorno**: [`app/Http/Controllers/ZonaPagosController.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Http/Controllers/ZonaPagosController.php)
4. **Sonda de Verificación (Cron Job)**: [`app/Console/Commands/VerificarPagosPendientes.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Console/Commands/VerificarPagosPendientes.php)
5. **Programación del Scheduler**: [`routes/console.php`](file:///Users/m/Desktop/REDIL-CLOUD/routes/console.php) (`everyFiveMinutes()->withoutOverlapping()`)
6. **Flujo Escuelas**: [`app/Livewire/Carrito/EscuelasCarrito.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Livewire/Carrito/EscuelasCarrito.php) y [`app/Livewire/Carrito/Checkout.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Livewire/Carrito/Checkout.php)
7. **Seeders**: [`database/seeders/TipoPagoSeeder.php`](file:///Users/m/Desktop/REDIL-CLOUD/database/seeders/TipoPagoSeeder.php) y [`database/seeders/EstadoPagoSeeder.php`](file:///Users/m/Desktop/REDIL-CLOUD/database/seeders/EstadoPagoSeeder.php)
8. **Suite de Pruebas PHPUnit**: [`tests/Feature/ZonaPagosEscuelasSondaTest.php`](file:///Users/m/Desktop/REDIL-CLOUD/tests/Feature/ZonaPagosEscuelasSondaTest.php)
9. **Workflow del Agente**: [`.agent/workflows/agenteZonaPagosPSE.md`](file:///Users/m/Desktop/REDIL-CLOUD/.agent/workflows/agenteZonaPagosPSE.md)

---

## 2. Resolución de Credenciales y Código de Servicio `1001`

`ZonaPagosService::obtenerCredenciales()` resuelve las credenciales en orden de prioridad:
1. **Tabla `tipos_pago` del tenant** (`key_reservada = 'zona'`, `activo = true`):
   - `enlace` → URL API base (`https://www.zonapagos.com/Apis_CicloPago/api`)
   - `client_id` → `int_id_comercio`
   - `bussines_id` → `str_usuario` / `str_usr_comercio`
   - `key_id` → `str_clave` / `str_pwd_comercio`
   - `account_id` → `codigo_servicio` (`1001` en producción)
2. **Fallback `.env` (`config/services.php` → `services.zonapagos`)**:
   - `ZONAPAGOS_API_URL`, `ZONAPAGOS_ID_COMERCIO`, `ZONAPAGOS_USUARIO`, `ZONAPAGOS_CLAVE`, `ZONAPAGOS_CODIGO_SERVICIO=1001`
   - `ZONAPAGOS_MINUTOS_ABANDONO_PASARELA=45`
   - `ZONAPAGOS_MINUTOS_ABANDONO_BORRADOR=30`

---

## 3. Contrato de Petición `POST /InicioPago` (v5.0)

```json
{
  "InformacionPago": {
    "flt_total_con_iva": 150000.0,
    "flt_valor_iva": 0,
    "str_id_pago": "P-123",
    "str_descripcion_pago": "Pago ESCUELAS - Fundamentos I",
    "str_email": "comprador@ejemplo.com",
    "str_id_cliente": "1018223344",
    "str_tipo_id": "1",
    "str_nombre_cliente": "María",
    "str_apellido_cliente": "Rodríguez",
    "str_telefono_cliente": "3001234567",
    "str_opcional1": "ESCUELAS",
    "str_opcional2": "45",
    "str_opcional3": "",
    "str_opcional4": "",
    "str_opcional5": ""
  },
  "InformacionSeguridad": {
    "int_id_comercio": 0,
    "str_usuario": "<USUARIO>",
    "str_clave": "********",
    "int_modalidad": -1
  },
  "AdicionalesPago": [],
  "AdicionalesConfiguracion": [
    { "int_codigo": 50, "str_valor": "1001" },
    { "int_codigo": 104, "str_valor": "https://tenant.dominio.com/pagos/zonapagos/retorno" }
  ]
}
```

---

## 4. Ciclo de Estados y Política de la Sonda

1. **Borrador en Escuelas (`EscuelasCarrito`)**:
   - Crea `Compra` (`PENDIENTE`), `Pago` borrador (`tipo_pago_id = null`, `estado_pago_id = null`) y `Matricula` con `compra_id` y `pago_id` explícitos.
   - Descuenta 1 cupo en `horarios_materia_periodo.cupos_disponibles`.
   - **No** promueve `tipo_usuario_inicial_id` hasta que el pago sea confirmado.
   - Si el usuario nunca hace clic en Pagar y pasan **30 minutos**, la Sonda anula la compra, hace soft-delete de la matrícula y restaura el cupo (+1).
2. **Envío a Pasarela (`Checkout::procesarPagoZonaPagos`)**:
   - Asigna `tipo_pago_id` (ZonaPagos) y estado inicial `999` (`id = 5`).
   - Cada reintento después de un rechazo/abandono crea un **nuevo `Pago`** (`P-{nuevo_id}` único).
3. **Verificación (`ZonaPagosService::sincronizarPagoDesdePasarela`)**:
   - Usada de forma unificada por **Callback**, **Retorno de Usuario**, **Polling (`wire:poll.4s`)** y **Sonda cada 5 minutos**.
   - `int_estado_pago === '1'`: marca `Compra = PAGADA (3)`, `Matricula = 'pagada'` (`valor_pagado`, `fecha_pago`), guarda CUS/forma de pago y ejecuta `aplicarEfectosMatricula()` (promoción de tipo de usuario + pasos de crecimiento al iniciar).
   - `1000`, `1001`, `4000`, `4003`: marca rechazo, anula `Compra`, hace soft-delete de `Matricula`, borra `matricula_horario_materia_periodo` y restaura `cupos_disponibles` (+1).
   - `888`, `999`, `200` o `no_encontrado`: **protege la matrícula durante 45 minutos**; al vencer, marca **Transacción Abandonada (`id = 16`)**, libera matrícula y restaura `cupos_disponibles` (+1).
   - `4001` (Pendiente CR en tarjeta): no se vence a los 45 min (espera resolución de franquicia).
   - Error de red / HTTP 5xx: nunca cancela ni borra matrículas; reintenta en el siguiente ciclo.

---

## 5. Checklist de Verificación y Paso a Producción

1. Eliminar el archivo obsoleto duplicado `app/Console/VerificarPagosPendientes.php`.
2. Configurar credenciales de producción y `account_id = 1001` en `tipos_pago` del tenant (y/o `ZONAPAGOS_CODIGO_SERVICIO=1001` en `.env`).
3. Confirmar URLs registradas en ZonaPagos (panel comercio):
   - Callback (asentamiento): `https://<dominio-tenant>/pagos/zonapagos/callback`
   - Retorno visual (`int_codigo = 104`): enviado dinámicamente como `https://<dominio-tenant>/pagos/zonapagos/retorno`
4. Confirmar cron activo en servidor: `* * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1` y validar con `php artisan schedule:list`.
5. Ejecutar pruebas automatizadas:
   ```bash
   php artisan test --compact tests/Feature/ZonaPagosEscuelasSondaTest.php tests/Feature/DonacionesOnlineTest.php
   ```
6. En local, probar abandono rápido con:
   ```bash
   php artisan pagos:verificar-zonapagos --minutos-pasarela=1 --minutos-borrador=1
   ```
