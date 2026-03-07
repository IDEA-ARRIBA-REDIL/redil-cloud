@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection
@section('page-script')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const cameraSelect = document.getElementById('camera-select');
    const readerDiv = document.getElementById('reader');
    const qrModal = document.getElementById('qrScannerModal');
    let html5Qrcode = null; // Cambiado para evitar confusión con la instancia
    let isScanning = false;
    let ultimaLectura = null;


    const loadCameras = async () => {
      try {
        const devices = await Html5Qrcode.getCameras();
        if (devices && devices.length) {
          cameraSelect.innerHTML = '<option value="" disabled>Seleccione una cámara</option>';
          devices.forEach((device, index) => {
            const option = document.createElement('option');
            option.value = device.id;
            option.text = device.label || `Cámara ${index + 1}`;
            cameraSelect.appendChild(option);
          });
          // Seleccionar la primera cámara por defecto
          if (cameraSelect.options.length > 1) {
             cameraSelect.selectedIndex = 1;
          }
        } else {
          Swal.fire('Error', 'No se encontraron cámaras.', 'error');
        }
      } catch (e) {
        Swal.fire('Permisos denegados', 'Por favor, activa los permisos de cámara en tu navegador para continuar.', 'error');
      }
    };

    const startScanner = async () => {
      if (isScanning || !cameraSelect.value) return;

      html5Qrcode = new Html5Qrcode("reader");

      try {
        await html5Qrcode.start(
          cameraSelect.value, {
            fps: 10,
            qrbox: { width: 250, height: 250 }
          },
          (decodedText) => { // Callback de éxito
            if (decodedText === ultimaLectura) return;
            ultimaLectura = decodedText;

            html5Qrcode.pause(true); // Pausa el escáner

            Livewire.dispatch('qrCodeScanned', { qrText: decodedText });

            setTimeout(() => { ultimaLectura = null; }, 3000);
          },
          (errorMessage) => { // Callback de error (ignorado)
          }
        );
        isScanning = true;
      } catch (err) {
        Swal.fire('Error al iniciar', `No se pudo iniciar el escáner. ${err.message}`, 'error');
      }
    };

    const stopScanner = async () => {
      if (html5Qrcode && isScanning) {
        try {
          await html5Qrcode.stop();
        } catch (e) {
          // Ignorar errores al detener, a veces ocurre si ya está detenido
        } finally {
          isScanning = false;
          html5Qrcode.clear();
          // Limpiamos el visualizador para que no quede la última imagen congelada
          readerDiv.innerHTML = "";
        }
      }
    };

    // Eventos del modal Bootstrap 5
    qrModal.addEventListener('shown.bs.modal', async () => {
      await loadCameras();
      await startScanner();
    });

    qrModal.addEventListener('hidden.bs.modal', async () => {
      await stopScanner();
    });

    cameraSelect.addEventListener('change', async () => {
      if (isScanning) {
        await stopScanner();
        await startScanner();
      }
    });

    // Listener de Livewire para reanudar el escaneo
    Livewire.on('msn', ({ msnIcono, msnTitulo, msnTexto, timer }) => {
      Swal.fire({
        icon: msnIcono ?? 'info',
        title: msnTitulo ?? 'Información',
        text: msnTexto ?? '',
        timer: timer ?? 3000,
        showConfirmButton: false,
        timerProgressBar: true,
      }).then(() => {
        if (html5Qrcode && isScanning) {
          try {
            html5Qrcode.resume();
          } catch (e) {
            console.error("No se pudo reanudar el escáner.", e);
          }
        }
      });
    });

  });
</script>
@endsection

@section('content')


<div class="row mt-10">
  <div class="col-12 offset-md-1 col-md-10">
    <div class="d-flex flex-column col-12 col-md-4 mb-3">
      <!-- Botón para abrir el modal -->
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
        <span class="ti ti-qrcode me-2"></span>
        Escánear asistencia con QR
      </button>
    </div>
  </div>

  @livewire('ReporteReuniones.asistentes', [
    'reporteReunion' => $reporteReunion,
  ])

</div>


<!-- Modal del escáner QR -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerLabel" aria-hidden="true" wire:ignore.self>
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrScannerLabel">Escáner QR</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="camera-select" class="form-label">Seleccionar cámara</label>
          <select id="camera-select" class="form-select w-100">
            <option value="">Cargando cámaras...</option>
          </select>
        </div>

        <div id="reader" style="width: 100%; max-width: 500px;" class="mx-auto border rounded p-2"></div>

        <div class="mt-3 text-center">
          <small class="text-muted">Apunta la cámara al código QR.</small>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
