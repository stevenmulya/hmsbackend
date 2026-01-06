<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\OtpService; // <-- Penting: Impor OtpService
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    protected $otpService;

    // Lakukan Dependency Injection untuk OtpService agar bisa digunakan
    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }
    
    // Metode private untuk menggunakan kembali logika
    private function generateAndSendPasswordResetOtp(Customer $customer)
    {
        $otp = rand(100000, 999999);
        $customer->password_reset_code = $otp;
        $customer->password_reset_code_expires_at = now()->addMinutes(10);
        $customer->save();
        
        // Panggil service untuk mengirim OTP
        $this->otpService->send($customer->customer_phone, $otp);
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'customer_phone' => 'required|string|exists:customers,customer_phone'
        ]);

        $customer = Customer::where('customer_phone', $data['customer_phone'])->first();

        // Panggil metode untuk generate, simpan, dan kirim OTP
        $this->generateAndSendPasswordResetOtp($customer);

        return response()->json(['message' => 'Kode OTP untuk reset password telah dikirim ke WhatsApp Anda.']);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'customer_phone' => 'required|exists:customers,customer_phone',
            'otp' => 'required|digits:6',
            'customer_password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Customer::where('customer_phone', $data['customer_phone'])->first();

        if (!$customer || $customer->password_reset_code !== $data['otp'] || now()->isAfter($customer->password_reset_code_expires_at)) {
            return response()->json(['message' => 'Kode OTP tidak valid atau telah kedaluwarsa.'], 422);
        }

        $customer->customer_password = $data['customer_password'];
        $customer->password_reset_code = null;
        $customer->password_reset_code_expires_at = null;
        $customer->save();

        return response()->json(['message' => 'Password berhasil diubah. Silakan login.']);
    }
}