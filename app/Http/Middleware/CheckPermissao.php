<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermissao
{
    public function handle(Request $request, Closure $next, string $permissao)
    {
        if (!auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Não autenticado.'], 401)
                : redirect()->route('login');
        }

        if (!auth()->user()->isAdmin() && !auth()->user()->temPermissao($permissao)) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Sem permissão.'], 403)
                : abort(403);
        }

        return $next($request);
    }
}