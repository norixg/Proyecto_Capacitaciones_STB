<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ContenidoHtmlSeguro
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = new HtmlSanitizerConfig();

        $elementos = [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'ol', 'ul', 'li', 'blockquote', 'pre', 'code',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span',
            'sub', 'sup',
        ];

        foreach ($elementos as $elemento) {
            $config = $config->allowElement($elemento, ['class']);
        }

        $config = $config
            ->allowElement('a', ['href', 'title', 'target', 'class'])
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'class'])
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(100_000);

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function limpiar(?string $contenido): ?string
    {
        $contenido = trim((string) $contenido);

        if ($contenido === '' || $contenido === '<p><br></p>') {
            return null;
        }

        $contenido = trim($this->sanitizer->sanitize($contenido));

        return $contenido !== '' ? $contenido : null;
    }
}
