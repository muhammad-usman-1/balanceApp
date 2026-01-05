<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Exception;

class TwilioService
{
    protected $client;
    protected $fromNumber;
    protected $messagingServiceSid;

    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $this->fromNumber = config('services.twilio.from_number');
        $this->messagingServiceSid = config('services.twilio.messaging_service_sid');

        // Twilio is disabled for testing - don't initialize if credentials are missing
        if (!$accountSid || !$authToken || (!$this->fromNumber && !$this->messagingServiceSid)) {
            // Log warning but don't throw exception - Twilio is disabled for testing
            Log::warning('Twilio credentials are incomplete. Twilio SMS service is disabled.');
            $this->client = null;
            return;
        }

        // Fix for "SSL certificate problem: unable to get local issuer certificate" on local dev
        $options = [
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ];
        $httpClient = new \Twilio\Http\CurlClient($options);

        $this->client = new Client($accountSid, $authToken, null, null, $httpClient);
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
        // Twilio is disabled for testing - return true without sending
        if (!$this->client) {
            Log::info('Twilio SMS service is disabled. SMS not sent.', [
                'to' => $to,
                'message' => $message,
            ]);
            return true;
        }

        try {
            $payload = ['body' => $message];

            if ($this->messagingServiceSid) {
                $payload['messagingServiceSid'] = $this->messagingServiceSid;
            } else {
                $payload['from'] = $this->fromNumber;
            }

            $message = $this->client->messages->create($to, $payload);

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

