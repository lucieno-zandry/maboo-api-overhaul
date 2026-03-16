<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use App\Models\VariantGroup;
use App\Models\VariantOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        // Realistic French product names for moms and babies
        $titles = [
            'Poussette CityZen', 'Robe de grossesse fleurie', 'Biberon anti-colique',
            'Lot de couches lavables', 'Tire-lait électrique', 'Transat bébé confort',
            'Gigoteuse 2 saisons', 'Porte-bébé physiologique', 'Stérilisateur vapeur',
            'Chauffe-biberon voyage', 'Bavoirs coton bio (lot de 5)', 'Lit évolutif 3 en 1',
            'Veilleuse musicale', 'Jouet d\'éveil en bois', 'Lait maternel infantile',
            'Crème change protectrice', 'Gelée de douche bébé', 'Coussin d\'allaitement'
        ];

        return [
            'slug' => $this->faker->unique()->slug(3),
            'title' => $this->faker->randomElement($titles),
            'description' => $this->faker->paragraph(3, true),
            'category_id' => Category::factory()->withParent(), // depth ≥2
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Configure the model factory to create variant groups, options, and variants.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            // Define possible groups based on product type (simplified: random groups)
            $groupNames = $this->getVariantGroupsForProduct($product->title);

            $groups = collect();
            foreach ($groupNames as $groupName) {
                $group = VariantGroup::factory()
                    ->for($product)
                    ->create(['name' => $groupName]);
                $groups->push($group);
            }

            // For each group, create options
            $optionsByGroup = [];
            foreach ($groups as $group) {
                $options = VariantOption::factory()
                    ->for($group)
                    ->count($this->faker->numberBetween(2, 4))
                    ->create();
                $optionsByGroup[$group->id] = $options;
            }

            // Generate all combinations of options (cartesian product)
            $combinations = $this->cartesianProduct($optionsByGroup);

            // Create a variant for each combination
            foreach ($combinations as $combination) {
                /** @var VariantOption[] $combination */
                $variant = Variant::factory()
                    ->for($product)
                    ->create([
                        'sku' => $this->generateSku($product, $combination),
                        'price' => $this->faker->randomFloat(2, 10, 500),
                        'stock' => $this->faker->numberBetween(0, 100),
                    ]);

                // Attach the options to the variant
                $variant->variant_options()->attach(collect($combination)->pluck('id'));
            }
        });
    }

    /**
     * Return a list of variant group names appropriate for the product title.
     */
    private function getVariantGroupsForProduct(string $title): array
    {
        // Simple logic – can be expanded
        if (preg_match('/Poussette|Transat|Lit|Siège/i', $title)) {
            return ['Couleur', 'Matière'];
        }
        if (preg_match('/Robe|Vêtements|Gigoteuse|Bavoirs/i', $title)) {
            return ['Taille', 'Couleur'];
        }
        if (preg_match('/Biberon|Tire-lait|Stérilisateur/i', $title)) {
            return ['Couleur', 'Débit']; // e.g., débit lent/moyen/rapide
        }
        return ['Couleur', 'Taille']; // default
    }

    /**
     * Generate a unique SKU for a variant based on product and option IDs.
     */
    private function generateSku(Product $product, array $combination): string
    {
        $optionIds = collect($combination)->pluck('id')->sort()->implode('-');
        return $product->id . '-' . $optionIds;
    }

    /**
     * Compute the cartesian product of options grouped by group.
     */
    private function cartesianProduct(array $optionsByGroup): array
    {
        $result = [[]];
        foreach ($optionsByGroup as $groupOptions) {
            $append = [];
            foreach ($result as $product) {
                foreach ($groupOptions as $item) {
                    $product[] = $item;
                    $append[] = $product;
                }
            }
            $result = $append;
        }
        return $result;
    }
}