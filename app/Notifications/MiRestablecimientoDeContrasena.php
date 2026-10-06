<?php

namespace App\Notifications;

use App\Mail\DefaultMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class MiRestablecimientoDeContrasena extends Notification
{
    use Queueable;

    /**
     * El token de restablecimiento de contraseña.
     *
     * @var string
     */
    public $token;

    /**
     * Callback opcional para generar la URL.
     *
     * @var (\Closure(mixed, string): string)|null
     */
    public static $createUrlCallback;

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(#[\SensitiveParameter] string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): Mailable
    {
        $resetUrl = $this->resetUrl($notifiable);
        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        $mailData = new \stdClass();
        $mailData->subject = Lang::get('Restablecer contraseña');
        $mailData->eyebrow = 'SEGURIDAD · RESTABLECIMIENTO DE CONTRASEÑA';
        $mailData->titulo = Lang::get('Restablece tu contraseña');
        $mailData->nombre = method_exists($notifiable, 'nombre') ? $notifiable->nombre(3) : ($notifiable->name ?? '');

        $mailData->mensaje = Lang::get('Has recibido este correo electrónico porque se ha solicitado el restablecimiento de contraseña para tu cuenta.<br><br>Haz clic en el botón de abajo para ingresar y elegir una nueva contraseña:')
            . '<p style="font-size:13px;color:#6B7280;line-height:1.5;margin-top:24px;margin-bottom:0;">'
            . Lang::get('Este enlace de restablecimiento de contraseña expirará en :count minutos.<br><br>Si no has solicitado este cambio, puedes ignorar este mensaje de forma segura.', ['count' => $expireMinutes])
            . '</p>';

        $mailData->actionUrl = $resetUrl;
        $mailData->actionText = Lang::get('Restablecer contraseña →');

        return (new DefaultMail($mailData))
            ->to($notifiable->getEmailForPasswordReset() ?? $notifiable->email);
    }

    /**
     * Genera la URL para restablecer la contraseña.
     */
    protected function resetUrl(mixed $notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    /**
     * Permite registrar un callback personalizado para generar la URL.
     *
     * @param  \Closure(mixed, string): string  $callback
     */
    public static function createUrlUsing(\Closure $callback): void
    {
        static::$createUrlCallback = $callback;
    }
}
