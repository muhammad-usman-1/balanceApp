<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserProfileController extends Controller
{
    /**
     * GET /api/v1/profile
     * Return the authenticated user's profile.
     */
    public function show(): JsonResponse
    {
        $user = Auth::user()->load(['roles', 'addresses']);

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
        ], Response::HTTP_OK);
    }

    /**
     * PUT /api/v1/profile
     * Update the authenticated user's profile fields.
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'email'              => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'gender'             => 'sometimes|string|in:male,female,other',
            'dob'                => 'sometimes|date_format:Y-m-d',
            'height'             => 'sometimes|numeric|min:0',
            'weight'             => 'sometimes|numeric|min:0',
            'goal'               => ['sometimes', 'string', Rule::in(['eat_healthy', 'lose_weight', 'gain_weight', 'build_muscle', 'maintain_weight'])],
            'activity_level'     => ['sometimes', 'string', Rule::in(['sedentary', 'lightly_active', 'very_active', 'highly_active'])],
            'has_food_allergies' => 'sometimes|boolean',
            'allergies'          => 'sometimes|nullable|array',
            'allergies.*'        => 'string|max:255',
        ]);

        // If allergies are cleared, nullify the list
        if (isset($validated['has_food_allergies']) && ! $validated['has_food_allergies']) {
            $validated['allergies'] = null;
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => new UserResource($user->fresh()->load('roles')),
        ], Response::HTTP_OK);
    }
}
