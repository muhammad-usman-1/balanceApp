<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPauseRequest extends Model
{
    use HasFactory;

    public $table = 'subscription_pause_requests';

    protected $fillable = [
        'user_subcrption_id',
        'user_id',
        'pause_start_date',
        'pause_end_date',
        'pause_days',
        'reason',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'pause_start_date' => 'date:Y-m-d',
        'pause_end_date'   => 'date:Y-m-d',
        'reviewed_at'      => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubcrption::class, 'user_subcrption_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
