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

    function evaluateCondition(condicion, valorEsperado, valorActual) {
      if (condicion === 'no_vacio') {
        if (Array.isArray(valorActual)) {
          return valorActual.length > 0;
        }
        if (valorActual === null || valorActual === undefined) {
          return false;
        }
        let strVal = String(valorActual).trim();
        return strVal !== '' && strVal !== '0';
      }

      if (condicion === 'igual_a') {
        if (Array.isArray(valorActual)) {
          return valorActual.map(String).includes(String(valorEsperado));
        }
        if (valorActual === null || valorActual === undefined) {
          return false;
        }
        return String(valorActual).trim() === String(valorEsperado).trim();
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

          let cumple = !padreEstaInactivo && evaluateCondition(dep.condicion, dep.valor_esperado, valorPadre);

          setFieldState($hijo, cumple, dep.accion);
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

    // Escuchar cambios de Livewire
    if (typeof Livewire !== 'undefined') {
      Livewire.hook('morph.updated', () => {
        setTimeout(resolverTodasLasDependencias, 100);
      });
    }
  });
</script>
@endif
