<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected $token;
    protected $endpoint = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN');
    }

    public function send(string $recipientPhoneNumber, string $otp): void
    {
        if (!$this->token) {
            Log::warning("Fonnte Token missing. OTP: {$otp}");
            return;
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => $this->token,
            ])->post($this->endpoint, [
                'target' => $recipientPhoneNumber,
                'message' => "Kode verifikasi HMS Anda adalah: *{$otp}*.\n\nSimpan kode ini dengan baik.",
                'countryCode' => '62',
            ]);

            $result = $response->json();

            if (!$response->successful() || (isset($result['status']) && !$result['status'])) {
                Log::error('Fonnte error: ' . json_encode($result));
            }

        } catch (\Exception $e) {
            Log::error('Fonnte connection failed: ' . $e->getMessage());
        }
    }
}