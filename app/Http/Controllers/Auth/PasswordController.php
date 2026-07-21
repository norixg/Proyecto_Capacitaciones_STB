<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        if ($request->user()?->debeCambiarPassword()) {
            return redirect()->route('password.temporal.edit');
        }

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'debe_cambiar_password' => 0,
            'password_temporal_expira_en' => null,
            'remember_token' => Str::random(60),
        ]);

        $request->session()->regenerate();

        return back()->with('status', 'password-updated');
    }
}
