<?php

namespace Tests\Feature;

use App\Models\Inbound;
use App\Models\InboundDetail;
use App\Models\Outbound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BACKLOG NOTE (per plan Step B10, do not fix in this phase — out of blast
 * radius): ReportController::buildReport() contains a dead ternary
 * (`$query->getModel()->getTable() === 'outbound_header' ? 'date_created' :
 * 'date_created'`) — both branches are identical, so the condition has no
 * effect; `$dateCol` is always 'date_created' regardless of which model the
 * report query is built against. Recorded as a Phase 2+ backlog candidate.
 */
class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    protected function dateRange(): array
    {
        return [
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
        ];
    }

    public function test_shipment_report_requires_date_range(): void
    {
        $this->actingUser();

        $response = $this->getJson('/api/reports/shipment');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_shipment_report_returns_shipments_within_range(): void
    {
        $this->actingUser();

        Inbound::factory()->fromShipment()->create(['date_created' => now()->subDays(2)]);
        Inbound::factory()->fromShipment()->create(['date_created' => now()->subDays(30)]); // out of range
        Inbound::factory()->create(['date_created' => now()->subDays(2)]); // not a shipment

        $response = $this->getJson('/api/reports/shipment?'.http_build_query($this->dateRange()));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page', 'start_date', 'end_date'],
            ]);

        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_inbound_report_requires_date_range(): void
    {
        $this->actingUser();

        $response = $this->getJson('/api/reports/inbound');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_inbound_report_returns_inbound_only_within_range(): void
    {
        $this->actingUser();

        Inbound::factory()->create(['date_created' => now()->subDays(2)]);
        Inbound::factory()->fromShipment()->create(['date_created' => now()->subDays(2)]); // not inbound-only

        $response = $this->getJson('/api/reports/inbound?'.http_build_query($this->dateRange()));

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_outbound_report_requires_date_range(): void
    {
        $this->actingUser();

        $response = $this->getJson('/api/reports/outbound');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_outbound_report_returns_outbound_within_range(): void
    {
        $this->actingUser();

        Outbound::factory()->create(['date_created' => now()->subDays(2)]);
        Outbound::factory()->create(['date_created' => now()->subDays(30)]); // out of range

        $response = $this->getJson('/api/reports/outbound?'.http_build_query($this->dateRange()));

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_inventory_report_requires_date_range(): void
    {
        $this->actingUser();

        $response = $this->getJson('/api/reports/inventory');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_inventory_report_returns_only_picked_items_within_range(): void
    {
        $this->actingUser();

        InboundDetail::factory()->picked()->create(['scan_time' => now()->subDays(2)]);
        InboundDetail::factory()->create(['flag' => false, 'scan_time' => now()->subDays(2)]); // not picked
        InboundDetail::factory()->picked()->create(['scan_time' => now()->subDays(30)]); // out of range

        $response = $this->getJson('/api/reports/inventory?'.http_build_query($this->dateRange()));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_outbound_report_rejects_end_date_before_start_date(): void
    {
        $this->actingUser();

        $response = $this->getJson('/api/reports/outbound?start_date='.now()->toDateString().'&end_date='.now()->subDays(5)->toDateString());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_shipment_report_requires_authentication(): void
    {
        $response = $this->getJson('/api/reports/shipment?'.http_build_query($this->dateRange()));

        $response->assertStatus(401);
    }
}
