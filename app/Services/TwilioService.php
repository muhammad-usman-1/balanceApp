<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Exception;

class TwilioService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $this->fromNumber = config('services.twilio.from_number');

        if (!$accountSid || !$authToken) {
            throw new Exception('Twilio credentials are not configured. Please set TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, and TWILIO_FROM_NUMBER in your .env file.');
        }

        $this->client = new Client($accountSid, $authToken);
    }

    /**
     * Send SMS message
     *
     * @param string $to Phone number to send to (with country code)
     * @param string $message Message content
     * @return bool
     */
    public function sendSms(string $to, string $message): bool
    {
        try {
            $message = $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message,
                ]
            );

            Log::info('SMS sent successfully', [
                'to' => $to,
                'message_sid' => $message->sid,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send SMS', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }

    /**
     * Send OTP via SMS
     *
     * @param string $phoneNumber Phone number to send OTP to
     * @param string $otpCode The 4-digit OTP code to send
     * @return bool
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        $message = "Your OTP code is: {$otpCode}. This code will expire in 10 minutes. Do not share this code with anyone.";
        
        return $this->sendSms($phoneNumber, $message);
    }
}

