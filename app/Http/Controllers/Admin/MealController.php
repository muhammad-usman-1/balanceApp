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

        return view('admin.meals.create', compact('categories'));
    }

    public function store(StoreMealRequest $request)
    {
        $meal = Meal::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
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

        return redirect()->route('admin.meals.index');
    }

    public function edit(Meal $meal)
    {
        abort_if(Gate::denies('meal_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $meal->load('category');
        $categories = \App\Models\Category::all();

        return view('admin.meals.edit', compact('meal', 'categories'));
    }

    public function update(UpdateMealRequest $request, Meal $meal)
    {
        $meal->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
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

        return redirect()->route('admin.meals.index');
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
