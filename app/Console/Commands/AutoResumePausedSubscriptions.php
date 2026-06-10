<?php

namespace App\Console\Commands;

use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoResumePausedSubscriptions extends Command
{
    protected $signature   = 'subscriptions:auto-resume';
    protected $description = 'Auto-resume subscriptions whose pause window has ended';

    public function handle(): void
    {
        $today = Carbon::today()->toDateString();

        $subscriptions = UserSubcrption::where('is_paused', true)
            ->whereNotNull('paused_until')
            ->whereDate('paused_until', '<', $today)
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions to resume.');
            return;
        }

        foreach ($subscriptions as $subscription) {
            $subscription->is_paused   = false;
            $subscription->paused_at   = null;
            $subscription->paused_until = null;
            $subscription->save();

            Log::info('subscriptions:auto-resume', [
                'subscription_id' => $subscription->id,
                'user_id'         => $subscription->user_id,
                'paused_until'    => $subscription->getOriginal('paused_until'),
                'resumed_on'      => $today,
            ]);
        }

        $this->info("Resumed {$subscriptions->count()} subscription(s).");
    }
}
