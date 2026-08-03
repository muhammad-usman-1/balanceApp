<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('areas')->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        // Only show areas not already assigned to any branch
        $assignedAreaIds = DB::table('area_branch')->pluck('area_id')->toArray();
        $areas = Area::where('status', 'active')
            ->whereNotIn('id', $assignedAreaIds)
            ->get();
        return view('admin.branches.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'name_ar'  => 'nullable|string|max:255',
            'areas'    => 'nullable|array',
            'areas.*'  => 'exists:areas,id',
            'status'   => 'required|in:active,inactive',
        ]);

        if ($request->filled('areas')) {
            $alreadyAssigned = DB::table('area_branch')
                ->whereIn('area_id', $request->areas)
                ->pluck('area_id')
                ->toArray();

            if (!empty($alreadyAssigned)) {
                $areaNames = Area::whereIn('id', $alreadyAssigned)->pluck('name')->implode(', ');
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['areas' => "These areas are already assigned to another branch: {$areaNames}"]);
            }
        }

        $branch = Branch::create($request->only(['name', 'name_ar', 'status']));

        if ($request->filled('areas')) {
            $branch->areas()->sync($request->areas);
        }

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully');
    }

    public function edit(Branch $branch)
    {
        $branch->load('areas');

        // Show areas not assigned to any other branch, plus areas already in this branch
        $assignedElsewhere = DB::table('area_branch')
            ->where('branch_id', '!=', $branch->id)
            ->pluck('area_id')
            ->toArray();

        $areas = Area::where('status', 'active')
            ->whereNotIn('id', $assignedElsewhere)
            ->get();

        return view('admin.branches.edit', compact('branch', 'areas'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'name_ar'  => 'nullable|string|max:255',
            'areas'    => 'nullable|array',
            'areas.*'  => 'exists:areas,id',
            'status'   => 'required|in:active,inactive',
        ]);

        if ($request->filled('areas')) {
            $alreadyAssigned = DB::table('area_branch')
                ->whereIn('area_id', $request->areas)
                ->where('branch_id', '!=', $branch->id)
                ->pluck('area_id')
                ->toArray();

            if (!empty($alreadyAssigned)) {
                $areaNames = Area::whereIn('id', $alreadyAssigned)->pluck('name')->implode(', ');
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['areas' => "These areas are already assigned to another branch: {$areaNames}"]);
            }
        }

        $branch->update($request->only(['name', 'name_ar', 'status']));

        if ($request->filled('areas')) {
            $branch->areas()->sync($request->areas);
        } else {
            $branch->areas()->detach();
        }

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully');
    }

    public function destroy(Branch $branch)
    {
        $branch->areas()->detach();
        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted successfully');
    }
}
