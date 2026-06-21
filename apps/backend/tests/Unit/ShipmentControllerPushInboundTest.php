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
 * go-live Phase 2 (B3a) fix: pushInbound() now transitions the existing
 * shipment row in-place (from_shipment 1 -> 0) instead of creating a new
 * `el_inbound_header` row with the same `hawb`. This removes the unique-
 * constraint violation that previously made the endpoint always fail with a
 * 500, and removes the now-unreachable 409 duplicate-hawb branch since only
 * one row per hawb ever exists. See
 * process/features/go-live/backlog/shipment-push-inbound-bug_NOTE_20-06-26.md
 * (resolved).
 */
class ShipmentControllerPushInboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_inbound_updates_shipment_row_in_place(): void
    {
        $user = User::factory()->create();

        $shipment = Inbound::factory()->fromShipment()->create([
            'hawb' => 'HAWB-UNITNOCONFLICT',
            'status' => 'created',
        ]);

        $request = Request::create('/api/shipments/'.$shipment->id.'/push-inbound', 'POST');
        $request->setUserResolver(fn () => $user);

        $controller = new ShipmentController();

        $response = $controller->pushInbound($request, $shipment->id);

        $this->assertSame(201, $response->getStatusCode());

        $payload = $response->getData(true);
        $this->assertSame($shipment->id, $payload['inbound_id']);

        $shipment->refresh();
        $this->assertFalse((bool) $shipment->from_shipment);
        $this->assertSame('inprogress', $shipment->status);

        // No duplicate row was created — only one el_inbound_header row for this hawb.
        $this->assertSame(1, Inbound::where('hawb', 'HAWB-UNITNOCONFLICT')->count());
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
