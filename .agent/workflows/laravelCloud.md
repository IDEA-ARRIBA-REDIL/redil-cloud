---
description: Agente de referencia para despliegue y gestión del proyecto REDIL Cloud en Laravel Cloud (cloud.laravel.com)
---

# Agente de Laravel Cloud

Este documento centraliza toda la información relevante sobre la plataforma **Laravel Cloud** y cómo aplica a nuestro proyecto **REDIL Cloud** (Laravel 12 + PostgreSQL + Multi-Tenancy + Livewire).

> **Referencia oficial**: https://cloud.laravel.com/docs/intro

---

## 1. ¿Qué es Laravel Cloud?

Es la plataforma oficial de infraestructura y despliegue para aplicaciones Laravel. Sus ventajas clave para REDIL Cloud:

| Característica                    | Descripción                                                                        |
| --------------------------------- | ---------------------------------------------------------------------------------- |
| **Sin configuración de servidor** | La mayoría de apps Laravel se despliegan sin cambios en el código                  |
| **Base de datos gestionada**      | MySQL, PostgreSQL (Serverless via Neon) y Redis/Valkey completamente administrados |
| **Zero Downtime Deployments**     | Despliegues sin interrupciones para los usuarios                                   |
| **Auto-escalado**                 | Escala la infraestructura automáticamente sin interrumpir usuarios                 |
| **TLS automático**                | Certificados SSL generados y renovados automáticamente                             |
| **Cloudflare Edge Network**       | CDN global, protección DDoS y cache de assets en el borde                          |
| **Monitoreo y Logs**              | CPU, memoria y logs accesibles desde el dashboard                                  |

---

## 2. Primeros Pasos (Quickstart)

1. Ir al dashboard de Laravel Cloud → **+ New Application**.
2. Conectar el proveedor Git (GitHub, GitLab, etc.).
3. Crear el **Environment** (producción, staging, etc.).
4. Configurar **Compute**, **Base de Datos**, y **Variables de Entorno**.
5. Desplegar.

**Documentación:** https://cloud.laravel.com/docs/quickstart

---

## 3. Environments (Entornos)

Cada environment tiene su propio compute, base de datos y variables. REDIL Cloud necesitará al menos:

- `production` → el entorno productivo con dominios `redilcloud.com` y `*.redilcloud.com`
- `staging` (opcional) → para pruebas antes de producción

### 3.1 Comandos de Build

Laravel Cloud ejecuta estos comandos al construir la imagen:

```bash
# Instalar dependencias de producción
composer install --no-dev

# Cachear rutas y configuración
php artisan optimize
php artisan config:cache
```

> **Nota para REDIL Cloud Multi-Tenancy:** No usar `php artisan optimize:clear` en el deploy ya que puede causar comportamientos inesperados con las colas.

### 3.2 Comandos de Deploy (NO necesarios en Laravel Cloud)

Los siguientes comandos son **innecesarios** porque Laravel Cloud los maneja:

| Comando                         | Por qué no usar                                         |
| ------------------------------- | ------------------------------------------------------- |
| `php artisan queue:restart`     | Workers se reinician automáticamente                    |
| `php artisan horizon:terminate` | Horizon se gestiona automáticamente                     |
| `php artisan optimize:clear`    | Limpia la caché y puede romper las colas                |
| `php artisan storage:link`      | El symlink no persiste; usar Object Storage en su lugar |

### 3.3 Configuración del Environment

| Parámetro            | Valor recomendado para REDIL Cloud                                                        |
| -------------------- | ----------------------------------------------------------------------------------------- |
| **PHP Version**      | 8.2+ (requerido por Laravel 12)                                                           |
| **PHP Extensions**   | `pdo_pgsql`, `redis`, `gd`, `imagick`, `pcntl`, `sockets` (todas disponibles por defecto) |
| **PHP Memory Limit** | Configurar en `public/index.php` con `ini_set('memory_limit', '512M')` si se requiere     |
| **Node Version**     | Configurable desde el dashboard (para compilar assets Vite)                               |

