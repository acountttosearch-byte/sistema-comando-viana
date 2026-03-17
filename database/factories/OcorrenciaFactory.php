<?php

namespace Database\Factories;

use App\Models\Ocorrencia;
use Illuminate\Database\Eloquent\Factories\Factory;

class OcorrenciaFactory extends Factory
{
    protected $model = Ocorrencia::class;
    private static int $counter = 0;

    public function definition(): array
    {
        self::$counter++;
        $data = fake()->dateTimeBetween('-12 months', 'now');
        $locais = ['Rua Principal, Viana Centro', 'Mercado do Zango 3', 'Paragem de táxi, Zango 1', 'Zona residencial, Kikuxi', 'Estrada Nacional, Sequele', 'Bar nocturno, Viana Centro', 'Beco sem saída, Zango 4', 'Campo de futebol, Zango 5', 'Escola Primária do Zango 2', 'Hospital Municipal de Viana'];
        $bairros = ['Viana Centro', 'Zango 0', 'Zango 1', 'Zango 2', 'Zango 3', 'Zango 4', 'Zango 5', 'Kikuxi', 'Sequele', 'Capalanga', 'Estalagem', 'Mulenvos'];
        $descricoes = [
            'A vítima foi abordada por dois indivíduos armados que subtraíram o telemóvel e carteira.',
            'Assalto à residência durante a madrugada. Meliantes forçaram a porta.',
            'Desentendimento entre vizinhos resultou em agressão física.',
            'Vítima apresentou-se com marcas de agressão. Relata violência pelo cônjuge.',
            'Corpo encontrado na via pública com sinais de esfaqueamento.',
            'Vidros partidos e paredes pintadas com grafiti numa escola.',
            'Perturbação da ordem pública junto ao mercado.',
            'Indivíduo em posse de arma branca sem justificação.',
            'Atropelamento na estrada principal. Vítima transportada ao hospital.',
            'Suspeita de tráfico de substâncias ilícitas numa residência.',
            'Furto de peças de roupa do estendal durante a noite.',
            'Telemóvel furtado no interior de um autocarro.',
            'Agressão com objecto contundente. Vítima com ferimentos na cabeça.',
        ];

        return [
            'numero_ocorrencia' => 'OC-VNA-' . $data->format('Y') . '-' . str_pad(self::$counter, 5, '0', STR_PAD_LEFT),
            'tipo_crime_id' => fake()->numberBetween(1, 19),
            'descricao' => fake()->randomElement($descricoes),
            'data_ocorrencia' => $data,
            'hora_ocorrencia' => fake()->time('H:i'),
            'local' => fake()->randomElement($locais),
            'bairro' => fake()->randomElement($bairros),
            'prioridade' => fake()->randomElement(['baixa', 'media', 'media', 'media', 'alta', 'alta', 'critica']),
            'estado_id' => fake()->randomElement([1, 2, 3, 4, 4, 5, 5, 5, 6, 7]),
            'unidade_id' => fake()->numberBetween(2, 6),
            'confidencial' => fake()->boolean(10),
            'created_at' => $data,
            'updated_at' => $data,
        ];
    }
}