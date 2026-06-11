<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Moving;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovingController extends Controller
{
    /**
     * GET /api/moving
     */
    public function index(Request $request): JsonResponse
    {
        $query = Moving::query();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hawb', 'like', "%{$search}%")
                  ->orWhere('hawb_descr', 'like', "%{$search}%")
                  ->orWhere('loc_before', 'like', "%{$search}%")
                  ->orWhere('loc_after', 'like', "%{$search}%")
                  ->orWhere('users', 'like', "%{$search}%");
            });
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
        $allowed = ['id', 'hawb', 'hawb_descr', 'loc_before', 'loc_after', 'date_created', 'users'];
        if (in_array($sortCol, $allowed)) {
            $query->orderBy($sortCol, $sortDir === 'asc' ? 'asc' : 'desc');
        }

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
     * POST /api/moving
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hawb'       => ['required', 'string', 'max:255'],
            'hawb_descr' => ['nullable', 'string', 'max:255'],
            'loc_before' => ['required', 'string', 'max:255'],
            'loc_after'  => ['required', 'string', 'max:255'],
        ]);

        $validated['users'] = $request->user()->full_name ?? $request->user()->email_address ?? 'system';

        $moving = Moving::create($validated);

        return response()->json(['data' => $moving], 201);
    }

    /**
     * DELETE /api/moving/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $moving = Moving::findOrFail($id);
        $moving->delete();

        return response()->json(['message' => 'Moving record deleted.']);
    }
}
