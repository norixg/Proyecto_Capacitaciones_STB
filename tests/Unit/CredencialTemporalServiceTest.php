<?php

namespace Tests\Unit;

use App\Services\CredencialTemporalService;
use PHPUnit\Framework\TestCase;

class CredencialTemporalServiceTest extends TestCase
{
    public function test_genera_credenciales_largas_con_tipos_de_caracter_requeridos(): void
    {
        $password = (new CredencialTemporalService())->generar();

        $this->assertSame(16, strlen($password));
        $this->assertMatchesRegularExpression('/[A-Za-z]/', $password);
        $this->assertMatchesRegularExpression('/[0-9]/', $password);
        $this->assertMatchesRegularExpression('/[!@#$%*_\-]/', $password);
    }
}
