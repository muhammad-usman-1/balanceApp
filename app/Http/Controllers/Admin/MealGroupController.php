<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\MealGroup;
use Illuminate\Http\Request;

class MealGroupController extends Controller
{
    public function index()
    {
        $groups = MealGroup::withCount('meals')
            ->with(['meals' => fn ($q) => $q->with('media', 'category')->orderBy('title')])
            ->orderBy('name')
            ->get();

        $ungroupedMeals = Meal::whereNull('meal_group_id')->where('is_active', 1)
            ->with('media', 'category')
            ->orderBy('title')
            ->get();

        return view('admin.mealGroups.index', compact('groups', 'ungroupedMeals'));
    }

    public function create()
    {
        // Only meals not already in another group can be picked when creating a new group
        $meals = Meal::whereNull('meal_group_id')
            ->with('media', 'category')
            ->orderBy('title')
            ->get();

        return view('admin.mealGroups.create', compact('meals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'weekly_limit' => 'required|integer|min:1|max:7',
            'meal_ids'     => 'nullable|array',
            'meal_ids.*'   => 'exists:meals,id',
        ]);

        $group = MealGroup::create($request->only('name', 'weekly_limit'));

        if ($request->filled('meal_ids')) {
            Meal::whereIn('id', $request->meal_ids)->update(['meal_group_id' => $group->id]);
        }

        return redirect()->route('admin.meal-groups.index')
            ->with('success', 'Meal group created successfully.');
    }

    public function edit(MealGroup $mealGroup)
    {
        $mealGroup->load('meals');

        // Meals already in this group, plus any meal not currently assigned elsewhere
        $meals = Meal::where('meal_group_id', $mealGroup->id)
            ->orWhereNull('meal_group_id')
            ->with('media', 'category')
            ->orderBy('title')
            ->get();

        return view('admin.mealGroups.edit', compact('mealGroup', 'meals'));
    }

    public function update(Request $request, MealGroup $mealGroup)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'weekly_limit' => 'required|integer|min:1|max:7',
            'meal_ids'     => 'nullable|array',
            'meal_ids.*'   => 'exists:meals,id',
        ]);

        $mealGroup->update($request->only('name', 'weekly_limit'));

        $selectedIds = $request->input('meal_ids', []);

        // Remove meals that were unchecked
        Meal::where('meal_group_id', $mealGroup->id)
            ->whereNotIn('id', $selectedIds)
            ->update(['meal_group_id' => null]);

        // Assign newly checked meals
        if (! empty($selectedIds)) {
            Meal::whereIn('id', $selectedIds)->update(['meal_group_id' => $mealGroup->id]);
        }

        return redirect()->route('admin.meal-groups.index')
            ->with('success', 'Meal group updated successfully.');
    }

    public function destroy(MealGroup $mealGroup)
    {
        // Meals in this group become unrestricted again, not deleted
        Meal::where('meal_group_id', $mealGroup->id)->update(['meal_group_id' => null]);

        $mealGroup->delete();

        return redirect()->route('admin.meal-groups.index')
            ->with('success', 'Meal group removed.');
    }
}
