<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::all();
        return view('admin.areas.index', compact('areas'));
    }

    public function create()
    {
        return view('admin.areas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'delivery_charges' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        Area::create($request->only(['name', 'name_ar', 'delivery_charges', 'status']));

        return redirect()->route('admin.areas.index')->with('success', 'Area created successfully');
    }

    public function edit(Area $area)
    {
        return view('admin.areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'delivery_charges' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $area->update($request->only(['name', 'name_ar', 'delivery_charges', 'status']));

        return redirect()->route('admin.areas.index')->with('success', 'Area updated successfully');
    }

    public function destroy(Area $area)
    {
        // Check if area is associated with any branches
        $branchesCount = $area->branches()->count();
        
        if ($branchesCount > 0) {
            return redirect()->route('admin.areas.index')
                ->with('error', "Cannot delete area. It is associated with {$branchesCount} branch(es). Please remove the associations first.");
        }
        
        $area->delete();
        return redirect()->route('admin.areas.index')->with('success', 'Area deleted successfully');
    }
}

