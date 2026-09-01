<!-- // turbo-all -->
# Agente de Tiempo con Dios

Este agente se encarga de la gestión del módulo "Mi Tiempo con Dios", un espacio devocional para los usuarios que incluye lectura bíblica, música, racha diaria y registro de reflexiones.

## Contexto del Módulo
El módulo permite a los usuarios realizar un tiempo devocional diario. Está estructurado en secciones y campos dinámicos que se configuran desde la base de datos.

### Componentes Principales
- **Modelo**: `App\Models\TiempoConDios` - Registra la entrada diaria del usuario.
- **Relaciones**:
    - Pertenece a un `User`.
    - Tiene muchos `CampoTiempoConDios` a través de una tabla pivote `campo_tiempo_con_dios` que guarda el `valor` encriptado.
- **Controlador**: `App\Http\Controllers\TiempoConDiosController` - Maneja el flujo de historial, creación y resumen.
- **Vistas**: `resources/views/contenido/paginas/tiempo-con-dios/`
- **Livewire**: `app/Livewire/TiempoConDios/` y `resources/views/livewire/tiempo-con-dios/`
    - `ValidarFormulario`: Maneja la validación de pasos.
    - `RachaDiaria`: Muestra el estado de la semana actual (check/uncheck por día).
    - `RachaSemanal`: Muestra el número total de semanas consecutivas completas.
    - `Biblia`: Lector bíblico integrado.
    - `Reproductor`: Gestión de audio.

## Flujo de Trabajo

### 1. Análisis de Estructura Dinámica
Si necesitas modificar los campos o secciones:
- Revisa `App\Models\SeccionTiempoConDios` y `App\Models\CampoTiempoConDios`.
- Los tipos de campo definen qué componente se renderiza (Texto, Imagen, Reproductor, Biblia).

### 2. Validación de Formulario (Multi-step)
La validación se realiza mediante el componente Livewire `ValidarFormulario`:
- El frontend despacha el evento `validar`.
- El componente valida los campos requeridos de la sección actual.
- Si es exitoso, se avanza al siguiente paso en el cliente (JS).

### 3. Encriptación de Datos
**IMPORTANTE**: Los datos de las reflexiones se guardan encriptados en la base de datos por privacidad del usuario.
- Al crear: `Crypt::encryptString($request[$campo->name_id])`.
- Al mostrar resumen: `Crypt::decryptString($campoRelacionado->pivot->valor)`.

### 4. Componentes Especiales
- **Biblia**: Consume una API externa (`https://bible-api.deno.dev`). Permite seleccionar libro, capítulo, versión y resaltar versículos.
- **Reproductor**: Gestiona listas de reproducción de audio para acompañar el tiempo devocional.
- **Rachas**: Se calculan mediante métodos en el modelo `User` (ej: `cantidadRachaSemanal()`).

## Tareas Comunes

### Modificar el Formulario de Creación
- Archivo: `resources/views/contenido/paginas/tiempo-con-dios/nuevo.blade.php`
- Lógica JS: Maneja el paso a paso y la integración con Livewire para validación.

### Ajustar el Resumen / Historial
- Archivo: `resources/views/contenido/paginas/tiempo-con-dios/resumen.blade.php`
- Asegúrate de manejar correctamente la desencriptación de los valores.

### Debugging de Rachas
- Archivos Livewire: `RachaDiaria.php` y `RachaSemanal.php`.
- Lógica en `User.php`:
    - `rachaSemanalActual()`: Retorna una colección con el estado (visto/no visto) de cada día de la semana actual (Lunes-Domingo).
    - `obtenerRachaSemanalActual()`: Calcula cuántas semanas completas (7/7 días) lleva el usuario de racha.
    - `cantidadRachaDiaria()`: Calcula los días consecutivos de racha hasta hoy.

### Debugging de la Biblia
- Archivo: `app/Livewire/TiempoConDios/Biblia.php`
- Revisa las peticiones HTTP y el manejo de excepciones si la API falla.

## Comandos Útiles
- Listar secciones: `php artisan tinker --execute="print_r(App\Models\SeccionTiempoConDios::all()->toArray())"`
- Ver campos de una sección: `php artisan tinker --execute="print_r(App\Models\CampoTiempoConDios::where('seccion_tiempo_con_dios_id', 1)->get()->toArray())"`
