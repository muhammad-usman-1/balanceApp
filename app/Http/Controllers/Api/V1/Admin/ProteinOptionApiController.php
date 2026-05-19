<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProteinOptionResource;
use App\Models\ProteinOption;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProteinOptionApiController extends Controller
{
    // Public — app fetches this to show protein choices + live pricing
    public function index()
    {
        return ProteinOptionResource::collection(
            ProteinOption::where('is_active', true)->orderBy('protein_grams')->get()
        );
    }

    // Admin CRUD below

    public function all()
    {
        return ProteinOptionResource::collection(
            ProteinOption::orderBy('protein_grams')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'protein_grams'        => ['required', 'integer', 'min:1', 'unique:protein_options,protein_grams'],
            'extra_price_per_meal' => ['required', 'numeric', 'min:0'],
            'is_active'            => ['boolean'],
        ]);

        $option = ProteinOption::create($data);

        return (new ProteinOptionResource($option))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ProteinOption $proteinOption)
    {
        return new ProteinOptionResource($proteinOption);
    }

    public function update(Request $request, ProteinOption $proteinOption)
    {
        $data = $request->validate([
            'protein_grams'        => ['sometimes', 'integer', 'min:1', 'unique:protein_options,protein_grams,' . $proteinOption->id],
            'extra_price_per_meal' => ['sometimes', 'numeric', 'min:0'],
            'is_active'            => ['sometimes', 'boolean'],
        ]);

        $proteinOption->update($data);

        return (new ProteinOptionResource($proteinOption))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(ProteinOption $proteinOption)
    {
        $proteinOption->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
