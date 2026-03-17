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
            'Detido por suspeita de tráfico.',
            'Detido por desacato à autoridade.',
            'Detido por violência doméstica.',
            'Detido em cumprimento de mandado de captura.',
            'Detido por perturbação da ordem pública.',
        ];

        return [
            'numero_detencao' => 'DT-VNA-' . $data->format('Y') . '-' . str_pad(self::$counter, 5, '0', STR_PAD_LEFT),
            'data_detencao' => $data,
            'local_detencao' => fake()->randomElement(['Mercado do Zango 3', 'Rua Principal, Viana', 'Bar nocturno, Viana Centro', 'Zona residencial, Kikuxi']),
            'motivo' => fake()->randomElement($motivos),
            'estado_id' => fake()->randomElement([1, 1, 2, 2, 3, 4, 5]),
            'data_libertacao' => fake()->boolean(30) ? fake()->dateTimeBetween($data, 'now') : null,
            'observacoes' => null,
            'created_at' => $data,
            'updated_at' => $data,
        ];
    }
}