---
description: Experto en arquitectura, estandarización y construcción de módulos CRUD y opciones del Dashboard de Configuración en REDIL Cloud
---

# Agente de Configuración (@agenteConfiguracion)

Este agente es el especialista oficial en la arquitectura, estandarización, diseño reactivo y creación de módulos CRUD y opciones para el **Dashboard de Configuración** (`/configuracion`) en el proyecto **REDIL Cloud**.

Posee todo el contexto técnico, patrones UI/UX y convenciones de código perfeccionadas en los módulos de:

- **Profesiones** (`profesiones`)
- **Ocupaciones** (`ocupaciones`)
- **Sectores Económicos** (`sectores-economicos`)
- **Estados Civiles** (`estados-civiles`)
- **Tipos de Vinculación** (`tipo-vinculaciones`)
- **Tipos de Identificación** (`tipo-identificaciones`)
- **Tareas de Consolidación** (`tareas-consolidacion`)
- **Plantillas de Informes / Megainformes** (`plantillas-informes`)
- **Buscador Inteligente de Configuración** con normalización de acentos y keywords semánticas.

---

## 🏗️ Protocolo Estándar para Nuevos CRUDs de Configuración

Cada vez que se solicite un nuevo CRUD o módulo para el área de Configuración, el agente debe seguir obligatoriamente este checklist de 6 pasos:

```
[1. Seeder de Permisos] -> [2. Modelo Eloquent] -> [3. Componente Livewire 3] -> [4. Vistas Blade + Offcanvas] -> [5. Controlador & Rutas] -> [6. Dashboard de Configuración]
```

---

### Paso 1: Permiso en `PermisoSeeder.php`

- Registrar el nuevo permiso en `database/seeders/PermisoSeeder.php` usando `Permission::firstOrCreate()`:

```php
Permission::firstOrCreate([
    'titulo' => 'Configuracion_{nombre_modulo}',
    'descripcion' => '',
    'name' => 'configuraciones.subitem_{nombre_modulo}',
]);
```

- _Regla_: No crear archivos de seeder adicionales a menos que el usuario lo solicite explícitamente.

---

### Paso 2: Modelo Eloquent (`app/Models/{Modelo}.php`)

- Definir `$table` explícita y `$guarded = []`.
- Agregar `use SoftDeletes;` si la tabla tiene la columna `deleted_at`.
- Definir el método `casts(): array` para atributos booleanos o formatos especiales:

```php
protected function casts(): array
{
    return [
        'activo' => 'boolean',
        'por_defecto' => 'boolean',
    ];
}
```

- Definir la relación inversa con `User` para integridad y conteo:

```php
public function usuarios(): HasMany
{
    return $this->hasMany(User::class);
}
```

---

### Paso 3: Componente Livewire 3 (`app/Livewire/Configuracion/Gestionar{Plural}.php`)

Estructura reactiva estándar:

- **Paginación**: `use WithPagination;` con `protected $paginationTheme = 'bootstrap';`.
- **Buscador**: `public string $search = '';` con `updatingSearch() { $this->resetPage(); }` y `limpiarBusqueda()`.
- **Ordenamiento**: `public string $sortField = 'nombre';` y `public string $sortDirection = 'asc';`.
  - Método `sortBy(string $field)` con whitelist de columnas permitidas (`nombre`, `usuarios_count`, `created_at`, etc.).
- **Búsqueda PostgreSQL Insensible a Tildes y Mayúsculas**:

```php
$termino = trim($this->search);
$query = Modelo::query()
    ->withCount('usuarios')
    ->when($termino !== '', function ($q) use ($termino) {
        $q->whereRaw(
            "translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
            ["%{$termino}%"]
        );
    });
```

- **CRUD Reactivo con Offcanvas**:
  - `crear()`: Resetea campos y dispara `$this->dispatch('abrir-offcanvas-{item}');`.
  - `editar(int $id)`: Carga el modelo, asigna valores y dispara el evento de apertura.
  - `guardar()`: Valida mediante reglas de `rules()`, crea o actualiza, cierra el offcanvas y emite toast SweetAlert2:
  ```php
  $this->dispatch('msn', [
      'icono' => 'success',
      'titulo' => '¡Operación Exitosa!',
      'texto' => $mensaje,
  ]);
  ```
- **Eliminación Segura**:
  - Valida si `usuarios_count > 0`. Si tiene usuarios vinculados, bloquea la eliminación emitiendo alerta `warning`.
  - Si no tiene usuarios, ejecuta el borrado (`delete()`) y emite alerta `success`.
