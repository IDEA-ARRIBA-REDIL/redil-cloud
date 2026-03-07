<?php

namespace App\Mail;

use App\Models\CarritoCursoUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompraCursoConfirmacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public CarritoCursoUser $carrito;
    public $nombreComprador;
    public $identificacionComprador;
    public $telefonoComprador;

    public function __construct(CarritoCursoUser $carrito, $nombreComprador, $identificacionComprador, $telefonoComprador)
    {
        $this->carrito = $carrito;
        $this->nombreComprador = $nombreComprador;
        $this->identificacionComprador = $identificacionComprador;
        $this->telefonoComprador = $telefonoComprador;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Compra de Cursos',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mensaje-confirmacion-compra-curso',
        );
    }
}