### 3.4 Variables de Entorno

Las variables del `.env` se configuran directamente en el dashboard de Laravel Cloud. **No se sube el `.env` al repositorio.**

Variables críticas para REDIL Cloud en producción:

```
APP_ENV=production
APP_URL=https://redilcloud.com
APP_DEBUG=false
DB_CONNECTION=pgsql
SESSION_DRIVER=database
CACHE_STORE=redis       # ← Importante para Multi-tenancy con Cache Tags
QUEUE_CONNECTION=redis  # ← Recomendado para producción
```

> **⚠️ CRÍTICO para Multi-Tenancy:** En producción, usar `CACHE_STORE=redis` (Laravel Valkey o Redis by Upstash) para habilitar los Cache Tags que requiere `stancl/tenancy`. En desarrollo local con `database` o `file` se debe comentar el `CacheTenancyBootstrapper` (ver Agente de Multi-Tenancy, Sección 11).

### 3.5 Preview Environments

- Disponibles en planes **Growth y Business**.
- Se crean automáticamente para cada Pull Request.
- Útil para pruebas de features antes de merge a `main`.
- Comparten recursos configurables (DB propia, misma DB, etc.).

---

## 4. Bases de Datos

### 4.1 Laravel MySQL

- Bases de datos MySQL completamente gestionadas.
- Configuración: Cluster → Instancia (Flex o Pro) → Storage (5GB a 1TB) → Región.
- La región debe coincidir con la región del cluster de compute.
- Soporta backups automáticos con retención configurable.
- Admite conexiones SSL y endpoints públicos.

### 4.2 Serverless Postgres (via Neon) ✅ RECOMENDADO PARA REDIL CLOUD

