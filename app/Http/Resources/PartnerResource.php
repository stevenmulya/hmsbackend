<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'link' => $this->link,
            'type' => $this->type,
            'is_visible' => (bool) $this->is_visible,
            'order' => $this->order,
            'logo_url' => $this->logo_path ? url('storage/' . $this->logo_path) : null,
        ];
    }
}