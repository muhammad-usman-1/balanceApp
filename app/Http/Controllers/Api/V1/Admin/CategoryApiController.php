<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryApiController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        $category = Category::create($request->validate([
            'name' => 'required|string|unique:categories,name',
            'name_ar' => 'nullable|string',
        ]));
        return response()->json($category, Response::HTTP_CREATED);
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id,
            'name_ar' => 'nullable|string',
        ]));
        return response()->json($category, Response::HTTP_OK);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
