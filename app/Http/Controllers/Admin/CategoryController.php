<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withTrashed()->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
            'name_ar' => 'nullable|string',
        ]);
        // New categories go to the end of the display order.
        $nextSortOrder = (Category::withTrashed()->max('sort_order') ?? -1) + 1;
        Category::create($request->only('name', 'name_ar') + ['sort_order' => $nextSortOrder]);
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully');
    }

    /**
     * Persist a new display order from dragging rows in the admin table.
     * This order is exactly what the app shows customers (category tabs,
     * meals/categories dropdown, etc.) — see CategoryApiController@index
     * and MealApiController@categories.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => 'required|array|min:1',
            'order.*' => 'integer|exists:categories,id',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $index => $id) {
                Category::where('id', $id)->update(['sort_order' => $index]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Category order updated.']);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id,
            'name_ar' => 'nullable|string',
        ]);
        $category->update($request->only('name', 'name_ar'));
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        // Check if any meals are using this category
        $mealsCount = \App\Models\Meal::where('category_id', $category->id)->count();
        
        if ($mealsCount > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', "Cannot delete category. It is being used by {$mealsCount} meal(s). Please update the meals first.");
        }
        
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully');
    }
    
    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();
        return redirect()->route('admin.categories.index')->with('success', 'Category restored successfully');
    }
    
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        
        // Set category_id to null for all meals using this category
        \App\Models\Meal::where('category_id', $category->id)->update(['category_id' => null]);
        
        $category->forceDelete();
        return redirect()->route('admin.categories.index')->with('success', 'Category permanently deleted successfully');
    }
}
