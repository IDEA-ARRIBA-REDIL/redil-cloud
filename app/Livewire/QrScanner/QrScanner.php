<?php

namespace App\Livewire\QrScanner;

use Livewire\Component;

class QrScanner extends Component
{


    // Variable para almacenar el resultado del escaneo
    public $scanResult = '';

    // Variable para controlar mensajes de éxito
    public $success = false;

    public $actividad;



    // Método que se llamará cuando se lea un código QR exitosamente
    public function handleSuccessfulScan($decodedText)
    {
        $this->scanResult = $decodedText;
        $this->success = true;

        // Primero disparamos el evento para registrar la asistencia
        $this->dispatch('registrarAsistencia', $decodedText);


        // Emitir evento para mostrar SweetAlert
        $this->dispatch('showAlert', [
            'title' => '¡Éxito!',
            'text' => 'Código QR leído exitosamente: ' . $decodedText,
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.qr-scanner.qr-scanner');
    }
}
