<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Http\Resources\Admin\MealResource;
use App\Models\Meal;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MealApiController extends Controller
{
    use MediaUploadingTrait;

    // Endpoint to get all categories for meal creation dropdown
    public function categories()
    {
        $categories = \App\Models\Category::all(['id', 'name', 'name_ar']);
        return response()->json(['categories' => $categories]);
    }

    public function index()
    {
        $meals = Meal::with(['category', 'media', 'mealGroup', 'mealExtras', 'availableIngredients'])->get();
        return MealResource::collection($meals);
    }

    public function store(StoreMealRequest $request)
    {
        $meal = Meal::create($request->all());
        
        if ($request->input('image', false)) {
            $meal->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))->toMediaCollection('image', 'meals');
        }
        
        $meal->load(['category', 'media', 'mealGroup', 'mealExtras', 'availableIngredients']);

        return (new MealResource($meal))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Meal $meal)
    {
        $meal->load(['category', 'media', 'mealGroup', 'mealExtras', 'availableIngredients']);
        return new MealResource($meal);
    }

    public function update(UpdateMealRequest $request, Meal $meal)
    {
        $meal->update($request->all());
        $meal->load(['category', 'media', 'mealGroup', 'mealExtras', 'availableIngredients']);

        if ($request->input('image', false)) {
            if (! $meal->image || $request->input('image') !== $meal->image->file_name) {
                if ($meal->image) {
                    $meal->image->delete();
                }
                $meal->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))->toMediaCollection('image', 'meals');
            }
        } elseif ($meal->image) {
            $meal->image->delete();
        }

        return (new MealResource($meal))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Meal $meal)
    {
        $meal->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
