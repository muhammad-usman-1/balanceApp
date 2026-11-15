<?php

namespace App\Console\Commands;

use App\Models\DeliveryOrder;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark active subscriptions as inactive when their end date has passed';

    public function handle(): void
    {
        $today = now()->startOfDay();

        $expired = UserSubcrption::where('status', 'active')
            ->where('is_paused', false)
            ->whereDate('end_date', '<', $today)
            ->get();

        $count = 0;

        foreach ($expired as $subscription) {
            $subscription->timestamps = false;
            $subscription->status = 'inactive';
            $subscription->save();
            $count++;
        }

        // Also end plans that already received every delivery they include
        // (days per week x weeks) even if end_date is later — e.g. older plans
        // whose end_date ran one day too long. end_date is moved back to the
        // last delivery so the app and renewals show the real end.
        $completed = 0;
        $running = UserSubcrption::where('status', 'active')
            ->where('is_paused', false)
            ->whereDate('end_date', '>=', $today)
            ->with('subcrption_plans')
            ->withCount('subscription_days')
            ->get();

        foreach ($running as $subscription) {
            $total = $subscription->totalDeliveries();
            if ($total === null) {
                continue;
            }

            $pastDates = DeliveryOrder::where('user_subcrption_id', $subscription->id)
                ->whereDate('delivery_date', '<', $today)
                ->pluck('delivery_date');

            if ($subscription->countUsedDeliveries($pastDates) < $total) {
                continue;
            }

            $subscription->timestamps = false;
            $subscription->end_date   = Carbon::parse($pastDates->max())->format(config('panel.date_format'));
            $subscription->status     = 'inactive';
            $subscription->save();
            $completed++;
        }

        Log::info("ExpireSubscriptions: marked {$count} subscription(s) as inactive, {$completed} completed all deliveries.");
        $this->info("Expired {$count} subscription(s); {$completed} completed all deliveries.");
    }
}
