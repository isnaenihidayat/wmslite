<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ProductCategory;
use App\Models\Recipient;
use Illuminate\Http\JsonResponse;

class MasterController extends Controller
{
    /**
     * GET /api/master/locations
     */
    public function locations(): JsonResponse
    {
        $locations = Location::orderBy('loc_name')->get(['loc_id', 'loc_name', 'loc_descr']);
        return response()->json(['data' => $locations]);
    }

    /**
     * GET /api/master/categories
     */
    public function categories(): JsonResponse
    {
        $categories = ProductCategory::orderBy('name')->get(['id', 'name']);
        return response()->json(['data' => $categories]);
    }

    /**
     * GET /api/master/recipients
     */
    public function recipients(): JsonResponse
    {
        $recipients = Recipient::orderBy('name')->get(['id', 'name', 'email', 'created_at']);
        return response()->json(['data' => $recipients]);
    }
}
