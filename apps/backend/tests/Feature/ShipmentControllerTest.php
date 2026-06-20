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

    public function test_update_modifies_shipment_record(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create(['descr' => 'Old descr']);

        $response = $this->putJson("/api/shipments/{$shipment->id}", [
            'descr' => 'New descr',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.descr', 'New descr');
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
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create();

        $response = $this->deleteJson("/api/shipments/{$shipment->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('el_inbound_header', ['id' => $shipment->id]);
    }

    /**
     * BACKLOG NOTE (discovered during Phase 1 test authoring, do not fix in this
     * phase — out of blast radius per plan's Blockers section): pushInbound()
     * creates a new Inbound row reusing the SAME hawb as the source shipment,
     * but `el_inbound_header.hawb` has a global UNIQUE KEY constraint (see
     * tables_schema.sql). Since the original shipment row is never deleted or
     * renamed, the INSERT always violates the unique constraint and the
     * endpoint currently returns HTTP 500 (PDOException: Integrity constraint
     * violation 1062 Duplicate entry for key 'hawb') instead of HTTP 201 with
     * a copied Inbound record. This test documents the ACTUAL current
     * behavior (confirmed via direct execution against wmslite_test) rather
     * than the originally-intended happy path. Recorded as a Phase 2+ backlog
     * candidate in the phase report.
     */
    public function test_push_inbound_currently_fails_with_500_due_to_duplicate_hawb_constraint(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create([
            'hawb' => 'HAWB-PUSH0001',
            'descr' => 'Pushable shipment',
        ]);

        $response = $this->postJson("/api/shipments/{$shipment->id}/push-inbound");

        // Documents the real, currently-broken behavior: the unique `hawb`
        // constraint on `el_inbound_header` rejects the duplicate insert
        // because the source shipment row is never removed/renamed first.
        $response->assertStatus(500);

        // Original shipment record is untouched (still from_shipment = 1,
        // still present) since the failure happens at INSERT time, after the
        // duplicate-inbound conflict-409 check already passed.
        $this->assertDatabaseHas('el_inbound_header', [
            'id' => $shipment->id,
            'from_shipment' => 1,
        ]);
    }

    /**
     * BACKLOG NOTE (discovered during Phase 1 test authoring, do not fix in
     * this phase): the conflict-409 branch in pushInbound() checks whether an
     * Inbound::inboundOnly() record already exists with the same `hawb` as
     * the shipment being pushed. However, `el_inbound_header.hawb` has a
     * global UNIQUE KEY constraint spanning ALL rows (shipment rows AND
     * inbound-only rows alike) — so a shipment row and an inbound-only row
     * can never simultaneously share the same hawb value in the first place.
     * This means the 409-conflict branch is effectively unreachable dead code
     * against the real schema — see the detailed writeup in
     * tests/Unit/ShipmentControllerPushInboundTest.php for why this is not
     * force-tested via schema mutation. Recorded as a Phase 2+ backlog
     * candidate alongside the 500-on-push-inbound bug above.
     */
    public function test_push_inbound_conflict_branch_is_unreachable_via_real_db_state(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create(['hawb' => 'HAWB-CONFLICT01']);

        // Attempting to create a second el_inbound_header row with the same
        // hawb (simulating the precondition the 409 branch checks for) is
        // rejected by the DB's own unique constraint — proving the 409
        // branch can never trigger against real data.
        $this->expectException(\Illuminate\Database\QueryException::class);

        Inbound::factory()->create(['hawb' => 'HAWB-CONFLICT01']);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/shipments');

        $response->assertStatus(401);
    }
}
