<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        $roles = collect($roles)
            ->map(fn ($rol) => strtolower(trim($rol)))
            ->filter()
            ->values()
            ->toArray();

        /*
        * 1. Primero intenta validar con Spatie.
        * Esto es lo que nos pidieron para seguridad formal.
        */
        if (method_exists($usuario, 'hasAnyRole') && $usuario->hasAnyRole($roles)) {
            return $next($request);
        }

        /*
        * 2. Si por alguna razón Spatie todavía no está sincronizado,
        * usa el sistema viejo como respaldo para no romper el proyecto.
        */
        if (method_exists($usuario, 'tieneRolSistema') && $usuario->tieneRolSistema($roles)) {
            return $next($request);
        }

        abort(403, 'No tienes permiso para acceder a esta sección.');
    }
}