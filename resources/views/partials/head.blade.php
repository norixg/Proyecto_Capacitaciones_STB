<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - Sistema de Capacitación' : 'Sistema de Capacitación' }}
</title>

<link rel="icon" type="image/png" href="{{ asset('images/logo-stb.png') }}">
<link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-stb.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/logo-stb.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance