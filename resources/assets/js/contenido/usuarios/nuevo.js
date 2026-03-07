
  $('.fecha-picker').flatpickr({
       dateFormat: 'Y-m-d'
  });

  $(document).ready(function () {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });
  });

  window.addEventListener('msn', event => {
    Swal.fire({
      title: event.detail.msnTitulo,
      html: event.detail.msnTexto,
      icon: event.detail.msnIcono,
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });

  window.addEventListener('bloquedoBtnGuardar', event => {
    $('.btnGuardar').attr('disabled', 'disabled');
  });

  window.addEventListener('desbloquedoBtnGuardar', event => {
    $('.btnGuardar').removeAttr('disabled');
  });



  $('#tienesUnaPeticion').change(function () {
    if (this.checked) {
      $('#divSelectTipoPeticion').removeClass('d-none');
      $('#divDescripcionPeticion').removeClass('d-none');
      $('#descripcion_peticion').prop('required', true);
      $('#tipo_peticion').prop('required', true);
    } else {
      $('#divSelectTipoPeticion').addClass('d-none');
      $('#divDescripcionPeticion').addClass('d-none');

      $('#descripcion_peticion').val('');
      $('#descripcion_peticion').removeAttr('required');

      $('#tipo_peticion').val('');
      $('#tipo_peticion').removeAttr('required');
    }
  });

  function sinComillas(e) {
    tecla = document.all ? e.keyCode : e.which;
    patron = /[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }

  $('#formulario').submit(function () {
    $('.btnGuardar').attr('disabled', 'disabled');

    Swal.fire({
      title: 'Espera un momento',
      text: 'Ya estamos guardando...',
      icon: 'info',
      showCancelButton: false,
      showConfirmButton: false,
      showDenyButton: false
    });
  });
