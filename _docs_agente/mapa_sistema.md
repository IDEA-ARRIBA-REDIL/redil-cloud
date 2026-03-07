# Mapa del Sistema CRECER

## Arquitectura Alto Nivel

- **Backend**: Laravel 11
- **Frontend**: Livewire 3 + Alpine.js
- **Base de Datos**: MySQL (Esquema extenso > 200 migraciones)

## Módulos Clave (Por Documentar)

- Gestión Académica (Alumnos, Grupos, Materias)
- Gestión Eclesiástica (Sedes, Ministerio)
- Actividades y Eventos
- **Calificaciones**: Sistema robusto con vistas múltiples (Acordeón, Grilla Livewire) y cálculo ponderado.
- **Informes y Exportación**: Manejo de reportes complejos y exportación a Excel (`app/Exports`).

## Ubicaciones Importantes

- `app/Services`: Lógica de negocio compleja.
- `app/Livewire`: Componentes reactivos.
- `app/Exports`: Clases para generación de archivos Excel.
