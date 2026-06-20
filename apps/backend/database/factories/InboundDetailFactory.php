<?php

namespace Database\Factories;

use App\Models\InboundDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InboundDetail>
 */
class InboundDetailFactory extends Factory
{
    protected $model = InboundDetail::class;

    public function definition(): array
    {
        return [
            'hawb' => fake()->bothify('HAWB-########'),
            'descr' => fake()->sentence(3),
            'loc' => 'RACK-01',
            'weight' => fake()->randomFloat(2, 1, 100),
            'long' => fake()->randomFloat(2, 1, 100),
            'wide' => fake()->randomFloat(2, 1, 100),
            'high' => fake()->randomFloat(2, 1, 100),
            'flag' => false,
            'scan_time' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function picked(): static
    {
        return $this->state(fn (array $attributes) => [
            'flag' => true,
            'scan_time' => now(),
        ]);
    }
}
