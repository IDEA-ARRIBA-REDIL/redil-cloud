# 🛠️ Procedimiento para Pruebas Manuales

A continuación se describe el flujo de trabajo recomendado para realizar pruebas manuales efectivas en el proyecto **REDIL-CLOUD**.

---

### 1. Preparación del Entorno
- **Limpieza de Caché:** Siempre ejecute `php artisan view:clear`, `php artisan cache:clear` antes de una sesión de pruebas profunda.
- **Datos de Prueba:** Utilice los seeders oficiales (`php artisan db:seed --class=CursoDemoSeeder`) para tener datos fidedignos.
- **Tenant Context:** Asegúrese de estar logueado con un usuario que pertenezca al tenant (Sede/Escuela) correcto.

### 2. Flujo de Ejecución de Pruebas
1.  **Exploración:** Navegue por el módulo designado (Ej. Matrículas).
2.  **Identificación:** Si ocurre un error, verifique si es reproducible (sucede siempre bajo las mismas condiciones).
3.  **Extracción de Datos:** Si es un error de código, capture el log (`storage/logs/laravel.log`) o el error de Livewire en la consola del navegador.
4.  **Registro:** Agregue una fila a la [Bitácora de Pruebas](file:///_docs_agente/pruebas/log_pruebas_manuales.md).

### 3. Clasificación de Errores (SaaS Standard)
- **Error de Base de Datos:** Relaciones rotas, campos faltantes, fallos de integridad.
- **Error de Lógica:** El sistema hace algo que no debería (Ej. Un estudiante se inscribe a un curso sin cumplir requisitos).
- **Error de UI/UX:** Elementos que no se ven bien, botones que no responden, falta de feedback (cargando...).
- **Error de Multitenancy:** ¡MUY IMPORTANTE! Si un usuario de la Sede A puede ver datos de la Sede B, es un error de alta prioridad.
- **Error de Multimedia:** Fallas al subir comprobantes de pago o fotos de perfil.

### 4. Resolución y Verificación
- El desarrollador marca el error como **"Corregido"** en la bitácora.
- El tester vuelve a realizar la prueba. Si se soluciona, se marca como **"Verificado"**.

---
*Este documento es dinámico y debe actualizarse conforme el sistema crezca.*
