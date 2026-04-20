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
- [ ] Tabla `notifications` (sistema nativo de Laravel)
- [ ] Modelo/Migración para notificaciones por tenant
- [ ] Componente Livewire `CampanaNotificaciones` (icono + badge contador)
- [ ] Integración con Laravel Reverb para actualizar contador en tiempo real
- [ ] Vista de listado de notificaciones del usuario
- [ ] Marcar como leídas / eliminar

### Fase 2 — Configuración PWA
- [ ] Archivo `manifest.json` dinámico (nombre, iconos, colores del tenant)
- [ ] Service Worker base (`sw.js`) registrado via Vite
- [ ] Splash screens y meta tags PWA en layout principal
- [ ] Prompt de instalación ("Añadir a pantalla de inicio")
- [ ] Estrategia de caché offline (shell de la app)

### Fase 3 — Notificaciones Push Externas
- [ ] Generación de claves VAPID (por tenant o global)
- [ ] Endpoint para suscripción Push (`PushSubscription`)
- [ ] Modelo `PushSubscription` vinculado al usuario
- [ ] Envío de notificaciones push desde el backend (queue)
- [ ] Recepción y display en Service Worker (`push` + `notificationclick`)
- [ ] App Badge API para "puntito rojo" en icono
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

| Archivo                                          | Estado     | Descripción                                |
| ------------------------------------------------ | ---------- | ------------------------------------------ |
| `public/manifest.json`                           | 🔲 Pendiente | Manifiesto PWA                             |
| `public/sw.js`                                   | 🔲 Pendiente | Service Worker                             |
| `resources/views/layouts/app.blade.php`          | 🔲 Pendiente | Meta tags PWA + registro SW                |
| `app/Livewire/Notificaciones/Campana.php`        | 🔲 Pendiente | Componente campana con badge               |
| `database/migrations/xxxx_notifications.php`     | 🔲 Pendiente | Migración de notificaciones                |
| `app/Models/PushSubscription.php`                | 🔲 Pendiente | Modelo para suscripciones push             |

---

## 7. Registro de Progreso

### 2026-04-20 — Inicio del Proyecto
- ✅ Documento de análisis revisado (`analisis_notificaciones_pwa.md`)
- ✅ Estado de git verificado (rama `main`, actualizada)
- ✅ Branch `feature/pwa-notificaciones` creada
- ✅ Workflow `agentePWA.md` creado

---

**Nota**: Este agente se activa con `/agentePWA`. Siempre consultar el análisis base y este workflow antes de implementar cambios.
