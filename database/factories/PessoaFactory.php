<?php

namespace Database\Factories;

use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaFactory extends Factory
{
    protected $model = Pessoa::class;

    public function definition(): array
    {
        $nomes = ['Adilson', 'Bernardo', 'Celestino', 'Eduardo', 'Firmino', 'Gaspar', 'Leonardo', 'Orlando', 'Salvador', 'Wilson', 'Adelaide', 'Dulce', 'Glória', 'Ivone', 'Lucinda', 'Margarida', 'Rosalina', 'Kizua'];
        $apelidos = ['Bumba', 'Tchikela', 'Zangui', 'Kiala', 'Mukuta', 'Ndala', 'Lukamba', 'Kasoma', 'Ngola', 'Samba', 'Vieira', 'Domingos', 'Santos', 'Costa', 'Pereira', 'Silva', 'Lopes', 'Correia'];
        $bairros = ['Viana Centro', 'Zango 0', 'Zango 1', 'Zango 2', 'Zango 3', 'Zango 4', 'Zango 5', 'Kikuxi', 'Sequele', 'Capalanga', 'Estalagem', 'Mulenvos'];

        return [
            'nome' => fake()->randomElement($nomes) . ' ' . fake()->randomElement($apelidos) . ' ' . fake()->randomElement($apelidos),
            'alcunha' => fake()->boolean(20) ? fake()->randomElement([null, 'Zé', 'Mano', 'Sombra', 'Ferro', 'Lobo', 'Flash']) : null,
            'data_nascimento' => fake()->dateTimeBetween('-55 years', '-16 years'),
            'bi' => fake()->boolean(70) ? fake()->numerify('##########LA###') : null,
            'sexo' => fake()->randomElement(['M', 'M', 'M', 'F']),
            'nacionalidade' => 'Angolana',
            'telefone' => fake()->boolean(60) ? '9' . fake()->numerify('########') : null,
            'morada' => fake()->randomElement($bairros) . ', Viana',
            'bairro' => fake()->randomElement($bairros),
            'caracteristicas_fisicas' => fake()->boolean(20) ? fake()->randomElement(['Cicatriz no rosto', 'Tatuagem no braço', 'Robusto, careca', 'Usa óculos']) : null,
            'observacoes' => null,
        ];
    }
}