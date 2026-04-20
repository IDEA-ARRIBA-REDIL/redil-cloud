---
description: Carga el contexto y memoria del Agente de Notificaciones PWA (Progressive Web App)
---

# Agente PWA (`agentePWA`)

## 1. Activación de Persona (desde `/baseDesarrollo`)

- **Rol**: Experto en Laravel 11, Livewire 3, Alpine.js, Bootstrap 5, Service Workers, Web Push API, PWA (Progressive Web Apps) y arquitectura multi-tenant.
- **Idioma**: Español nativo.
- **Convenciones**: variables/funciones en `camelCase`, comentarios numerados en bloques complejos.
- **Entorno**: Desarrollo local en macOS, pruebas en `cloud.laravel.com`.

---

## 2. Documento de Análisis Base

📄 **Referencia**: `_docs_agente/analisis_notificaciones_pwa.md`

### Visión General
Implementar un sistema de engagement y notificaciones sin depender de una aplicación nativa, utilizando tecnologías web modernas (PWA, Web Push, WebSockets) para maximizar la retención de usuarios en la plataforma REDIL CLOUD.

### Tecnologías del Módulo

| Tecnología           | Uso                                                               |
| -------------------- | ----------------------------------------------------------------- |
| **PWA**              | Instalación en pantalla de inicio, icono, splash screen           |
| **Service Worker**   | Caché offline, interceptar push, gestión de notificaciones        |
| **Web Push API**     | Notificaciones nativas (Android Chrome 42+, iOS 16.4+)           |
| **App Badge API**    | Contador ("puntito rojo") sobre el icono de la PWA                |
| **Laravel Reverb**   | WebSockets para actualizaciones en tiempo real dentro de la app   |
| **WhatsApp API**     | Canal fallback para dispositivos sin soporte Push                 |
| **Vite**             | Bundling del Service Worker y manifest                            |

---

## 3. Hoja de Ruta (Fases de Implementación)

### Fase 1 — Notificaciones Internas + Tiempo Real
- [x] Tabla `notifications` (sistema nativo de Laravel)
- [x] Clase `NotificacionGeneral` (canal database)
- [x] Componente Livewire `CampanaNotificaciones` (icono + badge contador con `wire:poll.30s`)
- [ ] Integración con Laravel Reverb para actualizar contador en tiempo real (diferido a Fase 3)
- [x] Vista de listado de notificaciones del usuario (`ListaNotificaciones`)
- [x] Marcar como leídas / eliminar
- [x] Ruta `/notificaciones` en `routes/app.php`
- [x] Integración de la campana en el `navbar.blade.php`

### Fase 2 — Configuración PWA
- [x] Archivo `manifest.json` dinámico (nombre, iconos, colores del tenant)
- [x] Service Worker base (`sw.js`) registrado con bypass de navegación
- [x] Splash screens y meta tags PWA en layout principal
- [x] Prompt de instalación ("Añadir a pantalla de inicio")
- [x] Estrategia de caché offline (versionamiento por params url)

### Fase 3 — Notificaciones Push Externas
- [ ] Generación de claves VAPID (por tenant o global)
- [ ] Endpoint para suscripción Push (`PushSubscription`)
- [ ] Modelo `PushSubscription` vinculado al usuario
- [ ] Envío de notificaciones push desde el backend (queue)
- [ ] Recepción y display en Service Worker (`push` + `notificationclick`)
- [x] App Badge API para "puntito rojo" en icono (W3C Standard App Badging)
- [ ] Integración WhatsApp Business API (fallback)

---

## 4. Estrategia Multi-Tenancy

- **PWA Unificada**: Una sola configuración de PWA bajo el dominio central.
- **Notificaciones Aisladas**: Se disparan desde cada BD tenant, vinculadas al `user_id`.
- **Suscripciones Push**: Almacenadas por usuario, el endpoint push es global.
- **Simplicidad**: El usuario pertenece a una única sede a la vez.

---

## 5. Branch de Desarrollo

- **Branch**: `feature/pwa-notificaciones`
- **Base**: `main`
- **Estrategia de commits**: Commits descriptivos por cada sub-tarea completada.

---

## 6. Archivos Clave del Módulo (se actualizará conforme avancemos)

| Archivo                                                                       | Estado       | Descripción                                |
| ----------------------------------------------------------------------------- | ------------ | ------------------------------------------ |
| `database/migrations/tenant/2026_04_20_170000_create_notifications_table.php` | ✅ Creado     | Migración de notificaciones (tenant)       |
| `app/Notifications/NotificacionGeneral.php`                                   | ✅ Creado     | Clase de notificación genérica (database)  |
| `app/Livewire/Notificaciones/CampanaNotificaciones.php`                       | ✅ Creado     | Componente campana con badge + dropdown    |
| `resources/views/livewire/notificaciones/campana-notificaciones.blade.php`    | ✅ Creado     | Vista de la campana en el navbar           |
| `app/Livewire/Notificaciones/ListaNotificaciones.php`                         | ✅ Creado     | Página completa de todas las notificaciones|
| `resources/views/livewire/notificaciones/lista-notificaciones.blade.php`      | ✅ Creado     | Vista de lista con filtros y paginación    |
| `resources/views/layouts/sections/navbar/navbar.blade.php`                    | ✅ Modificado | Integración del componente campana         |
| `routes/app.php`                                                              | ✅ Modificado | Ruta `/notificaciones` y Rutas PWA         |
| `app/Http/Controllers/PwaController.php`                                      | ✅ Creado     | Controlador de Manifest e Iconos Dinámicos |
| `public/sw.js`                                                                | ✅ Creado     | Service Worker V4 (Ignora Navegación)      |
| `app/Models/PushSubscription.php`                                             | 🔲 Pendiente  | Modelo para suscripciones push (Fase 3)    |

---

## 7. Registro de Progreso

### 2026-04-20 — Inicio del Proyecto
- ✅ Documento de análisis revisado (`analisis_notificaciones_pwa.md`)
- ✅ Estado de git verificado (rama `main`, actualizada)
- ✅ Branch `feature/pwa-notificaciones` creada
- ✅ Workflow `agentePWA.md` creado

### 2026-04-20 — Fase 1 Completada
- ✅ Migración `notifications` creada en `database/migrations/tenant/`
- ✅ Clase `NotificacionGeneral` creada (canal database)
- ✅ Componente `CampanaNotificaciones` (PHP + Blade) con `wire:poll.30s`
- ✅ Componente `ListaNotificaciones` (PHP + Blade) con filtros y paginación
- ✅ Navbar modificado para integrar la campana Livewire
- ✅ Ruta `/notificaciones` agregada en `routes/app.php`
- ✅ Laravel Pint ejecutado, código formateado
- ✅ Sincronización exitosa en `cloud.laravel.com` (Main Branch)

### 2026-04-20 — Fase 2 (Re-intento Seguro) Completada
- ✅ `PwaController` re-creado con branding dinámico por tenant
- ✅ Ruta `/manifest.json` re-integrada
- ✅ Service Worker V3 (Seguro) implementado: **Ignora peticiones de navegación**
- ✅ Se eliminaron errores de redirección de Safari y Android
- ✅ Layout `commonMaster.blade.php` actualizado con tags y registro V3
- ✅ Laravel Pint ejecutado
- ✅ Sincronización final en `main` lista para deploy

---

**Nota**: Este agente se activa con `/agentePWA`. Siempre consultar el análisis base y este workflow antes de implementar cambios.
