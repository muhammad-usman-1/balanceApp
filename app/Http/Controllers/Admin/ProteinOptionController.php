<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProteinOption;
use Illuminate\Http\Request;

class ProteinOptionController extends Controller
{
    public function index()
    {
        $proteinOptions = ProteinOption::orderBy('protein_grams')->get();
        return view('admin.proteinOptions.index', compact('proteinOptions'));
    }

    public function create()
    {
        return view('admin.proteinOptions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'protein_grams'        => 'required|integer|min:1|unique:protein_options,protein_grams',
            'extra_price_per_meal' => 'required|numeric|min:0',
            'is_active'            => 'nullable|boolean',
        ]);

        ProteinOption::create([
            'protein_grams'        => $request->protein_grams,
            'extra_price_per_meal' => $request->extra_price_per_meal,
            'is_active'            => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.protein-options.index')
            ->with('success', 'Protein option created successfully.');
    }

    public function edit(ProteinOption $proteinOption)
    {
        return view('admin.proteinOptions.edit', compact('proteinOption'));
    }

    public function update(Request $request, ProteinOption $proteinOption)
    {
        $request->validate([
            'protein_grams'        => 'required|integer|min:1|unique:protein_options,protein_grams,' . $proteinOption->id,
            'extra_price_per_meal' => 'required|numeric|min:0',
            'is_active'            => 'nullable|boolean',
        ]);

        $proteinOption->update([
            'protein_grams'        => $request->protein_grams,
            'extra_price_per_meal' => $request->extra_price_per_meal,
            'is_active'            => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.protein-options.index')
            ->with('success', 'Protein option updated successfully.');
    }

    public function destroy(ProteinOption $proteinOption)
    {
        $proteinOption->delete();

        return redirect()->route('admin.protein-options.index')
            ->with('success', 'Protein option deleted successfully.');
    }
}
