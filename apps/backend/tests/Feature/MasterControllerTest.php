<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\ProductCategory;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_locations_returns_ordered_list(): void
    {
        $this->actingUser();

        Location::factory()->create(['loc_name' => 'RACK-02']);
        Location::factory()->create(['loc_name' => 'RACK-01']);

        $response = $this->getJson('/api/master/locations');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['loc_id', 'loc_name', 'loc_descr']]]);

        $names = collect($response->json('data'))->pluck('loc_name')->all();
        $this->assertSame(['RACK-01', 'RACK-02'], $names);
    }

    public function test_categories_returns_ordered_list(): void
    {
        $this->actingUser();

        ProductCategory::factory()->create(['name' => 'Zebra Category']);
        ProductCategory::factory()->create(['name' => 'Apple Category']);

        $response = $this->getJson('/api/master/categories');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name']]]);

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(['Apple Category', 'Zebra Category'], $names);
    }

    public function test_recipients_returns_ordered_list(): void
    {
        $this->actingUser();

        Recipient::factory()->create(['name' => 'Zebra Corp']);
        Recipient::factory()->create(['name' => 'Apple Corp']);

        $response = $this->getJson('/api/master/recipients');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'created_at']]]);

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(['Apple Corp', 'Zebra Corp'], $names);
    }

    public function test_locations_requires_authentication(): void
    {
        $response = $this->getJson('/api/master/locations');

        $response->assertStatus(401);
    }

    public function test_categories_requires_authentication(): void
    {
        $response = $this->getJson('/api/master/categories');

        $response->assertStatus(401);
    }

    public function test_recipients_requires_authentication(): void
    {
        $response = $this->getJson('/api/master/recipients');

        $response->assertStatus(401);
    }
}
