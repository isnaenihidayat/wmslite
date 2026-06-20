<?php

namespace Tests\Feature;

use App\Models\Inbound;
use App\Models\Outbound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_stats_returns_aggregated_counts(): void
    {
        $this->actingUser();

        // 2 shipments (from_shipment = 1), 3 inbound-only (from_shipment = 0)
        Inbound::factory()->count(2)->fromShipment()->create();
        Inbound::factory()->count(3)->create();

        // 2 active outbound, 1 done
        Outbound::factory()->count(2)->create(['status' => 'inprogress']);
        Outbound::factory()->count(1)->create(['status' => 'successful']);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_shipments',
                    'total_inbound',
                    'outbound_active',
                    'outbound_done',
                    'recent_shipments',
                    'recent_outbound',
                    'status_breakdown',
                ],
            ]);

        $response->assertJsonPath('data.total_shipments', 2);
        $response->assertJsonPath('data.total_inbound', 3);
        $response->assertJsonPath('data.outbound_active', 2);
        $response->assertJsonPath('data.outbound_done', 1);
    }

    public function test_stats_recent_shipments_limited_to_five_and_ordered_desc(): void
    {
        $this->actingUser();

        Inbound::factory()->count(7)->fromShipment()->create();

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertOk();
        $recent = $response->json('data.recent_shipments');

        $this->assertCount(5, $recent);
    }

    public function test_stats_status_breakdown_groups_by_status(): void
    {
        $this->actingUser();

        Outbound::factory()->count(2)->create(['status' => 'inprogress']);
        Outbound::factory()->count(1)->create(['status' => 'successful']);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertOk();
        $breakdown = collect($response->json('data.status_breakdown'))
            ->keyBy('status')
            ->map(fn ($row) => $row['count']);

        $this->assertSame(2, (int) $breakdown['inprogress']);
        $this->assertSame(1, (int) $breakdown['successful']);
    }

    public function test_stats_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(401);
    }
}
