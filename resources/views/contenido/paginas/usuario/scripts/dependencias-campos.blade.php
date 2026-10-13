@php
  $dependenciasConfig = [];
  if (isset($formulario) && $formulario->secciones) {
      $mapaCampos = $formulario->secciones->flatMap->campos->keyBy('id');
      foreach ($formulario->secciones as $sec) {
          foreach ($sec->campos as $c) {
              if (!empty($c->pivot->depende_de_campo_id) && isset($mapaCampos[$c->pivot->depende_de_campo_id])) {
                  $padre = $mapaCampos[$c->pivot->depende_de_campo_id];
                  $dependenciasConfig[] = [
                      'hijo_name_id' => $c->name_id,
                      'padre_name_id' => $padre->name_id,
                      'condicion' => $c->pivot->tipo_condicion ?? 'no_vacio',
                      'valor_esperado' => (string) ($c->pivot->valor_condicion ?? ''),
                      'accion' => $c->pivot->accion_dependencia ?? 'deshabilitar',
                  ];
              }
          }
      }
  }
@endphp

@if(count($dependenciasConfig) > 0)
<style>
  .campo-dependiente-bloqueado {
    opacity: 0.45 !important;
    pointer-events: none !important;
    user-select: none !important;
    transition: opacity 0.25s ease-in-out;
  }
</style>

