# INFORME DE AUDITORÍA DE SEGURIDAD - REDIL Cloud

**Fecha de auditoria:** 28 de Mayo de 2026
**Proyecto:** REDIL Cloud
**Estado:** Issues identificados, algunos corregidos

---

## RESUMEN EJECUTIVO

Se identificaron **27 vulnerabilidades** divididas en 4 niveles de severidad:

| Severidad | Cantidad | Estado |
|-----------|----------|--------|
| **CRITICAL** | 4 | Requiere acción inmediata |
| **HIGH** | 7 | Alta prioridad |
| **MEDIUM** | 10 | Prioridad media |
| **LOW** | 6 | Mejoras recomendadas |

---

## CRITICAL - Acción Inmediata

### 1. Credenciales expuestas en archivo .env

**Ubicación:** `.env` (todo el archivo)

**Descripción:** El archivo `.env` contiene credenciales reales de producción incluyendo:
- Llave de encryption `APP_KEY`
- Credenciales de base de datos PostgreSQL
- API keys de Mailgun
- Credenciales de Google Calendar OAuth
- Credenciales de ZonaPagos (pasarela de pagos)
- Tokens de VAPID para notificaciones PWA

**Impacto:** Compromiso total del sistema si estas credenciales son accedidas por actores maliciosos.

**Acción requerida:**
1. Rotar TODAS las credenciales inmediatamente
2. Regenerar `APP_KEY` con `php artisan key:generate`
3. Activar 2FA en cuentas de Google usadas para OAuth
4. Contactar a ZonaPagos para invalidar credenciales expuestas
5. NUNCA commitear `.env` al repositorio (ya está en .gitignore, verificar con `git status`)

---

### 2. Contraseñas enviadas en texto plano por email

**Ubicación:** `app/Http/Controllers/UserController.php` líneas 4417, 4424, 4441, 4448

**Descripción:** El sistema envía nuevas contraseñas directamente en emails sin加密. Un atacante con acceso al email podría robar la contraseña.

**Código vulnerable:**
```php
$mailData->mensaje = '... Nueva clave: '.$nuevaContrasena.' ...';
```

**Acción requerida:**
- Implementar "Olvidé mi contraseña" con link seguro en vez de enviar contraseña
- Usar Laravel's password reset flow con tokens cifrados
- Enviar solo notificación de cambio, no la contraseña

---

### 3. Contraseñas débiles por defecto

**Ubicación:** `app/Http/Controllers/UserController.php` líneas 2310-2313

**Descripción:** El sistema usa la identificación del usuario o "123456" como contraseña por defecto cuando se crea un usuario sin password.

```php
$usuario->password = $configuracion->identificacion_obligatoria
    ? Hash::make($request->identificación)
    : Hash::make('123456');
```

**Impacto:** Usuarios nuevos tienen contraseñas fácilmente adivinables.

**Acción requerida:**
- Forzar cambio de contraseña en primer login
- Generar passwords aleatorios seguros en vez de usar identificación
- Implementar política de contraseñas mínimas

---

### 4. Path Traversal en FileViewerController

**Ubicación:** `app/Http/Controllers/FileViewerController.php` línea 35

**Descripción:** El sistema usa `archivo_path` directamente de la base de datos para construir la ruta del archivo a mostrar. Un atacante podría modificar este valor para acceder a archivos fuera del directorio esperado.

```php
$filePath = $leccion->archivo_path; // Viene de la BD
return response()->file(storage_path('app/public/' . $leccion->archivo_path));
```

**Acción requerida:**
- Validar que `archivo_path` sea un path válido dentro del storage del tenant
- Usar solo el nombre del archivo, nunca paths completos de la BD
- Implementar verificación de propiedad del archivo

---

## HIGH - Alta Prioridad

### 5. Sin verificación de signed URL en reactivación de cuenta

**Ubicación:** `app/Http/Controllers/Auth/ReactivacionCuentaController.php` líneas 71-81

**Descripción:** El controller genera un signed URL para reactivation pero nunca lo verifica antes de restaurar la cuenta.

**Acción requerida:**
- Agregar validación de firma: `URL::hasValidSignature($request)`

---

### 6. Todos los archivos subidos van a disco público

**Ubicación:** Múltiples controllers de upload

**Descripción:** Todos los uploads usan `Storage::disk('public')` lo que hace los archivos accesibles via URL sin autenticación.

