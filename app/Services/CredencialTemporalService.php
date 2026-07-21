<?php

namespace App\Services;

use App\Mail\CredencialTemporalMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class CredencialTemporalService
{
    public const HORAS_VIGENCIA = 24;

    public function generar(): string
    {
        $letras = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $numeros = '23456789';
        $simbolos = '!@#$%*-_';

        $caracteres = [
            $letras[random_int(0, strlen($letras) - 1)],
            $letras[random_int(0, strlen($letras) - 1)],
            $numeros[random_int(0, strlen($numeros) - 1)],
            $simbolos[random_int(0, strlen($simbolos) - 1)],
        ];

        $todos = $letras.$numeros.$simbolos;

        while (count($caracteres) < 16) {
            $caracteres[] = $todos[random_int(0, strlen($todos) - 1)];
        }

        for ($i = count($caracteres) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$caracteres[$i], $caracteres[$j]] = [$caracteres[$j], $caracteres[$i]];
        }

        return implode('', $caracteres);
    }

    public function preparar(User $usuario, string $passwordTemporal): void
    {
        $usuario->forceFill([
            'password' => $passwordTemporal,
            'debe_cambiar_password' => 1,
            'password_temporal_expira_en' => now()->addHours(self::HORAS_VIGENCIA),
            'remember_token' => Str::random(60),
        ])->save();
    }

    public function enviar(User $usuario, string $passwordTemporal): void
    {
        if (config('mail.default') === 'log') {
            throw new RuntimeException('El correo de credenciales no puede enviarse con MAIL_MAILER=log porque expondría la contraseña temporal en los registros.');
        }

        Mail::to($usuario->email)->send(new CredencialTemporalMail(
            usuario: $usuario,
            passwordTemporal: $passwordTemporal,
            horasVigencia: self::HORAS_VIGENCIA,
        ));
    }
}
