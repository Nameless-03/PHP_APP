<?php

namespace App\Jobs;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarEmailBienvenidaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Usuario $usuario;

    /**
     * Create a new job instance.
     */
    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Encolado asincrónicamente: Enviando email de bienvenida al usuario ID: {$this->usuario->id}");

        // Simulamos envío de correo electrónico.
        // Dado que MAIL_MAILER=log está activo, esto se registrará en storage/logs/laravel.log
        try {
            Mail::raw(
                "¡Hola {$this->usuario->nombre}! Bienvenido/a a nuestra plataforma de servicios. Nos alegra mucho tenerte con nosotros.",
                function ($message) {
                    $message->to($this->usuario->email)
                            ->subject("¡Bienvenido/a a la plataforma!");
                }
            );
            Log::info("Email de bienvenida enviado con éxito a: {$this->usuario->email}");
        } catch (\Exception $e) {
            Log::error("Error al enviar email de bienvenida a {$this->usuario->email}: " . $e->getMessage());
        }
    }
}
