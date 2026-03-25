# 📋 Formato de Reporte de Error (Template)

Utilice este formato para reportar errores críticos o complejos encontrados durante las pruebas manuales.

---

### 1. Información General
- **ID:** `ERR-XXXX`
- **Fecha:** `YYYY-MM-DD`
- **Módulo:** `(Ej: Cursos, Pagos, Escuela, etc.)`
- **Ruta:** `(Ej: /cursos/catalogo o Dashboard)`
- **Prioridad:** `(Baja | Media | Alta | Crítica)`

### 2. Clasificación del Error
- [ ] **UI/UX:** (Visual, Layout, Responsive)
- [ ] **Lógica/Backend:** (Cálculos erróneos, flujos rotos)
- [ ] **Base de Datos:** (Errores SQL, integridad, datos faltantes)
- [ ] **Multi-tenancy/SaaS:** (Datos cruzados entre tenants, contexto de sede perdido)
- [ ] **Livewire/Frontend:** (Reactividad fallida, estados de carga, bugs JS)
- [ ] **Multimedia:** (Carga de imágenes, reproducción de video)
- [ ] **Permisos:** (Acceso no autorizado o denegado erróneamente)

### 3. Descripción del Problema
- **Resumen:** `(Breve descripción)`
- **Pasos para reproducir:**
  1. `...`
  2. `...`
- **Comportamiento Observado:** `...`
- **Comportamiento Esperado:** `...`

### 4. Evidencia y Logs
- **Captura/Video:** `(Link o referencia)`
- **Log de Laravel (si aplica):**
  ```text
  ...
  ```

---
*Estado actual: [ ] Nuevo | [ ] En Proceso | [ ] Corregido | [ ] Verificado*
