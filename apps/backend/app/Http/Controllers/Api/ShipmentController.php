<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbound;
use App\Policies\ShipmentPolicy;
use Illuminate\Auth\Access\AuthorizationException;
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

        $this->authorizeShipment('update', $request);

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
    public function destroy(Request $request, int $id): JsonResponse
    {
        $shipment = Inbound::shipments()->findOrFail($id);

        $this->authorizeShipment('delete', $request);

        $shipment->delete();

        return response()->json(['message' => 'Shipment deleted successfully.']);
    }

    /**
     * POST /api/shipments/{id}/push-inbound
     *
     * Transisikan Shipment menjadi Inbound secara in-place: row yang sama
     * diubah dari from_shipment=1 menjadi from_shipment=0, tidak membuat row
     * baru. Tidak ada lagi konflik HAWB karena hanya ada satu row per HAWB.
     */
    public function pushInbound(Request $request, int $id): JsonResponse
    {
        $shipment = Inbound::shipments()->with('details')->findOrFail($id);

        $shipment->update([
            'from_shipment' => 0,
            'status'        => 'inprogress',
            'updated_by'    => $request->user()->user_id,
        ]);

        return response()->json([
            'message'    => "Shipment '{$shipment->hawb}' berhasil di-push ke Inbound.",
            'inbound_id' => $shipment->id,
            'data'       => $shipment->fresh()->load('category'),
        ], 201);
    }

    /**
     * Authorize against ShipmentPolicy directly (not via the Gate facade).
     *
     * ShipmentController operates on the same Inbound::class model as
     * InboundController, and Gate::policy() is keyed by model class —
     * Inbound::class is already bound to InboundPolicy in
     * AppServiceProvider::boot(). Routing this through $this->authorize()
     * (which resolves via the Gate) would invoke InboundPolicy instead of
     * ShipmentPolicy. Since both Policies currently enforce the identical
     * admin-only rule, this is not a behavioral risk today, but it would
     * silently mask a future divergence between the two rules. Calling
     * ShipmentPolicy's method directly keeps the two Policies independently
     * meaningful. Throwing AuthorizationException manually reproduces the
     * exact 403 response shape Gate-based authorization would have produced
     * (bootstrap/app.php already renders all api/* exceptions as JSON).
     */
    protected function authorizeShipment(string $ability, Request $request): void
    {
        if (! app(ShipmentPolicy::class)->{$ability}($request->user())) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
