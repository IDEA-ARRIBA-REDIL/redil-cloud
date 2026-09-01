---
description: Carga el contexto y memoria del Agente de Materias
---

# Agente de Materias

Este agente se encarga de asistir en el desarrollo, mantenimiento y soporte del sistema de gestión de materias dentro de la arquitectura Multi-Tenant, incluyendo la configuración, portadas y relaciones con pasos de crecimiento y tareas.

---

## 1. Arquitectura y Flujo de Portadas

### A. Subida de Imagen con Cropper.js
1. **Componente de la Vista (`crear-materia.blade.php`, `gestionar-materia.blade.php`):**
   - Utiliza Cropper.js para recortar imágenes con relación de aspecto 1693/376.
   - Al hacer click en "Guardar" del modal, el Cropper convierte la imagen recortada a Blob.
   - Realiza una petición `fetch` asíncrona (POST) a la ruta `materias.uploadPortada` pasando el archivo mediante `FormData`.
   - El endpoint retorna el nombre del archivo generado, que se almacena en un input hidden `#portada-nombre`.
   - Se muestra un indicador de carga "Subiendo imagen..." durante la subida (`#upload-status`).
   - Al enviar el formulario principal, solo se envía el nombre del archivo (no la imagen completa).

2. **Procesamiento en el Controlador (`MateriaController.php`):**
   - **Endpoint `uploadPortada()`:** Recibe la imagen recortada via fetch, la almacena en el storage del tenant y retorna el nombre del archivo.
   - Los métodos `guardar()` y `actualizar()` reciben el nombre del archivo (`portada_nombre`) del request.
   - **Directorio de almacenamiento:** `archivos/escuelas/materias` (dentro del storage del tenant).
   - **Nombre del archivo:** `portada-materia-{uniqid}-{timestamp}.{ext}` (nombre único para evitar colisiones).
   - **Permisos Unix:** Se aplican permisos `0755` recursivamente en las carpetas intermedias y en el archivo final.
   - **Eliminación de portada anterior:** Al actualizar, se elimina la portada anterior del storage si existe y no es `default.png`.

### B. Almacenamiento en Base de Datos
- La columna `portada` en la tabla `materias` almacena **solo el nombre del archivo** (ej: `portada-materia-6a0d114d68d4f-1779241293.png`).
- No se almacena la ruta completa en la base de datos.
- El campo tiene un valor por defecto de `default.png` en la migración.

### C. Flujo de Creación (`guardar()`)
1. Se crea la materia con todos sus campos de configuración.
2. Si `portada_nombre` tiene valor, se asigna el nombre del archivo al campo `portada`.
3. Se guardan las relaciones (pasos de crecimiento, prerrequisitos, tareas).

### D. Flujo de Actualización (`actualizar()`)
1. Se actualizan los campos básicos de la materia.
2. Si `portada_nombre` tiene valor:
   - Se elimina la portada anterior del storage (si existe y no es `default.png`).
   - Se asigna el nuevo nombre del archivo al campo `portada`.
3. Se actualizan las relaciones.

---

## 2. Acceso y URLs Públicas (Multi-Tenancy)

### A. Accesor del Modelo (`Materia.php`)
El modelo expone de manera segura la URL de la portada a través de su accesor `portada_url`:
```php
public function getPortadaUrlAttribute(): ?string
{
    if ($this->portada && $this->portada !== 'default.png') {
        return tenant_asset('archivos/escuelas/materias/'.$this->portada);
    }

    return null;
}
```

### B. Uso en Vistas
- Se utiliza `$materia->portada_url` para obtener la URL completa de la portada.
- Si `portada_url` es null (imagen por defecto), se usa un fallback a una imagen placeholder del sistema.
- **Nunca** usar `Storage::disk('public')->url()` directamente en las vistas.
- **Patrón recomendado:** `$materia->portada_url ?? asset('storage/global/img/otros/placeholder.jpg')`

---

## 3. Mapa de Archivos Clave

### A. Controlador
- **Archivo:** `app/Http/Controllers/MateriaController.php`
  - `uploadPortada()`: Endpoint para subir imagen de portada via fetch async (línea 629)
  - `guardar()`: Crea una nueva materia, recibe `portada_nombre` del request (línea 107)
  - `actualizar()`: Actualiza materia y su portada, elimina imagen anterior si existe (línea 369)

### B. Rutas
- **Archivo:** `routes/app.php`
  - `POST /materias/upload-portada` → `MateriaController@uploadPortada` (nombre: `materias.uploadPortada`)
  - **Importante:** La ruta de upload debe estar ANTES de `POST /materias/{materia}` para evitar conflicto de parámetros.

### C. Modelo
- **Archivo:** `app/Models/Materia.php`
  - Accesor: `getPortadaUrlAttribute()` → retorna URL completa con `tenant_asset()`
  - Relaciones: `pasosCrecimiento()`, `tareasRequisito()`, `tareasCulminadas()`, `prerrequisitosMaterias()`, `procesosPrerrequisito()`, `escuela()`, `nivel()`, `materiasPeriodo()`

### D. Vistas
- `resources/views/contenido/paginas/escuelas/materias/crear-materia.blade.php`
- `resources/views/contenido/paginas/escuelas/materias/gestionar-materia.blade.php`
- `resources/views/contenido/paginas/escuelas/materias-asociadas.blade.php`
- `resources/views/livewire/escuelas/materia-periodo.blade.php`

### E. Migración
- **Archivo:** `database/migrations/tenant/2025_03_19_162323_create_materias_table.php`
  - Columna `portada`: `string('portada', 500)->default('default.png')->nullable()`

---

