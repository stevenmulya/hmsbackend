<?php
// --- Customer API Resource (Final Version with All Relations) ---
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'email' => $this->email,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'customer_username' => $this->customer_username,
            'customer_role' => $this->customer_role,
            'marketing_name' => $this->marketing_name,
            'transaction_history' => $this->transaction_history,
            'is_verified' => !is_null($this->phone_verified_at),
            'verified_at' => $this->phone_verified_at ? $this->phone_verified_at->format('d F Y, H:i') : null,
            'company_details' => $this->when($this->customer_role === 'company', [
                'name' => $this->company_name,
                'role' => $this->company_role,
                'npwp_id' => $this->company_id_npwp,
                'phone' => $this->company_phone,
                'npwp_file_url' => $this->company_file_npwp ? Storage::url($this->company_file_npwp) : null,
                'npwp_file_path' => $this->company_file_npwp,
                'skt_file_url' => $this->company_file_skt ? Storage::url($this->company_file_skt) : null,
                'skt_file_path' => $this->company_file_skt,
            ]),
            'personal_details' => $this->when($this->customer_role === 'personal', [
                'ktp_file_url' => $this->personal_file_ktp ? Storage::url($this->personal_file_ktp) : null,
                'ktp_file_path' => $this->personal_file_ktp,
            ]),
            'quotations' => QuotationResource::collection($this->whenLoaded('quotations')),
            'registered_at' => $this->created_at->format('d F Y, H:i'),
        ];
    }
}