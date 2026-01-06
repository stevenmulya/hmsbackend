<?php
namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $customer = $request->user();

        $validatedData = $request->validate([
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_address' => 'sometimes|nullable|string',
            'customer_username' => ['sometimes','required','string','min:4', Rule::unique('customers')->ignore($customer->id)],
            
            // Company fields validation
            'company_name' => 'nullable|string|max:255',
            'company_role' => 'nullable|string|max:255',
            'company_id_npwp' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:255',

            // File validations
            'personal_file_ktp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'company_file_npwp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'company_file_skt'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
        $usernameSlug = Str::slug($customer->customer_username);

        // --- Professional File Handling: Custom Naming on Update ---
        $fileFields = [
            'personal_file_ktp' => '_ktp',
            'company_file_npwp' => '_npwp',
            'company_file_skt' => '_skt',
        ];

        foreach ($fileFields as $field => $suffix) {
            if ($request->hasFile($field)) {
                // 1. Delete the old file to save space
                if ($customer->{$field}) {
                    Storage::disk('public')->delete($customer->{$field});
                }

                // 2. Create new custom filename and store the new file
                $extension = $request->file($field)->getClientOriginalExtension();
                $fileName = "{$usernameSlug}{$suffix}.{$extension}";
                $validatedData[$field] = $request->file($field)->storeAs($field, $fileName, 'public');
            }
        }

        $customer->update($validatedData);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'customer' => $customer->fresh()
        ]);
    }
}