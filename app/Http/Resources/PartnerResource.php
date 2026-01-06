<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'link' => $this->link,
            'type' => $this->type,
            'is_visible' => $this->is_visible,
            'order' => $this->order,
            'logo_url' => $this->logo_path ? Storage::url($this->logo_path) : null,
        ];
    }
}