$(document).ready(function () {
  $('.select2BusquedaAvanzada').select2({
    dropdownParent: $('#modalBusquedaAvanzada')
  });
});

// Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
$('#modalBusquedaAvanzada').on('scroll', function (event) {
  $(this)
    .find('.select2BusquedaAvanzada')
    .each(function () {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
});

$(document).ready(function () {
  $('.select2GeneradorExcel').select2({
    dropdownParent: $('#modalGeneradorExcel')
  });
});

// Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
$('#modalGeneradorExcel').on('scroll', function (event) {
  $(this)
    .find('.select2GeneradorExcel')
    .each(function () {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
});

$('#filtroFechasPasosCrecimiento1').flatpickr({
  mode: 'range',
  dateFormat: 'Y-m-d',
  defaultDate: [
    "{{ $parametrosBusqueda->filtroFechaIniPaso1 ? $parametrosBusqueda->filtroFechaIniPaso1 : ''}}",
    "{{ $parametrosBusqueda->filtroFechaFinPaso1 ? $parametrosBusqueda->filtroFechaFinPaso1 : ''}}"
  ],
  locale: {
    firstDayOfWeek: 1,
    weekdays: {
      shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
      longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
    },
    months: {
      shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Оct', 'Nov', 'Dic'],
      longhand: [
        'Enero',
        'Febreo',
        'Мarzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
      ]
    }
  },
  onChange: function (dates) {
    if (dates.length == 2) {
      var _this = this;
      var dateArr = dates.map(function (date) {
        return _this.formatDate(date, 'Y-m-d');
      });
      $('#filtroFechaIniPaso1').val(dateArr[0]);
      $('#filtroFechaFinPaso1').val(dateArr[1]);
      // interact with selected dates here
    }
  },
  onReady: function (dateObj, dateStr, instance) {
    var $cal = $(instance.calendarContainer);
    if ($cal.find('.flatpickr-clear').length < 1) {
      $cal.append('<button type="button" class="btn btn-sm btn-outline-primary flatpickr-clear mb-2">Borrar</button>');
      $cal.find('.flatpickr-clear').on('click', function () {
        instance.clear();
        $('#filtroFechaIniPaso1').val('');
        $('#filtroFechaFinPaso1').val('');
        instance.close();
      });
    }
  }
});

$('#filtroFechasPasosCrecimiento2').flatpickr({
  mode: 'range',
  dateFormat: 'Y-m-d',
  defaultDate: [
    "{{ $parametrosBusqueda->filtroFechaIniPaso2 ? $parametrosBusqueda->filtroFechaIniPaso2 : ''}}",
    "{{ $parametrosBusqueda->filtroFechaFinPaso2 ? $parametrosBusqueda->filtroFechaFinPaso2 : ''}}"
  ],
  locale: {
    firstDayOfWeek: 1,
    weekdays: {
      shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
      longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
    },
    months: {
      shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Оct', 'Nov', 'Dic'],
      longhand: [
        'Enero',
        'Febreo',
        'Мarzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
      ]
    }
  },
  onChange: function (dates) {
    if (dates.length == 2) {
      var _this = this;
      var dateArr = dates.map(function (date) {
        return _this.formatDate(date, 'Y-m-d');
      });
      $('#filtroFechaIniPaso2').val(dateArr[0]);
      $('#filtroFechaFinPaso2').val(dateArr[1]);
      // interact with selected dates here
    }
  },
  onReady: function (dateObj, dateStr, instance) {
    var $cal = $(instance.calendarContainer);
    if ($cal.find('.flatpickr-clear').length < 1) {
      $cal.append('<button type="button" class="btn btn-sm btn-outline-primary flatpickr-clear mb-2">Borrar</button>');
      $cal.find('.flatpickr-clear').on('click', function () {
        instance.clear();
        $('#filtroFechaIniPaso2').val('');
        $('#filtroFechaFinPaso2').val('');
        instance.close();
      });
    }
  }
});

$('.clearAllItems').click(function () {
  value = $(this).data('select');
  $('#' + value)
    .val(null)
    .trigger('change');
});

$('.selectAllItems').click(function () {
  value = $(this).data('select');
  $('#' + value + ' > option').prop('selected', true);
  $('#' + value).trigger('change');
});

function darBajaAlta(usuarioId, tipo) {
  Livewire.dispatch('abrirModalBajaAlta', { usuarioId: usuarioId, tipo: tipo });
}

function comprobarSiTieneRegistros(usuarioId) {
  Livewire.dispatch('comprobarSiTieneRegistros', { usuarioId: usuarioId });
}

function eliminacionForzada(usuarioId) {
  Livewire.dispatch('confirmarEliminacion', { usuarioId: usuarioId });
}
