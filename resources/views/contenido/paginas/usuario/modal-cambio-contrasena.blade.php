<!-- Modal cambio de contraseña -->
<div class="modal fade" id="modalCambioContrasena" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-md" role="document">
    <form id="formCambioContrasena" class="forms-sample" method="POST" action="">
      @csrf
      <div class="modal-content">
        <div class="modal-header d-flex flex-column">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btnCerrarModalXCambioContrasena"></button>
        </div>
        <div class="modal-body">

          <div class="text-center mb-4">
            <h3 class="role-title mb-2"><i class="ti ti-password ti-lg"></i> Cambio de contraseña</h3>
            <p class="text-muted">La contraseña debe contener como mínimo 5 caracteres, una letra minúscula y un número.</p>
          </div>

          <div class="row">

            <!-- Nueva Contrasena -->
            <div class="col-12 mb-3">
              <label class="form-label" for="nueva_contrasena">Nueva contraseña</label>
              <input id="nueva_contrasena" name="password" value="" type="password" class="form-control" required pattern="(?=.*\d)(?=.*[A-Za-z]).{5,}" title="La contraseña debe contener como mínimo 5 caracteres alfanuméricos, es decir, debe contener como mínimo letras y números." autocomplete="new-password"/>
            </div>

            <!-- Confirmar Contrasena -->
            <div class="col-12 mb-3">
              <label class="form-label" for="confirmar_contrasena">Confirmar contraseña</label>
              <input id="confirmar_contrasena" name="password_confirmation" value="" type="password" class="form-control" required pattern="(?=.*\d)(?=.*[A-Za-z]).{5,}" title="La contraseña debe contener como mínimo 5 caracteres alfanuméricos, es decir, debe contener como mínimo letras y números." autocomplete="new-password"/>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn rounded-pill btn-label-secondary" data-bs-dismiss="modal" id="btnCerrarCambioContrasena">Cerrar</button>
          <button type="submit" id="btnSubmitCambioContrasena" class="btn rounded-pill btn-primary"><i class="ti ti-download me-1"></i> Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  window.confirmarCambioContrasenaDefault = function (formId, nombreUsuario = '') {
    if (typeof Swal === 'undefined') {
      if (confirm('¿Estás seguro de restablecer la contraseña por defecto de este usuario?')) {
        const form = document.getElementById(formId);
        if (form) form.submit();
      }
      return;
    }

    const textoHtml = nombreUsuario
      ? `La contraseña de <b>${nombreUsuario}</b> será restablecida a su valor por defecto (documento de identidad o 123456) y se enviará una notificación por correo.`
      : 'La contraseña del usuario será restablecida a su valor por defecto (documento de identidad o 123456) y se enviará una notificación por correo.';

    Swal.fire({
      title: '¿Restablecer contraseña por defecto?',
      html: textoHtml,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="ti ti-check me-1"></i> Sí, restablecer',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-primary rounded-pill me-2',
        cancelButton: 'btn btn-label-secondary rounded-pill'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Restableciendo contraseña...',
          text: 'Por favor espera un momento mientras procesamos el cambio y enviamos la notificación.',
          icon: 'info',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showCancelButton: false,
          showConfirmButton: false,
          showDenyButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        const form = document.getElementById(formId);
        if (form) form.submit();
      }
    });
  };

  document.addEventListener('DOMContentLoaded', function () {
    const formCambio = document.getElementById('formCambioContrasena');
    const modalCambio = document.getElementById('modalCambioContrasena');
    const btnSubmit = document.getElementById('btnSubmitCambioContrasena');
    const btnCerrar = document.getElementById('btnCerrarCambioContrasena');
    const btnCerrarX = document.getElementById('btnCerrarModalXCambioContrasena');
    const inputPass = document.getElementById('nueva_contrasena');
    const inputConfirm = document.getElementById('confirmar_contrasena');

    if (formCambio) {
      formCambio.addEventListener('submit', function (e) {
        // Validar campos según requisitos HTML5
        if (!formCambio.checkValidity()) {
          return;
        }

        // Validar que ambas contraseñas coincidan
        if (inputPass.value !== inputConfirm.value) {
          e.preventDefault();
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: "Las contraseñas no coinciden",
              text: "Por favor, verifica que la nueva contraseña y su confirmación sean exactamente iguales.",
              icon: "warning",
              customClass: {
                confirmButton: 'btn btn-primary rounded-pill'
              },
              buttonsStyling: false
            });
          } else {
            alert('Las contraseñas no coinciden. Por favor verifica antes de continuar.');
          }
          return false;
        }

        // Deshabilitar botones para evitar múltiples envíos
        if (btnSubmit) {
          btnSubmit.style.pointerEvents = 'none';
          btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Guardando...';
          setTimeout(() => {
            btnSubmit.disabled = true;
          }, 0);
        }

        if (btnCerrar) {
          btnCerrar.disabled = true;
        }
        if (btnCerrarX) {
          btnCerrarX.disabled = true;
        }

        // Mostrar SweetAlert2 con indicador de carga
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: "Actualizando contraseña...",
            text: "Por favor espera un momento mientras procesamos el cambio y enviamos la notificación.",
            icon: "info",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: false,
            showConfirmButton: false,
            showDenyButton: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
        }
      });
    }

    // Al cerrar el modal, restaurar valores y estado original del botón
    if (modalCambio) {
      modalCambio.addEventListener('hidden.bs.modal', function () {
        if (formCambio) {
          formCambio.reset();
        }
        if (btnSubmit) {
          btnSubmit.disabled = false;
          btnSubmit.style.pointerEvents = 'auto';
          btnSubmit.innerHTML = '<i class="ti ti-download me-1"></i> Guardar';
        }
        if (btnCerrar) {
          btnCerrar.disabled = false;
        }
        if (btnCerrarX) {
          btnCerrarX.disabled = false;
        }
      });
    }
  });
</script>
