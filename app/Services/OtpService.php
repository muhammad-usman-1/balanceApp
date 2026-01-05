<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class OtpService
{
    protected $expirationMinutes = 10;
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        // Twilio service is disabled for testing - OTP is always 1234
        $this->twilioService = $twilioService;
    }

    /**
     * Generate a random 4-digit OTP code
     * 
     * NOTE: Currently disabled - always returns 1234 for testing
     *
     * @return int
     */
    protected function generateOtpCode(): int
    {
        // Always generate OTP as 1234 for testing
        return 1234;
        
        // Original random generation (disabled)
        // return random_int(1000, 9999);
    }

    /**
     * Send OTP to phone number
     *
     * @param string $countryCode
     * @param string $phoneNumber
     * @return array
     * @throws Exception
     */
    public function sendOtp(string $countryCode, string $phoneNumber): array
    {
        try {
            // Normalize country code and phone number to digits only
            $normalizedCountryCode = preg_replace('/[^0-9]/', '', $countryCode);
            $normalizedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

            // Combine for E.164-like display only (no spaces)
            $mobileNumber = $normalizedCountryCode . $normalizedPhone;

            // Generate new 4-digit OTP
            $otpCode = $this->generateOtpCode();
            $expiresAt = Carbon::now()->addMinutes($this->expirationMinutes);

            // Find or create user by mobile number
            $user = User::where('mobile', $normalizedPhone)
                ->where('country_code', $normalizedCountryCode)
                ->first();

            if ($user) {
                // Update existing user's OTP
                $user->update([
                    'country_code' => $normalizedCountryCode,
                    'mobile' => $normalizedPhone,
                    'otp' => $otpCode,
                    'otp_expires_at' => $expiresAt,
                ]);
            } else {
                // Create new user with OTP
                $user = User::create([
                    'country_code' => $normalizedCountryCode,
                    'mobile' => $normalizedPhone,
                    'otp' => $otpCode,
                    'otp_expires_at' => $expiresAt,
                ]);
            }

            $e164Phone = '+' . $mobileNumber;

            // Twilio service disabled for testing
            // Send OTP through Twilio
            // $this->twilioService->sendOtp($e164Phone, (string) $otpCode);

            Log::info('OTP generated and saved (Twilio disabled)', [
                'phone_number' => $e164Phone,
                'country_code' => $normalizedCountryCode,
                'mobile' => $mobileNumber,
                'user_id' => $user->id,
            ]);

            return [
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_at' => $expiresAt->toIso8601String(),
                'phone_number' => $e164Phone,
                'country_code' => $normalizedCountryCode,
            ];
        } catch (Exception $e) {
            Log::error('Failed to send OTP', [
                'phone_number' => $countryCode . $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to send OTP: ' . $e->getMessage());
        }
    }

    /**
     * Verify OTP code
     *
     * @param string $countryCode
     * @param string $phoneNumber
     * @param string $otpCode
     * @return array
     */
    public function verifyOtp(string $countryCode, string $phoneNumber, string $otpCode): array
    {
        try {
            // Normalize country code and phone number to digits only
            $normalizedCountryCode = preg_replace('/[^0-9]/', '', $countryCode);
            $normalizedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
            $mobileNumber = $normalizedCountryCode . $normalizedPhone;
            $otpCodeInt = (int) $otpCode;

            // Find user by mobile number
            $user = User::where('mobile', $normalizedPhone)
                ->where('country_code', $normalizedCountryCode)
                ->first();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found with this phone number',
                ];
            }

            // Always accept 1234 for testing (Twilio disabled)
            // Also check if OTP matches the stored value
            $storedOtp = (int) $user->otp;
            $isValidOtp = ($otpCodeInt == 1234) || ($storedOtp == $otpCodeInt);

            if (!$isValidOtp) {
                return [
                    'success' => false,
                    'message' => 'Invalid OTP code',
                ];
            }

            // Skip expiration check for testing OTP 1234
            // Check if OTP is expired (only for non-test OTPs)
            if ($otpCodeInt != 1234 && (!$user->otp_expires_at || Carbon::parse($user->otp_expires_at)->isPast())) {
                return [
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.',
                ];
            }

            // OTP is valid - clear it (optional, or keep it for login)
            // We'll keep it for now as it might be used for login

            Log::info('OTP verified successfully', [
                'phone_number' => '+' . $mobileNumber,
                'country_code' => $normalizedCountryCode,
                'mobile' => $normalizedPhone,
                'user_id' => $user->id,
            ]);

            // Load user relationships
            $user->load('roles');

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'country_code' => $normalizedCountryCode,
                'phone_number' => '+' . $mobileNumber,
                'user' => $user,
            ];
        } catch (Exception $e) {
            Log::error('Failed to verify OTP', [
                'phone_number' => $countryCode . $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to verify OTP: ' . $e->getMessage(),
            ];
        }
    }
}

