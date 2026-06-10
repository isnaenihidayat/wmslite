<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outbound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutboundController extends Controller
{
    /**
     * GET /api/outbound
     */
    public function index(Request $request): JsonResponse
    {
        $query = Outbound::with('category');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhere('transporter', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Sort
        $sortCol = $request->get('sort', 'date_created');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'po', 'destination', 'transporter', 'qty', 'status', 'date_created', 'date_updated'];
        if (in_array($sortCol, $allowedSorts)) {
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
     * GET /api/outbound/{id}
     */
    public function show(int $id): JsonResponse
    {
        $outbound = Outbound::with('details', 'category')->findOrFail($id);
        return response()->json(['data' => $outbound]);
    }

    /**
     * POST /api/outbound
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'po'                  => ['nullable', 'string', 'max:255'],
            'destination'         => ['required', 'string', 'max:255'],
            'qty'                 => ['nullable', 'string'],
            'delivery_id'         => ['nullable', 'integer'],
            'transporter'         => ['nullable', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'integer'],
            'status'              => ['nullable', 'string'],
        ]);

        $validated['checker']    = $request->user()->full_name ?? 'system';
        $validated['created_by'] = $request->user()->user_id;
        $validated['status']     = $validated['status'] ?? 'inprogress';

        $outbound = Outbound::create($validated);

        return response()->json(['data' => $outbound], 201);
    }

    /**
     * PUT /api/outbound/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $outbound = Outbound::findOrFail($id);

        // Cannot edit successfully completed outbound
        if ($outbound->status === 'successful') {
            return response()->json([
                'message' => 'Cannot modify a successfully completed outbound.',
            ], 422);
        }

        $validated = $request->validate([
            'po'                  => ['nullable', 'string', 'max:255'],
            'destination'         => ['sometimes', 'string', 'max:255'],
            'qty'                 => ['nullable', 'string'],
            'delivery_id'         => ['nullable', 'integer'],
            'transporter'         => ['nullable', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'integer'],
            'status'              => ['nullable', 'string'],
        ]);

        $validated['updated_by'] = $request->user()->user_id;
        $outbound->update($validated);

        return response()->json(['data' => $outbound->fresh()]);
    }

    /**
     * DELETE /api/outbound/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $outbound = Outbound::findOrFail($id);

        if ($outbound->status === 'successful') {
            return response()->json([
                'message' => 'Cannot delete a successfully completed outbound.',
            ], 422);
        }

        $outbound->delete();

        return response()->json(['message' => 'Outbound record deleted.']);
    }
}
