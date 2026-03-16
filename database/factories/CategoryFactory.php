<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        // List of French category names for baby & mom products
        $titles = [
            'Poussettes', 'Sièges auto', 'Vêtements bébé', 'Vêtements maternité',
            'Alimentation', 'Hygiène & soins', 'Sommeil', 'Puériculture',
            'Jouets d\'éveil', 'Lingerie de grossesse', 'Tire-laits', 'Décoration chambre'
        ];

        return [
            'title' => $this->faker->unique()->randomElement($titles),
            'parent_id' => null, // Will be set in seeder or via state
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the category has a parent.
     */
    public function withParent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => Category::factory(), // creates a new parent category
            ];
        });
    }
}