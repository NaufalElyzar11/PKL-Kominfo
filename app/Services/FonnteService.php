<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('fonnte.token');
        $this->apiUrl = config('fonnte.api_url');
    }

    /**
     * Kirim pesan WhatsApp via Fonnte API
     *
     * @param string $target Nomor telepon tujuan
     * @param string $message Isi pesan
     * @return array Response dari Fonnte API
     */
    public function sendMessage(string $target, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->asForm()->post($this->apiUrl, [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Indonesia
            ]);

            $result = $response->json();

            Log::info('Fonnte API Response', [
                'target' => $target,
                'response' => $result,
            ]);

            return [
                'success' => $response->successful() && ($result['status'] ?? false),
                'message' => $result['detail'] ?? $result['reason'] ?? 'Unknown response',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Fonnte API Error', [
                'target' => $target,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan WhatsApp: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Kirim OTP reset password
     *
     * @param string $target Nomor telepon tujuan
     * @param string $otp Kode OTP
     * @param string $name Nama user (opsional)
     * @return array
     */
    public function sendPasswordResetOtp(string $target, string $otp, string $name = ''): array
    {
        $greeting = $name ? "Halo {$name}," : "Halo,";
        
        $message = "{$greeting}

Anda menerima pesan ini karena ada permintaan reset password untuk akun Siap Cuti Anda.

*Kode OTP Anda: {$otp}*

Kode ini berlaku selama 15 menit.

Jika Anda tidak meminta reset password, abaikan pesan ini.

_Siap Cuti - Diskominfo Kota Banjarbaru_";

        return $this->sendMessage($target, $message);
    }
}
