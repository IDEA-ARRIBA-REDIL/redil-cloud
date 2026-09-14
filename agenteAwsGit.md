# Protocolo Maestro: Despliegue Git y Conexión AWS EC2 — `agenteAwsGit`

Este archivo es una guía y protocolo operativo autosuficiente. Si eres un agente de IA en una nueva sesión o un desarrollador trabajando en este proyecto, **lee este documento para entender cómo realizar cambios en local, subirlos a GitHub y desplegarlos inmediatamente en el servidor AWS EC2**.

---

## 1. Arquitectura y Parámetros del Entorno

El flujo de trabajo conecta el desarrollo en tu máquina local con el repositorio remoto y el servidor de producción/pruebas:

```
[Entorno Local (Mac)] ──(git push)──> [GitHub: main] ──(git pull via SSH)──> [Servidor AWS EC2]
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
> El comando `aws ec2-instance-connect ssh` por defecto abre una terminal interactiva (TTY). Como agente, **NUNCA** debes invocar el comando de forma interactiva esperando un prompt. Siempre debes canalizar los comandos vía tubería (`echo "comando" | aws ec2-instance-connect ssh ...`).

> [!WARNING]
> **Permisos de Red (BypassSandbox)**:
> Cualquier comando que involucre comunicación con el exterior (`git push`, `git fetch`, `aws ec2-instance-connect`) requiere acceso a red y lectura de credenciales en `~/.aws`. Por lo tanto, en Antigravity se debe ejecutar con `BypassSandbox: true`.

1. **No usar `git add .` a ciegas**: Agrega únicamente los archivos editados relacionados con la tarea puntual para no arrastrar archivos temporales o modificaciones de otros módulos en desarrollo.
2. **Formateo obligatorio con Laravel Pint**: Si modificas archivos `.php`, corre siempre en local:
   ```bash
   vendor/bin/pint --dirty --format agent
   ```
3. **No romper la base de datos**: Nunca corras `php artisan migrate:fresh` en el servidor. Si hay migraciones de tenant pendientes (`database/migrations/tenant/`), consulta o corre `php artisan tenants:migrate`.

---

## 3. Flujo de Trabajo Paso a Paso

### Paso 1: Verificación y Preparación en Local
Revisa qué archivos se han cambiado y asegúrate de que el código PHP cumpla con el estándar:

```bash
# 1. Revisar estado de archivos
git status -s

# 2. Formatear código PHP modificado
vendor/bin/pint --dirty --format agent

# 3. Inspeccionar cambios puntuales
git diff path/al/archivo.php
```

---

### Paso 2: Commit y Subida a GitHub
Prepara y sube los cambios al repositorio central:

```bash
# 1. Preparar únicamente los archivos del cambio
git add ruta/archivo1.php ruta/archivo2.blade.php

# 2. Crear el commit semántico
git commit -m "feat/fix(modulo): descripcion concisa del cambio"

# 3. Subir a la rama main en GitHub
git push origin main
```

---

### Paso 3: Despliegue en el Servidor AWS EC2
Ejecuta la actualización en el servidor mediante el túnel SSH de EC2 Instance Connect:

```bash
# 1. Descargar los últimos cambios
echo "cd /var/www/html/redil-cloud && git pull origin main" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu

# 2. Limpiar caché de vistas Blade compiladas
echo "cd /var/www/html/redil-cloud && php artisan view:clear" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
```

---

### Paso 4 (Condicional): Limpieza Completa o Migraciones
Si el cambio incluyó rutas, configuraciones, eventos o migraciones de base de datos:

```bash
# Si cambiaste configuraciones o rutas:
echo "cd /var/www/html/redil-cloud && php artisan optimize:clear" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu

# Si agregaste migraciones centrales:
echo "cd /var/www/html/redil-cloud && php artisan migrate --force" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu

# Si agregaste migraciones tenant (database/migrations/tenant/):
echo "cd /var/www/html/redil-cloud && php artisan tenants:migrate" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
```

---

## 4. Comando Rápido One-Liner (Push + Pull + Limpieza)

Para un despliegue exprés una vez hechos los commits locales:

```bash
git push origin main && echo "cd /var/www/html/redil-cloud && git pull origin main && php artisan view:clear" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
```

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

### Conflicto de Merge al hacer `git pull` en el Servidor
*   **Causa**: Algún archivo fue editado directamente en el servidor (ej. por sincronización manual SFTP).
*   **Solución**:
    1. Revisa qué archivo genera el conflicto:
       ```bash
       echo "cd /var/www/html/redil-cloud && git status -s" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
       ```
    2. Si los cambios del repositorio deben prevalecer:
       ```bash
       echo "cd /var/www/html/redil-cloud && git checkout -- <archivo-en-conflicto> && git pull origin main" | aws ec2-instance-connect ssh --instance-id i-0a846e8fb50d9a003 --os-user ubuntu
       ```
