# Protocolo Maestro: Despliegue Git y Conexión AWS EC2 — `agenteAwsGit`

Este archivo es una guía y protocolo operativo autosuficiente. Si eres un agente de IA en una nueva sesión o un desarrollador trabajando en este proyecto, **lee este documento para entender cómo realizar cambios en local, subirlos a GitHub y desplegarlos inmediatamente en el servidor AWS EC2**.

---

## 1. Arquitectura y Parámetros del Entorno

El flujo de trabajo conecta el desarrollo en tu máquina local con el repositorio remoto y el servidor de producción/pruebas:

```
[Entorno Local (Mac)] → [rama de integración en GitHub] → [revisión y merge a main]
                                                        → [preflight y despliegue controlado en EC2]
```

### Tabla de Configuración de Infraestructura

| Variable | Valor / Ruta | Descripción |
|---|---|---|
| **ID Instancia AWS** | `i-0a846e8fb50d9a003` | Instancia EC2 donde corre la aplicación |
| **Usuario SSH** | `ubuntu` | Usuario del sistema operativo en el servidor |
| **Región AWS** | `us-east-1` | Región configurada en AWS CLI |
| **Directorio Servidor** | `/var/www/html/redil-cloud` | Ruta absoluta de la aplicación en el servidor |
| **Repositorio Remoto** | `https://github.com/IDEA-ARRIBA-REDIL/redil-cloud.git` | Origen remoto en GitHub |
| **Rama Principal** | `main` | Rama de despliegue |
| **Mecanismo Conexión** | `aws ec2-instance-connect ssh` | Conexión SSH segura sin llaves fijas locales |

---

## 2. Reglas de Oro para el Agente de IA

> [!IMPORTANT]
> **Modo no interactivo para comandos remotos**:
> El comando `aws ec2-instance-connect ssh` por defecto abre una terminal interactiva (TTY). Canaliza comandos preparados y revisados mediante entrada estándar. Comprueba el código de salida; no asumas que un túnel SSH abierto implica que el comando remoto terminó bien.

> [!WARNING]
> **Permisos de red**:
> `git fetch`, `git push` y `aws ec2-instance-connect` requieren acceso de red y credenciales. Usar los permisos que solicite el entorno; esta guía no autoriza saltarse sus controles.

1. **No usar `git add .` a ciegas**: Agrega únicamente los archivos editados relacionados con la tarea puntual para no arrastrar archivos temporales o modificaciones de otros módulos en desarrollo.
2. **Formateo obligatorio con Laravel Pint**: Si modificas archivos `.php`, corre siempre en local:
   ```bash
   vendor/bin/pint --dirty --format agent
   ```
3. **Base de datos**: No ejecutar en EC2 migraciones, seeders, comandos de sincronización de datos ni tareas que escriban en la base por el mero hecho de actualizar código. Si el código nuevo requiere tablas o columnas pendientes, detener el despliegue de ese lote hasta tener un plan de migración autorizado y probado. Nunca ejecutar `migrate:fresh`.
4. **Estado propio de EC2**: Antes de integrar en `main`, comprobar `git status` y comparar los archivos que tocaría el nuevo commit. El 2026-10-06 EC2 tenía cambios locales en `database/seeders/PermisoSeeder.php`, numerosos recursos de `storage/` y datos de seeders; conservarlos. No usar `git checkout --`, `git reset --hard`, `git clean` ni un `stash` masivo como solución automática.
5. **Permisos de Manantial**: El seeder en EC2 selecciona el rol `Super Administrador` y asigna permisos de gamificación; el archivo local utiliza `Super Administrador Prueba` y difiere en asignaciones. No reemplazar ni ejecutar el seeder en EC2 hasta reconciliar esa diferencia y validar su efecto en los roles existentes.

---

## 3. Flujo de Trabajo Paso a Paso

### Paso 1: Verificación y Preparación en Local
Revisa qué archivos se han cambiado y asegúrate de que el código PHP cumpla con el estándar:

```bash
# 1. Revisar rama, estado y commits nuevos en GitHub
git status -sb
git fetch origin main

# 2. Formatear código PHP del lote de manera controlada
vendor/bin/pint --dirty --format agent

# 3. Inspeccionar cambios y comprobar sintaxis/pruebas pertinentes
git diff path/al/archivo.php
```

