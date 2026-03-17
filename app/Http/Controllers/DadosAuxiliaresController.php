<?php

namespace App\Http\Controllers;

use App\Models\Patente;
use App\Models\Perfil;
use App\Models\TipoCrime;
use App\Models\CategoriaCrime;
use App\Models\EstadoOcorrencia;
use App\Models\EstadoDetencao;
use App\Models\EstadoInvestigacao;
use App\Models\TipoEvidencia;
use App\Models\TipoAlerta;
use App\Models\TipoMandado;
use App\Models\TipoArmamento;
use App\Models\TipoRelatorio;
use App\Models\Bairro;
use App\Models\Unidade;
use App\Models\Turno;

class DadosAuxiliaresController extends Controller
{
    public function todos()
    {
        return response()->json([
            'patentes' => Patente::orderBy('nivel_hierarquico', 'desc')->get(),
            'perfis' => Perfil::all(),
            'categorias_crime' => CategoriaCrime::with('tiposCrime')->get(),
            'tipos_crime' => TipoCrime::with('categoria')->orderBy('nome')->get(),
            'estados_ocorrencia' => EstadoOcorrencia::orderBy('ordem')->get(),
            'estados_detencao' => EstadoDetencao::all(),
            'estados_investigacao' => EstadoInvestigacao::orderBy('ordem')->get(),
            'tipos_evidencia' => TipoEvidencia::all(),
            'tipos_alerta' => TipoAlerta::all(),
            'tipos_mandado' => TipoMandado::all(),
            'tipos_armamento' => TipoArmamento::all(),
            'tipos_relatorio' => TipoRelatorio::all(),
            'bairros' => Bairro::orderBy('nome')->get(),
            'unidades' => Unidade::with('tipoUnidade')->activas()->orderBy('nome')->get(),
            'turnos' => Turno::all(),
        ]);
    }
}