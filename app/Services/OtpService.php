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
        $this->twilioService = $twilioService;
    }

    /**
     * Generate a random 4-digit OTP code
     *
     * @return int
     */
    protected function generateOtpCode(): int
    {
        return random_int(1000, 9999);
    }

    public function sendOtp(string $countryCode, string $phoneNumber): array
    {
        try {
            Log::debug('OTP send request raw data', [
                'country_code' => $countryCode,
                'phone_number' => $phoneNumber
            ]);

            // Normalize country code and phone number to digits only
            $normalizedCountryCode = preg_replace('/[^0-9]/', '', $countryCode);
            $normalizedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

            // Strip leading zeros (common for 00XXX or 0XXX formats)
            $normalizedCountryCode = ltrim($normalizedCountryCode, '0');
            $normalizedPhone = ltrim($normalizedPhone, '0');

            // If phone number starts with the country code, strip it to avoid duplication in E.164
            if (!empty($normalizedCountryCode) && str_starts_with($normalizedPhone, $normalizedCountryCode) && strlen($normalizedPhone) > strlen($normalizedCountryCode)) {
                $normalizedPhone = substr($normalizedPhone, strlen($normalizedCountryCode));
                // Again strip any leading zeros after removing country code
                $normalizedPhone = ltrim($normalizedPhone, '0');
            }

            // Combine for E.164-like display only (no spaces)
            $mobileNumber = $normalizedCountryCode . $normalizedPhone;
            $e164Phone = '+' . $mobileNumber;

            Log::debug('OTP normalized phone data', [
                'normalized_country_code' => $normalizedCountryCode,
                'normalized_phone' => $normalizedPhone,
                'e164_format' => $e164Phone
            ]);

            // Test account: always use fixed OTP 1234 and skip real SMS
            $isTestNumber = ($mobileNumber === '96565560520');
            $otpCode = $isTestNumber ? 1234 : $this->generateOtpCode();
            $expiresAt = $isTestNumber
                ? Carbon::now()->addYears(1)
                : Carbon::now()->addMinutes($this->expirationMinutes);

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

            // Send OTP through Twilio (skipped for test account)
            if (!$isTestNumber) {
                $this->twilioService->sendOtp($e164Phone, (string) $otpCode);
            }

            Log::info('OTP generated and sent via Twilio', [
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

            // Strip leading zeros from phone number (common national trunk prefix)
            $normalizedPhone = ltrim($normalizedPhone, '0');

            // If phone number starts with the country code, strip it to avoid duplication in E.164
            if (!empty($normalizedCountryCode) && str_starts_with($normalizedPhone, $normalizedCountryCode) && strlen($normalizedPhone) > strlen($normalizedCountryCode)) {
                $normalizedPhone = substr($normalizedPhone, strlen($normalizedCountryCode));
                // Again strip any leading zeros after removing country code
                $normalizedPhone = ltrim($normalizedPhone, '0');
            }

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

            // Check if OTP matches the stored value
            $storedOtp = (int) $user->otp;
            $isValidOtp = ($storedOtp == $otpCodeInt);

            if (!$isValidOtp) {
                return [
                    'success' => false,
                    'message' => 'Invalid OTP code',
                ];
            }

            // Check if OTP is expired
            if (!$user->otp_expires_at || Carbon::parse($user->otp_expires_at)->isPast()) {
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

