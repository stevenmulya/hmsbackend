<?php
// --- Quotation API Resource (Final Version) ---
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'is_seen_by_admin' => $this->is_seen_by_admin,
            'notes' => $this->notes,
            'date' => $this->created_at->format('d F Y'),
            'hour' => $this->created_at->format('H:i'),
            
            // --- THE FIX IS HERE: Eager load customer data for efficiency ---
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            'total_items' => $this->whenLoaded('items', $this->items->count()),
        ];
    }
}