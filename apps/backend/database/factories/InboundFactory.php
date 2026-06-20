<?php

namespace Database\Factories;

use App\Models\Inbound;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inbound>
 */
class InboundFactory extends Factory
{
    protected $model = Inbound::class;

    public function definition(): array
    {
        return [
            'hawb' => fake()->unique()->bothify('HAWB-########'),
            'descr' => fake()->sentence(3),
            'product_category_id' => null,
            'modality' => fake()->randomElement(['air', 'sea']),
            'delivery_id' => null,
            'qty' => (string) fake()->numberBetween(1, 100),
            'po' => fake()->bothify('PO-#####'),
            'locator' => 'RACK-01',
            'checker' => fake()->name(),
            'date_created' => now(),
            'status' => 'inprogress',
            'ship_method' => fake()->randomElement(['air', 'sea', 'land']),
            'etd' => now()->subDays(5),
            'eta' => now()->subDays(1),
            'ata' => null,
            'pib_number' => fake()->bothify('PIB-#####'),
            'sppb_date' => null,
            'created_by' => null,
            'updated_by' => null,
            'from_shipment' => 0,
        ];
    }

    /**
     * Indicate that this Inbound record originated from a pushed Shipment.
     */
    public function fromShipment(): static
    {
        return $this->state(fn (array $attributes) => [
            'from_shipment' => 1,
        ]);
    }
}
