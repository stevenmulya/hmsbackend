<?php
// --- Quotation Item API Resource ---
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price_at_time_of_quotation' => $this->price,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}