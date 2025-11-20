<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class UserApiController extends Controller
{
    /**
     * Get all users
     */
    public function index()
    {
        $users = User::with('roles')->get();
        return UserResource::collection($users);
    }

    /**
     * Get a specific user
     */
    public function show(User $user)
    {
        $user->load('roles');
        return new UserResource($user);
    }

    /**
     * Get a user by mobile number
     */
    public function findByMobile($mobile)
    {
        $user = User::with('roles')
            ->where('mobile', $mobile)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new UserResource($user);
    }
}
