<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $aviso->asunto }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2 style="margin-bottom: 8px;">
        {{ $aviso->asunto }}
    </h2>

    <p>
        Se ha generado un aviso relacionado con una capacitación asignada.
    </p>

    <div style="background: #f3f4f6; border: 1px solid #d1d5db; padding: 12px; border-radius: 6px; margin: 12px 0;">
        {!! nl2br(e($aviso->mensaje)) !!}
    </div>

    @if($aviso->tipo_aviso === 'terminada' && $aviso->destinatario_tipo === 'empleado' && $aviso->empleadoCapacitacion?->estado === 'aprobada')
        <div style="margin-top: 20px; padding: 18px; border: 2px dashed #1d4ed8; background-color: #eff6ff; text-align: center;">
            <h3 style="margin: 0; color: #1e3a8a; font-size: 18px;">
                Diploma temporal de aprobación
            </h3>

            <p style="margin: 10px 0 0 0; color: #1f2937; font-size: 14px;">
                Este espacio representa el diploma oficial que será adjuntado cuando el formato definitivo esté disponible.
            </p>

            <p style="margin: 10px 0 0 0; color: #111827; font-size: 14px;">
                <strong>Empleado:</strong>
                {{ $aviso->empleadoCapacitacion?->empleado?->nombre_completo ?? 'Empleado' }}
            </p>

            <p style="margin: 6px 0 0 0; color: #111827; font-size: 14px;">
                <strong>Capacitación:</strong>
                {{ $aviso->empleadoCapacitacion?->capacitacion?->capacitacion ?? 'Capacitación' }}
            </p>

            <p style="margin: 6px 0 0 0; color: #047857; font-size: 14px;">
                <strong>Estado:</strong> APROBADA
            </p>
        </div>
    @endif

    <p style="font-size: 13px; color: #4b5563;">
        Este correo fue generado automáticamente por el sistema de capacitaciones.
    </p>
</body>
</html>