<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Agente;
use App\Models\Detencao;
use App\Models\Evidencia;
use App\Models\Investigacao;
use App\Models\Ocorrencia;
use App\Models\ProcessoCriminal;

trait AuthorizesOperationalAccess
{
    protected function perfilNome(): string
    {
        return auth()->user()?->perfil?->nome ?? '';
    }

    protected function agenteAtual(): ?Agente
    {
        return auth()->user()?->agente;
    }

    protected function agenteAtualId(): ?int
    {
        return $this->agenteAtual()?->id;
    }

    protected function unidadeAtualId(): ?int
    {
        return $this->agenteAtual()?->unidade_id;
    }

    protected function temVisaoGlobal(): bool
    {
        return in_array($this->perfilNome(), ['admin', 'comandante'], true);
    }

    protected function eChefeEsquadra(): bool
    {
        return $this->perfilNome() === 'chefe_esquadra';
    }

    protected function podeAcederUnidade(?int $unidadeId): bool
    {
        if (!$unidadeId) {
            return false;
        }

        return $this->temVisaoGlobal() || $unidadeId === $this->unidadeAtualId();
    }

    protected function exigirUnidadePermitida(?int $unidadeId, string $message = 'Sem permissao para esta unidade.'): void
    {
        abort_unless($this->podeAcederUnidade($unidadeId), 403, $message);
    }

    protected function podeAcederOcorrencia(Ocorrencia $ocorrencia): bool
    {
        if ($this->temVisaoGlobal()) {
            return true;
        }

        if ($this->eChefeEsquadra()) {
            return $ocorrencia->unidade_id === $this->unidadeAtualId();
        }

        $agenteId = $this->agenteAtualId();

        return $agenteId
            && ($ocorrencia->agente_registo_id === $agenteId || $ocorrencia->agente_responsavel_id === $agenteId);
    }

    protected function exigirOcorrenciaPermitida(Ocorrencia $ocorrencia): void
    {
        abort_unless($this->podeAcederOcorrencia($ocorrencia), 403, 'Sem permissao para esta ocorrencia.');
    }

    protected function podeAcederDetencao(Detencao $detencao): bool
    {
        if ($this->temVisaoGlobal()) {
            return true;
        }

        if ($this->eChefeEsquadra()) {
            return $detencao->unidade_id === $this->unidadeAtualId();
        }

        return $detencao->agente_responsavel_id === $this->agenteAtualId();
    }

    protected function exigirDetencaoPermitida(Detencao $detencao): void
    {
        abort_unless($this->podeAcederDetencao($detencao), 403, 'Sem permissao para esta detencao.');
    }

    protected function podeAcederEvidencia(Evidencia $evidencia): bool
    {
        $evidencia->loadMissing('ocorrencia');

        return $this->podeAcederOcorrencia($evidencia->ocorrencia)
            || $evidencia->agente_registo_id === $this->agenteAtualId();
    }

    protected function exigirEvidenciaPermitida(Evidencia $evidencia): void
    {
        abort_unless($this->podeAcederEvidencia($evidencia), 403, 'Sem permissao para esta evidencia.');
    }

    protected function podeAcederInvestigacao(Investigacao $investigacao): bool
    {
        if ($this->temVisaoGlobal()) {
            return true;
        }

        $investigacao->loadMissing('ocorrencia');

        if ($this->eChefeEsquadra()) {
            return $investigacao->ocorrencia?->unidade_id === $this->unidadeAtualId();
        }

        return $investigacao->investigador_id === $this->agenteAtualId();
    }

    protected function exigirInvestigacaoPermitida(Investigacao $investigacao): void
    {
        abort_unless($this->podeAcederInvestigacao($investigacao), 403, 'Sem permissao para esta investigacao.');
    }

    protected function podeAcederProcesso(ProcessoCriminal $processo): bool
    {
        if ($this->temVisaoGlobal()) {
            return true;
        }

        if ($this->eChefeEsquadra()) {
            return $processo->unidade_id === $this->unidadeAtualId();
        }

        return $processo->agente_responsavel_id === $this->agenteAtualId();
    }

    protected function exigirProcessoPermitido(ProcessoCriminal $processo): void
    {
        abort_unless($this->podeAcederProcesso($processo), 403, 'Sem permissao para este processo.');
    }
}
