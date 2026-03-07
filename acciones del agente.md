# Guía del Proyecto CRECER para el Agente AI

## 1. Resumen del Proyecto

Este es un sistema de gestión académica y eclesiástica para la institución CRECER.

## 2. Tecnologías Principales

- Backend: Laravel 11
- Frontend: Livewire 3 y Alpine.js
- Build Tool: Vite

## 3. Estructura y Convenciones

### Ubicaciones Clave de Archivos

- **Controladores**: Se encuentran en `app/Http/Controllers`. Hay muchos controladores, organizados tanto en la raíz como en subdirectorios temáticos (ej. `Auth`, `dashboard`, `pages`, `escuela`). Algunos controladores principales son `AlumnoEscuelasController.php`, `GrupoController.php`, `MateriaController.php`, `PeriodoController.php`, `MatriculaController.php`, etc.
- **Componentes de Livewire**: Se encuentran en `app/Livewire`. Cada componente es una sola clase.
- **Modelos**: Se encuentran en `app/Models`.
- **Migraciones de Base de Datos**: Se encuentran en `database/migrations`. La estructura sigue el formato `YYYY_MM_DD_HHMMSS_nombre_de_la_accion.php`. El proyecto tiene un esquema de base de datos extenso con más de 200 migraciones, reflejando su complejidad.
- **Vistas Principales**:
  - Las vistas de componentes de Livewire están en `resources/views/livewire`.
  - El directorio `resources/views/contenido` contiene vistas específicas para `authentications`, `front-pages`, y `paginas`.
- **Rutas**: Las rutas web principales están en `routes/web.php`.

### Lógica de Negocio y Pruebas

- **Lógica de Negocio**: La lógica de negocio compleja se ubica en Clases de Servicio en el directorio `app/Services`.
- **Pruebas**: Las pruebas se escriben con Pest y se encuentran en el directorio `tests/`. Para ejecutar las pruebas, usa el comando `php artisan test`.

## 4. Estándares de Codificación

- **Idioma**: TODO el código nuevo (variables, funciones, nombres de clases, etc.) DEBE ser escrito en **español**.
- **Estilo de Nomenclatura**: Se DEBE usar **camelCase** para variables y funciones (ej. `function calcularTotal()`, `$nombreCompleto`).
- **Comentarios**: El código DEBE estar bien comentado. Los comentarios deben explicar el "porqué" de la lógica.
- **Comentarios Enumerados**: Para lógica compleja o procesos de varios pasos, se deben usar comentarios enumerados para clarificar el flujo.

  - Ejemplo:

    ```php
    // 1. Validar los datos de entrada del formulario.
    $validatedData = $request->validate([...]);

    // 2. Buscar el registro del alumno.
    $alumno = Alumno::find($id);

    // 3. Actualizar el registro y guardar los cambios.
    $alumno->update($validatedData);
    ```

## 5. Tareas Comunes

- **Crear un nuevo componente Livewire**: Usa el comando `php artisan make:livewire NombreComponente`.

## 6. Sincronización con VPS (Producción/Pruebas)

El proyecto está configurado para sincronizarse automáticamente con un VPS a través del plugin de VS Code 'SFTP' de Natizyskunk. Al guardar un archivo en el entorno de desarrollo local, el plugin SFTP se encarga de subir los cambios al servidor.

- **Importante**: No confundir la sincronización SFTP con el control de versiones en GitHub.

## 7. Protocolo de Memoria "Agente" (CRÍTICO)

Este proyecto depende de una continuidad estricta y de una "memoria explícita".

1.  **Antes de empezar**: Lee siempre `_docs_agente/estado_actual.md` para saber en qué situación exacta se encuentra el proyecto y qué decisiones recientes se han tomado.
2.  **Durante el trabajo**: Si descubres información estructural importante (nuevos servicios, cambios en DB), regístralo en `_docs_agente/mapa_sistema.md`.
3.  **Al finalizar**: Actualiza `_docs_agente/estado_actual.md` eliminando tareas completadas y agregando los siguientes pasos lógicos.

## 8. Control de Versiones (Git)

- **Repositorio Remoto**: Para cualquier operación de `git push`, se DEBE usar siempre la cuenta de GitHub: `https://github.com/IDEA-ARRIBA-REDIL/Crecer.git`.
- **Rama Principal**: Solo se DEBE trabajar y hacer push a la rama **main**. No se deben crear ni utilizar otras ramas.
- **Diferencia de Entornos**:
  - **Sincronización Local -> Server Propio**: Se maneja vía SFTP (automático al guardar).
  - **Control de Versiones**: Se gestiona exclusivamente hacia GitHub vía Git.
- **Flujo de Trabajo**: Siempre realizar `git pull origin main` antes de trabajar para sincronizar cambios remotos y evitar conflictos.
