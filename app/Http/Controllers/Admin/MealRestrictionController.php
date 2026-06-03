<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\MealRestriction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MealRestrictionController extends Controller
{
    public function index()
    {
        $restrictions = MealRestriction::with('meal')->orderByDesc('id')->get();
        $meals = Meal::whereDoesntHave('restriction')->where('is_active', 1)->orderBy('title')->get();

        return view('admin.mealRestrictions.index', compact('restrictions', 'meals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'meal_id'      => 'required|integer|exists:meals,id|unique:meal_restrictions,meal_id',
            'weekly_limit' => 'required|integer|min:1|max:7',
        ], [
            'meal_id.unique' => 'This meal already has a restriction.',
        ]);

        MealRestriction::create($request->only('meal_id', 'weekly_limit'));

        return redirect()->route('admin.meal-restrictions.index')
            ->with('success', 'Meal restriction added successfully.');
    }

    public function update(Request $request, MealRestriction $mealRestriction)
    {
        $request->validate([
            'weekly_limit' => 'required|integer|min:1|max:7',
        ]);

        $mealRestriction->update(['weekly_limit' => $request->weekly_limit]);

        return redirect()->route('admin.meal-restrictions.index')
            ->with('success', 'Restriction updated successfully.');
    }

    public function destroy(MealRestriction $mealRestriction)
    {
        $mealRestriction->delete();

        return redirect()->route('admin.meal-restrictions.index')
            ->with('success', 'Restriction removed.');
    }
}
