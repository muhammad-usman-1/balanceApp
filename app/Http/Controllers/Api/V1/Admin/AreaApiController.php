<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\JsonResponse;

class AreaApiController extends Controller
{
    /**
     * Return all active areas with their branch auto-resolved.
     *
     * Mobile shows this list to the user. The user picks an area only.
     * The branch is embedded in each area — mobile does NOT need a separate branch call.
     * Send area_id in checkout; the server stores branch_id automatically.
     *
     * GET /api/v1/areas
     */
    public function index(): JsonResponse
    {
        $areas = Area::where('status', 'active')
            ->with([
                'branches' => fn ($q) => $q->where('status', 'active')->select('branches.id', 'branches.name', 'branches.name_ar'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'name_ar', 'delivery_charges', 'status']);

        $data = $areas->map(function (Area $area) {
            // Each area is linked to one branch in this system
            $branch = $area->branches->first();

            return [
                'id'               => $area->id,
                'name'             => $area->name,
                'name_ar'          => $area->name_ar,
                'delivery_charges' => (float) $area->delivery_charges,
                'branch_id'        => $branch?->id,
                'branch_name'      => $branch?->name,
                'branch_name_ar'   => $branch?->name_ar,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data->values(),
        ]);
    }
}
