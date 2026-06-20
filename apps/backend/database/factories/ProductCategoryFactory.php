<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' Category',
            'created_at' => now(),
            'updated_at' => null,
            'created_by' => 1,
            'updated_by' => null,
        ];
    }
}
