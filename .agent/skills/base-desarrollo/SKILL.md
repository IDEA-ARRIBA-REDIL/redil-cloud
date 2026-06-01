---
name: base-desarrollo
description: >
  Protocolo base de desarrollo para REDIL Cloud. Define persona, idioma, convenciones de código,
  estándares técnicos y reglas de UI. Se activa automáticamente en cualquier tarea de programación
  que involucre Laravel, Livewire, Alpine.js, JavaScript o Bootstrap.
---

# Protocolo Base de Desarrollo — REDIL Cloud

## 1. Persona del Agente

- **Rol**: Experto en Laravel 12, Livewire 3, Alpine.js, Javascript, Bootstrap 5.
- **Idioma**: Español nativo.

## 2. Convenciones de Código

- **Variables/Funciones**: `camelCase` (ej. `calcularTotal`, `$usuarioActivo`).
- **Comentarios**: Obligatorios y enumerados para bloques lógicos complejos.
- **Artisan (Base de datos)**: NO ejecutar `php artisan migrate`, `php artisan tenant:migrate`, ni cualquier otro comando de ejecución de base de datos. El desarrollador los ejecuta manualmente en su servidor.

## 3. Protocolo de Memoria ("El Cerebro")

Cada vez que se active este modo, el agente DEBE:

1. **Leer Instrucciones Maestras**: `view_file acciones del agente.md`
2. **Cargar Contexto Actual**: `view_file _docs_agente/estado_actual.md`
3. **Consultar Mapa (Si es necesario)**: `view_file _docs_agente/mapa_sistema.md` (Solo si se requiere entender arquitectura).

## 4. Ejecución de Tareas

- Al terminar una tarea significativa, el agente DEBE actualizar `_docs_agente/estado_actual.md` con los avances y los nuevos pasos pendientes.

## 5. Estándares Técnicos Aprobados (Enero 2026)

### 5.1. Diseño de Módulos (Procesos y Tareas)

- Sin `card-header` ni wrappers antiguos. Títulos controlados por el componente.
- Tablas con contorno punteado (`dashed-border`) y botones redondeados (`rounded-pill`).
- Badges con colores dinámicos (`bg-{{ $color }}`).

### 5.2. Select2

- Inicialización manual en bloques `@script` con JQuery.
- **IDs Únicos**: Usar prefijos (ej. `#select-materia-...`) para evitar conflictos en vistas compuestas.
- **Persistencia**: Usar `wire:ignore` en el contenedor del select.
- **Reset**: Escuchar eventos de Livewire para limpiar selección (`.val(null).trigger('change.select2')`).

### 5.3. Visibilidad con Alpine.js

- Lógica de formulario: `x-data="{ formVisible: {{ $lista->count() == 0 ? 'true' : 'false' }} }"`.
- El formulario se muestra automáticamente si la lista está vacía.

### 5.4. Eliminación con SweetAlert2

- **NO USAR** `wire:confirm`.
- Crear función JS global (ej. `window.confirmarEliminacion...`) que dispare `Swal.fire`.
- Al confirmar, llamar al método Livewire: `@this.call('eliminar', id)`.
- Escuchar evento `Livewire.on('msn', ...)` con icono `success` para mostrar alerta final "¡Eliminado!".

## 6. Directrices de Multi-Tenancy (Almacenamiento de Archivos)

Este proyecto es **multi-tenant**. El almacenamiento de imágenes y archivos se divide en tres sistemas que el agente DEBE conocer y respetar.

### 6.1. Los Tres Sistemas de Storage

| Sistema | Cuándo usar | Función/Disco |
|---|---|---|
| **`tenant_asset()`** | Archivos propios del tenant (fotos de usuarios, portadas, banners, archivos subidos) | Helper global: `tenant_asset('ruta/relativa/archivo.png')` |
| **`Storage::disk('global_media')`** | Imágenes por defecto y estáticas compartidas entre TODOS los tenants (defaults, placeholders, íconos del sistema) | Disco: `global_media` → `storage/app/global_media/` |
| **`$configuracion->ruta_almacenamiento`** | Ruta base dinámica del tenant para construir paths al guardar/subir archivos | Prefijo configurable por tenant |

### 6.2. Lectura / Visualización de Assets (Accessors en Modelos)

Al crear un **accessor** (`get___Attribute`) para URL de imagen/archivo, SIEMPRE seguir este patrón de prioridad:

1. **Si el registro tiene valor propio** → usar `tenant_asset('ruta/subcarpeta/' . $this->campo)`.
2. **Fallback a imagen por defecto** → usar `Storage::disk('global_media')->url('carpeta/default.png')`.

```php
// ✅ PATRÓN CORRECTO — Ejemplo: Modelo con portada
public function getPortadaUrlAttribute(): string
{
    if ($this->portada && $this->portada !== '' && $this->portada !== 'default.png') {
        return tenant_asset('img/mi-modulo/' . $this->portada);
    }
    return Storage::disk('global_media')->url('mi-modulo/default.png');
}
```

