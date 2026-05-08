<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tarea pendiente</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; background: #f9fafb; margin: 0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px;">
        <h1 style="font-size: 20px; margin: 0 0 12px;">Tienes una tarea pendiente</h1>

        <p style="font-size: 14px; line-height: 1.5; margin: 0 0 12px;">
            <strong>{{ $tarea->tipo_tarea }}</strong>
        </p>

        <p style="font-size: 14px; line-height: 1.5; margin: 0 0 16px;">
            {{ $tarea->descripcion }}
        </p>

        @if($convenio)
            <p style="font-size: 14px; line-height: 1.5; margin: 0 0 16px;">
                Convenio #{{ $convenio->id }}
                @if($convenio->empresa)
                    - {{ $convenio->empresa->nombre_razon_social }}
                @endif
            </p>
        @endif

        @if($actionUrl)
            <p style="margin: 20px 0;">
                <a href="{{ $actionUrl }}" style="background: #4f46e5; color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 6px; display: inline-block; font-size: 14px;">
                    Abrir tarea
                </a>
            </p>
        @endif

        <p style="font-size: 12px; color: #6b7280; margin: 20px 0 0;">
            Este correo se ha enviado porque hay una actualizacion en la gestion de convenios FFE.
        </p>
    </div>
</body>
</html>