- **¿Por qué elegirlo?** REDIL Cloud ya usa **PostgreSQL** localmente. Esta opción es la compatibilidad directa.
- Servido por [Neon](https://neon.tech), escala hasta `0` cuando no hay actividad (reduce costos).
- Límites por plan:
  - Starter: Hasta 1 vCPU
  - Growth: Hasta 4 vCPU
  - Business: Hasta 16 vCPU

> **Para Multi-Tenancy:** Cada tenant tiene su propia base de datos (`tenantIGLESIA1`, etc.). Al migrar a Laravel Cloud se deben gestionar todas las bases de datos de los tenants. El comando `php artisan tenants:migrate` debe estar en el proceso de deployment.

### 4.3 Importar datos existentes

Laravel Cloud recomienda herramientas específicas para importar datos:

- Se hace desde la sección de la base de datos en el dashboard.
- Útil para migrar los datos existentes de `redil2024_db` y las DBs de cada tenant.

---

## 5. Cache y Key-Value Storage

### 5.1 Laravel Valkey (Redis-compatible) ✅ RECOMENDADO

- Solución Redis compatible gestionada por Laravel Cloud.
- Permite usar **Cache Tags** (requerido por `stancl/tenancy` para el `CacheTenancyBootstrapper`).
- Habilita el uso de colas con Redis en producción (más performante que la tabla `jobs` en DB).

### 5.2 Redis by Upstash

- Alternativa serverless serverless para Redis.
- Límites por plan:
  - Starter: Hasta 2.5GB
  - Growth: Hasta 50GB
  - Business: Hasta 500GB

> **Nota:** Con Valkey o Upstash Redis en producción, se puede **reactivar** el `CacheTenancyBootstrapper` en `config/tenancy.php` (que fue desactivado en desarrollo por no soportar Cache Tags con el driver `database`/`file`).

---

## 6. Object Storage (Archivos Subidos por Usuarios) ✅ IMPORTANTE PARA REDIL CLOUD

- Almacenamiento de objetos compatible con **S3** basado en **Cloudflare R2**.
- Reemplaza el `storage/app/public` local que no persiste entre despliegues.
- **¿Por qué es crítico para REDIL Cloud?** Actualmente los tenants guardan sus archivos en `storage/app/tenant{ID}/...`. En la nube, este storage no persiste. **Se debe migrar a Object Storage.**

### Cómo implementarlo:

1. Instalar el paquete S3:
   ```bash
   composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
   ```
2. En el dashboard de Laravel Cloud → **Add Object Storage** → crear un bucket.
3. Configurar `FILESYSTEM_DISK=s3` en las variables de entorno.
4. El paquete `stancl/tenancy` tiene soporte para discos S3, ajustar `config/tenancy.php` para apuntar al disco `s3` en vez de `local`/`public`.

### Consideraciones para Multi-Tenancy con S3:

- Los archivos de cada tenant deben seguir almacenándose con prefijos por tenant en S3 (ej. `{tenant_id}/avatar.jpg`).
- La configuración del `FilesystemTenancyBootstrapper` en `config/tenancy.php` debe ajustarse para usar el disco S3 con un prefijo por tenant.

---

## 7. Dominios

### 7.1 Dominio automático en Laravel Cloud

- Cada environment recibe un subdominio gratuito en `*.laravel.cloud`.
- Estos dominios tienen el header `X-Robots-Tag: noindex, nofollow` (no indexados por Google).
- Útiles para staging/preview environments.

### 7.2 Dominio personalizado ✅ PARA REDIL CLOUD

Para configurar `redilcloud.com` y `*.redilcloud.com`:

1. En el dashboard: **Environment → Domains → Add Custom Domain**.
2. Añadir el dominio raíz: `redilcloud.com`
3. Añadir el wildcard: `*.redilcloud.com` (para los subdominios de tenants)
4. Configurar los registros DNS en tu proveedor:
   - **Registro Origin** (A o CNAME) → apunta a Laravel Cloud
   - **Registro de Verificación de Propiedad** → registro TXT
   - **Registros de Validación SSL** → para el certificado TLS

### 7.3 Wildcard Domains (Crítico para Multi-Tenancy)

Para que `iglesia1.redilcloud.com`, `iglesia2.redilcloud.com`, etc. funcionen:

- Registrar `*.redilcloud.com` en el dashboard de dominios.
- Configurar también `redilcloud.com` (el root).
- Agregar el **DCV Delegation Record** (`_acme-challenge CNAME`) para que Laravel Cloud pueda renovar los certificados SSL wildcard automáticamente.

**Límites de dominios personalizados por plan:**
| Plan | Dominios por environment |
|---|---|
| Starter | 1 dominio |
| Growth | Hasta 5 dominios |
| Business | Hasta 200 dominios |
| Enterprise | Personalizado |

### 7.4 Estados de verificación de un dominio

| Estado                  | Significado                                          |
| ----------------------- | ---------------------------------------------------- |
| **Verifying ownership** | Esperando que los registros DNS se propaguen         |
| **Verifying**           | Propiedad y SSL confirmados, esperando DNS origen    |
| **Connected**           | Todo verificado, dominio activo                      |
| **Timed out**           | Expiró el tiempo; revisar registros DNS y reintentar |

---

## 8. Escalado y Compute

### 8.1 Tipos de Compute

- **Flex**: Serverless, escala automáticamente desde `0` (ideal para staging y desarrollo).
  - Se puede configurar **hibernación** para ambientes de desarrollo (reduce costos a casi $0 cuando no hay tráfico).
- **Pro**: Servidores dedicados, recomendados para producción con tráfico constante.

### 8.2 Replicación de Environments

Al replicar un environment, Laravel Cloud duplica:

- ✅ Compute (tamaño, autoscaling, hibernación, Octane, Inertia, procesos en segundo plano)
- ✅ Database: Usa el mismo cluster de DB pero crea un **nuevo schema lógico**
- ✅ Cache: Usa el mismo KV Store pero agrega un `CACHE_PREFIX` único
- ❌ Object Storage: No se replica

---

## 9. Planes y Precios

| Plan           | Descripción                    | Mejor para                         |
| -------------- | ------------------------------ | ---------------------------------- |
| **Starter**    | Básico, 1 dominio por env      | Proyectos pequeños / desarrollo    |
| **Growth**     | Hasta 5 dominios, Preview Envs | Proyectos en crecimiento           |
| **Business**   | Hasta 200 dominios             | SaaS multi-tenant como REDIL Cloud |
| **Enterprise** | Personalizado                  | Grandes corporaciones              |

> **Recomendación para REDIL Cloud:** El plan **Business** es el ideal una vez en producción, ya que permite hasta 200 dominios por environment (necesario para los subdominios de iglesias). Para desarrollo, el plan **Starter** o **Growth** es suficiente.

### 9.1 Recursos disponibles en todos los planes

- Laravel MySQL (managed)
- Serverless Postgres (Neon)
- Laravel Valkey (Redis-compatible)
- Redis by Upstash
- Laravel Object Storage (Cloudflare R2)
- Laravel Reverb (WebSockets)

---

## 10. Laravel Reverb (WebSockets)

- Servidor de WebSockets oficial de Laravel, integrado en la plataforma.
- Útil para funcionalidades en tiempo real en REDIL Cloud (notificaciones, chat de grupos, actualizaciones de asistencia en vivo).
- Se integra directamente con **Laravel Broadcasting** y **Livewire**.

---

## 11. Comandos Artisan desde el Dashboard

Laravel Cloud permite ejecutar comandos `artisan` directamente desde el dashboard del environment:

```bash
# Ejemplos útiles para REDIL Cloud
php artisan cache:clear
php artisan tenants:migrate
php artisan tenants:seed --tenants=iglesia1
```

> Los comandos se ejecutan en el contexto del environment seleccionado.

---

## 12. Checklist de Migración: REDIL Cloud → Laravel Cloud

- [ ] **Base de Datos**: Migrar `redil2024_db` (central) a Laravel Cloud PostgreSQL.
- [ ] **Bases de Datos de Tenants**: Migrar cada `tenant{ID}` a Laravel Cloud PostgreSQL.
- [ ] **Variables de Entorno**: Configurar todas las variables del `.env` en el dashboard.
- [ ] **Object Storage**: Migrar archivos de `storage/app/` a un bucket S3 (Cloudflare R2).
- [ ] **Cache Store**: Cambiar `CACHE_STORE` a `redis` (Valkey) para habilitar Cache Tags.
- [ ] **Queue Connection**: Cambiar `QUEUE_CONNECTION` a `redis` para mayor rendimiento.
- [ ] **Reactivar CacheTenancyBootstrapper**: Una vez con Redis activo, descomentar en `config/tenancy.php`.
- [ ] **Dominio Wildcard**: Configurar `*.redilcloud.com` y los registros DNS correctos.
- [ ] **Build Commands**: Configurar `composer install --no-dev && php artisan optimize`.
- [ ] **Deploy Commands**: Incluir `php artisan migrate --force` y `php artisan tenants:migrate`.
- [ ] **Reverb**: Evaluar agregar WebSockets para notificaciones en tiempo real.

---

## 13. Referencias Rápidas

| Recurso                 | URL                                                              |
| ----------------------- | ---------------------------------------------------------------- |
| Documentación principal | https://cloud.laravel.com/docs/intro                             |
| Environments            | https://cloud.laravel.com/docs/environments                      |
| Bases de datos MySQL    | https://cloud.laravel.com/docs/resources/databases/laravel-mysql |
| Object Storage          | https://cloud.laravel.com/docs/resources/object-storage          |
| Dominios                | https://cloud.laravel.com/docs/domains                           |
| Precios                 | https://cloud.laravel.com/docs/pricing                           |
| Organizaciones          | https://cloud.laravel.com/docs/organizations                     |
