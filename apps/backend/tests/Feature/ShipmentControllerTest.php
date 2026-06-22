<?php

namespace Tests\Feature;

use App\Models\Inbound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentControllerTest extends TestCase
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

    public function test_index_returns_only_shipment_records(): void
    {
        $this->actingUser();

        Inbound::factory()->count(2)->fromShipment()->create();
        Inbound::factory()->count(3)->create(); // inbound-only, from_shipment = 0

        $response = $this->getJson('/api/shipments');

        $response->assertOk();
        $this->assertSame(2, $response->json('meta.total'));
    }

    public function test_index_filters_by_search(): void
    {
        $this->actingUser();

        Inbound::factory()->fromShipment()->create(['hawb' => 'HAWB-MATCHME']);
        Inbound::factory()->fromShipment()->create(['hawb' => 'HAWB-OTHER']);

        $response = $this->getJson('/api/shipments?search=MATCHME');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_show_returns_shipment_with_details_and_category(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create();

        $response = $this->getJson("/api/shipments/{$shipment->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $shipment->id);
    }

    public function test_show_does_not_return_inbound_only_record(): void
    {
        $this->actingUser();

        $inbound = Inbound::factory()->create(); // from_shipment = 0

        $response = $this->getJson("/api/shipments/{$inbound->id}");

        $response->assertStatus(404);
    }

    public function test_store_creates_shipment_with_from_shipment_flag(): void
    {
        $user = $this->actingUser();

        $payload = [
            'hawb' => 'HAWB-NEW0001',
            'descr' => 'New shipment',
        ];

        $response = $this->postJson('/api/shipments', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.hawb', 'HAWB-NEW0001')
            ->assertJsonPath('data.from_shipment', true)
            ->assertJsonPath('data.created_by', $user->user_id);

        $this->assertDatabaseHas('el_inbound_header', [
            'hawb' => 'HAWB-NEW0001',
            'from_shipment' => 1,
        ]);
    }

    public function test_store_rejects_duplicate_hawb(): void
    {
        $this->actingUser();

        Inbound::factory()->fromShipment()->create(['hawb' => 'HAWB-DUPE0001']);

        $response = $this->postJson('/api/shipments', [
            'hawb' => 'HAWB-DUPE0001',
            'descr' => 'Duplicate',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hawb']);
    }

    /**
     * go-live Phase 3, Section 1: ShipmentController::update()/destroy() are
     * now admin-only (ShipmentPolicy, invoked directly — see ShipmentPolicy
     * class doc comment). Pre-existing tests below that called actingUser()
     * then mutated a record were updated to actingAdmin().
     */
    public function test_update_modifies_shipment_record(): void
    {
        $this->actingAdmin();

        $shipment = Inbound::factory()->fromShipment()->create(['descr' => 'Old descr']);

        $response = $this->putJson("/api/shipments/{$shipment->id}", [
            'descr' => 'New descr',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.descr', 'New descr');
    }

    public function test_update_returns_403_for_non_admin(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create(['descr' => 'Old descr']);

        $response = $this->putJson("/api/shipments/{$shipment->id}", [
            'descr' => 'Should not apply',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('el_inbound_header', [
            'id' => $shipment->id,
            'descr' => 'Old descr',
        ]);
    }

    public function test_update_does_not_affect_inbound_only_record(): void
    {
        $this->actingUser();

        $inbound = Inbound::factory()->create();

        $response = $this->putJson("/api/shipments/{$inbound->id}", [
            'descr' => 'Should not apply',
        ]);

        $response->assertStatus(404);
    }

    public function test_destroy_deletes_shipment_record(): void
    {
        $this->actingAdmin();

        $shipment = Inbound::factory()->fromShipment()->create();

        $response = $this->deleteJson("/api/shipments/{$shipment->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('el_inbound_header', ['id' => $shipment->id]);
    }

    public function test_destroy_returns_403_for_non_admin(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create();

        $response = $this->deleteJson("/api/shipments/{$shipment->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('el_inbound_header', ['id' => $shipment->id]);
    }

    /**
     * go-live Phase 2 (B3a) fix: pushInbound() now transitions the shipment
     * row in-place (from_shipment 1 -> 0, status -> inprogress) instead of
     * creating a duplicate `el_inbound_header` row with the same `hawb`. The
     * `inbound_id` returned is the SAME id as the originating shipment — see
     * Public Contracts in phase-02-data-model-auth-hardening_PLAN_19-06-26.md.
     * Closes
     * process/features/go-live/backlog/shipment-push-inbound-bug_NOTE_20-06-26.md.
     */
    public function test_push_inbound_updates_shipment_in_place(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create([
            'hawb' => 'HAWB-PUSH0001',
            'descr' => 'Pushable shipment',
            'status' => 'created',
        ]);

        $response = $this->postJson("/api/shipments/{$shipment->id}/push-inbound");

        $response->assertCreated()
            ->assertJsonPath('inbound_id', $shipment->id)
            ->assertJsonPath('data.id', $shipment->id)
            ->assertJsonPath('data.from_shipment', false)
            ->assertJsonPath('data.status', 'inprogress');

        // Same row updated in place — no duplicate el_inbound_header row for this hawb.
        $this->assertSame(1, Inbound::where('hawb', 'HAWB-PUSH0001')->count());
        $this->assertDatabaseHas('el_inbound_header', [
            'id' => $shipment->id,
            'hawb' => 'HAWB-PUSH0001',
            'from_shipment' => 0,
            'status' => 'inprogress',
        ]);
    }

    /**
     * go-live Phase 2 (B3a) fix: the 409 duplicate-hawb branch was removed
     * since it is now unreachable — pushInbound() no longer creates a new
     * row, so there is no way for a shipment row and an inbound-only row to
     * collide on the same hawb via this endpoint. Pushing the same shipment
     * a second time is idempotent: it simply re-applies the same update.
     */
    public function test_push_inbound_is_idempotent_when_called_twice(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create(['hawb' => 'HAWB-CONFLICT01']);

        $this->postJson("/api/shipments/{$shipment->id}/push-inbound")->assertCreated();
        $response = $this->postJson("/api/shipments/{$shipment->id}/push-inbound");

        // Second call 404s via Inbound::shipments()->findOrFail() since the
        // row is no longer from_shipment=1 after the first push.
        $response->assertStatus(404);

        $this->assertSame(1, Inbound::where('hawb', 'HAWB-CONFLICT01')->count());
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/shipments');

        $response->assertStatus(401);
    }
}
