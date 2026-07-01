<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Capacitaciones</title>

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
                radial-gradient(circle at 12% 14%, rgba(186, 230, 253, 0.40), transparent 30%),
                radial-gradient(circle at 90% 10%, rgba(187, 247, 208, 0.44), transparent 30%),
                radial-gradient(circle at 82% 88%, rgba(219, 234, 254, 0.42), transparent 32%),
                linear-gradient(135deg, #f8fafc 0%, #f4f8fc 48%, #eef8f1 100%);
            overflow-x: hidden;
        }

        .public-shell {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 38px 24px;
            overflow: hidden;
        }

        .public-orb {
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            opacity: 0.78;
            filter: blur(0.5px);
            animation: float-soft 7s ease-in-out infinite;
            mix-blend-mode: multiply;
        }

        .public-orb-one {
            width: 150px;
            height: 150px;
            left: 7%;
            top: 17%;
            background: radial-gradient(circle at 35% 35%, rgba(187, 247, 208, 0.92), rgba(187, 247, 208, 0.30) 62%, transparent 100%);
            animation-delay: 0s;
        }

        .public-orb-two {
            width: 122px;
            height: 122px;
            right: 8%;
            bottom: 17%;
            background: radial-gradient(circle at 40% 40%, rgba(191, 219, 254, 0.95), rgba(191, 219, 254, 0.30) 62%, transparent 100%);
            animation-delay: -1.5s;
        }

        .public-orb-three {
            width: 92px;
            height: 92px;
            right: 26%;
            top: 11%;
            background: radial-gradient(circle at 30% 35%, rgba(187, 247, 208, 0.86), rgba(191, 219, 254, 0.46) 58%, transparent 100%);
            animation-delay: -3s;
        }

        .public-orb-four {
            width: 108px;
            height: 108px;
            left: 19%;
            bottom: 10%;
            background: radial-gradient(circle at 36% 32%, rgba(191, 219, 254, 0.88), rgba(187, 247, 208, 0.42) 62%, transparent 100%);
            animation-delay: -4.5s;
        }

        .public-orb-five {
            width: 76px;
            height: 76px;
            left: 50%;
            top: 7%;
            background: linear-gradient(135deg, rgba(191, 219, 254, 0.82), rgba(187, 247, 208, 0.70));
            animation-delay: -2.2s;
        }

        .public-orb-six {
            width: 96px;
            height: 96px;
            right: 17%;
            top: 55%;
            background: linear-gradient(225deg, rgba(187, 247, 208, 0.82), rgba(191, 219, 254, 0.70));
            animation-delay: -5.8s;
        }

        @keyframes float-soft {
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

        .public-card {
            position: relative;
            z-index: 1;
            width: min(1080px, 100%);
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(360px, 0.82fr);
            overflow: hidden;
            border-radius: 34px;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(255, 255, 255, 0.82);
            box-shadow: 0 26px 70px rgba(15, 23, 42, 0.09);
            backdrop-filter: blur(18px);
        }

        .public-hero {
            padding: 48px 52px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.78));
        }

        .public-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 54px;
        }

        .public-logo {
            width: 50px;
            height: 50px;
            border-radius: 18px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(191, 219, 254, 0.95);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.08);
            padding: 6px;
        }

        .public-brand-kicker {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .public-brand-name {
            margin-top: 3px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .public-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            border: 1px solid rgba(191, 219, 254, 0.95);
            background: rgba(239, 246, 255, 0.82);
            padding: 8px 12px;
            color: #1e3a8a;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .public-label-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.12);
        }

        .public-title {
            margin: 20px 0 0;
            max-width: 560px;
            font-size: clamp(35px, 4.8vw, 56px);
            line-height: 1.02;
            font-weight: 750;
            letter-spacing: -0.055em;
            color: #0f172a;
        }

        .public-text {
            margin: 22px 0 0;
            max-width: 560px;
            font-size: 15.5px;
            line-height: 1.85;
            font-weight: 500;
            color: #64748b;
        }

        .public-actions {
            margin-top: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .public-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 999px;
            border: 1px solid transparent;
            background: #0f172a;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.15);
            transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }

        .public-btn:hover {
            transform: translateY(-2px);
            background: #1e293b;
            box-shadow: 0 20px 36px rgba(15, 23, 42, 0.18);
        }

        .public-btn span {
            transition: transform 180ms ease;
        }

        .public-btn:hover span {
            transform: translateX(3px);
        }

        .public-side {
            padding: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 20% 20%, rgba(219, 234, 254, 0.62), transparent 34%),
                radial-gradient(circle at 88% 18%, rgba(187, 247, 208, 0.62), transparent 34%),
                linear-gradient(135deg, rgba(239, 246, 255, 0.72), rgba(240, 253, 244, 0.60));
        }

        .public-summary {
            width: 100%;
            max-width: 365px;
            border-radius: 30px;
            border: 1px solid rgba(226, 232, 240, 0.90);
            background: rgba(255, 255, 255, 0.74);
            padding: 28px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.07);
            backdrop-filter: blur(12px);
            transition: transform 220ms ease, box-shadow 220ms ease;
        }

        .public-summary:hover {
            transform: translateY(-4px);
            box-shadow: 0 26px 60px rgba(15, 23, 42, 0.10);
        }

        .public-summary-title {
            margin: 0;
            font-size: 21px;
            line-height: 1.2;
            font-weight: 750;
            letter-spacing: -0.035em;
            color: #0f172a;
        }

        .public-items {
            display: grid;
            gap: 13px;
            margin-top: 20px;
        }

        .public-item {
            display: flex;
            gap: 12px;
            padding: 15px;
            border-radius: 22px;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(248, 250, 252, 0.80);
            transition: transform 180ms ease, border-color 180ms ease, background-color 180ms ease;
        }

        .public-item:hover {
            transform: translateX(5px);
            border-color: rgba(147, 197, 253, 0.95);
            background: rgba(239, 246, 255, 0.90);
        }

        .public-number {
            width: 31px;
            height: 31px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: #dcfce7;
            color: #166534;
            font-size: 12px;
            font-weight: 800;
        }

        .public-item strong {
            display: block;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }

        .public-item span {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 12.5px;
            line-height: 1.55;
            font-weight: 500;
        }

        @media (max-width: 900px) {
            .public-card {
                grid-template-columns: 1fr;
            }

            .public-hero,
            .public-side {
                padding: 32px;
            }

            .public-brand {
                margin-bottom: 32px;
            }
        }
    </style>
