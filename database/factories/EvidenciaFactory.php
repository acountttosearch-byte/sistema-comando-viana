<?php

namespace Database\Factories;

use App\Models\Evidencia;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvidenciaFactory extends Factory
{
    protected $model = Evidencia::class;
    private static int $counter = 0;

    public function definition(): array
    {
        self::$counter++;
        $tipo = fake()->numberBetween(1, 5);
        $descricoes = [
            1 => ['Fotografia da cena do crime', 'Foto do suspeito capturada por CCTV', 'Foto dos objectos apreendidos'],
            2 => ['Vídeo de vigilância do local', 'Gravação de câmara de segurança'],
            3 => ['Relatório médico da vítima', 'Auto de apreensão', 'Declaração da testemunha'],
            4 => ['Gravação do depoimento da vítima', 'Áudio da chamada de emergência'],
            5 => ['Faca apreendida no local', 'Telemóvel apreendido', 'Arma de fogo apreendida'],
        ];

        return [
            'codigo' => 'EV-' . date('Y') . '-' . str_pad(self::$counter, 5, '0', STR_PAD_LEFT),
            'tipo_evidencia_id' => $tipo,
            'descricao' => fake()->randomElement($descricoes[$tipo]),
            'localizacao_fisica' => fake()->randomElement(['Cofre A, Prateleira 1', 'Cofre B, Prateleira 2', 'Sala de evidências', null]),
            'tamanho_ficheiro' => $tipo <= 4 ? fake()->numberBetween(100000, 50000000) : null,
            'hash_ficheiro' => $tipo <= 4 ? fake()->sha256() : null,
            'estado' => fake()->randomElement(['em_custodia', 'em_custodia', 'em_custodia', 'em_analise', 'tribunal']),
        ];
    }
}