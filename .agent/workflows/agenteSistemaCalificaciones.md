---
description: Agente para la gestión del Sistema de Calificaciones, Entregas y Visualización de Actividades (Alumno-Maestro) en arquitectura Multi-Tenant.
---

# Agente de Sistema de Calificaciones y Entregas

Este agente se encarga de asistir en el desarrollo, mantenimiento y soporte del sistema de calificaciones, entregas de actividades, subida de archivos adjuntos y visualización/descarga de respuestas tanto desde la perspectiva del Alumno como la del Maestro.

---

## 1. Arquitectura y Flujo de Entregas (Lado Alumno)

### A. Subida del Archivo Adjunto (Frontend & Backend)
1. **Interfaz del Alumno (`calificaciones.blade.php`):**
   - Utiliza un componente inline de Alpine.js (`x-data`) para gestionar el estado de subida y comunicarse de forma reactiva con Livewire a través de `$wire.nombreArchivoSubido`.
   - Se realiza una petición `fetch` asíncrona hacia la ruta `alumnos.uploadArchivoRespuesta` (`AlumnoEscuelasController.php`) enviando el archivo por FormData junto con el `item_id` y `periodo_id`.
   - Tras recibir `{ success: true, nombre: '...' }`, Alpine llama a `$wire.set('nombreArchivoSubido', data.nombre)` para sincronizarlo inmediatamente con el servidor.

2. **Procesamiento de Archivos (`AlumnoEscuelasController.php`):**
   - Los archivos se almacenan en el disco público dentro del directorio del periodo: `/archivos/escuelas/periodo-{$periodoId}/respuestas`.
   - **Permisos Unix (0755):** Para evitar bloqueos `403 Forbidden` en servidores con políticas de `umask` estrictas (como cPanel), se fuerza de manera recursiva la creación de directorios con `0755` y se aplica un `chmod(..., 0755)` explícito tanto sobre la estructura de carpetas como sobre el archivo final subido.

### B. Validación Dinámica de Entregas
Para dar flexibilidad al alumno, la validación en `CalificacionesAlumno.php` es en tiempo de ejecución (método `rules()`):
- **Si hay un archivo adjunto subido** (`$this->nombreArchivoSubido` no está vacío): La respuesta de texto se vuelve opcional (`nullable|string`).
- **Si no hay archivo adjunto:** El texto es estrictamente requerido y debe tener una longitud mínima de 10 caracteres (`required|string|min:10`).

```php
public function rules(): array
{
    $tieneArchivo = !empty($this->nombreArchivoSubido);

    return [
        'respuestaTexto' => $tieneArchivo ? 'nullable|string' : 'required|string|min:10',
    ];
}
```

### C. Guardado y Persistencia en BD
- Al presionar **Guardar Respuesta**, se ejecuta `guardarRespuesta()`.
- Para evitar problemas de hidratación en Livewire, se realiza una consulta directa y fresca a la base de datos buscando si el alumno ya cuenta con un registro en `AlumnoRespuestaItem` para ese ítem.
- Si existe, se actualizan los campos `respuesta_alumno` y `enlace_documento_alumno` de manera explícita y se llama a `$response->save()`. Si no existe, se crea una nueva instancia.

---

## 2. Visualización e Integridad de Rutas (Lado Maestro y Alumno)

### A. El Desafío de la Ruta en Multi-Tenancy (Error 403)
En una arquitectura Multi-Tenant (basada en el paquete `stancl/tenancy`), cada tenant tiene su almacenamiento público aislado físicamente en la carpeta `storage/tenant<id>/app/public/`. 
- **Error anterior:** Utilizar `Storage::disk('public')->url($path)` generaba una URL tipo `/storage/archivos/...`, que apunta al storage central y provoca errores `403 Forbidden` o `404 Not Found` en producción.
- **Solución oficial:** Se utiliza el helper oficial de Tenancy **`tenant_asset($path)`** pasando una ruta relativa sin barra inicial (`/`).

### B. Accesor del Modelo (`AlumnoRespuestaItem.php`)
El método `archivoUrl` resuelve de manera segura la URL pública del documento del alumno consumible por el navegador:
```php
protected function archivoUrl(): Attribute
{
    return Attribute::make(
        get: function () {
            if (!$this->enlace_documento_alumno) {
                return null;
            }

            $item = $this->itemCalificado;
            if (!$item || !$item->materiaPeriodo) {
                return null;
            }
            $periodoId = $item->materiaPeriodo->periodo_id;

            // Ruta relativa al almacenamiento del tenant sin barra inicial
            $rutaRelativa = "archivos/escuelas/periodo-{$periodoId}/respuestas/{$this->enlace_documento_alumno}";

            return tenant_asset($rutaRelativa);
        },
    );
}
```

### C. Interfaz del Maestro (`calificacion-multiple-alumnos.blade.php`)
- Cuando el maestro abre el modal de calificaciones o el detalle de entregas del alumno, puede visualizar la respuesta textual e interactuar con el enlace de descarga generado a través de `$respuesta->archivo_url`.
- Al hacer clic en "Descargar Archivo", se abre de manera fluida en una nueva pestaña apuntando a la ruta del tenant `/assets/archivos/...` que es transmitida correctamente por el Asset Controller del tenant.

---

## 3. Mapa de Archivos Clave

- **Controlador de Subidas:** `app/Http/Controllers/AlumnoEscuelasController.php` (Método `uploadArchivoRespuesta`)
- **Modelos:**
  - `app/Models/AlumnoRespuestaItem.php` (Lógica de URL con `tenant_asset()`)
  - `app/Models/ItemCorteMateriaPeriodo.php`
- **Controlador Livewire (Alumno):** 
  - `app/Livewire/Alumno/CalificacionesAlumno.php` (Componente activo)
  - `app/Livewire/Alumno/Calificaciones.php` (Clase gemela de respaldo)
- **Vista Livewire (Alumno):** `resources/views/livewire/alumno/calificaciones.blade.php` (Modal unificado)
- **Controlador Livewire (Maestro):** `app/Livewire/Maestros/CalificacionMultipleAlumnos.php`
- **Vista Livewire (Maestro):** `resources/views/livewire/maestros/calificacion-multiple-alumnos.blade.php`

---

## 4. Reglas Críticas para Desarrolladores

1. **Evitar Storage::disk('public')->url() para recursos del Tenant:** Siempre utiliza `tenant_asset('ruta/relativa')` (sin barra inicial) para garantizar que los enlaces no den 403 en producción.
2. **Validaciones de Texto y Archivos:** Recuerda siempre emplear el método `rules()` de forma dinámica si hay carga de adjuntos implicada en la vista.
3. **Permisos en Servidores Unix:** Cada vez que guardes o crees directorios para escuelas, asegura los permisos con octales estrictos (`0755`).
4. **Formateo de Código:** Siempre ejecuta `vendor/bin/pint --dirty --format agent` antes de finalizar cambios en archivos PHP para mantener el código perfectamente ordenado.
