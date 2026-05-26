<?php

namespace Database\Factories;

use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

class PessoaFactory extends Factory
{
    protected $model = Pessoa::class;

    public function definition(): array
    {
        // Nomes masculinos angolanos comuns
        $nomesM = ['Adilson', 'Bernardo', 'Celestino', 'Eduardo', 'Firmino', 'Gaspar', 'Leonardo', 'Orlando', 'Salvador', 'Wilson', 'Manuel', 'João', 'Pedro', 'Francisco', 'Domingos', 'Sebastião', 'Gilberto', 'Tomás', 'André', 'Fernando', 'Paulo', 'Miguel', 'Ricardo'];
        // Nomes femininos angolanos comuns
        $nomesF = ['Adelaide', 'Dulce', 'Glória', 'Ivone', 'Lucinda', 'Margarida', 'Rosalina', 'Kizua', 'Ana', 'Teresa', 'Luísa', 'Helena', 'Joana', 'Marta', 'Rosa', 'Maria', 'Catarina', 'Esperança', 'Felismina'];

        $apelidos = ['Bumba', 'Tchikela', 'Zangui', 'Kiala', 'Mukuta', 'Ndala', 'Lukamba', 'Kasoma', 'Ngola', 'Samba', 'Vieira', 'Domingos', 'Santos', 'Costa', 'Pereira', 'Silva', 'Lopes', 'Correia', 'Mateus', 'Gonçalves', 'Fernandes'];

        $bairros = ['Capalanga', 'Comissão', 'CAOP', 'Belo Horizonte', 'Paz', 'Pantanal', 'Grafanil', 'Ana Paula', 'Regedoria'];
        $bairrosCompletos = [
            'Capalanga, Distrito de Viana',
            'Comissão, Distrito de Viana',
            'CAOP, Distrito de Viana',
            'Belo Horizonte, Distrito do Kikuxi',
            'Paz, Distrito do Kikuxi',
            'Pantanal, Distrito do Kikuxi',
            'Grafanil, Distrito da Estalagem',
            'Ana Paula, Distrito da Estalagem',
            'Regedoria, Distrito da Estalagem',
        ];

        // Definir sexo primeiro para garantir coerência com o nome
        $sexo = fake()->randomElement(['M', 'M', 'M', 'F']);
        $primeiroNome = $sexo === 'M' ? fake()->randomElement($nomesM) : fake()->randomElement($nomesF);
        $nome = $primeiroNome . ' ' . fake()->randomElement($apelidos) . ' ' . fake()->randomElement($apelidos);

        $bairro = fake()->randomElement($bairros);
        $morada = fake()->randomElement($bairrosCompletos) . ', Município de Viana';

        $nacionalidade = fake()->randomElement([
            'Angolana', 'Angolana', 'Angolana', 'Angolana', 'Angolana', 'Angolana', 'Angolana', 'Angolana',
            'Congolesa', 'Portuguesa', 'Brasileira',
        ]);

        return [
            'nome' => $nome,
            'alcunha' => fake()->boolean(15) ? fake()->randomElement([null, 'Zé', 'Mano', 'Sombra', 'Ferro', 'Lobo', 'Flash', 'Kota', 'Piru', 'Xico']) : null,
            'data_nascimento' => fake()->dateTimeBetween('-55 years', '-16 years'),
            'bi' => fake()->boolean(70) ? fake()->numerify('##########LA###') : null,
            'sexo' => $sexo,
            'nacionalidade' => $nacionalidade,
            'telefone' => fake()->boolean(60) ? '9' . fake()->numerify('########') : null,
            'morada' => $morada,
            'bairro' => $bairro,
            'caracteristicas_fisicas' => fake()->boolean(20) ? fake()->randomElement([
                'Cicatriz no rosto', 'Tatuagem no braço direito', 'Estatura alta, robusto',
                'Usa óculos', 'Cicatriz na mão esquerda', 'Magro, estatura média',
            ]) : null,
            'observacoes' => null,
        ];
    }
}
