<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Credenciales temporales</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2>Tu acceso al Sistema de Capacitaciones STB</h2>

    <p>Hola, {{ $usuario->name }}.</p>

    <p>Se generaron credenciales temporales para tu cuenta:</p>

    <div style="background: #f3f4f6; border: 1px solid #d1d5db; padding: 16px; margin: 16px 0;">
        <p style="margin: 0 0 8px;"><strong>Usuario:</strong> {{ $usuario->username }}</p>
        <p style="margin: 0 0 8px;"><strong>Correo:</strong> {{ $usuario->email }}</p>
        <p style="margin: 0;"><strong>Contraseña temporal:</strong> {{ $passwordTemporal }}</p>
    </div>

    <p>
        Esta contraseña vence en {{ $horasVigencia }} horas. Al iniciar sesión deberás establecer una contraseña nueva antes de acceder al sistema.
    </p>

    <p>Si no solicitaste estas credenciales, comunícate con el administrador.</p>
</body>
</html>
