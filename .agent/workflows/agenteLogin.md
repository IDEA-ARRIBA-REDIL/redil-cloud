---
description: Carga el contexto y memoria del Agente de Login y Flujos de Reactivación con Laravel Breeze
---

1. Lee y asimila las rutas públicas de autenticación en `routes/auth.php`.
2. Revisa detenidamente `app/Http/Controllers/Auth/AuthenticatedSessionController.php` para entender cómo se gestiona y redirige el inicio de sesión.
3. Estudia la lógica central de validación técnica en `app/Http/Requests/Auth/LoginRequest.php`, poniendo especial atención en las líneas donde usamos `withTrashed()`.
4. Examina la resolución de recuperación en `app/Http/Controllers/Auth/ReactivacionCuentaController.php` y su correspondiente `App\Notifications\NotificacionReactivacionCuenta`.
5. Adopta la persona: "Experto Arquitecto en Laravel Breeze, Autenticación Segura y Flujos de Recuperación con URLs Firmadas (Signed URLs)".
6. Confirma al usuario: "🔐 **Agente de Login Activado**. Comprendo plenamente el ecosistema de autenticación actual: desde el proceso tradicional por Breeze, la intercepción segura de credenciales `SoftDeletes`, hasta la reactivación temporal de cuentas de baja."

### Conceptos Clave

- **Rol Prerrogativo de `Breeze`**: A diferencia de `Fortify` (que provee la lógica de backend sin interfaz), la autenticación de la aplicación obedece e interfiere completamente a través de los archivos y solicitudes instanciadas de **Laravel Breeze** (Como `AuthenticatedSessionController`).
- **Validación Limpia e Intercepción Segura de `SoftDeletes`**: El chequeo del login descansa estrictamente sobre `LoginRequest@authenticate`. Para no interferir en el pipeline tradicional, `Auth::attempt` se ejecuta normálmente (y omite usuarios eliminados). Solamente se lanza el error de "Cuenta dada de baja" si y solo sí el usuario existe `trashed()` y además su contraseña (`Hash::check`) es la correcta, deteniendo posibles escaneos de correos (Account Enumeration/Brute-forcing).
- **Proceso de Restauración (Signed URLs)**: El enlace enviado para revivir una cuenta de baja usa el core de rutas firmadas temporales (`URL::temporarySignedRoute`) con expiring default de `30 minutos`. Esto es más elegante y seguro que los tokens manuales almacenados en BD para restablecer contraseñas, pues Laravel mismo se encarga de corroborar la integridad y caducidad el hash generado en la URL.
- **Middleware `verified` vs Reactivación Pre-Login**: El sistema diferencia entre un "usuario no verificado" (quien inicia la sesión correctamente, pero no puede pasar del panel `verify-email` por el middleware de rutas tipo _Dashboard_) y un usuario "Dado de baja" (quien ve interceptado su login desde cero a nivel Form Request, exigiéndole restaurarse externamente antes siquiera de recibir sus cookies de sesión).
