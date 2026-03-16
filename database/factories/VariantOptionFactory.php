<?php

namespace Database\Factories;

use App\Models\VariantOption;
use App\Models\VariantGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantOptionFactory extends Factory
{
    protected $model = VariantOption::class;

    public function definition(): array
    {
        $values = [
            'Couleur' => ['Rouge', 'Bleu', 'Vert', 'Jaune', 'Rose', 'Gris', 'Blanc', 'Noir'],
            'Taille' => ['0-3 mois', '3-6 mois', '6-12 mois', '12-18 mois', '18-24 mois', 'Unique'],
            'Matière' => ['Coton', 'Bambou', 'Polyester', 'Laine', 'Velours'],
            'Débit' => ['Lent', 'Moyen', 'Rapide'],
            'Motif' => ['Rayures', 'Poiscaille', 'Fleurs', 'Étoiles', 'Animaux'],
        ];

        // Guess group name from the factory's state or just pick random
        $groupName = $this->faker->randomElement(array_keys($values));
        $value = $this->faker->randomElement($values[$groupName]);

        return [
            'value' => $value,
            'variant_group_id' => VariantGroup::factory(),
        ];
    }

    /**
     * Set the group for which this option belongs.
     */
    public function forGroup(VariantGroup $group): static
    {
        return $this->state(function (array $attributes) use ($group) {
            return [
                'variant_group_id' => $group->id,
                'value' => $this->faker->randomElement($this->valuesForGroup($group->name)),
            ];
        });
    }

    private function valuesForGroup(string $groupName): array
    {
        return match ($groupName) {
            'Couleur' => ['Rouge', 'Bleu', 'Vert', 'Jaune', 'Rose', 'Gris', 'Blanc', 'Noir'],
            'Taille' => ['0-3 mois', '3-6 mois', '6-12 mois', '12-18 mois', '18-24 mois', 'Unique'],
            'Matière' => ['Coton', 'Bambou', 'Polyester', 'Laine', 'Velours'],
            'Débit' => ['Lent', 'Moyen', 'Rapide'],
            'Motif' => ['Rayures', 'Poiscaille', 'Fleurs', 'Étoiles', 'Animaux'],
            default => ['Standard'],
        };
    }
}