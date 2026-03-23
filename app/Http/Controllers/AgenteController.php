<?php

namespace App\Http\Controllers;

use App\Models\Agente;
use App\Models\User;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AgenteController extends Controller
{
    public function index(Request $request)
    {
        $q = Agente::with(['user.perfil', 'patente', 'unidade']);

        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('unidade_id')) $q->where('unidade_id', $request->unidade_id);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('nome', 'like', "%$b%")->orWhere('nip', 'like', "%$b%")->orWhere('bi', 'like', "%$b%"));
        }

        return response()->json($q->orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:200',
            'nip' => 'required|string|max:50|unique:agentes,nip',
            'email' => 'required|email|unique:users,email',
            'patente_id' => 'required|exists:patentes,id',
            'cargo' => 'required|string|max:100',
            'unidade_id' => 'required|exists:unidades,id',
            'perfil_id' => 'required|exists:perfis,id',
            'estado' => 'required|in:activo,inactivo',
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make(config('auth.default_agent_password')),
                'perfil_id' => $request->perfil_id,
                'estado' => $request->estado,
            ]);

            $agente = Agente::create(array_merge(
                $request->only(['nome', 'nip', 'bi', 'data_nascimento', 'sexo', 'telefone', 'patente_id', 'cargo', 'unidade_id', 'estado']),
                ['user_id' => $user->id, 'data_admissao' => $request->data_admissao ?? now()]
            ));

            Log::registar('criar', 'agentes', $agente->id, "Agente {$agente->nome} criado");

            return response()->json([
                'success' => true, 'message' => 'Agente registado.',
                'agente' => $agente->load(['user.perfil', 'patente', 'unidade']),
            ], 201);
        });
    }

    public function show(Agente $agente)
    {
        return response()->json($agente->load(['user.perfil', 'patente', 'unidade', 'ocorrenciasResponsavel.tipoCrime', 'detencoes', 'investigacoes']));
    }

    public function update(Request $request, Agente $agente)
    {
        $request->validate([
            'nome' => 'required|string|max:200',
            'nip' => 'required|string|max:50|unique:agentes,nip,' . $agente->id,
            'estado' => 'required|in:activo,inactivo,suspenso,transferido',
        ]);

        $agente->update($request->only(['nome', 'nip', 'bi', 'data_nascimento', 'sexo', 'telefone', 'morada', 'patente_id', 'cargo', 'unidade_id', 'estado']));
        if ($request->filled('perfil_id')) $agente->user->update(['perfil_id' => $request->perfil_id]);
        $agente->user->update(['estado' => $request->estado]);

        Log::registar('editar', 'agentes', $agente->id, "Agente actualizado");

        return response()->json(['success' => true, 'message' => 'Agente actualizado.', 'agente' => $agente->fresh(['user.perfil', 'patente', 'unidade'])]);
    }

    public function toggleEstado(Agente $agente)
    {
        $novo = $agente->estado === 'activo' ? 'inactivo' : 'activo';
        $agente->update(['estado' => $novo]);
        $agente->user->update(['estado' => $novo]);
        Log::registar('editar', 'agentes', $agente->id, "Estado alterado para {$novo}");
        return response()->json(['success' => true, 'message' => "Agente {$novo}."]);
    }
}
