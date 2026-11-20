---
description: Carga el contexto, arquitectura y memoria del Agente de ZonaPagos y Pasarela PSE para REDIL Cloud.
---

# Agente de ZonaPagos y Pasarela PSE (Integración Transaccional — Producción v5.0)

Este documento contiene la especificación técnica completa, arquitectura, mapa de código, flujo de datos, política de la Sonda y pruebas verificadas para la integración con **ZonaPagos (v5.0 REST API)** en REDIL Cloud (Fase 1 Producción: **Escuelas**, código de servicio `1001`).

Referencia oficial de la pasarela: `https://docs.zonavirtual.com/docs`

---

## 1. Contexto e Instrucciones del Agente

Cuando este agente se activa:
1. Lee [`app/Services/ZonaPagosService.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Services/ZonaPagosService.php) y [`app/Services/EstadoFinancieroService.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Services/EstadoFinancieroService.php).
2. Lee [`app/Http/Controllers/ZonaPagosController.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Http/Controllers/ZonaPagosController.php) para el flujo de callback y retorno de usuario.
3. Lee [`app/Console/Commands/VerificarPagosPendientes.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Console/Commands/VerificarPagosPendientes.php) y [`routes/console.php`](file:///Users/m/Desktop/REDIL-CLOUD/routes/console.php) para la Sonda (Scheduler Laravel 11/12).
4. Lee [`app/Livewire/Carrito/EscuelasCarrito.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Livewire/Carrito/EscuelasCarrito.php) y [`app/Livewire/Carrito/Checkout.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Livewire/Carrito/Checkout.php) para el flujo completo de Escuelas.
5. Adopta la persona: *"Experto en Pasarelas de Pago, Integración ZonaPagos REST y Flujos Transaccionales Multi-Tenant"*.
6. Confirma al usuario: *"💳 **Agente ZonaPagos & PSE Activado**. Conozco el flujo transaccional completo, la Sonda de verificación, el callback de retorno y la gestión de estados."*

---

## 2. Mapa de Archivos del Módulo

| Archivo | Responsabilidad / Función | Estado |
|---------|---------------------------|--------|
| [`app/Services/ZonaPagosService.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Services/ZonaPagosService.php) | **Servicio Canónico Unificado**: credenciales por tenant (`tipos_pago`) con fallback a `.env`, `iniciarPago`, `verificarPago`, `parsearRespuestaVerificacion` y `sincronizarPagoDesdePasarela`. | ✅ Producción v5.0 |
| [`app/Services/EstadoFinancieroService.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Services/EstadoFinancieroService.php) | Motor financiero atómico con `lockForUpdate()`: aplica estados, reconcilia `Compra`/`Matricula`/`Inscripcion`, restaura `cupos_disponibles` y limpia reservas fallidas/expiradas. | ✅ Actualizado |
| [`app/Http/Controllers/ZonaPagosController.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Http/Controllers/ZonaPagosController.php) | Procesa Callback GET (`/pagos/zonapagos/callback`) y Retorno de Usuario (`/pagos/zonapagos/retorno`) verificando SIEMPRE contra `VerificacionPago`. | ✅ Unificado |
| [`app/Console/Commands/VerificarPagosPendientes.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Console/Commands/VerificarPagosPendientes.php) | Sonda multi-tenant (`pagos:verificar-zonapagos`). Verifica pendientes en pasarela, vence abandonados (45 min) y libera borradores expirados (30 min) restaurando cupos. | ✅ Endurecido |
| [`routes/console.php`](file:///Users/m/Desktop/REDIL-CLOUD/routes/console.php) | Scheduler activo de Laravel 11/12: ejecuta `pagos:verificar-zonapagos` cada 5 minutos (`->everyFiveMinutes()->withoutOverlapping()`). | ✅ Activo |
| [`app/Livewire/Carrito/EscuelasCarrito.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Livewire/Carrito/EscuelasCarrito.php) | Crea borrador con FKs explícitas (`compra_id`, `pago_id`), difiere `tipo_usuario_inicial_id` hasta confirmación del pago y protege pagos PSE activos. | ✅ Corregido |
| [`app/Livewire/Carrito/Checkout.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Livewire/Carrito/Checkout.php) | Genera nuevo `Pago` en reintentos (`P-{nuevo_id}` único), evita duplicar pagos activos y delega el polling (`wire:poll.4s`) al servicio canónico. | ✅ Corregido |
| [`app/Http/Controllers/CarritoController.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Http/Controllers/CarritoController.php) | `compraFinalizada`: envía correo de confirmación únicamente cuando el pago está aprobado (`estado_final_inscripcion = true`) y una sola vez. | ✅ Corregido |
| [`app/Http/Controllers/ActividadController.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Http/Controllers/ActividadController.php) | `perfil`: busca matrícula asociada estrictamente a la compra actual (`compra_id` / `pago_id` / `referencia_pago`). | ✅ Corregido |
| [`config/services.php`](file:///Users/m/Desktop/REDIL-CLOUD/config/services.php) | Configuración fallback `services.zonapagos` + ventanas `minutos_abandono_pasarela` (45) y `minutos_abandono_borrador` (30). | ✅ Configurado |
| [`database/seeders/EstadoPagoSeeder.php`](file:///Users/m/Desktop/REDIL-CLOUD/database/seeders/EstadoPagoSeeder.php) | Mapeo de códigos `int_estado_pago` (v5.0) a `estados_pago`, incluyendo ID `16` (Transacción Abandonada) y `21` (Error ACH `1001`). | ✅ Alineado v5.0 |
| [`tests/Feature/ZonaPagosEscuelasSondaTest.php`](file:///Users/m/Desktop/REDIL-CLOUD/tests/Feature/ZonaPagosEscuelasSondaTest.php) | Suite PHPUnit (17 pruebas) cubriendo producción `1001`, Sonda, protección de pagos PSE activos, abandonos, cupos, reintentos y scheduler. | ✅ Verificado |
| [`app/Services/ZonaPagosOfrendaService.php`](file:///Users/m/Desktop/REDIL-CLOUD/app/Services/ZonaPagosOfrendaService.php) | Servicio independiente para Donaciones (`O-{id}`). Ver [`.agent/workflows/agenteDonaciones.md`](file:///Users/m/Desktop/REDIL-CLOUD/.agent/workflows/agenteDonaciones.md). | ✅ Activo |

---

## 3. Correcciones Críticas Aplicadas para Paso a Producción (2026-11-20)

### 🔴 1. Activación real de la Sonda en Laravel 11/12 (`routes/console.php`)
- **Causa raíz del incidente en producción:** La aplicación arranca desde `bootstrap/app.php` cargando el scheduler de `routes/console.php` (no `app/Console/Kernel.php`), donde `pagos:verificar-zonapagos` estaba comentado.
- **Solución:** Se activó `Schedule::command('pagos:verificar-zonapagos')->everyFiveMinutes()->withoutOverlapping();` en `routes/console.php` (frecuencia recomendada por ZonaPagos/ACH: 5–10 min).
- **Limpieza pendiente manual:** Eliminar el archivo duplicado obsoleto `app/Console/VerificarPagosPendientes.php` en local y servidores.

### 🔴 2. Eliminación del borrado indiscriminado en la Sonda
- **Riesgo corregido:** Antes, `limpiarMatriculasDePagosAnulados()` eliminaba **toda** matrícula con `estado_pago_matricula != 'pagada'`, lo que habría borrado matrículas de estudiantes que estaban pagando en PSE en ese instante.
- **Solución:** Reemplazado por `limpiarReservasBorradorYFallidas()`, que:
  1. **NUNCA** toca matrículas cuyo pago esté pendiente en ZonaPagos/PSE dentro de su ventana activa (ni pagos TC en estado `4001` Pendiente CR).
  2. Libera borradores que nunca dieron clic en Pagar (`tipo_pago_id IS NULL && payment_url IS NULL`) tras **30 minutos**.
  3. Libera matrículas cuyo pago fue rechazado (`1000`, `1001`, `4000`, `4003`) o vencido por abandono tras **45 minutos** en pasarela (`888`, `999`, `200` o `no_encontrado`), marcando el pago como **Transacción Abandonada (ID 16)** y **restaurando `cupos_disponibles` (+1)** de forma atómica con `lockForUpdate()`.
  4. Incluye soporte seguro para limpiar matrículas históricas/legacy previas al fix (sin `compra_id`/`pago_id`) cuando su pago ya está definitivamente fallido o abandonado.

### 🔴 3. Integridad relacional en Escuelas (`EscuelasCarrito.php` + `EstadoFinancieroService.php`)
- `EscuelasCarrito::crearMatricula()` ahora persiste explícitamente `compra_id`, `pago_id`, `referencia_pago => (string) $pago->id` y `estado_pago_id` en `matriculas`.
- `tipo_usuario_inicial_id` dejó de aplicarse en el borrador: ahora se promueve **únicamente cuando el pago es aprobado** vía `Matricula::aplicarEfectosMatricula()`.
- Al liberar una matrícula fallida o expirada, `EstadoFinancieroService` restaura `cupos_disponibles` (+1) una sola vez dentro de la misma transacción.
- `EscuelasCarrito::mount()` ya no borra matrículas con pago PSE activo pendiente.

### 🔴 4. Unicidad de `str_id_pago` en reintentos y corrección de `Checkout.php`
- Según `docs.zonavirtual.com`, nunca se debe reutilizar el mismo `str_id_pago` tras un rechazo. `Checkout::procesarPagoZonaPagos()` crea un **nuevo `Pago`** (`P-{nuevo_id}`) en cada reintento, sincroniza la matrícula y evita abrir intentos duplicados mientras exista uno pendiente con `payment_url`.
- Se agregó el `use App\Models\Matricula;` faltante en `Checkout.php` y se delegó `consultarEstadoPagoAuto()` a `ZonaPagosService::sincronizarPagoDesdePasarela()`.

### 🟡 5. Cumplimiento API REST ZonaPagos v5.0 y credenciales por tenant (`1001`)
- `ZonaPagosService::obtenerCredenciales()` lee primero desde la tabla `tipos_pago` del tenant (`key_reservada = 'zona'`):
  - `enlace` → URL base API (`https://www.zonapagos.com/Apis_CicloPago/api`)
  - `client_id` → `int_id_comercio` (cast a `int`)
  - `bussines_id` → `str_usuario` / `str_usr_comercio`
  - `key_id` → `str_clave` / `str_pwd_comercio`
  - `account_id` → `codigo_servicio` (**`1001`** en producción)
  - Con fallback automático a `config('services.zonapagos.*')` (`.env`).
- Payload `InicioPago` ahora incluye `'AdicionalesPago' => []`, `'int_modalidad' => -1` y truncamiento seguro `mb_substr(..., 'UTF-8')`.
- `parsearRespuestaVerificacion()` descarta segmentos vacíos tras `|;|` y prioriza la sub-transacción aprobada (`int_estado_pago === '1'`).
- Se guardan datos de conciliación exigidos por ZonaPagos: `gateway_transaction_id` (CUS / `str_ticketID`), `int_id_forma_pago` (`29` PSE, `32` TC) y `datos_parseados` en `gateway_response`.
- `ZonaPagosController::retornoUsuario()` verifica el pago contra ZonaPagos antes de redirigir a `carrito.compraFinalizada`, y `CarritoController::compraFinalizada()` envía el correo de confirmación **solo si el pago está aprobado** y una única vez.

---

## 4. Configuración de Producción (Multi-Tenant + Fallback `.env` + Cron)

### A. Tabla `tipos_pago` del tenant (Prioridad 1 — Recomendado)
Para el registro con `key_reservada = 'zona'` (`activo = true`):

| Columna en `tipos_pago` | Valor en Producción | Descripción |
|---|---|---|
| `enlace` | `https://www.zonapagos.com/Apis_CicloPago/api` | URL base del API REST Ciclo de Pago |
| `client_id` | `<ID_COMERCIO_PRODUCCION>` | Entero asignado por ZonaPagos |
| `bussines_id` | `<USUARIO_COMERCIO_PRODUCCION>` | Usuario API asignado por ZonaPagos |
| `key_id` | `<CLAVE_COMERCIO_PRODUCCION>` | Clave API asignada por ZonaPagos |
| `account_id` | `1001` | Código de servicio PSE/Producción |

### B. Variables `.env` (Fallback global + Ventanas de Abandono)
```env
ZONAPAGOS_API_URL="https://www.zonapagos.com/Apis_CicloPago/api"
ZONAPAGOS_ID_COMERCIO=<ID_COMERCIO_PRODUCCION>
ZONAPAGOS_USUARIO="<USUARIO_COMERCIO_PRODUCCION>"
ZONAPAGOS_CLAVE="<CLAVE_COMERCIO_PRODUCCION>"
ZONAPAGOS_CODIGO_SERVICIO=1001
ZONAPAGOS_MINUTOS_ABANDONO_PASARELA=45
ZONAPAGOS_MINUTOS_ABANDONO_BORRADOR=30
```

> [!IMPORTANT]
> Nunca almacenar secretos reales en documentación ni repositorios. Configurar las credenciales reales únicamente en `tipos_pago` del tenant y/o en el `.env` del servidor.

### C. Cron del servidor (Obligatorio para que ejecute la Sonda cada 5 min)
- **EC2 / Linux:**
  ```bash
  * * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
  ```
- **cPanel:**
  ```bash
  * * * * * cd /home/redil2024/public_html && /usr/local/bin/ea-php82 artisan schedule:run >> /dev/null 2>&1
  ```
- Verificar que Laravel reconozca la tarea programada:
  ```bash
  php artisan schedule:list
  ```

---

## 5. Mapeo de Estados de ZonaPagos (`estados_pago`)

| Código Externo (`int_estado_pago`) | ID BD | Nombre | Acción en Sonda / Callback |
|---|---|---|---|
| `1` | `9` | Pago Finalizado OK | `Compra = PAGADA (3)`, `Matricula = 'pagada'`, `valor_pagado`, `fecha_pago`, `aplicarEfectosMatricula()` |
| `999` | `5` | Pago Pendiente por Finalizar (PSE) | Mantiene reserva hasta 45 min; si vence → ID `16` Abandonada + libera matrícula y cupo |
| `888` | `39` | Pendiente por Iniciar | Mantiene reserva hasta 45 min; si vence → ID `16` Abandonada + libera matrícula y cupo |
| `200` | `37` | Pago Iniciado | Mantiene reserva hasta 45 min; si vence → ID `16` Abandonada + libera matrícula y cupo |
| `4001` | `6` | Pendiente por CR (TC) | Mantiene reserva (NO se vence a los 45 min; revisión antifraude ~24h) |
| `1000` | `4` | Pago Rechazado | `Compra = ANULADA`, soft-delete `Matricula`, borra estado académico, restaura cupo (+1) |
| `1001` | `21` | Error ACH-Banco (Rechazado) | `Compra = ANULADA`, soft-delete `Matricula`, borra estado académico, restaura cupo (+1) |
| `4000` | `7` | Rechazado por CR (TC) | `Compra = ANULADA`, soft-delete `Matricula`, borra estado académico, restaura cupo (+1) |
| `4003` | `8` | Error CR (TC) | `Compra = ANULADA`, soft-delete `Matricula`, borra estado académico, restaura cupo (+1) |
| `null` (local) | `16` | Transacción Abandonada | Asignado por la Sonda al vencer 45 min sin concretar; libera matrícula y restaura cupo (+1) |

---

## 6. Protocolo de Pruebas (Automatizadas + Manual en Local)

### A. Pruebas automatizadas PHPUnit
```bash
php artisan test --compact tests/Feature/ZonaPagosEscuelasSondaTest.php tests/Feature/DonacionesOnlineTest.php
```

### B. Prueba manual rápida de abandono en Local (sin esperar 45 min)
1. Verificar `cupos_disponibles` del horario elegido en Escuelas (ej. `10`).
2. Como estudiante, iniciar matrícula en `EscuelasCarrito` → confirmar que `cupos_disponibles` baja a `9`, `matriculas` guarda `compra_id` y `pago_id`, y el `tipo_usuario_id` **NO** cambió aún.
3. En `Checkout`, hacer clic en **Pagar** con ZonaPagos (código `1001`) y cerrar la pestaña de ZonaPagos sin finalizar el pago.
4. Esperar 1–2 minutos y ejecutar la Sonda con ventana acelerada para prueba:
   ```bash
   php artisan pagos:verificar-zonapagos --minutos-pasarela=1 --minutos-borrador=1
   ```
5. Verificar resultado:
   - `pagos.estado_pago_id` pasa a `16` (`Transacción Abandonada`).
   - `compras.estado` pasa a `2` (`ANULADA`).
   - `matriculas.deleted_at` queda poblado (soft-delete) y `matricula_horario_materia_periodo` queda eliminado.
   - `horarios_materia_periodo.cupos_disponibles` vuelve exactamente a `10`.
   - En el perfil de la actividad vuelve a habilitarse el botón para matricularse de nuevo y al reintentar se genera un **nuevo `Pago` (`P-{nuevo_id}`)**.
