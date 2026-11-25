<script type="module">
  $(document).ready(function() {
    const $selectSede = $('#sede');
    if (!$selectSede.length) {
      return;
    }

    const tieneCampoPais = $selectSede.attr('data-tiene-campo-pais') === '1';
    const tieneCampoMunicipio = $selectSede.attr('data-tiene-campo-municipio') === '1';
    const tieneCampoLocalidad = $selectSede.attr('data-tiene-campo-localidad') === '1';
    const tieneAlgunCampoGeo = tieneCampoPais || tieneCampoMunicipio || tieneCampoLocalidad;
    const placeholderOriginal = $selectSede.attr('data-placeholder') || 'Ninguno';

    // 1. Cachear todas las opciones originales del selector de sedes con sus reglas
    const opcionesOriginales = [];
    let haySedeConReglas = false;

    $selectSede.find('option').each(function() {
      const $opt = $(this);
      const val = $opt.val();
      let reglas = null;

      try {
        const raw = $opt.attr('data-reglas');
        if (raw) {
          reglas = JSON.parse(raw);
          if (reglas && reglas.aparece_en_todos_los_casos === false) {
            haySedeConReglas = true;
          }
        }
      } catch (e) {
        reglas = null;
      }

      opcionesOriginales.push({
        value: val,
        text: $opt.text(),
        reglas: reglas,
        isNinguno: val === ''
      });
    });

    // Estado geográfico actual
    const estadoGeo = {
      paisId: $selectSede.attr('data-pais-inicial') ? Number($selectSede.attr('data-pais-inicial')) : null,
      paisTieneMunicipios: true,
      municipioId: $selectSede.attr('data-municipio-inicial') ? Number($selectSede.attr('data-municipio-inicial')) : null,
      municipioTieneLocalidades: true,
      localidadId: $selectSede.attr('data-localidad-inicial') ? Number($selectSede.attr('data-localidad-inicial')) : null
    };

    // Si en carga inicial hay localidad o municipio sin país en el form, deducirlo del DOM si ya está montado
    if (!estadoGeo.localidadId) {
      const locDom = $('#localidadIdCalculada').val();
      if (locDom) estadoGeo.localidadId = Number(locDom);
    }

    /**
     * Evalúa si una sede cumple exactamente con el estado geográfico actual.
     */
    function sedeCumpleReglas(reglas, estado) {
      if (!reglas || reglas.aparece_en_todos_los_casos) {
        return true;
      }

      if (!estado.paisId) {
        return false;
      }

      const modoPaises = reglas.modo_paises || 'incluir';
      const paisesIds = Array.isArray(reglas.paises_ids) ? reglas.paises_ids.map(Number) : [];

      if (modoPaises === 'excluir') {
        return !paisesIds.includes(estado.paisId);
      }

      if (!paisesIds.includes(estado.paisId)) {
        return false;
      }

      const modoMunicipios = reglas.modo_municipios || 'ninguno';
      if (modoMunicipios === 'ninguno') {
        return !estado.municipioId && !estado.localidadId;
      }

      if (!estado.municipioId) {
        return false;
      }

      const municipiosIds = Array.isArray(reglas.municipios_ids) ? reglas.municipios_ids.map(Number) : [];
      if (modoMunicipios === 'excluir') {
        return !municipiosIds.includes(estado.municipioId);
      }

      if (!municipiosIds.includes(estado.municipioId)) {
        return false;
      }

      const modoLocalidades = reglas.modo_localidades || 'ninguno';
      if (modoLocalidades === 'ninguno') {
        return !estado.localidadId;
      }

      if (!estado.localidadId) {
        return false;
      }

      const localidadesIds = Array.isArray(reglas.localidades_ids) ? reglas.localidades_ids.map(Number) : [];
      if (modoLocalidades === 'excluir') {
        return !localidadesIds.includes(estado.localidadId);
      }

      return localidadesIds.includes(estado.localidadId);
    }

    /**
     * Determina si el usuario aún no ha terminado de llenar los campos geográficos visibles.
     */
    function ubicacionIncompleta(estado) {
      if (tieneCampoPais && !estado.paisId) {
        return true;
      }
      if (tieneCampoMunicipio && estado.paisTieneMunicipios && !estado.municipioId) {
        return true;
      }
      if (tieneCampoLocalidad && estado.paisTieneMunicipios && estado.municipioTieneLocalidades && !estado.localidadId) {
        return true;
      }
      return false;
    }

    function actualizarPlaceholderSelect2(texto) {
      $selectSede.attr('data-placeholder', texto);
      if ($selectSede.data('select2')) {
        $selectSede.select2({
          width: '100%',
          allowClear: true,
          placeholder: texto
        });
      }
    }

    /**
     * Reconstruye las opciones del select #sede y maneja bloqueo / autoselección.
     */
    function aplicarOpciones(opcionesSedes, valorActual, limpiarSeleccion, estadoDeshabilitado, textoPlaceholder) {
      $selectSede.empty();
      $selectSede.append($('<option></option>').val('').text(estadoDeshabilitado ? textoPlaceholder : 'Ninguno'));

      let valorAunExiste = false;

      opcionesSedes.forEach(opt => {
        const $option = $('<option></option>').val(opt.value).text(opt.text);
        if (String(opt.value) === String(valorActual) && opt.value !== '') {
          valorAunExiste = true;
        }
        $selectSede.append($option);
      });

      if (estadoDeshabilitado) {
        $selectSede.val('').prop('disabled', true);
        actualizarPlaceholderSelect2(textoPlaceholder);
      } else if (opcionesSedes.length === 1) {
        $selectSede.prop('disabled', false);
        actualizarPlaceholderSelect2(placeholderOriginal);
        $selectSede.val(opcionesSedes[0].value);
      } else {
        $selectSede.prop('disabled', false);
        actualizarPlaceholderSelect2(placeholderOriginal);
        if (limpiarSeleccion || !valorAunExiste) {
          $selectSede.val('');
        } else {
          $selectSede.val(valorActual);
        }
      }

      $selectSede.trigger('change.select2');
    }

    /**
     * Evalúa y actualiza #sede según el estado geográfico actual.
     */
    function evaluarSedes(limpiarSeleccion = false) {
      const valorActual = $selectSede.val();
      const soloSedesReales = opcionesOriginales.filter(opt => !opt.isNinguno);

      // Si no hay ninguna sede con reglas geográficas o el formulario no tiene campos geográficos -> mostrar todas
      if (!haySedeConReglas || !tieneAlgunCampoGeo) {
        aplicarOpciones(soloSedesReales, valorActual, limpiarSeleccion, false, placeholderOriginal);
        return;
      }

      const opcionesCoincidentes = soloSedesReales.filter(opt => sedeCumpleReglas(opt.reglas, estadoGeo));

      if (opcionesCoincidentes.length > 0) {
        aplicarOpciones(opcionesCoincidentes, valorActual, limpiarSeleccion, false, placeholderOriginal);
        return;
      }

      // No coincidió ninguna sede todavía: distinguir si falta ubicación o si definitivamente no hay sedes
      if (ubicacionIncompleta(estadoGeo)) {
        aplicarOpciones([], valorActual, true, true, 'Primero selecciona tu ubicación');
      } else {
        aplicarOpciones([], valorActual, true, true, 'No hay sedes disponibles');
      }
    }

    // 2. Evaluación inicial al cargar
    evaluarSedes(false);

    // 3. Escuchar eventos de Livewire 3
    if (typeof Livewire !== 'undefined') {
      Livewire.on('pais-seleccionado', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const interactivo = payload?.interactivo !== false;
        estadoGeo.paisId = payload?.paisId ? Number(payload.paisId) : null;
        estadoGeo.paisTieneMunicipios = payload?.tieneMunicipios !== false;

        if (interactivo) {
          estadoGeo.municipioId = null;
          estadoGeo.municipioTieneLocalidades = true;
          estadoGeo.localidadId = null;
        }
        evaluarSedes(interactivo);
      });

      Livewire.on('pais-limpiado', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const interactivo = payload?.interactivo !== false;
        estadoGeo.paisId = null;
        estadoGeo.paisTieneMunicipios = true;
        estadoGeo.municipioId = null;
        estadoGeo.municipioTieneLocalidades = true;
        estadoGeo.localidadId = null;
        evaluarSedes(interactivo);
      });

      Livewire.on('municipio-seleccionado', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const interactivo = payload?.interactivo !== false;
        estadoGeo.municipioId = payload?.municipioId ? Number(payload.municipioId) : null;
        estadoGeo.municipioTieneLocalidades = payload?.tieneLocalidades !== false;
        if (payload?.paisId) {
          estadoGeo.paisId = Number(payload.paisId);
        }
        if (interactivo) {
          estadoGeo.localidadId = null;
        }
        evaluarSedes(interactivo);
      });

      Livewire.on('municipio-limpiado', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const interactivo = payload?.interactivo !== false;
        estadoGeo.municipioId = null;
        estadoGeo.municipioTieneLocalidades = true;
        estadoGeo.localidadId = null;
        evaluarSedes(interactivo);
      });

      Livewire.on('ubicacion-seleccionada', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const interactivo = payload?.interactivo !== false;
        estadoGeo.localidadId = payload?.localidadId ? Number(payload.localidadId) : null;
        if (payload?.municipioId) {
          estadoGeo.municipioId = Number(payload.municipioId);
        }
        if (payload?.paisId) {
          estadoGeo.paisId = Number(payload.paisId);
        }
        evaluarSedes(interactivo);
      });

      Livewire.on('ubicacion-limpiada', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const interactivo = payload?.interactivo !== false;
        estadoGeo.localidadId = null;
        evaluarSedes(interactivo);
      });
    }
  });
</script>