</head>

<body>
    <main class="public-shell">

        <button
            type="button"
            onclick="
                const html = document.documentElement;
                const modoOscuroActivo = html.classList.toggle('dark');
                localStorage.setItem('tema-sistema-capacitacion', modoOscuroActivo ? 'oscuro' : 'claro');
            "
            class="public-theme-toggle"
            title="Cambiar modo claro/oscuro"
        >
            <span class="dark:hidden">🌙</span>
            <span class="hidden dark:inline">☀️</span>
        </button>

        <div class="public-orb public-orb-one"></div>
        <div class="public-orb public-orb-two"></div>
        <div class="public-orb public-orb-three"></div>
        <div class="public-orb public-orb-four"></div>
        <div class="public-orb public-orb-five"></div>
        <div class="public-orb public-orb-six"></div>

        <section class="public-card">
            <div class="public-hero">
                <div class="public-brand">
                    <img src="{{ asset('images/logo-stb.png') }}" alt="Logo STB" class="public-logo">

                    <div>
                        <div class="public-brand-kicker">Sistema de capacitaciones</div>
                        <div class="public-brand-name">Service and Trading Business</div>
                    </div>
                </div>

                <div class="public-label">
                    <span class="public-label-dot"></span>
                    Plataforma interna
                </div>

                <h1 class="public-title">
                    Capacitación clara, ordenada y fácil de seguir
                </h1>

                <p class="public-text">
                    Un espacio para gestionar capacitaciones, consultar avances, revisar actividades
                    y mantener el seguimiento formativo del personal de manera simple.
                </p>

                <div class="public-actions">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="public-btn">
                            Ir al panel <span>→</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="public-btn">
                            Iniciar sesión <span>→</span>
                        </a>
                    @endauth
                </div>
            </div>

            <div class="public-side">
                <div class="public-summary">
                    <h2 class="public-summary-title">
                        Todo el proceso en un solo lugar
                    </h2>

                    <div class="public-items">
                        <div class="public-item">
                            <div class="public-number">01</div>
                            <div>
                                <strong>Capacitaciones</strong>
                                <span>Contenido organizado por módulos, recursos y evaluaciones.</span>
                            </div>
                        </div>

                        <div class="public-item">
                            <div class="public-number">02</div>
                            <div>
                                <strong>Seguimiento</strong>
                                <span>Avance, estados y resultados del personal.</span>
                            </div>
                        </div>

                        <div class="public-item">
                            <div class="public-number">03</div>
                            <div>
                                <strong>Avisos</strong>
                                <span>Notificaciones de asignaciones, vencimientos, retrasos y finalización de capacitaciones.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>