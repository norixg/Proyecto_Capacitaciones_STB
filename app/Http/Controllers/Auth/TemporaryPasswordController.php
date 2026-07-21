<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TemporaryPasswordController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $usuario = $request->user();

        if (!$usuario instanceof User || !$usuario->debeCambiarPassword()) {
            return redirect()->route('dashboard');
        }

        return view('auth.cambiar-password-temporal', [
            'passwordTemporalExpirada' => $usuario->passwordTemporalExpirada(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        if (!$usuario instanceof User || !$usuario->debeCambiarPassword()) {
            return redirect()->route('dashboard');
        }

        if ($usuario->passwordTemporalExpirada()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'La contraseña temporal venció. Solicita al administrador una nueva.',
            ]);
        }

        $validated = $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password' => [
                'required',
                Password::min(12)->mixedCase()->letters()->numbers()->symbols(),
                'confirmed',
                'different:password_actual',
            ],
        ]);

        $usuario->forceFill([
            'password' => $validated['password'],
            'debe_cambiar_password' => 0,
            'password_temporal_expira_en' => null,
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Tu contraseña fue establecida correctamente.');
    }
}
