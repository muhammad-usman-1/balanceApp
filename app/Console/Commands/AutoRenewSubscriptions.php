<?php

namespace App\Console\Commands;

use App\Models\SubscriptionDay;
use App\Models\SubscriptionMeal;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoRenewSubscriptions extends Command
{
    protected $signature = 'subscriptions:auto-renew {--days=3 : Days before end_date to trigger renewal}';

    protected $description = 'Queue renewal subscriptions for active plans expiring soon';

    public function handle(): void
    {
        $daysAhead = (int) $this->option('days');
        $threshold = now()->addDays($daysAhead)->format('Y-m-d');
        $today     = now()->format('Y-m-d');

        // Find active plans expiring within the window that haven't been queued yet
        $expiringSoon = UserSubcrption::where('status', 'active')
            ->where('auto_renew', true)
            ->whereNull('renewal_notified_at')
            ->whereRaw('DATE(end_date) <= ?', [$threshold])
            ->whereRaw('DATE(end_date) >= ?', [$today])
            ->whereDoesntHave('queuedSubscription')
            ->with(['subcrption_plans', 'subscription_days.subscription_meals'])
            ->get();

        $renewed = 0;

        foreach ($expiringSoon as $subscription) {
            try {
                DB::transaction(function () use ($subscription) {
                    $plan     = $subscription->subcrption_plans;
                    $newStart = Carbon::parse($subscription->getRawOriginal('end_date'))->addDay();
                    $newEnd   = $newStart->copy()->addWeeks((int) $plan->no_of_weeks);

                    $renewal = new UserSubcrption();
                    $renewal->user_id                      = $subscription->user_id;
                    $renewal->user_address_id              = $subscription->user_address_id;
                    $renewal->area_id                      = $subscription->area_id;
                    $renewal->branch_id                    = $subscription->branch_id;
                    $renewal->subcrption_plans_id          = $subscription->subcrption_plans_id;
                    $renewal->selected_days                = $subscription->selected_days;
                    $renewal->attributes['start_date']     = $newStart->format('Y-m-d');
                    $renewal->attributes['end_date']       = $newEnd->format('Y-m-d');
                    $renewal->price                        = $subscription->price;
                    $renewal->currency                     = $subscription->currency;
                    $renewal->payment                      = 'pending';
                    $renewal->payment_gateway              = $subscription->payment_gateway;
                    $renewal->card_last_four               = $subscription->card_last_four;
                    $renewal->card_brand                   = $subscription->card_brand;
                    $renewal->status                       = 'queued';
                    $renewal->queued_after_subscription_id = $subscription->id;
                    $renewal->auto_renew                   = true;
                    $renewal->is_personalized              = $subscription->is_personalized;
                    $renewal->protein                      = $subscription->protein;
                    $renewal->carbs                        = $subscription->carbs;
                    $renewal->save();

                    // Copy weekly delivery day pattern
                    $dayMap = [];
                    foreach ($subscription->subscription_days as $day) {
                        $newDay = SubscriptionDay::create([
                            'user_subcrptions_id' => $renewal->id,
                            'day'                 => $day->day,
                        ]);
                        $dayMap[$day->id] = $newDay->id;
                    }

                    // Copy meal assignments for each day
                    foreach ($subscription->subscription_days as $day) {
                        foreach ($day->subscription_meals as $meal) {
                            SubscriptionMeal::create([
                                'subscription_days_id' => $dayMap[$day->id],
                                'meal_id'              => $meal->meal_id,
                                'type'                 => $meal->type,
                            ]);
                        }
                    }

                    // Mark the source subscription as notified so we don't queue it again
                    $subscription->renewal_notified_at = now();
                    $subscription->save();
                });

                $renewed++;
                Log::info("subscriptions:auto-renew: queued renewal #{$subscription->id} for user #{$subscription->user_id}");
            } catch (\Throwable $e) {
                Log::error("subscriptions:auto-renew: failed for subscription #{$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info("Queued {$renewed} renewal subscription(s).");
    }
}
