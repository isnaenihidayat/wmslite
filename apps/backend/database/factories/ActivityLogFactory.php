<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'hawb' => fake()->unique()->bothify('HAWB-########'),
            'hawb_descr' => fake()->sentence(3),
            'loc_name' => 'RACK-'.fake()->numerify('##'),
            'date_created' => now(),
            'status' => fake()->randomElement(['in', 'out', 'moved']),
        ];
    }
}
