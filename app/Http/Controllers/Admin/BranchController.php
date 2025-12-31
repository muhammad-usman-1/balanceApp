<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Area;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withTrashed()->with('areas')->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        $areas = Area::where('status', 'active')->get();
        return view('admin.branches.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'areas' => 'nullable|array',
            'areas.*' => 'exists:areas,id',
            'status' => 'required|in:active,inactive',
        ]);

        $branch = Branch::create($request->only(['name', 'status']));

        if ($request->has('areas')) {
            $branch->areas()->sync($request->areas);
        }

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully');
    }

    public function edit(Branch $branch)
    {
        $areas = Area::where('status', 'active')->get();
        $branch->load('areas');
        return view('admin.branches.edit', compact('branch', 'areas'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'areas' => 'nullable|array',
            'areas.*' => 'exists:areas,id',
            'status' => 'required|in:active,inactive',
        ]);

        $branch->update($request->only(['name', 'status']));

        if ($request->has('areas')) {
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

