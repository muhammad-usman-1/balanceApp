<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'gender' => $this->gender,
            'height' => $this->height,
            'weight' => $this->weight,
            'dob' => $this->dob,
            'goal' => $this->goal,
            'activity_level' => $this->activity_level,
            'has_food_allergies' => (bool) $this->has_food_allergies,
            'allergies' => $this->allergies ?? [],
            'otp' => $this->otp,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'title' => $role->title,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
