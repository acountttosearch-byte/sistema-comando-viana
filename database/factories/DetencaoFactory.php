<?php

namespace Database\Factories;

use App\Models\Detencao;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetencaoFactory extends Factory
{
    protected $model = Detencao::class;
    private static int $counter = 0;

    public function definition(): array
    {
        self::$counter++;
        $data = fake()->dateTimeBetween('-8 months', 'now');
        $motivos = [
            'Detido em flagrante delito por roubo.',
            'Detido por posse de arma branca sem licença.',
            'Detido por agressão física a terceiros.',
            'Detido por suspeita de tráfico de substâncias ilícitas.',
            'Detido por desacato à autoridade policial.',
            'Detido por violência doméstica.',
            'Detido em cumprimento de mandado de captura.',
            'Detido por perturbação da ordem pública.',
        ];

        // Locais reais de Viana
        $locais = [
            'Mercado do Capalanga',
            'Rua Principal da Comissão',
            'Paragem de táxi da CAOP',
            'Zona residencial de Belo Horizonte',
            'Mercado do bairro Paz',
            'Rua principal do Pantanal',
            'Rotunda do Grafanil',
            'Mercado da Regedoria',
        ];

        return [
            'numero_detencao' => 'DT-VNA-' . $data->format('Y') . '-' . str_pad(self::$counter, 5, '0', STR_PAD_LEFT),
            'data_detencao' => $data,
            'local_detencao' => fake()->randomElement($locais),
            'motivo' => fake()->randomElement($motivos),
            'estado_id' => fake()->randomElement([1, 1, 2, 2, 3, 4, 5]),
            'data_libertacao' => fake()->boolean(30) ? fake()->dateTimeBetween($data, 'now') : null,
            'observacoes' => null,
            'created_at' => $data,
            'updated_at' => $data,
        ];
    }
}
