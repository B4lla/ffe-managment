<?php

namespace App\Mail;

use App\Models\TareaPendiente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TareaPendienteAsignadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TareaPendiente $tarea)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine()
        );
    }

    public function subjectLine(): string
    {
        return 'Tarea pendiente: '.$this->tarea->tipo_tarea;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tarea-pendiente',
            with: [
                'tarea' => $this->tarea,
                'convenio' => $this->tarea->convenio,
                'actionUrl' => $this->actionUrl(),
            ],
        );
    }

    private function actionUrl(): ?string
    {
        if (! $this->tarea->convenio_id) {
            return null;
        }

        $tipo = strtolower(trim((string) $this->tarea->tipo_tarea));
        $routeName = match (true) {
            str_contains($tipo, 'firmar centro'), str_contains($tipo, 'firma centro') => 'convenios.firmar_centro',
            str_contains($tipo, 'firmar empresa'), str_contains($tipo, 'firma empresa') => 'convenios.firmar_empresa',
            str_contains($tipo, 'validar') => 'convenios.validar_firma',
            str_contains($tipo, 'generar pdf'), str_contains($tipo, 'pdf') => 'convenios.generar_pdf',
            str_contains($tipo, 'datos') => 'convenios.datos',
            str_contains($tipo, 'descargar convenio firmado'), str_contains($tipo, 'descargar firmado') => 'convenios.descargar_firmado',
            default => 'convenios.show',
        };

        return route($routeName, $this->tarea->convenio_id);
    }
}
