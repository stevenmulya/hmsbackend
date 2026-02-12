<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'platform' => $this->platform,
            'url' => $this->url,
            'is_visible' => (bool) $this->is_visible,
            'order' => $this->order,
            // PERBAIKAN: Menggunakan url('storage/' . ...) agar link lengkap
            'logo_url' => $this->logo_path ? url('storage/' . $this->logo_path) : null,
        ];
    }
}