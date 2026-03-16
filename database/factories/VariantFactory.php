<?php

namespace Database\Factories;

use App\Models\Variant;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantFactory extends Factory
{
    protected $model = Variant::class;

    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->ean8(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'stock' => $this->faker->numberBetween(0, 100),
            'product_id' => Product::factory(),
            'image_id' => null, // optional
        ];
    }
}