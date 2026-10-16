<script>
  (function() {
    /**
     * Módulo de Restricciones Dinámicas para Campos de Identificación
     * Aplica reglas de formato (solo números, alfanumérico, alfanumérico con símbolos)
     * y mayúsculas automáticas según el tipo de documento seleccionado.
     */
    function vincularTipoConIdentificacion(selectId, inputId) {
      const select = document.getElementById(selectId);
      const input = document.getElementById(inputId);

      // Regla de negocio: Si el formulario no contiene ambos campos, no aplicar restricciones forzadas.
      if (!select || !input) {
        return;
      }

      // Evitar vinculaciones múltiples
      if (input.dataset.restriccionInicializada === 'true') {
        return;
      }
      input.dataset.restriccionInicializada = 'true';

      function obtenerConfiguracionActual() {
        const selectedOption = select.options && select.selectedIndex >= 0
          ? select.options[select.selectedIndex]
          : null;

        if (!selectedOption || !selectedOption.value) {
          return {
            tipoCaracteres: 'solo_numeros',
            forzarMayusculas: true,
            activo: false
          };
        }

        return {
          tipoCaracteres: selectedOption.dataset.caracteres || 'solo_numeros',
          forzarMayusculas: selectedOption.dataset.mayusculas !== '0',
          activo: true
        };
      }

      function aplicarLimpiezaYFormato() {
        const config = obtenerConfiguracionActual();
        let valorOriginal = input.value;
        if (!valorOriginal) return;

        // 1. Quitar siempre puntos y espacios en blanco accidentales
        let valorLimpio = valorOriginal.replace(/[\.\s]/g, '');

        // 2. Mayúsculas automáticas si está habilitado
        if (config.forzarMayusculas) {
          valorLimpio = valorLimpio.toUpperCase();
        }

        // 3. Filtrado según tipo de caracteres
        if (config.tipoCaracteres === 'solo_numeros') {
          valorLimpio = valorLimpio.replace(/[^0-9]/g, '');
        } else if (config.tipoCaracteres === 'alfanumerico') {
          valorLimpio = valorLimpio.replace(/[^A-Za-z0-9]/g, '');
        }
        // 'alfanumerico_simbolos': permite números, letras y símbolos sin filtrar caracteres especiales

        if (valorOriginal !== valorLimpio) {
          input.value = valorLimpio;
        }
      }

      function actualizarModoTeclado() {
        const config = obtenerConfiguracionActual();
        if (config.tipoCaracteres === 'solo_numeros') {
          input.setAttribute('inputmode', 'numeric');
          input.setAttribute('pattern', '[0-9]*');
        } else {
          input.setAttribute('inputmode', 'text');
          input.removeAttribute('pattern');
        }
        aplicarLimpiezaYFormato();
      }

      // Eventos sobre el input de identificación
      input.addEventListener('input', aplicarLimpiezaYFormato);
      input.addEventListener('keyup', aplicarLimpiezaYFormato);
      input.addEventListener('paste', function() {
        setTimeout(aplicarLimpiezaYFormato, 10);
      });
      input.addEventListener('blur', aplicarLimpiezaYFormato);

      // Evento de cambio nativo sobre el select
      select.addEventListener('change', actualizarModoTeclado);

      // Soporte para Select2 (jQuery)
      if (typeof window.jQuery !== 'undefined') {
        window.jQuery(select).on('change.select2 change', function() {
          actualizarModoTeclado();
        });
      }

      // Configuración inicial en carga
      actualizarModoTeclado();
    }

    function inicializarValidacionesIdentificacion() {
      // 1. Identificación del titular
      vincularTipoConIdentificacion('tipo_identificacion_id', 'identificacion');
      // 2. Identificación del acudiente (si aplica)
      vincularTipoConIdentificacion('tipo_identificacion_acudiente_id', 'identificacion_acudiente');
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', inicializarValidacionesIdentificacion);
    } else {
      inicializarValidacionesIdentificacion();
    }

    // Re-vincular ante navegación por pasos o modales (Livewire / Turbo / DOM mutations)
    if (typeof window.Livewire !== 'undefined') {
      document.addEventListener('livewire:navigated', inicializarValidacionesIdentificacion);
    }

    document.addEventListener('shown.bs.offcanvas', inicializarValidacionesIdentificacion);
    document.addEventListener('shown.bs.modal', inicializarValidacionesIdentificacion);
  })();
</script>
