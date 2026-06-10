<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboundController extends Controller
{
    /**
     * GET /api/inbound
     */
    public function index(Request $request): JsonResponse
    {
        $query = Inbound::inboundOnly()->with('category');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hawb', 'like', "%{$search}%")
                  ->orWhere('descr', 'like', "%{$search}%")
                  ->orWhere('po', 'like', "%{$search}%")
                  ->orWhere('locator', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Warehouse (locator) filter
        if ($warehouse = $request->get('warehouse')) {
            if ($warehouse !== 'all') {
                $query->where('locator', 'like', "%{$warehouse}%");
            }
        }

        // Sort
        $sortCol = $request->get('sort', 'date_created');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['id', 'hawb', 'descr', 'status', 'date_created', 'etd', 'eta', 'locator'];
        if (in_array($sortCol, $allowedSorts)) {
            $query->orderBy($sortCol, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->get('per_page', 25), 100);
        $results = $query->paginate($perPage);

        // Add pick progress stats per record
        $items = collect($results->items())->map(function ($inbound) {
            $details  = $inbound->details;
            $total    = $details->count();
            $picked   = $details->where('flag', 1)->count();
            $data     = $inbound->toArray();
            $data['items_total']  = $total;
            $data['items_picked'] = $picked;
            return $data;
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $results->currentPage(),
                'per_page'     => $results->perPage(),
                'total'        => $results->total(),
                'last_page'    => $results->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/inbound/{id}
     */
    public function show(int $id): JsonResponse
    {
        $inbound = Inbound::inboundOnly()->with(['category', 'details'])->findOrFail($id);
        return response()->json(['data' => $inbound]);
    }

    /**
     * GET /api/inbound/{id}/details
     */
    public function details(int $id): JsonResponse
    {
        $inbound = Inbound::inboundOnly()->findOrFail($id);
        $details = $inbound->details()->orderBy('id')->get();
        return response()->json(['data' => $details]);
    }

    /**
     * POST /api/inbound
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hawb'                => ['required', 'string', 'max:255', 'unique:el_inbound_header,hawb'],
            'descr'               => ['required', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'integer'],
            'modality'            => ['nullable', 'string'],
            'delivery_id'         => ['nullable', 'integer'],
            'qty'                 => ['nullable', 'string'],
            'po'                  => ['nullable', 'string'],
            'locator'             => ['nullable', 'string'],
            'etd'                 => ['nullable', 'date'],
            'eta'                 => ['nullable', 'date'],
            'ata'                 => ['nullable', 'date'],
            'status'              => ['nullable', 'string'],
        ]);

        $validated['from_shipment'] = 0;
        $validated['checker']       = $request->user()->full_name ?? 'system';
        $validated['created_by']    = $request->user()->user_id;
        $validated['status']        = $validated['status'] ?? 'inprogress';

        $inbound = Inbound::create($validated);

        return response()->json(['data' => $inbound], 201);
    }

    /**
     * PUT /api/inbound/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $inbound = Inbound::inboundOnly()->findOrFail($id);

        $validated = $request->validate([
            'hawb'                => ['sometimes', 'string', 'max:255', "unique:el_inbound_header,hawb,{$id}"],
            'descr'               => ['sometimes', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'integer'],
            'modality'            => ['nullable', 'string'],
            'delivery_id'         => ['nullable', 'integer'],
            'qty'                 => ['nullable', 'string'],
            'po'                  => ['nullable', 'string'],
            'locator'             => ['nullable', 'string'],
            'etd'                 => ['nullable', 'date'],
            'eta'                 => ['nullable', 'date'],
            'ata'                 => ['nullable', 'date'],
            'status'              => ['nullable', 'string'],
        ]);

        $validated['updated_by'] = $request->user()->user_id;
        $inbound->update($validated);

        return response()->json(['data' => $inbound->fresh()]);
    }

    /**
     * DELETE /api/inbound/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $inbound = Inbound::inboundOnly()->findOrFail($id);
        $inbound->delete();

        return response()->json(['message' => 'Inbound record deleted.']);
    }
}
