<?php

namespace App\Console\Commands;

use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActivateQueuedSubscriptions extends Command
{
    protected $signature = 'subscriptions:activate-queued';

    protected $description = 'Activate queued subscriptions whose parent plan has ended';

    public function handle(): void
    {
        $today = now()->startOfDay();

        $queued = UserSubcrption::where('status', 'queued')
            ->with(['queuedAfter', 'subcrption_plans'])
            ->get();

        $activated = 0;

        foreach ($queued as $subscription) {
            $parent = $subscription->queuedAfter;

            // Activate if: parent is gone, parent is inactive, or parent's end_date has passed
            $shouldActivate = ! $parent
                || $parent->status === 'inactive'
                || Carbon::parse($parent->getRawOriginal('end_date'))->lt($today);

            if (! $shouldActivate) {
                continue;
            }

            $plan     = $subscription->subcrption_plans;
            $newStart = $today->copy();
            $newEnd   = $newStart->copy()->addWeeks((int) $plan->no_of_weeks);

            // Bypass mutators — write raw dates directly
            $subscription->attributes['start_date'] = $newStart->format('Y-m-d');
            $subscription->attributes['end_date']   = $newEnd->format('Y-m-d');
            $subscription->status                          = 'active';
            $subscription->queued_after_subscription_id   = null;
            $subscription->save();

            // Mark the parent inactive if it's still flagged active
            if ($parent && $parent->status === 'active') {
                $parent->status = 'inactive';
                $parent->save();
            }

            $activated++;
            Log::info("subscriptions:activate-queued: activated #{$subscription->id} for user #{$subscription->user_id}, new end_date {$newEnd->format('Y-m-d')}");
        }

        $this->info("Activated {$activated} queued subscription(s).");
    }
}
