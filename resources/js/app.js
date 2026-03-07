import './bootstrap';



import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es';

// Configuración global opcional
flatpickr.localize(Spanish);

document.addEventListener('DOMContentLoaded', () => {
    flatpickr(".fecha-picker", {
        dateFormat: "Y-m-d",
        disableMobile: false,
        locale: 'es'
    });
});


// o


/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**'
]);
