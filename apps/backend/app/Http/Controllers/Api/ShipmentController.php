<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * GET /api/shipments
     * List shipments (inbound records with from_shipment = 1)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Inbound::shipments()->with('category');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hawb', 'like', "%{$search}%")
                  ->orWhere('descr', 'like', "%{$search}%")
                  ->orWhere('po', 'like', "%{$search}%");
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
        $allowedSorts = ['id', 'hawb', 'descr', 'status', 'date_created', 'etd', 'eta'];
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
     * GET /api/shipments/{id}
     */
    public function show(int $id): JsonResponse
    {
        $shipment = Inbound::shipments()->with(['category', 'details'])->findOrFail($id);
        return response()->json(['data' => $shipment]);
    }

    /**
     * POST /api/shipments
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

        $validated['from_shipment'] = 1;
        $validated['checker']       = $request->user()->full_name ?? 'system';
        $validated['created_by']    = $request->user()->user_id;
        $validated['status']        = $validated['status'] ?? 'inprogress';

        $shipment = Inbound::create($validated);

        return response()->json(['data' => $shipment], 201);
    }

    /**
     * PUT /api/shipments/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $shipment = Inbound::shipments()->findOrFail($id);

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

        $shipment->update($validated);

        return response()->json(['data' => $shipment->fresh()]);
    }

    /**
     * DELETE /api/shipments/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $shipment = Inbound::shipments()->findOrFail($id);
        $shipment->delete();

        return response()->json(['message' => 'Shipment deleted successfully.']);
    }

    /**
     * POST /api/shipments/{id}/push-inbound
     *
     * Salin Shipment → Inbound baru (from_shipment = 0).
     * Jika HAWB sudah ada di inbound, return error 409.
     */
    public function pushInbound(Request $request, int $id): JsonResponse
    {
        $shipment = Inbound::shipments()->with('details')->findOrFail($id);

        // Cek apakah inbound dengan HAWB yang sama sudah ada
        $existingInbound = Inbound::inboundOnly()
            ->where('hawb', $shipment->hawb)
            ->first();

        if ($existingInbound) {
            return response()->json([
                'message' => "Inbound dengan HAWB '{$shipment->hawb}' sudah ada (ID: {$existingInbound->id}).",
                'inbound_id' => $existingInbound->id,
            ], 409);
        }

        // Salin semua field dari shipment ke inbound baru
        $inbound = Inbound::create([
            'hawb'                => $shipment->hawb,
            'descr'               => $shipment->descr,
            'product_category_id' => $shipment->product_category_id,
            'modality'            => $shipment->modality,
            'delivery_id'         => $shipment->delivery_id,
            'qty'                 => $shipment->qty,
            'po'                  => $shipment->po,
            'locator'             => $shipment->locator,
            'etd'                 => $shipment->etd,
            'eta'                 => $shipment->eta,
            'ata'                 => $shipment->ata,
            'status'              => 'inprogress',
            'from_shipment'       => 0,
            'checker'             => $request->user()->full_name ?? 'system',
            'created_by'          => $request->user()->user_id,
        ]);

        return response()->json([
            'message'    => "Shipment '{$shipment->hawb}' berhasil di-push ke Inbound.",
            'inbound_id' => $inbound->id,
            'data'       => $inbound->load('category'),
        ], 201);
    }
}
