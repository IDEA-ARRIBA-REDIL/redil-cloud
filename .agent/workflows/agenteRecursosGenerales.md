---
description: Carga el contexto y memoria del Agente de Recursos Generales
---

# Agente de Recursos Generales de la Escuela

Este agente se encarga de asistir en el desarrollo, mantenimiento y soporte del sistema de administración y carga de recursos generales para las escuelas en la arquitectura Multi-Tenant, garantizando un flujo interactivo y seguro desde el panel de administración.

---

## 1. Arquitectura y Flujo de Subida (Panel de Administración)

### A. Subida del Archivo Adjunto con Alpine.js
1. **Componente de la Vista (`gestion-recursos-generales.blade.php`):**
   - Utiliza una directiva interactiva de Alpine.js (`x-data`) para aislar la lógica de subida y evitar recargas complejas de Livewire.
   - Realiza una petición `fetch` asíncrona (POST) a la ruta `escuela.recursos-generales.upload` pasando el archivo mediante un objeto `FormData` y el token CSRF obtenido de la cabecera del documento.
   - Sincroniza dinámicamente el resultado exitoso con el backend de Livewire configurando `$wire.set('nombreArchivoSubido', data.nombre)` y `$wire.set('rutaArchivoSubida', data.ruta_relativa)`.
   - Incorpora barras o textos animados "Subiendo archivo..." en tiempo real para optimizar la experiencia de usuario (UX).

2. **Procesamiento en el Controlador (`RecursoGeneralEscuelaController.php`):**
   - El endpoint `uploadArchivoRecursoGeneral` valida la carga (máximo 10MB; tipos válidos: PDF, JPG, JPEG, PNG, Excel, Word, PowerPoint).
   - **Permisos Unix y Tenancy (0755):** Asegura el aislamiento físico almacenando los archivos en la ruta del tenant: `archivos/escuelas/recursos-generales`. 
   - Para evitar errores de permisos tipo `403 Forbidden` comunes en cPanel, se realiza la creación y asignación recursiva de permisos `0755` tanto en las carpetas intermedias del storage como en el archivo final subido con `chmod()`.

### B. Ciclo de Vida y Limpieza (Garbage Collection)
Para no acumular archivos huérfanos o temporales no deseados en el almacenamiento del tenant:
- **Método `eliminarArchivoLocal()`:** Si el administrador cancela la subida en el formulario o pulsa el botón "cerrar" en el archivo temporal cargado, Livewire ejecuta `Storage::disk('public')->delete($this->rutaArchivoSubida)` para borrar físicamente el archivo del disco de manera inmediata.
- **Limpieza al Guardar o Cancelar (`resetInputFields`):** Al guardar correctamente el recurso o cerrar el modal de creación, se limpia el formulario y se verifica si quedó alguna subida temporal activa descartada para eliminarla físicamente del storage.

---

## 2. Acceso y URLs Públicas (Multi-Tenancy)

### A. Helper `tenant_asset()`
En una arquitectura Multi-Tenant, las rutas de storage absoluto no deben generarse usando el método estándar de Laravel `Storage::url()`, ya que apuntan a la carpeta central del servidor, provocando accesos denegados.
- Se utiliza el helper dinámico oficial **`tenant_asset($path)`** pasando la ruta relativa guardada (sin barra diagonal inicial `/`).
- El controlador de recursos del tenant se encarga de servir el archivo de forma transparente a través de `/assets/...`.

### B. Accesor del Modelo (`RecursoGeneralEscuela.php`)
El modelo expone de manera segura la URL del recurso a través de su accesor `archivoUrl`:
```php
public function getArchivoUrlAttribute(): ?string
{
    if ($this->ruta_archivo) {
        return tenant_asset($this->ruta_archivo);
    }
    return null;
}
```

---

## 3. Mapa de Archivos Clave

- **Controlador del Endpoint:** `app/Http/Controllers/RecursoGeneralEscuelaController.php` (Método `uploadArchivoRecursoGeneral`)
- **Ruta del Endpoint:** `routes/app.php` (`escuela.recursos-generales.upload`)
- **Modelo Eloquent:** `app/Models/RecursoGeneralEscuela.php`
- **Controlador Livewire (Admin):** `app/Livewire/Escuelas/GestionRecursosGenerales.php`
- **Vista Livewire (Admin):** `resources/views/livewire/escuelas/gestion-recursos-generales.blade.php`

---

## 4. Reglas Críticas para Desarrolladores

1. **Aislamiento Multi-Tenant:** Jamás utilices `Storage::disk('public')->url()`. Siempre delega la resolución de enlaces de descarga públicos al accesor del modelo que consume `tenant_asset()`.
2. **Seguridad de Directorios:** Forzar siempre recursivamente permisos `0755` con octales limpios al manipular y almacenar archivos en el backend.
3. **Limpieza del Disco:** Asegurar que todo flujo que permita cargas temporales de prueba cuente con una llamada correspondiente a la recolección de basura física (`eliminarArchivoLocal()`) en caso de descarte por parte del usuario.
4. **Formateo PSR-12:** Corre siempre `vendor/bin/pint --dirty --format agent` antes de confirmar cambios en el código PHP.
