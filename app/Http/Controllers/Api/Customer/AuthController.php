<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    private function generateAndSendOtp(Customer $customer)
    {
        $otp = rand(100000, 999999);
        $customer->verification_code = $otp;
        $customer->verification_code_expires_at = now()->addMinutes(10);
        $customer->save();
        
        $this->otpService->send($customer->customer_phone, $otp);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'customer_phone' => 'required|string|unique:customers,customer_phone|min:10',
            'customer_username' => 'required|string|unique:customers,customer_username|min:4',
            'customer_password' => 'required|string|min:8|confirmed',
            'customer_role' => ['required', Rule::in(['company', 'personal'])],
            'company_file_npwp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'company_file_skt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'personal_file_ktp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['customer_password'] = Hash::make($validatedData['customer_password']);
        $usernameSlug = Str::slug($validatedData['customer_username']);

        if ($request->hasFile('personal_file_ktp')) {
            $extension = $request->file('personal_file_ktp')->getClientOriginalExtension();
            $fileName = "{$usernameSlug}_ktp.{$extension}";
            $validatedData['personal_file_ktp'] = $request->file('personal_file_ktp')->storeAs('personal_file_ktp', $fileName, 'public');
        }
        if ($request->hasFile('company_file_npwp')) {
            $extension = $request->file('company_file_npwp')->getClientOriginalExtension();
            $fileName = "{$usernameSlug}_npwp.{$extension}";
            $validatedData['company_file_npwp'] = $request->file('company_file_npwp')->storeAs('company_file_npwp', $fileName, 'public');
        }
        if ($request->hasFile('company_file_skt')) {
            $extension = $request->file('company_file_skt')->getClientOriginalExtension();
            $fileName = "{$usernameSlug}_skt.{$extension}";
            $validatedData['company_file_skt'] = $request->file('company_file_skt')->storeAs('company_file_skt', $fileName, 'public');
        }

        $customer = Customer::create($validatedData);
        $this->generateAndSendOtp($customer);

        return response()->json([
            'message' => 'Registrasi berhasil. Kode verifikasi telah dikirim ke WhatsApp Anda.',
            'phone' => $customer->customer_phone
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'customer_phone' => 'required|string|exists:customers,customer_phone',
            'otp_code' => 'required|digits:6'
        ]);

        $customer = Customer::where('customer_phone', $data['customer_phone'])->first();

        if (!$customer || $customer->verification_code !== $data['otp_code'] || now()->isAfter($customer->verification_code_expires_at)) {
            return response()->json(['message' => 'Kode OTP tidak valid atau telah kedaluwarsa.'], 422);
        }

        $customer->phone_verified_at = now();
        $customer->verification_code = null;
        $customer->verification_code_expires_at = null;
        $customer->save();

        $token = $customer->createToken('customer-auth-token')->plainTextToken;
        return response()->json(['message' => 'Akun berhasil diverifikasi!', 'token' => $token, 'customer' => $customer]);
    }

    public function resendVerificationOtp(Request $request)
    {
        $data = $request->validate(['customer_phone' => 'required|string|exists:customers,customer_phone']);
        $customer = Customer::where('customer_phone', $data['customer_phone'])->first();

        if ($customer->phone_verified_at) {
            return response()->json(['message' => 'Akun ini sudah terverifikasi.'], 400);
        }

        $this->generateAndSendOtp($customer);

        return response()->json(['message' => 'Kode OTP baru telah berhasil dikirim.']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'customer_username' => 'required',
            'customer_password' => 'required'
        ]);
        $customer = Customer::where('customer_username', $credentials['customer_username'])->first();

        if (!$customer || !Hash::check($credentials['customer_password'], $customer->customer_password)) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }

        if (!$customer->phone_verified_at) {
            $this->generateAndSendOtp($customer);
            return response()->json([
                'message' => 'Akun Anda belum terverifikasi. Kode OTP baru telah dikirim ke WhatsApp Anda.',
                'unverified' => true,
                'phone' => $customer->customer_phone
            ], 403);
        }

        $token = $customer->createToken('customer-auth-token')->plainTextToken;
        return response()->json(['message' => 'Login berhasil!', 'token' => $token, 'customer' => $customer]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }
}