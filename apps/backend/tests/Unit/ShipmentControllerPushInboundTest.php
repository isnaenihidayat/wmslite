<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ShipmentController;
use App\Models\Inbound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Isolated Unit test for ShipmentController::pushInbound() — calls the
 * controller method directly (not via HTTP) to verify its behavior in
 * isolation from routing/middleware concerns, which are already covered by
 * the Feature-level tests in ShipmentControllerTest.
 *
 * BACKLOG NOTE (discovered during Phase 1 test authoring, do not fix in this
 * phase — out of blast radius per the plan's Blockers section):
 * `el_inbound_header.hawb` has a global UNIQUE KEY constraint covering every
 * row in the table (shipment rows where from_shipment=1 AND inbound-only
 * rows where from_shipment=0 alike — confirmed via tables_schema.sql line
 * ~182). This has two consequences for pushInbound():
 *
 *   1. The intended "happy path" (copy a shipment into a new Inbound row
 *      with the same hawb) ALWAYS fails with a 500 PDOException (Integrity
 *      constraint violation 1062) because the source shipment row already
 *      occupies that hawb value and is never deleted/renamed first.
 *   2. The 409-conflict branch (`Inbound::inboundOnly()->where('hawb', ...)`)
 *      checks for a precondition — a shipment row and an inbound-only row
 *      simultaneously sharing the same hawb — that can NEVER occur in real
 *      data, because the same unique constraint that breaks case 1 also
 *      prevents this precondition from ever being constructed. The 409
 *      branch is therefore unreachable dead code against the real schema.
 *
 * Both findings are recorded in the phase report as Phase 2+ backlog
 * candidates. This test file documents the real, currently-broken behavior
 * (case 1) rather than asserting the originally-intended 201 happy path.
 * The unreachable 409 branch (case 2) is not force-tested via schema
 * mutation (e.g. temporarily dropping the unique index) because ALTER TABLE
 * statements implicitly commit in MySQL/InnoDB, which breaks Laravel's
 * RefreshDatabase transaction wrapping for subsequent tests in the suite —
 * confirmed empirically during this phase's test authoring (the DDL mutation
 * corrupted the schema for the next test in the run). Forcing an
 * unreachable, schema-violating precondition is not worth that risk; the
 * 409 branch's existence and unreachability is documented here and in the
 * phase report instead.
 */
class ShipmentControllerPushInboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_inbound_fails_with_500_due_to_duplicate_hawb_constraint(): void
    {
        $user = User::factory()->create();

        $shipment = Inbound::factory()->fromShipment()->create(['hawb' => 'HAWB-UNITNOCONFLICT']);

        $request = Request::create('/api/shipments/'.$shipment->id.'/push-inbound', 'POST');
        $request->setUserResolver(fn () => $user);

        $controller = new ShipmentController();

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->expectExceptionMessageMatches('/1062/');

        $controller->pushInbound($request, $shipment->id);
    }

    public function test_push_inbound_returns_404_for_inbound_only_record_not_a_shipment(): void
    {
        $user = User::factory()->create();

        // from_shipment = 0 (default factory state) — not a shipment, so
        // Inbound::shipments()->findOrFail() must reject it.
        $inbound = Inbound::factory()->create();

        $request = Request::create('/api/shipments/'.$inbound->id.'/push-inbound', 'POST');
        $request->setUserResolver(fn () => $user);

        $controller = new ShipmentController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->pushInbound($request, $inbound->id);
    }
}
