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
        // Mengambil token dari .env
        $this->token = env('FONNTE_TOKEN');
    }

    public function send(string $recipientPhoneNumber, string $otp): void
    {
        // Pastikan token terbaca
        if (!$this->token) {
            Log::warning("Fonnte Token tidak ditemukan di .env. OTP disimulasikan: {$otp}");
            return;
        }

        try {
            // withoutVerifying() digunakan untuk bypass error SSL cURL 60
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => $this->token,
            ])->post($this->endpoint, [
                'target' => $recipientPhoneNumber,
                'message' => "Kode verifikasi HMS Anda adalah: *{$otp}*.\n\nSimpan kode ini dengan baik dan jangan berikan kepada siapapun.",
                'countryCode' => '62',
            ]);

            $result = $response->json();

            if (!$response->successful() || (isset($result['status']) && !$result['status'])) {
                Log::error('Fonnte gagal mengirim pesan: ' . json_encode($result));
            }

        } catch (\Exception $e) {
            Log::error('Koneksi ke Fonnte bermasalah: ' . $e->getMessage());
        }
    }
}