**Reglas estrictas:**
- NUNCA usar `asset()` ni `Storage::url()` directamente para archivos del tenant.
- NUNCA hardcodear la URL del dominio.
- SIEMPRE validar que el campo no sea vacío ni sea el nombre del default antes de llamar `tenant_asset()`.

### 6.3. Subida / Guardado de Archivos (Upload)

Al **subir archivos** en componentes Livewire o Controllers, seguir este patrón:

```php
// ✅ PATRÓN CORRECTO — Subida de imagen en Livewire
use Livewire\WithFileUploads;

// 1. Definir directorio relativo SIN $configuracion->ruta_almacenamiento
//    (el tenant ya resuelve su carpeta automáticamente en disco 'public')
$directorio = 'img/mi-modulo/fotos';

// 2. Generar nombre descriptivo único
$nombreArchivo = Str::slug($this->nombre) . '-' . time() . '.' . $this->foto->getClientOriginalExtension();

// 3. Guardar en disco 'public' (el tenant lo resuelve)
$this->foto->storeAs($directorio, $nombreArchivo, 'public');

// 4. En BD guardar SOLO el nombre del archivo, NO la ruta completa
$modelo->foto = $nombreArchivo;
```

**Cuando se usa `$configuracion->ruta_almacenamiento`** (patrón legacy en Controllers):
```php
// Patrón con ruta_almacenamiento (usado en Controllers tradicionales)
$path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/mi-modulo/');
```

### 6.4. Eliminación de Archivos Previos

Al actualizar una imagen/archivo, SIEMPRE eliminar el archivo anterior:

```php
// ✅ Eliminar archivo anterior antes de guardar el nuevo
if ($modelo->foto && $modelo->foto !== 'default.png') {
    Storage::disk('public')->delete('img/mi-modulo/fotos/' . $modelo->foto);
}
```

### 6.5. En Vistas Blade

```blade
{{-- ✅ CORRECTO: Usar el accessor del modelo --}}
<img src="{{ $usuario->foto_url }}" alt="Foto">

{{-- ✅ CORRECTO: Imagen global (placeholder, ícono del sistema) --}}
<img src="{{ Storage::disk('global_media')->url('placeholder.jpg') }}" alt="Placeholder">

{{-- ❌ INCORRECTO: Nunca usar asset() para archivos del tenant --}}
<img src="{{ asset('storage/img/usuario/' . $foto) }}">
```

### 6.6. Resumen Rápido de Decisión

```
¿El archivo pertenece a UN tenant específico?
  └─ SÍ → tenant_asset() para leer, storeAs(..., 'public') para guardar
  └─ NO (es un default/placeholder global)
       └─ Storage::disk('global_media')->url('...')
```

## 7. Entorno y Flujo de Trabajo (Local vs VPS)

El entorno de desarrollo local (Mac) se utiliza únicamente para escribir código. La ejecución, base de datos y pruebas ocurren en un servidor VPS remoto al cual se sincroniza el código vía un plugin SFTP de VS Code / Antigravity IDE (`.vscode/sftp.json`).

### 7.1. Restricciones de Comandos (Terminal)

Debido a la arquitectura separada, el agente tiene prohibido ejecutar ciertos comandos localmente ya que no tendrían efecto en el servidor de pruebas:

- **NO ejecutar comandos de Artisan pesados o de BD:** `php artisan tinker`, `php artisan migrate`, `php artisan optimize`, `php artisan cache:clear`, etc.
  - *Alternativa:* Si se necesita ejecutar uno de estos comandos para que el código funcione, el agente **DEBE notificar al usuario** en su respuesta indicando exactamente qué comando debe correr manualmente en el servidor VPS.
- **NO ejecutar comandos de Git:** `git status`, `git commit`, `git push`, etc. El usuario maneja su control de versiones cuando él lo decide.
- **NO ejecutar comandos de NPM de forma automática:** `npm install`, `npm run dev`, `npm update`, etc. Debido a las recientes alertas de seguridad en paquetes y scripts maliciosos de la comunidad, el agente NUNCA debe ejecutar herramientas de Node/NPM. Cualquier instalación o compilación de frontend debe ser notificada al usuario para que la revise y ejecute manualmente (o utilice alternativas más seguras/aisladas como `pnpm`).

### 7.2. Sincronización SFTP e IDE

El agente escribe y guarda los archivos directamente en el disco duro local. Sin embargo, por limitaciones técnicas de la integración:

- El agente **no puede forzar la sincronización del plugin SFTP** automáticamente.
- El agente **no puede abrir archivos como pestañas nuevas** dentro de la interfaz del IDE Antigravity.

*Flujo recomendado:* El agente guardará las modificaciones en el disco. Si el plugin SFTP no detecta automáticamente el cambio por el guardado externo, el usuario deberá abrir el archivo modificado en el IDE y hacer un "Guardar" manual (`Cmd+S`) para forzar la subida al VPS.
