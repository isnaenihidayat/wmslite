<?php

namespace Tests\Feature;

use App\Models\Moving;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    protected function actingAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    public function test_index_returns_paginated_moving_records(): void
    {
        $this->actingUser();

        Moving::factory()->count(3)->create();

        $response = $this->getJson('/api/moving');

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

        Moving::factory()->create(['hawb' => 'HAWB-MATCHME']);
        Moving::factory()->create(['hawb' => 'HAWB-OTHER']);

        $response = $this->getJson('/api/moving?search=MATCHME');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_index_filters_by_date_range(): void
    {
        $this->actingUser();

        Moving::factory()->create(['date_created' => now()->subDays(10)]);
        Moving::factory()->create(['date_created' => now()]);

        $dateFrom = now()->subDays(1)->toDateString();

        $response = $this->getJson("/api/moving?date_from={$dateFrom}");

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_store_creates_moving_record_with_current_user(): void
    {
        $user = $this->actingAdmin();

        $payload = [
            'hawb' => 'HAWB-99999999',
            'hawb_descr' => 'Test item',
            'loc_before' => 'RACK-01',
            'loc_after' => 'RACK-02',
        ];

        $response = $this->postJson('/api/moving', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.hawb', 'HAWB-99999999')
            ->assertJsonPath('data.loc_after', 'RACK-02')
            ->assertJsonPath('data.users', $user->full_name);

        $this->assertDatabaseHas('el_moving', [
            'hawb' => 'HAWB-99999999',
            'loc_before' => 'RACK-01',
            'loc_after' => 'RACK-02',
        ]);
    }

    public function test_store_requires_required_fields(): void
    {
        $this->actingAdmin();

        $response = $this->postJson('/api/moving', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hawb', 'loc_before', 'loc_after']);
    }

    public function test_store_returns_403_for_non_admin(): void
    {
        $this->actingUser();

        $payload = [
            'hawb' => 'HAWB-99999999',
            'hawb_descr' => 'Test item',
            'loc_before' => 'RACK-01',
            'loc_after' => 'RACK-02',
        ];

        $response = $this->postJson('/api/moving', $payload);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('el_moving', ['hawb' => 'HAWB-99999999']);
    }

    public function test_destroy_deletes_moving_record(): void
    {
        $this->actingAdmin();

        $moving = Moving::factory()->create();

        $response = $this->deleteJson("/api/moving/{$moving->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('el_moving', ['id' => $moving->id]);
    }

    public function test_destroy_returns_404_for_missing_record(): void
    {
        $this->actingAdmin();

        $response = $this->deleteJson('/api/moving/999999');

        $response->assertStatus(404);
    }

    public function test_destroy_returns_403_for_non_admin(): void
    {
        $this->actingUser();

        $moving = Moving::factory()->create();

        $response = $this->deleteJson("/api/moving/{$moving->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('el_moving', ['id' => $moving->id]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/moving');

        $response->assertStatus(401);
    }
}
