<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KwtSmsService
{
    private const SEND_URL = 'https://www.kwtsms.com/API/send/';

    protected string $username;
    protected string $password;
    protected string $sender;
    protected bool $testMode;
    protected bool $verifySsl;

    public function __construct()
    {
        $this->username  = (string) config('services.kwtsms.username');
        $this->password  = (string) config('services.kwtsms.password');
        $this->sender    = (string) config('services.kwtsms.sender');
        $this->testMode  = filter_var(config('services.kwtsms.test'), FILTER_VALIDATE_BOOLEAN);
        $this->verifySsl = filter_var(config('services.kwtsms.verify_ssl', true), FILTER_VALIDATE_BOOLEAN);

        if ($this->username === '' || $this->password === '' || $this->sender === '') {
            Log::error('kwtSMS credentials are incomplete. Set KWTSMS_USERNAME, KWTSMS_PASSWORD and KWTSMS_SENDER.');
            throw new Exception('SMS service is not configured.');
        }
    }

    /**
     * Send an SMS. $to may be in any common format (+965…, 00965…, spaces);
     * kwtSMS wants digits only, country code first (e.g. 96598765432).
     */
    public function sendSms(string $to, string $message): bool
    {
        $mobile = preg_replace('/\D/', '', $to);
        $mobile = preg_replace('/^00/', '', $mobile);

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->timeout(15)
                ->withOptions(['verify' => $this->verifySsl])
                ->post(self::SEND_URL, [
                    'username' => $this->username,
                    'password' => $this->password,
                    'sender'   => $this->sender,
                    'mobile'   => $mobile,
                    'message'  => $message,
                    'test'     => $this->testMode ? '1' : '0',
                ]);
        } catch (Exception $e) {
            Log::error('kwtSMS request failed', ['to' => $mobile, 'error' => $e->getMessage()]);

            throw new Exception('Failed to send SMS: ' . $e->getMessage());
        }

        $body = $response->json();

        if (! is_array($body) || strtoupper((string) ($body['result'] ?? '')) !== 'OK') {
            $code        = is_array($body) ? ($body['code'] ?? 'unknown') : 'unknown';
            $description = is_array($body) ? ($body['description'] ?? 'Unexpected response from SMS gateway') : 'Unexpected response from SMS gateway';

            Log::error('kwtSMS rejected the message', [
                'to'          => $mobile,
                'http_status' => $response->status(),
                'code'        => $code,
                'description' => $description,
            ]);

            throw new Exception("Failed to send SMS: {$description} ({$code})");
        }

        Log::info('SMS sent successfully via kwtSMS', [
            'to'                => $mobile,
            'msg_id'            => $body['msg-id'] ?? null,
            'points_charged'    => $body['points-charged'] ?? null,
            'balance_after'     => $body['balance-after'] ?? null,
            'test_mode'         => $this->testMode,
        ]);

        return true;
    }

    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        $message = "Your OTP code for Balance is {$otpCode}. This code will expire in 10 minutes. Do not share this code with anyone.";

        return $this->sendSms($phoneNumber, $message);
    }
}
