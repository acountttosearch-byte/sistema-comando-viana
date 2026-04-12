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

        // Distritos urbanos reais do município de Viana (divisão administrativa 2025)
        $distritos = ['Viana Sede', 'Zango', 'Estalagem', 'Kikuxi', 'Vila Flor', 'Baía'];
        // Bairros reais dentro dos distritos
        $bairrosCompletos = [
            'Zango 0, Zango', 'Zango 1, Zango', 'Zango 2, Zango', 'Zango 3, Zango',
            'Zango 4, Zango', 'Zango 5, Zango',
            'Viana Sede, Viana', 'Estalagem, Viana', 'Kikuxi, Viana',
            'Vila Flor, Viana', 'Baía, Viana',
            'Bairro da Paz, Viana Sede', 'Bairro Popular, Viana Sede',
        ];

        // Definir sexo primeiro para garantir coerência com o nome
        $sexo = fake()->randomElement(['M', 'M', 'M', 'F']);
        $primeiroNome = $sexo === 'M' ? fake()->randomElement($nomesM) : fake()->randomElement($nomesF);
        $nome = $primeiroNome . ' ' . fake()->randomElement($apelidos) . ' ' . fake()->randomElement($apelidos);

        $bairro = fake()->randomElement($distritos);
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