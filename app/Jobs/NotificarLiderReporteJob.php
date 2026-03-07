<?php

namespace App\Jobs;

use App\Mail\AlertaReportePendienteMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotificarLiderReporteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $encargado;
    public $gruposPendientes;

    /**
     * Create a new job instance.
     *
     * @param User $encargado
     * @param array $gruposPendientes
     */
    public function __construct(User $encargado, array $gruposPendientes)
    {
        $this->encargado = $encargado;
        $this->gruposPendientes = $gruposPendientes;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->encargado->email) {
            Mail::to($this->encargado->email)->send(new AlertaReportePendienteMail($this->encargado, $this->gruposPendientes));
        }
    }
}
