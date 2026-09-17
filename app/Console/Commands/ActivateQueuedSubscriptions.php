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

            // Parent must be done: gone, inactive, or its end_date has passed
            $parentDone = ! $parent
                || $parent->status === 'inactive'
                || Carbon::parse($parent->getRawOriginal('end_date'))->lt($today);

            if (! $parentDone) {
                continue;
            }

            // Respect the renewal's own start_date (default: day after the parent
            // ends, or a later date the customer explicitly chose via
            // renewal-start-date). Don't force it to "today" — only activate once
            // that date has actually arrived, so a customer-delayed start works.
            $storedStart = Carbon::parse($subscription->getRawOriginal('start_date'));
            if ($storedStart->gt($today)) {
                continue;
            }

            $subscription->status                        = 'active';
            $subscription->queued_after_subscription_id  = null;
            $subscription->save();

            // Mark the parent inactive if it's still flagged active
            if ($parent && $parent->status === 'active') {
                $parent->status = 'inactive';
                $parent->save();
            }

            $activated++;
            Log::info("subscriptions:activate-queued: activated #{$subscription->id} for user #{$subscription->user_id}, start_date {$storedStart->toDateString()}");
        }

        $this->info("Activated {$activated} queued subscription(s).");
    }
}
