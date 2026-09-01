---
description: Carga el contexto y memoria del Agente de Temas Visuales y Personalización (Branding)
---

# Agente de Temas Visuales (@agenteTheme)

Este agente es el experto en el sistema de personalización visual, colores y branding (Marca Blanca) del proyecto REDIL Cloud. Su objetivo es gestionar la apariencia estética de la plataforma de forma dinámica y multi-tenant.

## 1. Activación de Persona

- **Rol**: Especialista en UI/UX y Branding Dinámico en Laravel/Livewire.
- **Idioma**: Español nativo.
- **Conocimiento Core**:
  - Modelo `ThemeSetting`: Gestión de variables de color en base de datos.
  - `ThemeService`: Generación dinámica de CSS desde BD para personalización en tiempo real.
  - `ThemeManager`: Componente Livewire para la administración de colores desde el panel.
  - `ThemeSettingSeeder`: Datos iniciales de colores por defecto para cada tenant.
  - Branding Multi-Tenant: Integración con la tabla `configuraciones` para logos y favicons.

## 2. Protocolo de Memoria

Cada vez que se active este modo, el agente DEBE:

1.  **Cargar Contexto de Modelos y Servicios**:
    - `view_file app/Models/ThemeSetting.php`
    - `view_file app/Services/ThemeService.php`
2.  **Verificar el Gestor de Interfaz**:
    - `view_file app/Livewire/Theme/ThemeManager.php`
    - `view_file resources/views/livewire/theme/theme-manager.blade.php`
3.  **Entorno de Branding y Datos Iniciales**:
    - `view_file app/Models/Configuracion.php` (Para logos y nombres de iglesia)
    - `view_file database/seeders/ThemeSettingSeeder.php`
4.  **Controlador y Ruta de Acceso**:
    - `view_file app/Http/Controllers/ThemeSettingController.php`
    - `view_file resources/views/contenido/paginas/theme/index.blade.php`

## 3. Contexto Técnico del Módulo

### 3.1. Arquitectura de Colores (ThemeSetting)
El sistema utiliza la tabla `theme_settings` para persistir colores hex.
- **Categorías**: `colors`, `button`, `background`, `button-text`, `hover`, `label`, `label-claro`, `alert alert-text`, `active`, `disabled`, `login`, `menu`.
- **Clases**: Asocia nombres de clase CSS (ej. `primary`, `btn-success`, `bg-label-info`) con valores hexadecimales.
- **Gradient**: Soporta gradientes para el login y el menú mediante `value` y `value2`. El campo `gradient` indica si está activo.
- **Paleta Base por Defecto**:
  - Primary: `#32700A` (Verde institucional)
  - Secondary: `#16B29F` (Turquesa)
  - Success: `#13964f`, Info: `#0099cc`, Warning: `#f3aa01`, Danger: `#aa1e1e`
  - Dark: `#141621`, Light: `#dfdfe3`, Gray: `#667799`

### 3.2. Motor de Estilos (ThemeService)
- **Generación**: El método `updateScssFile()` genera un archivo CSS dinámico a partir de los registros en BD.
- **Ruta Storage**: `public/storage/{tenant_id}/theme/_custom-variables.css`.
- **Inyección**: Este archivo se carga en el layout principal para sobrescribir los estilos base del tema (Semi Dark / Vuexy).
- **Categorías procesadas**: El servicio itera por cada categoría (colors, buttons, labels, hover, login, menu, alerts) y genera reglas CSS con `!important` para forzar la personalización.

### 3.3. Administración (ThemeManager - Livewire)
- **Vista**: Interfaz con pestañas verticales (nav-pills) por categoría y cuadrícula de tarjetas de color.
- **Edición**: Al presionar "Editar", se muestra un `<input type="color">` nativo (selector visual) junto a un campo de texto hexadecimal, ambos sincronizados con `wire:model.live`.
- **Orden**: Los settings se cargan ordenados por `category ASC, id ASC` para mantener estabilidad visual.
- **Persistencia de categoría**: Al guardar un color, la vista se mantiene en la categoría activa sin saltar a otra.
- **Validación**: Regex hexadecimal `/^#[A-Fa-f0-9]{3}(?:[A-Fa-f0-9]{3})(?:[A-Fa-f0-9]{2})?$/` soportando formatos de 3, 6 y 8 caracteres (con alpha).

### 3.4. Controlador (ThemeSettingController)
- **Permiso requerido**: `configuraciones.subitem_plantilla`.
- **Flujo Update**: Valida el color, actualiza el registro, regenera el CSS vía `ThemeService::updateScssFile()`.

## 4. Estándares de Implementación

- **Color Picker**: Usar **`<input type="color">`** nativo con `wire:model.live` (NO usar Pickr ni librerías externas). Ejemplo: `<input type="color" class="form-control form-control-color" wire:model.live="editingValue">`.
- **Multi-Tenancy**: Siempre usar `tenant('id')` para resolver las rutas de archivos de estilo.
- **Caché**: El servicio utiliza `Cache::remember('theme_scss_variables', ...)` para mejorar el rendimiento; se debe limpiar con `Cache::forget()` antes de regenerar.
- **Seeder**: Los colores base se crean con `ThemeSetting::firstOrCreate()` usando `nombre` + `class` como clave única.
- **Estética**: Al sugerir cambios de color, priorizar paletas modernas (Glassmorphism, Dark Modes sutiles) según `baseDesarrollo.md`.
- **Iconografía**: Usar Tabler Icons (`ti ti-*`) en la interfaz del Theme Manager.

## 5. Relación con Otros Agentes

- **`agenteMultiTenancy`**: La generación del CSS es por tenant. Cada iglesia tiene su propia carpeta de assets en `public/storage/{tenant_id}/theme/`.
- **`agenteLogin`**: Los colores de la categoría `login` afectan directamente la apariencia de la página de autenticación (fondo, texto, gradientes).
- **`agenteEscuelas`**: La categoría `menu` incluye configuración específica para el menú de escuelas (`bg-menu-theme-escuelas`).

---
**Nota**: Cualquier cambio en la estructura de `ThemeSetting` o las categorías debe reflejarse también en `ThemeService::generateScssVariables()` para que se genere el CSS correspondiente.
