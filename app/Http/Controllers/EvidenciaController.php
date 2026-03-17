<?php

namespace App\Http\Controllers;

use App\Models\Evidencia;
use App\Models\CadeiaCustodia;
use App\Models\Log;
use Illuminate\Http\Request;

class EvidenciaController extends Controller
{
    public function index(Request $request)
    {
        $q = Evidencia::with(['ocorrencia', 'tipoEvidencia', 'agenteRegisto']);
        if ($request->filled('ocorrencia_id')) $q->where('ocorrencia_id', $request->ocorrencia_id);
        if ($request->filled('tipo_evidencia_id')) $q->where('tipo_evidencia_id', $request->tipo_evidencia_id);
        return response()->json($q->orderByDesc('created_at')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ocorrencia_id' => 'required|exists:ocorrencias,id',
            'tipo_evidencia_id' => 'required|exists:tipos_evidencia,id',
            'descricao' => 'required|string|max:500',
            'ficheiro' => 'nullable|file|max:51200',
        ]);

        $ag = auth()->user()->agente;
        $dados = [
            'codigo' => Evidencia::gerarCodigo(),
            'ocorrencia_id' => $request->ocorrencia_id,
            'tipo_evidencia_id' => $request->tipo_evidencia_id,
            'descricao' => $request->descricao,
            'localizacao_fisica' => $request->localizacao_fisica,
            'agente_registo_id' => $ag->id,
            'estado' => 'em_custodia',
        ];

        if ($request->hasFile('ficheiro')) {
            $file = $request->file('ficheiro');
            $dados['ficheiro'] = $file->store('evidencias/' . date('Y/m'), 'local');
            $dados['tamanho_ficheiro'] = $file->getSize();
            $dados['hash_ficheiro'] = hash_file('sha256', $file->getRealPath());
        }

        $ev = Evidencia::create($dados);
        Log::registar('criar', 'evidencias', $ev->id, "Evidência {$ev->codigo} registada");
        return response()->json(['success' => true, 'message' => 'Evidência registada.', 'evidencia' => $ev->load('tipoEvidencia')], 201);
    }

    public function transferirCustodia(Request $request, Evidencia $evidencia)
    {
        $request->validate([
            'agente_destino_id' => 'required|exists:agentes,id',
            'local_destino' => 'required|string|max:200',
            'motivo' => 'required|string|max:300',
        ]);

        CadeiaCustodia::create([
            'evidencia_id' => $evidencia->id,
            'agente_origem_id' => auth()->user()->agente->id,
            'agente_destino_id' => $request->agente_destino_id,
            'local_origem' => $request->local_origem ?? $evidencia->localizacao_fisica ?? 'N/A',
            'local_destino' => $request->local_destino,
            'data_transferencia' => now(),
            'motivo' => $request->motivo,
            'observacoes' => $request->observacoes,
        ]);

        $evidencia->update(['localizacao_fisica' => $request->local_destino]);
        Log::registar('criar', 'cadeia_custodia', $evidencia->id, "Custódia transferida");
        return response()->json(['success' => true, 'message' => 'Custódia transferida.']);
    }

    public function historicoCustodia(Evidencia $evidencia)
    {
        return response()->json($evidencia->cadeiaCustodia()->with(['agenteOrigem', 'agenteDestino'])->orderBy('data_transferencia')->get());
    }
}