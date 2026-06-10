<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbound;
use App\Models\Outbound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Returns aggregated stats for the dashboard.
     */
    public function stats(Request $request): JsonResponse
    {
        $totalShipments   = Inbound::shipments()->count();
        $totalInbound     = Inbound::inboundOnly()->count();
        $outboundActive   = Outbound::where('status', 'inprogress')->count();
        $outboundDone     = Outbound::where('status', 'successful')->count();

        // Recent shipments (last 5)
        $recentShipments = Inbound::shipments()
            ->orderByDesc('date_created')
            ->limit(5)
            ->get(['id', 'hawb', 'descr', 'status', 'date_created', 'eta']);

        // Recent outbound (last 5)
        $recentOutbound = Outbound::orderByDesc('date_created')
            ->limit(5)
            ->get(['id', 'po', 'destination', 'qty', 'status', 'date_created']);

        // Outbound status breakdown
        $statusBreakdown = Outbound::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'data' => [
                'total_shipments'    => $totalShipments,
                'total_inbound'      => $totalInbound,
                'outbound_active'    => $outboundActive,
                'outbound_done'      => $outboundDone,
                'recent_shipments'   => $recentShipments,
                'recent_outbound'    => $recentOutbound,
                'status_breakdown'   => $statusBreakdown,
            ],
        ]);
    }
}
