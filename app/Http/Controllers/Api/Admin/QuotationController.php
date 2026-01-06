<?php
// --- Controller for managing Quotations from the Admin Panel (with Export) ---
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuotationResource;
use App\Models\Customer;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::query();
        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                      $customerQuery->where('customer_name', 'like', "%{$searchTerm}%")
                                    ->orWhere('customer_username', 'like', "%{$searchTerm}%");
                  });
            });
        }
        $quotations = $query->with(['customer', 'items'])->latest()->paginate(20);
        return QuotationResource::collection($quotations);
    }

    public function showByCustomer(Customer $customer, Quotation $quotation)
    {
        if ($quotation->customer_id !== $customer->id) {
            abort(404, 'Quotation not found for this customer.');
        }
        $quotation->load(['customer', 'items.product']);
        if (!$quotation->is_seen_by_admin) {
            $quotation->update(['is_seen_by_admin' => true]);
        }
        return new QuotationResource($quotation);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate(['status' => 'required|in:on progress,done']);
        $quotation->update($validated);
        return new QuotationResource($quotation->load('customer', 'items.product'));
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return response()->json(null, 204);
    }

    /**
     * Export a list of all quotations to a CSV file.
     */
    public function exportCsvList()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="all_quotations_' . date("Y-m-d") . '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Customer', 'Status', 'Jumlah Item', 'Tanggal']);
            Quotation::with('customer')->cursor()->each(function ($quote) use ($file) {
                fputcsv($file, [
                    $quote->id,
                    $quote->customer->customer_name,
                    $quote->status,
                    $quote->items()->count(),
                    $quote->created_at->format('d-m-Y'),
                ]);
            });
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export the details of a single quotation to a CSV file (for printing).
     */
    public function exportCsvDetail(Quotation $quotation)
    {
        $quotation->load(['customer', 'items.product']);
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quotation_' . $quotation->id . '_' . $quotation->customer->customer_username . '.csv"',
        ];

        $callback = function () use ($quotation) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['QUOTATION REQUEST']);
            fputcsv($file, ['ID Penawaran:', '#' . $quotation->id]);
            fputcsv($file, ['Tanggal:', $quotation->created_at->format('d F Y')]);
            fputcsv($file, ['Status:', strtoupper($quotation->status)]);
            fputcsv($file, []);
            fputcsv($file, ['CUSTOMER']);
            fputcsv($file, ['Nama:', $quotation->customer->customer_name]);
            fputcsv($file, ['Telepon:', $quotation->customer->customer_phone]);
            fputcsv($file, ['Email:', $quotation->customer->email]);
            fputcsv($file, []);
            fputcsv($file, ['DAFTAR ITEM']);
            fputcsv($file, ['Nama Produk', 'Kode Produk', 'Jumlah', 'Harga Satuan']);
            foreach ($quotation->items as $item) {
                fputcsv($file, [
                    $item->product->product_name,
                    $item->product->product_code,
                    $item->quantity,
                    $item->price,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}