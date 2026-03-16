<?php

namespace Database\Factories;

use App\Models\VariantGroup;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantGroupFactory extends Factory
{
    protected $model = VariantGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Couleur', 'Taille', 'Matière', 'Débit', 'Motif']),
            'product_id' => Product::factory(),
        ];
    }
}