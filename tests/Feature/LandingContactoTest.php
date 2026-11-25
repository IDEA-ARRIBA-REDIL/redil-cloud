<?php

namespace Tests\Feature;

use App\Livewire\Central\ContactoLanding;
use App\Mail\SolicitudDemoLandingMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class LandingContactoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('landing:contacto:'.hash('sha256', '127.0.0.1'));
    }

    public function test_landing_se_renderiza_con_componente_de_contacto_y_whatsapp(): void
    {
        $response = $this->get('http://localhost:8000/');

        $response->assertOk()
            ->assertSeeLivewire(ContactoLanding::class)
            ->assertSee('Gestión de Miembros')
            ->assertSee('Ahorro de tiempo garantizado')
            ->assertSee('wa.me/', false)
            ->assertDontSee('PostgreSQL')
            ->assertDontSee('Rueda de la Vida');
    }

    public function test_formulario_valida_campos_obligatorios(): void
    {
        Livewire::test(ContactoLanding::class)
            ->call('enviarSolicitud')
            ->assertHasErrors([
                'nombreIglesia',
                'nombreContacto',
                'email',
                'whatsapp',
                'pais',
                'tamanoCongregacion',
            ]);
    }

    public function test_formulario_envia_correo_y_genera_enlace_whatsapp_prellenado(): void
    {
        Mail::fake();
        config()->set('services.landing.contact_email', 'comercial@redilcloud.com');
        config()->set('services.landing.whatsapp_number', '573105551234');

        Livewire::test(ContactoLanding::class)
            ->set('nombreIglesia', 'Iglesia Vida Nueva')
            ->set('nombreContacto', 'Pastor Carlos Ruiz')
            ->set('email', 'carlos@vidanueva.org')
            ->set('whatsapp', '+57 311 222 3344')
            ->set('pais', 'Colombia')
            ->set('tamanoCongregacion', '300 a 1.000 miembros')
            ->set('moduloInteres', 'Escuelas y Campus LMS')
            ->set('mensaje', 'Queremos ver el módulo de escuelas bíblicas.')
            ->call('enviarSolicitud')
            ->assertHasNoErrors()
            ->assertSet('enviado', true)
            ->assertSet('correoEnviado', true)
            ->assertSee('https://wa.me/573105551234?text=', false);

        Mail::assertSent(SolicitudDemoLandingMail::class, function (SolicitudDemoLandingMail $mail): bool {
            return $mail->hasTo('comercial@redilcloud.com')
                && $mail->solicitud['nombreIglesia'] === 'Iglesia Vida Nueva'
                && $mail->solicitud['moduloInteres'] === 'Escuelas y Campus LMS';
        });
    }

    public function test_honeypot_evita_envio_de_correo_para_bots(): void
    {
        Mail::fake();

        Livewire::test(ContactoLanding::class)
            ->set('website', 'https://spam-bot.example')
            ->call('enviarSolicitud')
            ->assertSet('enviado', true);

        Mail::assertNothingSent();
    }
}
