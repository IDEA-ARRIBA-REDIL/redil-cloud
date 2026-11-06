<script type="module">
  $(document).ready(function() {
    const $selectSede = $('#sede');
    if (!$selectSede.length) {
      return;
    }

    // 1. Cachear todas las opciones originales del selector de sedes
    const opcionesOriginales = [];
    $selectSede.find('option').each(function() {
      const $opt = $(this);
      const val = $opt.val();
      let localidades = [];
      try {
        const raw = $opt.attr('data-localidades');
        if (raw) {
          localidades = JSON.parse(raw);
          if (!Array.isArray(localidades)) localidades = [];
        }
      } catch (e) {
        localidades = [];
      }

      opcionesOriginales.push({
        value: val,
        text: $opt.text(),
        localidades: localidades.map(id => String(id)),
        isNinguno: val === ''
      });
    });

    /**
     * Reconstruye las opciones del select #sede según la lista proporcionada
     * y maneja la retención o reseteo del valor seleccionado.
     */
    function reconstruirOpciones(listaOpciones, valorActual, limpiarSeleccion) {
      $selectSede.empty();
      let valorAunExiste = false;

      listaOpciones.forEach(opt => {
        const $option = $('<option></option>')
          .val(opt.value)
          .text(opt.text);

        if (opt.value === valorActual && opt.value !== '') {
          valorAunExiste = true;
        }

        $selectSede.append($option);
      });

      if (limpiarSeleccion || !valorAunExiste) {
        $selectSede.val(null);
      } else {
        $selectSede.val(valorActual);
      }

      $selectSede.trigger('change.select2');
    }

    /**
     * Filtra las sedes por localidadId.
     * Si no hay localidadId o si la localidad no tiene sedes vinculadas, restaura todas.
     * Si limpiarSeleccion es true, resetea el valor a null.
     */
    function filtrarSedes(localidadId, limpiarSeleccion = false) {
      const locIdStr = (localidadId !== null && localidadId !== undefined && localidadId !== '') ? String(localidadId).trim() : '';
      const valorActual = $selectSede.val();

      // Caso 1: No hay localidad seleccionada -> Mostrar todas
      if (!locIdStr) {
        reconstruirOpciones(opcionesOriginales, valorActual, limpiarSeleccion);
        return;
      }

      // Caso 2: Filtrar opciones que cubran dicha localidad
      const opcionesFiltradas = opcionesOriginales.filter(opt => {
        if (opt.isNinguno) return true;
        return opt.localidades.includes(locIdStr);
      });

      // Caso 3: Si la localidad no tiene ninguna sede asociada (solo quedó la opción 'Ninguno')
      const tieneSedesAsociadas = opcionesFiltradas.some(opt => !opt.isNinguno);
      if (!tieneSedesAsociadas) {
        // Fallback: mostrar todas las sedes sin restricción
        reconstruirOpciones(opcionesOriginales, valorActual, limpiarSeleccion);
        return;
      }

      // Caso 4: Mostrar sedes vinculadas
      reconstruirOpciones(opcionesFiltradas, valorActual, limpiarSeleccion);
    }

    // 2. Inicialización al cargar: evaluar si ya hay una localidad seleccionada (en editar o old)
    const $inputLocalidad = $('#localidadIdCalculada');
    const localidadInicial = $inputLocalidad.length ? $inputLocalidad.val() : null;
    if (localidadInicial) {
      filtrarSedes(localidadInicial, false);
    }

    // 3. Escuchar eventos de Livewire 3
    if (typeof Livewire !== 'undefined') {
      Livewire.on('ubicacion-seleccionada', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const locId = payload?.localidadId || null;
        const interactivo = payload?.interactivo !== false;
        filtrarSedes(locId, interactivo);
      });

      Livewire.on('ubicacion-limpiada', (data) => {
        filtrarSedes(null, true);
      });
    }

    // 4. Escuchar eventos nativos en window por compatibilidad
    window.addEventListener('ubicacion-seleccionada', (e) => {
      const locId = e.detail?.localidadId || null;
      const interactivo = e.detail?.interactivo !== false;
      filtrarSedes(locId, interactivo);
    });

    window.addEventListener('ubicacion-limpiada', () => {
      filtrarSedes(null, true);
    });
  });
</script>
