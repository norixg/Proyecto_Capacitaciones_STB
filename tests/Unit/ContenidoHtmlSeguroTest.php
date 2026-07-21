<?php

namespace Tests\Unit;

use App\Services\ContenidoHtmlSeguro;
use PHPUnit\Framework\TestCase;

class ContenidoHtmlSeguroTest extends TestCase
{
    public function test_elimina_scripts_eventos_y_protocolos_peligrosos(): void
    {
        $html = '<p onclick="alert(1)">Texto</p>'
            . '<script>alert(2)</script>'
            . '<a href="javascript:alert(3)" target="_blank">Enlace</a>'
            . '<img src="https://example.com/a.png" onerror="alert(4)">';

        $limpio = (new ContenidoHtmlSeguro())->limpiar($html);

        $this->assertNotNull($limpio);
        $this->assertStringNotContainsStringIgnoringCase('script', $limpio);
        $this->assertStringNotContainsStringIgnoringCase('onclick', $limpio);
        $this->assertStringNotContainsStringIgnoringCase('onerror', $limpio);
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $limpio);
        $this->assertStringContainsString('rel="noopener noreferrer"', $limpio);
    }
}