<script type="module">
  $(document).ready(function() {
    const dependencias = @json($dependenciasConfig);

    function getElementByNameOrId(nameId) {
      if (!nameId) return $();
      // 1. Buscar por atributo name exacto o con corchetes []
      let $el = $('[name="' + nameId + '"], [name="' + nameId + '[]"]');
      if ($el.length) return $el;

      // 2. Buscar por id exacto
      $el = $('#' + nameId);
      if ($el.length) return $el;

      // 3. Buscar por clase
      $el = $('.' + nameId);
      return $el;
    }

    function getFieldWrapper($element) {
      if (!$element || !$element.length) return $();
      let $wrapper = $element.closest('.col-12, [class*="col-md-"], [class*="col-sm-"], [class*="col-lg-"], [class*="col-xl-"], .form-group, .mb-3');
      return $wrapper.length ? $wrapper : $element.parent();
    }

    function getFieldValue(nameId) {
      let $el = getElementByNameOrId(nameId);
      if (!$el.length) return null;

      // Checkbox o Switch
      if ($el.is(':checkbox')) {
        return $el.is(':checked') ? '1' : '0';
      }

      // Radio button
      if ($el.is(':radio')) {
        let $checked = $('[name="' + nameId + '"]:checked');
        return $checked.length ? $checked.val() : '';
      }

      // Select (simple o múltiple)
      if ($el.is('select')) {
        let val = $el.val();
        return val !== null ? val : '';
      }

      // Input estándar, textarea, etc.
      let val = $el.val();
      return val !== null && val !== undefined ? String(val).trim() : '';
    }

    function normalizeValue(val) {
      if (val === null || val === undefined) return '';
      let str = String(val).trim().toLowerCase();
      // Si equivale a booleano verdadero
      if (['1', 'true', 'si', 'sí', 'on', 'activo', 'activado'].includes(str)) return '1';
      // Si equivale a booleano falso
      if (['0', 'false', 'no', 'off', 'inactivo', 'desactivado'].includes(str)) return '0';
      return str;
    }

    function evaluateCondition(condicion, valorEsperado, valorActual) {
      if (condicion === 'vacio') {
        if (Array.isArray(valorActual)) {
          return valorActual.length === 0;
        }
        if (valorActual === null || valorActual === undefined) {
          return true;
        }
        let norm = normalizeValue(valorActual);
        return norm === '' || norm === '0';
      }

      if (condicion === 'no_vacio') {
        if (Array.isArray(valorActual)) {
          return valorActual.length > 0;
        }
        if (valorActual === null || valorActual === undefined) {
          return false;
        }
        let norm = normalizeValue(valorActual);
        return norm !== '' && norm !== '0';
      }

      if (condicion === 'igual_a') {
        if (Array.isArray(valorActual)) {
          let normEsperado = normalizeValue(valorEsperado);
          return valorActual.map(normalizeValue).includes(normEsperado);
        }
        if (valorActual === null || valorActual === undefined) {
          return false;
        }
        return normalizeValue(valorActual) === normalizeValue(valorEsperado);
      }

      return true;
    }

    function setFieldState($hijoElement, habilitar, accion) {
      if (!$hijoElement || !$hijoElement.length) return;
      let $wrapper = getFieldWrapper($hijoElement);
      let $controls = $wrapper.find('input, select, textarea, button:not([data-bs-toggle="collapse"])');
      if (!$controls.length) {
        $controls = $hijoElement;
      }

      accion = accion || 'deshabilitar';

      if (habilitar) {
        $controls.prop('disabled', false).removeAttr('disabled').removeClass('disabled');
        $wrapper.removeClass('campo-dependiente-bloqueado d-none');

        // Restaurar plugins (Select2, Datepicker)
        $controls.filter('.select2, select').each(function() {
          $(this).prop('disabled', false);
        });
      } else {
        $controls.prop('disabled', true).attr('disabled', 'disabled').addClass('disabled');

        if (accion === 'ocultar') {
          $wrapper.addClass('d-none').removeClass('campo-dependiente-bloqueado');
        } else {
          $wrapper.addClass('campo-dependiente-bloqueado').removeClass('d-none');
        }

        // Deshabilitar Select2
        $controls.filter('.select2, select').each(function() {
          $(this).prop('disabled', true);
        });
      }
    }

    function resolverTodasLasDependencias() {
      if (!dependencias || !dependencias.length) return;

      // Realizar hasta 3 pasadas para resolver cadenas jerárquicas (A -> B -> C)
      for (let pasada = 0; pasada < 3; pasada++) {
        dependencias.forEach(function(dep) {
          let $hijo = getElementByNameOrId(dep.hijo_name_id);
          if (!$hijo.length) return;

          let $padre = getElementByNameOrId(dep.padre_name_id);
          let $padreWrapper = getFieldWrapper($padre);

          let padreEstaInactivo = $padreWrapper.hasClass('campo-dependiente-bloqueado') || $padreWrapper.hasClass('d-none');
          let valorPadre = getFieldValue(dep.padre_name_id);

          // Si el padre está inactivo (oculto o bloqueado), el hijo hereda el estado inactivo
          if (padreEstaInactivo) {
            setFieldState($hijo, false, dep.accion);
            return;
          }

          let seCumpleCondicion = evaluateCondition(dep.condicion, dep.valor_esperado, valorPadre);

          // Modelo SI ... ENTONCES ...:
          // Si la condición se cumple -> se aplica el efecto (ocultar/bloquear) -> habilitar = false
          // Si la condición NO se cumple -> el campo se mantiene en su estado normal -> habilitar = true
          let habilitar = !seCumpleCondicion;

          setFieldState($hijo, habilitar, dep.accion);
        });
      }
    }

    // Evaluación inicial al cargar la página
    setTimeout(function() {
      resolverTodasLasDependencias();
    }, 200);

    // Escuchar eventos en vivo sobre cualquier input/select/switch
    $(document).on('input change select2:select select2:unselect select2:clear', 'input, select, textarea', function() {
      resolverTodasLasDependencias();
    });

    // Escuchar clics en botones de pasos (wizard), tabs y switches
    $(document).on('click', '.btn-next, .btn-prev, .step, [data-bs-toggle="tab"], .switch, .switch-toggle-slider', function() {
      setTimeout(resolverTodasLasDependencias, 50);
      setTimeout(resolverTodasLasDependencias, 250);
    });

    // Escuchar cambios de Livewire
    if (typeof Livewire !== 'undefined') {
      Livewire.hook('morph.updated', () => {
        setTimeout(resolverTodasLasDependencias, 100);
      });
    }
  });
</script>
@endif
