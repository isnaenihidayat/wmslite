<?php

namespace Tests\Feature;

use App\Models\Outbound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutboundControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_index_returns_paginated_outbound_with_category(): void
    {
        $this->actingUser();

        Outbound::factory()->count(3)->create();

        $response = $this->getJson('/api/outbound');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertSame(3, $response->json('meta.total'));
    }

    public function test_index_filters_by_search(): void
    {
        $this->actingUser();

        Outbound::factory()->create(['destination' => 'Jakarta Warehouse']);
        Outbound::factory()->create(['destination' => 'Bandung Warehouse']);

        $response = $this->getJson('/api/outbound?search=Jakarta');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_index_filters_by_status(): void
    {
        $this->actingUser();

        Outbound::factory()->create(['status' => 'inprogress']);
        Outbound::factory()->create(['status' => 'successful']);

        $response = $this->getJson('/api/outbound?status=successful');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_show_returns_outbound_with_details_and_category(): void
    {
        $this->actingUser();

        $outbound = Outbound::factory()->create();

        $response = $this->getJson("/api/outbound/{$outbound->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $outbound->id);
    }

    public function test_show_returns_404_for_missing_outbound(): void
    {
        $this->actingUser();

        $response = $this->getJson('/api/outbound/999999');

        $response->assertStatus(404);
    }

    public function test_store_creates_outbound_record(): void
    {
        $user = $this->actingUser();

        $payload = [
            'destination' => 'Surabaya Warehouse',
            'po' => 'PO-12345',
            'qty' => '10',
        ];

        $response = $this->postJson('/api/outbound', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.destination', 'Surabaya Warehouse')
            ->assertJsonPath('data.status', 'inprogress')
            ->assertJsonPath('data.created_by', $user->user_id);

        $this->assertDatabaseHas('el_outbound_header', [
            'destination' => 'Surabaya Warehouse',
            'po' => 'PO-12345',
        ]);
    }

    public function test_store_requires_destination(): void
    {
        $this->actingUser();

        $response = $this->postJson('/api/outbound', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination']);
    }

    public function test_update_modifies_outbound_record(): void
    {
        $this->actingUser();

        $outbound = Outbound::factory()->create(['status' => 'inprogress', 'destination' => 'Old Dest']);

        $response = $this->putJson("/api/outbound/{$outbound->id}", [
            'destination' => 'New Dest',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.destination', 'New Dest');

        $this->assertDatabaseHas('el_outbound_header', [
            'id' => $outbound->id,
            'destination' => 'New Dest',
        ]);
    }

    public function test_update_rejects_successful_outbound(): void
    {
        $this->actingUser();

        $outbound = Outbound::factory()->create(['status' => 'successful']);

        $response = $this->putJson("/api/outbound/{$outbound->id}", [
            'destination' => 'Should Not Apply',
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('el_outbound_header', [
            'id' => $outbound->id,
            'destination' => $outbound->destination,
        ]);
    }

    public function test_destroy_deletes_outbound_record(): void
    {
        $this->actingUser();

        $outbound = Outbound::factory()->create(['status' => 'inprogress']);

        $response = $this->deleteJson("/api/outbound/{$outbound->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('el_outbound_header', ['id' => $outbound->id]);
    }

    public function test_destroy_rejects_successful_outbound(): void
    {
        $this->actingUser();

        $outbound = Outbound::factory()->create(['status' => 'successful']);

        $response = $this->deleteJson("/api/outbound/{$outbound->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('el_outbound_header', ['id' => $outbound->id]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/outbound');

        $response->assertStatus(401);
    }
}
