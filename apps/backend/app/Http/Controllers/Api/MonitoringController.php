<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * GET /api/monitoring
     * Read-only activity log viewer.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::query();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hawb', 'like', "%{$search}%")
                  ->orWhere('hawb_descr', 'like', "%{$search}%")
                  ->orWhere('loc_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Date range filter
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('date_created', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('date_created', '<=', $dateTo);
        }

        // Sort
        $sortCol = $request->get('sort', 'date_created');
        $sortDir = $request->get('dir', 'desc');
        $allowed = ['id', 'hawb', 'loc_name', 'status', 'date_created'];
        if (in_array($sortCol, $allowed)) {
            $query->orderBy($sortCol, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->get('per_page', 50), 200);
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
}
