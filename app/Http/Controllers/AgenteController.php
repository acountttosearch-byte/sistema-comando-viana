<?php

namespace App\Http\Controllers;

use App\Models\Agente;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AgenteController extends Controller
{
    private const CODIGOS_BI_PROVINCIAS = [
        'BO' => 'Bengo',
        'BE' => 'Benguela',
        'BI' => 'Bie',
        'CA' => 'Cabinda',
        'CD' => 'Cuando',
        'CB' => 'Cubango',
        'CN' => 'Cuanza Norte',
        'CS' => 'Cuanza Sul',
        'CE' => 'Cunene',
        'HB' => 'Huambo',
        'HL' => 'Huila',
        'IB' => 'Icolo e Bengo',
        'LA' => 'Luanda',
        'LN' => 'Lunda Norte',
        'LS' => 'Lunda Sul',
        'MA' => 'Malanje',
        'MO' => 'Moxico',
        'ML' => 'Moxico Leste',
        'NA' => 'Namibe',
        'UI' => 'Uige',
        'ZA' => 'Zaire',
    ];

    public function index(Request $request)
    {
        $perfil = auth()->user()->perfil->nome;
        $unidadeId = auth()->user()->agente?->unidade_id;
        $q = Agente::with(['user.perfil', 'patente', 'unidade']);

        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('unidade_id') && in_array($perfil, ['admin', 'comandante'], true)) {
            $q->where('unidade_id', $request->unidade_id);
        } elseif (!in_array($perfil, ['admin', 'comandante'], true) && $unidadeId) {
            $q->where('unidade_id', $unidadeId);
        }
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('nome', 'like', "%$b%")->orWhere('nip', 'like', "%$b%")->orWhere('bi', 'like', "%$b%"));
        }

        return response()->json($q->orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $this->normalizarDadosAgente($request, true);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'min:3', 'max:200', 'regex:/^[\pL\s\'-]+$/u'],
            'nip' => ['required', 'string', 'max:50', 'regex:/^NIP-\d{5}$/', 'unique:agentes,nip'],
            'bi' => ['required', 'string', 'max:30', 'regex:/^\d{10}[A-Z]{2}\d{3}$/', $this->regraCodigoProvinciaBI(), 'unique:agentes,bi'],
            'email' => ['required', 'email:rfc', 'max:150', 'ends_with:@policia-viana.ao', 'unique:users,email'],
            'telefone' => ['required', 'string', 'max:20', 'regex:/^\+2449\d{8}$/', 'unique:agentes,telefone'],
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'sexo' => 'nullable|in:M,F',
            'patente_id' => ['required', Rule::exists('patentes', 'id')->where(fn ($q) => $q->where('nome', '!=', 'Chefe'))],
            'unidade_id' => ['required', Rule::exists('unidades', 'id')->where(fn ($q) => $q->where('estado', 'activo'))],
            'perfil_id' => ['required', Rule::exists('perfis', 'id')->where(fn ($q) => $q->where('nome', '!=', 'admin'))],
            'estado' => 'required|in:activo,inactivo',
            'data_admissao' => 'nullable|date|before_or_equal:today',
        ], [
            'nome.regex' => 'O nome deve conter apenas letras, espacos, apostrofos ou hifens.',
            'nip.regex' => 'Informe um NIP valido no formato NIP-00000.',
            'bi.regex' => 'Informe um BI angolano valido. Ex: 0012345678LA042.',
            'email.ends_with' => 'O email institucional deve terminar com @policia-viana.ao.',
            'telefone.regex' => 'Informe um telefone movel angolano valido. Ex: +244 923 000 000.',
            'nip.unique' => 'Ja existe um agente com este NIP.',
            'bi.unique' => 'Ja existe um agente registado com este numero de BI.',
            'email.unique' => 'Ja existe um utilizador com este email.',
            'telefone.unique' => 'Ja existe um agente registado com este numero de telefone.',
        ]);

        return DB::transaction(function () use ($dados) {
            $user = User::create([
                'email' => Str::lower(trim($dados['email'])),
                'password' => Hash::make(config('auth.default_agent_password')),
                'perfil_id' => $dados['perfil_id'],
                'estado' => $this->estadoUser($dados['estado']),
            ]);

            $agente = Agente::create([
                'user_id' => $user->id,
                'nome' => $dados['nome'],
                'nip' => $dados['nip'],
                'bi' => $dados['bi'] ?? null,
                'data_nascimento' => $dados['data_nascimento'] ?? null,
                'sexo' => $dados['sexo'] ?? null,
                'telefone' => $dados['telefone'] ?? null,
                'patente_id' => $dados['patente_id'],
                'unidade_id' => $dados['unidade_id'],
                'estado' => $dados['estado'],
                'data_admissao' => $dados['data_admissao'] ?? now(),
            ]);

            Log::registar('criar', 'agentes', $agente->id, "Agente {$agente->nome} criado");

            return response()->json([
                'success' => true,
                'message' => 'Agente registado.',
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
        $this->normalizarDadosAgente($request, false);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'min:3', 'max:200', 'regex:/^[\pL\s\'-]+$/u'],
            'nip' => ['required', 'string', 'max:50', 'regex:/^NIP-\d{5}$/', 'unique:agentes,nip,' . $agente->id],
            'bi' => ['required', 'string', 'max:30', 'regex:/^\d{10}[A-Z]{2}\d{3}$/', $this->regraCodigoProvinciaBI(), 'unique:agentes,bi,' . $agente->id],
            'telefone' => ['required', 'string', 'max:20', 'regex:/^\+2449\d{8}$/', 'unique:agentes,telefone,' . $agente->id],
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'sexo' => 'nullable|in:M,F',
            'morada' => 'nullable|string|max:300',
            'patente_id' => ['required', Rule::exists('patentes', 'id')->where(fn ($q) => $q->where('nome', '!=', 'Chefe'))],
            'unidade_id' => ['required', Rule::exists('unidades', 'id')->where(fn ($q) => $q->where('estado', 'activo'))],
            'perfil_id' => ['nullable', Rule::exists('perfis', 'id')->where(fn ($q) => $q->where('nome', '!=', 'admin'))],
            'estado' => 'required|in:activo,inactivo,suspenso,transferido',
        ], [
            'nome.regex' => 'O nome deve conter apenas letras, espacos, apostrofos ou hifens.',
            'nip.regex' => 'Informe um NIP valido no formato NIP-00000.',
            'bi.regex' => 'Informe um BI angolano valido. Ex: 0012345678LA042.',
            'telefone.regex' => 'Informe um telefone movel angolano valido. Ex: +244 923 000 000.',
            'nip.unique' => 'Ja existe um agente com este NIP.',
            'bi.unique' => 'Ja existe um agente registado com este numero de BI.',
            'telefone.unique' => 'Ja existe um agente registado com este numero de telefone.',
        ]);

        $agente->update([
            'nome' => $dados['nome'],
            'nip' => $dados['nip'],
            'bi' => $dados['bi'] ?? null,
            'data_nascimento' => $dados['data_nascimento'] ?? null,
            'sexo' => $dados['sexo'] ?? null,
            'telefone' => $dados['telefone'] ?? null,
            'morada' => $dados['morada'] ?? null,
            'patente_id' => $dados['patente_id'],
            'unidade_id' => $dados['unidade_id'],
            'estado' => $dados['estado'],
        ]);

        if (!empty($dados['perfil_id'])) {
            $agente->user->update(['perfil_id' => $dados['perfil_id']]);
        }

        $agente->user->update(['estado' => $this->estadoUser($dados['estado'])]);
        Log::registar('editar', 'agentes', $agente->id, 'Agente actualizado');

        return response()->json(['success' => true, 'message' => 'Agente actualizado.', 'agente' => $agente->fresh(['user.perfil', 'patente', 'unidade'])]);
    }

    public function toggleEstado(Agente $agente)
    {
        $novo = $agente->estado === 'activo' ? 'inactivo' : 'activo';
        $agente->update(['estado' => $novo]);
        $agente->user->update(['estado' => $this->estadoUser($novo)]);
        Log::registar('editar', 'agentes', $agente->id, "Estado alterado para {$novo}");

        return response()->json(['success' => true, 'message' => "Agente {$novo}."]);
    }

    private function estadoUser(string $estadoAgente): string
    {
        return match ($estadoAgente) {
            'activo' => 'activo',
            'suspenso' => 'bloqueado',
            default => 'inactivo',
        };
    }

    private function normalizarDadosAgente(Request $request, bool $incluirEmail): void
    {
        $dados = [
            'nome' => preg_replace('/\s+/u', ' ', trim((string) $request->input('nome'))),
            'nip' => $this->normalizarNip((string) $request->input('nip')),
            'bi' => $request->filled('bi') ? preg_replace('/[^0-9A-Z]/', '', Str::upper((string) $request->input('bi'))) : null,
            'telefone' => $request->filled('telefone') ? $this->normalizarTelefone((string) $request->input('telefone')) : null,
        ];

        if ($incluirEmail) {
            $dados['email'] = Str::lower(trim((string) $request->input('email')));
        }

        $request->merge($dados);
    }

    private function normalizarNip(string $nip): string
    {
        $nip = Str::upper(preg_replace('/[^0-9A-Z-]/', '', $nip));
        $numero = preg_replace('/\D/', '', preg_replace('/^NIP-?/i', '', $nip));

        return $numero !== '' ? 'NIP-' . substr($numero, 0, 5) : substr($nip, 0, 9);
    }

    private function normalizarTelefone(string $telefone): string
    {
        $digits = preg_replace('/\D+/', '', $telefone);

        if (Str::startsWith($digits, '244')) {
            return '+' . substr($digits, 0, 12);
        }

        if (Str::startsWith($digits, '9')) {
            return '+244' . substr($digits, 0, 9);
        }

        return substr($digits, 0, 12);
    }

    private function regraCodigoProvinciaBI(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $codigoProvincia = substr((string) $value, 10, 2);

            if (!array_key_exists($codigoProvincia, self::CODIGOS_BI_PROVINCIAS)) {
                $fail("O codigo provincial do BI ({$codigoProvincia}) nao corresponde a uma provincia valida de Angola.");
            }
        };
    }
}
