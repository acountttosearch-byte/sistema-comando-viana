<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPerfil
{
    public function handle(Request $request, Closure $next, string ...$perfis)
    {
        if (!auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Não autenticado.'], 401)
                : redirect()->route('login');
        }

        if (!auth()->user()->temAlgumPerfil($perfis)) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Perfil não autorizado.'], 403)
                : abort(403);
        }

        return $next($request);
    }
}