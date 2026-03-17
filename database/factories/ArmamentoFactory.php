<?php

namespace Database\Factories;

use App\Models\Armamento;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArmamentoFactory extends Factory
{
    protected $model = Armamento::class;

    public function definition(): array
    {
        $tipo = fake()->numberBetween(1, 4);
        $dados = match($tipo) {
            1 => ['marca' => fake()->randomElement(['Glock', 'Beretta', 'Taurus']), 'modelo' => fake()->randomElement(['17', '92FS', 'PT92']), 'calibre' => '9mm'],
            2 => ['marca' => fake()->randomElement(['Mossberg', 'Remington']), 'modelo' => fake()->randomElement(['500', '870']), 'calibre' => '12 gauge'],
            3 => ['marca' => 'FN', 'modelo' => 'Minimi', 'calibre' => '5.56mm'],
            4 => ['marca' => 'Taurus', 'modelo' => '856', 'calibre' => '.38 Special'],
        };

        return [
            'tipo_armamento_id' => $tipo,
            'marca' => $dados['marca'],
            'modelo' => $dados['modelo'],
            'numero_serie' => strtoupper(fake()->bothify('??-####-####-##')),
            'calibre' => $dados['calibre'],
            'unidade_id' => fake()->numberBetween(2, 6),
            'estado' => fake()->randomElement(['operacional', 'operacional', 'operacional', 'manutencao']),
        ];
    }
}