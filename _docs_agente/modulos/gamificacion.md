# Análisis Técnico: Sistema de Gamificación (cjmellor/level-up)

Este documento contiene el análisis y la arquitectura acordada para la implementación del sistema de gamificación en el proyecto REDIL-CLOUD. Queda a la espera de aprobación por parte del cliente.

## 1. Core de Gamificación
Se utilizará el paquete `cjmellor/level-up` para gestionar:
- **XP (Experiencia)**: Puntos acumulables por acciones en la plataforma.
- **Niveles (Rangos)**: Progresión basada en XP. Visualmente se llamarán "Rangos" para no chocar con los "Niveles Académicos".
- **Logros (Achievements)**: Hitos específicos alcanzados por el usuario.

## 2. Arquitectura Multi-Tenant
- **Ubicación**: Las tablas de gamificación residirán en el esquema de cada Tenant (`database/migrations/tenant`).
- **Aislamiento**: Cada iglesia podrá configurar sus propios rangos y recompensas.

## 3. Sistema de Recompensas Canjeables
Se implementará una capa de negocio sobre el paquete base para manejar premios físicos o bonos.

### Componentes de Datos
- **Modelo `Premio`**:
    - Campos: `nombre`, `descripcion`, `imagen`, `requerimiento_tipo` (slug de logro/nivel).
    - **Gestión de Stock**: No se requiere control de inventario en esta fase.
- **Modelo `Redencion`**:
    - Campos: `user_id`, `premio_id`, `codigo_redencion` (alfanumérico único), `estado` (Canjeado/Entregado), `fecha_entrega`.

### Flujo de Redención
1. **Desbloqueo**: El usuario alcanza un logro o nivel que tiene un premio asociado.
2. **Solicitud**: El usuario pulsa "Canjear" en su perfil. El sistema genera un código alfanumérico y un código QR.
3. **Entrega**: El usuario presenta su código en la iglesia.
4. **Verificación**: Un administrador escanea el QR o ingresa el código en un panel administrativo para marcar el premio como "Entregado".

## 4. Puntaje Modular (Contextual)
Para manejar metas específicas de módulos (ej: "8 puntos en Grupos" para ganar una camiseta):
- **Registro de Progreso**: Se usará una tabla `progreso_meta_modular`.
- **Lógica**: Al registrar asistencia en Grupos, se suma +1 a la meta modular.
- **Trigger**: Al llegar a la meta (ej: 8), el sistema dispara el logro de `level-up` correspondiente automáticamente.

## 5. Administradores y Seeders
- **CRUD de Premios**: Vista administrativa para que cada iglesia gestione sus incentivos.
- **Premios Base**: Se proveerá un `Seeder` con premios por defecto (Bono café, Gorras, etc.) que podrán ser activados por los centros.

---
**Estado**: Pendiente de Aprobación del Cliente.
**Última Actualización**: 2026-04-08
