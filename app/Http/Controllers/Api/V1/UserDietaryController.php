<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserDietaryController extends Controller
{
    // ── ALLERGIES ──────────────────────────────────────────────

    public function getAllergies(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'has_food_allergies' => (bool) $user->has_food_allergies,
                'allergies'          => $user->allergies ?? [],
            ],
        ], Response::HTTP_OK);
    }

    public function updateAllergies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'has_food_allergies' => 'required|boolean',
            'allergies'          => 'nullable|array',
            'allergies.*'        => 'string|max:255',
        ]);

        if (! $validated['has_food_allergies']) {
            $validated['allergies'] = null;
        }

        $user = Auth::user();
        $user->update([
            'has_food_allergies' => $validated['has_food_allergies'],
            'allergies'          => $validated['allergies'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Allergies updated successfully.',
            'data' => [
                'has_food_allergies' => (bool) $user->has_food_allergies,
                'allergies'          => $user->allergies ?? [],
            ],
        ], Response::HTTP_OK);
    }

    // ── DISLIKES ──────────────────────────────────────────────

    public function getDislikes(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'dislikes' => $user->dislikes ?? [],
            ],
        ], Response::HTTP_OK);
    }

    public function updateDislikes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dislikes'   => 'required|array',
            'dislikes.*' => 'string|max:255',
        ]);

        $user = Auth::user();
        $user->update(['dislikes' => $validated['dislikes']]);

        return response()->json([
            'success' => true,
            'message' => 'Dislikes updated successfully.',
            'data' => [
                'dislikes' => $user->fresh()->dislikes ?? [],
            ],
        ], Response::HTTP_OK);
    }

    public function clearDislikes(): JsonResponse
    {
        Auth::user()->update(['dislikes' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Dislikes cleared.',
            'data'    => ['dislikes' => []],
        ], Response::HTTP_OK);
    }
}
