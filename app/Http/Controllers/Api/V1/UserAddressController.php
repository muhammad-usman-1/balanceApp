<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTimeSlot;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserAddressController extends Controller
{
    /**
     * GET /api/v1/addresses
     * List all addresses for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $addresses = UserAddress::where('user_id', Auth::id())
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($a) => $this->formatAddress($a));

        return response()->json([
            'success' => true,
            'data'    => $addresses,
        ], Response::HTTP_OK);
    }

    /**
     * GET /api/v1/addresses/{id}
     * Get a single address owned by the authenticated user.
     */
    public function show($id): JsonResponse
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatAddress($address),
        ], Response::HTTP_OK);
    }

    /**
     * POST /api/v1/addresses
     * Create a new address for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $address = UserAddress::create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        if ($address->is_primary) {
            $this->clearOtherPrimary($address->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully.',
            'data'    => $this->formatAddress($address->fresh()),
        ], Response::HTTP_CREATED);
    }

    /**
     * PUT /api/v1/addresses/{id}
     * Update an existing address owned by the authenticated user.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate($this->rules(required: false));

        $address->update($validated);

        if ($address->is_primary) {
            $this->clearOtherPrimary($address->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'data'    => $this->formatAddress($address->fresh()),
        ], Response::HTTP_OK);
    }

    /**
     * DELETE /api/v1/addresses/{id}
     * Delete an address owned by the authenticated user.
     */
    public function destroy($id): JsonResponse
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ], Response::HTTP_OK);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function rules(bool $required = true): array
    {
        $req = $required ? 'required' : 'sometimes';

        return [
            'first_name'               => "{$req}|string|max:255",
            'last_name'                => 'nullable|string|max:255',
            'area'                     => 'nullable|string|max:255',
            'block_number'             => 'nullable|string|max:255',
            'street'                   => 'nullable|string|max:255',
            'house_building'           => 'nullable|string|max:255',
            'floor_apartment'          => 'nullable|string|max:255',
            'phone_number'             => "{$req}|string|max:20",
            'remarks'                  => 'nullable|string|max:1000',
            'delivery_notes'           => 'nullable|string|max:1000',
            'category'                 => ["{$req}", 'string', Rule::in(['home', 'office'])],
            'is_primary'               => 'nullable|boolean',
            'preferred_delivery_slot'  => ["{$req}", 'string', Rule::in(DeliveryTimeSlot::where('is_active', true)->pluck('value')->all())],
        ];
    }

    private function clearOtherPrimary(int $exceptId): void
    {
        UserAddress::where('user_id', Auth::id())
            ->where('id', '!=', $exceptId)
            ->update(['is_primary' => false]);
    }

    private function formatAddress(UserAddress $a): array
    {
        return [
            'id'                      => $a->id,
            'first_name'              => $a->first_name,
            'last_name'               => $a->last_name,
            'area'                    => $a->area,
            'block_number'            => $a->block_number,
            'street'                  => $a->street,
            'house_building'          => $a->house_building,
            'floor_apartment'         => $a->floor_apartment,
            'phone_number'            => $a->phone_number,
            'remarks'                 => $a->remarks,
            'delivery_notes'          => $a->delivery_notes,
            'category'                => $a->category,
            'is_primary'              => (bool) $a->is_primary,
            'preferred_delivery_slot' => $a->preferred_delivery_slot,
            'created_at'              => $a->created_at?->format('Y-m-d H:i:s'),
            'updated_at'              => $a->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
