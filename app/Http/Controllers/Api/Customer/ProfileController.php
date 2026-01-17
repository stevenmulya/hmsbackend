<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\OtpService;

class ProfileController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function sendOtpUpdatePhone(Request $request)
    {
        $phone = preg_replace('/\D/', '', $request->new_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        $request->merge(['new_phone' => $phone]);

        $request->validate([
            'new_phone' => [
                'required',
                'numeric',
                'digits_between:10,15',
                Rule::unique('customers', 'customer_phone')->ignore($request->user()->id)
            ],
        ]);

        $otp = (string) rand(111111, 666666);
        Cache::put('otp_update_' . $phone, $otp, now()->addMinutes(10));

        $this->otpService->send($phone, $otp);

        return response()->json(['message' => 'OTP dikirim ke nomor baru']);
    }

    public function verifyOtpUpdatePhone(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|numeric',
            'otp_code' => 'required|numeric',
        ]);

        $cachedOtp = Cache::get('otp_update_' . $request->customer_phone);

        if (!$cachedOtp || $cachedOtp != $request->otp_code) {
            return response()->json(['message' => 'Kode OTP salah atau kadaluwarsa.'], 422);
        }

        Cache::forget('otp_update_' . $request->customer_phone);

        return response()->json(['message' => 'Nomor telepon berhasil diverifikasi']);
    }

    public function update(Request $request)
    {
        $customer = $request->user();

        if ($request->has('customer_phone')) {
            $phone = preg_replace('/\D/', '', $request->customer_phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }
            $request->merge(['customer_phone' => $phone]);
        }

        $validatedData = $request->validate([
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_address' => 'sometimes|nullable|string',
            'customer_username' => ['sometimes', 'required', 'string', 'min:4', Rule::unique('customers')->ignore($customer->id)],
            'customer_email' => ['sometimes', 'required', 'email', Rule::unique('customers', 'email')->ignore($customer->id)],
            'customer_phone' => ['sometimes', 'required', 'numeric', Rule::unique('customers', 'customer_phone')->ignore($customer->id)],
            'company_name' => 'nullable|string|max:255',
            'company_role' => 'nullable|string|max:255',
            'company_id_npwp' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:255',
            'personal_file_ktp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'company_file_npwp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'company_file_skt'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
        $usernameSlug = Str::slug($request->customer_username ?? $customer->customer_username);

        $fileFields = [
            'personal_file_ktp' => '_ktp',
            'company_file_npwp' => '_npwp',
            'company_file_skt' => '_skt',
        ];

        foreach ($fileFields as $field => $suffix) {
            if ($request->hasFile($field)) {
                if ($customer->{$field}) {
                    Storage::disk('public')->delete($customer->{$field});
                }

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