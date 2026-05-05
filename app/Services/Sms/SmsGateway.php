<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsGateway
{
    public function send(string $phone, string $message): bool
    {
        $driver = strtolower((string) env('SMS_GATEWAY', 'log'));

        if ($driver === 'msegat') {
            return $this->sendViaMsegat($phone, $message);
        }

        if ($driver === 'vonage') {
            return $this->sendViaVonage($phone, $message);
        }

        if ($driver === 'log') {
            return $this->sendViaLog($phone, $message);
        }

        throw new RuntimeException('SMS_GATEWAY must be set to msegat, vonage, or log.');
    }

    private function sendViaLog(string $phone, string $message): bool
    {
        Log::info('[SMS LOG DRIVER] OTP Message', [
            'to'      => $phone,
            'message' => $message,
        ]);
        return true;
    }

    private function sendViaMsegat(string $phone, string $message): bool
    {
        $username = env('MSEGAT_USERNAME');
        $password = env('MSEGAT_PASSWORD');
        $sender = env('MSEGAT_SENDER', 'RamoStore');

        if (!$username || !$password) {
            throw new RuntimeException('Msegat credentials are missing.');
        }

        $response = Http::asForm()->post('https://www.msegat.com/gw/sendsms.php', [
            'userName'   => $username,
            'numbers'    => $phone,
            'userSender' => $sender,
            'apiKey'     => $password,
            'msg'        => $message,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Msegat SMS request failed.');
        }

        return true;
    }

    private function sendViaVonage(string $phone, string $message): bool
    {
        $key    = env('VONAGE_KEY');
        $secret = env('VONAGE_SECRET');
        $from   = env('VONAGE_FROM', 'RamoStore');

        if (!$key || !$secret) {
            throw new RuntimeException('Vonage credentials are missing.');
        }

        $response = Http::asForm()->post('https://rest.nexmo.com/sms/json', [
            'api_key'    => $key,
            'api_secret' => $secret,
            'to'         => $phone,
            'from'       => $from,
            'text'       => $message,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Vonage SMS request failed.');
        }

        return true;
    }
}
