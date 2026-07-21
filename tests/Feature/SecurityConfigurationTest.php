<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class SecurityConfigurationTest extends TestCase
{
    public function test_respuestas_web_incluyen_cabeceras_defensivas(): void
    {
        $this->withoutVite();

        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_politica_global_rechaza_contrasenas_de_ocho_caracteres(): void
    {
        $validator = Validator::make([
            'password' => 'Abcd123!',
            'password_confirmation' => 'Abcd123!',
        ], [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_urls_de_recursos_rechazan_protocolos_ejecutables(): void
    {
        $reglas = ['url_recurso' => ['nullable', 'url:http,https', 'max:1000']];

        $this->assertTrue(Validator::make(['url_recurso' => 'javascript:alert(1)'], $reglas)->fails());
        $this->assertFalse(Validator::make(['url_recurso' => 'https://example.com/recurso'], $reglas)->fails());
    }
}
