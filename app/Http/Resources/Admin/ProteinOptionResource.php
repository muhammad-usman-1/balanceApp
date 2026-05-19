<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ProteinOptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'protein_grams'        => $this->protein_grams,
            'extra_price_per_meal' => $this->extra_price_per_meal,
            'is_active'            => $this->is_active,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