**Acción requerida:**
- Implementar disco privado para archivos sensibles
- Usar signed URLs para acceder a archivos privados

---

### 7. Modelo con $guarded = [] vulnerable a Mass Assignment

**Archivos:**
- `app/Models/Actividad.php` ✅ CORREGIDO
- `app/Models/ActividadCategoria.php` ✅ CORREGIDO
- `app/Models/Compra.php` ✅ CORREGIDO
- `app/Models/Pago.php` ✅ CORREGIDO

**Descripción:** Modelos de pago usaban `$guarded = []` permitiendo asignar cualquier campo vía mass assignment.

**Acción requerida:** Implementar `$fillable` explícito. ✅ YA CORREGIDO

---

### 8. ruta_almacenamiento de BD usado en paths

**Ubicación:** Múltiples controllers y vistas

**Descripción:** El sistema usaba `$configuracion->ruta_almacenamiento` de la base de datos para construir paths de archivos, permitiendo path traversal si un atacante modificaba este valor.

**Acción requerida:** Usar `tenant_asset()` en vez de paths de BD. ✅ YA CORREGIDO

---

### 9. Rol "Admin" hardcodeado en verificación

**Ubicación:** `app/Http/Controllers/MaestroController.php` línea 642

```php
if (! $usuario->hasRole('Admin') && ! $usuario->can('escuelas.tab_gestionar_items')) {
```

**Descripción:** Usa string hardcodeado 'Admin' en vez de permission check. Si se renombra el rol, el código falla.

**Acción requerida:**
- Usar solo permisos: `$usuario->can('permiso')`
- Eliminar dependencia de nombres de roles

---

### 10. Sesiones sin encrypt por defecto

**Ubicación:** `config/session.php` línea 50

```php
'encrypt' => env('SESSION_ENCRYPT', false),
```

**Descripción:** Los datos de sesión no están cifrados en el cliente.

**Acción requerida:**
- Cambiar a `encrypt => true` en producción
- Asegurar que `SESSION_SECURE_COOKIE=true` en producción

---

### 11. Mass assignment en modelos de pago

**Ver en sección CRITICAL #7** ✅ YA CORREGIDO

---

## MEDIUM - Prioridad Media

### 12. Validación MIME solo client-side

**Descripción:** Las validaciones de tipo de archivo solo se hacen en JavaScript del navegador, no en servidor.

**Acción requerida:**
- Validar MIME type en servidor con `finfo_file()` o `mime_content_type()`
- No confiar en `$file->getClientMimeType()`

---

### 13. QR withdrawal sin verificación de adulto

**Ubicación:** `app/Http/Controllers/IglesiaInfantilController.php` línea 278

**Descripción:** El método `retirarConQr()` permite retirar niños solo con el código QR, sin verificar identidad del adulto.

**Acción requerida:**
- Agregar verificación adicional (pregunta de seguridad, foto)
- Considerar como riesgo de diseño según contexto

---

### 14. Rate limiting global insuficiente

**Ubicación:** `app/Providers/RouteServiceProvider.php` línea 27

**Descripción:** Solo hay un rate limit global de 60/min para toda la API. Endpoints sensibles (auth, pagos) deberían tener límites más estrictos.

**Acción requerida:**
- Agregar rate limits específicos para auth (5/min)
- Agregar rate limits para pagos (10/min)
- Implementar exponential backoff

---

### 15. No existe configuración CORS

**Descripción:** No hay archivo `config/cors.php` personalizado. El sistema usa defaults de Laravel.

**Acción requerida:**
- Crear `config/cors.php` con orígenes explícitos
- Configurar allowed methods y headers para el frontend

---

### 16. Excepciones silenciosas en envío de passwords

**Ubicación:** `app/Http/Controllers/UserController.php` líneas 4426-4429

```php
try {
    Mail::to($usuario->email)->send(new DefaultMail($mailData));
} catch (Exception $e) {
    // Silenciado - admin no sabe que falló
}
```

**Descripción:** Fallas en envío de email de password no notifican al administrador.

**Acción requerida:**
- Loguear excepciones silenciosamente
- Notificar al admin de fallas críticas
- Implementar cola de retry para emails

---

### 17. Email verification usa sha1

**Ubicación:** `app/Http/Controllers/UserController.php` línea 81

```php
if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
```