---

### Paso 2: Rama, commit y revisión en GitHub
Crear una rama de integración a partir de `main` actualizado. Los cambios pendientes que ya están en el árbol de trabajo se conservan al crear la rama. Revisar la lista antes de agregar: excluir `.ftpquota`, credenciales, `storage/`, logs, artefactos temporales y cambios ajenos al lote. Los archivos nuevos se agregan explícitamente. Subir primero la rama, revisar su diff y luego integrar en `main` por fast-forward o PR. Confirmar que `origin/main` sigue en el commit esperado antes del merge. No usar `git add .` ni `git push origin main` como atajo desde una rama sin revisar.

```bash
git switch -c codex/integracion-ec2-AAAAMMDD
git add ruta/archivo1.php ruta/archivo2.blade.php
git diff --cached --check
git diff --cached --stat
git commit -m "feat(modulos): integrar cambios revisados"
git push -u origin codex/integracion-ec2-AAAAMMDD
```

---

### Paso 3: Preflight de EC2
Antes de actualizar el servidor, confirmar la rama y commit actuales, el estado de archivos locales, un respaldo recuperable de código y base de datos conforme a su operación, espacio disponible, ventana de mantenimiento y reversión. Comparar especialmente `PermisoSeeder.php` y cualquier ruta de `storage/` incluida en el nuevo commit. Si hay un archivo local que Git reemplazaría, detenerse y resolverlo por archivo preservando su contenido; nunca forzar el `pull`. Verificar también si la versión nueva requiere migraciones: sin autorización para alterar la BD, mantener EC2 en la versión previa o preparar un lote compatible sin esas dependencias.

### Paso 4: Despliegue controlado en el Servidor AWS EC2
Solo después de pasar el preflight, actualizar mediante EC2 Instance Connect. `--ff-only` evita crear merges accidentales en el servidor. Ejecutar una comprobación de salud y revisar errores/colas tras el cambio.

```bash
echo "cd /var/www/html/redil-cloud && git pull --ff-only origin main" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
echo "cd /var/www/html/redil-cloud && php artisan view:clear" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
```

### Paso 5: Comandos condicionales y aprobados
Si cambian rutas o configuración, determinar primero las cachés activas y ejecutar únicamente las limpiezas necesarias. Migraciones y seeders quedan fuera de este procedimiento automático; requieren una liberación separada, probada y autorizada.

```bash
# Si cambiaste configuraciones o rutas:
echo "cd /var/www/html/redil-cloud && php artisan optimize:clear" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu

```

---

## 4. Regla para publicación rápida

No combinar `push`, `pull` y limpieza de cachés en un solo comando. Cada transición requiere comprobar el resultado de la anterior; omitir el preflight puede sobrescribir o bloquear cambios locales de EC2.

---

## 5. Comandos de Diagnóstico Remoto en el Servidor

Úsalos cuando necesites verificar el estado del servidor sin entrar interactivamente:

### Inspeccionar Estado de Git en Servidor
```bash
echo "cd /var/www/html/redil-cloud && git status -s && git log -1 --oneline" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
```

### Monitorear Logs de Errores de Laravel
```bash
echo "tail -n 50 /var/www/html/redil-cloud/storage/logs/laravel.log" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
```

### Consultar Recursos (Memoria y Espacio en Disco)
```bash
echo "df -h / && free -m" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
```

---

## 6. Resolución de Problemas Comunes

### Conflicto al actualizar el Servidor
Si Git informa que un archivo local sería reemplazado, registrar el archivo y comparar las dos versiones. Respaldar su contenido fuera del árbol de trabajo antes de cualquier reconciliación. Para `PermisoSeeder.php`, conservar los roles y permisos actualmente configurados en EC2; no asumir que el seeder local puede sustituirlo. Los archivos de `storage/` contienen datos propios del servidor y no se descartan para conseguir un árbol Git limpio. Resolver un conflicto exige una decisión por archivo y nueva verificación de integridad antes de reintentar el despliegue.
