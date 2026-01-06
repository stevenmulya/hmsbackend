<?php
// --- Controller for managing Customers from the Admin Panel (with Search) ---
// This file handles all administrative actions for viewing, updating, and deleting customer data,
// including eager loading their quotation history and providing search functionality.

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    /**
     * Display a paginated list of all customers, with search functionality.
     */
    public function index(Request $request)
    {
        // Start with a base query
        $query = Customer::query();

        // If a 'search' parameter is present in the URL, filter the results.
        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            // This groups the WHERE clauses to correctly filter by name OR username
            $query->where(function ($q) use ($searchTerm) {
                $q->where('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_username', 'like', "%{$searchTerm}%");
            });
        }

        $customers = $query->latest()->paginate(10);
        
        return CustomerResource::collection($customers);
    }

    /**
     * Display a single customer's details, including their quotation history.
     */
    public function show(Customer $customer)
    {
        $customer->load(['quotations.items']);
        return new CustomerResource($customer);
    }

    /**
     * Update a customer's details (by an admin).
     */
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

    /**
     * Soft delete a customer account.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(null, 204);
    }

    /**
     * Export all customers to a CSV file.
     */
    public function exportCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_' . date("Y-m-d_H-i-s") . '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // CSV Header Row
            fputcsv($file, [
                'ID', 'Nama', 'Email', 'Telepon', 'Username', 'Role',
                'Nama Perusahaan', 'Jabatan Perusahaan', 'NPWP', 'Telepon Perusahaan',
                'Nama Marketing', 'Terverifikasi', 'Tanggal Daftar'
            ]);

            // Using cursor() is memory-efficient for large exports
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