<?php

namespace Database\Factories;

use App\Models\Investigacao;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestigacaoFactory extends Factory
{
    protected $model = Investigacao::class;
    private static int $counter = 0;

    public function definition(): array
    {
        self::$counter++;
        $dataInicio = fake()->dateTimeBetween('-6 months', '-1 week');
        $progresso = fake()->numberBetween(5, 100);
        $estadoId = $progresso >= 100 ? 4 : ($progresso >= 20 ? 2 : 1);

        return [
            'numero_investigacao' => 'INV-VNA-' . $dataInicio->format('Y') . '-' . str_pad(self::$counter, 5, '0', STR_PAD_LEFT),
            'estado_id' => $estadoId,
            'resumo' => fake()->randomElement([
                'Investigação em curso. A recolher depoimentos.',
                'Suspeito identificado através de videovigilância.',
                'Análise de evidências forenses em andamento.',
                'Investigação concluída. Processo encaminhado ao tribunal.',
            ]),
            'data_inicio' => $dataInicio,
            'data_fim' => $estadoId === 4 ? fake()->dateTimeBetween($dataInicio, 'now') : null,
            'prazo' => fake()->dateTimeBetween('now', '+3 months'),
            'progresso' => min($progresso, 100),
        ];
    }
}