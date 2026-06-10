<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SubscriptionPauseLog;
use App\Models\SubscriptionPauseRequest;

class UserSubcrption extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'user_subcrptions';

    public const PAYMENT_SELECT = [
        'pending' => 'Pending',
        'paid'    => 'Paid',
    ];

    public const STATUS_SELECT = [
        'active'   => 'Active',
        'inactive' => 'Inactive',
        'queued'   => 'Queued',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'paused_at',
        'paused_until',
        'renewal_notified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'selected_days',
        'user_address_id',
        'branch_id',
        'area_id',
        'start_date',
        'end_date',
        'user_id',
        'subcrption_plans_id',
        'duration_id',
        'price',
        'currency',
        'payment',
        'payment_reference',
        'payment_gateway',
        'card_last_four',
        'card_brand',
        'payment_meta',
        'status',
        'is_personalized',
        'protein',
        'carbs',
        'is_paused',
        'paused_at',
        'paused_until',
        'total_paused_days',
        'original_end_date',
        'queued_after_subscription_id',
        'auto_renew',
        'renewal_notified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'payment_meta' => 'array',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getStartDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getEndDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subcrption_plans()
    {
        return $this->belongsTo(SubcrptionPlan::class, 'subcrption_plans_id');
    }

    public function duration()
    {
        return $this->belongsTo(Duration::class, 'duration_id');
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    public function area()
    {
        return $this->belongsTo(\App\Models\Area::class, 'area_id');
    }

    public function subscription_days()
    {
        return $this->hasMany(SubscriptionDay::class, 'user_subcrptions_id');
    }

    public function pause_logs()
    {
        return $this->hasMany(SubscriptionPauseLog::class, 'user_subcrption_id');
    }

    public function pause_requests()
    {
        return $this->hasMany(SubscriptionPauseRequest::class, 'user_subcrption_id');
    }

    // The subscription this one is queued to start after
    public function queuedAfter()
    {
        return $this->belongsTo(UserSubcrption::class, 'queued_after_subscription_id');
    }

    // The next queued subscription waiting to start after this one
    public function queuedSubscription()
    {
        return $this->hasOne(UserSubcrption::class, 'queued_after_subscription_id');
    }

    /**
     * Pause the subscription
     * 
     * @param int $days Number of days to pause
     * @param string $reason Reason for pausing
     * @param string $performedByType 'admin' or 'user'
     * @param int|null $performedById ID of the person performing the action
     * @param string|null $performedByName Name of the person performing the action
     * @param string|null $notes Additional notes
     * @return bool
     */
    public function pause($days, $reason = null, $performedByType = 'admin', $performedById = null, $performedByName = null, $notes = null)
    {
        if ($this->is_paused) {
            return ['success' => false, 'message' => 'Subscription is already paused.'];
        }

        if ($this->status !== 'active') {
            return ['success' => false, 'message' => 'Can only pause active subscriptions.'];
        }

        // Store original end date if not already stored
        $originalEndDate = $this->original_end_date ?: $this->end_date;
        if (!$this->original_end_date) {
            $this->original_end_date = $this->end_date;
        }

        $pausedAt = now();
        $pausedUntil = $pausedAt->copy()->addDays($days);

        // Extend the end date by the number of paused days
        $currentEndDate = Carbon::parse($this->end_date);
        $newEndDate = $currentEndDate->copy()->addDays($days);

        $this->is_paused = true;
        $this->paused_at = $pausedAt;
        $this->paused_until = $pausedUntil;
        $this->total_paused_days += $days;
        $this->end_date = $newEndDate->format('Y-m-d');
        $this->save();

        // Log the pause action
        SubscriptionPauseLog::create([
            'user_subcrption_id' => $this->id,
            'action' => 'pause',
            'action_timestamp' => $pausedAt,
            'paused_at' => $pausedAt,
            'paused_days' => $days,
            'reason' => $reason,
            'performed_by_type' => $performedByType,
            'performed_by_id' => $performedById,
            'performed_by_name' => $performedByName,
            'notes' => $notes,
            'metadata' => [
                'original_end_date' => $originalEndDate,
                'new_end_date' => $this->end_date,
                'days_added' => $days,
            ],
        ]);

        return [
            'success' => true,
            'message' => "Subscription paused successfully. End date extended by {$days} day(s).",
            'data' => [
                'days_paused' => $days,
                'new_end_date' => $this->end_date,
            ]
        ];
    }

    /**
     * Resume the subscription
     * 
     * @param string $performedByType 'admin' or 'user'
     * @param int|null $performedById ID of the person performing the action
     * @param string|null $performedByName Name of the person performing the action
     * @param string|null $notes Additional notes
     * @return bool
     */
    public function resume($performedByType = 'admin', $performedById = null, $performedByName = null, $notes = null)
    {
        if (!$this->is_paused) {
            return ['success' => false, 'message' => 'Subscription is not currently paused.'];
        }

        if ($this->status !== 'active') {
            return ['success' => false, 'message' => 'Cannot resume a subscription that is not active.'];
        }

        $resumedAt       = now();
        $pausedAt        = $this->paused_at    ? Carbon::parse($this->paused_at)    : now();
        $pausedUntil     = $this->paused_until ? Carbon::parse($this->paused_until) : $pausedAt;

        $endDateBeforeResume   = $this->end_date;
        $totalPausedDaysBefore = $this->total_paused_days;

        if ($pausedAt->isFuture()) {
            // Pause hasn't started yet (admin approved a future-date pause request).
            // Restore the full end_date extension that was added during approval.
            $daysToRestore    = $pausedAt->isSameDay($pausedUntil) ? 1 : (int) $pausedAt->diffInDays($pausedUntil);
            $actualPausedDays = 0;
            $remainingDays    = $daysToRestore;

            $this->end_date          = Carbon::parse($this->end_date)->subDays($daysToRestore)->format('Y-m-d');
            $this->total_paused_days = max(0, $this->total_paused_days - $daysToRestore);
        } else {
            // Pause already started — standard early-resume logic.
            $actualPausedDays = max(0, $pausedAt->diffInDays($resumedAt));
            $remainingDays    = max(0, $pausedUntil->diffInDays($resumedAt, false));

            if ($remainingDays > 0) {
                $this->end_date          = Carbon::parse($this->end_date)->subDays($remainingDays)->format('Y-m-d');
                $this->total_paused_days = max(0, $this->total_paused_days - $remainingDays);
            }
        }

        // Direct DB update — bypasses Eloquent date-cast dirty-check and model events
        // to guarantee is_paused is cleared even if save() would silently no-op.
        $affected = DB::table('user_subcrptions')->where('id', $this->id)->update([
            'is_paused'          => false,
            'paused_at'          => null,
            'paused_until'       => null,
            'end_date'           => $this->end_date,
            'total_paused_days'  => $this->total_paused_days,
            'updated_at'         => now(),
        ]);

        Log::info('UserSubcrption.resume DB update', [
            'subscription_id' => $this->id,
            'rows_affected'   => $affected,
            'is_paused_set_to'=> false,
            'new_end_date'    => $this->end_date,
        ]);

        if ($affected === 0) {
            return ['success' => false, 'message' => 'Failed to resume subscription. Please try again.'];
        }

        // Sync model state with what we just wrote
        $this->is_paused   = false;
        $this->paused_at   = null;
        $this->paused_until = null;

        // Mark the matching approved pause request as "resumed"
        // so the app can distinguish an active pause from a completed one.
        DB::table('subscription_pause_requests')
            ->where('user_subcrption_id', $this->id)
            ->where('status', 'approved')
            ->update([
                'status'      => 'resumed',
                'admin_notes' => trim(($notes ? $notes . ' — ' : '') . 'Resumed by ' . ($performedByType === 'user' ? 'user' : 'admin') . ' on ' . $resumedAt->format('Y-m-d H:i')),
                'reviewed_at' => $resumedAt,
                'updated_at'  => $resumedAt,
            ]);

        // Log the resume action
        SubscriptionPauseLog::create([
            'user_subcrption_id' => $this->id,
            'action' => 'resume',
            'action_timestamp' => $resumedAt,
            'paused_at' => $pausedAt,
            'resumed_at' => $resumedAt,
            'paused_days' => $actualPausedDays,
            'performed_by_type' => $performedByType,
            'performed_by_id' => $performedById,
            'performed_by_name' => $performedByName,
            'notes' => $notes ?: ($actualPausedDays < 1 ? 'Resumed immediately after pause (mistaken pause)' : null),
            'metadata' => [
                'scheduled_resume_date' => $pausedUntil->format('Y-m-d H:i:s'),
                'actual_resume_date' => $resumedAt->format('Y-m-d H:i:s'),
                'remaining_days' => $remainingDays,
                'actual_paused_days' => $actualPausedDays,
                'end_date_before_resume' => $endDateBeforeResume,
                'end_date_after_resume' => $this->end_date,
                'total_paused_days_before' => $totalPausedDaysBefore,
                'total_paused_days_after' => $this->total_paused_days,
                'was_mistaken_pause' => $actualPausedDays < 1,
            ],
        ]);

        return [
            'success' => true,
            'message' => $remainingDays > 0
                ? "Subscription resumed successfully. End date adjusted by {$remainingDays} day(s)."
                : 'Subscription resumed successfully.',
            'data' => [
                'actual_paused_days' => $actualPausedDays,
                'remaining_days' => $remainingDays,
                'end_date_adjusted' => $remainingDays > 0,
            ]
        ];
    }

    /**
     * Pause the subscription using specific start/end dates from an approved pause request.
     */
    public function pauseByRequest(string $pauseStartDate, string $pauseEndDate, $reason = null, $performedByType = 'admin', $performedById = null, $performedByName = null, $notes = null): array
    {
        if ($this->is_paused) {
            return ['success' => false, 'message' => 'Subscription is already paused.'];
        }

        if ($this->status !== 'active') {
            return ['success' => false, 'message' => 'Can only pause active subscriptions.'];
        }

        $start = Carbon::parse($pauseStartDate);
        $end   = Carbon::parse($pauseEndDate);
        // Inclusive counting: same day = 1, next day = 2, etc.
        $days  = $start->isSameDay($end) ? 1 : (int) $start->diffInDays($end);

        if (!$this->original_end_date) {
            $this->original_end_date = $this->attributes['end_date'];
        }

        $currentEndDate = Carbon::parse($this->attributes['end_date']);
        $newEndDate     = $currentEndDate->copy()->addDays($days);

        $this->is_paused        = true;
        $this->paused_at        = $start;
        $this->paused_until     = $end;
        $this->total_paused_days += $days;
        $this->attributes['end_date'] = $newEndDate->format('Y-m-d');
        $this->save();

        SubscriptionPauseLog::create([
            'user_subcrption_id' => $this->id,
            'action'             => 'pause',
            'action_timestamp'   => now(),
            'paused_at'          => $start,
            'paused_days'        => $days,
            'reason'             => $reason,
            'performed_by_type'  => $performedByType,
            'performed_by_id'    => $performedById,
            'performed_by_name'  => $performedByName,
            'notes'              => $notes,
            'metadata'           => [
                'pause_start_date' => $start->format('Y-m-d'),
                'pause_end_date'   => $end->format('Y-m-d'),
                'new_end_date'     => $newEndDate->format('Y-m-d'),
                'days_added'       => $days,
            ],
        ]);

        return [
            'success' => true,
            'message' => "Subscription paused for {$days} day(s). End date extended to {$newEndDate->format('Y-m-d')}.",
            'data'    => [
                'pause_start_date' => $start->format('Y-m-d'),
                'pause_end_date'   => $end->format('Y-m-d'),
                'days_paused'      => $days,
                'new_end_date'     => $newEndDate->format('Y-m-d'),
            ],
        ];
    }
}
