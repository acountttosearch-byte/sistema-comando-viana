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

        $q->orderBy('nome');

        if ($request->filled('page') || $request->filled('per_page')) {
            $perPage = min((int) $request->input('per_page', 10), 50);
            return response()->json($q->paginate($perPage));
        }

        return response()->json($q->get());
    }

    public function proximoNip()
    {
        return response()->json(['nip' => $this->gerarProximoNip()]);
    }

    public function store(Request $request)
    {
        $this->normalizarDadosAgente($request, true);
        $request->merge(['nip' => $this->gerarProximoNip()]);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'min:3', 'max:200', 'regex:/^[\pL\s\'-]+$/u'],
            'nip' => ['required', 'string', 'max:50', 'regex:/^NIP-\d{5}$/', 'unique:agentes,nip'],
            'bi' => ['required', 'string', 'max:30', 'regex:/^\d{10}[A-Z]{2}\d{3}$/', $this->regraCodigoProvinciaBI(), 'unique:agentes,bi'],
            'email' => ['required', 'email:rfc', 'max:150', 'regex:/^[a-z._-]+@policia-viana\.ao$/', $this->regraEmailPrimeiroNome(), 'unique:users,email'],
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
            'email.regex' => 'O email institucional deve usar apenas letras e os separadores ponto, hifen ou underscore antes de @policia-viana.ao.',
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
        abort_unless($this->podeVerAgente($agente), 403, 'Sem permissao para consultar este agente.');

        $agente->load([
            'user.perfil',
            'patente',
            'unidade.tipoUnidade',
            'ocorrenciasRegistadas' => fn ($q) => $q->with(['tipoCrime', 'estado', 'unidade'])->latest('data_ocorrencia')->limit(10),
            'ocorrenciasResponsavel' => fn ($q) => $q->with(['tipoCrime', 'estado', 'unidade'])->latest('data_ocorrencia')->limit(10),
            'detencoes' => fn ($q) => $q->with(['pessoa', 'estado', 'ocorrencia.tipoCrime'])->latest('data_detencao')->limit(10),
            'investigacoes' => fn ($q) => $q->with(['ocorrencia.tipoCrime', 'estado'])->latest('data_inicio')->limit(10),
            'patrulhas' => fn ($q) => $q->with(['turno', 'zona', 'unidade', 'viatura'])->latest('data')->limit(10),
            'armamentoAtribuido.armamento.tipoArmamento',
            'viaturasAtribuidas' => fn ($q) => $q->with('viatura')->whereNull('data_retorno')->latest('data_saida')->limit(5),
        ])->loadCount([
            'ocorrenciasRegistadas',
            'ocorrenciasResponsavel',
            'detencoes',
            'investigacoes',
            'patrulhas',
            'alertasCriados',
            'despachosRecebidos',
            'evidenciasRegistadas',
        ]);

        return response()->json($agente);
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

    private function podeVerAgente(Agente $agente): bool
    {
        $user = auth()->user();
        $perfil = $user?->perfil?->nome;
        $agenteAtual = $user?->agente;

        if (in_array($perfil, ['admin', 'comandante'], true)) {
            return true;
        }

        if ($perfil === 'chefe_esquadra') {
            return $agenteAtual && (int) $agenteAtual->unidade_id === (int) $agente->unidade_id;
        }

        return $agenteAtual && (int) $agenteAtual->id === (int) $agente->id;
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
            $dados['email'] = preg_replace('/[^a-z@._-]/', '', Str::lower(trim((string) $request->input('email'))));
        }

        $request->merge($dados);
    }

    private function normalizarNip(string $nip): string
    {
        $nip = Str::upper(preg_replace('/[^0-9A-Z-]/', '', $nip));
        $numero = preg_replace('/\D/', '', preg_replace('/^NIP-?/i', '', $nip));

        return $numero !== '' ? 'NIP-' . substr($numero, 0, 5) : substr($nip, 0, 9);
    }

    private function gerarProximoNip(): string
    {
        $ultimoNumero = Agente::query()
            ->where('nip', 'like', 'NIP-%')
            ->pluck('nip')
            ->reduce(function (int $maior, string $nip): int {
                if (preg_match('/^NIP-(\d{5})$/', $nip, $m)) {
                    return max($maior, (int) $m[1]);
                }

                return $maior;
            }, 0);

        return 'NIP-' . str_pad((string) ($ultimoNumero + 1), 5, '0', STR_PAD_LEFT);
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

    private function regraEmailPrimeiroNome(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $primeiroNome = $this->chaveEmailNome((string) request()->input('nome'));
            $parteLocal = explode('@', (string) $value)[0] ?? '';
            $parteLocal = preg_replace('/[^a-z]/', '', $parteLocal);

            if ($primeiroNome !== '' && !Str::startsWith($parteLocal, $primeiroNome)) {
                $fail("O email institucional deve comecar pelo primeiro nome informado ({$primeiroNome}).");
            }
        };
    }

    private function chaveEmailNome(string $nome): string
    {
        $nome = Str::of($nome)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z\s\'-]/', '')
            ->squish()
            ->value();

        return explode(' ', $nome)[0] ?? '';
    }
}
