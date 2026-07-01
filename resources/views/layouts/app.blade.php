<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

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

        <title>Sistema de Capacitaciones</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo-stb.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-stb.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .esf-admin-modal-card {
                border-radius: 32px !important;
                border: 1px solid rgba(226, 232, 240, 0.96) !important;
                background: linear-gradient(135deg, rgba(255,255,255,.98), rgba(239,246,255,.88)) !important;
                box-shadow: 0 28px 80px rgba(15, 23, 42, 0.24) !important;
                color: #0f172a !important;
            }

            .esf-admin-modal-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            @media (min-width: 768px) {
                .esf-admin-modal-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }
            }

            .esf-admin-modal-full {
                grid-column: 1 / -1 !important;
            }

            .esf-admin-modal-card label {
                display: block !important;
                margin-bottom: 0.35rem !important;
                font-size: 0.78rem !important;
                font-weight: 900 !important;
                color: #334155 !important;
            }

            .esf-admin-modal-card input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            .esf-admin-modal-card select,
            .esf-admin-modal-card textarea {
                width: 100% !important;
                border-radius: 1rem !important;
                border: 1px solid rgba(203, 213, 225, 0.95) !important;
                background: rgba(255, 255, 255, 0.95) !important;
                color: #0f172a !important;
                padding: 0.78rem 1rem !important;
                font-size: 0.9rem !important;
                font-weight: 650 !important;
                box-shadow: 0 8px 20px rgba(15,23,42,.035) !important;
            }

            .esf-admin-modal-card textarea {
                min-height: 110px !important;
            }

            .esf-admin-modal-card input:focus,
            .esf-admin-modal-card select:focus,
            .esf-admin-modal-card textarea:focus {
                border-color: rgba(96,165,250,.9) !important;
                box-shadow: 0 0 0 4px rgba(191,219,254,.65) !important;
                outline: none !important;
            }

            .esf-admin-actions-footer {
                margin-top: 1.25rem !important;
                padding-top: 1rem !important;
                border-top: 1px solid rgba(226, 232, 240, 0.9) !important;
                display: flex !important;
                justify-content: flex-end !important;
                gap: 0.75rem !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            .esf-help-text {
                margin-top: 0.35rem !important;
                font-size: 0.75rem !important;
                font-weight: 600 !important;
                color: #64748b !important;
            }

            .dark .esf-admin-modal-card {
                border-color: rgba(51, 65, 85, 0.95) !important;
                background:
                    linear-gradient(135deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.96)) !important;
                color: #e5e7eb !important;
                box-shadow: 0 28px 80px rgba(0, 0, 0, 0.42) !important;
            }

            .dark .esf-admin-modal-card label {
                color: #e2e8f0 !important;
            }

            .dark .esf-admin-modal-card input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            .dark .esf-admin-modal-card select,
            .dark .esf-admin-modal-card textarea {
                border-color: rgba(71, 85, 105, 0.95) !important;
                background: rgba(15, 23, 42, 0.92) !important;
                color: #f8fafc !important;
            }

            .dark .esf-admin-modal-card input::placeholder,
            .dark .esf-admin-modal-card textarea::placeholder {
                color: #64748b !important;
            }

            .dark .esf-admin-modal-card input:focus,
            .dark .esf-admin-modal-card select:focus,
            .dark .esf-admin-modal-card textarea:focus {
                border-color: rgba(96, 165, 250, 0.8) !important;
                box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.35) !important;
                background: rgba(15, 23, 42, 0.98) !important;
            }

            .dark .esf-admin-actions-footer {
                border-top-color: rgba(51, 65, 85, 0.95) !important;
            }

            .dark .esf-help-text {
                color: #94a3b8 !important;
            }
        </style>
    </head>

    <body class="font-sans antialiased">
        @php
            $usuarioLayout = auth()->user();

            $esAdminLayout = auth()->check()
                && $usuarioLayout?->esAdminSistema();

            $esInstructorLayout = auth()->check()
                && $usuarioLayout?->esInstructorSistema();

            $esRutaUsuario = request()->routeIs('dashboard')
                || request()->routeIs('mis_capacitaciones.*')
                || request()->routeIs('mis_calificaciones.*')
                || request()->routeIs('mis_modulos.*')
                || request()->routeIs('mis_ejercicios.*')
                || request()->routeIs('mis_evaluaciones.*');

            $usarLayoutUsuario = auth()->check()
                && !$esAdminLayout
                && !$esInstructorLayout
                && $esRutaUsuario;

            $usarLayoutAdmin = auth()->check()
                && ($esAdminLayout || $esInstructorLayout);
        @endphp

        @if(request()->boolean('integrado_modulo'))
            <main class="esf-integrated-user-frame">
                {{ $slot }}
            </main>
        @elseif($usarLayoutUsuario)
            <div
                x-data="{ menuUsuarioAbierto: false, perfilUsuarioAbierto: false }"
                class="esf-shell esf-user-shell"
            >
                <div
                    x-show="menuUsuarioAbierto"
                    x-transition.opacity
                    @click="menuUsuarioAbierto = false"
                    class="esf-system-menu-overlay fixed inset-0 bg-black/40 backdrop-blur-sm"
                ></div>

                <aside
                    x-show="menuUsuarioAbierto"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="esf-system-menu-sidebar fixed inset-y-0 left-0 w-80 esf-sidebar overflow-y-auto"
                >
                    <div class="p-5 border-b border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/logo-stb.png') }}"
                                alt="Logo STB"
                                class="h-11 w-11 rounded-2xl object-contain bg-white/80 border border-blue-100 p-1.5 shadow-sm">

                            <div>
                                <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400 font-bold">
                                    Accesos del usuario
                                </p>

                                <h2 class="mt-1 text-lg font-black text-slate-900 dark:text-slate-100">
                                    Mi capacitación
                                </h2>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="menuUsuarioAbierto = false"
                            class="esf-icon-button"
                            title="Cerrar menú"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                            Sesión activa
                        </p>

                        <p class="mt-1 text-sm font-extrabold text-slate-800 dark:text-slate-100">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Usuario de capacitación
                        </p>
                    </div>

                    <nav class="p-4 space-y-5">
                        <div>
                            <p class="px-3 mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">
                                Principal
                            </p>

                            <a href="{{ route('dashboard') }}"
                            class="esf-sidebar-link {{ request()->routeIs('dashboard') ? 'esf-sidebar-link-active' : '' }}">
                                <span class="esf-sidebar-icon">IN</span>
                                <span>Inicio</span>
                            </a>
                        </div>


                    </nav>
                </aside>

                <div class="min-h-screen">
                    <header class="esf-header esf-system-fixed-header">
                        <div class="w-full max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <button
                                    type="button"
                                    @click="menuUsuarioAbierto = true"
                                    class="esf-icon-button"
                                    title="Abrir menú"
                                >
                                    <span class="text-xl leading-none">☰</span>
                                </button>

                                <img src="{{ asset('images/logo-stb.png') }}"
                                        alt="Logo STB"
                                        class="h-11 w-11 rounded-xl object-contain">

                                <div class="hidden sm:block">
                                    <p class="text-[11px] uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                        Sistema de capacitaciones
                                    </p>

                                    <h1 class="text-sm font-black text-slate-800 dark:text-slate-100">
                                        Service and Trading Business
                                    </h1>
                                </div>

                                <nav class="hidden lg:flex items-center gap-2">
                                    <a href="{{ route('dashboard') }}"
                                    class="esf-header-link {{ request()->routeIs('dashboard') ? 'esf-header-link-active' : '' }}">
                                        Inicio
                                    </a>

                                </nav>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    onclick="
                                        const html = document.documentElement;
                                        const modoOscuroActivo = html.classList.toggle('dark');
                                        localStorage.setItem('tema-sistema-capacitacion', modoOscuroActivo ? 'oscuro' : 'claro');
                                    "
                                    class="esf-theme-toggle"
                                    title="Cambiar modo claro/oscuro"
                                >
                                    <span class="dark:hidden">🌙</span>
                                    <span class="hidden dark:inline">☀️</span>
                                </button>

                                <div class="relative">
                                    <button
                                        type="button"
                                        @click="perfilUsuarioAbierto = !perfilUsuarioAbierto"
                                        class="esf-header-link"
                                    >
                                        Sesión
                                        <span class="ml-1">▾</span>
                                    </button>

                                    <div
                                        x-show="perfilUsuarioAbierto"
                                        x-transition
                                        @click.away="perfilUsuarioAbierto = false"
                                        class="absolute right-0 mt-3 w-56 rounded-3xl bg-white/95 dark:bg-slate-900/95 shadow-xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden"
                                    >
                                        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                                            <p class="text-sm font-black text-slate-800 dark:text-slate-100">
                                                {{ auth()->user()->name }}
                                            </p>

                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ auth()->user()->email }}
                                            </p>
                                        </div>

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf

                                            <button
                                                type="submit"
                                                class="w-full text-left block px-4 py-3 text-sm font-bold text-slate-700 hover:bg-blue-50 dark:text-slate-200 dark:hover:bg-slate-800"
                                            >
                                                Cerrar sesión
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    @isset($header)
                        <section class="esf-user-page-header">
                            <div class="w-full max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </section>
                    @endisset

                    <main class="esf-user-content">
                        {{ $slot }}
                    </main>
                </div>
            </div>

        @elseif($usarLayoutAdmin)
    <div
        x-data="{ menuAdminAbierto: false, perfilAdminAbierto: false }"
        class="esf-shell"
            >
                {{-- FONDO OSCURO CUANDO SE ABRE EL MENÚ ADMIN --}}
                <div
                    x-show="menuAdminAbierto"
                    x-transition.opacity
                    @click="menuAdminAbierto = false"
                    class="esf-system-menu-overlay fixed inset-0 bg-black/40"
                ></div>

               {{-- MENÚ LATERAL ADMIN --}}
                <aside
                    x-show="menuAdminAbierto"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="esf-system-menu-sidebar fixed inset-y-0 left-0 w-80 esf-sidebar overflow-y-auto"
                >
                    <div class="p-5 border-b border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/logo-stb.png') }}"
                                alt="Logo STB"
                                class="h-11 w-11 rounded-2xl object-contain bg-white/80 border border-blue-100 p-1.5 shadow-sm">

                            <div>
                                <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400 font-bold">
                                    Accesos del sistema
                                </p>

                                <h2 class="mt-1 text-lg font-black text-slate-900 dark:text-slate-100">
                                    {{ $esAdminLayout ? 'Administración' : 'Instructor' }}
                                </h2>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="menuAdminAbierto = false"
                            class="esf-icon-button"
                            title="Cerrar menú"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/80">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                            Sesión activa
                        </p>

                        <p class="mt-1 text-sm font-extrabold text-slate-800 dark:text-slate-100">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Service and Trading Business
                        </p>
                    </div>

                    <nav class="p-4 space-y-5">
                        <div>
                            <p class="px-3 mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">
                                Principal
                            </p>

                            <a href="{{ url('/dashboard') }}"
                            class="esf-sidebar-link {{ request()->is('dashboard') ? 'esf-sidebar-link-active' : '' }}">
                                <span class="esf-sidebar-icon">IN</span>
                                <span>Inicio</span>
                            </a>
                        </div>

                        <div>
                            <p class="px-3 mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">
                                Gestión
                            </p>

                            <div class="space-y-2">
                                @if($esAdminLayout)
                                    <a href="{{ url('/usuarios') }}"
                                    class="esf-sidebar-link {{ request()->is('usuarios*') ? 'esf-sidebar-link-active' : '' }}">
                                        <span class="esf-sidebar-icon">US</span>
                                        <span>Usuarios</span>
                                    </a>

                                    <a href="{{ url('/instructores') }}"
                                    class="esf-sidebar-link {{ request()->is('instructores*') ? 'esf-sidebar-link-active' : '' }}">
                                        <span class="esf-sidebar-icon">IS</span>
                                        <span>Instructores</span>
                                    </a>
                                @endif

                                <a href="{{ url('/capacitaciones') }}"
                                class="esf-sidebar-link {{ request()->is('capacitaciones*') || request()->is('capacitacion-modulos*') || request()->is('capacitacion-recursos*') || request()->is('evaluaciones*') || request()->is('ejercicios*') ? 'esf-sidebar-link-active' : '' }}">
                                    <span class="esf-sidebar-icon">CA</span>
                                    <span>Capacitaciones</span>
                                </a>

                                @if($esAdminLayout)
                                    <a href="{{ url('/asignaciones-capacitacion') }}"
                                    class="esf-sidebar-link {{ request()->is('asignaciones-capacitacion*') ? 'esf-sidebar-link-active' : '' }}">
                                        <span class="esf-sidebar-icon">AS</span>
                                        <span>Asignaciones</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div>
                            <p class="px-3 mb-2 text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">
                                Planificación y control
                            </p>

                            <div class="space-y-2">
                                @if($esAdminLayout)
                                    <a href="{{ url('/matriz-puestos-capacitacion') }}"
                                    class="esf-sidebar-link {{ request()->is('matriz-puestos-capacitacion*') ? 'esf-sidebar-link-active' : '' }}">
                                        <span class="esf-sidebar-icon">MP</span>
                                        <span>Matriz por puesto</span>
                                    </a>

                                    <a href="{{ url('/necesidades-capacitacion') }}"
                                    class="esf-sidebar-link {{ request()->is('necesidades-capacitacion*') ? 'esf-sidebar-link-active' : '' }}">
                                        <span class="esf-sidebar-icon">NE</span>
                                        <span>Necesidades por empleado</span>
                                    </a>
                                @endif

                                <a href="{{ url('/seguimiento-capacitaciones') }}"
                                class="esf-sidebar-link {{ request()->is('seguimiento-capacitaciones*') ? 'esf-sidebar-link-active' : '' }}">
                                    <span class="esf-sidebar-icon">SG</span>
                                    <span>Seguimiento</span>
                                </a>

                                @if($esAdminLayout)
                                    <a href="{{ url('/reportes') }}"
                                    class="esf-sidebar-link {{ request()->is('reportes*') ? 'esf-sidebar-link-active' : '' }}">
                                        <span class="esf-sidebar-icon">RP</span>
                                        <span>Reportes</span>
                                    </a>

                                    <a href="{{ url('/avisos-correo') }}"
                                    class="esf-sidebar-link {{ request()->is('avisos-correo*') ? 'esf-sidebar-link-active' : '' }}">
                                        <span class="esf-sidebar-icon">AV</span>
                                        <span>Avisos por correo</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </nav>
                </aside>

                {{-- CONTENIDO PRINCIPAL ADMIN --}}
                <div class="min-h-screen">
                    {{-- HEADER ADMIN --}}
                    <header class="esf-header esf-system-fixed-header">
                        <div class="w-full max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <button
                                    type="button"
                                    @click="menuAdminAbierto = true"
                                    class="esf-icon-button"
                                    title="Abrir menú"
                                >
                                    <span class="text-xl leading-none">☰</span>
                                </button>

                                <img src="{{ asset('images/logo-stb.png') }}"
                                        alt="Logo STB"
                                        class="h-11 w-11 rounded-xl object-contain">

                                <div class="hidden sm:block">
                                    <p class="text-[11px] uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                        Sistema de capacitaciones
                                    </p>

                                    <p class="text-sm font-black text-slate-800 dark:text-slate-100">
                                        Service and Trading Business
                                    </p>
                                </div>

                                <nav class="hidden lg:flex items-center gap-2">
                                    <a href="{{ url('/dashboard') }}"
                                    class="esf-header-link {{ request()->is('dashboard') ? 'esf-header-link-active' : '' }}">
                                        Inicio
                                    </a>

                                    @if($esAdminLayout)
                                        <a href="{{ url('/usuarios') }}"
                                        class="esf-header-link {{ request()->is('usuarios*') ? 'esf-header-link-active' : '' }}">
                                            Usuarios
                                        </a>

                                        <a href="{{ url('/asignaciones-capacitacion') }}"
                                        class="esf-header-link {{ request()->is('asignaciones-capacitacion*') ? 'esf-header-link-active' : '' }}">
                                            Asignaciones
                                        </a>
                                    @endif
                                </nav>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    onclick="
                                        const html = document.documentElement;
                                        const modoOscuroActivo = html.classList.toggle('dark');
                                        localStorage.setItem('tema-sistema-capacitacion', modoOscuroActivo ? 'oscuro' : 'claro');
                                    "
                                    class="esf-theme-toggle"
                                    title="Cambiar modo claro/oscuro"
                                >
                                    <span class="dark:hidden">🌙</span>
                                    <span class="hidden dark:inline">☀️</span>
                                </button>

                                <div class="relative">
                                    <button
                                        type="button"
                                        @click="perfilAdminAbierto = !perfilAdminAbierto"
                                        class="esf-header-link"
                                    >
                                        Sesión
                                        <span class="ml-1">▾</span>
                                    </button>

                                <div
                                    x-show="perfilAdminAbierto"
                                    x-transition
                                    @click.away="perfilAdminAbierto = false"
                                    class="absolute right-0 mt-3 w-56 rounded-2xl bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden"
                                >
                                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                                        <p class="text-sm font-black text-slate-800 dark:text-slate-100">
                                            {{ auth()->user()->name }}
                                        </p>

                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $esAdminLayout ? 'Cuenta administrativa' : 'Cuenta instructor' }}
                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <button
                                            type="submit"
                                            class="w-full text-left block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-blue-50 dark:text-slate-200 dark:hover:bg-slate-800"
                                        >
                                            Cerrar sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </header>

                    @isset($header)
                        <section class="bg-transparent">
                            <div class="w-full max-w-[1500px] mx-auto pt-6 pb-3 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </section>
                    @endisset

                    <main class="esf-admin-content">
                        {{ $slot }}
                    </main>
                </div>
            </div>

        @else
            <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
                @include('layouts.navigation')

                @isset($header)
                    <header class="bg-white dark:bg-gray-800 shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="esf-default-content">
                    {{ $slot }}
                </main>
            </div>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const erroresSistema = @json($errors->getMessages());
                const oldSistema = @json(session()->getOldInput());

                function hayObjetoConDatos(objeto) {
                    return objeto && typeof objeto === 'object' && Object.keys(objeto).length > 0;
                }

                function crearIdFormularioSistema(formulario, indice) {
                    const metodo = (formulario.getAttribute('method') || 'GET').toUpperCase();
                    const accion = formulario.getAttribute('action') || window.location.pathname;

                    return 'form-' + indice + '-' + btoa(unescape(encodeURIComponent(metodo + '|' + accion))).replace(/=+/g, '');
                }

                const formulariosSistema = Array.from(document.querySelectorAll('form'));

                formulariosSistema.forEach(function (formulario, indice) {
                    formulario.dataset.formularioSistemaId = crearIdFormularioSistema(formulario, indice);

                    formulario.addEventListener('submit', function () {
                        let inputActivo = formulario.querySelector('input[name="__formulario_activo"]');

                        if (!inputActivo) {
                            inputActivo = document.createElement('input');
                            inputActivo.type = 'hidden';
                            inputActivo.name = '__formulario_activo';
                            formulario.appendChild(inputActivo);
                        }

                        inputActivo.value = formulario.dataset.formularioSistemaId;
                    }, true);
                });

                function aplanarObjetoSistema(objeto, prefijo = '') {
                    const resultado = {};

                    if (!objeto || typeof objeto !== 'object') {
                        return resultado;
                    }

                    Object.keys(objeto).forEach(function (clave) {
                        if (clave === '__formulario_activo') {
                            return;
                        }

                        const valor = objeto[clave];
                        const nuevaClave = prefijo ? prefijo + '.' + clave : clave;

                        if (valor !== null && typeof valor === 'object' && !Array.isArray(valor)) {
                            Object.assign(resultado, aplanarObjetoSistema(valor, nuevaClave));
                            return;
                        }

                        if (Array.isArray(valor)) {
                            valor.forEach(function (item, index) {
                                if (item !== null && typeof item === 'object') {
                                    Object.assign(resultado, aplanarObjetoSistema(item, nuevaClave + '.' + index));
                                } else {
                                    resultado[nuevaClave + '.' + index] = item;
                                }
                            });

                            return;
                        }

                        resultado[nuevaClave] = valor;
                    });

                    return resultado;
                }

                function obtenerFormularioActivoSistema() {
                    const idActivo = oldSistema && oldSistema.__formulario_activo
                        ? oldSistema.__formulario_activo
                        : null;

                    if (!idActivo) {
                        return null;
                    }

                    return document.querySelector('form[data-formulario-sistema-id="' + idActivo + '"]');
                }

                function obtenerCamposSistema(nombre, formularioPreferido = null) {
                    const raiz = formularioPreferido || document;
                    const campos = Array.from(raiz.querySelectorAll('input, select, textarea'));

                    let encontrados = campos.filter(function (campo) {
                        return campo.name === nombre;
                    });

                    if (encontrados.length > 0) {
                        return encontrados;
                    }

                    const partes = String(nombre).split('.');

                    if (partes.length >= 2 && /^\d+$/.test(partes[partes.length - 1])) {
                        const indice = parseInt(partes[partes.length - 1], 10);
                        const base = partes.slice(0, -1).join('.');

                        encontrados = campos.filter(function (campo) {
                            return campo.name === base + '[]';
                        });

                        if (encontrados[indice]) {
                            return [encontrados[indice]];
                        }
                    }

                    return [];
                }

                function asignarValorCampoSistema(campo, valor) {
                    if (!campo || campo.type === 'file' || campo.name === '__formulario_activo') {
                        return;
                    }

                    if (campo.type === 'checkbox') {
                        if (Array.isArray(valor)) {
                            campo.checked = valor.map(String).includes(String(campo.value));
                        } else {
                            campo.checked = String(campo.value) === String(valor) || String(valor) === '1';
                        }

                        return;
                    }

                    if (campo.type === 'radio') {
                        campo.checked = String(campo.value) === String(valor);
                        return;
                    }

                    if (campo.tagName === 'SELECT' && campo.multiple && Array.isArray(valor)) {
                        Array.from(campo.options).forEach(function (opcion) {
                            opcion.selected = valor.map(String).includes(String(opcion.value));
                        });

                        return;
                    }

                    campo.value = valor ?? '';
                }

                function obtenerContenedorErrorSistema(campo) {
                    return campo.closest('.mb-4, .space-y-1, .esf-admin-modal-full, [data-bloque-pagina-seccion], .seccion-modulo-item, .esf-admin-modal-card')
                        || campo.parentElement
                        || campo;
                }

                function insertarErrorSistema(campo, mensaje) {
                    const contenedor = obtenerContenedorErrorSistema(campo);

                    if (!contenedor) {
                        return;
                    }

                    const errorAnterior = contenedor.querySelector(':scope > .esf-form-inline-error');

                    if (errorAnterior) {
                        errorAnterior.remove();
                    }

                    const error = document.createElement('div');
                    error.className = 'esf-form-inline-error';
                    error.textContent = mensaje;

                    if (campo.parentElement && campo.parentElement !== contenedor) {
                        campo.parentElement.insertAdjacentElement('afterend', error);
                    } else {
                        campo.insertAdjacentElement('afterend', error);
                    }

                    campo.classList.add('esf-form-field-invalid');
                }

                function abrirLugarDelErrorSistema(campo) {
                    if (!campo) {
                        return;
                    }

                    const modal = campo.closest('.modal-builder, [id^="modalCrear"], [id^="modalEditar"], .fixed.inset-0');

                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');

                        setTimeout(function () {
                            try {
                                modal.scrollTop = Math.max(0, campo.getBoundingClientRect().top + modal.scrollTop - 180);
                            } catch (error) {
                                //
                            }
                        }, 80);
                    }

                    const bloque = campo.closest('[data-bloque-pagina-seccion], .seccion-modulo-item, .esf-admin-modal-card, form');

                    setTimeout(function () {
                        (bloque || campo).scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'nearest'
                        });

                        if (typeof campo.focus === 'function' && campo.type !== 'hidden') {
                            campo.focus({ preventScroll: true });
                        }
                    }, 120);
                }

                const formularioActivoSistema = obtenerFormularioActivoSistema();

                if (hayObjetoConDatos(oldSistema)) {
                    const oldPlano = aplanarObjetoSistema(oldSistema);

                    Object.keys(oldPlano).forEach(function (nombre) {
                        const campos = obtenerCamposSistema(nombre, formularioActivoSistema);

                        campos.forEach(function (campo) {
                            asignarValorCampoSistema(campo, oldPlano[nombre]);
                        });
                    });
                }

                if (hayObjetoConDatos(erroresSistema)) {
                    let primerCampoError = null;

                    Object.keys(erroresSistema).forEach(function (nombre) {
                        const mensajes = erroresSistema[nombre] || [];
                        const mensaje = Array.isArray(mensajes) ? mensajes[0] : mensajes;
                        const campos = obtenerCamposSistema(nombre, formularioActivoSistema);

                        if (campos.length === 0) {
                            return;
                        }

                        campos.forEach(function (campo) {
                            insertarErrorSistema(campo, mensaje);
                        });

                        if (!primerCampoError) {
                            primerCampoError = campos[0];
                        }
                    });

                    if (primerCampoError) {
                        abrirLugarDelErrorSistema(primerCampoError);
                    }
                }
            });
        </script>

                   <script>
            (function () {
                const rutasPermitidas = [
                    '/seguimiento-capacitaciones',
                    '/reportes'
                ];

                const rutaActual = window.location.pathname;

                const estaEnSeguimientoOReportes = rutasPermitidas.some(function (ruta) {
                    return rutaActual === ruta || rutaActual.startsWith(ruta + '/');
                });

                if (!estaEnSeguimientoOReportes) {
                    return;
                }

                const prefijoClave = 'stb_retorno_exacto:';

                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }

                function clavePaginaActual() {
                    return prefijoClave + window.location.pathname + window.location.search;
                }

                function esUrlInterna(url) {
                    try {
                        const objetoUrl = new URL(url, window.location.origin);

                        return objetoUrl.origin === window.location.origin;
                    } catch (error) {
                        return false;
                    }
                }

                function normalizarSelectorId(id) {
                    if (!id) {
                        return null;
                    }

                    try {
                        return '#' + CSS.escape(id);
                    } catch (error) {
                        return '#' + id.replace(/([^a-zA-Z0-9_-])/g, '\\$1');
                    }
                }

                function obtenerIndiceEntreHermanos(elemento) {
                    let indice = 1;
                    let hermano = elemento;

                    while ((hermano = hermano.previousElementSibling) !== null) {
                        if (hermano.tagName === elemento.tagName) {
                            indice++;
                        }
                    }

                    return indice;
                }

                function crearSelectorExacto(elemento) {
                    if (!elemento || elemento === document.body || elemento === document.documentElement) {
                        return 'body';
                    }

                    if (elemento.id) {
                        return normalizarSelectorId(elemento.id);
                    }

                    const partes = [];
                    let actual = elemento;

                    while (
                        actual
                        && actual.nodeType === Node.ELEMENT_NODE
                        && actual !== document.body
                        && actual !== document.documentElement
                    ) {
                        if (actual.id) {
                            partes.unshift(normalizarSelectorId(actual.id));
                            break;
                        }

                        const tag = actual.tagName.toLowerCase();
                        const indice = obtenerIndiceEntreHermanos(actual);

                        partes.unshift(tag + ':nth-of-type(' + indice + ')');

                        actual = actual.parentElement;
                    }

                    return partes.length > 0 ? partes.join(' > ') : 'body';
                }

                function obtenerPuntoReferencia(elemento) {
                    if (!elemento) {
                        return null;
                    }

                    return elemento.closest(
                        '[data-restaurar-punto], ' +
                        '[data-seguimiento-row], ' +
                        '[data-reporte-row], ' +
                        'tr, ' +
                        'article, ' +
                        '.esf-training-card, ' +
                        '.esf-report-card, ' +
                        '.esf-admin-sheet-card, ' +
                        '.esf-history-card, ' +
                        '.esf-seguimiento-table-card, ' +
                        '.esf-page-card, ' +
                        '.rounded-3xl, ' +
                        '.rounded-2xl, ' +
                        'form'
                    ) || elemento;
                }

                function guardarPosicionExacta(elementoOrigen) {
                    const puntoReferencia = obtenerPuntoReferencia(elementoOrigen);
                    const scrollActual = window.scrollY || window.pageYOffset || 0;

                    const datos = {
                        url: window.location.pathname + window.location.search,
                        scrollY: scrollActual,
                        selector: puntoReferencia ? crearSelectorExacto(puntoReferencia) : null,
                        fecha: Date.now()
                    };

                    try {
                        sessionStorage.setItem(clavePaginaActual(), JSON.stringify(datos));
                    } catch (error) {
                        //
                    }
                }

                function restaurarPosicionExacta() {
                    let datos = null;
                    const valor = sessionStorage.getItem(clavePaginaActual());

                    if (!valor) {
                        return;
                    }

                    try {
                        datos = JSON.parse(valor);
                    } catch (error) {
                        datos = null;
                    }

                    if (!datos) {
                        return;
                    }

                    function ejecutarRestauracion() {
                        let elemento = null;

                        if (datos.selector) {
                            try {
                                elemento = document.querySelector(datos.selector);
                            } catch (error) {
                                elemento = null;
                            }
                        }

                        if (elemento) {
                            elemento.scrollIntoView({
                                behavior: 'auto',
                                block: 'center',
                                inline: 'nearest'
                            });

                            return;
                        }

                        window.scrollTo({
                            top: parseInt(datos.scrollY, 10) || 0,
                            left: 0,
                            behavior: 'auto'
                        });
                    }

                    requestAnimationFrame(function () {
                        ejecutarRestauracion();
                        setTimeout(ejecutarRestauracion, 150);
                        setTimeout(ejecutarRestauracion, 400);
                        setTimeout(ejecutarRestauracion, 800);
                    });
                }

                function elementoDebeGuardarPosicion(elemento) {
                    if (!elemento) {
                        return false;
                    }

                    if (elemento.closest('[data-no-restaurar-posicion="1"]')) {
                        return false;
                    }

                    const enlace = elemento.closest('a[href]');

                    if (enlace) {
                        const href = enlace.getAttribute('href') || '';

                        if (
                            href === ''
                            || href === '#'
                            || href.startsWith('#')
                            || href.startsWith('javascript:')
                            || href.startsWith('mailto:')
                            || href.startsWith('tel:')
                        ) {
                            return false;
                        }

                        return esUrlInterna(enlace.href);
                    }

                    const boton = elemento.closest('button, input[type="button"], input[type="submit"], input[type="image"]');

                    if (boton) {
                        return true;
                    }

                    return false;
                }

                document.addEventListener('pointerdown', function (event) {
                    if (!elementoDebeGuardarPosicion(event.target)) {
                        return;
                    }

                    guardarPosicionExacta(event.target);
                }, true);

                document.addEventListener('click', function (event) {
                    if (!elementoDebeGuardarPosicion(event.target)) {
                        return;
                    }

                    guardarPosicionExacta(event.target);
                }, true);

                document.addEventListener('submit', function (event) {
                    guardarPosicionExacta(event.target);
                }, true);

                window.addEventListener('pagehide', function () {
                    guardarPosicionExacta(document.activeElement);
                });

                document.addEventListener('DOMContentLoaded', function () {
                    restaurarPosicionExacta();
                });

                window.addEventListener('pageshow', function () {
                    restaurarPosicionExacta();
                });
            })();
        </script>
    </body>
</html>