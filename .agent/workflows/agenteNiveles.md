---
description: Agente para la gestión de Niveles de Agrupación (Sistema Escolar Grados)
---

# Agente de Niveles (Agrupación de Materias)

Este agente se encarga especificamente del nuevo sistema de agrupación de materias ("Niveles" o "Grados"), que funciona de manera paralela al sistema tradicional de materias independientes.

## 1. Contexto y Alcance

- **Objetivo**: Gestionar la agrupación de materias bajo una entidad "Nivel", centralizando restricciones académicas y matrículas.
- **Aislamiento**: Este sistema **NO DEBE** modificar la lógica existente de `Escuela`, `Materia` o `Matricula` tradicional. Toda su lógica vive en modelos y controladores nuevos con el prefijo `NivelAgrupacion`.
- **Activación**: Este flujo solo aplica para Escuelas con `tipo_matricula = 'niveles_agrupados'`.

## 2. Reglas de Negocio Específicas

1.  **Jerarquía**:
    - `Escuela` -> `NivelAgrupacion` -> `NivelAgrupacionMateria` (Materia Existente).
2.  **Restricciones**:
    - Las restricciones (asistencia mínima, reportes, etc.) se definen en `NivelAgrupacionConfiguracion` y aplican a TODO el bloque de materias del nivel.
3.  **Matrícula**:
    - El estudiante se matricula al `NivelAgrupacion`.
    - Debe seleccionar obligatoriamente un horario para cada materia que compone el nivel (salvo excepciones definidas).
    - Para aprobar el Nivel, debe aprobar TODAS las materias.

## 3. Estructura de Archivos (NUEVOS)

Este agente solo tiene permiso para trabajar sobre estos archivos y sus satélites:

- **Modelos**:
  - `app/Models/NivelAgrupacion.php`
  - `app/Models/NivelAgrupacionConfiguracion.php`
  - `app/Models/NivelAgrupacionMateria.php`
  - `app/Models/MatriculaNivel.php`
- **Controladores**:
  - `app/Http/Controllers/NivelAgrupacionController.php`
- **Livewire**:
  - `app/Livewire/Escuelas/Niveles/*`
  - `resources/views/livewire/escuelas/niveles/*`

## 4. Protocolo de Desarrollo

Sigue estrictamente el protocolo definido en `baseDesarrollo.md`:

- **Idioma**: Español.
- **Estilo**: Laravel 11 + Livewire 3 + AlpineJS.
- **Validaciones**: Siempre validar `tipo_matricula` antes de ejecutar lógica de niveles.