## 4. Relaciones del Modelo Materia

### A. Pasos de Crecimiento
- `pasosCrecimiento()`: BelongsToMany con `PasoCrecimiento` a través de `materia_paso_crecimiento`
  - Pivot: `estado`, `al_iniciar`, `estado_paso_crecimiento_usuario_id`, `indice`
  - `al_iniciar = 1`: Pasos que se asignan al iniciar la materia
  - `al_iniciar = 0`: Pasos que se asignan al culminar la materia

### B. Tareas
- `tareasRequisito()`: HasMany con `MateriaTareaRequisito`
- `tareasCulminadas()`: HasMany con `MateriaTareaCulminada`

### C. Prerrequisitos
- `prerrequisitosMaterias()`: BelongsToMany con `Materia` (auto-relación a través de `materia_prerrequisito`)
- `procesosPrerrequisito()`: BelongsToMany con `PasoCrecimiento` (a través de `materia_proceso_prerrequisito`)

### D. Jerarquía
- `escuela()`: BelongsTo con `Escuela`
- `nivel()`: BelongsTo con `NivelEscuela`
- `materiasPeriodo()`: HasMany con `MateriaPeriodo`
- `itemPlantillas()`: HasMany con `ItemPlantilla`

---

## 5. Configuración de la Materia

### A. Campos de Configuración
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `habilitar_calificaciones` | boolean | Habilita sistema de calificaciones |
| `habilitar_asistencias` | boolean | Habilita control de asistencias |
| `habilitar_inasistencias` | boolean | Habilita alerta de inasistencias |
| `habilitar_traslado` | boolean | Permite traslados entre horarios |
| `caracter_obligatorio` | boolean | Define si la materia es obligatoria |
| `asistencias_minimas` | integer | Mínimo de asistencias requeridas |
| `asistencias_minima_alerta` | integer | Umbral para alerta de inasistencias |
| `limite_reporte_asistencias` | integer | Límite total de reportes |
| `tiene_dia_limite` | boolean | Si tiene día límite semanal |
| `dia_limite_reporte` | integer | Día límite (0=Domingo, 6=Sábado) |
| `cantidad_limite_reportes_semana` | integer | Reportes permitidos por semana |
| `dias_plazo_reporte` | integer | Días de plazo para reportar |
| `tipo_usuario_inicial_id` | integer | Tipo de usuario al matricular |
| `tipo_usuario_objetivo_id` | integer | Tipo de usuario al aprobar la materia |
| `portada` | string | Nombre del archivo de portada (500 chars max) |

### B. Herencia de Configuración
- Si la materia tiene `nivel_id`, hereda la configuración del nivel asociado.
- El método `getConfigProp()` maneja esta lógica de herencia.
- Los accesorios como `getHabilitarAsistenciasAttribute()` usan `getConfigProp()` automáticamente.

---

## 6. Reglas Críticas para Desarrolladores

1. **Aislamiento Multi-Tenant:** Jamás utilices `Storage::disk('public')->url()`. Siempre delega la resolución de URLs al accesor `portada_url` del modelo.
2. **Seguridad de Directorios:** Forzar siempre recursivamente permisos `0755` con octales limpios al manipular y almacenar archivos en el backend.
3. **Nombres Únicos:** Usar `uniqid()` y `time()` para generar nombres de archivo únicos y evitar colisiones.
4. **Formateo PSR-12:** Corre siempre `vendor/bin/pint --dirty --format agent` antes de confirmar cambios en el código PHP.
5. **Relaciones:** Usar Eloquent relationships en lugar de queries raw. Verificar que los eager loading estén configurados correctamente para evitar N+1 queries.
6. **Configuración Heredada:** Al modificar configuraciones, verificar si la materia tiene nivel asociado para entender qué valores prevalecen.
7. **Rutas Laravel:** Las rutas estáticas (como `/materias/upload-portada`) deben definirse ANTES de las rutas con parámetros dinámicos (como `/materias/{materia}`) para evitar conflictos de coincidencia.
8. **Eliminación de Archivos:** Al actualizar o eliminar, siempre verificar si existe un archivo anterior y eliminarlo del storage para evitar archivos huérfanos.

---

## 7. Vistas que usan Portada

Las siguientes vistas deben usar `$materia->portada_url` (o su equivalente según la relación):

| Vista | Variable | Nota |
|-------|----------|------|
| `materias/crear-materia.blade.php` | N/A (solo sube) | Creación de materia |
| `materias/gestionar-materia.blade.php` | `$materia->portada_url` | Edición de materia |
| `materias-asociadas.blade.php` | `$materia->portada_url` | Listado de materias de escuela |
| `escuelas/materia-periodo.blade.php` | `$materiaPe->materia->portada_url` | Materia en periodo (Livewire) |
| `matricula/matricula-nivel-process.blade.php` | `$materia->portada_url` | Proceso de matrícula |
| `niveles/gestionar-materias-nivel.blade.php` | `$materia->portada_url` | Materias del nivel |
| `matriculas/gestionar-traslados.blade.php` | `$matricula->...->materia->portada_url` | Traslados |
| `matriculas/gestionar-matriculas.blade.php` | `$item->portada_url` | Gestión de matrículas |

---

## 8. Datos de Ejemplo

### A. Valores en Base de Datos
- **Portada por defecto:** `default.png`
- **Portada nueva:** `portada-materia-6a0d114d68d4f-1779241293.png`

### B. URLs Generadas
- **Con accesor:** `$materia->portada_url` → `https://tenant.example.com/assets/archivos/escuelas/materias/portada-materia-xxx.png`
- **Sin portada:** `$materia->portada_url` → `null`
