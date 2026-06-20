<?php

namespace Database\Factories;

use App\Models\Outbound;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outbound>
 */
class OutboundFactory extends Factory
{
    protected $model = Outbound::class;

    public function definition(): array
    {
        return [
            'qty' => (string) fake()->numberBetween(1, 100),
            'po' => fake()->bothify('PO-#####'),
            'destination' => fake()->city(),
            'checker' => fake()->name(),
            'docfile' => null,
            'date_created' => now(),
            'status' => 'inprogress',
            'created_by' => null,
            'updated_by' => null,
            'date_updated' => null,
            'delivery_id' => null,
            'transporter' => fake()->company(),
        ];
    }
}
