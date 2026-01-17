<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
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
                  ->orWhere('customer_username', 'like', "%{$searchTerm}%");
            });
        }

        $customers = $query->latest()->paginate(10);
        
        return CustomerResource::collection($customers);
    }

    public function show(Customer $customer)
    {
        $customer->load(['quotations.items']);
        return new CustomerResource($customer);
    }

    public function update(Request $request, Customer $customer)
    {
        $validatedData = $request->validate([
            'marketing_name' => 'sometimes|nullable|string|max:255',
        ]);
        
        $customer->update($validatedData);
        
        return response()->json([
            'message' => 'Data customer berhasil diperbarui.',
            'customer' => new CustomerResource($customer->fresh()->load(['quotations.items']))
        ]);
    }

    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return response()->json([
                'message' => 'Customer berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
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
                'Nama Marketing', 'Terverifikasi', 'Tanggal Daftar'
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
                    $customer->phone_verified_at ? 'Yes' : 'No',
                    $customer->created_at->format('Y-m-d H:i:s'),
                ]);
            });
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}