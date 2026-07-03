<?php

namespace App\Console\Commands;

use App\Models\UserSubcrption;
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

        Log::info("ExpireSubscriptions: marked {$count} subscription(s) as inactive.");
        $this->info("Expired {$count} subscription(s).");
    }
}
