<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $q = Log::with('user');
        if ($request->filled('acao')) $q->where('acao', $request->acao);
        if ($request->filled('tabela')) $q->where('tabela', $request->tabela);
        if ($request->filled('user_id')) $q->where('user_id', $request->user_id);
        if ($request->filled('data_inicio')) $q->where('created_at', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $q->where('created_at', '<=', $request->data_fim);
        return response()->json($q->orderByDesc('created_at')->paginate(50));
    }
}