<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_username', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_phone', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $customers = $query->latest()->paginate(10);
        
        return CustomerResource::collection($customers);
    }

    public function show($username)
    {
        $customer = Customer::where('customer_username', $username)
            ->with(['quotations.items'])
            ->firstOrFail();

        return new CustomerResource($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        if ($request->has('customer_phone')) {
            $phone = preg_replace('/\D/', '', $request->customer_phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }
            $request->merge(['customer_phone' => $phone]);
        }

        $validatedData = $request->validate([
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_phone' => [
                'sometimes', 
                'required', 
                'numeric', 
                Rule::unique('customers', 'customer_phone')->ignore($customer->id)
            ],
            'customer_email' => [
                'sometimes', 
                'nullable', 
                'email', 
                Rule::unique('customers', 'email')->ignore($customer->id)
            ],
            'marketing_name' => 'sometimes|nullable|string|max:255',
            'customer_address' => 'sometimes|nullable|string',
            'company_name' => 'sometimes|nullable|string|max:255',
            'company_role' => 'sometimes|nullable|string|max:255',
            'company_id_npwp' => 'sometimes|nullable|string|max:255',
            'company_phone' => 'sometimes|nullable|string|max:255',
        ]);
        
        if (isset($validatedData['customer_email'])) {
            $validatedData['email'] = $validatedData['customer_email'];
            unset($validatedData['customer_email']);
        }

        $customer->update($validatedData);
        
        return response()->json([
            'message' => 'Data customer berhasil diperbarui.',
            'customer' => new CustomerResource($customer->fresh()->load(['quotations.items']))
        ]);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $customer = Customer::findOrFail($id);

            $fileFields = ['personal_file_ktp', 'company_file_npwp', 'company_file_skt'];
            foreach ($fileFields as $field) {
                if ($customer->$field) {
                    Storage::disk('public')->delete($customer->$field);
                }
            }

            foreach ($customer->quotations as $quotation) {
                $quotation->items()->delete();
                $quotation->delete();
            }

            $customer->delete();

            return response()->json([
                'message' => 'Customer dan seluruh riwayat penawaran berhasil dihapus.'
            ], 200);
        });
    }

    public function exportCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_' . date("Y-m-d_H-i-s") . '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Nama', 'Email', 'Telepon', 'Username', 'Role',
                'Nama Perusahaan', 'Jabatan Perusahaan', 'NPWP', 'Telepon Perusahaan',
                'Nama Marketing', 'Tanggal Daftar'
            ]);

            Customer::cursor()->each(function ($customer) use ($file) {
                fputcsv($file, [
                    $customer->id,
                    $customer->customer_name,
                    $customer->email,
                    $customer->customer_phone,
                    $customer->customer_username,
                    $customer->customer_role,
                    $customer->company_name,
                    $customer->company_role,
                    $customer->company_id_npwp,
                    $customer->company_phone,
                    $customer->marketing_name,
                    $customer->created_at->format('Y-m-d H:i:s'),
                ]);
            });
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}