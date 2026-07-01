<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Capacitaciones</title>

    <script>
        (function () {
            const temaGuardado = localStorage.getItem('tema-sistema-capacitacion');
            const prefiereOscuro = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (temaGuardado === 'oscuro' || (!temaGuardado && prefiereOscuro)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-stb.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-stb.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Figtree, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #0f172a;
            background:
                radial-gradient(circle at 12% 16%, rgba(186, 230, 253, 0.40), transparent 30%),
                radial-gradient(circle at 88% 12%, rgba(187, 247, 208, 0.42), transparent 28%),
                radial-gradient(circle at 74% 85%, rgba(219, 234, 254, 0.42), transparent 32%),
                linear-gradient(135deg, #f8fafc 0%, #f4f8fc 48%, #eef8f1 100%);
            overflow-x: hidden;
        }

        .auth-page {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 34px 22px;
            overflow: hidden;
        }

        .auth-floating {
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            opacity: 0.78;
            filter: blur(0.5px);
            animation: auth-float 7s ease-in-out infinite;
            mix-blend-mode: multiply;
        }

        .auth-floating-one {
            width: 150px;
            height: 150px;
            left: 7%;
            top: 17%;
            background: radial-gradient(circle at 35% 35%, rgba(187, 247, 208, 0.92), rgba(187, 247, 208, 0.30) 62%, transparent 100%);
            animation-delay: 0s;
        }

        .auth-floating-two {
            width: 122px;
            height: 122px;
            right: 8%;
            bottom: 17%;
            background: radial-gradient(circle at 40% 40%, rgba(191, 219, 254, 0.95), rgba(191, 219, 254, 0.30) 62%, transparent 100%);
            animation-delay: -1.5s;
        }

        .auth-floating-three {
            width: 92px;
            height: 92px;
            right: 26%;
            top: 11%;
            background: radial-gradient(circle at 30% 35%, rgba(187, 247, 208, 0.86), rgba(191, 219, 254, 0.46) 58%, transparent 100%);
            animation-delay: -3s;
        }

        .auth-floating-four {
            width: 108px;
            height: 108px;
            left: 19%;
            bottom: 10%;
            background: radial-gradient(circle at 36% 32%, rgba(191, 219, 254, 0.88), rgba(187, 247, 208, 0.42) 62%, transparent 100%);
            animation-delay: -4.5s;
        }

        .auth-floating-five {
            width: 76px;
            height: 76px;
            left: 50%;
            top: 7%;
            background: linear-gradient(135deg, rgba(191, 219, 254, 0.82), rgba(187, 247, 208, 0.70));
            animation-delay: -2.2s;
        }

        .auth-floating-six {
            width: 96px;
            height: 96px;
            right: 17%;
            top: 55%;
            background: linear-gradient(225deg, rgba(187, 247, 208, 0.82), rgba(191, 219, 254, 0.70));
            animation-delay: -5.8s;
        }

        @keyframes auth-float {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            25% {
                transform: translate3d(18px, -24px, 0) scale(1.08);
            }

            50% {
                transform: translate3d(-14px, 18px, 0) scale(0.96);
            }

            75% {
                transform: translate3d(22px, 16px, 0) scale(1.05);
            }

            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        .auth-card-shell {
            position: relative;
            z-index: 1;
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: minmax(0, 0.94fr) minmax(360px, 420px);
            overflow: hidden;
            border-radius: 34px;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 26px 70px rgba(15, 23, 42, 0.09);
            backdrop-filter: blur(18px);
        }

        .auth-info {
            padding: 42px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.78));
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            text-decoration: none;
        }

        .auth-logo {
            width: 48px;
            height: 48px;
            border-radius: 17px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(191, 219, 254, 0.95);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.08);
            padding: 6px;
        }

        .auth-brand-kicker {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .auth-brand-name {
            margin-top: 3px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .auth-copy {
            margin-top: 68px;
        }

        .auth-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid rgba(191, 219, 254, 0.95);
            background: rgba(239, 246, 255, 0.82);
            color: #1e3a8a;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .auth-pill-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.12);
        }

        .auth-title {
            margin: 18px 0 0;
            max-width: 470px;
            font-size: clamp(32px, 4.2vw, 48px);
            line-height: 1.06;
            font-weight: 750;
            letter-spacing: -0.052em;
            color: #0f172a;
        }

        .auth-text {
            margin: 18px 0 0;
            max-width: 480px;
            font-size: 14.5px;
            line-height: 1.78;
            font-weight: 500;
            color: #64748b;
        }

        .auth-feature-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 11px;
            margin-top: 28px;
        }

        .auth-feature {
            min-height: 96px;
            border-radius: 22px;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(255, 255, 255, 0.72);
            padding: 15px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.045);
            transition: transform 180ms ease, background-color 180ms ease, border-color 180ms ease;
        }

        .auth-feature:hover {
            transform: translateY(-4px);
            background: rgba(239, 246, 255, 0.90);
            border-color: rgba(147, 197, 253, 0.92);
        }

        .auth-feature strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .auth-feature span {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            line-height: 1.45;
            font-weight: 500;
            color: #64748b;
        }

        .auth-note {
            margin-top: 48px;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
        }

        .auth-form-area {
            padding: 34px;
            display: flex;
            align-items: center;
            background:
                radial-gradient(circle at top, rgba(219, 234, 254, 0.60), transparent 42%),
                linear-gradient(135deg, rgba(240, 253, 244, 0.64), rgba(239, 246, 255, 0.74));
        }

        .auth-form-card {
            width: 100%;
            border-radius: 30px;
            border: 1px solid rgba(226, 232, 240, 0.88);
            background: rgba(255, 255, 255, 0.78);
            padding: 30px;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.075);
            backdrop-filter: blur(14px);
            transition: transform 220ms ease, box-shadow 220ms ease;
        }

        .auth-form-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 26px 58px rgba(15, 23, 42, 0.095);
        }

        .auth-card-header {
            margin-bottom: 24px;
        }

        .auth-card-kicker {
            margin: 0;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .auth-card-title {
            margin: 8px 0 0;
            font-size: 26px;
            line-height: 1.08;
            font-weight: 750;
            letter-spacing: -0.04em;
            color: #0f172a;
        }

        .auth-card-subtitle {
            margin: 10px 0 0;
            font-size: 13.5px;
            line-height: 1.62;
            font-weight: 500;
            color: #64748b;
        }

        .auth-form-card label {
            display: block;
            margin-bottom: 7px;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        .auth-form-card input[type="email"],
        .auth-form-card input[type="password"],
        .auth-form-card input[type="text"] {
            width: 100%;
            height: 46px;
            border-radius: 18px;
            border: 1px solid rgba(203, 213, 225, 0.95);
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
            padding: 0 14px;
            font-size: 14px;
            font-weight: 500;
            outline: none;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.025);
            transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
        }

        .auth-form-card input[type="email"]:hover,
        .auth-form-card input[type="password"]:hover,
        .auth-form-card input[type="text"]:hover {
            border-color: rgba(147, 197, 253, 0.95);
            background: #ffffff;
        }

        .auth-form-card input[type="email"]:focus,
        .auth-form-card input[type="password"]:focus,
        .auth-form-card input[type="text"]:focus {
            border-color: rgba(59, 130, 246, 0.58);
            box-shadow: 0 0 0 4px rgba(191, 219, 254, 0.60);
            transform: translateY(-1px);
            background: #ffffff;
        }

        .auth-form-card input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 6px;
            border-color: rgba(148, 163, 184, 0.95);
            color: #1d4ed8;
        }

        .auth-form-card a {
            color: #1e3a8a;
            font-size: 13px;
            font-weight: 650;
            text-decoration: none;
            transition: color 160ms ease;
        }

        .auth-form-card a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .auth-primary-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 43px;
            padding: 0 18px;
            border-radius: 999px;
            border: 0;
            background: #0f172a;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.14);
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }

        .auth-primary-button:hover {
            transform: translateY(-2px);
            background: #1e293b;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.18);
        }

        .auth-secondary-link {
            color: #64748b !important;
        }

        .auth-status {
            margin-bottom: 18px;
            border-radius: 18px;
            border: 1px solid rgba(74, 222, 128, 0.40);
            background: rgba(220, 252, 231, 0.86);
            color: #166534;
            padding: 12px 14px;
            font-size: 13px;
            font-weight: 600;
        }

        .auth-error {
            margin-top: 7px;
            font-size: 12px;
            font-weight: 600;
            color: #dc2626;
        }

        @media (max-width: 960px) {
            .auth-card-shell {
                grid-template-columns: 1fr;
            }

            .auth-copy {
                margin-top: 36px;
            }

            .auth-note {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .auth-page {
                padding: 18px;
            }

            .auth-info,
            .auth-form-area {
                padding: 24px;
            }

            .auth-feature-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main class="auth-page">

        <button
            type="button"
            onclick="
                const html = document.documentElement;
                const modoOscuroActivo = html.classList.toggle('dark');
                localStorage.setItem('tema-sistema-capacitacion', modoOscuroActivo ? 'oscuro' : 'claro');
            "
            class="auth-theme-toggle"
            title="Cambiar modo claro/oscuro"
        >
            <span class="dark:hidden">🌙</span>
            <span class="hidden dark:inline">☀️</span>
        </button>

        <div class="auth-floating auth-floating-one"></div>
        <div class="auth-floating auth-floating-two"></div>
        <div class="auth-floating auth-floating-three"></div>
        <div class="auth-floating auth-floating-four"></div>
        <div class="auth-floating auth-floating-five"></div>
        <div class="auth-floating auth-floating-six"></div>

        <section class="auth-card-shell">
            <div class="auth-info">
                <a href="{{ url('/') }}" class="auth-brand">
                    <img src="{{ asset('images/logo-stb.png') }}" alt="Logo STB" class="auth-logo">

                    <div>
                        <div class="auth-brand-kicker">Sistema de capacitaciones</div>
                        <div class="auth-brand-name">Service and Trading Business</div>
                    </div>
                </a>

                <div class="auth-copy">
                    <div class="auth-pill">
                        <span class="auth-pill-dot"></span>
                        Acceso seguro
                    </div>

                    <h1 class="auth-title">
                        Bienvenido al sistema de capacitación
                    </h1>

                    <p class="auth-text">
                        Ingresa para continuar con la gestión, seguimiento y revisión de capacitaciones del personal.
                    </p>

                    <div class="auth-feature-row">
                        <div class="auth-feature">
                            <strong>Capacitaciones</strong>
                            <span>Módulos, recursos y evaluaciones.</span>
                        </div>

                        <div class="auth-feature">
                            <strong>Seguimiento</strong>
                            <span>Avance, notas e intentos.</span>
                        </div>

                        <div class="auth-feature">
                            <strong>Avisos</strong>
                            <span>Recordatorios del proceso.</span>
                        </div>
                    </div>
                </div>

                <p class="auth-note">
                    Uso interno para personal autorizado.
                </p>
            </div>

            <div class="auth-form-area">
                <div class="auth-form-card">
                    {{ $slot }}
                </div>
            </div>
        </section>
    </main>
</body>
</html>