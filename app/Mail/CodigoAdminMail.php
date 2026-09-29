<?php

namespace App\Mail;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoAdminMail extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 2;

    public int $maxExceptions = 2;

    public int $timeout = 30;

    public int $venceEn;

    public function __construct(public string $codigo)
    {
        $this->onConnection('admin_security')->onQueue('admin-security');
        $this->venceEn = now()->addMinutes(config('admin_global.mfa_minutes'))->timestamp;
    }

    public function retryUntil(): DateTimeInterface
    {
        return CarbonImmutable::createFromTimestamp($this->venceEn ?? 0);
    }

    public function build(): static
    {
        return $this->subject('Código de acceso administrativo REDIL')
            ->view('mail.codigo-admin');
    }
}
