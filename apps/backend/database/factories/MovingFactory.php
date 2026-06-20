<?php

namespace Database\Factories;

use App\Models\Moving;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Moving>
 */
class MovingFactory extends Factory
{
    protected $model = Moving::class;

    public function definition(): array
    {
        return [
            'hawb' => fake()->unique()->bothify('HAWB-########'),
            'hawb_descr' => fake()->sentence(3),
            'loc_before' => 'RACK-01',
            'loc_after' => 'RACK-02',
            'users' => fake()->userName(),
        ];
    }
}
