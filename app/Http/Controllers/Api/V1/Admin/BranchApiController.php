<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;

class BranchApiController extends Controller
{
    /**
     * List all active, non-deleted branches.
     * GET /api/v1/branches
     */
    public function index()
    {
        $branches = Branch::where('status', 'active')
            ->get(['id', 'name', 'status']);

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * List active areas belonging to a branch.
     * GET /api/v1/branches/{branch}/areas
     */
    public function areas(Branch $branch)
    {
        if ($branch->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found or inactive.',
            ], 404);
        }

        $areas = $branch->areas()
            ->where('areas.status', 'active')
            ->get(['areas.id', 'areas.name', 'areas.delivery_charges', 'areas.status']);

        return response()->json([
            'success' => true,
            'data' => $areas,
        ]);
    }
}
