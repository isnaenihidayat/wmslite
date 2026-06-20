<?php

namespace Tests\Feature;

use App\Models\Inbound;
use App\Models\InboundDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BACKLOG NOTE (discovered during Phase 1 test authoring, do not fix in this
 * phase — out of blast radius, this phase adds tests not product fixes):
 * InboundController::index() has a potential N+1 query pattern — for each
 * paginated Inbound record it accesses `$inbound->details` (lazy-loaded
 * relation) inside a ->map() callback, without eager-loading `details` on
 * the initial query (only `category` is eager-loaded via ->with('category')).
 * This means N additional queries run per page of results (one per row) to
 * compute items_total/items_picked. Recorded as a Phase 2+ backlog
 * candidate.
 *
 * SECOND BACKLOG NOTE (also discovered during this phase, same do-not-fix
 * scope): InboundController::details() (`GET /api/inbound/{id}/details`)
 * calls `->orderBy('id')` on the `el_inbound_details` table, but that table
 * has no `id` column at all (confirmed via tables_schema.sql — no primary
 * key, only a non-unique KEY on `hawb`). The endpoint always returns 500.
 * See test_details_endpoint_currently_fails_with_500_due_to_missing_id_column
 * below.
 */
class InboundControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_index_returns_only_inbound_only_records(): void
    {
        $this->actingUser();

        Inbound::factory()->count(3)->create(); // from_shipment = 0
        Inbound::factory()->count(2)->fromShipment()->create();

        $response = $this->getJson('/api/inbound');

        $response->assertOk();
        $this->assertSame(3, $response->json('meta.total'));
    }

    public function test_index_includes_items_total_and_items_picked(): void
    {
        $this->actingUser();

        $inbound = Inbound::factory()->create(['hawb' => 'HAWB-PICKTEST']);
        InboundDetail::factory()->count(2)->create(['hawb' => 'HAWB-PICKTEST']);
        InboundDetail::factory()->picked()->create(['hawb' => 'HAWB-PICKTEST']);

        $response = $this->getJson('/api/inbound');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $inbound->id);

        $this->assertSame(3, $row['items_total']);
        $this->assertSame(1, $row['items_picked']);
    }

    public function test_index_filters_by_search(): void
    {
        $this->actingUser();

        Inbound::factory()->create(['hawb' => 'HAWB-MATCHME']);
        Inbound::factory()->create(['hawb' => 'HAWB-OTHER']);

        $response = $this->getJson('/api/inbound?search=MATCHME');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_index_filters_by_status(): void
    {
        $this->actingUser();

        Inbound::factory()->create(['status' => 'inprogress']);
        Inbound::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/inbound?status=completed');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_index_filters_by_warehouse_locator(): void
    {
        $this->actingUser();

        Inbound::factory()->create(['locator' => 'RACK-01']);
        Inbound::factory()->create(['locator' => 'RACK-02']);

        $response = $this->getJson('/api/inbound?warehouse=RACK-01');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_show_returns_inbound_with_category_and_details(): void
    {
        $this->actingUser();

        $inbound = Inbound::factory()->create();

        $response = $this->getJson("/api/inbound/{$inbound->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $inbound->id);
    }

    public function test_show_does_not_return_shipment_record(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create();

        $response = $this->getJson("/api/inbound/{$shipment->id}");

        $response->assertStatus(404);
    }

    /**
     * BACKLOG NOTE (discovered during Phase 1 test authoring, do not fix in
     * this phase — out of blast radius): `el_inbound_details` has NO `id`
     * column at all (confirmed via tables_schema.sql — the table has no
     * primary key, only a non-unique KEY on `hawb`). However,
     * `InboundController::details()` calls
     * `$inbound->details()->orderBy('id')->get()`, which always fails with a
     * 500 (PDOException: Unknown column 'id' in 'ORDER BY') because that
     * column does not exist. This means GET /api/inbound/{id}/details is
     * currently completely broken in production. This test documents the
     * real, currently-broken behavior. Recorded as a Phase 2+ backlog
     * candidate alongside the index() N+1 note above.
     */
    public function test_details_endpoint_currently_fails_with_500_due_to_missing_id_column(): void
    {
        $this->actingUser();

        $inbound = Inbound::factory()->create(['hawb' => 'HAWB-DETAILTEST']);
        InboundDetail::factory()->count(3)->create(['hawb' => 'HAWB-DETAILTEST']);

        $response = $this->getJson("/api/inbound/{$inbound->id}/details");

        $response->assertStatus(500);
    }

    public function test_store_creates_inbound_record(): void
    {
        $user = $this->actingUser();

        $payload = [
            'hawb' => 'HAWB-NEWINBOUND',
            'descr' => 'New inbound item',
        ];

        $response = $this->postJson('/api/inbound', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.hawb', 'HAWB-NEWINBOUND')
            ->assertJsonPath('data.from_shipment', false)
            ->assertJsonPath('data.created_by', $user->user_id);

        $this->assertDatabaseHas('el_inbound_header', [
            'hawb' => 'HAWB-NEWINBOUND',
            'from_shipment' => 0,
        ]);
    }

    public function test_store_rejects_duplicate_hawb(): void
    {
        $this->actingUser();

        Inbound::factory()->create(['hawb' => 'HAWB-DUPEINBOUND']);

        $response = $this->postJson('/api/inbound', [
            'hawb' => 'HAWB-DUPEINBOUND',
            'descr' => 'Duplicate',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hawb']);
    }

    public function test_update_modifies_inbound_record(): void
    {
        $this->actingUser();

        $inbound = Inbound::factory()->create(['descr' => 'Old descr']);

        $response = $this->putJson("/api/inbound/{$inbound->id}", [
            'descr' => 'New descr',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.descr', 'New descr');
    }

    public function test_update_does_not_affect_shipment_record(): void
    {
        $this->actingUser();

        $shipment = Inbound::factory()->fromShipment()->create();

        $response = $this->putJson("/api/inbound/{$shipment->id}", [
            'descr' => 'Should not apply',
        ]);

        $response->assertStatus(404);
    }

    public function test_destroy_deletes_inbound_record(): void
    {
        $this->actingUser();

        $inbound = Inbound::factory()->create();

        $response = $this->deleteJson("/api/inbound/{$inbound->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('el_inbound_header', ['id' => $inbound->id]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/inbound');

        $response->assertStatus(401);
    }
}
