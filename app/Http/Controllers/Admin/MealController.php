<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyMealRequest;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Models\Meal;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class MealController extends Controller
{
    use MediaUploadingTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('meal_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $search = trim($request->get('search', ''));

        $meals = Meal::with(['media', 'category'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('title', 'like', '%' . $search . '%')
                       ->orWhereHas('category', fn($q3) => $q3->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.meals.index', compact('meals', 'search'));
    }

    public function create()
    {
        abort_if(Gate::denies('meal_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $categories = \App\Models\Category::all();
        $mealExtras = \App\Models\MealExtra::where('is_active', true)
            ->with('activeIngredients')
            ->orderBy('sort_order')->orderBy('name')->get();

        $selectedExtraIds      = old('meal_extras', []);
        $selectedIngredientIds = old('extra_ingredients', []);
        $selectedMaxSelect     = old('max_select', []);

        return view('admin.meals.create', compact('categories', 'mealExtras', 'selectedExtraIds', 'selectedIngredientIds', 'selectedMaxSelect'));
    }

    public function store(StoreMealRequest $request)
    {
        $meal = Meal::create([
            'title' => $request->input('title'),
            'title_ar' => $request->input('title_ar'),
            'description' => $request->input('description'),
            'description_ar' => $request->input('description_ar'),
            'category_id' => $request->input('category_id'),
            'calories' => $request->input('calories'),
            'protein_g' => $request->input('protein_g'),
            'fat_g' => $request->input('fat_g'),
            'carbs_g' => $request->input('carbs_g'),
            'extras' => $request->input('extras'),
            'is_active' => $request->input('is_active'),
            'type' => $request->input('type'),
        ]);

        if ($request->hasFile('image')) {
            $meal->addMediaFromRequest('image')
                ->usingName('meal_' . $meal->id)
                ->usingFileName('meal_' . $meal->id . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension())
                ->toMediaCollection('image', 'meals');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $meal->id]);
        }

        $this->syncMealExtras($meal, $request);

        return redirect()->route('admin.meals.index');
    }

    public function edit(Meal $meal)
    {
        abort_if(Gate::denies('meal_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $meal->load('category', 'mealExtras', 'availableIngredients');
        $categories = \App\Models\Category::all();
        $mealExtras = \App\Models\MealExtra::where('is_active', true)
            ->with('activeIngredients')
            ->orderBy('sort_order')->orderBy('name')->get();

        $selectedExtraIds      = old('meal_extras', $meal->mealExtras->pluck('id')->all());
        $selectedIngredientIds = old('extra_ingredients', $meal->availableIngredients->pluck('id')->all());
        $selectedMaxSelect     = old('max_select', $meal->mealExtras->mapWithKeys(fn ($e) => [$e->id => $e->pivot->max_select])->all());

        return view('admin.meals.edit', compact('meal', 'categories', 'mealExtras', 'selectedExtraIds', 'selectedIngredientIds', 'selectedMaxSelect'));
    }

    public function update(UpdateMealRequest $request, Meal $meal)
    {
        $meal->update([
            'title' => $request->input('title'),
            'title_ar' => $request->input('title_ar'),
            'description' => $request->input('description'),
            'description_ar' => $request->input('description_ar'),
            'category_id' => $request->input('category_id'),
            'calories' => $request->input('calories'),
            'protein_g' => $request->input('protein_g'),
            'fat_g' => $request->input('fat_g'),
            'carbs_g' => $request->input('carbs_g'),
            'extras' => $request->input('extras'),
            'is_active' => $request->input('is_active'),
            'type' => $request->input('type'),
        ]);

        if ($request->hasFile('image')) {
            if ($meal->image) {
                $meal->image->delete();
            }
            $meal->addMediaFromRequest('image')
                ->usingName('meal_' . $meal->id)
                ->usingFileName('meal_' . $meal->id . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension())
                ->toMediaCollection('image', 'meals');
        }

        $this->syncMealExtras($meal, $request);

        return redirect()->route('admin.meals.index');
    }

    /**
     * Sync a meal's available extras and the enabled ingredient options.
     * Ingredients are kept only when their parent extra is enabled for the meal.
     */
    private function syncMealExtras(Meal $meal, Request $request): void
    {
        $extraIds      = array_filter((array) $request->input('meal_extras', []));
        $ingredientIds = array_filter((array) $request->input('extra_ingredients', []));
        $maxInput      = (array) $request->input('max_select', []);

        // Build pivot data per extra. max_select only applies to "multiple" extras;
        // "single" extras are always capped at 1 (stored as NULL, interpreted as 1).
        $extras = \App\Models\MealExtra::whereIn('id', $extraIds)->get()->keyBy('id');
        $pivotData = [];
        foreach ($extraIds as $extraId) {
            $extra   = $extras->get($extraId);
            $max     = null;
            if ($extra && $extra->selection_type === 'multiple') {
                $val = isset($maxInput[$extraId]) ? (int) $maxInput[$extraId] : 0;
                $max = $val > 0 ? $val : null; // null = no cap
            }
            $pivotData[$extraId] = ['max_select' => $max];
        }

        $meal->mealExtras()->sync($pivotData);

        $validIngredientIds = empty($ingredientIds) ? [] : \App\Models\MealExtraIngredient::whereIn('id', $ingredientIds)
            ->whereIn('meal_extra_id', $extraIds ?: [0])
            ->pluck('id')
            ->all();

        $meal->availableIngredients()->sync($validIngredientIds);
    }

    public function show(Meal $meal)
    {
        abort_if(Gate::denies('meal_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.meals.show', compact('meal'));
    }

    public function destroy(Meal $meal)
    {
        abort_if(Gate::denies('meal_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $meal->delete();

        return back();
    }

    public function massDestroy(MassDestroyMealRequest $request)
    {
        $meals = Meal::find(request('ids'));

        foreach ($meals as $meal) {
            $meal->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('meal_create') && Gate::denies('meal_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Meal();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
