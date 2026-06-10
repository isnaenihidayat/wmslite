<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbound;
use App\Models\InboundDetail;
use App\Models\Outbound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * GET /api/reports/shipment
     */
    public function shipment(Request $request): JsonResponse
    {
        return $this->buildReport(
            Inbound::shipments(),
            $request,
            ['id', 'hawb', 'descr', 'modality', 'qty', 'po', 'locator', 'status', 'etd', 'eta', 'ata', 'date_created']
        );
    }

    /**
     * GET /api/reports/inbound
     */
    public function inbound(Request $request): JsonResponse
    {
        return $this->buildReport(
            Inbound::inboundOnly(),
            $request,
            ['id', 'hawb', 'descr', 'modality', 'qty', 'po', 'locator', 'status', 'etd', 'eta', 'ata', 'date_created']
        );
    }

    /**
     * GET /api/reports/outbound
     */
    public function outbound(Request $request): JsonResponse
    {
        return $this->buildReport(
            Outbound::query(),
            $request,
            ['id', 'po', 'destination', 'transporter', 'qty', 'status', 'date_created', 'date_updated']
        );
    }

    /**
     * GET /api/reports/inventory
     * Items that have been picked (flag=1) within date range
     */
    public function inventory(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $query = InboundDetail::select([
                'hawb', 'descr', 'loc', 'weight', 'flag', 'scan_time',
            ])
            ->where('flag', 1)
            ->whereBetween('scan_time', [
                $request->start_date . ' 00:00:00',
                $request->end_date   . ' 23:59:59',
            ])
            ->orderByDesc('scan_time');

        $perPage = min((int) $request->get('per_page', 25), 100);
        $results = $query->paginate($perPage);

        return response()->json([
            'data' => $results->items(),
            'meta' => [
                'current_page' => $results->currentPage(),
                'per_page'     => $results->perPage(),
                'total'        => $results->total(),
                'last_page'    => $results->lastPage(),
            ],
        ]);
    }

    /**
     * Generic paginated report builder with date range filter.
     */
    private function buildReport($query, Request $request, array $columns): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $dateCol = $query->getModel()->getTable() === 'outbound_header'
            ? 'date_created'
            : 'date_created';

        $query->select($columns)
            ->whereBetween($dateCol, [
                $request->start_date . ' 00:00:00',
                $request->end_date   . ' 23:59:59',
            ])
            ->orderByDesc($dateCol);

        $perPage = min((int) $request->get('per_page', 25), 1000);
        $results = $query->paginate($perPage);

        return response()->json([
            'data' => $results->items(),
            'meta' => [
                'current_page' => $results->currentPage(),
                'per_page'     => $results->perPage(),
                'total'        => $results->total(),
                'last_page'    => $results->lastPage(),
                'start_date'   => $request->start_date,
                'end_date'     => $request->end_date,
            ],
        ]);
    }
}
