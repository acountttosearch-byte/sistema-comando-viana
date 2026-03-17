<?php

namespace Database\Factories;

use App\Models\Viatura;
use Illuminate\Database\Eloquent\Factories\Factory;

class ViaturaFactory extends Factory
{
    protected $model = Viatura::class;

    public function definition(): array
    {
        return [
            'matricula' => 'LD-' . fake()->numerify('##') . '-' . fake()->numerify('##') . '-' . chr(rand(65,90)) . chr(rand(65,90)),
            'marca' => fake()->randomElement(['Toyota', 'Toyota', 'Nissan', 'Mitsubishi', 'Land Rover', 'Hyundai']),
            'modelo' => fake()->randomElement(['Hilux', 'Land Cruiser', 'Patrol', 'Pajero', 'Defender', 'Tucson']),
            'ano' => fake()->numberBetween(2015, 2024),
            'cor' => fake()->randomElement(['Branco', 'Branco', 'Azul', 'Verde', 'Cinza']),
            'unidade_id' => fake()->numberBetween(2, 6),
            'estado' => fake()->randomElement(['operacional', 'operacional', 'operacional', 'manutencao']),
            'quilometragem' => fake()->numberBetween(15000, 180000),
        ];
    }
}