<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$kernel->bootstrap();

$config = config('services.hesabe');
$secret = $config['secret_key'] ?? '';
$iv = $config['iv_key'] ?? '';

$output = "Loaded Config:\n";
$output .= "Secret Raw Length: " . strlen($secret) . "\n";
$output .= "IV Raw Length: " . strlen($iv) . "\n";

$secretTrimmed = trim($secret);
$ivTrimmed = trim($iv);

$output .= "Secret Trimmed Length: " . strlen($secretTrimmed) . "\n";
$output .= "IV Trimmed Length: " . strlen($ivTrimmed) . "\n";

if (strlen($secret) !== strlen($secretTrimmed)) {
    $output .= "WARNING: Secret key has whitespace!\n";
}
if (strlen($iv) !== strlen($ivTrimmed)) {
    $output .= "WARNING: IV has whitespace!\n";
}

$output .= "Secret Trimmed is Hex: " . (ctype_xdigit($secretTrimmed) ? 'YES' : 'NO') . "\n";
$output .= "IV Trimmed is Hex: " . (ctype_xdigit($ivTrimmed) ? 'YES' : 'NO') . "\n";

// Proposed better normalization logic
function normalizeKeyBetter($key) {
    if (!$key) return null;
    $key = trim($key); // Fix whitespace
    
    // AES-256 requires 32 bytes (256 bits).
    // If we have 64 hex chars, that's 32 bytes.
    if (ctype_xdigit($key) && strlen($key) === 64) {
        return hex2bin($key);
    }
    
    // AES-256 requires 16 byte IV (128 bits).
    // If we have 32 hex chars, that's 16 bytes.
    // BUT wait, is the input meant to be the IV itself or the hex rep?
    // If the logical IV is 16 bytes, and user gives 32 hex chars...
    // The current code assumes hex → bin.
    
    // Existing logic was: any even length hex -> bin.
    // For a 32-char hex string (16 bytes), existing logic makes it 16 bytes.
    // This is correct for IV (needs 16 bytes).
    // This is INCORRECT for Secret (needs 32 bytes).
    
    // So distinct logic for Secret vs IV?
    // Or just check lengths.
    
    // If it's hex and even:
    if (ctype_xdigit($key) && strlen($key) % 2 === 0) {
        // If it decodes to 32 bytes (64 chars), great for key.
        // If it decodes to 16 bytes (32 chars), great for IV.
        // If it decodes to 16 bytes (32 chars), BAD for key?
        // But we don't know if this is key or IV here.
        return hex2bin($key);
    }
    
    return $key;
}

$normSecret = normalizeKeyBetter($secret);
$normIv = normalizeKeyBetter($iv);

$output .= "\nWith Proposed Fix (+trim):\n";
$output .= "Secret Length: " . strlen($normSecret) . " bytes\n";
$output .= "IV Length: " . strlen($normIv) . " bytes\n";

file_put_contents('debug_result.txt', $output);
echo "Done.\n";
