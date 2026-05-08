<?php

namespace App\Services;

use App\Mail\TareaPendienteAsignadaMail;
use App\Models\TareaPendiente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendTareaPendienteMailer
{
	public function send(TareaPendiente $tarea): void
	{
		$tarea->loadMissing(['usuario', 'convenio.empresa']);

		$email = $tarea->usuario?->email;
		$apiKey = config('services.resend.key');
		$fromAddress = config('mail.from.address');

		if (! $apiKey || ! $fromAddress || ! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return;
		}

		$mail = new TareaPendienteAsignadaMail($tarea);

		try {
			$response = Http::withToken($apiKey)
				->asJson()
				->post('https://api.resend.com/emails', [
					'from' => $this->fromHeader($fromAddress),
					'to' => [$email],
					'subject' => $mail->subjectLine(),
					'html' => $mail->render(),
				]);

			if ($response->failed()) {
				Log::warning('Resend rechazo el correo de tarea pendiente.', [
					'tarea_id' => $tarea->id,
					'usuario_id' => $tarea->usuario_id,
					'status' => $response->status(),
					'body' => $response->body(),
				]);
			}
		} catch (\Throwable $e) {
			Log::warning('No se pudo enviar correo de tarea pendiente por Resend.', [
				'tarea_id' => $tarea->id,
				'usuario_id' => $tarea->usuario_id,
				'error' => $e->getMessage(),
			]);
		}
	}

	private function fromHeader(string $address): string
	{
		$name = trim((string) config('mail.from.name'));

		return $name !== '' ? $name.' <'.$address.'>' : $address;
	}
}
