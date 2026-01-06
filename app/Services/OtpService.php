<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Http\CurlClient; // Import CurlClient bawaan Twilio

class OtpService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $this->fromNumber = env('TWILIO_WHATSAPP_FROM');

        if ($sid && $token && $this->fromNumber) {
            
            // --- AWAL DARI PERBAIKAN ---

            // 1. Opsi untuk menonaktifkan verifikasi SSL di cURL
            $curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ];

            // 2. Buat HTTP client bawaan Twilio dengan opsi cURL kustom kita
            $httpClient = new CurlClient($curlOptions);

            // 3. Berikan HTTP client yang sudah dikonfigurasi ke Twilio
            $this->client = new Client($sid, $token, null, null, $httpClient);

            // --- AKHIR DARI PERBAIKAN ---
        }
    }

    public function send(string $recipientPhoneNumber, string $otp): void
    {
        if (!$this->client) {
            Log::warning("Twilio credentials not set. OTP sending is simulated.");
            Log::info("SIMULASI OTP: Kode untuk {$recipientPhoneNumber} adalah {$otp}");
            return;
        }

        try {
            $this->client->messages->create("whatsapp:{$recipientPhoneNumber}", [
                "from" => "whatsapp:{$this->fromNumber}",
                "body" => "Kode verifikasi Anda adalah: {$otp}"
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim OTP via Twilio: ' . $e->getMessage());
        }
    }
}