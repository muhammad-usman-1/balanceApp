<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MealExtra;
use Illuminate\Http\Request;

class MealExtraController extends Controller
{
    public function index()
    {
        $mealExtras = MealExtra::withCount('ingredients')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.mealExtras.index', compact('mealExtras'));
    }

    public function create()
    {
        return view('admin.mealExtras.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_required'] = $request->boolean('is_required');
        $data['is_active']   = $request->boolean('is_active');

        MealExtra::create($data);

        return redirect()->route('admin.meal-extras.index')
            ->with('success', 'Meal extra created successfully.');
    }

    public function edit(MealExtra $mealExtra)
    {
        $mealExtra->load('ingredients');

        return view('admin.mealExtras.edit', compact('mealExtra'));
    }

    public function update(Request $request, MealExtra $mealExtra)
    {
        $data = $this->validateData($request);
        $data['is_required'] = $request->boolean('is_required');
        $data['is_active']   = $request->boolean('is_active');

        $mealExtra->update($data);

        return redirect()->route('admin.meal-extras.edit', $mealExtra->id)
            ->with('success', 'Meal extra updated successfully.');
    }

    public function destroy(MealExtra $mealExtra)
    {
        $mealExtra->delete();

        return redirect()->route('admin.meal-extras.index')
            ->with('success', 'Meal extra removed.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'           => 'required|string|max:255',
            'name_ar'        => 'nullable|string|max:255',
            'selection_type' => 'required|in:single,multiple',
            'sort_order'     => 'nullable|integer|min:0',
        ]);
    }
}
