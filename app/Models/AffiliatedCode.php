<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AffiliatedCode extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'affiliated_codes';

    public const GIFT_TYPE_SELECT = [
        'percentage' => 'Percentage',
        'fixed'      => 'Fixed Amount',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'full_name',
        'code',
        'gift_type',
        'gift_value',
        'is_active',
        'usage_count',
        'notes',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'gift_value' => 'decimal:2',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Generate an affiliated code from full name
     * Takes first few letters of name and appends random 3-4 digit number
     * 
     * @param string $fullName
     * @return string
     */
    public static function generateCode($fullName)
    {
        // Remove spaces and special characters, convert to uppercase
        $name = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $fullName));
        
        // Take first 3-4 letters (prefer 4 if available)
        $prefix = substr($name, 0, min(4, strlen($name)));
        
        // If name is too short, pad with random letters
        if (strlen($prefix) < 3) {
            $prefix .= strtoupper(Str::random(3 - strlen($prefix)));
        }
        
        // Generate random 3-4 digit number
        $randomNumber = rand(100, 9999);
        
        $code = $prefix . $randomNumber;
        
        // Ensure uniqueness
        $counter = 1;
        while (self::where('code', $code)->exists()) {
            $randomNumber = rand(100, 9999);
            $code = $prefix . $randomNumber;
            $counter++;
            
            // Prevent infinite loop
            if ($counter > 100) {
                $code = $prefix . time(); // Fallback to timestamp
                break;
            }
        }
        
        return $code;
    }

    /**
     * Increment usage count when code is used
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Relationship to users who registered with this code
     */
    public function users()
    {
        return $this->hasMany(User::class, 'affiliated_code_id');
    }
}
