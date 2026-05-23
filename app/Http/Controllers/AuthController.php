<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('painel');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:150',
            'password' => 'required|string|max:255',
        ]);

        $email = Str::lower(trim($request->email));
        $key = 'login:' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Muitas tentativas de login. Aguarde alguns minutos e tente novamente.',
            ]);
        }

        if (Auth::attempt(['email' => $email, 'password' => $request->password, 'estado' => 'activo'])) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update([
                'ultimo_acesso' => now(),
                'ip_ultimo_acesso' => $request->ip(),
            ]);

            Log::registar('login', 'users', $user->id, 'Login realizado');

            return redirect()->intended(route('painel'));
        }

        RateLimiter::hit($key, 300);

        return back()
            ->withErrors(['email' => 'Credenciais invalidas ou utilizador inactivo.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Log::registar('logout', 'users', auth()->id(), 'Logout realizado');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