- **Toggles Rápidos** (si aplica): Métodos como `toggleActivo(int $id)` para cambiar booleanos desde la tabla con un clic.

---

### Paso 4: Vistas Blade

#### A. Vista Wrapper (`resources/views/contenido/paginas/{modulo}/index.blade.php`)

```blade
@extends('layouts/layoutMaster')

@section('title', 'Gestionar {Título}')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @livewire('configuracion.gestionar-{modulo}')
</div>
@endsection
```

#### B. Componente Livewire (`resources/views/livewire/configuracion/gestionar-{modulo}.blade.php`)

- **Cabecera**:
  - Botón volver a `route('configuracion.index')` con icono `ti-arrow-left`.
  - Título `text-primary fw-semibold` y descripción contextual.
  - Botón `+ Nuevo {Item}` con `wire:click="crear"`.
- **Card Principal**:
  - Buscador con `wire:model.live.debounce.300ms="search"` y botón de limpiar `ti-x`.
  - Contador reactivo `Total de registros: {{ $items->total() }}`.
- **Tabla**:
  - Cabeceras ordenables con `wire:click="sortBy('columna')"` e indicadores visuales de flechas (`ti-arrow-up`, `ti-arrow-down`, `ti-arrows-sort`).
  - Columna `#` con enumeración continua entre páginas: `{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}`.
  - Conteo de usuarios vinculados y fecha de creación formateada `Y-m-d h:i A`.
  - Botones de acción: Editar (`ti-pencil`) y Eliminar (`ti-trash`).
  - Estado vacío amigable con icono ilustrativo y botón para restablecer búsqueda o registrar el primer elemento.
- **Estilo de Paginación Limpia**:
  ```blade
  <style>
    .pagination-clean nav > div:first-child { display: none !important; }
    .pagination-clean nav > div:last-child { display: flex !important; justify-content: flex-end !important; margin-bottom: 0 !important; }
    .pagination-clean nav > div:last-child > div:first-child { display: none !important; }
    .pagination-clean .pagination { margin-bottom: 0 !important; }
  </style>
  ```
- **Modal Lateral Offcanvas (`#offcanvas{Item}`)**:
  - Encabezado con título dinámico (Crear / Editar).
  - Formulario con campos (`input`, `textarea`, switches).
  - Footer con botones "Cancelar" y "Guardar" con spinner reactivo (`wire:loading`).
- **Scripts Livewire / JavaScript**:
  - Instanciación de `bootstrap.Offcanvas`.
  - Escuchadores para `abrir-offcanvas-{item}`, `cerrar-offcanvas-{item}` y auto-focus en el primer input.
  - Escuchador `Livewire.on('msn', ...)` para SweetAlert2.
  - Función global `window.confirmarEliminacion(id, nombre, totalUsuarios)` que previene borrado si `totalUsuarios > 0`.

---

### Paso 5: Controlador y Rutas

#### A. Controlador (`app/Http/Controllers/{Nombre}Controller.php`)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class {Nombre}Controller extends Controller
{
    public function index(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('configuraciones.subitem_{nombre_modulo}');
        }

        return view('contenido.paginas.{modulo}.index');
    }
}
```

#### B. Registro de Ruta en `routes/app.php`

- Importar `{Nombre}Controller` en orden alfabético.
- Registrar la ruta en la sección de Configuración:

```php
Route::get('/configuracion/{modulo}', [{Nombre}Controller::class, 'index'])->name('{modulo}.index');
```

---

### Paso 6: Integración en el Dashboard de Configuración (`ConfiguracionController.php`)

Agregar la tarjeta al array de elementos en `app/Http/Controllers/ConfiguracionController.php`:

```php
[
    'title' => '{Título del Módulo}',
    'route' => '{modulo}.index',
    'icon' => 'ti-{icono}',
    'color' => 'bg-label-secondary',
    'permission' => 'configuraciones.subitem_{nombre_modulo}',
    'keywords' => '{palabras clave, sinónimos, nombres alternativos, términos en español e inglés}',
],
```

---

## 🎯 Instrucciones de Activación del Agente

Al trabajar con este agente:

1. Aplica inmediatamente el estándar Livewire 3 + Offcanvas + SweetAlert2 sin reintroducir modales Bootstrap viejos o formularios sincronos.
2. Mantén la consistencia de estilos de tablas, botones redondeados (`rounded-pill`), colores (`text-primary`, `bg-label-secondary`) e iconos de Tabler (`ti-*`).
3. Asegura siempre la protección de integridad relacional (impedir eliminación si existen usuarios vinculados).
4. Configura palabras clave ricas y pertinentes en `ConfiguracionController.php` para el buscador inteligente con Alpine.js.
