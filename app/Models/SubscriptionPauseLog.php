<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPauseLog extends Model
{
    use HasFactory;

    public $table = 'subscription_pause_logs';

    protected $dates = [
        'action_timestamp',
        'paused_at',
        'resumed_at',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_subcrption_id',
        'action',
        'action_timestamp',
        'paused_at',
        'resumed_at',
        'paused_days',
        'reason',
        'performed_by_type',
        'performed_by_id',
        'performed_by_name',
        'notes',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'action_timestamp' => 'datetime',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function userSubscription()
    {
        return $this->belongsTo(UserSubcrption::class, 'user_subcrption_id');
    }
}
