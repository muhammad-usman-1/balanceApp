<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliatedCode;
use Illuminate\Http\Request;

class AffiliatedCodeController extends Controller
{
    public function index()
    {
        $affiliatedCodes = AffiliatedCode::withCount('users')->orderBy('created_at', 'desc')->get();
        return view('admin.affiliatedCodes.index', compact('affiliatedCodes'));
    }

    public function create()
    {
        return view('admin.affiliatedCodes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:affiliated_codes,code',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // If code is not provided, auto-generate it
        if (empty($request->code)) {
            $code = AffiliatedCode::generateCode($request->full_name);
        } else {
            $code = strtoupper($request->code);
        }

        AffiliatedCode::create([
            'full_name' => $request->full_name,
            'code' => $code,
            'gift_type' => 'percentage', // Dummy value - will be handled properly later
            'gift_value' => 0, // Dummy value - will be handled properly later
            'is_active' => $request->has('is_active') && $request->is_active == '1' ? true : false,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.affiliated-codes.index')->with('success', 'Affiliated code created successfully.');
    }

    public function show(AffiliatedCode $affiliatedCode)
    {
        $affiliatedCode->load('users');
        return view('admin.affiliatedCodes.show', compact('affiliatedCode'));
    }

    public function logs(AffiliatedCode $affiliatedCode)
    {
        $affiliatedCode->load(['users' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);
        return view('admin.affiliatedCodes.logs', compact('affiliatedCode'));
    }

    public function edit(AffiliatedCode $affiliatedCode)
    {
        return view('admin.affiliatedCodes.edit', compact('affiliatedCode'));
    }

    public function update(Request $request, AffiliatedCode $affiliatedCode)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:affiliated_codes,code,' . $affiliatedCode->id,
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $affiliatedCode->update([
            'full_name' => $request->full_name,
            'code' => strtoupper($request->code),
            // Keep existing gift_type and gift_value (dummy values) - will be handled properly later
            'is_active' => $request->has('is_active') && $request->is_active == '1' ? true : false,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.affiliated-codes.index')->with('success', 'Affiliated code updated successfully.');
    }

    public function destroy(AffiliatedCode $affiliatedCode)
    {
        // Check if code has been used
        if ($affiliatedCode->usage_count > 0) {
            return redirect()->route('admin.affiliated-codes.index')
                ->with('error', "Cannot delete affiliated code. It has been used by {$affiliatedCode->usage_count} user(s).");
        }

        $affiliatedCode->delete();
        return redirect()->route('admin.affiliated-codes.index')->with('success', 'Affiliated code deleted successfully.');
    }

    /**
     * Auto-generate code based on full name
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateCode(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
        ]);

        $code = AffiliatedCode::generateCode($request->full_name);

        return response()->json([
            'success' => true,
            'code' => $code,
        ]);
    }
}
