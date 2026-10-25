<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Http\Resources\Admin\MealListResource;
use App\Http\Resources\Admin\MealResource;
use App\Models\Meal;
use App\Support\MealMenuCache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MealApiController extends Controller
{
    use MediaUploadingTrait;

    // Endpoint to get all categories for meal creation dropdown
    public function categories()
    {
        $categories = \App\Models\Category::orderBy('sort_order')->orderBy('id')->get(['id', 'name', 'name_ar']);
        return response()->json(['categories' => $categories]);
    }

    /**
     * Menu list — active meals only, served from cache (see MealMenuCache).
     * Supports ETag / Last-Modified (304 when unchanged) and gzip.
     */
    public function index(Request $request)
    {
        $menu = MealMenuCache::remember(function () {
            $meals = Meal::with(['category', 'media', 'mealGroup', 'mealExtras', 'availableIngredients'])
                ->where('is_active', 1)
                ->get();

            return MealListResource::collection($meals)->response()->getContent();
        });

        $response = response('', Response::HTTP_OK, [
            'Content-Type' => 'application/json',
            'Vary'         => 'Accept-Encoding',
        ]);

        // Clients must revalidate each time, but get an empty 304 when nothing changed.
        $response->setCache([
            'public'          => true,
            'max_age'         => 0,
            'must_revalidate' => true,
            'etag'            => $menu['etag'],
            'last_modified'   => \Carbon\Carbon::createFromTimestamp($menu['generated_at']),
        ]);

        if ($response->isNotModified($request)) {
            return $response;
        }

        // Compress here so it works even if the web server doesn't (nginx won't re-compress).
        if ($menu['gzip'] !== null && str_contains((string) $request->header('Accept-Encoding'), 'gzip')) {
            $response->setContent($menu['gzip']);
            $response->headers->set('Content-Encoding', 'gzip');
        } else {
            $response->setContent($menu['json']);
        }

        return $response;
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
