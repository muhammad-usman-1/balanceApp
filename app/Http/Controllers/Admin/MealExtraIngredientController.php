<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MealExtraIngredient;
use Illuminate\Http\Request;

class MealExtraIngredientController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'meal_extra_id' => 'required|exists:meal_extras,id',
            'name'          => 'required|string|max:255',
            'name_ar'       => 'nullable|string|max:255',
            'sort_order'    => 'nullable|integer|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        MealExtraIngredient::create($data);

        return redirect()->route('admin.meal-extras.edit', $data['meal_extra_id'])
            ->with('success', 'Option added.');
    }

    public function update(Request $request, MealExtraIngredient $mealExtraIngredient)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'name_ar'    => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $mealExtraIngredient->update($data);

        return redirect()->route('admin.meal-extras.edit', $mealExtraIngredient->meal_extra_id)
            ->with('success', 'Option updated.');
    }

    public function destroy(MealExtraIngredient $mealExtraIngredient)
    {
        $extraId = $mealExtraIngredient->meal_extra_id;
        $mealExtraIngredient->delete();

        return redirect()->route('admin.meal-extras.edit', $extraId)
            ->with('success', 'Option removed.');
    }
}
