<?php

namespace Database\Factories;

use App\Models\Viatura;
use Illuminate\Database\Eloquent\Factories\Factory;

class ViaturaFactory extends Factory
{
    protected $model = Viatura::class;

    public function definition(): array
    {
        // Marca e modelo devem ser coerentes
        $veiculos = [
            ['marca' => 'Toyota', 'modelo' => 'Hilux'],
            ['marca' => 'Toyota', 'modelo' => 'Land Cruiser'],
            ['marca' => 'Toyota', 'modelo' => 'Corolla'],
            ['marca' => 'Nissan', 'modelo' => 'Patrol'],
            ['marca' => 'Nissan', 'modelo' => 'Navara'],
            ['marca' => 'Mitsubishi', 'modelo' => 'Pajero'],
            ['marca' => 'Mitsubishi', 'modelo' => 'L200'],
            ['marca' => 'Land Rover', 'modelo' => 'Defender'],
            ['marca' => 'Hyundai', 'modelo' => 'Tucson'],
            ['marca' => 'Hyundai', 'modelo' => 'Santa Fe'],
        ];
        $v = fake()->randomElement($veiculos);

        return [
            'matricula' => 'LD-' . fake()->numerify('##') . '-' . fake()->numerify('##') . '-' . chr(rand(65,90)) . chr(rand(65,90)),
            'marca' => $v['marca'],
            'modelo' => $v['modelo'],
            'ano' => fake()->numberBetween(2015, 2024),
            'cor' => fake()->randomElement(['Branco', 'Branco', 'Azul', 'Verde', 'Cinza']),
            'unidade_id' => fake()->numberBetween(2, 6),
            'estado' => fake()->randomElement(['operacional', 'operacional', 'operacional', 'manutencao']),
            'quilometragem' => fake()->numberBetween(15000, 180000),
        ];
    }
}