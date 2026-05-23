<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOperationalAccess;
use App\Models\Agente;
use App\Models\CadeiaCustodia;
use App\Models\Evidencia;
use App\Models\Log;
use App\Models\Ocorrencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenciaController extends Controller
{
    use AuthorizesOperationalAccess;

    public function index(Request $request)
    {
        $user = auth()->user();
        $perfil = $user->perfil->nome;
        $agenteId = $user->agente?->id;
        $q = Evidencia::with(['ocorrencia.unidade', 'tipoEvidencia', 'agenteRegisto']);

        if (in_array($perfil, ['admin', 'comandante'], true)) {
            // visao global
        } elseif ($perfil === 'chefe_esquadra') {
            $q->whereHas('ocorrencia', fn($q2) => $q2->where('unidade_id', $this->unidadeAtualId()));
        } elseif ($agenteId) {
            $q->where('agente_registo_id', $agenteId);
        }

        if ($request->filled('ocorrencia_id')) $q->where('ocorrencia_id', $request->ocorrencia_id);
        if ($request->filled('tipo_evidencia_id')) $q->where('tipo_evidencia_id', $request->tipo_evidencia_id);
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('busca')) {
            $b = $request->busca;
            $q->where(fn($q2) => $q2->where('codigo', 'like', "%$b%")->orWhere('descricao', 'like', "%$b%"));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        return response()->json($q->orderByDesc('created_at')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'tipo_evidencia_id' => 'required|exists:tipos_evidencia,id',
            'descricao' => 'required|string|min:3|max:500',
            'localizacao_fisica' => 'nullable|string|max:200',
            'ficheiro' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,pdf,doc,docx,mp3,wav,mp4,mov,avi',
        ]);

        $ocorrencia = Ocorrencia::findOrFail($dados['ocorrencia_id']);
        $this->exigirOcorrenciaPermitida($ocorrencia);

        $ag = $this->agenteAtual();
        abort_unless($ag, 422, 'O utilizador autenticado deve estar associado a um agente.');

        $dadosCriacao = [
            'codigo' => Evidencia::gerarCodigo(),
            'ocorrencia_id' => $dados['ocorrencia_id'],
            'tipo_evidencia_id' => $dados['tipo_evidencia_id'],
            'descricao' => $dados['descricao'],
            'localizacao_fisica' => $dados['localizacao_fisica'] ?? null,
            'agente_registo_id' => $ag->id,
            'estado' => 'em_custodia',
        ];

        if ($request->hasFile('ficheiro')) {
            $file = $request->file('ficheiro');
            $dadosCriacao['ficheiro'] = $file->store('evidencias/' . date('Y/m'), 'local');
            $dadosCriacao['tamanho_ficheiro'] = $file->getSize();
            $dadosCriacao['hash_ficheiro'] = hash_file('sha256', $file->getRealPath());
        }

        $ev = Evidencia::create($dadosCriacao);

        CadeiaCustodia::create([
            'evidencia_id' => $ev->id,
            'agente_origem_id' => $ag->id,
            'agente_destino_id' => $ag->id,
            'local_origem' => 'Registo inicial',
            'local_destino' => $ev->localizacao_fisica ?: 'Arquivo de evidencias',
            'data_transferencia' => now(),
            'motivo' => 'Entrada inicial da evidencia no sistema',
        ]);

        Log::registar('criar', 'evidencias', $ev->id, "Evidencia {$ev->codigo} registada");

        return response()->json(['success' => true, 'message' => 'Evidencia registada.', 'evidencia' => $ev->load('tipoEvidencia')], 201);
    }

    public function show(Evidencia $evidencia)
    {
        $this->exigirEvidenciaPermitida($evidencia);

        return response()->json($evidencia->load([
            'ocorrencia.tipoCrime', 'ocorrencia.unidade', 'tipoEvidencia',
            'agenteRegisto', 'cadeiaCustodia.agenteOrigem', 'cadeiaCustodia.agenteDestino',
        ]));
    }

    public function ficheiro(Evidencia $evidencia)
    {
        $this->exigirEvidenciaPermitida($evidencia);

        if (!$evidencia->ficheiro || !Storage::disk('local')->exists($evidencia->ficheiro)) {
            return response()->json(['error' => 'Ficheiro nao encontrado.'], 404);
        }

        return response()->file(Storage::disk('local')->path($evidencia->ficheiro));
    }

    public function transferirCustodia(Request $request, Evidencia $evidencia)
    {
        $this->exigirEvidenciaPermitida($evidencia);

        $dados = $request->validate([
            'agente_destino_id' => 'required|exists:agentes,id',
            'local_origem' => 'nullable|string|max:200',
            'local_destino' => 'required|string|min:3|max:200',
            'motivo' => 'required|string|min:5|max:300',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        $destino = Agente::activos()->find($dados['agente_destino_id']);
        abort_unless($destino, 422, 'O agente de destino deve estar activo.');

        CadeiaCustodia::create([
            'evidencia_id' => $evidencia->id,
            'agente_origem_id' => $this->agenteAtualId(),
            'agente_destino_id' => $dados['agente_destino_id'],
            'local_origem' => $dados['local_origem'] ?? $evidencia->localizacao_fisica ?? 'N/A',
            'local_destino' => $dados['local_destino'],
            'data_transferencia' => now(),
            'motivo' => $dados['motivo'],
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        $evidencia->update(['localizacao_fisica' => $dados['local_destino']]);
        Log::registar('criar', 'cadeia_custodia', $evidencia->id, 'Custodia transferida');

        return response()->json(['success' => true, 'message' => 'Custodia transferida.']);
    }

    public function historicoCustodia(Evidencia $evidencia)
    {
        $this->exigirEvidenciaPermitida($evidencia);

        return response()->json($evidencia->cadeiaCustodia()->with(['agenteOrigem', 'agenteDestino'])->orderBy('data_transferencia')->get());
    }
}