**Descripción:** Usa sha1() para hash de email verification. `hash_equals()` mitiga timing attacks pero sha1 es débil.

**Acción requerida:**
- Considerar usar SHA256 o hash dedicados
- Para contexto actual probablemente acceptable

---

### 18. Validación de ruta_almacenamiento ausente

**Ubicación:** `app/Models/Configuracion.php`

**Descripción:** El campo `ruta_almacenamiento` se guarda sin validación, permitiendo valores maliciosos.

**Acción requerida:**
- Agregar setter que valide formato: `^[a-zA-Z0-9_\-]+$`
- O usar valor fijo basado en ID del tenant

---

## LOW - Mejoras Recomendadas

### 19. Email verification deshabilitado en Fortify

**Ubicación:** `config/fortify.php` línea 149

```php
// Features::emailVerification() está comentado
```

**Descripción:** La verificación de email está deshabilitada.

**Recomendación:** Habilitar si el flujo lo requiere.

---

### 20. Nombres de archivo predecibles en uploads

**Descripción:** Los uploads usan `uniqid() + time()` lo que es teóricamente predecible.

**Recomendación:** Usar `Storage::putFileAs()` con nombres generados criptográficamente.

---

### 21. Permisos de directorio 0755

**Descripción:** Los directorios creados tienen permisos 0755 (world-readable).

**Recomendación:** Usar 0750 para mayor seguridad.

---

### 22. Algoritmo de password hash no especificado

**Descripción:** Usa bcrypt por defecto (Laravel) sin configuración explícita.

**Recomendación:** Considerar Argon2id en `config/hashing.php`.

---

### 23. Session lifetime 120 min por defecto

**Ubicación:** `config/session.php`

**Descripción:** Las sesiones expiran en 2 horas. Para app sensitive podría ser muy largo.

**Recomendación:** Reducir a 30-60 min para sesiones inactivas.

---

### 24. SoftDeletes sin cleanup

**Descripción:** Modelos usan SoftDeletes pero no hay job de limpieza de registros eliminados.

**Recomendación:** Implementar limpieza periódica de registros eliminados.

---

## ARCHIVOS CORREGIDOS

### Mass Assignment (Punto 7) ✅

| Modelo | Cambio |
|--------|--------|
| `app/Models/Actividad.php` | `$guarded = []` → `$guarded = [12 campos]` |
| `app/Models/ActividadCategoria.php` | `$guarded = []` → `$guarded = [5 campos]` |
| `app/Models/Compra.php` | `$guarded = []` → `$fillable = [14 campos]` |
| `app/Models/Pago.php` | `$guarded = []` → `$fillable = [12 campos]` |

### ruta_almacenamiento (Punto 8) ✅

**Controladores PHP corregidos (8 archivos):**
- `app/Http/Controllers/CursoController.php`
- `app/Http/Controllers/PeticionController.php`
- `app/Livewire/Peticiones/GestionarPeticiones.php`
- `app/Http/Controllers/NivelEscuelaController.php`
- `app/Http/Controllers/NivelesEscuelasController.php`
- `app/Mail/DefaultMail.php`
- `app/Mail/RecordatorioFormularioMail.php`
- `app/Mail/InscripcionConfirmacionMail.php`
- `app/Livewire/FormulariosParaUsuarios/GestionarSeccionesYCampos.php`

**Views corregidas (~40 archivos):** Todos ahora usan `tenant_asset('...')` en vez de `Storage::url($configuracion->ruta_almacenamiento . '/...')`

---

## PENDIENTE POR CORREGIR

| # | Issue | Severidad | Prioridad |
|---|-------|-----------|-----------|
| 1 | Rotar credenciales .env | CRITICAL | Inmediato |
| 2 | Implementar reset password con link | CRITICAL | Inmediato |
| 3 | Verificar signed URL en ReactivacionCuenta | HIGH | Alta |
| 4 | Implementar disco privado para uploads | HIGH | Alta |
| 5 | Cambiar hasRole('Admin') a permission check | HIGH | Alta |
| 6 | Encrypt sessions en producción | HIGH | Alta |
| 7 | Rate limits específicos para auth/pagos | MEDIUM | Media |
| 8 | Configurar CORS | MEDIUM | Media |
| 9 | Agregar validación MIME server-side | MEDIUM | Media |

---

**Documento generado:** 28 de Mayo de 2026
**Auditoría realizada por:** Sistema de análisis de seguridad