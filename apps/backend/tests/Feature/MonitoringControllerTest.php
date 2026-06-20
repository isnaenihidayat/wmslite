<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_index_returns_paginated_activity_log(): void
    {
        $this->actingUser();

        ActivityLog::factory()->count(3)->create();

        $response = $this->getJson('/api/monitoring');

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

        ActivityLog::factory()->create(['hawb' => 'HAWB-MATCHME']);
        ActivityLog::factory()->create(['hawb' => 'HAWB-OTHER']);

        $response = $this->getJson('/api/monitoring?search=MATCHME');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('HAWB-MATCHME', $response->json('data.0.hawb'));
    }

    public function test_index_filters_by_status(): void
    {
        $this->actingUser();

        ActivityLog::factory()->create(['status' => 'in']);
        ActivityLog::factory()->create(['status' => 'out']);

        $response = $this->getJson('/api/monitoring?status=in');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('in', $response->json('data.0.status'));
    }

    public function test_index_filters_by_date_range(): void
    {
        $this->actingUser();

        ActivityLog::factory()->create(['date_created' => now()->subDays(10)]);
        ActivityLog::factory()->create(['date_created' => now()]);

        $dateFrom = now()->subDays(1)->toDateString();

        $response = $this->getJson("/api/monitoring?date_from={$dateFrom}");

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_index_respects_per_page_cap(): void
    {
        $this->actingUser();

        ActivityLog::factory()->count(5)->create();

        $response = $this->getJson('/api/monitoring?per_page=500');

        $response->assertOk();
        $this->assertSame(200, $response->json('meta.per_page'));
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/monitoring');

        $response->assertStatus(401);
    }
}
